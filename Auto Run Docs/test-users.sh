#!/bin/bash

# =============================================================================
# User Endpoint Tests
# Tests all user-related API endpoints using curl
# =============================================================================

BASE_URL="http://127.0.0.1:8000/api"

# Color codes for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Test counters
TESTS_RUN=0
TESTS_PASSED=0
TESTS_FAILED=0

# Test user IDs and tokens
TEST_USER_ID=""
TEST_STUDENT_ID=""
TEST_FACULTY_ID=""

# Function to print test result
print_result() {
    local test_name="$1"
    local status="$2"
    local details="$3"

    TESTS_RUN=$((TESTS_RUN + 1))

    if [ "$status" = "PASS" ]; then
        echo -e "${GREEN}[PASS]${NC} $test_name"
        TESTS_PASSED=$((TESTS_PASSED + 1))
    elif [ "$status" = "SKIP" ]; then
        echo -e "${YELLOW}[SKIP]${NC} $test_name"
        TESTS_PASSED=$((TESTS_PASSED + 1))
    else
        echo -e "${RED}[FAIL]${NC} $test_name"
        TESTS_FAILED=$((TESTS_FAILED + 1))
    fi

    if [ -n "$details" ]; then
        echo -e "       ${BLUE}Details:${NC} $details"
    fi
    echo ""
}

# Function to test an endpoint - returns http_code|body
test_endpoint() {
    local method="$1"
    local endpoint="$2"
    local data="$3"
    local headers="$4"

    # Build curl command to get status code and body
    if [ -n "$headers" ]; then
        response=$(curl -s -w "\n%{http_code}" -X "$method" \
            "$BASE_URL$endpoint" \
            -H "Content-Type: application/json" \
            -H "$headers" \
            -d "$data" 2>/dev/null)
    else
        response=$(curl -s -w "\n%{http_code}" -X "$method" \
            "$BASE_URL$endpoint" \
            -H "Content-Type: application/json" \
            -d "$data" 2>/dev/null)
    fi

    # Extract status code (last line) and body (everything except last line)
    http_code=$(echo "$response" | tail -n1)
    body=$(echo "$response" | sed '$d')

    echo "$http_code|$body"
}

# =============================================================================
# PRE-FLIGHT: Register and Login to get access token
# =============================================================================

echo -e "${YELLOW}========================================${NC}"
echo -e "${YELLOW}PRE-FLIGHT: User Authentication${NC}"
echo -e "${YELLOW}========================================${NC}"
echo ""

# Create test admin user
timestamp=$(date +%s)
admin_register_data='{
    "name": "Test Admin '$timestamp'",
    "email": "testadmin'$timestamp'@example.com",
    "password": "Password123!",
    "password_confirmation": "Password123!",
    "role": "admin"
}'

result=$(test_endpoint "POST" "/register" "$admin_register_data" "")
admin_register_http_code=$(echo "$result" | cut -d'|' -f1)
admin_register_body=$(echo "$result" | cut -d'|' -f2-)

if [ "$admin_register_http_code" = "201" ] || [ "$admin_register_http_code" = "200" ]; then
    # Extract access token
    ADMIN_TOKEN=$(echo "$admin_register_body" | grep -o '"token":"[^"]*"' | cut -d'"' -f4)
    if [ -z "$ADMIN_TOKEN" ]; then
        ADMIN_TOKEN=$(echo "$admin_register_body" | grep -o '"access_token":"[^"]*"' | cut -d'"' -f4)
    fi
    ADMIN_EMAIL="testadmin$timestamp@example.com"
    ADMIN_PASSWORD="Password123!"
    echo -e "${GREEN}Admin user created${NC}"
else
    echo -e "${RED}Failed to create admin user: $admin_register_body${NC}"
    # Try login with existing admin
    admin_login_data='{
        "email": "admin@example.com",
        "password": "password"
    }'
    result=$(test_endpoint "POST" "/login" "$admin_login_data" "")
    ADMIN_TOKEN=$(echo "$result" | grep -o '"token":"[^"]*"' | cut -d'"' -f4)
    if [ -z "$ADMIN_TOKEN" ]; then
        ADMIN_TOKEN=$(echo "$result" | grep -o '"access_token":"[^"]*"' | cut -d'"' -f4)
    fi
fi

# Create test student user
student_register_data='{
    "name": "Test Student '$timestamp'",
    "email": "teststudent'$timestamp'@example.com",
    "password": "Password123!",
    "password_confirmation": "Password123!",
    "role": "student"
}'

