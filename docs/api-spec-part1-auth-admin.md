# API Specification Part 1 - Auth, User Management & Administration

## Authentication

### POST /api/auth/login
**Frontend Location**: Login.tsx (handleSubmit function), App.tsx (handleLogin function)
**Triggers**: Form submission on login page
**Auth Required**: No

Request Body:
```json
{
  "email": "string",
  "password": "string"
}
```

Response:
```json
{
  "user": {
    "id": "string",
    "name": "string",
    "email": "string",
    "role": "string",
    "avatarUrl": "string",
    "studentId": "string",
    "facultyId": "string",
    "majorId": "string",
    "bio": "string"
  },
  "token": "string"
}
```

**Notes**: Based on form fields in Login.tsx lines 48-63 and handleLogin function in App.tsx lines 137-160. Uses mock data in current implementation.

### POST /api/auth/register
**Frontend Location**: Register.tsx (handleSubmit function), App.tsx (handleRegister function)
**Triggers**: Form submission on registration page
**Auth Required**: No

Request Body:
```json
{
  "name": "string",
  "email": "string",
  "password": "string",
  "password_confirmation": "string",
  "role": "string",
  "phoneNumber": "string"
}
```

Response:
```json
{
  "user": {
    "id": "string",
    "name": "string",
    "email": "string",
    "role": "string",
    "avatarUrl": "string",
    "studentId": "string",
    "facultyId": "string",
    "majorId": "string",
    "bio": "string"
  },
  "token": "string"
}
```

**Notes**: Based on form fields in Register.tsx lines 81-113 and handleRegister function in App.tsx lines 356-381. Uses mock data in current implementation.

### POST /api/auth/register/student
**Frontend Location**: RegistrasiPage.tsx (handleSubmit function)
**Triggers**: New student submits comprehensive registration form
**Auth Required**: No (for new registrations) / Yes (for logged in users)
**Role Access**: All

Request Body:
```json
{
  "registrationType": "string",
  "personalInfo": {
    "name": "string",
    "email": "string",
    "phone_number": "string",
    "address": "string",
    "date_of_birth": "string",
    "place_of_birth": "string",
    "gender": "string",
    "religion": "string",
    "city": "string",
    "postal_code": "string",
    "nationality": "string",
    "parent_name": "string",
    "parent_occupation": "string",
    "parent_phone": "string",
    "nisn": "string",
    "nik": "string"
  },
  "educationInfo": {
    "high_school_name": "string",
    "high_school_address": "string",
    "high_school_graduation_year": "number",
    "high_school_type": "string",
    "high_school_major": "string",
    "high_school_average_grade": "number"
  },
  "preferences": {
    "faculty_preference_1": "string",
    "faculty_preference_2": "string"
  },
  "documents": [
    {
      "type": "string",
      "url": "string"
    }
  ]
}
```

Response:
```json
{
  "message": "Registration submitted successfully",
  "registration": {
    "id": "string",
    "status": "pending",
    "applicationDate": "string"
  }
}
```

**Notes**: Based on comprehensive student registration form in RegistrasiPage.tsx lines 38-370. Includes additional fields beyond basic registration.

### POST /api/auth/logout
**Frontend Location**: Settings.tsx (Account section logout button)
**Triggers**: User clicks logout button
**Auth Required**: Yes

Request Body: (empty)

Response:
```json
{
  "message": "Successfully logged out"
}
```

**Notes**: Based on logout functionality in Settings.tsx lines 330-333.

## User Management

### GET /api/users
**Frontend Location**: UserManagementPage.tsx (useEffect that loads users), App.tsx (currentUser and users state)
**Triggers**: Loading user management page, dashboard, or other pages that display user data
**Auth Required**: Yes (Admin role)
**Role Access**: Super Admin, Manajemen Kampus, Prodi Admin

Query Parameters:
- `page` (optional): Page number for pagination
- `limit` (optional): Number of users per page
- `search` (optional): Search term for filtering users
- `role` (optional): Filter by user role
- `facultyId` (optional): Filter by faculty
- `status` (optional): Filter by user status
- `sortBy` (optional): Field to sort by (name, email, joinDate, etc.)
- `sortOrder` (optional): Sort order (asc, desc)

