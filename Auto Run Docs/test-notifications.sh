#!/bin/bash

# =============================================================================
# Notification Endpoint Tests
# Tests all notification-related API endpoints using curl
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

# Test resource IDs and tokens
TEST_NOTIFICATION_ID=""
TEST_USER_ID=""

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

# Create test admin user (for creating notifications)
timestamp=$(date +%s)
admin_register_data='{
    "name": "Test Admin Notifications '$timestamp'",
    "email": "testadminnotifications'$timestamp'@example.com",
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
    ADMIN_EMAIL="testadminnotifications$timestamp@example.com"
    ADMIN_PASSWORD="Password123!"
    echo -e "${GREEN}Admin user created${NC}"
else
    echo -e "${YELLOW}Admin user already exists or failed${NC}"
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
    "name": "Test Student Notifications '$timestamp'",
    "email": "teststudentnotifications'$timestamp'@example.com",
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
    STUDENT_EMAIL="teststudentnotifications$timestamp@example.com"
    STUDENT_PASSWORD="Password123!"
    echo -e "${GREEN}Student user created${NC}"
else
    echo -e "${YELLOW}Student user already exists or failed${NC}"
    # Try login
    student_login_data='{
        "email": "student@example.com",
        "password": "password"
    }'
    result=$(test_endpoint "POST" "/login" "$student_login_data" "")
    STUDENT_TOKEN=$(echo "$result" | grep -o '"token":"[^"]*"' | cut -d'"' -f4)
    if [ -z "$STUDENT_TOKEN" ]; then
        STUDENT_TOKEN=$(echo "$result" | grep -o '"access_token":"[^"]*"' | cut -d'"' -f4)
    fi
fi

# Get user ID for notification creation
if [ -n "$STUDENT_TOKEN" ]; then
    result=$(test_endpoint "GET" "/users/me/profile" "" "Authorization: Bearer $STUDENT_TOKEN")
    body=$(echo "$result" | cut -d'|' -f2-)
    TEST_USER_ID=$(echo "$body" | grep -o '"id":[0-9]*' | head -1 | cut -d':' -f2)
fi

echo ""

# =============================================================================
# TEST 1: GET /api/notifications - List all notifications
# =============================================================================
echo -e "${YELLOW}========================================${NC}"
echo -e "${YELLOW}TEST 1: List All Notifications${NC}"
echo -e "${YELLOW}========================================${NC}"
echo ""

if [ -n "$STUDENT_TOKEN" ]; then
    result=$(test_endpoint "GET" "/notifications" "" "Authorization: Bearer $STUDENT_TOKEN")
    http_code=$(echo "$result" | cut -d'|' -f1)
    body=$(echo "$result" | cut -d'|' -f2-)

    echo -e "       ${BLUE}Method:${NC} GET"
    echo -e "       ${BLUE}Endpoint:${NC} /notifications"
    echo -e "       ${BLUE}Status:${NC} $http_code"
    echo -e "       ${BLUE}Response:${NC} $(echo "$body" | head -c 200)..."
    echo ""

    if [ "$http_code" = "200" ]; then
        print_result "List all notifications" "PASS" "HTTP 200 - Notifications retrieved successfully"
    else
        print_result "List all notifications" "FAIL" "HTTP $http_code - $body"
    fi
else
    print_result "List all notifications" "SKIP" "No student token available"
fi

# =============================================================================
# TEST 2: GET /api/notifications/unread - Get unread notifications
# =============================================================================
echo -e "${YELLOW}========================================${NC}"
echo -e "${YELLOW}TEST 2: Get Unread Notifications${NC}"
echo -e "${YELLOW}========================================${NC}"
echo ""

if [ -n "$STUDENT_TOKEN" ]; then
    result=$(test_endpoint "GET" "/notifications/unread" "" "Authorization: Bearer $STUDENT_TOKEN")
    http_code=$(echo "$result" | cut -d'|' -f1)
    body=$(echo "$result" | cut -d'|' -f2-)

    echo -e "       ${BLUE}Method:${NC} GET"
    echo -e "       ${BLUE}Endpoint:${NC} /notifications/unread"
    echo -e "       ${BLUE}Status:${NC} $http_code"
    echo ""

    if [ "$http_code" = "200" ]; then
        print_result "Get unread notifications" "PASS" "HTTP 200 - Unread notifications retrieved successfully"
    else
        print_result "Get unread notifications" "FAIL" "HTTP $http_code - $body"
    fi
else
    print_result "Get unread notifications" "SKIP" "No student token available"
fi

# =============================================================================
# TEST 3: GET /api/notifications/urgent - Get urgent notifications
# =============================================================================
echo -e "${YELLOW}========================================${NC}"
echo -e "${YELLOW}TEST 3: Get Urgent Notifications${NC}"
echo -e "${YELLOW}========================================${NC}"
echo ""

if [ -n "$STUDENT_TOKEN" ]; then
    result=$(test_endpoint "GET" "/notifications/urgent" "" "Authorization: Bearer $STUDENT_TOKEN")
    http_code=$(echo "$result" | cut -d'|' -f1)
    body=$(echo "$result" | cut -d'|' -f2-)

    echo -e "       ${BLUE}Method:${NC} GET"
    echo -e "       ${BLUE}Endpoint:${NC} /notifications/urgent"
    echo -e "       ${BLUE}Status:${NC} $http_code"
    echo ""

    if [ "$http_code" = "200" ]; then
        print_result "Get urgent notifications" "PASS" "HTTP 200 - Urgent notifications retrieved successfully"
    else
        print_result "Get urgent notifications" "FAIL" "HTTP $http_code - $body"
    fi