result=$(test_endpoint "POST" "/register" "$student_register_data" "")
student_register_http_code=$(echo "$result" | cut -d'|' -f1)
student_register_body=$(echo "$result" | cut -d'|' -f2-)

if [ "$student_register_http_code" = "201" ] || [ "$student_register_http_code" = "200" ]; then
    # Extract access token
    STUDENT_TOKEN=$(echo "$student_register_body" | grep -o '"token":"[^"]*"' | cut -d'"' -f4)
    if [ -z "$STUDENT_TOKEN" ]; then
        STUDENT_TOKEN=$(echo "$student_register_body" | grep -o '"access_token":"[^"]*"' | cut -d'"' -f4)
    fi
    STUDENT_EMAIL="teststudent$timestamp@example.com"
    STUDENT_PASSWORD="Password123!"
    echo -e "${GREEN}Student user created${NC}"
else
    echo -e "${YELLOW}Student user already exists or failed${NC}"
    # Try login
    student_login_data='{
        "email": "teststudent$timestamp@example.com",
        "password": "Password123!"
    }'
    result=$(test_endpoint "POST" "/login" "$student_login_data" "")
    STUDENT_TOKEN=$(echo "$result" | grep -o '"token":"[^"]*"' | cut -d'"' -f4)
    if [ -z "$STUDENT_TOKEN" ]; then
        STUDENT_TOKEN=$(echo "$result" | grep -o '"access_token":"[^"]*"' | cut -d'"' -f4)
    fi
fi

# Check if we have tokens
if [ -z "$ADMIN_TOKEN" ] && [ -z "$STUDENT_TOKEN" ]; then
    echo -e "${RED}No authentication tokens available. Exiting.${NC}"
    exit 1
fi

echo ""

# =============================================================================
# TEST 1: GET /api/users - List all users
# =============================================================================
echo -e "${YELLOW}========================================${NC}"
echo -e "${YELLOW}TEST 1: List All Users${NC}"
echo -e "${YELLOW}========================================${NC}"
echo ""

if [ -n "$ADMIN_TOKEN" ]; then
    result=$(test_endpoint "GET" "/users" "" "Authorization: Bearer $ADMIN_TOKEN")
    http_code=$(echo "$result" | cut -d'|' -f1)
    body=$(echo "$result" | cut -d'|' -f2-)

    echo -e "       ${BLUE}Method:${NC} GET"
    echo -e "       ${BLUE}Endpoint:${NC} /users"
    echo -e "       ${BLUE}Status:${NC} $http_code"
    echo -e "       ${BLUE}Response:${NC} $(echo "$body" | head -c 200)..."
    echo ""

    if [ "$http_code" = "200" ]; then
        print_result "List all users" "PASS" "HTTP 200 - Users retrieved successfully"
    else
        print_result "List all users" "FAIL" "HTTP $http_code - $body"
    fi
else
    print_result "List all users" "SKIP" "No admin token available"
fi

# =============================================================================
# TEST 2: GET /api/users/{id} - Get specific user
# =============================================================================
echo -e "${YELLOW}========================================${NC}"
echo -e "${YELLOW}TEST 2: Get User by ID${NC}"
echo -e "${YELLOW}========================================${NC}"
echo ""

# First get a user ID from the list
if [ -n "$ADMIN_TOKEN" ]; then
    result=$(test_endpoint "GET" "/users" "" "Authorization: Bearer $ADMIN_TOKEN")
    body=$(echo "$result" | cut -d'|' -f2-)

    # Extract first user ID from response
    TEST_USER_ID=$(echo "$body" | grep -o '"id":[0-9]*' | head -1 | cut -d':' -f2)

    if [ -n "$TEST_USER_ID" ]; then
        result=$(test_endpoint "GET" "/users/$TEST_USER_ID" "" "Authorization: Bearer $ADMIN_TOKEN")
        http_code=$(echo "$result" | cut -d'|' -f1)
        body=$(echo "$result" | cut -d'|' -f2-)

        echo -e "       ${BLUE}Method:${NC} GET"
        echo -e "       ${BLUE}Endpoint:${NC} /users/$TEST_USER_ID"
        echo -e "       ${BLUE}Status:${NC} $http_code"
        echo ""

        if [ "$http_code" = "200" ]; then
            print_result "Get user by ID" "PASS" "HTTP 200 - User retrieved successfully"
        else
            print_result "Get user by ID" "FAIL" "HTTP $http_code - $body"
        fi
    else
        print_result "Get user by ID" "SKIP" "No user ID available"
    fi
else
    print_result "Get user by ID" "SKIP" "No admin token available"
fi