Response:
```json
{
  "data": [
    {
      "id": "string",
      "name": "string",
      "email": "string",
      "role": "string",
      "avatarUrl": "string",
      "studentId": "string",
      "facultyId": "string",
      "majorId": "string",
      "studentStatus": "Aktif | Cuti | Lulus | DO | Pendaftaran",
      "gpa": "number",
      "totalSks": "number",
      "bio": "string",
      "phoneNumber": "string",
      "joinDate": "string",
      "created_at": "string",
      "updated_at": "string"
    }
  ],
  "pagination": {
    "current_page": "number",
    "total_pages": "number",
    "total_users": "number",
    "per_page": "number"
  }
}
```

**Notes**: Based on UserManagementPage.tsx lines 17-18 where users are loaded and used for filtering.

### POST /api/users
**Frontend Location**: UserManagementPage.tsx (handleAddUser function - modal functionality)
**Triggers**: Admin adds new user
**Auth Required**: Yes (Admin role)
**Role Access**: Super Admin, Manajemen Kampus

Request Body:
```json
{
  "name": "string",
  "email": "string",
  "password": "string",
  "role": "string",
  "facultyId": "string",
  "majorId": "string",
  "studentId": "string",
  "phoneNumber": "string"
}
```

Response:
```json
{
  "user": {
    "id": "string",
    "name": "string",
    "email": "string",
    "role": "string",
    "avatarUrl": "string",
    "studentId": "string",
    "facultyId": "string",
    "majorId": "string",
    "bio": "string",
    "phoneNumber": "string"
  }
}
```

**Notes**: Based on modal functionality in UserManagementPage.tsx lines 304-411 where new users can be added.

### GET /api/users/{id}
**Frontend Location**: Profile.tsx (useEffect that loads user profile), UserManagementPage.tsx (edit modal)
**Triggers**: Loading user profile page or viewing user details
**Auth Required**: Yes
**Role Access**: All (with authorization checks for viewing other users)

Response:
```json
{
  "user": {
    "id": "string",
    "name": "string",
    "email": "string",
    "role": "string",
    "avatarUrl": "string",
    "studentId": "string",
    "facultyId": "string",
    "majorId": "string",
    "studentStatus": "Aktif | Cuti | Lulus | DO | Pendaftaran",
    "gpa": "number",
    "totalSks": "number",
    "bio": "string",
    "phoneNumber": "string",
    "joinDate": "string",
    "badges": ["string"],
    "created_at": "string",
    "updated_at": "string"
  }
}
```

**Notes**: Based on Profile.tsx where user profile data is loaded and displayed.

### PUT /api/users/{id}
**Frontend Location**: Profile.tsx (saveProfile function), UserManagementPage.tsx (edit modal save)
**Triggers**: User updates their profile or admin updates user details
**Auth Required**: Yes
**Role Access**: User can update own profile, Admin can update any user

Request Body:
```json
{
  "name": "string",
  "email": "string",
  "role": "string",
  "facultyId": "string",
  "majorId": "string",
  "studentId": "string",
  "studentStatus": "Aktif | Cuti | Lulus | DO | Pendaftaran",
  "gpa": "number",
  "totalSks": "number",
  "bio": "string",
  "avatarUrl": "string",
  "phoneNumber": "string"
}
```

Response:
```json
{
  "user": {
    "id": "string",
    "name": "string",
    "email": "string",
    "role": "string",
    "avatarUrl": "string",
    "studentId": "string",
    "facultyId": "string",
    "majorId": "string",
    "studentStatus": "Aktif | Cuti | Lulus | DO | Pendaftaran",
    "gpa": "number",
    "totalSks": "number",
    "bio": "string",
    "phoneNumber": "string"
  }
}
```

**Notes**: Based on saveProfile function in Settings.tsx lines 37-63 and edit functionality in UserManagementPage.tsx.

### DELETE /api/users/{id}
**Frontend Location**: UserManagementPage.tsx (deleteUser function)
**Triggers**: Admin deletes user
**Auth Required**: Yes (Admin role)
**Role Access**: Super Admin, Manajemen Kampus

Response:
```json
{
  "message": "User deleted successfully"
}
```

