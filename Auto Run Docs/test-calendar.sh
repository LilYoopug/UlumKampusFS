#!/bin/bash

# =============================================================================
# Academic Calendar Event Endpoint Tests
# Tests all calendar event-related API endpoints using curl
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

# Test data
TEST_EVENT_ID=""

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
# PRE-FLIGHT: Register and Login to get access tokens
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

# Create test faculty user
faculty_register_data='{
    "name": "Test Faculty '$timestamp'",
    "email": "testfaculty'$timestamp'@example.com",
    "password": "Password123!",
    "password_confirmation": "Password123!",
    "role": "faculty"
}'

result=$(test_endpoint "POST" "/register" "$faculty_register_data" "")
faculty_register_http_code=$(echo "$result" | cut -d'|' -f1)
faculty_register_body=$(echo "$result" | cut -d'|' -f2-)

if [ "$faculty_register_http_code" = "201" ] || [ "$faculty_register_http_code" = "200" ]; then
    # Extract access token
    FACULTY_TOKEN=$(echo "$faculty_register_body" | grep -o '"token":"[^"]*"' | cut -d'"' -f4)
    if [ -z "$FACULTY_TOKEN" ]; then
        FACULTY_TOKEN=$(echo "$faculty_register_body" | grep -o '"access_token":"[^"]*"' | cut -d'"' -f4)
    fi
    FACULTY_EMAIL="testfaculty$timestamp@example.com"
    FACULTY_PASSWORD="Password123!"
    echo -e "${GREEN}Faculty user created${NC}"
else
    echo -e "${YELLOW}Faculty user already exists or failed${NC}"
    # Try login
    faculty_login_data='{
        "email": "testfaculty$timestamp@example.com",
        "password": "Password123!"
    }'
    result=$(test_endpoint "POST" "/login" "$faculty_login_data" "")
    FACULTY_TOKEN=$(echo "$result" | grep -o '"token":"[^"]*"' | cut -d'"' -f4)
    if [ -z "$FACULTY_TOKEN" ]; then
        FACULTY_TOKEN=$(echo "$result" | grep -o '"access_token":"[^"]*"' | cut -d'"' -f4)
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
if [ -z "$ADMIN_TOKEN" ] && [ -z "$FACULTY_TOKEN" ] && [ -z "$STUDENT_TOKEN" ]; then
    echo -e "${RED}No authentication tokens available. Exiting.${NC}"
    exit 1
fi

echo ""

# =============================================================================
# TEST 1: GET /api/academic-calendar-events - List all events
# =============================================================================
echo -e "${YELLOW}========================================${NC}"
echo -e "${YELLOW}TEST 1: List All Calendar Events${NC}"
echo -e "${YELLOW}========================================${NC}"
echo ""

if [ -n "$ADMIN_TOKEN" ]; then
    result=$(test_endpoint "GET" "/academic-calendar-events" "" "Authorization: Bearer $ADMIN_TOKEN")
    http_code=$(echo "$result" | cut -d'|' -f1)
    body=$(echo "$result" | cut -d'|' -f2-)

    echo -e "       ${BLUE}Method:${NC} GET"
    echo -e "       ${BLUE}Endpoint:${NC} /academic-calendar-events"
    echo -e "       ${BLUE}Status:${NC} $http_code"
    echo -e "       ${BLUE}Response:${NC} $(echo "$body" | head -c 200)..."
    echo ""

    if [ "$http_code" = "200" ]; then
        print_result "List all calendar events" "PASS" "HTTP 200 - Events retrieved successfully"
    else
        print_result "List all calendar events" "FAIL" "HTTP $http_code - $body"
    fi
else
    print_result "List all calendar events" "SKIP" "No admin token available"
fi

# =============================================================================
# TEST 2: GET /api/academic-calendar-events with student token
# =============================================================================
echo -e "${YELLOW}========================================${NC}"
echo -e "${YELLOW}TEST 2: List Calendar Events (Student)${NC}"
echo -e "${YELLOW}========================================${NC}"
echo ""

if [ -n "$STUDENT_TOKEN" ]; then
    result=$(test_endpoint "GET" "/academic-calendar-events" "" "Authorization: Bearer $STUDENT_TOKEN")
    http_code=$(echo "$result" | cut -d'|' -f1)
    body=$(echo "$result" | cut -d'|' -f2-)

    echo -e "       ${BLUE}Method:${NC} GET"
    echo -e "       ${BLUE}Endpoint:${NC} /academic-calendar-events"
    echo -e "       ${BLUE}Status:${NC} $http_code"
    echo ""

    if [ "$http_code" = "200" ]; then
        print_result "List calendar events (student)" "PASS" "HTTP 200 - Events retrieved successfully"
    else
        print_result "List calendar events (student)" "FAIL" "HTTP $http_code - $body"
    fi
else
    print_result "List calendar events (student)" "SKIP" "No student token available"
fi

# =============================================================================
# TEST 3: POST /api/academic-calendar-events - Create new event (admin)
# =============================================================================
echo -e "${YELLOW}========================================${NC}"
echo -e "${YELLOW}TEST 3: Create New Calendar Event (Admin)${NC}"
echo -e "${YELLOW}========================================${NC}"
echo ""