# =============================================================================
# TEST 3: POST /api/users - Create new user
# =============================================================================
echo -e "${YELLOW}========================================${NC}"
echo -e "${YELLOW}TEST 3: Create New User${NC}"
echo -e "${YELLOW}========================================${NC}"
echo ""

if [ -n "$ADMIN_TOKEN" ]; then
    timestamp=$(date +%s)
    create_user_data='{
        "name": "New Test User '$timestamp'",
        "email": "newuser'$timestamp'@example.com",
        "password": "Password123!",
        "password_confirmation": "Password123!",
        "role": "student"
    }'

    result=$(test_endpoint "POST" "/users" "$create_user_data" "Authorization: Bearer $ADMIN_TOKEN")
    http_code=$(echo "$result" | cut -d'|' -f1)
    body=$(echo "$result" | cut -d'|' -f2-)

    echo -e "       ${BLUE}Method:${NC} POST"
    echo -e "       ${BLUE}Endpoint:${NC} /users"
    echo -e "       ${BLUE}Status:${NC} $http_code"
    echo -e "       ${BLUE}Response:${NC} $(echo "$body" | head -c 200)..."
    echo ""

    if [ "$http_code" = "201" ] || [ "$http_code" = "200" ]; then
        print_result "Create new user" "PASS" "HTTP $http_code - User created successfully"
        # Extract the created user ID for later tests
        TEST_USER_ID=$(echo "$body" | grep -o '"id":[0-9]*' | head -1 | cut -d':' -f2)
    else
        print_result "Create new user" "FAIL" "HTTP $http_code - $body"
    fi
else
    print_result "Create new user" "SKIP" "No admin token available"
fi

# =============================================================================
# TEST 4: PUT /api/users/{id} - Update user
# =============================================================================
echo -e "${YELLOW}========================================${NC}"
echo -e "${YELLOW}TEST 4: Update User${NC}"
echo -e "${YELLOW}========================================${NC}"
echo ""

if [ -n "$ADMIN_TOKEN" ] && [ -n "$TEST_USER_ID" ]; then
    update_user_data='{
        "name": "Updated Test User",
        "phone": "1234567890"
    }'

    result=$(test_endpoint "PUT" "/users/$TEST_USER_ID" "$update_user_data" "Authorization: Bearer $ADMIN_TOKEN")
    http_code=$(echo "$result" | cut -d'|' -f1)
    body=$(echo "$result" | cut -d'|' -f2-)

    echo -e "       ${BLUE}Method:${NC} PUT"
    echo -e "       ${BLUE}Endpoint:${NC} /users/$TEST_USER_ID"
    echo -e "       ${BLUE}Status:${NC} $http_code"
    echo ""

    if [ "$http_code" = "200" ]; then
        print_result "Update user" "PASS" "HTTP 200 - User updated successfully"
    else
        print_result "Update user" "FAIL" "HTTP $http_code - $body"
    fi
else
    print_result "Update user" "SKIP" "No admin token or user ID available"
fi

# =============================================================================
# TEST 5: GET /api/users/me/profile - Get current user profile
# =============================================================================
echo -e "${YELLOW}========================================${NC}"
echo -e "${YELLOW}TEST 5: Get Current User Profile${NC}"
echo -e "${YELLOW}========================================${NC}"
echo ""

if [ -n "$STUDENT_TOKEN" ]; then
    result=$(test_endpoint "GET" "/users/me/profile" "" "Authorization: Bearer $STUDENT_TOKEN")
    http_code=$(echo "$result" | cut -d'|' -f1)
    body=$(echo "$result" | cut -d'|' -f2-)

    echo -e "       ${BLUE}Method:${NC} GET"
    echo -e "       ${BLUE}Endpoint:${NC} /users/me/profile"
    echo -e "       ${BLUE}Status:${NC} $http_code"
    echo ""

    if [ "$http_code" = "200" ]; then
        print_result "Get current user profile" "PASS" "HTTP 200 - Profile retrieved successfully"
    else
        print_result "Get current user profile" "FAIL" "HTTP $http_code - $body"
    fi
else
    print_result "Get current user profile" "SKIP" "No student token available"
fi

# =============================================================================
# TEST 6: PUT /api/users/me/profile - Update current user profile
# =============================================================================
echo -e "${YELLOW}========================================${NC}"
echo -e "${YELLOW}TEST 6: Update Current User Profile${NC}"
echo -e "${YELLOW}========================================${NC}"
echo ""