**Notes**: Based on delete functionality in UserManagementPage.tsx lines 156-161.

### GET /api/users/profile
**Frontend Location**: Profile.tsx (useEffect that loads user profile)
**Triggers**: Loading user's own profile page
**Auth Required**: Yes
**Role Access**: User can access own profile

Response:
```json
{
  "user": {
    "id": "string",
    "name": "string",
    "email": "string",
    "role": "string",
    "avatarUrl": "string",
    "studentId": "string",
    "facultyId": "string",
    "majorId": "string",
    "studentStatus": "Aktif | Cuti | Lulus | DO | Pendaftaran",
    "gpa": "number",
    "totalSks": "number",
    "bio": "string",
    "phoneNumber": "string",
    "joinDate": "string",
    "badges": ["string"],
    "created_at": "string",
    "updated_at": "string"
  }
}
```

**Notes**: Based on Profile.tsx where user profile data is loaded and displayed for the currently authenticated user.

### PUT /api/users/{id}/profile
**Frontend Location**: Settings.tsx (saveProfile function)
**Triggers**: User updates their profile information
**Auth Required**: Yes
**Role Access**: User can update own profile

Request Body:
```json
{
  "name": "string",
  "bio": "string",
  "avatarUrl": "string"
}
```

Response:
```json
{
  "user": {
    "id": "string",
    "name": "string",
    "email": "string",
    "role": "string",
    "avatarUrl": "string",
    "studentId": "string",
    "facultyId": "string",
    "majorId": "string",
    "bio": "string"
  }
}
```

**Notes**: Based on profile update functionality in Settings.tsx lines 37-63.

## Student Registration Management

### GET /api/student-registrations
**Frontend Location**: StudentRegistrationPage.tsx (useEffect that loads registrations)
**Triggers**: Loading student registration management page
**Auth Required**: Yes (Admin role)
**Role Access**: Super Admin, Manajemen Kampus, Prodi Admin

Query Parameters:
- `page` (optional): Page number for pagination
- `limit` (optional): Number of registrations per page
- `status` (optional): Filter by registration status (pending, approved, rejected)
- `facultyId` (optional): Filter by faculty

Response:
```json
{
  "data": [
    {
      "id": "string",
      "userId": "string",
      "user": {
        "id": "string",
        "name": "string",
        "email": "string",
        "role": "string",
        "studentId": "string",
        "facultyId": "string",
        "majorId": "string"
      },
      "registrationType": "string",
      "status": "pending | approved | rejected",
      "applicationDate": "string",
      "approvalDate": "string",
      "personalInfo": {
        "name": "string",
        "email": "string",
        "phone_number": "string",
        "address": "string",
        "date_of_birth": "string",
        "place_of_birth": "string",
        "gender": "string"
      },
      "educationInfo": {
        "high_school_name": "string",
        "high_school_major": "string",
        "graduation_year": "string",
        "gpa": "number"
      },
      "preferences": {
        "faculty_choice": "string",
        "major_choice": "string",
        "second_choice": "string"
      },
      "documents": [
        {
          "type": "string",
          "url": "string",
          "status": "pending | approved | rejected"
        }
      ],
      "created_at": "string",
      "updated_at": "string"
    }
  ],
  "pagination": {
    "current_page": "number",
    "total_pages": "number",
    "total_registrations": "number",
    "per_page": "number"
  }
}
```

**Notes**: Based on StudentRegistrationPage.tsx lines 44-45 where registration data is loaded from mock data.

### PUT /api/student-registrations/{id}/approve
**Frontend Location**: StudentRegistrationPage.tsx (handleApproveRegistration function)
**Triggers**: Admin approves student registration
**Auth Required**: Yes (Admin role)
**Role Access**: Super Admin, Manajemen Kampus, Prodi Admin

Request Body: (empty)

Response:
```json
{
  "message": "Registration approved successfully",
  "registration": {
    "id": "string",
    "userId": "string",
    "status": "approved",
    "approvalDate": "string",
    "studentId": "string"
  }
}
```

**Notes**: Based on approve functionality in StudentRegistrationPage.tsx lines 147-158.