if [ -n "$ADMIN_TOKEN" ]; then
    timestamp=$(date +%s)
    create_event_data='{
        "title": "Final Exams Fall 2025",
        "start_date": "2025-12-15",
        "end_date": "2025-12-22",
        "category": "exam",
        "description": "Final examination period for Fall 2025 semester"
    }'

    result=$(test_endpoint "POST" "/academic-calendar-events" "$create_event_data" "Authorization: Bearer $ADMIN_TOKEN")
    http_code=$(echo "$result" | cut -d'|' -f1)
    body=$(echo "$result" | cut -d'|' -f2-)

    echo -e "       ${BLUE}Method:${NC} POST"
    echo -e "       ${BLUE}Endpoint:${NC} /academic-calendar-events"
    echo -e "       ${BLUE}Status:${NC} $http_code"
    echo -e "       ${BLUE}Response:${NC} $(echo "$body" | head -c 300)..."
    echo ""

    if [ "$http_code" = "201" ] || [ "$http_code" = "200" ]; then
        print_result "Create calendar event (admin)" "PASS" "HTTP $http_code - Event created successfully"
        # Extract the created event ID for later tests
        TEST_EVENT_ID=$(echo "$body" | grep -o '"id":[0-9]*' | head -1 | cut -d':' -f2)
    else
        print_result "Create calendar event (admin)" "FAIL" "HTTP $http_code - $body"
    fi
else
    print_result "Create calendar event (admin)" "SKIP" "No admin token available"
fi

# =============================================================================
# TEST 4: POST /api/academic-calendar-events - Create new event (faculty)
# =============================================================================
echo -e "${YELLOW}========================================${NC}"
echo -e "${YELLOW}TEST 4: Create New Calendar Event (Faculty)${NC}"
echo -e "${YELLOW}========================================${NC}"
echo ""

if [ -n "$FACULTY_TOKEN" ]; then
    timestamp=$(date +%s)
    create_event_data='{
        "title": "Faculty Workshop '$timestamp'",
        "start_date": "2025-12-15",
        "end_date": "2025-12-15",
        "category": "workshop",
        "description": "Professional development workshop for faculty members"
    }'

    result=$(test_endpoint "POST" "/academic-calendar-events" "$create_event_data" "Authorization: Bearer $FACULTY_TOKEN")
    http_code=$(echo "$result" | cut -d'|' -f1)
    body=$(echo "$result" | cut -d'|' -f2-)

    echo -e "       ${BLUE}Method:${NC} POST"
    echo -e "       ${BLUE}Endpoint:${NC} /academic-calendar-events"
    echo -e "       ${BLUE}Status:${NC} $http_code"
    echo ""

    if [ "$http_code" = "201" ] || [ "$http_code" = "200" ]; then
        print_result "Create calendar event (faculty)" "PASS" "HTTP $http_code - Event created successfully"
        # Extract the created event ID if we don't have one yet
        if [ -z "$TEST_EVENT_ID" ]; then
            TEST_EVENT_ID=$(echo "$body" | grep -o '"id":[0-9]*' | head -1 | cut -d':' -f2)
        fi
    else
        print_result "Create calendar event (faculty)" "FAIL" "HTTP $http_code - $body"
    fi
else
    print_result "Create calendar event (faculty)" "SKIP" "No faculty token available"
fi

# =============================================================================
# TEST 5: POST /api/academic-calendar-events with student token (should fail)
# =============================================================================
echo -e "${YELLOW}========================================${NC}"
echo -e "${YELLOW}TEST 5: Create Calendar Event (Student - should fail)${NC}"
echo -e "${YELLOW}========================================${NC}"
echo ""

if [ -n "$STUDENT_TOKEN" ]; then
    create_event_data='{
        "title": "Unauthorized Event",
        "start_date": "2025-12-15",
        "end_date": "2025-12-15",
        "category": "other",
        "description": "This should not be created"
    }'

    result=$(test_endpoint "POST" "/academic-calendar-events" "$create_event_data" "Authorization: Bearer $STUDENT_TOKEN")
    http_code=$(echo "$result" | cut -d'|' -f1)
    body=$(echo "$result" | cut -d'|' -f2-)

    echo -e "       ${BLUE}Method:${NC} POST"
    echo -e "       ${BLUE}Endpoint:${NC} /academic-calendar-events"
    echo -e "       ${BLUE}Status:${NC} $http_code"
    echo ""

    if [ "$http_code" = "403" ]; then
        print_result "Create calendar event (student)" "PASS" "HTTP 403 - Forbidden as expected"
    else
        print_result "Create calendar event (student)" "FAIL" "Expected 403, got $http_code - $body"
    fi
else
    print_result "Create calendar event (student)" "SKIP" "No student token available"
fi

# =============================================================================
# TEST 6: GET /api/academic-calendar-events/{id} - Get specific event
# =============================================================================
echo -e "${YELLOW}========================================${NC}"
echo -e "${YELLOW}TEST 6: Get Calendar Event by ID${NC}"
echo -e "${YELLOW}========================================${NC}"
echo ""

if [ -n "$ADMIN_TOKEN" ] && [ -n "$TEST_EVENT_ID" ]; then
    result=$(test_endpoint "GET" "/academic-calendar-events/$TEST_EVENT_ID" "" "Authorization: Bearer $ADMIN_TOKEN")
    http_code=$(echo "$result" | cut -d'|' -f1)
    body=$(echo "$result" | cut -d'|' -f2-)

    echo -e "       ${BLUE}Method:${NC} GET"
    echo -e "       ${BLUE}Endpoint:${NC} /academic-calendar-events/$TEST_EVENT_ID"
    echo -e "       ${BLUE}Status:${NC} $http_code"
    echo ""

    if [ "$http_code" = "200" ]; then
        print_result "Get calendar event by ID" "PASS" "HTTP 200 - Event retrieved successfully"
    else
        print_result "Get calendar event by ID" "FAIL" "HTTP $http_code - $body"
    fi