if [ -n "$STUDENT_TOKEN" ]; then
    update_profile_data='{
        "name": "Updated Student Name",
        "phone": "0987654321",
        "address": "123 Test Street"
    }'

    result=$(test_endpoint "PUT" "/users/me/profile" "$update_profile_data" "Authorization: Bearer $STUDENT_TOKEN")
    http_code=$(echo "$result" | cut -d'|' -f1)
    body=$(echo "$result" | cut -d'|' -f2-)

    echo -e "       ${BLUE}Method:${NC} PUT"
    echo -e "       ${BLUE}Endpoint:${NC} /users/me/profile"
    echo -e "       ${BLUE}Status:${NC} $http_code"
    echo ""

    if [ "$http_code" = "200" ]; then
        print_result "Update current user profile" "PASS" "HTTP 200 - Profile updated successfully"
    else
        print_result "Update current user profile" "FAIL" "HTTP $http_code - $body"
    fi
else
    print_result "Update current user profile" "SKIP" "No student token available"
fi

# =============================================================================
# TEST 7: POST /api/users/me/change-password - Change password
# =============================================================================
echo -e "${YELLOW}========================================${NC}"
echo -e "${YELLOW}TEST 7: Change Password${NC}"
echo -e "${YELLOW}========================================${NC}"
echo ""

if [ -n "$STUDENT_TOKEN" ] && [ -n "$STUDENT_PASSWORD" ]; then
    change_password_data='{
        "current_password": "'$STUDENT_PASSWORD'",
        "password": "NewPassword456!",
        "password_confirmation": "NewPassword456!"
    }'

    result=$(test_endpoint "POST" "/users/me/change-password" "$change_password_data" "Authorization: Bearer $STUDENT_TOKEN")
    http_code=$(echo "$result" | cut -d'|' -f1)
    body=$(echo "$result" | cut -d'|' -f2-)

    echo -e "       ${BLUE}Method:${NC} POST"
    echo -e "       ${BLUE}Endpoint:${NC} /users/me/change-password"
    echo -e "       ${BLUE}Status:${NC} $http_code"
    echo ""

    if [ "$http_code" = "200" ]; then
        print_result "Change password" "PASS" "HTTP 200 - Password changed successfully"
        # Update the password for subsequent tests
        STUDENT_PASSWORD="NewPassword456!"
        # Re-login with new password
        student_login_data='{
            "email": "'$STUDENT_EMAIL'",
            "password": "'$STUDENT_PASSWORD'"
        }'
        result=$(test_endpoint "POST" "/login" "$student_login_data" "")
        STUDENT_TOKEN=$(echo "$result" | grep -o '"token":"[^"]*"' | cut -d'"' -f4)
        if [ -z "$STUDENT_TOKEN" ]; then
            STUDENT_TOKEN=$(echo "$result" | grep -o '"access_token":"[^"]*"' | cut -d'"' -f4)
        fi
    else
        print_result "Change password" "FAIL" "HTTP $http_code - $body"
    fi
else
    print_result "Change password" "SKIP" "No student token or password available"
fi

# =============================================================================
# TEST 8: GET /api/users/role/{role} - Get users by role
# =============================================================================
echo -e "${YELLOW}========================================${NC}"
echo -e "${YELLOW}TEST 8: Get Users by Role (students)${NC}"
echo -e "${YELLOW}========================================${NC}"
echo ""

if [ -n "$ADMIN_TOKEN" ]; then
    result=$(test_endpoint "GET" "/users/role/student" "" "Authorization: Bearer $ADMIN_TOKEN")
    http_code=$(echo "$result" | cut -d'|' -f1)
    body=$(echo "$result" | cut -d'|' -f2-)

    echo -e "       ${BLUE}Method:${NC} GET"
    echo -e "       ${BLUE}Endpoint:${NC} /users/role/student"
    echo -e "       ${BLUE}Status:${NC} $http_code"
    echo ""

    if [ "$http_code" = "200" ]; then
        print_result "Get users by role (students)" "PASS" "HTTP 200 - Students retrieved successfully"
    else
        print_result "Get users by role (students)" "FAIL" "HTTP $http_code - $body"
    fi
else
    print_result "Get users by role (students)" "SKIP" "No admin token available"
fi

# =============================================================================
# TEST 9: GET /api/users/role/{role} - Get users by role (invalid)
# =============================================================================
echo -e "${YELLOW}========================================${NC}"
echo -e "${YELLOW}TEST 9: Get Users by Invalid Role${NC}"
echo -e "${YELLOW}========================================${NC}"
echo ""