### PUT /api/student-registrations/{id}/reject
**Frontend Location**: StudentRegistrationPage.tsx (handleRejectRegistration function)
**Triggers**: Admin rejects student registration
**Auth Required**: Yes (Admin role)
**Role Access**: Super Admin, Manajemen Kampus, Prodi Admin

Request Body:
```json
{
  "reason": "string"
}
```

Response:
```json
{
  "message": "Registration rejected successfully",
  "registration": {
    "id": "string",
    "userId": "string",
    "status": "rejected",
    "rejectionReason": "string"
  }
}
```

**Notes**: Based on reject functionality in StudentRegistrationPage.tsx lines 160-171.

### POST /api/student-registrations
**Frontend Location**: RegistrasiPage.tsx (handleSubmit function)
**Triggers**: New student submits registration form
**Auth Required**: No (for new registrations) / Yes (for logged in users)
**Role Access**: All

Request Body:
```json
{
  "registrationType": "string",
  "personalInfo": {
    "name": "string",
    "email": "string",
    "phone_number": "string",
    "address": "string",
    "date_of_birth": "string",
    "place_of_birth": "string",
    "gender": "string"
  },
  "educationInfo": {
    "high_school_name": "string",
    "high_school_major": "string",
    "graduation_year": "string",
    "gpa": "number"
  },
  "preferences": {
    "faculty_choice": "string",
    "major_choice": "string",
    "second_choice": "string"
  },
  "documents": [
    {
      "type": "string",
      "url": "string"
    }
  ]
}
```

Response:
```json
{
  "message": "Registration submitted successfully",
  "registration": {
    "id": "string",
    "status": "pending",
    "applicationDate": "string"
  }
}
```

**Notes**: Based on form submission in RegistrasiPage.tsx lines 359-462.

## Payment Administration

### GET /api/payments
**Frontend Location**: AdministrasiPage.tsx (useEffect that loads payment items)
**Triggers**: Loading payment administration page for students
**Auth Required**: Yes
**Role Access**: All (students see their own payments)

Query Parameters:
- `status` (optional): Filter by payment status (paid, unpaid, pending)

Response:
```json
{
  "data": [
    {
      "id": "string",
      "titleKey": "string",
      "descriptionKey": "string",
      "amount": "number",
      "status": "paid | unpaid | pending",
      "dueDate": "string",
      "created_at": "string",
      "updated_at": "string"
    }
  ]
}
```

**Notes**: Based on payment items loading in AdministrasiPage.tsx lines 17-18 using PAYMENT_ITEMS_MOCK.

### GET /api/payments/history
**Frontend Location**: AdministrasiPage.tsx (useEffect that loads payment history)
**Triggers**: Loading payment administration page for students
**Auth Required**: Yes
**Role Access**: All (students see their own history)

Query Parameters:
- `page` (optional): Page number for pagination
- `limit` (optional): Number of records per page

Response:
```json
{
  "data": [
    {
      "id": "string",
      "title": "string",
      "amount": "number",
      "date": "string",
      "status": "completed | failed",
      "paymentMethod": "string",
      "created_at": "string"
    }
  ],
  "pagination": {
    "current_page": "number",
    "total_pages": "number",
    "total_records": "number",
    "per_page": "number"
  }
}
```

**Notes**: Based on payment history loading in AdministrasiPage.tsx lines 19-20 using PAYMENT_HISTORY_MOCK.

### POST /api/payments/{id}/process
**Frontend Location**: AdministrasiPage.tsx (handlePayment function)
**Triggers**: Student makes a payment
**Auth Required**: Yes
**Role Access**: Students

Request Body:
```json
{
  "paymentMethod": "bank_transfer | credit_card | e_wallet | virtual_account"
}
```

Response:
```json
{
  "message": "Payment processed successfully",
  "receipt": {
    "id": "string",
    "paymentItem": {
      "id": "string",
      "title": "string",
      "description": "string",
      "amount": "number"
    },
    "amount": "number",
    "date": "string",
    "method": "string",
    "studentName": "string",
    "studentId": "string",
    "title": "string"
  }
}
```

**Notes**: Based on payment processing in AdministrasiPage.tsx lines 21-72.

### GET /api/management/payments
**Frontend Location**: ManagementAdministrationPage.tsx (overview data)
**Triggers**: Loading payment management dashboard
**Auth Required**: Yes (Admin role)
**Role Access**: Super Admin, Manajemen Kampus