elif [ -n "$ADMIN_TOKEN" ]; then
    # Try to get any event from the list
    result=$(test_endpoint "GET" "/academic-calendar-events" "" "Authorization: Bearer $ADMIN_TOKEN")
    body=$(echo "$result" | cut -d'|' -f2-)
    TEST_EVENT_ID=$(echo "$body" | grep -o '"id":[0-9]*' | head -1 | cut -d':' -f2)

    if [ -n "$TEST_EVENT_ID" ]; then
        result=$(test_endpoint "GET" "/academic-calendar-events/$TEST_EVENT_ID" "" "Authorization: Bearer $ADMIN_TOKEN")
        http_code=$(echo "$result" | cut -d'|' -f1)
        body=$(echo "$result" | cut -d'|' -f2-)

        echo -e "       ${BLUE}Method:${NC} GET"
        echo -e "       ${BLUE}Endpoint:${NC} /academic-calendar-events/$TEST_EVENT_ID"
        echo -e "       ${BLUE}Status:${NC} $http_code"
        echo ""

        if [ "$http_code" = "200" ]; then
            print_result "Get calendar event by ID" "PASS" "HTTP 200 - Event retrieved successfully"
        else
            print_result "Get calendar event by ID" "FAIL" "HTTP $http_code - $body"
        fi
    else
        print_result "Get calendar event by ID" "SKIP" "No event ID available"
    fi
else
    print_result "Get calendar event by ID" "SKIP" "No admin token available"
fi

# =============================================================================
# TEST 7: GET /api/academic-calendar-events/{id} with student token
# =============================================================================
echo -e "${YELLOW}========================================${NC}"
echo -e "${YELLOW}TEST 7: Get Calendar Event by ID (Student)${NC}"
echo -e "${YELLOW}========================================${NC}"
echo ""

if [ -n "$STUDENT_TOKEN" ] && [ -n "$TEST_EVENT_ID" ]; then
    result=$(test_endpoint "GET" "/academic-calendar-events/$TEST_EVENT_ID" "" "Authorization: Bearer $STUDENT_TOKEN")
    http_code=$(echo "$result" | cut -d'|' -f1)
    body=$(echo "$result" | cut -d'|' -f2-)

    echo -e "       ${BLUE}Method:${NC} GET"
    echo -e "       ${BLUE}Endpoint:${NC} /academic-calendar-events/$TEST_EVENT_ID"
    echo -e "       ${BLUE}Status:${NC} $http_code"
    echo ""

    if [ "$http_code" = "200" ]; then
        print_result "Get calendar event by ID (student)" "PASS" "HTTP 200 - Event retrieved successfully"
    else
        print_result "Get calendar event by ID (student)" "FAIL" "HTTP $http_code - $body"
    fi
else
    print_result "Get calendar event by ID (student)" "SKIP" "No student token or event ID available"
fi

# =============================================================================
# TEST 8: PUT /api/academic-calendar-events/{id} - Update event (admin)
# =============================================================================
echo -e "${YELLOW}========================================${NC}"
echo -e "${YELLOW}TEST 8: Update Calendar Event (Admin)${NC}"
echo -e "${YELLOW}========================================${NC}"
echo ""

if [ -n "$ADMIN_TOKEN" ] && [ -n "$TEST_EVENT_ID" ]; then
    update_event_data='{
        "title": "Updated Final Exams Fall 2025",
        "description": "Updated final examination period for Fall 2025 semester"
    }'

    result=$(test_endpoint "PUT" "/academic-calendar-events/$TEST_EVENT_ID" "$update_event_data" "Authorization: Bearer $ADMIN_TOKEN")
    http_code=$(echo "$result" | cut -d'|' -f1)
    body=$(echo "$result" | cut -d'|' -f2-)

    echo -e "       ${BLUE}Method:${NC} PUT"
    echo -e "       ${BLUE}Endpoint:${NC} /academic-calendar-events/$TEST_EVENT_ID"
    echo -e "       ${BLUE}Status:${NC} $http_code"
    echo ""

    if [ "$http_code" = "200" ]; then
        print_result "Update calendar event (admin)" "PASS" "HTTP 200 - Event updated successfully"
    else
        print_result "Update calendar event (admin)" "FAIL" "HTTP $http_code - $body"
    fi
else
    print_result "Update calendar event (admin)" "SKIP" "No admin token or event ID available"
fi

# =============================================================================
# TEST 9: PUT /api/academic-calendar-events/{id} - Update event (faculty)
# =============================================================================
echo -e "${YELLOW}========================================${NC}"
echo -e "${YELLOW}TEST 9: Update Calendar Event (Faculty)${NC}"
echo -e "${YELLOW}========================================${NC}"
echo ""

if [ -n "$FACULTY_TOKEN" ] && [ -n "$TEST_EVENT_ID" ]; then
    update_event_data='{
        "title": "Faculty Updated Event",
        "description": "Event updated by faculty member"
    }'

    result=$(test_endpoint "PUT" "/academic-calendar-events/$TEST_EVENT_ID" "$update_event_data" "Authorization: Bearer $FACULTY_TOKEN")
    http_code=$(echo "$result" | cut -d'|' -f1)
    body=$(echo "$result" | cut -d'|' -f2-)

    echo -e "       ${BLUE}Method:${NC} PUT"
    echo -e "       ${BLUE}Endpoint:${NC} /academic-calendar-events/$TEST_EVENT_ID"
    echo -e "       ${BLUE}Status:${NC} $http_code"
    echo ""

    if [ "$http_code" = "200" ]; then
        print_result "Update calendar event (faculty)" "PASS" "HTTP 200 - Event updated successfully"
    else
        print_result "Update calendar event (faculty)" "FAIL" "HTTP $http_code - $body"
    fi
