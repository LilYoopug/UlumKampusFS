<?php

namespace Tests\Feature\Api;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Laravel\Sanctum\Sanctum;
use Midtrans\Config as MidtransConfig;
use Midtrans\Snap;
use Mockery;
use Tests\TestCase;

class PaymentControllerTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create(['role' => 'student']);
        $this->admin = User::factory()->create(['role' => 'admin']);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    // -------------------------------------------------------------------------
    // CREATE TRANSACTION Tests
    // -------------------------------------------------------------------------

    public function test_create_transaction_returns_snap_token(): void
    {
        Sanctum::actingAs($this->user);

        // Mock Midtrans Snap::getSnapToken
        $snapToken = 'fake-snap-token-for-testing';

        Snap::shouldReceive('getSnapToken')
            ->once()
            ->with(Mockery::on(function ($params) {
                return isset($params['transaction_details'])
                    && $params['transaction_details']['order_id'] === 'ORDER-123'
                    && $params['transaction_details']['gross_amount'] === 50000;
            }))
            ->andReturn($snapToken);

        $response = $this->postJson('/api/payment/create-transaction', [
            'order_id' => 'ORDER-123',
            'amount' => 50000,
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'snap_token' => $snapToken,
            ]);
    }

    public function test_create_transaction_validates_required_fields(): void
    {
        Sanctum::actingAs($this->user);

        $response = $this->postJson('/api/payment/create-transaction', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['order_id', 'amount']);
    }

    public function test_create_transaction_validates_order_id_is_unique(): void
    {
        Sanctum::actingAs($this->user);

        $response = $this->postJson('/api/payment/create-transaction', [
            'order_id' => 'ORDER-123',
            'amount' => 50000,
        ]);

        // First request should succeed or fail based on mock, but validation should pass
        $response->assertStatus(200);

        // Second request with same order_id should fail validation
        $response2 = $this->postJson('/api/payment/create-transaction', [
            'order_id' => 'ORDER-123',
            'amount' => 50000,
        ]);

        $response2->assertStatus(422)
            ->assertJsonValidationErrors(['order_id']);
    }

    public function test_create_transaction_validates_amount_minimum(): void
    {
        Sanctum::actingAs($this->user);

        $response = $this->postJson('/api/payment/create-transaction', [
            'order_id' => 'ORDER-123',
            'amount' => 500, // Below minimum of 1000
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['amount']);
    }

    public function test_create_transaction_includes_customer_details(): void
    {
        Sanctum::actingAs($this->user);

        $snapToken = 'fake-snap-token';

        Snap::shouldReceive('getSnapToken')
            ->once()
            ->with(Mockery::on(function ($params) {
                return isset($params['customer_details'])
                    && $params['customer_details']['first_name'] === $this->user->name
                    && $params['customer_details']['email'] === $this->user->email;
            }))
            ->andReturn($snapToken);

        $response = $this->postJson('/api/payment/create-transaction', [
            'order_id' => 'ORDER-456',
            'amount' => 100000,
        ]);

        $response->assertStatus(200);
    }

    public function test_create_transaction_requires_authentication(): void
    {
        $response = $this->postJson('/api/payment/create-transaction', [
            'order_id' => 'ORDER-123',
            'amount' => 50000,
        ]);

        $response->assertStatus(401);
    }

    public function test_create_transaction_handles_midtrans_exception(): void
    {
        Sanctum::actingAs($this->user);

        Snap::shouldReceive('getSnapToken')
            ->once()
            ->andThrow(new \Exception('Midtrans API error'));

        $response = $this->postJson('/api/payment/create-transaction', [
            'order_id' => 'ORDER-ERROR',
            'amount' => 50000,
        ]);

        $response->assertStatus(500)
            ->assertJson([
                'error' => 'Midtrans API error',
            ]);
    }

    // -------------------------------------------------------------------------
    // CHECK TRANSACTION STATUS Tests
    // -------------------------------------------------------------------------

    public function test_check_transaction_status_returns_transaction_info(): void
    {
        Sanctum::actingAs($this->user);

        $transactionData = [
            'order_id' => 'ORDER-STATUS',
            'gross_amount' => 75000,
            'transaction_status' => 'pending',
            'payment_type' => 'bank_transfer',
            'transaction_time' => '2025-12-28 10:00:00',
        ];

        $transactionMock = Mockery::mock();
        $transactionMock->order_id = $transactionData['order_id'];
        $transactionMock->gross_amount = $transactionData['gross_amount'];
        $transactionMock->transaction_status = $transactionData['transaction_status'];
        $transactionMock->payment_type = $transactionData['payment_type'];
        $transactionMock->transaction_time = $transactionData['transaction_time'];
        $transactionMock->expiry_time = null;

        $transactionClass = Mockery::mock('alias:Midtrans\Transaction');
        $transactionClass->shouldReceive('status')
            ->once()
            ->with('ORDER-STATUS')
            ->andReturn($transactionMock);

        $response = $this->getJson('/api/payment/status/ORDER-STATUS');

        $response->assertStatus(200)
            ->assertJson([
                'order_id' => 'ORDER-STATUS',
                'gross_amount' => 75000,
                'transaction_status' => 'pending',
                'payment_type' => 'bank_transfer',
            ]);
    }

    public function test_check_transaction_status_returns_404_for_nonexistent_transaction(): void
    {
        Sanctum::actingAs($this->user);

        $transactionClass = Mockery::mock('alias:Midtrans\Transaction');
        $transactionClass->shouldReceive('status')
            ->once()
            ->with('NONEXISTENT-ORDER')
            ->andThrow(new \Exception('Transaction not found'));

        $response = $this->getJson('/api/payment/status/NONEXISTENT-ORDER');

        $response->assertStatus(404)
            ->assertJson([
                'error' => 'Transaction not found or an error occurred.',
            ]);
    }

    public function test_check_transaction_status_requires_authentication(): void
    {
        $response = $this->getJson('/api/payment/status/ORDER-123');

        $response->assertStatus(401);
    }

    // -------------------------------------------------------------------------
    // NOTIFICATION HANDLER Tests (Webhook)
    // -------------------------------------------------------------------------

    public function test_notification_handler_processes_settlement(): void
    {
        $notificationPayload = json_encode([
            'order_id' => 'ORDER-NOTIF',
            'status_code' => '200',
            'gross_amount' => '50000',
            'transaction_status' => 'settlement',
            'fraud_status' => 'accept',
            'signature_key' => hash('sha512', 'ORDER-NOTIF20050000' . config('midtrans.server_key')),
        ]);

        $response = $this->postJson('/api/payment/notification', [], [], [], [], $notificationPayload);

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'ok',
            ]);
    }

    public function test_notification_handler_processes_pending(): void
    {
        $notificationPayload = json_encode([
            'order_id' => 'ORDER-PENDING',
            'status_code' => '201',
            'gross_amount' => '50000',
            'transaction_status' => 'pending',
            'fraud_status' => 'accept',
            'signature_key' => hash('sha512', 'ORDER-PENDING20150000' . config('midtrans.server_key')),
        ]);

        $response = $this->postJson('/api/payment/notification', [], [], [], [], $notificationPayload);

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'ok',
            ]);
    }

    public function test_notification_handler_processes_capture_with_fraud_accept(): void
    {
        $notificationPayload = json_encode([
            'order_id' => 'ORDER-CAPTURE',
            'status_code' => '200',
            'gross_amount' => '50000',
            'transaction_status' => 'capture',
            'fraud_status' => 'accept',
            'signature_key' => hash('sha512', 'ORDER-CAPTURE20050000' . config('midtrans.server_key')),
        ]);

        $response = $this->postJson('/api/payment/notification', [], [], [], [], $notificationPayload);

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'ok',
            ]);
    }

    public function test_notification_handler_processes_deny(): void
    {
        $notificationPayload = json_encode([
            'order_id' => 'ORDER-DENY',
            'status_code' => '202',
            'gross_amount' => '50000',
            'transaction_status' => 'deny',
            'fraud_status' => 'accept',
            'signature_key' => hash('sha512', 'ORDER-DENY20250000' . config('midtrans.server_key')),
        ]);

        $response = $this->postJson('/api/payment/notification', [], [], [], [], $notificationPayload);

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'ok',
            ]);
    }

    public function test_notification_handler_processes_expire(): void
    {
        $notificationPayload = json_encode([
            'order_id' => 'ORDER-EXPIRE',
            'status_code' => '407',
            'gross_amount' => '50000',
            'transaction_status' => 'expire',
            'fraud_status' => 'accept',
            'signature_key' => hash('sha512', 'ORDER-EXPIRE40750000' . config('midtrans.server_key')),
        ]);

        $response = $this->postJson('/api/payment/notification', [], [], [], [], $notificationPayload);

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'ok',
            ]);
    }

    public function test_notification_handler_processes_cancel(): void
    {
        $notificationPayload = json_encode([
            'order_id' => 'ORDER-CANCEL',
            'status_code' => '200',
            'gross_amount' => '50000',
            'transaction_status' => 'cancel',
            'fraud_status' => 'accept',
            'signature_key' => hash('sha512', 'ORDER-CANCEL20050000' . config('midtrans.server_key')),
        ]);

        $response = $this->postJson('/api/payment/notification', [], [], [], [], $notificationPayload);

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'ok',
            ]);
    }

    public function test_notification_handler_rejects_invalid_signature(): void
    {
        $notificationPayload = json_encode([
            'order_id' => 'ORDER-INVALID',
            'status_code' => '200',
            'gross_amount' => '50000',
            'transaction_status' => 'settlement',
            'fraud_status' => 'accept',
            'signature_key' => 'invalid-signature-key',
        ]);

        $response = $this->postJson('/api/payment/notification', [], [], [], [], $notificationPayload);

        $response->assertStatus(403)
            ->assertJson([
                'error' => 'Invalid signature',
            ]);
    }

    public function test_notification_handler_does_not_require_authentication(): void
    {
        // Webhook endpoints should be publicly accessible
        $notificationPayload = json_encode([
            'order_id' => 'ORDER-PUBLIC',
            'status_code' => '200',
            'gross_amount' => '50000',
            'transaction_status' => 'settlement',
            'fraud_status' => 'accept',
            'signature_key' => hash('sha512', 'ORDER-PUBLIC20050000' . config('midtrans.server_key')),
        ]);

        $response = $this->postJson('/api/payment/notification', [], [], [], [], $notificationPayload);

        $response->assertStatus(200);
    }

    // -------------------------------------------------------------------------
    // Configuration Tests
    // -------------------------------------------------------------------------

    public function test_midtrans_config_is_set(): void
    {
        $this->assertNotNull(config('midtrans.server_key'));
        $this->assertIsBool(config('midtrans.is_production'));
        $this->assertIsBool(config('midtrans.is_sanitized'));
        $this->assertIsBool(config('midtrans.is_3ds'));
    }

    public function test_midtrans_is_in_test_mode_by_default(): void
    {
        $this->assertFalse(config('midtrans.is_production'));
    }
}