Response:
```json
{
  "stats": {
    "totalStudents": "number",
    "totalPayments": "number",
    "totalPaid": "number",
    "totalUnpaid": "number",
    "pendingPayments": "number"
  },
  "recentPayments": [
    {
      "id": "string",
      "student": {
        "id": "string",
        "name": "string"
      },
      "type": "string",
      "amount": "number",
      "date": "string",
      "status": "completed | pending | failed"
    }
  ],
  "paymentTypes": [
    {
      "id": "string",
      "title": "string",
      "total": "number",
      "paid": "number",
      "unpaid": "number"
    }
  ],
  "paymentMethods": [
    {
      "id": "string",
      "name": "string",
      "icon": "string",
      "count": "number"
    }
  ]
}
```

**Notes**: Based on ManagementAdministrationPage.tsx lines 29-50 where mock data is used for payment statistics.

### GET /api/management/payments/students
**Frontend Location**: ManagementAdministrationPage.tsx (payment management tab)
**Triggers**: Loading payment management page
**Auth Required**: Yes (Admin role)
**Role Access**: Super Admin, Manajemen Kampus

Query Parameters:
- `page` (optional): Page number for pagination
- `limit` (optional): Number of records per page
- `search` (optional): Search term for filtering students
- `status` (optional): Filter by payment status

Response:
```json
{
  "data": [
    {
      "id": "string",
      "name": "string",
      "totalAmount": "number",
      "latestTransaction": "string",
      "status": "paid | unpaid",
      "paymentList": [
        {
          "id": "string",
          "type": "string",
          "amount": "number",
          "status": "paid | unpaid",
          "date": "string"
        }
      ]
    }
  ],
  "pagination": {
    "current_page": "number",
    "total_pages": "number",
    "total_records": "number",
    "per_page": "number"
  }
}
```

**Notes**: Based on payment management functionality in ManagementAdministrationPage.tsx lines 223-240.

### GET /api/payment-types
**Frontend Location**: ManagementAdministrationPage.tsx (payment types tab)
**Triggers**: Loading payment types management page
**Auth Required**: Yes (Admin role)
**Role Access**: Super Admin, Manajemen Kampus

Response:
```json
{
  "data": [
    {
      "id": "string",
      "name": "string",
      "description": "string",
      "amount": "number"
    }
  ]
}
```

**Notes**: Based on payment types management in ManagementAdministrationPage.tsx lines 605-684.

### POST /api/payment-types
**Frontend Location**: ManagementAdministrationPage.tsx (add payment type functionality)
**Triggers**: Admin adds new payment type
**Auth Required**: Yes (Admin role)
**Role Access**: Super Admin, Manajemen Kampus

Request Body:
```json
{
  "name": "string",
  "description": "string",
  "amount": "number"
}
```

Response:
```json
{
  "paymentType": {
    "id": "string",
    "name": "string",
    "description": "string",
    "amount": "number"
  }
}
```

**Notes**: Based on add payment type functionality in ManagementAdministrationPage.tsx lines 606-618.

### PUT /api/payment-types/{id}
**Frontend Location**: ManagementAdministrationPage.tsx (edit payment type functionality)
**Triggers**: Admin updates payment type
**Auth Required**: Yes (Admin role)
**Role Access**: Super Admin, Manajemen Kampus

Request Body:
```json
{
  "name": "string",
  "description": "string",
  "amount": "number"
}
```

Response:
```json
{
  "paymentType": {
    "id": "string",
    "name": "string",
    "description": "string",
    "amount": "number"
  }
}
```

**Notes**: Based on edit payment type functionality in ManagementAdministrationPage.tsx lines 828-862.

### DELETE /api/payment-types/{id}
**Frontend Location**: ManagementAdministrationPage.tsx (delete payment type functionality)
**Triggers**: Admin deletes payment type
**Auth Required**: Yes (Admin role)
**Role Access**: Super Admin, Manajemen Kampus

Response:
```json
{
  "message": "Payment type deleted successfully"
}
```

**Notes**: Based on delete payment type functionality in ManagementAdministrationPage.tsx lines 620-622.