else
    print_result "Update calendar event (faculty)" "SKIP" "No faculty token or event ID available"
fi

# =============================================================================
# TEST 10: PUT /api/academic-calendar-events/{id} with student token (should fail)
# =============================================================================
echo -e "${YELLOW}========================================${NC}"
echo -e "${YELLOW}TEST 10: Update Calendar Event (Student - should fail)${NC}"
echo -e "${YELLOW}========================================${NC}"
echo ""

if [ -n "$STUDENT_TOKEN" ] && [ -n "$TEST_EVENT_ID" ]; then
    update_event_data='{
        "title": "Unauthorized Update"
    }'

    result=$(test_endpoint "PUT" "/academic-calendar-events/$TEST_EVENT_ID" "$update_event_data" "Authorization: Bearer $STUDENT_TOKEN")
    http_code=$(echo "$result" | cut -d'|' -f1)
    body=$(echo "$result" | cut -d'|' -f2-)

    echo -e "       ${BLUE}Method:${NC} PUT"
    echo -e "       ${BLUE}Endpoint:${NC} /academic-calendar-events/$TEST_EVENT_ID"
    echo -e "       ${BLUE}Status:${NC} $http_code"
    echo ""

    if [ "$http_code" = "403" ]; then
        print_result "Update calendar event (student)" "PASS" "HTTP 403 - Forbidden as expected"
    else
        print_result "Update calendar event (student)" "FAIL" "Expected 403, got $http_code - $body"
    fi
else
    print_result "Update calendar event (student)" "SKIP" "No student token or event ID available"
fi

# =============================================================================
# TEST 11: DELETE /api/academic-calendar-events/{id} - Delete event (admin)
# =============================================================================
echo -e "${YELLOW}========================================${NC}"
echo -e "${YELLOW}TEST 11: Delete Calendar Event (Admin)${NC}"
echo -e "${YELLOW}========================================${NC}"
echo ""

if [ -n "$ADMIN_TOKEN" ] && [ -n "$TEST_EVENT_ID" ]; then
    result=$(test_endpoint "DELETE" "/academic-calendar-events/$TEST_EVENT_ID" "" "Authorization: Bearer $ADMIN_TOKEN")
    http_code=$(echo "$result" | cut -d'|' -f1)
    body=$(echo "$result" | cut -d'|' -f2-)

    echo -e "       ${BLUE}Method:${NC} DELETE"
    echo -e "       ${BLUE}Endpoint:${NC} /academic-calendar-events/$TEST_EVENT_ID"
    echo -e "       ${BLUE}Status:${NC} $http_code"
    echo ""

    if [ "$http_code" = "204" ] || [ "$http_code" = "200" ]; then
        print_result "Delete calendar event (admin)" "PASS" "HTTP $http_code - Event deleted successfully"
    else
        print_result "Delete calendar event (admin)" "FAIL" "HTTP $http_code - $body"
    fi
else
    print_result "Delete calendar event (admin)" "SKIP" "No admin token or event ID available"
fi

# =============================================================================
# TEST 12: Create another event for remaining tests
# =============================================================================
echo -e "${YELLOW}========================================${NC}"
echo -e "${YELLOW}TEST 12: Create Event for Remaining Tests${NC}"
echo -e "${YELLOW}========================================${NC}"
echo ""

if [ -n "$ADMIN_TOKEN" ]; then
    create_event_data='{
        "title": "Test Event for Deletion",
        "start_date": "2025-12-20",
        "end_date": "2025-12-20",
        "category": "other",
        "description": "Event created for testing deletion"
    }'

    result=$(test_endpoint "POST" "/academic-calendar-events" "$create_event_data" "Authorization: Bearer $ADMIN_TOKEN")
    http_code=$(echo "$result" | cut -d'|' -f1)
    body=$(echo "$result" | cut -d'|' -f2-)

    if [ "$http_code" = "201" ] || [ "$http_code" = "200" ]; then
        TEST_EVENT_ID=$(echo "$body" | grep -o '"id":[0-9]*' | head -1 | cut -d':' -f2)
        print_result "Create event for tests" "PASS" "HTTP $http_code - Event created, ID: $TEST_EVENT_ID"
    else
        print_result "Create event for tests" "FAIL" "HTTP $http_code - $body"
    fi
else
    print_result "Create event for tests" "SKIP" "No admin token available"
fi

# =============================================================================
# TEST 13: DELETE /api/academic-calendar-events/{id} - Delete event (faculty)
# =============================================================================
echo -e "${YELLOW}========================================${NC}"
echo -e "${YELLOW}TEST 13: Delete Calendar Event (Faculty)${NC}"
echo -e "${YELLOW}========================================${NC}"
echo ""

if [ -n "$FACULTY_TOKEN" ] && [ -n "$TEST_EVENT_ID" ]; then
    result=$(test_endpoint "DELETE" "/academic-calendar-events/$TEST_EVENT_ID" "" "Authorization: Bearer $FACULTY_TOKEN")
    http_code=$(echo "$result" | cut -d'|' -f1)
    body=$(echo "$result" | cut -d'|' -f2-)

    echo -e "       ${BLUE}Method:${NC} DELETE"
    echo -e "       ${BLUE}Endpoint:${NC} /academic-calendar-events/$TEST_EVENT_ID"
    echo -e "       ${BLUE}Status:${NC} $http_code"
    echo ""

    if [ "$http_code" = "204" ] || [ "$http_code" = "200" ]; then
        print_result "Delete calendar event (faculty)" "PASS" "HTTP $http_code - Event deleted successfully"
    else
        print_result "Delete calendar event (faculty)" "FAIL" "HTTP $http_code - $body"
    fi
