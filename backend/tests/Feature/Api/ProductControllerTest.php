<?php

namespace Tests\Feature\Api;

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ProductControllerTest extends TestCase
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

    // -------------------------------------------------------------------------
    // INDEX Tests - Listing products
    // -------------------------------------------------------------------------

    public function test_index_returns_paginated_products_for_authenticated_user(): void
    {
        Sanctum::actingAs($this->user);

        Product::factory()->count(15)->create();

        $response = $this->getJson('/api/products');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [],
                'links',
                'meta',
            ]);
    }

    public function test_index_returns_products(): void
    {
        Product::factory()->create(['name' => 'Product 1', 'price' => 100]);
        Product::factory()->create(['name' => 'Product 2', 'price' => 200]);

        Sanctum::actingAs($this->user);

        $response = $this->getJson('/api/products');

        $response->assertStatus(200);

        $data = $response->json('data');
        $this->assertGreaterThanOrEqual(2, count($data));
    }

    public function test_index_requires_authentication(): void
    {
        $response = $this->getJson('/api/products');

        $response->assertStatus(401);
    }

    public function test_index_respects_per_page_parameter(): void
    {
        Product::factory()->count(20)->create();

        Sanctum::actingAs($this->user);

        $response = $this->getJson('/api/products?per_page=5');

        $response->assertStatus(200);

        $data = $response->json('data');
        $this->assertCount(5, $data);
    }

    public function test_index_filters_products_by_price_min(): void
    {
        Product::factory()->create(['price' => 50]);
        Product::factory()->create(['price' => 150]);
        Product::factory()->create(['price' => 250]);

        Sanctum::actingAs($this->user);

        $response = $this->getJson('/api/products?min_price=100');

        $response->assertStatus(200);

        $data = $response->json('data');
        foreach ($data as $product) {
            $this->assertGreaterThanOrEqual(100, $product['price']);
        }
    }

    public function test_index_filters_products_by_price_max(): void
    {
        Product::factory()->create(['price' => 50]);
        Product::factory()->create(['price' => 150]);
        Product::factory()->create(['price' => 250]);

        Sanctum::actingAs($this->user);

        $response = $this->getJson('/api/products?max_price=200');

        $response->assertStatus(200);

        $data = $response->json('data');
        foreach ($data as $product) {
            $this->assertLessThanOrEqual(200, $product['price']);
        }

        $this->assertLessThanOrEqual(2, count($data));
    }

    public function test_index_searches_products_by_name(): void
    {
        Product::factory()->create(['name' => 'Laptop Pro']);
        Product::factory()->create(['name' => 'Mouse Wireless']);
        Product::factory()->create(['name' => 'Laptop Basic']);

        Sanctum::actingAs($this->user);

        $response = $this->getJson('/api/products?search=Laptop');

        $response->assertStatus(200);

        $data = $response->json('data');
        $this->assertGreaterThanOrEqual(2, count($data));
    }

    // -------------------------------------------------------------------------
    // SHOW Tests - Retrieving a single product
    // -------------------------------------------------------------------------

    public function test_show_returns_product_for_authenticated_user(): void
    {
        $product = Product::factory()->create([
            'name' => 'Test Product',
            'price' => 99.99,
        ]);

        Sanctum::actingAs($this->user);

        $response = $this->getJson('/api/products/' . $product->id);

        $response->assertStatus(200)
            ->assertJson([
                'id' => $product->id,
                'name' => 'Test Product',
                'price' => 99.99,
            ]);
    }

    public function test_show_returns_404_for_nonexistent_product(): void
    {
        Sanctum::actingAs($this->user);

        $response = $this->getJson('/api/products/99999');

        $response->assertStatus(404);
    }

    public function test_show_requires_authentication(): void
    {
        $product = Product::factory()->create();

        $response = $this->getJson('/api/products/' . $product->id);

        $response->assertStatus(401);
    }

    // -------------------------------------------------------------------------
    // STORE Tests - Creating products (if enabled)
    // -------------------------------------------------------------------------

    public function test_store_creates_product_for_authenticated_user(): void
    {
        Sanctum::actingAs($this->user);

        $productData = [
            'name' => 'New Product',
            'description' => 'A great product',
            'price' => 149.99,
        ];

        $response = $this->postJson('/api/products', $productData);

        $response->assertStatus(201)
            ->assertJson([
                'name' => 'New Product',
                'price' => 149.99,
            ]);

        $this->assertDatabaseHas('products', [
            'name' => 'New Product',
            'price' => 149.99,
        ]);
    }

    public function test_store_validates_required_fields(): void
    {
        Sanctum::actingAs($this->user);

        $response = $this->postJson('/api/products', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'price']);
    }

    public function test_store_validates_price_is_numeric(): void
    {
        Sanctum::actingAs($this->user);

        $response = $this->postJson('/api/products', [
            'name' => 'Test Product',
            'price' => 'not-a-number',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['price']);
    }

    public function test_store_validates_price_is_non_negative(): void
    {
        Sanctum::actingAs($this->user);

        $response = $this->postJson('/api/products', [
            'name' => 'Test Product',
            'price' => -10,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['price']);
    }

    public function test_store_validates_name_max_length(): void
    {
        Sanctum::actingAs($this->user);

        $response = $this->postJson('/api/products', [
            'name' => str_repeat('a', 300),
            'price' => 100,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name']);
    }

    public function test_store_requires_authentication(): void
    {
        $response = $this->postJson('/api/products', [
            'name' => 'Test Product',
            'price' => 100,
        ]);

        $response->assertStatus(401);
    }

    // -------------------------------------------------------------------------
    // UPDATE Tests - Updating products
    // -------------------------------------------------------------------------

    public function test_update_modifies_product_for_authenticated_user(): void
    {
        $product = Product::factory()->create([
            'name' => 'Original Name',
            'price' => 100,
        ]);

        Sanctum::actingAs($this->user);

        $updateData = [
            'name' => 'Updated Name',
            'price' => 199.99,
        ];

        $response = $this->putJson('/api/products/' . $product->id, $updateData);

        $response->assertStatus(200)
            ->assertJson([
                'name' => 'Updated Name',
                'price' => 199.99,
            ]);

        $product->refresh();
        $this->assertEquals('Updated Name', $product->name);
        $this->assertEquals(199.99, $product->price);
    }

    public function test_update_with_partial_data(): void
    {
        $product = Product::factory()->create([
            'name' => 'Original',
            'price' => 100,
        ]);

        Sanctum::actingAs($this->user);

        $response = $this->putJson('/api/products/' . $product->id, [
            'name' => 'Updated Name',
        ]);

        $response->assertStatus(200);

        $product->refresh();
        $this->assertEquals('Updated Name', $product->name);
        $this->assertEquals(100, $product->price); // Price unchanged
    }

    public function test_update_requires_authentication(): void
    {
        $product = Product::factory()->create();

        $response = $this->putJson('/api/products/' . $product->id, [
            'name' => 'Updated',
        ]);

        $response->assertStatus(401);
    }

    // -------------------------------------------------------------------------
    // DESTROY Tests - Deleting products
    // -------------------------------------------------------------------------

    public function test_delete_removes_product_for_authenticated_user(): void
    {
        $product = Product::factory()->create();

        Sanctum::actingAs($this->user);

        $response = $this->deleteJson('/api/products/' . $product->id);

        $response->assertStatus(204);

        $this->assertDatabaseMissing('products', ['id' => $product->id]);
    }

    public function test_delete_returns_404_for_nonexistent_product(): void
    {
        Sanctum::actingAs($this->user);

        $response = $this->deleteJson('/api/products/99999');

        $response->assertStatus(404);
    }

    public function test_delete_requires_authentication(): void
    {
        $product = Product::factory()->create();

        $response = $this->deleteJson('/api/products/' . $product->id);

        $response->assertStatus(401);
    }

    // -------------------------------------------------------------------------
    // Product with description tests
    // -------------------------------------------------------------------------

    public function test_product_with_long_description(): void
    {
        $product = Product::factory()->create([
            'name' => 'Complex Product',
            'description' => 'This is a very detailed description of the product that explains all its features and benefits for the customer.',
            'price' => 299.99,
        ]);

        Sanctum::actingAs($this->user);

        $response = $this->getJson('/api/products/' . $product->id);

        $response->assertStatus(200)
            ->assertJson([
                'name' => 'Complex Product',
                'description' => $product->description,
                'price' => 299.99,
            ]);
    }

    public function test_product_with_zero_price(): void
    {
        $product = Product::factory()->create([
            'name' => 'Free Product',
            'price' => 0,
        ]);

        Sanctum::actingAs($this->user);

        $response = $this->getJson('/api/products/' . $product->id);

        $response->assertStatus(200)
            ->assertJson([
                'name' => 'Free Product',
                'price' => 0,
            ]);
    }

    public function test_product_with_high_price(): void
    {
        $product = Product::factory()->create([
            'name' => 'Premium Product',
            'price' => 99999.99,
        ]);

        Sanctum::actingAs($this->user);

        $response = $this->getJson('/api/products/' . $product->id);

        $response->assertStatus(200)
            ->assertJson([
                'name' => 'Premium Product',
                'price' => 99999.99,
            ]);
    }

    // -------------------------------------------------------------------------
    // Pagination edge cases
    // -------------------------------------------------------------------------

    public function test_index_with_empty_database(): void
    {
        Sanctum::actingAs($this->user);

        $response = $this->getJson('/api/products');

        $response->assertStatus(200);

        $data = $response->json('data');
        $this->assertEmpty($data);
    }

    public function test_index_with_single_product(): void
    {
        Product::factory()->create();

        Sanctum::actingAs($this->user);

        $response = $this->getJson('/api/products');

        $response->assertStatus(200);

        $data = $response->json('data');
        $this->assertCount(1, $data);
    }

    public function test_index_pagination_works_correctly(): void
    {
        Product::factory()->count(25)->create();

        Sanctum::actingAs($this->user);

        // First page
        $response1 = $this->getJson('/api/products?page=1&per_page=10');
        $data1 = $response1->json('data');
        $this->assertCount(10, $data1);

        // Second page
        $response2 = $this->getJson('/api/products?page=2&per_page=10');
        $data2 = $response2->json('data');
        $this->assertCount(10, $data2);

        // Third page (remaining 5)
        $response3 = $this->getJson('/api/products?page=3&per_page=10');
        $data3 = $response3->json('data');
        $this->assertCount(5, $data3);
    }
}