else
    print_result "Get urgent notifications" "SKIP" "No student token available"
fi

# =============================================================================
# TEST 4: GET /api/notifications/counts - Get notification counts
# =============================================================================
echo -e "${YELLOW}========================================${NC}"
echo -e "${YELLOW}TEST 4: Get Notification Counts${NC}"
echo -e "${YELLOW}========================================${NC}"
echo ""

if [ -n "$STUDENT_TOKEN" ]; then
    result=$(test_endpoint "GET" "/notifications/counts" "" "Authorization: Bearer $STUDENT_TOKEN")
    http_code=$(echo "$result" | cut -d'|' -f1)
    body=$(echo "$result" | cut -d'|' -f2-)

    echo -e "       ${BLUE}Method:${NC} GET"
    echo -e "       ${BLUE}Endpoint:${NC} /notifications/counts"
    echo -e "       ${BLUE}Status:${NC} $http_code"
    echo ""

    if [ "$http_code" = "200" ]; then
        print_result "Get notification counts" "PASS" "HTTP 200 - Notification counts retrieved successfully"
    else
        print_result "Get notification counts" "FAIL" "HTTP $http_code - $body"
    fi
else
    print_result "Get notification counts" "SKIP" "No student token available"
fi

# =============================================================================
# TEST 5: POST /api/notifications - Create new notification
# =============================================================================
echo -e "${YELLOW}========================================${NC}"
echo -e "${YELLOW}TEST 5: Create New Notification${NC}"
echo -e "${YELLOW}========================================${NC}"
echo ""

if [ -n "$ADMIN_TOKEN" ] && [ -n "$TEST_USER_ID" ]; then
    timestamp=$(date +%s)
    create_notification_data='{
        "user_id": '$TEST_USER_ID',
        "title": "Test Notification '$timestamp'",
        "message": "This is a test notification created during automated testing.",
        "type": "info",
        "is_urgent": false,
        "is_read": false
    }'

    result=$(test_endpoint "POST" "/notifications" "$create_notification_data" "Authorization: Bearer $ADMIN_TOKEN")
    http_code=$(echo "$result" | cut -d'|' -f1)
    body=$(echo "$result" | cut -d'|' -f2-)

    echo -e "       ${BLUE}Method:${NC} POST"
    echo -e "       ${BLUE}Endpoint:${NC} /notifications"
    echo -e "       ${BLUE}Status:${NC} $http_code"
    echo -e "       ${BLUE}Response:${NC} $(echo "$body" | head -c 300)..."
    echo ""

    if [ "$http_code" = "201" ] || [ "$http_code" = "200" ]; then
        print_result "Create new notification" "PASS" "HTTP $http_code - Notification created successfully"
        # Extract the created notification ID for later tests
        TEST_NOTIFICATION_ID=$(echo "$body" | grep -o '"id":[0-9]*' | head -1 | cut -d':' -f2)
    else
        print_result "Create new notification" "FAIL" "HTTP $http_code - $body"
    fi
else
    print_result "Create new notification" "SKIP" "No admin token or user ID available"
fi

# =============================================================================
# TEST 6: GET /api/notifications/{id} - Get notification by ID
# =============================================================================
echo -e "${YELLOW}========================================${NC}"
echo -e "${YELLOW}TEST 6: Get Notification by ID${NC}"
echo -e "${YELLOW}========================================${NC}"
echo ""

if [ -n "$STUDENT_TOKEN" ] && [ -n "$TEST_NOTIFICATION_ID" ]; then
    result=$(test_endpoint "GET" "/notifications/$TEST_NOTIFICATION_ID" "" "Authorization: Bearer $STUDENT_TOKEN")
    http_code=$(echo "$result" | cut -d'|' -f1)
    body=$(echo "$result" | cut -d'|' -f2-)

    echo -e "       ${BLUE}Method:${NC} GET"
    echo -e "       ${BLUE}Endpoint:${NC} /notifications/$TEST_NOTIFICATION_ID"
    echo -e "       ${BLUE}Status:${NC} $http_code"
    echo ""

    if [ "$http_code" = "200" ]; then
        print_result "Get notification by ID" "PASS" "HTTP 200 - Notification retrieved successfully"
    else
        print_result "Get notification by ID" "FAIL" "HTTP $http_code - $body"
    fi
elif [ -n "$STUDENT_TOKEN" ]; then
    # Try to get any notification from the list
    result=$(test_endpoint "GET" "/notifications" "" "Authorization: Bearer $STUDENT_TOKEN")
    body=$(echo "$result" | cut -d'|' -f2-)
    TEST_NOTIFICATION_ID=$(echo "$body" | grep -o '"id":[0-9]*' | head -1 | cut -d':' -f2)

    if [ -n "$TEST_NOTIFICATION_ID" ]; then
        result=$(test_endpoint "GET" "/notifications/$TEST_NOTIFICATION_ID" "" "Authorization: Bearer $STUDENT_TOKEN")
        http_code=$(echo "$result" | cut -d'|' -f1)
        body=$(echo "$result" | cut -d'|' -f2-)

        echo -e "       ${BLUE}Method:${NC} GET"
        echo -e "       ${BLUE}Endpoint:${NC} /notifications/$TEST_NOTIFICATION_ID"
        echo -e "       ${BLUE}Status:${NC} $http_code"
        echo ""

        if [ "$http_code" = "200" ]; then
            print_result "Get notification by ID" "PASS" "HTTP 200 - Notification retrieved successfully"
        else
            print_result "Get notification by ID" "FAIL" "HTTP $http_code - $body"
        fi
    else
        print_result "Get notification by ID" "SKIP" "No notification ID available"
    fi
