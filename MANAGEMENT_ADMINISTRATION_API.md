# Management Administration API Documentation

## Overview
This document describes the backend API implementation for the Manajemen Administrasi (Management Administration) page. The API provides endpoints for managing student payments, fee types, and payment statistics.

## Base URL
All endpoints are prefixed with: `/api/management/administration`

## Authentication
All endpoints require authentication and the user must have either `admin` or `dosen` role.

## Endpoints

### 1. Overview Statistics
**GET** `/overview`

Returns dashboard statistics including total students, payments, paid amounts, unpaid amounts, and pending payments.

**Response:**
```json
{
  "success": true,
  "data": {
    "total_students": 1245,
    "total_payments": 2500000000,
    "total_paid": 2300000000,
    "total_unpaid": 200000000,
    "pending_payments": 42
  }
}
```

### 2. Recent Payments
**GET** `/recent-payments?limit=10`

Returns a list of recent payment transactions.

**Query Parameters:**
- `limit` (optional): Number of records to return (default: 10)

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "student": "Ahmad Faris",
      "type": "administrasi_registration_title",
      "amount": 5000000,
      "date": "2024-08-15",
      "status": "completed"
    }
  ]
}
```

### 3. Payment Types Statistics
**GET** `/payment-types`

Returns statistics grouped by payment type (registration, semester, exam, etc.).

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "id": "registration",
      "title": "administrasi_registration_title",
      "total": 500000000,
      "paid": 480000000,
      "unpaid": 20000000
    }
  ]
}
```

### 4. Payment Methods
**GET** `/payment-methods`

Returns available payment methods with transaction counts.

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "id": "bank_transfer",
      "name": "administrasi_payment_method_bank_transfer",
      "icon": "<svg>...</svg>",
      "count": 245
    }
  ]
}
```

### 5. Students Payment Status
**GET** `/students?per_page=15&search=keyword`

Returns paginated list of students with their payment status.

**Query Parameters:**
- `per_page` (optional): Number of records per page (default: 15)
- `search` (optional): Search by name, email, or ID

**Response:**
```json
{
  "success": true,
  "data": {
    "data": [
      {
        "id": 1,
        "name": "Ahmad Faris",
        "email": "ahmad@example.com",
        "total_amount": 9750000,
        "latest_transaction": "2024-12-10",
        "status": "paid"
      }
    ],
    "pagination": {
      "total": 100,
      "per_page": 15,
      "current_page": 1,
      "last_page": 7
    }
  }
}
```

### 6. Student Payment Details
**GET** `/students/{studentId}`

Returns detailed payment information for a specific student.

**Response:**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "name": "Ahmad Faris",
    "email": "ahmad@example.com",
    "total_amount": 9750000,
    "latest_transaction": "2024-12-10",
    "status": "paid",
    "payment_list": [
      {
        "id": 1,
        "type": "administrasi_registration_title",
        "description": "administrasi_registration_desc",
        "amount": 5000000,
        "status": "paid",
        "date": "2024-09-15",
        "has_receipt": true
      }
    ]
  }
}
```

### 7. Update Payment Status
**PUT** `/students/{studentId}/payments/{paymentItemId}`

Updates the status of a specific payment item for a student.

**Request Body:**
```json
{
  "status": "paid"
}
```

**Status Values:**
- `paid`: Payment completed
- `unpaid`: Payment not completed
- `pending`: Payment in progress

**Response:**
```json
{
  "success": true,
  "message": "Payment status updated successfully",
  "data": {
    "id": 1,
    "item_id": "registration-1",
    "title_key": "administrasi_registration_title",
    "amount": 5000000,
    "status": "paid"
  }
}
```

### 8. Fee Types
**GET** `/fee-types`

Returns all available fee types.

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "id": "registration",
      "name": "administrasi_registration_title",
      "description": "administrasi_registration_desc",
      "amount": 5000000
    }
  ]
}
```

### 9. Create Fee Type
**POST** `/fee-types`

Creates a new fee type and assigns it to all students.

**Request Body:**
```json
{
  "item_id": "library",
  "title_key": "administrasi_library_title",
  "description_key": "administrasi_library_desc",
  "amount": 500000
}
```

**Response:**
```json
{
  "success": true,
  "message": "Fee type created successfully",
  "data": {
    "item_id": "library",
    "title_key": "administrasi_library_title",
    "description_key": "administrasi_library_desc",
    "amount": 500000
  }
}
```

### 10. Update Fee Type
**PUT** `/fee-types/{itemId}`

Updates an existing fee type for all students.

**Request Body:**
```json
{
  "title_key": "administrasi_library_title",
  "description_key": "administrasi_library_desc",
  "amount": 750000
}
```

**Response:**
```json
{
  "success": true,
  "message": "Fee type updated successfully",
  "data": {
    "item_id": "library",
    "title_key": "administrasi_library_title",
    "description_key": "administrasi_library_desc",
    "amount": 750000
  }
}
```

### 11. Delete Fee Type
**DELETE** `/fee-types/{itemId}`

Deletes a fee type for all students.

**Response:**
```json
{
  "success": true,
  "message": "Fee type deleted successfully"
}
```

### 12. Get Receipt
**GET** `/receipt/{historyId}`

Returns receipt details for a specific payment transaction.

**Response:**
```json
{
  "success": true,
  "data": {
    "id": "HIS-1234567890-1",
    "title": "administrasi_registration_title",
    "amount": 5000000,
    "date": "2024-09-15",
    "student_name": "Ahmad Faris",
    "student_id": 1,
    "method": "administrasi_payment_method_bank_transfer"
  }
}
```

## Database Schema

### Payment Methods Table
- `method_id`: Unique identifier (bank_transfer, credit_card, e_wallet, virtual_account)
- `name_key`: Translation key for display name
- `icon`: SVG icon as text
- `is_active`: Boolean indicating if method is active

### Payment Items Table
- `item_id`: Unique identifier (pattern: `{type}-{userId}`)
- `title_key`: Translation key for payment type name
- `description_key`: Translation key for description
- `amount`: Payment amount
- `status`: Payment status (paid/unpaid/pending)
- `due_date`: Due date for payment
- `user_id`: Foreign key to users table

### Payment Histories Table
- `history_id`: Unique transaction identifier
- `title`: Payment type name
- `amount`: Payment amount
- `payment_date`: Date of payment
- `status`: Transaction status (completed/failed/pending)
- `payment_method_id`: Foreign key to payment_methods table
- `user_id`: Foreign key to users table

## Seeding

The `ManagementAdministrationSeeder` creates:
1. 4 payment methods (bank_transfer, credit_card, e_wallet, virtual_account)
2. 4 payment types for each student (registration, semester, exam, other fees)
3. Sample payment histories for testing

To run the seeder:
```bash
php artisan db:seed --class=ManagementAdministrationSeeder
```

## Notes

1. **Unique Item IDs**: Each payment item has a unique `item_id` that combines the payment type with the user ID (e.g., `registration-1`, `registration-2`) to allow multiple students to have the same payment type.

2. **Payment Status Updates**: When a payment status is updated to "paid", a payment history record is automatically created.

3. **Fee Type Management**: Creating, updating, or deleting a fee type affects all students in the system.

4. **Translation Keys**: All names and descriptions use translation keys for internationalization support.

5. **Pagination**: Student list supports pagination with configurable page size.

6. **Search**: Student list supports searching by name, email, or ID.