### PUT /api/management/payments/{id}/status
**Frontend Location**: ManagementAdministrationPage.tsx (payment status toggle functionality)
**Triggers**: Admin updates payment status (paid/unpaid)
**Auth Required**: Yes (Admin role)
**Role Access**: Super Admin, Manajemen Kampus

Request Body:
```json
{
  "status": "paid | unpaid"
}
```

Response:
```json
{
  "message": "Payment status updated successfully",
  "payment": {
    "id": "string",
    "status": "paid | unpaid"
  }
}
```

**Notes**: Based on payment status toggle functionality in ManagementAdministrationPage.tsx lines 416-452 where admin can change payment status in bulk.

## Dashboard Endpoints

### GET /api/dashboard/overview
**Frontend Location**: Dashboard.tsx (useEffect that loads dashboard data)
**Triggers**: Loading dashboard page
**Auth Required**: Yes
**Role Access**: All (with role-based data filtering)

Response:
```json
{
  "academicStats": {
    "coursesCompleted": "number",
    "totalSks": "number",
    "gpa": "string"
  },
  "announcements": [
    {
      "id": "string",
      "title": "string",
      "category": "string",
      "authorName": "string",
      "timestamp": "string"
    }
  ],
  "coursesInProgress": [
    {
      "id": "string",
      "title": "string",
      "instructor": "string",
      "progress": "number",
      "imageUrl": "string",
      "facultyId": "string"
    }
  ],
  "pastSemesterStats": {
    "courses": [
      {
        "id": "string",
        "title": "string",
        "gradeLetter": "string"
      }
    ],
    "coursesCompleted": "number",
    "totalSks": "number",
    "gpa": "string"
  },
  "earnedBadges": [
    {
      "id": "string",
      "icon": "React.ReactNode",
      "titleKey": "string",
      "descriptionKey": "string"
    }
  ],
  "ibadahData": [
    {
      "name": "string",
      "Tilawah": "number",
      "Hafalan": "number"
    }
  ]
}
```

**Notes**: Based on data loading in Dashboard.tsx lines 41-45 where mock data is used.

### GET /api/management/dashboard
**Frontend Location**: ManajemenDashboard.tsx (useEffect that loads management data)
**Triggers**: Loading management dashboard page
**Auth Required**: Yes (Admin role)
**Role Access**: Super Admin, Manajemen Kampus

Response:
```json
{
  "managementStats": {
    "totalStudents": "string",
    "totalLecturers": "string",
    "totalRegistrants": "string",
    "totalBudget": "string"
  },
  "facultyEnrollmentData": [
    {
      "name": "string",
      "mahasiswa": "number"
    }
  ],
  "campusActivities": [
    {
      "id": "string",
      "title": "string",
      "timestamp": "string",
      "type": "string"
    }
  ]
}
```

**Notes**: Based on data loading in ManajemenDashboard.tsx lines 37-69 where mock data is calculated from users and faculties.

### GET /api/prodi/dashboard
**Frontend Location**: ProdiDashboard.tsx (useEffect that loads prodi data)
**Triggers**: Loading prodi dashboard page
**Auth Required**: Yes (Prodi Admin role)
**Role Access**: Prodi Admin

Response:
```json
{
  "prodiStats": {
    "totalStudents": "number",
    "totalCourses": "number",
    "avgGPA": "string"
  },
  "enrollmentData": [
    {
      "name": "string",
      "mahasiswa": "number"
    }
  ],
  "gradRate": "string"
}
```

**Notes**: Based on data loading in ProdiDashboard.tsx lines 36-47 where mock data is calculated from users and courses.

### GET /api/prodi/students
**Frontend Location**: ProdiStudentsPage.tsx (useEffect that loads students)
**Triggers**: Loading prodi students page
**Auth Required**: Yes (Prodi Admin role)
**Role Access**: Prodi Admin

Query Parameters:
- `search` (optional): Search term for filtering students
- `status` (optional): Filter by student status

Response:
```json
{
  "data": [
    {
      "id": "string",
      "name": "string",
      "studentId": "string",
      "studentStatus": "Aktif | Cuti | Lulus | DO | Pendaftaran",
      "gpa": "number",
      "totalSks": "number",
      "avatarUrl": "string"
    }
  ]
}
```