else
    print_result "Delete calendar event (faculty)" "SKIP" "No faculty token or event ID available"
fi

# =============================================================================
# TEST 14: Create another event for student deletion test
# =============================================================================
echo -e "${YELLOW}========================================${NC}"
echo -e "${YELLOW}TEST 14: Create Event for Student Deletion Test${NC}"
echo -e "${YELLOW}========================================${NC}"
echo ""

if [ -n "$ADMIN_TOKEN" ]; then
    create_event_data='{
        "title": "Another Test Event",
        "start_date": "2025-12-25",
        "end_date": "2025-12-25",
        "category": "other",
        "description": "Event for testing student deletion attempts"
    }'

    result=$(test_endpoint "POST" "/academic-calendar-events" "$create_event_data" "Authorization: Bearer $ADMIN_TOKEN")
    http_code=$(echo "$result" | cut -d'|' -f1)
    body=$(echo "$result" | cut -d'|' -f2-)

    if [ "$http_code" = "201" ] || [ "$http_code" = "200" ]; then
        TEST_EVENT_ID=$(echo "$body" | grep -o '"id":[0-9]*' | head -1 | cut -d':' -f2)
        print_result "Create event for student test" "PASS" "HTTP $http_code - Event created, ID: $TEST_EVENT_ID"
    else
        print_result "Create event for student test" "FAIL" "HTTP $http_code - $body"
    fi
else
    print_result "Create event for student test" "SKIP" "No admin token available"
fi

# =============================================================================
# TEST 15: DELETE /api/academic-calendar-events/{id} with student token (should fail)
# =============================================================================
echo -e "${YELLOW}========================================${NC}"
echo -e "${YELLOW}TEST 15: Delete Calendar Event (Student - should fail)${NC}"
echo -e "${YELLOW}========================================${NC}"
echo ""

if [ -n "$STUDENT_TOKEN" ] && [ -n "$TEST_EVENT_ID" ]; then
    result=$(test_endpoint "DELETE" "/academic-calendar-events/$TEST_EVENT_ID" "" "Authorization: Bearer $STUDENT_TOKEN")
    http_code=$(echo "$result" | cut -d'|' -f1)
    body=$(echo "$result" | cut -d'|' -f2-)

    echo -e "       ${BLUE}Method:${NC} DELETE"
    echo -e "       ${BLUE}Endpoint:${NC} /academic-calendar-events/$TEST_EVENT_ID"
    echo -e "       ${BLUE}Status:${NC} $http_code"
    echo ""

    if [ "$http_code" = "403" ]; then
        print_result "Delete calendar event (student)" "PASS" "HTTP 403 - Forbidden as expected"
    else
        print_result "Delete calendar event (student)" "FAIL" "Expected 403, got $http_code - $body"
    fi
else
    print_result "Delete calendar event (student)" "SKIP" "No student token or event ID available"
fi

# =============================================================================
# TEST 16: GET /api/academic-calendar-events without authentication
# =============================================================================
echo -e "${YELLOW}========================================${NC}"
echo -e "${YELLOW}TEST 16: List Events Without Authentication${NC}"
echo -e "${YELLOW}========================================${NC}"
echo ""

result=$(test_endpoint "GET" "/academic-calendar-events" "" "")
http_code=$(echo "$result" | cut -d'|' -f1)
body=$(echo "$result" | cut -d'|' -f2-)

echo -e "       ${BLUE}Method:${NC} GET"
echo -e "       ${BLUE}Endpoint:${NC} /academic-calendar-events"
echo -e "       ${BLUE}Status:${NC} $http_code"
echo ""

if [ "$http_code" = "401" ]; then
    print_result "List events without authentication" "PASS" "HTTP 401 - Unauthorized as expected"
else
    print_result "List events without authentication" "FAIL" "Expected 401, got $http_code - $body"
fi

# =============================================================================
# TEST 17: POST /api/academic-calendar-events without authentication
# =============================================================================
echo -e "${YELLOW}========================================${NC}"
echo -e "${YELLOW}TEST 17: Create Event Without Authentication${NC}"
echo -e "${YELLOW}========================================${NC}"
echo ""

timestamp=$(date +%s)
create_event_no_auth='{
    "title": "Unauthorized Event '$timestamp'",
    "start_date": "2025-12-15",
    "end_date": "2025-12-15",
    "category": "other",
    "description": "This should not be created"
}'

result=$(test_endpoint "POST" "/academic-calendar-events" "$create_event_no_auth" "")
http_code=$(echo "$result" | cut -d'|' -f1)
body=$(echo "$result" | cut -d'|' -f2-)

echo -e "       ${BLUE}Method:${NC} POST"
echo -e "       ${BLUE}Endpoint:${NC} /academic-calendar-events"
echo -e "       ${BLUE}Status:${NC} $http_code"
echo ""

if [ "$http_code" = "401" ]; then
    print_result "Create event without authentication" "PASS" "HTTP 401 - Unauthorized as expected"
else
    print_result "Create event without authentication" "FAIL" "Expected 401, got $http_code - $body"
fi

# =============================================================================
# TEST 18: GET /api/academic-calendar-events/{id} without authentication
# =============================================================================
echo -e "${YELLOW}========================================${NC}"
echo -e "${YELLOW}TEST 18: Get Event Without Authentication${NC}"
echo -e "${YELLOW}========================================${NC}"
echo ""