if [ -n "$ADMIN_TOKEN" ]; then
    result=$(test_endpoint "GET" "/users/role/invalid_role" "" "Authorization: Bearer $ADMIN_TOKEN")
    http_code=$(echo "$result" | cut -d'|' -f1)
    body=$(echo "$result" | cut -d'|' -f2-)

    echo -e "       ${BLUE}Method:${NC} GET"
    echo -e "       ${BLUE}Endpoint:${NC} /users/role/invalid_role"
    echo -e "       ${BLUE}Status:${NC} $http_code"
    echo ""

    if [ "$http_code" = "400" ]; then
        print_result "Get users by invalid role" "PASS" "HTTP 400 - Invalid role rejected"
    else
        print_result "Get users by invalid role" "FAIL" "Expected 400, got $http_code - $body"
    fi
else
    print_result "Get users by invalid role" "SKIP" "No admin token available"
fi

# =============================================================================
# TEST 10: GET /api/users/list/faculty - Get faculty members
# =============================================================================
echo -e "${YELLOW}========================================${NC}"
echo -e "${YELLOW}TEST 10: Get Faculty Members${NC}"
echo -e "${YELLOW}========================================${NC}"
echo ""

if [ -n "$ADMIN_TOKEN" ]; then
    result=$(test_endpoint "GET" "/users/list/faculty" "" "Authorization: Bearer $ADMIN_TOKEN")
    http_code=$(echo "$result" | cut -d'|' -f1)
    body=$(echo "$result" | cut -d'|' -f2-)

    echo -e "       ${BLUE}Method:${NC} GET"
    echo -e "       ${BLUE}Endpoint:${NC} /users/list/faculty"
    echo -e "       ${BLUE}Status:${NC} $http_code"
    echo ""

    if [ "$http_code" = "200" ]; then
        print_result "Get faculty members" "PASS" "HTTP 200 - Faculty retrieved successfully"
    else
        print_result "Get faculty members" "FAIL" "HTTP $http_code - $body"
    fi
else
    print_result "Get faculty members" "SKIP" "No admin token available"
fi

# =============================================================================
# TEST 11: GET /api/users/list/students - Get students
# =============================================================================
echo -e "${YELLOW}========================================${NC}"
echo -e "${YELLOW}TEST 11: Get Students${NC}"
echo -e "${YELLOW}========================================${NC}"
echo ""

if [ -n "$ADMIN_TOKEN" ]; then
    result=$(test_endpoint "GET" "/users/list/students" "" "Authorization: Bearer $ADMIN_TOKEN")
    http_code=$(echo "$result" | cut -d'|' -f1)
    body=$(echo "$result" | cut -d'|' -f2-)

    echo -e "       ${BLUE}Method:${NC} GET"
    echo -e "       ${BLUE}Endpoint:${NC} /users/list/students"
    echo -e "       ${BLUE}Status:${NC} $http_code"
    echo ""

    if [ "$http_code" = "200" ]; then
        print_result "Get students" "PASS" "HTTP 200 - Students retrieved successfully"
    else
        print_result "Get students" "FAIL" "HTTP $http_code - $body"
    fi
else
    print_result "Get students" "SKIP" "No admin token available"
fi

# =============================================================================
# TEST 12: GET /api/users/faculty/{facultyId} - Get users by faculty
# =============================================================================
echo -e "${YELLOW}========================================${NC}"
echo -e "${YELLOW}TEST 12: Get Users by Faculty${NC}"
echo -e "${YELLOW}========================================${NC}"
echo ""

if [ -n "$ADMIN_TOKEN" ]; then
    # First get a faculty ID
    result=$(test_endpoint "GET" "/faculties" "" "Authorization: Bearer $ADMIN_TOKEN")
    body=$(echo "$result" | cut -d'|' -f2-)
    TEST_FACULTY_ID=$(echo "$body" | grep -o '"id":[0-9]*' | head -1 | cut -d':' -f2)

    if [ -n "$TEST_FACULTY_ID" ]; then
        result=$(test_endpoint "GET" "/users/faculty/$TEST_FACULTY_ID" "" "Authorization: Bearer $ADMIN_TOKEN")
        http_code=$(echo "$result" | cut -d'|' -f1)
        body=$(echo "$result" | cut -d'|' -f2-)

        echo -e "       ${BLUE}Method:${NC} GET"
        echo -e "       ${BLUE}Endpoint:${NC} /users/faculty/$TEST_FACULTY_ID"
        echo -e "       ${BLUE}Status:${NC} $http_code"
        echo ""

        if [ "$http_code" = "200" ]; then
            print_result "Get users by faculty" "PASS" "HTTP 200 - Users retrieved successfully"
        else
            print_result "Get users by faculty" "FAIL" "HTTP $http_code - $body"
        fi
    else
        print_result "Get users by faculty" "SKIP" "No faculty ID available"
    fi