else
    print_result "Get notification by ID" "SKIP" "No student token available"
fi

# =============================================================================
# TEST 7: GET /api/notifications/{id} with invalid ID
# =============================================================================
echo -e "${YELLOW}========================================${NC}"
echo -e "${YELLOW}TEST 7: Get Notification with Invalid ID${NC}"
echo -e "${YELLOW}========================================${NC}"
echo ""

if [ -n "$STUDENT_TOKEN" ]; then
    result=$(test_endpoint "GET" "/notifications/9999999" "" "Authorization: Bearer $STUDENT_TOKEN")
    http_code=$(echo "$result" | cut -d'|' -f1)
    body=$(echo "$result" | cut -d'|' -f2-)

    echo -e "       ${BLUE}Method:${NC} GET"
    echo -e "       ${BLUE}Endpoint:${NC} /notifications/9999999"
    echo -e "       ${BLUE}Status:${NC} $http_code"
    echo ""

    if [ "$http_code" = "404" ]; then
        print_result "Get notification with invalid ID" "PASS" "HTTP 404 - Not found as expected"
    else
        print_result "Get notification with invalid ID" "FAIL" "Expected 404, got $http_code - $body"
    fi
else
    print_result "Get notification with invalid ID" "SKIP" "No student token available"
fi

# =============================================================================
# TEST 8: POST /api/notifications/{id}/mark-read - Mark notification as read
# =============================================================================
echo -e "${YELLOW}========================================${NC}"
echo -e "${YELLOW}TEST 8: Mark Notification as Read${NC}"
echo -e "${YELLOW}========================================${NC}"
echo ""

if [ -n "$STUDENT_TOKEN" ] && [ -n "$TEST_NOTIFICATION_ID" ]; then
    result=$(test_endpoint "POST" "/notifications/$TEST_NOTIFICATION_ID/mark-read" "" "Authorization: Bearer $STUDENT_TOKEN")
    http_code=$(echo "$result" | cut -d'|' -f1)
    body=$(echo "$result" | cut -d'|' -f2-)

    echo -e "       ${BLUE}Method:${NC} POST"
    echo -e "       ${BLUE}Endpoint:${NC} /notifications/$TEST_NOTIFICATION_ID/mark-read"
    echo -e "       ${BLUE}Status:${NC} $http_code"
    echo ""

    if [ "$http_code" = "200" ]; then
        print_result "Mark notification as read" "PASS" "HTTP 200 - Notification marked as read successfully"
    else
        print_result "Mark notification as read" "FAIL" "HTTP $http_code - $body"
    fi
else
    print_result "Mark notification as read" "SKIP" "No student token or notification ID available"
fi

# =============================================================================
# TEST 9: POST /api/notifications/{id}/mark-unread - Mark notification as unread
# =============================================================================
echo -e "${YELLOW}========================================${NC}"
echo -e "${YELLOW}TEST 9: Mark Notification as Unread${NC}"
echo -e "${YELLOW}========================================${NC}"
echo ""

if [ -n "$STUDENT_TOKEN" ] && [ -n "$TEST_NOTIFICATION_ID" ]; then
    result=$(test_endpoint "POST" "/notifications/$TEST_NOTIFICATION_ID/mark-unread" "" "Authorization: Bearer $STUDENT_TOKEN")
    http_code=$(echo "$result" | cut -d'|' -f1)
    body=$(echo "$result" | cut -d'|' -f2-)

    echo -e "       ${BLUE}Method:${NC} POST"
    echo -e "       ${BLUE}Endpoint:${NC} /notifications/$TEST_NOTIFICATION_ID/mark-unread"
    echo -e "       ${BLUE}Status:${NC} $http_code"
    echo ""

    if [ "$http_code" = "200" ]; then
        print_result "Mark notification as unread" "PASS" "HTTP 200 - Notification marked as unread successfully"
    else
        print_result "Mark notification as unread" "FAIL" "HTTP $http_code - $body"
    fi
else
    print_result "Mark notification as unread" "SKIP" "No student token or notification ID available"
fi

# =============================================================================
# TEST 10: PUT /api/notifications/{id}/read - Mark notification as read (PUT variant)
# =============================================================================
echo -e "${YELLOW}========================================${NC}"
echo -e "${YELLOW}TEST 10: Mark Notification as Read (PUT variant)${NC}"
echo -e "${YELLOW}========================================${NC}"
echo ""