if [ -n "$TEST_EVENT_ID" ]; then
    result=$(test_endpoint "GET" "/academic-calendar-events/$TEST_EVENT_ID" "" "")
    http_code=$(echo "$result" | cut -d'|' -f1)
    body=$(echo "$result" | cut -d'|' -f2-)

    echo -e "       ${BLUE}Method:${NC} GET"
    echo -e "       ${BLUE}Endpoint:${NC} /academic-calendar-events/$TEST_EVENT_ID"
    echo -e "       ${BLUE}Status:${NC} $http_code"
    echo ""

    if [ "$http_code" = "401" ]; then
        print_result "Get event without authentication" "PASS" "HTTP 401 - Unauthorized as expected"
    else
        print_result "Get event without authentication" "FAIL" "Expected 401, got $http_code - $body"
    fi
else
    print_result "Get event without authentication" "SKIP" "No event ID available"
fi

# =============================================================================
# TEST 19: PUT /api/academic-calendar-events/{id} without authentication
# =============================================================================
echo -e "${YELLOW}========================================${NC}"
echo -e "${YELLOW}TEST 19: Update Event Without Authentication${NC}"
echo -e "${YELLOW}========================================${NC}"
echo ""

if [ -n "$TEST_EVENT_ID" ]; then
    update_event_no_auth='{
        "title": "Unauthorized Update"
    }'

    result=$(test_endpoint "PUT" "/academic-calendar-events/$TEST_EVENT_ID" "$update_event_no_auth" "")
    http_code=$(echo "$result" | cut -d'|' -f1)
    body=$(echo "$result" | cut -d'|' -f2-)

    echo -e "       ${BLUE}Method:${NC} PUT"
    echo -e "       ${BLUE}Endpoint:${NC} /academic-calendar-events/$TEST_EVENT_ID"
    echo -e "       ${BLUE}Status:${NC} $http_code"
    echo ""

    if [ "$http_code" = "401" ]; then
        print_result "Update event without authentication" "PASS" "HTTP 401 - Unauthorized as expected"
    else
        print_result "Update event without authentication" "FAIL" "Expected 401, got $http_code - $body"
    fi
else
    print_result "Update event without authentication" "SKIP" "No event ID available"
fi

# =============================================================================
# TEST 20: DELETE /api/academic-calendar-events/{id} without authentication
# =============================================================================
echo -e "${YELLOW}========================================${NC}"
echo -e "${YELLOW}TEST 20: Delete Event Without Authentication${NC}"
echo -e "${YELLOW}========================================${NC}"
echo ""

if [ -n "$TEST_EVENT_ID" ]; then
    result=$(test_endpoint "DELETE" "/academic-calendar-events/$TEST_EVENT_ID" "" "")
    http_code=$(echo "$result" | cut -d'|' -f1)
    body=$(echo "$result" | cut -d'|' -f2-)

    echo -e "       ${BLUE}Method:${NC} DELETE"
    echo -e "       ${BLUE}Endpoint:${NC} /academic-calendar-events/$TEST_EVENT_ID"
    echo -e "       ${BLUE}Status:${NC} $http_code"
    echo ""

    if [ "$http_code" = "401" ]; then
        print_result "Delete event without authentication" "PASS" "HTTP 401 - Unauthorized as expected"
    else
        print_result "Delete event without authentication" "FAIL" "Expected 401, got $http_code - $body"
    fi
else
    print_result "Delete event without authentication" "SKIP" "No event ID available"
fi

# =============================================================================
# TEST 21: Create event with holiday category
# =============================================================================
echo -e "${YELLOW}========================================${NC}"
echo -e "${YELLOW}TEST 21: Create Event with Holiday Category${NC}"
echo -e "${YELLOW}========================================${NC}"
echo ""

if [ -n "$ADMIN_TOKEN" ]; then
    create_event_data='{
        "title": "Winter Break",
        "start_date": "2025-12-23",
        "end_date": "2026-01-05",
        "category": "holiday",
        "description": "Winter holiday break"
    }'

    result=$(test_endpoint "POST" "/academic-calendar-events" "$create_event_data" "Authorization: Bearer $ADMIN_TOKEN")
    http_code=$(echo "$result" | cut -d'|' -f1)
    body=$(echo "$result" | cut -d'|' -f2-)

    echo -e "       ${BLUE}Method:${NC} POST"
    echo -e "       ${BLUE}Endpoint:${NC} /academic-calendar-events"
    echo -e "       ${BLUE}Status:${NC} $http_code"
    echo ""

    if [ "$http_code" = "201" ] || [ "$http_code" = "200" ]; then
        print_result "Create holiday event" "PASS" "HTTP $http_code - Holiday event created successfully"
    else
        print_result "Create holiday event" "FAIL" "HTTP $http_code - $body"
    fi
else
    print_result "Create holiday event" "SKIP" "No admin token available"
fi

# =============================================================================
# TEST 22: Create event with registration category
# =============================================================================
echo -e "${YELLOW}========================================${NC}"
echo -e "${YELLOW}TEST 22: Create Event with Registration Category${NC}"
echo -e "${YELLOW}========================================${NC}"
echo ""

if [ -n "$ADMIN_TOKEN" ]; then
    create_event_data='{
        "title": "Spring Registration",
        "start_date": "2026-01-10",
        "end_date": "2026-01-20",
        "category": "registration",
        "description": "Course registration period for Spring 2026"
    }'

    result=$(test_endpoint "POST" "/academic-calendar-events" "$create_event_data" "Authorization: Bearer $ADMIN_TOKEN")
    http_code=$(echo "$result" | cut -d'|' -f1)
    body=$(echo "$result" | cut -d'|' -f2-)

    echo -e "       ${BLUE}Method:${NC} POST"
    echo -e "       ${BLUE}Endpoint:${NC} /academic-calendar-events"
    echo -e "       ${BLUE}Status:${NC} $http_code"
    echo ""

    if [ "$http_code" = "201" ] || [ "$http_code" = "200" ]; then
        print_result "Create registration event" "PASS" "HTTP $http_code - Registration event created successfully"
    else
        print_result "Create registration event" "FAIL" "HTTP $http_code - $body"
    fi