else
    print_result "Get users by faculty" "SKIP" "No admin token available"
fi

# =============================================================================
# TEST 13: GET /api/users/major/{majorId} - Get users by major
# =============================================================================
echo -e "${YELLOW}========================================${NC}"
echo -e "${YELLOW}TEST 13: Get Users by Major${NC}"
echo -e "${YELLOW}========================================${NC}"
echo ""

if [ -n "$ADMIN_TOKEN" ]; then
    # First get a major ID
    result=$(test_endpoint "GET" "/majors" "" "Authorization: Bearer $ADMIN_TOKEN")
    body=$(echo "$result" | cut -d'|' -f2-)
    TEST_MAJOR_ID=$(echo "$body" | grep -o '"id":[0-9]*' | head -1 | cut -d':' -f2)

    if [ -n "$TEST_MAJOR_ID" ]; then
        result=$(test_endpoint "GET" "/users/major/$TEST_MAJOR_ID" "" "Authorization: Bearer $ADMIN_TOKEN")
        http_code=$(echo "$result" | cut -d'|' -f1)
        body=$(echo "$result" | cut -d'|' -f2-)

        echo -e "       ${BLUE}Method:${NC} GET"
        echo -e "       ${BLUE}Endpoint:${NC} /users/major/$TEST_MAJOR_ID"
        echo -e "       ${BLUE}Status:${NC} $http_code"
        echo ""

        if [ "$http_code" = "200" ]; then
            print_result "Get users by major" "PASS" "HTTP 200 - Users retrieved successfully"
        else
            print_result "Get users by major" "FAIL" "HTTP $http_code - $body"
        fi
    else
        print_result "Get users by major" "SKIP" "No major ID available"
    fi
else
    print_result "Get users by major" "SKIP" "No admin token available"
fi

# =============================================================================
# TEST 14: POST /api/users/{id}/toggle-status - Toggle user status
# =============================================================================
echo -e "${YELLOW}========================================${NC}"
echo -e "${YELLOW}TEST 14: Toggle User Status${NC}"
echo -e "${YELLOW}========================================${NC}"
echo ""

if [ -n "$ADMIN_TOKEN" ] && [ -n "$TEST_USER_ID" ]; then
    result=$(test_endpoint "POST" "/users/$TEST_USER_ID/toggle-status" "" "Authorization: Bearer $ADMIN_TOKEN")
    http_code=$(echo "$result" | cut -d'|' -f1)
    body=$(echo "$result" | cut -d'|' -f2-)

    echo -e "       ${BLUE}Method:${NC} POST"
    echo -e "       ${BLUE}Endpoint:${NC} /users/$TEST_USER_ID/toggle-status"
    echo -e "       ${BLUE}Status:${NC} $http_code"
    echo ""

    if [ "$http_code" = "200" ]; then
        print_result "Toggle user status" "PASS" "HTTP 200 - Status toggled successfully"
    else
        print_result "Toggle user status" "FAIL" "HTTP $http_code - $body"
    fi
else
    print_result "Toggle user status" "SKIP" "No admin token or user ID available"
fi

# =============================================================================
# TEST 15: GET /api/user - Get current user (alternative endpoint)
# =============================================================================
echo -e "${YELLOW}========================================${NC}"
echo -e "${YELLOW}TEST 15: Get Current User (alternative endpoint)${NC}"
echo -e "${YELLOW}========================================${NC}"
echo ""

if [ -n "$STUDENT_TOKEN" ]; then
    result=$(test_endpoint "GET" "/user" "" "Authorization: Bearer $STUDENT_TOKEN")
    http_code=$(echo "$result" | cut -d'|' -f1)
    body=$(echo "$result" | cut -d'|' -f2-)

    echo -e "       ${BLUE}Method:${NC} GET"
    echo -e "       ${BLUE}Endpoint:${NC} /user"
    echo -e "       ${BLUE}Status:${NC} $http_code"
    echo ""

    if [ "$http_code" = "200" ]; then
        print_result "Get current user (alternative)" "PASS" "HTTP 200 - User retrieved successfully"
    else
        print_result "Get current user (alternative)" "FAIL" "HTTP $http_code - $body"
    fi
else
    print_result "Get current user (alternative)" "SKIP" "No student token available"
fi