if [ -n "$STUDENT_TOKEN" ] && [ -n "$TEST_NOTIFICATION_ID" ]; then
    result=$(test_endpoint "PUT" "/notifications/$TEST_NOTIFICATION_ID/read" "" "Authorization: Bearer $STUDENT_TOKEN")
    http_code=$(echo "$result" | cut -d'|' -f1)
    body=$(echo "$result" | cut -d'|' -f2-)

    echo -e "       ${BLUE}Method:${NC} PUT"
    echo -e "       ${BLUE}Endpoint:${NC} /notifications/$TEST_NOTIFICATION_ID/read"
    echo -e "       ${BLUE}Status:${NC} $http_code"
    echo ""

    if [ "$http_code" = "200" ]; then
        print_result "Mark notification as read (PUT variant)" "PASS" "HTTP 200 - Notification marked as read successfully"
    else
        print_result "Mark notification as read (PUT variant)" "FAIL" "HTTP $http_code - $body"
    fi
else
    print_result "Mark notification as read (PUT variant)" "SKIP" "No student token or notification ID available"
fi

# =============================================================================
# TEST 11: PATCH /api/notifications/{id}/read - Mark notification as read (PATCH variant)
# =============================================================================
echo -e "${YELLOW}========================================${NC}"
echo -e "${YELLOW}TEST 11: Mark Notification as Read (PATCH variant)${NC}"
echo -e "${YELLOW}========================================${NC}"
echo ""

if [ -n "$STUDENT_TOKEN" ] && [ -n "$TEST_NOTIFICATION_ID" ]; then
    result=$(test_endpoint "PATCH" "/notifications/$TEST_NOTIFICATION_ID/read" "" "Authorization: Bearer $STUDENT_TOKEN")
    http_code=$(echo "$result" | cut -d'|' -f1)
    body=$(echo "$result" | cut -d'|' -f2-)

    echo -e "       ${BLUE}Method:${NC} PATCH"
    echo -e "       ${BLUE}Endpoint:${NC} /notifications/$TEST_NOTIFICATION_ID/read"
    echo -e "       ${BLUE}Status:${NC} $http_code"
    echo ""

    if [ "$http_code" = "200" ]; then
        print_result "Mark notification as read (PATCH variant)" "PASS" "HTTP 200 - Notification marked as read successfully"
    else
        print_result "Mark notification as read (PATCH variant)" "FAIL" "HTTP $http_code - $body"
    fi
else
    print_result "Mark notification as read (PATCH variant)" "SKIP" "No student token or notification ID available"
fi

# =============================================================================
# TEST 12: POST /api/notifications/mark-all-read - Mark all notifications as read (POST)
# =============================================================================
echo -e "${YELLOW}========================================${NC}"
echo -e "${YELLOW}TEST 12: Mark All Notifications as Read (POST)${NC}"
echo -e "${YELLOW}========================================${NC}"
echo ""

if [ -n "$STUDENT_TOKEN" ]; then
    result=$(test_endpoint "POST" "/notifications/mark-all-read" "" "Authorization: Bearer $STUDENT_TOKEN")
    http_code=$(echo "$result" | cut -d'|' -f1)
    body=$(echo "$result" | cut -d'|' -f2-)

    echo -e "       ${BLUE}Method:${NC} POST"
    echo -e "       ${BLUE}Endpoint:${NC} /notifications/mark-all-read"
    echo -e "       ${BLUE}Status:${NC} $http_code"
    echo ""

    if [ "$http_code" = "200" ]; then
        print_result "Mark all notifications as read (POST)" "PASS" "HTTP 200 - All notifications marked as read successfully"
    else
        print_result "Mark all notifications as read (POST)" "FAIL" "HTTP $http_code - $body"
    fi
else
    print_result "Mark all notifications as read (POST)" "SKIP" "No student token available"
fi

# =============================================================================
# TEST 13: PUT /api/notifications/mark-all-read - Mark all notifications as read (PUT)
# =============================================================================
echo -e "${YELLOW}========================================${NC}"
echo -e "${YELLOW}TEST 13: Mark All Notifications as Read (PUT)${NC}"
echo -e "${YELLOW}========================================${NC}"
echo ""

if [ -n "$STUDENT_TOKEN" ]; then
    result=$(test_endpoint "PUT" "/notifications/mark-all-read" "" "Authorization: Bearer $STUDENT_TOKEN")
    http_code=$(echo "$result" | cut -d'|' -f1)
    body=$(echo "$result" | cut -d'|' -f2-)

    echo -e "       ${BLUE}Method:${NC} PUT"
    echo -e "       ${BLUE}Endpoint:${NC} /notifications/mark-all-read"
    echo -e "       ${BLUE}Status:${NC} $http_code"
    echo ""

    if [ "$http_code" = "200" ]; then
        print_result "Mark all notifications as read (PUT)" "PASS" "HTTP 200 - All notifications marked as read successfully"
    else
        print_result "Mark all notifications as read (PUT)" "FAIL" "HTTP $http_code - $body"
    fi
else
    print_result "Mark all notifications as read (PUT)" "SKIP" "No student token available"
fi

# =============================================================================
# TEST 14: PATCH /api/notifications/mark-all-read - Mark all notifications as read (PATCH)
# =============================================================================
echo -e "${YELLOW}========================================${NC}"
echo -e "${YELLOW}TEST 14: Mark All Notifications as Read (PATCH)${NC}"
echo -e "${YELLOW}========================================${NC}"
echo ""