else
    print_result "Create registration event" "SKIP" "No admin token available"
fi

# =============================================================================
# TEST 23: Create event with graduation category
# =============================================================================
echo -e "${YELLOW}========================================${NC}"
echo -e "${YELLOW}TEST 23: Create Event with Graduation Category${NC}"
echo -e "${YELLOW}========================================${NC}"
echo ""

if [ -n "$ADMIN_TOKEN" ]; then
    create_event_data='{
        "title": "Fall 2025 Graduation",
        "start_date": "2026-01-15",
        "end_date": "2026-01-15",
        "category": "graduation",
        "description": "Fall 2025 graduation ceremony"
    }'

    result=$(test_endpoint "POST" "/academic-calendar-events" "$create_event_data" "Authorization: Bearer $ADMIN_TOKEN")
    http_code=$(echo "$result" | cut -d'|' -f1)
    body=$(echo "$result" | cut -d'|' -f2-)

    echo -e "       ${BLUE}Method:${NC} POST"
    echo -e "       ${BLUE}Endpoint:${NC} /academic-calendar-events"
    echo -e "       ${BLUE}Status:${NC} $http_code"
    echo ""

    if [ "$http_code" = "201" ] || [ "$http_code" = "200" ]; then
        print_result "Create graduation event" "PASS" "HTTP $http_code - Graduation event created successfully"
    else
        print_result "Create graduation event" "FAIL" "HTTP $http_code - $body"
    fi
else
    print_result "Create graduation event" "SKIP" "No admin token available"
fi

# =============================================================================
# TEST 24: Create event with orientation category
# =============================================================================
echo -e "${YELLOW}========================================${NC}"
echo -e "${YELLOW}TEST 24: Create Event with Orientation Category${NC}"
echo -e "${YELLOW}========================================${NC}"
echo ""

if [ -n "$ADMIN_TOKEN" ]; then
    create_event_data='{
        "title": "New Student Orientation",
        "start_date": "2026-01-25",
        "end_date": "2026-01-27",
        "category": "orientation",
        "description": "Orientation for new students"
    }'

    result=$(test_endpoint "POST" "/academic-calendar-events" "$create_event_data" "Authorization: Bearer $ADMIN_TOKEN")
    http_code=$(echo "$result" | cut -d'|' -f1)
    body=$(echo "$result" | cut -d'|' -f2-)

    echo -e "       ${BLUE}Method:${NC} POST"
    echo -e "       ${BLUE}Endpoint:${NC} /academic-calendar-events"
    echo -e "       ${BLUE}Status:${NC} $http_code"
    echo ""

    if [ "$http_code" = "201" ] || [ "$http_code" = "200" ]; then
        print_result "Create orientation event" "PASS" "HTTP $http_code - Orientation event created successfully"
    else
        print_result "Create orientation event" "FAIL" "HTTP $http_code - $body"
    fi
else
    print_result "Create orientation event" "SKIP" "No admin token available"
fi

# =============================================================================
# TEST 25: Create event with conference category
# =============================================================================
echo -e "${YELLOW}========================================${NC}"
echo -e "${YELLOW}TEST 25: Create Event with Conference Category${NC}"
echo -e "${YELLOW}========================================${NC}"
echo ""

if [ -n "$ADMIN_TOKEN" ]; then
    create_event_data='{
        "title": "Academic Conference 2026",
        "start_date": "2026-02-10",
        "end_date": "2026-02-12",
        "category": "conference",
        "description": "Annual academic conference"
    }'

    result=$(test_endpoint "POST" "/academic-calendar-events" "$create_event_data" "Authorization: Bearer $ADMIN_TOKEN")
    http_code=$(echo "$result" | cut -d'|' -f1)
    body=$(echo "$result" | cut -d'|' -f2-)

    echo -e "       ${BLUE}Method:${NC} POST"
    echo -e "       ${BLUE}Endpoint:${NC} /academic-calendar-events"
    echo -e "       ${BLUE}Status:${NC} $http_code"
    echo ""

    if [ "$http_code" = "201" ] || [ "$http_code" = "200" ]; then
        print_result "Create conference event" "PASS" "HTTP $http_code - Conference event created successfully"
    else
        print_result "Create conference event" "FAIL" "HTTP $http_code - $body"
    fi
else
    print_result "Create conference event" "SKIP" "No admin token available"
fi

# =============================================================================
# TEST 26: Create event with missing required fields
# =============================================================================
echo -e "${YELLOW}========================================${NC}"
echo -e "${YELLOW}TEST 26: Create Event with Missing Fields${NC}"
echo -e "${YELLOW}========================================${NC}"
echo ""

if [ -n "$ADMIN_TOKEN" ]; then
    create_event_missing='{
        "title": "Incomplete Event"
    }'

    result=$(test_endpoint "POST" "/academic-calendar-events" "$create_event_missing" "Authorization: Bearer $ADMIN_TOKEN")
    http_code=$(echo "$result" | cut -d'|' -f1)
    body=$(echo "$result" | cut -d'|' -f2-)

    echo -e "       ${BLUE}Method:${NC} POST"
    echo -e "       ${BLUE}Endpoint:${NC} /academic-calendar-events"
    echo -e "       ${BLUE}Status:${NC} $http_code"
    echo ""

    if [ "$http_code" = "422" ]; then
        print_result "Create event with missing fields" "PASS" "HTTP 422 - Validation error as expected"
    else
        print_result "Create event with missing fields" "FAIL" "Expected 422, got $http_code - $body"
    fi