**Notes**: Based on student filtering in ProdiStudentsPage.tsx lines 25-36 where students are filtered by role.

## Notifications

### GET /api/notifications
**Frontend Location**: Notifications.tsx (props passed from parent)
**Triggers**: Loading notifications page
**Auth Required**: Yes
**Role Access**: All (users see their own notifications)

Query Parameters:
- `page` (optional): Page number for pagination
- `limit` (optional): Number of notifications per page
- `readStatus` (optional): Filter by read status

Response:
```json
{
  "data": [
    {
      "id": "string",
      "type": "forum | grade | assignment | announcement",
      "messageKey": "string",
      "context": "string",
      "timestamp": "string",
      "isRead": "boolean",
      "link": {
        "page": "string",
        "params": "any"
      },
      "user_id": "string"
    }
  ],
  "pagination": {
    "current_page": "number",
    "total_pages": "number",
    "total_notifications": "number",
    "per_page": "number"
  }
}
```

**Notes**: Based on notification data structure in Notifications.tsx where notifications are displayed.

### PATCH /api/notifications/{id}/read
**Frontend Location**: Notifications.tsx (onMarkAsRead function)
**Triggers**: User marks notification as read
**Auth Required**: Yes
**Role Access**: All (users can only mark their own notifications as read)

Request Body: (empty)

Response:
```json
{
  "message": "Notification marked as read",
  "notification": {
    "id": "string",
    "isRead": "boolean"
  }
}
```

**Notes**: Based on onMarkAsRead function in Notifications.tsx lines 10 and 25-27.

## Settings

### PUT /api/users/{id}/preferences
**Frontend Location**: Settings.tsx (notification toggle functions)
**Triggers**: User updates notification preferences
**Auth Required**: Yes
**Role Access**: User can update own preferences

Request Body:
```json
{
  "notifications": {
    "course": "boolean",
    "assignments": "boolean",
    "forum": "boolean"
  }
}
```

Response:
```json
{
  "message": "Preferences updated successfully",
  "preferences": {
    "notifications": {
      "course": "boolean",
      "assignments": "boolean",
      "forum": "boolean"
    }
  }
}
```

**Notes**: Based on notification toggle functionality in Settings.tsx lines 65-68.

### PUT /api/users/{id}/password
**Frontend Location**: Settings.tsx (change password functionality)
**Triggers**: User changes password
**Auth Required**: Yes
**Role Access**: User can change own password

Request Body:
```json
{
  "current_password": "string",
  "new_password": "string",
  "new_password_confirmation": "string"
}
```

Response:
```json
{
  "message": "Password updated successfully"
}
```

**Notes**: Based on password change button in Settings.tsx lines 326-328.


## Analysis Complete
- [x] Login.tsx - analyzed, POST /api/auth/login documented
- [x] Register.tsx - analyzed, POST /api/auth/register documented
- [x] Profile.tsx - analyzed, GET/PUT /api/users/{id} documented
- [x] UserManagementPage.tsx - analyzed, GET/POST/PUT/DELETE /api/users endpoints documented
- [x] StudentRegistrationPage.tsx - analyzed, GET/PUT /api/student-registrations endpoints documented
- [x] RegistrasiPage.tsx - analyzed, POST /api/student-registrations documented
- [x] AdministrasiPage.tsx - analyzed, payment administration endpoints documented
- [x] ManagementAdministrationPage.tsx - analyzed, payment management endpoints documented
- [x] Dashboard.tsx - analyzed, GET /api/dashboard endpoints documented
- [x] ManajemenDashboard.tsx - analyzed, GET /api/management/dashboard documented
- [x] ProdiDashboard.tsx - analyzed, GET /api/prodi endpoints documented
- [x] ProdiStudentsPage.tsx - analyzed, GET /api/prodi/students documented
- [x] PrayerTimes.tsx - analyzed, GET /api/prayer-times endpoints documented
- [x] Notifications.tsx - analyzed, GET/PATCH /api/notifications endpoints documented
- [x] Settings.tsx - analyzed, various user settings endpoints documented
- [x] types.ts - analyzed, used to understand data structures
- [x] docs/api-spec-part1-auth-admin.md - created with all identified endpoints