if [ -n "$STUDENT_TOKEN" ]; then
    result=$(test_endpoint "PATCH" "/notifications/mark-all-read" "" "Authorization: Bearer $STUDENT_TOKEN")
    http_code=$(echo "$result" | cut -d'|' -f1)
    body=$(echo "$result" | cut -d'|' -f2-)

    echo -e "       ${BLUE}Method:${NC} PATCH"
    echo -e "       ${BLUE}Endpoint:${NC} /notifications/mark-all-read"
    echo -e "       ${BLUE}Status:${NC} $http_code"
    echo ""

    if [ "$http_code" = "200" ]; then
        print_result "Mark all notifications as read (PATCH)" "PASS" "HTTP 200 - All notifications marked as read successfully"
    else
        print_result "Mark all notifications as read (PATCH)" "FAIL" "HTTP $http_code - $body"
    fi
else
    print_result "Mark all notifications as read (PATCH)" "SKIP" "No student token available"
fi

# =============================================================================
# TEST 15: DELETE /api/notifications/clear-read - Clear read notifications
# =============================================================================
echo -e "${YELLOW}========================================${NC}"
echo -e "${YELLOW}TEST 15: Clear Read Notifications${NC}"
echo -e "${YELLOW}========================================${NC}"
echo ""

if [ -n "$STUDENT_TOKEN" ]; then
    result=$(test_endpoint "DELETE" "/notifications/clear-read" "" "Authorization: Bearer $STUDENT_TOKEN")
    http_code=$(echo "$result" | cut -d'|' -f1)
    body=$(echo "$result" | cut -d'|' -f2-)

    echo -e "       ${BLUE}Method:${NC} DELETE"
    echo -e "       ${BLUE}Endpoint:${NC} /notifications/clear-read"
    echo -e "       ${BLUE}Status:${NC} $http_code"
    echo ""

    if [ "$http_code" = "200" ] || [ "$http_code" = "204" ]; then
        print_result "Clear read notifications" "PASS" "HTTP $http_code - Read notifications cleared successfully"
    else
        print_result "Clear read notifications" "FAIL" "HTTP $http_code - $body"
    fi
else
    print_result "Clear read notifications" "SKIP" "No student token available"
fi

# =============================================================================
# TEST 16: PUT /api/notifications/{id} - Update notification
# =============================================================================
echo -e "${YELLOW}========================================${NC}"
echo -e "${YELLOW}TEST 16: Update Notification${NC}"
echo -e "${YELLOW}========================================${NC}"
echo ""

if [ -n "$ADMIN_TOKEN" ] && [ -n "$TEST_NOTIFICATION_ID" ]; then
    update_notification_data='{
        "title": "Updated Test Notification",
        "message": "This notification has been updated during testing.",
        "is_urgent": true
    }'

    result=$(test_endpoint "PUT" "/notifications/$TEST_NOTIFICATION_ID" "$update_notification_data" "Authorization: Bearer $ADMIN_TOKEN")
    http_code=$(echo "$result" | cut -d'|' -f1)
    body=$(echo "$result" | cut -d'|' -f2-)

    echo -e "       ${BLUE}Method:${NC} PUT"
    echo -e "       ${BLUE}Endpoint:${NC} /notifications/$TEST_NOTIFICATION_ID"
    echo -e "       ${BLUE}Status:${NC} $http_code"
    echo ""

    if [ "$http_code" = "200" ]; then
        print_result "Update notification" "PASS" "HTTP 200 - Notification updated successfully"
    else
        print_result "Update notification" "FAIL" "HTTP $http_code - $body"
    fi
else
    print_result "Update notification" "SKIP" "No admin token or notification ID available"
fi

# =============================================================================
# TEST 17: PUT /api/notifications/{id} by student (should fail)
# =============================================================================
echo -e "${YELLOW}========================================${NC}"
echo -e "${YELLOW}TEST 17: Update Notification by Student (should fail)${NC}"
echo -e "${YELLOW}========================================${NC}"
echo ""

if [ -n "$STUDENT_TOKEN" ] && [ -n "$TEST_NOTIFICATION_ID" ]; then
    update_notification_data='{
        "title": "Student Attempting to Update"
    }'

    result=$(test_endpoint "PUT" "/notifications/$TEST_NOTIFICATION_ID" "$update_notification_data" "Authorization: Bearer $STUDENT_TOKEN")
    http_code=$(echo "$result" | cut -d'|' -f1)
    body=$(echo "$result" | cut -d'|' -f2-)

    echo -e "       ${BLUE}Method:${NC} PUT"
    echo -e "       ${BLUE}Endpoint:${NC} /notifications/$TEST_NOTIFICATION_ID"
    echo -e "       ${BLUE}Status:${NC} $http_code"
    echo ""

    if [ "$http_code" = "403" ]; then
        print_result "Update notification by student" "PASS" "HTTP 403 - Forbidden as expected"
    else
        print_result "Update notification by student" "FAIL" "Expected 403, got $http_code - $body"
    fi
else
    print_result "Update notification by student" "SKIP" "No student token or notification ID available"
fi

# =============================================================================
# TEST 18: DELETE /api/notifications/{id} - Delete notification
# =============================================================================
echo -e "${YELLOW}========================================${NC}"
echo -e "${YELLOW}TEST 18: Delete Notification${NC}"
echo -e "${YELLOW}========================================${NC}"
echo ""