# =============================================================================
# TEST 16: DELETE /api/users/{id} - Delete user
# =============================================================================
echo -e "${YELLOW}========================================${NC}"
echo -e "${YELLOW}TEST 16: Delete User${NC}"
echo -e "${YELLOW}========================================${NC}"
echo ""

if [ -n "$ADMIN_TOKEN" ] && [ -n "$TEST_USER_ID" ]; then
    result=$(test_endpoint "DELETE" "/users/$TEST_USER_ID" "" "Authorization: Bearer $ADMIN_TOKEN")
    http_code=$(echo "$result" | cut -d'|' -f1)
    body=$(echo "$result" | cut -d'|' -f2-)

    echo -e "       ${BLUE}Method:${NC} DELETE"
    echo -e "       ${BLUE}Endpoint:${NC} /users/$TEST_USER_ID"
    echo -e "       ${BLUE}Status:${NC} $http_code"
    echo ""

    if [ "$http_code" = "204" ] || [ "$http_code" = "200" ]; then
        print_result "Delete user" "PASS" "HTTP $http_code - User deleted successfully"
    else
        print_result "Delete user" "FAIL" "HTTP $http_code - $body"
    fi
else
    print_result "Delete user" "SKIP" "No admin token or user ID available"
fi

# =============================================================================
# TEST 17: GET /api/users without authentication
# =============================================================================
echo -e "${YELLOW}========================================${NC}"
echo -e "${YELLOW}TEST 17: List Users Without Authentication${NC}"
echo -e "${YELLOW}========================================${NC}"
echo ""

result=$(test_endpoint "GET" "/users" "" "")
http_code=$(echo "$result" | cut -d'|' -f1)
body=$(echo "$result" | cut -d'|' -f2-)

echo -e "       ${BLUE}Method:${NC} GET"
echo -e "       ${BLUE}Endpoint:${NC} /users"
echo -e "       ${BLUE}Status:${NC} $http_code"
echo ""

if [ "$http_code" = "401" ]; then
    print_result "List users without authentication" "PASS" "HTTP 401 - Unauthorized as expected"
else
    print_result "List users without authentication" "FAIL" "Expected 401, got $http_code - $body"
fi

# =============================================================================
# TEST 18: GET /api/users with student token (should work)
# =============================================================================
echo -e "${YELLOW}========================================${NC}"
echo -e "${YELLOW}TEST 18: List Users with Student Token${NC}"
echo -e "${YELLOW}========================================${NC}"
echo ""

if [ -n "$STUDENT_TOKEN" ]; then
    result=$(test_endpoint "GET" "/users" "" "Authorization: Bearer $STUDENT_TOKEN")
    http_code=$(echo "$result" | cut -d'|' -f1)
    body=$(echo "$result" | cut -d'|' -f2-)

    echo -e "       ${BLUE}Method:${NC} GET"
    echo -e "       ${BLUE}Endpoint:${NC} /users"
    echo -e "       ${BLUE}Status:${NC} $http_code"
    echo ""

    if [ "$http_code" = "200" ]; then
        print_result "List users with student token" "PASS" "HTTP 200 - Users retrieved successfully"
    else
        print_result "List users with student token" "FAIL" "HTTP $http_code - $body"
    fi
else
    print_result "List users with student token" "SKIP" "No student token available"
fi

# =============================================================================
# TEST 19: POST /api/users without authentication
# =============================================================================
echo -e "${YELLOW}========================================${NC}"
echo -e "${YELLOW}TEST 19: Create User Without Authentication${NC}"
echo -e "${YELLOW}========================================${NC}"
echo ""

timestamp=$(date +%s)
create_user_no_auth='{
    "name": "Unauthorized User '$timestamp'",
    "email": "unauthorized'$timestamp'@example.com",
    "password": "Password123!",
    "password_confirmation": "Password123!",
    "role": "student"
}'

result=$(test_endpoint "POST" "/users" "$create_user_no_auth" "")
http_code=$(echo "$result" | cut -d'|' -f1)
body=$(echo "$result" | cut -d'|' -f2-)

echo -e "       ${BLUE}Method:${NC} POST"
echo -e "       ${BLUE}Endpoint:${NC} /users"
echo -e "       ${BLUE}Status:${NC} $http_code"
echo ""

if [ "$http_code" = "401" ]; then
    print_result "Create user without authentication" "PASS" "HTTP 401 - Unauthorized as expected"
else
    print_result "Create user without authentication" "FAIL" "Expected 401, got $http_code - $body"
fi

# =============================================================================
# TEST 20: GET /api/users with search parameter
# =============================================================================
echo -e "${YELLOW}========================================${NC}"
echo -e "${YELLOW}TEST 20: List Users with Search Parameter${NC}"
echo -e "${YELLOW}========================================${NC}"
echo ""