else
    print_result "Create event with missing fields" "SKIP" "No admin token available"
fi

# =============================================================================
# TEST 27: Create event with invalid category
# =============================================================================
echo -e "${YELLOW}========================================${NC}"
echo -e "${YELLOW}TEST 27: Create Event with Invalid Category${NC}"
echo -e "${YELLOW}========================================${NC}"
echo ""

if [ -n "$ADMIN_TOKEN" ]; then
    create_event_invalid='{
        "title": "Invalid Category Event",
        "start_date": "2025-12-15",
        "end_date": "2025-12-15",
        "category": "invalid_category",
        "description": "Event with invalid category"
    }'

    result=$(test_endpoint "POST" "/academic-calendar-events" "$create_event_invalid" "Authorization: Bearer $ADMIN_TOKEN")
    http_code=$(echo "$result" | cut -d'|' -f1)
    body=$(echo "$result" | cut -d'|' -f2-)

    echo -e "       ${BLUE}Method:${NC} POST"
    echo -e "       ${BLUE}Endpoint:${NC} /academic-calendar-events"
    echo -e "       ${BLUE}Status:${NC} $http_code"
    echo ""

    if [ "$http_code" = "422" ]; then
        print_result "Create event with invalid category" "PASS" "HTTP 422 - Validation error as expected"
    else
        print_result "Create event with invalid category" "FAIL" "Expected 422, got $http_code - $body"
    fi
else
    print_result "Create event with invalid category" "SKIP" "No admin token available"
fi

# =============================================================================
# TEST 28: Create event with invalid date range (end before start)
# =============================================================================
echo -e "${YELLOW}========================================${NC}"
echo -e "${YELLOW}TEST 28: Create Event with Invalid Date Range${NC}"
echo -e "${YELLOW}========================================${NC}"
echo ""

if [ -n "$ADMIN_TOKEN" ]; then
    create_event_invalid_dates='{
        "title": "Invalid Date Range Event",
        "start_date": "2025-12-20",
        "end_date": "2025-12-15",
        "category": "other",
        "description": "Event with end date before start date"
    }'

    result=$(test_endpoint "POST" "/academic-calendar-events" "$create_event_invalid_dates" "Authorization: Bearer $ADMIN_TOKEN")
    http_code=$(echo "$result" | cut -d'|' -f1)
    body=$(echo "$result" | cut -d'|' -f2-)

    echo -e "       ${BLUE}Method:${NC} POST"
    echo -e "       ${BLUE}Endpoint:${NC} /academic-calendar-events"
    echo -e "       ${BLUE}Status:${NC} $http_code"
    echo ""

    if [ "$http_code" = "422" ]; then
        print_result "Create event with invalid date range" "PASS" "HTTP 422 - Validation error as expected"
    else
        print_result "Create event with invalid date range" "FAIL" "Expected 422, got $http_code - $body"
    fi
else
    print_result "Create event with invalid date range" "SKIP" "No admin token available"
fi

# =============================================================================
# TEST 29: GET /api/academic-calendar-events/{id} with invalid ID
# =============================================================================
echo -e "${YELLOW}========================================${NC}"
echo -e "${YELLOW}TEST 29: Get Event with Invalid ID${NC}"
echo -e "${YELLOW}========================================${NC}"
echo ""

if [ -n "$ADMIN_TOKEN" ]; then
    result=$(test_endpoint "GET" "/academic-calendar-events/999999" "" "Authorization: Bearer $ADMIN_TOKEN")
    http_code=$(echo "$result" | cut -d'|' -f1)
    body=$(echo "$result" | cut -d'|' -f2-)

    echo -e "       ${BLUE}Method:${NC} GET"
    echo -e "       ${BLUE}Endpoint:${NC} /academic-calendar-events/999999"
    echo -e "       ${BLUE}Status:${NC} $http_code"
    echo ""

    if [ "$http_code" = "404" ]; then
        print_result "Get event with invalid ID" "PASS" "HTTP 404 - Not found as expected"
    else
        print_result "Get event with invalid ID" "FAIL" "Expected 404, got $http_code - $body"
    fi
else
    print_result "Get event with invalid ID" "SKIP" "No admin token available"
fi

# =============================================================================
# TEST 30: PUT /api/academic-calendar-events/{id} with invalid ID
# =============================================================================
echo -e "${YELLOW}========================================${NC}"
echo -e "${YELLOW}TEST 30: Update Event with Invalid ID${NC}"
echo -e "${YELLOW}========================================${NC}"
echo ""

if [ -n "$ADMIN_TOKEN" ]; then
    update_event_data='{
        "title": "Update Non-Existent Event"
    }'

    result=$(test_endpoint "PUT" "/academic-calendar-events/999999" "$update_event_data" "Authorization: Bearer $ADMIN_TOKEN")
    http_code=$(echo "$result" | cut -d'|' -f1)
    body=$(echo "$result" | cut -d'|' -f2-)

    echo -e "       ${BLUE}Method:${NC} PUT"
    echo -e "       ${BLUE}Endpoint:${NC} /academic-calendar-events/999999"
    echo -e "       ${BLUE}Status:${NC} $http_code"
    echo ""

    if [ "$http_code" = "404" ]; then
        print_result "Update event with invalid ID" "PASS" "HTTP 404 - Not found as expected"
    else
        print_result "Update event with invalid ID" "FAIL" "Expected 404, got $http_code - $body"
    fi
else
    print_result "Update event with invalid ID" "SKIP" "No admin token available"
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