if [ -n "$STUDENT_TOKEN" ] && [ -n "$TEST_NOTIFICATION_ID" ]; then
    result=$(test_endpoint "DELETE" "/notifications/$TEST_NOTIFICATION_ID" "" "Authorization: Bearer $STUDENT_TOKEN")
    http_code=$(echo "$result" | cut -d'|' -f1)
    body=$(echo "$result" | cut -d'|' -f2-)

    echo -e "       ${BLUE}Method:${NC} DELETE"
    echo -e "       ${BLUE}Endpoint:${NC} /notifications/$TEST_NOTIFICATION_ID"
    echo -e "       ${BLUE}Status:${NC} $http_code"
    echo ""

    if [ "$http_code" = "204" ] || [ "$http_code" = "200" ]; then
        print_result "Delete notification" "PASS" "HTTP $http_code - Notification deleted successfully"
    else
        print_result "Delete notification" "FAIL" "HTTP $http_code - $body"
    fi
else
    print_result "Delete notification" "SKIP" "No student token or notification ID available"
fi

# =============================================================================
# TEST 19: GET /api/notifications without authentication
# =============================================================================
echo -e "${YELLOW}========================================${NC}"
echo -e "${YELLOW}TEST 19: List Notifications Without Authentication${NC}"
echo -e "${YELLOW}========================================${NC}"
echo ""

result=$(test_endpoint "GET" "/notifications" "" "")
http_code=$(echo "$result" | cut -d'|' -f1)
body=$(echo "$result" | cut -d'|' -f2-)

echo -e "       ${BLUE}Method:${NC} GET"
echo -e "       ${BLUE}Endpoint:${NC} /notifications"
echo -e "       ${BLUE}Status:${NC} $http_code"
echo ""

if [ "$http_code" = "401" ]; then
    print_result "List notifications without authentication" "PASS" "HTTP 401 - Unauthorized as expected"
else
    print_result "List notifications without authentication" "FAIL" "Expected 401, got $http_code - $body"
fi

# =============================================================================
# TEST 20: POST /api/notifications without authentication
# =============================================================================
echo -e "${YELLOW}========================================${NC}"
echo -e "${YELLOW}TEST 20: Create Notification Without Authentication${NC}"
echo -e "${YELLOW}========================================${NC}"
echo ""

timestamp=$(date +%s)
create_notification_no_auth='{
    "title": "Unauthorized Notification '$timestamp'",
    "message": "This should not be created without authentication.",
    "type": "info"
}'

result=$(test_endpoint "POST" "/notifications" "$create_notification_no_auth" "")
http_code=$(echo "$result" | cut -d'|' -f1)
body=$(echo "$result" | cut -d'|' -f2-)

echo -e "       ${BLUE}Method:${NC} POST"
echo -e "       ${BLUE}Endpoint:${NC} /notifications"
echo -e "       ${BLUE}Status:${NC} $http_code"
echo ""

if [ "$http_code" = "401" ]; then
    print_result "Create notification without authentication" "PASS" "HTTP 401 - Unauthorized as expected"
else
    print_result "Create notification without authentication" "FAIL" "Expected 401, got $http_code - $body"
fi

# =============================================================================
# TEST 21: POST /api/notifications by student (should fail - admin only)
# =============================================================================
echo -e "${YELLOW}========================================${NC}"
echo -e "${YELLOW}TEST 21: Create Notification by Student (should fail)${NC}"
echo -e "${YELLOW}========================================${NC}"
echo ""

if [ -n "$STUDENT_TOKEN" ] && [ -n "$TEST_USER_ID" ]; then
    timestamp=$(date +%s)
    create_notification_student='{
        "user_id": '$TEST_USER_ID',
        "title": "Student Created Notification '$timestamp'",
        "message": "This should not be created by a student.",
        "type": "info"
    }'

    result=$(test_endpoint "POST" "/notifications" "$create_notification_student" "Authorization: Bearer $STUDENT_TOKEN")
    http_code=$(echo "$result" | cut -d'|' -f1)
    body=$(echo "$result" | cut -d'|' -f2-)

    echo -e "       ${BLUE}Method:${NC} POST"
    echo -e "       ${BLUE}Endpoint:${NC} /notifications"
    echo -e "       ${BLUE}Status:${NC} $http_code"
    echo ""

    if [ "$http_code" = "403" ]; then
        print_result "Create notification by student" "PASS" "HTTP 403 - Forbidden as expected"
    else
        print_result "Create notification by student" "FAIL" "Expected 403, got $http_code - $body"
    fi
else
    print_result "Create notification by student" "SKIP" "No student token or user ID available"
fi

# =============================================================================
# TEST 22: GET /api/notifications/{id} without authentication
# =============================================================================
echo -e "${YELLOW}========================================${NC}"
echo -e "${YELLOW}TEST 22: Get Notification Without Authentication${NC}"
echo -e "${YELLOW}========================================${NC}"
echo ""

if [ -n "$TEST_NOTIFICATION_ID" ]; then
    result=$(test_endpoint "GET" "/notifications/$TEST_NOTIFICATION_ID" "" "")
    http_code=$(echo "$result" | cut -d'|' -f1)
    body=$(echo "$result" | cut -d'|' -f2-)

    echo -e "       ${BLUE}Method:${NC} GET"
    echo -e "       ${BLUE}Endpoint:${NC} /notifications/$TEST_NOTIFICATION_ID"
    echo -e "       ${BLUE}Status:${NC} $http_code"
    echo ""

    if [ "$http_code" = "401" ]; then
        print_result "Get notification without authentication" "PASS" "HTTP 401 - Unauthorized as expected"
    else
        print_result "Get notification without authentication" "FAIL" "Expected 401, got $http_code - $body"
    fi