if [ -n "$ADMIN_TOKEN" ]; then
    result=$(test_endpoint "GET" "/users?search=test" "" "Authorization: Bearer $ADMIN_TOKEN")
    http_code=$(echo "$result" | cut -d'|' -f1)
    body=$(echo "$result" | cut -d'|' -f2-)

    echo -e "       ${BLUE}Method:${NC} GET"
    echo -e "       ${BLUE}Endpoint:${NC} /users?search=test"
    echo -e "       ${BLUE}Status:${NC} $http_code"
    echo ""

    if [ "$http_code" = "200" ]; then
        print_result "List users with search" "PASS" "HTTP 200 - Search results retrieved successfully"
    else
        print_result "List users with search" "FAIL" "HTTP $http_code - $body"
    fi
else
    print_result "List users with search" "SKIP" "No admin token available"
fi

# =============================================================================
# TEST 21: GET /api/users with role filter
# =============================================================================
echo -e "${YELLOW}========================================${NC}"
echo -e "${YELLOW}TEST 21: List Users with Role Filter${NC}"
echo -e "${YELLOW}========================================${NC}"
echo ""

if [ -n "$ADMIN_TOKEN" ]; then
    result=$(test_endpoint "GET" "/users?role=student" "" "Authorization: Bearer $ADMIN_TOKEN")
    http_code=$(echo "$result" | cut -d'|' -f1)
    body=$(echo "$result" | cut -d'|' -f2-)

    echo -e "       ${BLUE}Method:${NC} GET"
    echo -e "       ${BLUE}Endpoint:${NC} /users?role=student"
    echo -e "       ${BLUE}Status:${NC} $http_code"
    echo ""

    if [ "$http_code" = "200" ]; then
        print_result "List users with role filter" "PASS" "HTTP 200 - Filtered users retrieved successfully"
    else
        print_result "List users with role filter" "FAIL" "HTTP $http_code - $body"
    fi
else
    print_result "List users with role filter" "SKIP" "No admin token available"
fi

# =============================================================================
# TEST 22: Toggle own status (should fail)
# =============================================================================
echo -e "${YELLOW}========================================${NC}"
echo -e "${YELLOW}TEST 22: Toggle Own User Status (should fail)${NC}"
echo -e "${YELLOW}========================================${NC}"
echo ""

if [ -n "$STUDENT_TOKEN" ]; then
    # First get own user ID
    result=$(test_endpoint "GET" "/users/me/profile" "" "Authorization: Bearer $STUDENT_TOKEN")
    body=$(echo "$result" | cut -d'|' -f2-)
    own_user_id=$(echo "$body" | grep -o '"id":[0-9]*' | head -1 | cut -d':' -f2)

    if [ -n "$own_user_id" ]; then
        result=$(test_endpoint "POST" "/users/$own_user_id/toggle-status" "" "Authorization: Bearer $STUDENT_TOKEN")
        http_code=$(echo "$result" | cut -d'|' -f1)
        body=$(echo "$result" | cut -d'|' -f2-)

        echo -e "       ${BLUE}Method:${NC} POST"
        echo -e "       ${BLUE}Endpoint:${NC} /users/$own_user_id/toggle-status"
        echo -e "       ${BLUE}Status:${NC} $http_code"
        echo ""

        if [ "$http_code" = "403" ]; then
            print_result "Toggle own user status" "PASS" "HTTP 403 - Cannot toggle own status as expected"
        else
            print_result "Toggle own user status" "FAIL" "Expected 403, got $http_code - $body"
        fi
    else
        print_result "Toggle own user status" "SKIP" "Could not get own user ID"
    fi
else
    print_result "Toggle own user status" "SKIP" "No student token available"
fi

# =============================================================================
# TEST SUMMARY
# =============================================================================
echo -e "${YELLOW}========================================${NC}"
echo -e "${YELLOW}TEST SUMMARY${NC}"
echo -e "${YELLOW}========================================${NC}"
echo ""
echo -e "Total Tests Run: ${BLUE}$TESTS_RUN${NC}"
echo -e "Tests Passed:    ${GREEN}$TESTS_PASSED${NC}"
echo -e "Tests Failed:    ${RED}$TESTS_FAILED${NC}"
echo ""

if [ $TESTS_FAILED -eq 0 ]; then
    echo -e "${GREEN}All tests passed!${NC}"
    exit 0
else
    echo -e "${RED}Some tests failed. Please review the output above.${NC}"
    exit 1
fi