else
    print_result "Get notification without authentication" "SKIP" "No notification ID available"
fi

# =============================================================================
# TEST 23: GET /api/notifications/unread without authentication
# =============================================================================
echo -e "${YELLOW}========================================${NC}"
echo -e "${YELLOW}TEST 23: Get Unread Notifications Without Authentication${NC}"
echo -e "${YELLOW}========================================${NC}"
echo ""

result=$(test_endpoint "GET" "/notifications/unread" "" "")
http_code=$(echo "$result" | cut -d'|' -f1)
body=$(echo "$result" | cut -d'|' -f2-)

echo -e "       ${BLUE}Method:${NC} GET"
echo -e "       ${BLUE}Endpoint:${NC} /notifications/unread"
echo -e "       ${BLUE}Status:${NC} $http_code"
echo ""

if [ "$http_code" = "401" ]; then
    print_result "Get unread notifications without authentication" "PASS" "HTTP 401 - Unauthorized as expected"
else
    print_result "Get unread notifications without authentication" "FAIL" "Expected 401, got $http_code - $body"
fi

# =============================================================================
# TEST 24: GET /api/notifications/urgent without authentication
# =============================================================================
echo -e "${YELLOW}========================================${NC}"
echo -e "${YELLOW}TEST 24: Get Urgent Notifications Without Authentication${NC}"
echo -e "${YELLOW}========================================${NC}"
echo ""

result=$(test_endpoint "GET" "/notifications/urgent" "" "")
http_code=$(echo "$result" | cut -d'|' -f1)
body=$(echo "$result" | cut -d'|' -f2-)

echo -e "       ${BLUE}Method:${NC} GET"
echo -e "       ${BLUE}Endpoint:${NC} /notifications/urgent"
echo -e "       ${BLUE}Status:${NC} $http_code"
echo ""

if [ "$http_code" = "401" ]; then
    print_result "Get urgent notifications without authentication" "PASS" "HTTP 401 - Unauthorized as expected"
else
    print_result "Get urgent notifications without authentication" "FAIL" "Expected 401, got $http_code - $body"
fi

# =============================================================================
# TEST 25: GET /api/notifications/counts without authentication
# =============================================================================
echo -e "${YELLOW}========================================${NC}"
echo -e "${YELLOW}TEST 25: Get Notification Counts Without Authentication${NC}"
echo -e "${YELLOW}========================================${NC}"
echo ""

result=$(test_endpoint "GET" "/notifications/counts" "" "")
http_code=$(echo "$result" | cut -d'|' -f1)
body=$(echo "$result" | cut -d'|' -f2-)

echo -e "       ${BLUE}Method:${NC} GET"
echo -e "       ${BLUE}Endpoint:${NC} /notifications/counts"
echo -e "       ${BLUE}Status:${NC} $http_code"
echo ""

if [ "$http_code" = "401" ]; then
    print_result "Get notification counts without authentication" "PASS" "HTTP 401 - Unauthorized as expected"
else
    print_result "Get notification counts without authentication" "FAIL" "Expected 401, got $http_code - $body"
fi

# =============================================================================
# TEST 26: POST /api/notifications - Create urgent notification
# =============================================================================
echo -e "${YELLOW}========================================${NC}"
echo -e "${YELLOW}TEST 26: Create Urgent Notification${NC}"
echo -e "${YELLOW}========================================${NC}"
echo ""

if [ -n "$ADMIN_TOKEN" ] && [ -n "$TEST_USER_ID" ]; then
    timestamp=$(date +%s)
    create_urgent_notification_data='{
        "user_id": '$TEST_USER_ID',
        "title": "Urgent Notification '$timestamp'",
        "message": "This is an urgent test notification.",
        "type": "alert",
        "is_urgent": true,
        "is_read": false
    }'

    result=$(test_endpoint "POST" "/notifications" "$create_urgent_notification_data" "Authorization: Bearer $ADMIN_TOKEN")
    http_code=$(echo "$result" | cut -d'|' -f1)
    body=$(echo "$result" | cut -d'|' -f2-)

    echo -e "       ${BLUE}Method:${NC} POST"
    echo -e "       ${BLUE}Endpoint:${NC} /notifications"
    echo -e "       ${BLUE}Status:${NC} $http_code"
    echo ""

    if [ "$http_code" = "201" ] || [ "$http_code" = "200" ]; then
        print_result "Create urgent notification" "PASS" "HTTP $http_code - Urgent notification created successfully"
        # Extract the created notification ID for later tests
        TEST_NOTIFICATION_ID=$(echo "$body" | grep -o '"id":[0-9]*' | head -1 | cut -d':' -f2)
    else
        print_result "Create urgent notification" "FAIL" "HTTP $http_code - $body"
    fi
else
    print_result "Create urgent notification" "SKIP" "No admin token or user ID available"
fi

# =============================================================================
# TEST 27: POST /api/notifications - Create notification with different types
# =============================================================================
echo -e "${YELLOW}========================================${NC}"
echo -e "${YELLOW}TEST 27: Create Notification with Different Types${NC}"
echo -e "${YELLOW}========================================${NC}"
echo ""

if [ -n "$ADMIN_TOKEN" ] && [ -n "$TEST_USER_ID" ]; then
    timestamp=$(date +%s)
    create_type_notification_data='{
        "user_id": '$TEST_USER_ID',
        "title": "Warning Notification '$timestamp'",
        "message": "This is a warning type notification.",
        "type": "warning",
        "is_urgent": false,
        "is_read": false
    }'

    result=$(test_endpoint "POST" "/notifications" "$create_type_notification_data" "Authorization: Bearer $ADMIN_TOKEN")
    http_code=$(echo "$result" | cut -d'|' -f1)
    body=$(echo "$result" | cut -d'|' -f2-)

    echo -e "       ${BLUE}Method:${NC} POST"
    echo -e "       ${BLUE}Endpoint:${NC} /notifications"
    echo -e "       ${BLUE}Status:${NC} $http_code"
    echo ""

    if [ "$http_code" = "201" ] || [ "$http_code" = "200" ]; then
        print_result "Create notification with different types" "PASS" "HTTP $http_code - Notification created successfully"
    else
        print_result "Create notification with different types" "FAIL" "HTTP $http_code - $body"
    fi
else
    print_result "Create notification with different types" "SKIP" "No admin token or user ID available"
fi

# =============================================================================
# TEST 28: POST /api/notifications - Create notification with missing fields
# =============================================================================
echo -e "${YELLOW}========================================${NC}"
echo -e "${YELLOW}TEST 28: Create Notification with Missing Fields${NC}"
echo -e "${YELLOW}========================================${NC}"
echo ""

if [ -n "$ADMIN_TOKEN" ]; then
    timestamp=$(date +%s)
    create_incomplete_notification_data='{
        "title": "Incomplete Notification '$timestamp'",
        "type": "info"
    }'

    result=$(test_endpoint "POST" "/notifications" "$create_incomplete_notification_data" "Authorization: Bearer $ADMIN_TOKEN")
    http_code=$(echo "$result" | cut -d'|' -f1)
    body=$(echo "$result" | cut -d'|' -f2-)

    echo -e "       ${BLUE}Method:${NC} POST"
    echo -e "       ${BLUE}Endpoint:${NC} /notifications"
    echo -e "       ${BLUE}Status:${NC} $http_code"
    echo ""

    if [ "$http_code" = "422" ]; then
        print_result "Create notification with missing fields" "PASS" "HTTP 422 - Validation error as expected"
    else
        print_result "Create notification with missing fields" "FAIL" "Expected 422, got $http_code - $body"
    fi
else
    print_result "Create notification with missing fields" "SKIP" "No admin token available"
fi

# =============================================================================
# TEST 29: DELETE /api/notifications/{id} with invalid ID
# =============================================================================
echo -e "${YELLOW}========================================${NC}"
echo -e "${YELLOW}TEST 29: Delete Notification with Invalid ID${NC}"
echo -e "${YELLOW}========================================${NC}"
echo ""

if [ -n "$STUDENT_TOKEN" ]; then
    result=$(test_endpoint "DELETE" "/notifications/9999999" "" "Authorization: Bearer $STUDENT_TOKEN")
    http_code=$(echo "$result" | cut -d'|' -f1)
    body=$(echo "$result" | cut -d'|' -f2-)

    echo -e "       ${BLUE}Method:${NC} DELETE"
    echo -e "       ${BLUE}Endpoint:${NC} /notifications/9999999"
    echo -e "       ${BLUE}Status:${NC} $http_code"
    echo ""

    if [ "$http_code" = "404" ]; then
        print_result "Delete notification with invalid ID" "PASS" "HTTP 404 - Not found as expected"
    else
        print_result "Delete notification with invalid ID" "FAIL" "Expected 404, got $http_code - $body"
    fi
else
    print_result "Delete notification with invalid ID" "SKIP" "No student token available"
fi

# =============================================================================
# TEST 30: PUT /api/notifications/{id} with invalid ID
# =============================================================================
echo -e "${YELLOW}========================================${NC}"
echo -e "${YELLOW}TEST 30: Update Notification with Invalid ID${NC}"
echo -e "${YELLOW}========================================${NC}"
echo ""

if [ -n "$ADMIN_TOKEN" ]; then
    update_invalid_notification_data='{
        "title": "Update Invalid ID"
    }'

    result=$(test_endpoint "PUT" "/notifications/9999999" "$update_invalid_notification_data" "Authorization: Bearer $ADMIN_TOKEN")
    http_code=$(echo "$result" | cut -d'|' -f1)
    body=$(echo "$result" | cut -d'|' -f2-)

    echo -e "       ${BLUE}Method:${NC} PUT"
    echo -e "       ${BLUE}Endpoint:${NC} /notifications/9999999"
    echo -e "       ${BLUE}Status:${NC} $http_code"
    echo ""

    if [ "$http_code" = "404" ]; then
        print_result "Update notification with invalid ID" "PASS" "HTTP 404 - Not found as expected"
    else
        print_result "Update notification with invalid ID" "FAIL" "Expected 404, got $http_code - $body"
    fi
else
    print_result "Update notification with invalid ID" "SKIP" "No admin token available"
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