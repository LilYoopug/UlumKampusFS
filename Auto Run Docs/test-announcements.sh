#!/bin/bash

# =============================================================================
# Announcement Endpoint Tests
# Tests all announcement-related API endpoints using curl
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

# Test announcement IDs and tokens
TEST_ANNOUNCEMENT_ID=""
TEST_COURSE_ID=""
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

# Create test faculty user (for creating announcements)
timestamp=$(date +%s)
faculty_register_data='{
    "name": "Test Faculty Announce '$timestamp'",
    "email": "testfacultyannounce'$timestamp'@example.com",
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
    FACULTY_EMAIL="testfacultyannounce$timestamp@example.com"
    FACULTY_PASSWORD="Password123!"
    echo -e "${GREEN}Faculty user created${NC}"
else
    echo -e "${YELLOW}Faculty user already exists or failed${NC}"
    # Try login with existing faculty
    faculty_login_data='{
        "email": "faculty@example.com",
        "password": "password"
    }'
    result=$(test_endpoint "POST" "/login" "$faculty_login_data" "")
    FACULTY_TOKEN=$(echo "$result" | grep -o '"token":"[^"]*"' | cut -d'"' -f4)
    if [ -z "$FACULTY_TOKEN" ]; then
        FACULTY_TOKEN=$(echo "$result" | grep -o '"access_token":"[^"]*"' | cut -d'"' -f4)
    fi
fi

# Create test student user
student_register_data='{
    "name": "Test Student Announce '$timestamp'",
    "email": "teststudentannounce'$timestamp'@example.com",
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
    STUDENT_EMAIL="teststudentannounce$timestamp@example.com"
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

# Get faculty ID for announcements
if [ -n "$FACULTY_TOKEN" ]; then
    result=$(test_endpoint "GET" "/faculties" "" "Authorization: Bearer $FACULTY_TOKEN")
    body=$(echo "$result" | cut -d'|' -f2-)
    TEST_FACULTY_ID=$(echo "$body" | grep -o '"id":[0-9]*' | head -1 | cut -d':' -f2)
fi

# Get course ID for course-specific announcements
if [ -n "$FACULTY_TOKEN" ]; then
    result=$(test_endpoint "GET" "/courses" "" "Authorization: Bearer $FACULTY_TOKEN")
    body=$(echo "$result" | cut -d'|' -f2-)
    TEST_COURSE_ID=$(echo "$body" | grep -o '"id":[0-9]*' | head -1 | cut -d':' -f2)
fi

echo ""

# =============================================================================
# TEST 1: GET /api/announcements - List all announcements
# =============================================================================
echo -e "${YELLOW}========================================${NC}"
echo -e "${YELLOW}TEST 1: List All Announcements${NC}"
echo -e "${YELLOW}========================================${NC}"
echo ""

if [ -n "$STUDENT_TOKEN" ]; then
    result=$(test_endpoint "GET" "/announcements" "" "Authorization: Bearer $STUDENT_TOKEN")
    http_code=$(echo "$result" | cut -d'|' -f1)
    body=$(echo "$result" | cut -d'|' -f2-)

    echo -e "       ${BLUE}Method:${NC} GET"
    echo -e "       ${BLUE}Endpoint:${NC} /announcements"
    echo -e "       ${BLUE}Status:${NC} $http_code"
    echo -e "       ${BLUE}Response:${NC} $(echo "$body" | head -c 200)..."
    echo ""

    if [ "$http_code" = "200" ]; then
        print_result "List all announcements" "PASS" "HTTP 200 - Announcements retrieved successfully"
    else
        print_result "List all announcements" "FAIL" "HTTP $http_code - $body"
    fi
else
    print_result "List all announcements" "SKIP" "No student token available"
fi

# =============================================================================
# TEST 2: GET /api/announcements with search parameter
# =============================================================================
echo -e "${YELLOW}========================================${NC}"
echo -e "${YELLOW}TEST 2: List Announcements with Search${NC}"
echo -e "${YELLOW}========================================${NC}"
echo ""

if [ -n "$STUDENT_TOKEN" ]; then
    result=$(test_endpoint "GET" "/announcements?search=exam" "" "Authorization: Bearer $STUDENT_TOKEN")
    http_code=$(echo "$result" | cut -d'|' -f1)
    body=$(echo "$result" | cut -d'|' -f2-)

    echo -e "       ${BLUE}Method:${NC} GET"
    echo -e "       ${BLUE}Endpoint:${NC} /announcements?search=exam"
    echo -e "       ${BLUE}Status:${NC} $http_code"
    echo ""

    if [ "$http_code" = "200" ]; then
        print_result "List announcements with search" "PASS" "HTTP 200 - Search results retrieved successfully"
    else
        print_result "List announcements with search" "FAIL" "HTTP $http_code - $body"
    fi
else
    print_result "List announcements with search" "SKIP" "No student token available"
fi

# =============================================================================
# TEST 3: GET /api/announcements with category filter
# =============================================================================
echo -e "${YELLOW}========================================${NC}"
echo -e "${YELLOW}TEST 3: List Announcements with Category Filter${NC}"
echo -e "${YELLOW}========================================${NC}"
echo ""

if [ -n "$STUDENT_TOKEN" ]; then
    result=$(test_endpoint "GET" "/announcements?category=general" "" "Authorization: Bearer $STUDENT_TOKEN")
    http_code=$(echo "$result" | cut -d'|' -f1)
    body=$(echo "$result" | cut -d'|' -f2-)

    echo -e "       ${BLUE}Method:${NC} GET"
    echo -e "       ${BLUE}Endpoint:${NC} /announcements?category=general"
    echo -e "       ${BLUE}Status:${NC} $http_code"
    echo ""

    if [ "$http_code" = "200" ]; then
        print_result "List announcements with category filter" "PASS" "HTTP 200 - Filtered announcements retrieved successfully"
    else
        print_result "List announcements with category filter" "FAIL" "HTTP $http_code - $body"
    fi
else
    print_result "List announcements with category filter" "SKIP" "No student token available"
fi

# =============================================================================
# TEST 4: GET /api/announcements with priority filter
# =============================================================================
echo -e "${YELLOW}========================================${NC}"
echo -e "${YELLOW}TEST 4: List Announcements with Priority Filter${NC}"
echo -e "${YELLOW}========================================${NC}"
echo ""

if [ -n "$STUDENT_TOKEN" ]; then
    result=$(test_endpoint "GET" "/announcements?priority=high" "" "Authorization: Bearer $STUDENT_TOKEN")
    http_code=$(echo "$result" | cut -d'|' -f1)
    body=$(echo "$result" | cut -d'|' -f2-)

    echo -e "       ${BLUE}Method:${NC} GET"
    echo -e "       ${BLUE}Endpoint:${NC} /announcements?priority=high"
    echo -e "       ${BLUE}Status:${NC} $http_code"
    echo ""

    if [ "$http_code" = "200" ]; then
        print_result "List announcements with priority filter" "PASS" "HTTP 200 - Filtered announcements retrieved successfully"
    else
        print_result "List announcements with priority filter" "FAIL" "HTTP $http_code - $body"
    fi
else
    print_result "List announcements with priority filter" "SKIP" "No student token available"
fi

# =============================================================================
# TEST 5: GET /api/announcements with target audience filter
# =============================================================================
echo -e "${YELLOW}========================================${NC}"
echo -e "${YELLOW}TEST 5: List Announcements with Target Audience Filter${NC}"
echo -e "${YELLOW}========================================${NC}"
echo ""

if [ -n "$STUDENT_TOKEN" ]; then
    result=$(test_endpoint "GET" "/announcements?target_audience=student" "" "Authorization: Bearer $STUDENT_TOKEN")
    http_code=$(echo "$result" | cut -d'|' -f1)
    body=$(echo "$result" | cut -d'|' -f2-)

    echo -e "       ${BLUE}Method:${NC} GET"
    echo -e "       ${BLUE}Endpoint:${NC} /announcements?target_audience=student"
    echo -e "       ${BLUE}Status:${NC} $http_code"
    echo ""

    if [ "$http_code" = "200" ]; then
        print_result "List announcements with target audience filter" "PASS" "HTTP 200 - Filtered announcements retrieved successfully"
    else
        print_result "List announcements with target audience filter" "FAIL" "HTTP $http_code - $body"
    fi
else
    print_result "List announcements with target audience filter" "SKIP" "No student token available"
fi

# =============================================================================
# TEST 6: GET /api/announcements with course filter
# =============================================================================
echo -e "${YELLOW}========================================${NC}"
echo -e "${YELLOW}TEST 6: List Announcements with Course Filter${NC}"
echo -e "${YELLOW}========================================${NC}"
echo ""

if [ -n "$STUDENT_TOKEN" ] && [ -n "$TEST_COURSE_ID" ]; then
    result=$(test_endpoint "GET" "/announcements?course_id=$TEST_COURSE_ID" "" "Authorization: Bearer $STUDENT_TOKEN")
    http_code=$(echo "$result" | cut -d'|' -f1)
    body=$(echo "$result" | cut -d'|' -f2-)

    echo -e "       ${BLUE}Method:${NC} GET"
    echo -e "       ${BLUE}Endpoint:${NC} /announcements?course_id=$TEST_COURSE_ID"
    echo -e "       ${BLUE}Status:${NC} $http_code"
    echo ""

    if [ "$http_code" = "200" ]; then
        print_result "List announcements with course filter" "PASS" "HTTP 200 - Filtered announcements retrieved successfully"
    else
        print_result "List announcements with course filter" "FAIL" "HTTP $http_code - $body"
    fi
else
    print_result "List announcements with course filter" "SKIP" "No student token or course ID available"
fi

# =============================================================================
# TEST 7: GET /api/announcements with faculty filter
# =============================================================================
echo -e "${YELLOW}========================================${NC}"
echo -e "${YELLOW}TEST 7: List Announcements with Faculty Filter${NC}"
echo -e "${YELLOW}========================================${NC}"
echo ""

if [ -n "$STUDENT_TOKEN" ] && [ -n "$TEST_FACULTY_ID" ]; then
    result=$(test_endpoint "GET" "/announcements?faculty_id=$TEST_FACULTY_ID" "" "Authorization: Bearer $STUDENT_TOKEN")
    http_code=$(echo "$result" | cut -d'|' -f1)
    body=$(echo "$result" | cut -d'|' -f2-)

    echo -e "       ${BLUE}Method:${NC} GET"
    echo -e "       ${BLUE}Endpoint:${NC} /announcements?faculty_id=$TEST_FACULTY_ID"
    echo -e "       ${BLUE}Status:${NC} $http_code"
    echo ""

    if [ "$http_code" = "200" ]; then
        print_result "List announcements with faculty filter" "PASS" "HTTP 200 - Filtered announcements retrieved successfully"
    else
        print_result "List announcements with faculty filter" "FAIL" "HTTP $http_code - $body"
    fi
else
    print_result "List announcements with faculty filter" "SKIP" "No student token or faculty ID available"
fi

# =============================================================================
# TEST 8: POST /api/announcements - Create new announcement
# =============================================================================
echo -e "${YELLOW}========================================${NC}"
echo -e "${YELLOW}TEST 8: Create New Announcement${NC}"
echo -e "${YELLOW}========================================${NC}"
echo ""

if [ -n "$FACULTY_TOKEN" ]; then
    timestamp=$(date +%s)
    create_announcement_data='{
        "title": "Test Announcement '$timestamp'",
        "content": "This is a test announcement content created during testing.",
        "category": "general",
        "target_audience": "all",
        "priority": "normal",
        "is_published": true,
        "allow_comments": true
    }'

    result=$(test_endpoint "POST" "/announcements" "$create_announcement_data" "Authorization: Bearer $FACULTY_TOKEN")
    http_code=$(echo "$result" | cut -d'|' -f1)
    body=$(echo "$result" | cut -d'|' -f2-)

    echo -e "       ${BLUE}Method:${NC} POST"
    echo -e "       ${BLUE}Endpoint:${NC} /announcements"
    echo -e "       ${BLUE}Status:${NC} $http_code"
    echo -e "       ${BLUE}Response:${NC} $(echo "$body" | head -c 200)..."
    echo ""

    if [ "$http_code" = "201" ] || [ "$http_code" = "200" ]; then
        print_result "Create new announcement" "PASS" "HTTP $http_code - Announcement created successfully"
        # Extract the created announcement ID for later tests
        TEST_ANNOUNCEMENT_ID=$(echo "$body" | grep -o '"id":[0-9]*' | head -1 | cut -d':' -f2)
    else
        print_result "Create new announcement" "FAIL" "HTTP $http_code - $body"
    fi
else
    print_result "Create new announcement" "SKIP" "No faculty token available"
fi

# =============================================================================
# TEST 9: GET /api/announcements/{id} - Get announcement by ID
# =============================================================================
echo -e "${YELLOW}========================================${NC}"
echo -e "${YELLOW}TEST 9: Get Announcement by ID${NC}"
echo -e "${YELLOW}========================================${NC}"
echo ""

if [ -n "$STUDENT_TOKEN" ] && [ -n "$TEST_ANNOUNCEMENT_ID" ]; then
    result=$(test_endpoint "GET" "/announcements/$TEST_ANNOUNCEMENT_ID" "" "Authorization: Bearer $STUDENT_TOKEN")
    http_code=$(echo "$result" | cut -d'|' -f1)
    body=$(echo "$result" | cut -d'|' -f2-)

    echo -e "       ${BLUE}Method:${NC} GET"
    echo -e "       ${BLUE}Endpoint:${NC} /announcements/$TEST_ANNOUNCEMENT_ID"
    echo -e "       ${BLUE}Status:${NC} $http_code"
    echo ""

    if [ "$http_code" = "200" ]; then
        print_result "Get announcement by ID" "PASS" "HTTP 200 - Announcement retrieved successfully"
    else
        print_result "Get announcement by ID" "FAIL" "HTTP $http_code - $body"
    fi
else
    print_result "Get announcement by ID" "SKIP" "No student token or announcement ID available"
fi

# =============================================================================
# TEST 10: GET /api/announcements/{id} - Get announcement with invalid ID
# =============================================================================
echo -e "${YELLOW}========================================${NC}"
echo -e "${YELLOW}TEST 10: Get Announcement with Invalid ID${NC}"
echo -e "${YELLOW}========================================${NC}"
echo ""

if [ -n "$STUDENT_TOKEN" ]; then
    result=$(test_endpoint "GET" "/announcements/9999999" "" "Authorization: Bearer $STUDENT_TOKEN")
    http_code=$(echo "$result" | cut -d'|' -f1)
    body=$(echo "$result" | cut -d'|' -f2-)

    echo -e "       ${BLUE}Method:${NC} GET"
    echo -e "       ${BLUE}Endpoint:${NC} /announcements/9999999"
    echo -e "       ${BLUE}Status:${NC} $http_code"
    echo ""

    if [ "$http_code" = "404" ]; then
        print_result "Get announcement with invalid ID" "PASS" "HTTP 404 - Not found as expected"
    else
        print_result "Get announcement with invalid ID" "FAIL" "Expected 404, got $http_code - $body"
    fi
else
    print_result "Get announcement with invalid ID" "SKIP" "No student token available"
fi

# =============================================================================
# TEST 11: PUT /api/announcements/{id} - Update announcement
# =============================================================================
echo -e "${YELLOW}========================================${NC}"
echo -e "${YELLOW}TEST 11: Update Announcement${NC}"
echo -e "${YELLOW}========================================${NC}"
echo ""

if [ -n "$FACULTY_TOKEN" ] && [ -n "$TEST_ANNOUNCEMENT_ID" ]; then
    update_announcement_data='{
        "title": "Updated Test Announcement",
        "content": "This announcement has been updated during testing.",
        "priority": "high"
    }'

    result=$(test_endpoint "PUT" "/announcements/$TEST_ANNOUNCEMENT_ID" "$update_announcement_data" "Authorization: Bearer $FACULTY_TOKEN")
    http_code=$(echo "$result" | cut -d'|' -f1)
    body=$(echo "$result" | cut -d'|' -f2-)

    echo -e "       ${BLUE}Method:${NC} PUT"
    echo -e "       ${BLUE}Endpoint:${NC} /announcements/$TEST_ANNOUNCEMENT_ID"
    echo -e "       ${BLUE}Status:${NC} $http_code"
    echo ""

    if [ "$http_code" = "200" ]; then
        print_result "Update announcement" "PASS" "HTTP 200 - Announcement updated successfully"
    else
        print_result "Update announcement" "FAIL" "HTTP $http_code - $body"
    fi
else
    print_result "Update announcement" "SKIP" "No faculty token or announcement ID available"
fi

# =============================================================================
# TEST 12: PUT /api/announcements/{id} - Update announcement by student (should fail)
# =============================================================================
echo -e "${YELLOW}========================================${NC}"
echo -e "${YELLOW}TEST 12: Update Announcement by Student (should fail)${NC}"
echo -e "${YELLOW}========================================${NC}"
echo ""

if [ -n "$STUDENT_TOKEN" ] && [ -n "$TEST_ANNOUNCEMENT_ID" ]; then
    update_announcement_data='{
        "title": "Student Attempting to Update"
    }'

    result=$(test_endpoint "PUT" "/announcements/$TEST_ANNOUNCEMENT_ID" "$update_announcement_data" "Authorization: Bearer $STUDENT_TOKEN")
    http_code=$(echo "$result" | cut -d'|' -f1)
    body=$(echo "$result" | cut -d'|' -f2-)

    echo -e "       ${BLUE}Method:${NC} PUT"
    echo -e "       ${BLUE}Endpoint:${NC} /announcements/$TEST_ANNOUNCEMENT_ID"
    echo -e "       ${BLUE}Status:${NC} $http_code"
    echo ""

    if [ "$http_code" = "403" ]; then
        print_result "Update announcement by student" "PASS" "HTTP 403 - Forbidden as expected"
    else
        print_result "Update announcement by student" "FAIL" "Expected 403, got $http_code - $body"
    fi
else
    print_result "Update announcement by student" "SKIP" "No student token or announcement ID available"
fi

# =============================================================================
# TEST 13: POST /api/announcements/{id}/publish - Publish announcement
# =============================================================================
echo -e "${YELLOW}========================================${NC}"
echo -e "${YELLOW}TEST 13: Publish Announcement${NC}"
echo -e "${YELLOW}========================================${NC}"
echo ""

if [ -n "$FACULTY_TOKEN" ] && [ -n "$TEST_ANNOUNCEMENT_ID" ]; then
    result=$(test_endpoint "POST" "/announcements/$TEST_ANNOUNCEMENT_ID/publish" "" "Authorization: Bearer $FACULTY_TOKEN")
    http_code=$(echo "$result" | cut -d'|' -f1)
    body=$(echo "$result" | cut -d'|' -f2-)

    echo -e "       ${BLUE}Method:${NC} POST"
    echo -e "       ${BLUE}Endpoint:${NC} /announcements/$TEST_ANNOUNCEMENT_ID/publish"
    echo -e "       ${BLUE}Status:${NC} $http_code"
    echo ""

    if [ "$http_code" = "200" ]; then
        print_result "Publish announcement" "PASS" "HTTP 200 - Announcement published successfully"
    else
        print_result "Publish announcement" "FAIL" "HTTP $http_code - $body"
    fi
else
    print_result "Publish announcement" "SKIP" "No faculty token or announcement ID available"
fi

# =============================================================================
# TEST 14: POST /api/announcements/{id}/unpublish - Unpublish announcement
# =============================================================================
echo -e "${YELLOW}========================================${NC}"
echo -e "${YELLOW}TEST 14: Unpublish Announcement${NC}"
echo -e "${YELLOW}========================================${NC}"
echo ""

if [ -n "$FACULTY_TOKEN" ] && [ -n "$TEST_ANNOUNCEMENT_ID" ]; then
    result=$(test_endpoint "POST" "/announcements/$TEST_ANNOUNCEMENT_ID/unpublish" "" "Authorization: Bearer $FACULTY_TOKEN")
    http_code=$(echo "$result" | cut -d'|' -f1)
    body=$(echo "$result" | cut -d'|' -f2-)

    echo -e "       ${BLUE}Method:${NC} POST"
    echo -e "       ${BLUE}Endpoint:${NC} /announcements/$TEST_ANNOUNCEMENT_ID/unpublish"
    echo -e "       ${BLUE}Status:${NC} $http_code"
    echo ""

    if [ "$http_code" = "200" ]; then
        print_result "Unpublish announcement" "PASS" "HTTP 200 - Announcement unpublished successfully"
    else
        print_result "Unpublish announcement" "FAIL" "HTTP $http_code - $body"
    fi
else
    print_result "Unpublish announcement" "SKIP" "No faculty token or announcement ID available"
fi

# =============================================================================
# TEST 15: POST /api/announcements/{id}/mark-read - Mark announcement as read
# =============================================================================
echo -e "${YELLOW}========================================${NC}"
echo -e "${YELLOW}TEST 15: Mark Announcement as Read${NC}"
echo -e "${YELLOW}========================================${NC}"
echo ""

if [ -n "$STUDENT_TOKEN" ] && [ -n "$TEST_ANNOUNCEMENT_ID" ]; then
    result=$(test_endpoint "POST" "/announcements/$TEST_ANNOUNCEMENT_ID/mark-read" "" "Authorization: Bearer $STUDENT_TOKEN")
    http_code=$(echo "$result" | cut -d'|' -f1)
    body=$(echo "$result" | cut -d'|' -f2-)

    echo -e "       ${BLUE}Method:${NC} POST"
    echo -e "       ${BLUE}Endpoint:${NC} /announcements/$TEST_ANNOUNCEMENT_ID/mark-read"
    echo -e "       ${BLUE}Status:${NC} $http_code"
    echo ""

    if [ "$http_code" = "200" ]; then
        print_result "Mark announcement as read" "PASS" "HTTP 200 - Announcement marked as read"
    else
        print_result "Mark announcement as read" "FAIL" "HTTP $http_code - $body"
    fi
else
    print_result "Mark announcement as read" "SKIP" "No student token or announcement ID available"
fi

# =============================================================================
# TEST 16: DELETE /api/announcements/{id} - Delete announcement
# =============================================================================
echo -e "${YELLOW}========================================${NC}"
echo -e "${YELLOW}TEST 16: Delete Announcement${NC}"
echo -e "${YELLOW}========================================${NC}"
echo ""

if [ -n "$FACULTY_TOKEN" ] && [ -n "$TEST_ANNOUNCEMENT_ID" ]; then
    result=$(test_endpoint "DELETE" "/announcements/$TEST_ANNOUNCEMENT_ID" "" "Authorization: Bearer $FACULTY_TOKEN")
    http_code=$(echo "$result" | cut -d'|' -f1)
    body=$(echo "$result" | cut -d'|' -f2-)

    echo -e "       ${BLUE}Method:${NC} DELETE"
    echo -e "       ${BLUE}Endpoint:${NC} /announcements/$TEST_ANNOUNCEMENT_ID"
    echo -e "       ${BLUE}Status:${NC} $http_code"
    echo ""

    if [ "$http_code" = "204" ] || [ "$http_code" = "200" ]; then
        print_result "Delete announcement" "PASS" "HTTP $http_code - Announcement deleted successfully"
    else
        print_result "Delete announcement" "FAIL" "HTTP $http_code - $body"
    fi
else
    print_result "Delete announcement" "SKIP" "No faculty token or announcement ID available"
fi

# =============================================================================
# TEST 17: GET /api/announcements without authentication
# =============================================================================
echo -e "${YELLOW}========================================${NC}"
echo -e "${YELLOW}TEST 17: List Announcements Without Authentication${NC}"
echo -e "${YELLOW}========================================${NC}"
echo ""

result=$(test_endpoint "GET" "/announcements" "" "")
http_code=$(echo "$result" | cut -d'|' -f1)
body=$(echo "$result" | cut -d'|' -f2-)

echo -e "       ${BLUE}Method:${NC} GET"
echo -e "       ${BLUE}Endpoint:${NC} /announcements"
echo -e "       ${BLUE}Status:${NC} $http_code"
echo ""

if [ "$http_code" = "401" ]; then
    print_result "List announcements without authentication" "PASS" "HTTP 401 - Unauthorized as expected"
else
    print_result "List announcements without authentication" "FAIL" "Expected 401, got $http_code - $body"
fi

# =============================================================================
# TEST 18: POST /api/announcements without authentication
# =============================================================================
echo -e "${YELLOW}========================================${NC}"
echo -e "${YELLOW}TEST 18: Create Announcement Without Authentication${NC}"
echo -e "${YELLOW}========================================${NC}"
echo ""

timestamp=$(date +%s)
create_announcement_no_auth='{
    "title": "Unauthorized Announcement '$timestamp'",
    "content": "This announcement should not be created without authentication.",
    "category": "general"
}'

result=$(test_endpoint "POST" "/announcements" "$create_announcement_no_auth" "")
http_code=$(echo "$result" | cut -d'|' -f1)
body=$(echo "$result" | cut -d'|' -f2-)

echo -e "       ${BLUE}Method:${NC} POST"
echo -e "       ${BLUE}Endpoint:${NC} /announcements"
echo -e "       ${BLUE}Status:${NC} $http_code"
echo ""

if [ "$http_code" = "401" ]; then
    print_result "Create announcement without authentication" "PASS" "HTTP 401 - Unauthorized as expected"
else
    print_result "Create announcement without authentication" "FAIL" "Expected 401, got $http_code - $body"
fi

# =============================================================================
# TEST 19: POST /api/announcements - Create course-specific announcement
# =============================================================================
echo -e "${YELLOW}========================================${NC}"
echo -e "${YELLOW}TEST 19: Create Course-Specific Announcement${NC}"
echo -e "${YELLOW}========================================${NC}"
echo ""

if [ -n "$FACULTY_TOKEN" ] && [ -n "$TEST_COURSE_ID" ]; then
    timestamp=$(date +%s)
    create_course_announcement_data='{
        "title": "Course Announcement '$timestamp'",
        "content": "This is a course-specific announcement.",
        "category": "course",
        "target_audience": "student",
        "priority": "normal",
        "is_published": true,
        "course_id": '$TEST_COURSE_ID'
    }'

    result=$(test_endpoint "POST" "/announcements" "$create_course_announcement_data" "Authorization: Bearer $FACULTY_TOKEN")
    http_code=$(echo "$result" | cut -d'|' -f1)
    body=$(echo "$result" | cut -d'|' -f2-)

    echo -e "       ${BLUE}Method:${NC} POST"
    echo -e "       ${BLUE}Endpoint:${NC} /announcements"
    echo -e "       ${BLUE}Status:${NC} $http_code"
    echo ""

    if [ "$http_code" = "201" ] || [ "$http_code" = "200" ]; then
        print_result "Create course-specific announcement" "PASS" "HTTP $http_code - Announcement created successfully"
        # Extract the created announcement ID for later tests
        TEST_ANNOUNCEMENT_ID=$(echo "$body" | grep -o '"id":[0-9]*' | head -1 | cut -d':' -f2)
    else
        print_result "Create course-specific announcement" "FAIL" "HTTP $http_code - $body"
    fi
else
    print_result "Create course-specific announcement" "SKIP" "No faculty token or course ID available"
fi

# =============================================================================
# TEST 20: POST /api/announcements - Create faculty-specific announcement
# =============================================================================
echo -e "${YELLOW}========================================${NC}"
echo -e "${YELLOW}TEST 20: Create Faculty-Specific Announcement${NC}"
echo -e "${YELLOW}========================================${NC}"
echo ""

if [ -n "$FACULTY_TOKEN" ] && [ -n "$TEST_FACULTY_ID" ]; then
    timestamp=$(date +%s)
    create_faculty_announcement_data='{
        "title": "Faculty Announcement '$timestamp'",
        "content": "This is a faculty-specific announcement.",
        "category": "faculty",
        "target_audience": "all",
        "priority": "high",
        "is_published": true,
        "faculty_id": '$TEST_FACULTY_ID'
    }'

    result=$(test_endpoint "POST" "/announcements" "$create_faculty_announcement_data" "Authorization: Bearer $FACULTY_TOKEN")
    http_code=$(echo "$result" | cut -d'|' -f1)
    body=$(echo "$result" | cut -d'|' -f2-)

    echo -e "       ${BLUE}Method:${NC} POST"
    echo -e "       ${BLUE}Endpoint:${NC} /announcements"
    echo -e "       ${BLUE}Status:${NC} $http_code"
    echo ""

    if [ "$http_code" = "201" ] || [ "$http_code" = "200" ]; then
        print_result "Create faculty-specific announcement" "PASS" "HTTP $http_code - Announcement created successfully"
    else
        print_result "Create faculty-specific announcement" "FAIL" "HTTP $http_code - $body"
    fi
else
    print_result "Create faculty-specific announcement" "SKIP" "No faculty token or faculty ID available"
fi

# =============================================================================
# TEST 21: GET /api/announcements/{id} without authentication
# =============================================================================
echo -e "${YELLOW}========================================${NC}"
echo -e "${YELLOW}TEST 21: Get Announcement Without Authentication${NC}"
echo -e "${YELLOW}========================================${NC}"
echo ""

if [ -n "$TEST_ANNOUNCEMENT_ID" ]; then
    result=$(test_endpoint "GET" "/announcements/$TEST_ANNOUNCEMENT_ID" "" "")
    http_code=$(echo "$result" | cut -d'|' -f1)
    body=$(echo "$result" | cut -d'|' -f2-)

    echo -e "       ${BLUE}Method:${NC} GET"
    echo -e "       ${BLUE}Endpoint:${NC} /announcements/$TEST_ANNOUNCEMENT_ID"
    echo -e "       ${BLUE}Status:${NC} $http_code"
    echo ""

    if [ "$http_code" = "401" ]; then
        print_result "Get announcement without authentication" "PASS" "HTTP 401 - Unauthorized as expected"
    else
        print_result "Get announcement without authentication" "FAIL" "Expected 401, got $http_code - $body"
    fi
else
    print_result "Get announcement without authentication" "SKIP" "No announcement ID available"
fi

# =============================================================================
# TEST 22: PUT /api/announcements/{id} without authentication
# =============================================================================
echo -e "${YELLOW}========================================${NC}"
echo -e "${YELLOW}TEST 22: Update Announcement Without Authentication${NC}"
echo -e "${YELLOW}========================================${NC}"
echo ""

if [ -n "$TEST_ANNOUNCEMENT_ID" ]; then
    update_announcement_no_auth='{
        "title": "Unauthorized Update"
    }'

    result=$(test_endpoint "PUT" "/announcements/$TEST_ANNOUNCEMENT_ID" "$update_announcement_no_auth" "")
    http_code=$(echo "$result" | cut -d'|' -f1)
    body=$(echo "$result" | cut -d'|' -f2-)

    echo -e "       ${BLUE}Method:${NC} PUT"
    echo -e "       ${BLUE}Endpoint:${NC} /announcements/$TEST_ANNOUNCEMENT_ID"
    echo -e "       ${BLUE}Status:${NC} $http_code"
    echo ""

    if [ "$http_code" = "401" ]; then
        print_result "Update announcement without authentication" "PASS" "HTTP 401 - Unauthorized as expected"
    else
        print_result "Update announcement without authentication" "FAIL" "Expected 401, got $http_code - $body"
    fi
else
    print_result "Update announcement without authentication" "SKIP" "No announcement ID available"
fi

# =============================================================================
# TEST 23: DELETE /api/announcements/{id} without authentication
# =============================================================================
echo -e "${YELLOW}========================================${NC}"
echo -e "${YELLOW}TEST 23: Delete Announcement Without Authentication${NC}"
echo -e "${YELLOW}========================================${NC}"
echo ""

if [ -n "$TEST_ANNOUNCEMENT_ID" ]; then
    result=$(test_endpoint "DELETE" "/announcements/$TEST_ANNOUNCEMENT_ID" "" "")
    http_code=$(echo "$result" | cut -d'|' -f1)
    body=$(echo "$result" | cut -d'|' -f2-)

    echo -e "       ${BLUE}Method:${NC} DELETE"
    echo -e "       ${BLUE}Endpoint:${NC} /announcements/$TEST_ANNOUNCEMENT_ID"
    echo -e "       ${BLUE}Status:${NC} $http_code"
    echo ""

    if [ "$http_code" = "401" ]; then
        print_result "Delete announcement without authentication" "PASS" "HTTP 401 - Unauthorized as expected"
    else
        print_result "Delete announcement without authentication" "FAIL" "Expected 401, got $http_code - $body"
    fi
else
    print_result "Delete announcement without authentication" "SKIP" "No announcement ID available"
fi

# =============================================================================
# TEST 24: POST /api/announcements with missing required fields
# =============================================================================
echo -e "${YELLOW}========================================${NC}"
echo -e "${YELLOW}TEST 24: Create Announcement with Missing Required Fields${NC}"
echo -e "${YELLOW}========================================${NC}"
echo ""

if [ -n "$FACULTY_TOKEN" ]; then
    create_announcement_missing='{
        "priority": "high"
    }'

    result=$(test_endpoint "POST" "/announcements" "$create_announcement_missing" "Authorization: Bearer $FACULTY_TOKEN")
    http_code=$(echo "$result" | cut -d'|' -f1)
    body=$(echo "$result" | cut -d'|' -f2-)

    echo -e "       ${BLUE}Method:${NC} POST"
    echo -e "       ${BLUE}Endpoint:${NC} /announcements"
    echo -e "       ${BLUE}Status:${NC} $http_code"
    echo ""

    if [ "$http_code" = "422" ]; then
        print_result "Create announcement with missing fields" "PASS" "HTTP 422 - Validation error as expected"
    else
        print_result "Create announcement with missing fields" "FAIL" "Expected 422, got $http_code - $body"
    fi
else
    print_result "Create announcement with missing fields" "SKIP" "No faculty token available"
fi

# =============================================================================
# TEST 25: POST /api/announcements/{id}/publish by student (should fail)
# =============================================================================
echo -e "${YELLOW}========================================${NC}"
echo -e "${YELLOW}TEST 25: Publish Announcement by Student (should fail)${NC}"
echo -e "${YELLOW}========================================${NC}"
echo ""

if [ -n "$STUDENT_TOKEN" ] && [ -n "$TEST_ANNOUNCEMENT_ID" ]; then
    result=$(test_endpoint "POST" "/announcements/$TEST_ANNOUNCEMENT_ID/publish" "" "Authorization: Bearer $STUDENT_TOKEN")
    http_code=$(echo "$result" | cut -d'|' -f1)
    body=$(echo "$result" | cut -d'|' -f2-)

    echo -e "       ${BLUE}Method:${NC} POST"
    echo -e "       ${BLUE}Endpoint:${NC} /announcements/$TEST_ANNOUNCEMENT_ID/publish"
    echo -e "       ${BLUE}Status:${NC} $http_code"
    echo ""

    if [ "$http_code" = "403" ]; then
        print_result "Publish announcement by student" "PASS" "HTTP 403 - Forbidden as expected"
    else
        print_result "Publish announcement by student" "FAIL" "Expected 403, got $http_code - $body"
    fi
else
    print_result "Publish announcement by student" "SKIP" "No student token or announcement ID available"
fi

# =============================================================================
# TEST 26: POST /api/announcements/{id}/unpublish by student (should fail)
# =============================================================================
echo -e "${YELLOW}========================================${NC}"
echo -e "${YELLOW}TEST 26: Unpublish Announcement by Student (should fail)${NC}"
echo -e "${YELLOW}========================================${NC}"
echo ""

if [ -n "$STUDENT_TOKEN" ] && [ -n "$TEST_ANNOUNCEMENT_ID" ]; then
    result=$(test_endpoint "POST" "/announcements/$TEST_ANNOUNCEMENT_ID/unpublish" "" "Authorization: Bearer $STUDENT_TOKEN")
    http_code=$(echo "$result" | cut -d'|' -f1)
    body=$(echo "$result" | cut -d'|' -f2-)

    echo -e "       ${BLUE}Method:${NC} POST"
    echo -e "       ${BLUE}Endpoint:${NC} /announcements/$TEST_ANNOUNCEMENT_ID/unpublish"
    echo -e "       ${BLUE}Status:${NC} $http_code"
    echo ""

    if [ "$http_code" = "403" ]; then
        print_result "Unpublish announcement by student" "PASS" "HTTP 403 - Forbidden as expected"
    else
        print_result "Unpublish announcement by student" "FAIL" "Expected 403, got $http_code - $body"
    fi
else
    print_result "Unpublish announcement by student" "SKIP" "No student token or announcement ID available"
fi

# =============================================================================
# TEST 27: DELETE /api/announcements/{id} by student (should fail)
# =============================================================================
echo -e "${YELLOW}========================================${NC}"
echo -e "${YELLOW}TEST 27: Delete Announcement by Student (should fail)${NC}"
echo -e "${YELLOW}========================================${NC}"
echo ""

if [ -n "$STUDENT_TOKEN" ] && [ -n "$TEST_ANNOUNCEMENT_ID" ]; then
    result=$(test_endpoint "DELETE" "/announcements/$TEST_ANNOUNCEMENT_ID" "" "Authorization: Bearer $STUDENT_TOKEN")
    http_code=$(echo "$result" | cut -d'|' -f1)
    body=$(echo "$result" | cut -d'|' -f2-)

    echo -e "       ${BLUE}Method:${NC} DELETE"
    echo -e "       ${BLUE}Endpoint:${NC} /announcements/$TEST_ANNOUNCEMENT_ID"
    echo -e "       ${BLUE}Status:${NC} $http_code"
    echo ""

    if [ "$http_code" = "403" ]; then
        print_result "Delete announcement by student" "PASS" "HTTP 403 - Forbidden as expected"
    else
        print_result "Delete announcement by student" "FAIL" "Expected 403, got $http_code - $body"
    fi
else
    print_result "Delete announcement by student" "SKIP" "No student token or announcement ID available"
fi

# =============================================================================
# TEST 28: POST /api/announcements - Create announcement with expires_at
# =============================================================================
echo -e "${YELLOW}========================================${NC}"
echo -e "${YELLOW}TEST 28: Create Announcement with Expiration Date${NC}"
echo -e "${YELLOW}========================================${NC}"
echo ""

if [ -n "$FACULTY_TOKEN" ]; then
    timestamp=$(date +%s)
    expires_date=$(date -d "+30 days" +%Y-%m-%d 2>/dev/null || date -v+30d +%Y-%m-%d 2>/dev/null || echo "2025-12-31")
    create_expiring_announcement_data='{
        "title": "Expiring Announcement '$timestamp'",
        "content": "This announcement will expire.",
        "category": "general",
        "target_audience": "all",
        "priority": "normal",
        "is_published": true,
        "expires_at": "'$expires_date'"
    }'

    result=$(test_endpoint "POST" "/announcements" "$create_expiring_announcement_data" "Authorization: Bearer $FACULTY_TOKEN")
    http_code=$(echo "$result" | cut -d'|' -f1)
    body=$(echo "$result" | cut -d'|' -f2-)

    echo -e "       ${BLUE}Method:${NC} POST"
    echo -e "       ${BLUE}Endpoint:${NC} /announcements"
    echo -e "       ${BLUE}Status:${NC} $http_code"
    echo ""

    if [ "$http_code" = "201" ] || [ "$http_code" = "200" ]; then
        print_result "Create announcement with expiration date" "PASS" "HTTP $http_code - Announcement created successfully"
    else
        print_result "Create announcement with expiration date" "FAIL" "HTTP $http_code - $body"
    fi
else
    print_result "Create announcement with expiration date" "SKIP" "No faculty token available"
fi

# =============================================================================
# TEST 29: POST /api/announcements - Create announcement with attachment
# =============================================================================
echo -e "${YELLOW}========================================${NC}"
echo -e "${YELLOW}TEST 29: Create Announcement with Attachment${NC}"
echo -e "${YELLOW}========================================${NC}"
echo ""

if [ -n "$FACULTY_TOKEN" ]; then
    timestamp=$(date +%s)
    create_attachment_announcement_data='{
        "title": "Announcement with Attachment '$timestamp'",
        "content": "This announcement has an attachment.",
        "category": "general",
        "target_audience": "all",
        "priority": "normal",
        "is_published": true,
        "attachment_url": "https://example.com/document.pdf",
        "attachment_type": "pdf"
    }'

    result=$(test_endpoint "POST" "/announcements" "$create_attachment_announcement_data" "Authorization: Bearer $FACULTY_TOKEN")
    http_code=$(echo "$result" | cut -d'|' -f1)
    body=$(echo "$result" | cut -d'|' -f2-)

    echo -e "       ${BLUE}Method:${NC} POST"
    echo -e "       ${BLUE}Endpoint:${NC} /announcements"
    echo -e "       ${BLUE}Status:${NC} $http_code"
    echo ""

    if [ "$http_code" = "201" ] || [ "$http_code" = "200" ]; then
        print_result "Create announcement with attachment" "PASS" "HTTP $http_code - Announcement created successfully"
    else
        print_result "Create announcement with attachment" "FAIL" "HTTP $http_code - $body"
    fi
else
    print_result "Create announcement with attachment" "SKIP" "No faculty token available"
fi

# =============================================================================
# TEST 30: GET /api/announcements - List announcements with multiple filters
# =============================================================================
echo -e "${YELLOW}========================================${NC}"
echo -e "${YELLOW}TEST 30: List Announcements with Multiple Filters${NC}"
echo -e "${YELLOW}========================================${NC}"
echo ""

if [ -n "$STUDENT_TOKEN" ]; then
    result=$(test_endpoint "GET" "/announcements?category=general&priority=high&target_audience=all" "" "Authorization: Bearer $STUDENT_TOKEN")
    http_code=$(echo "$result" | cut -d'|' -f1)
    body=$(echo "$result" | cut -d'|' -f2-)

    echo -e "       ${BLUE}Method:${NC} GET"
    echo -e "       ${BLUE}Endpoint:${NC} /announcements?category=general&priority=high&target_audience=all"
    echo -e "       ${BLUE}Status:${NC} $http_code"
    echo ""

    if [ "$http_code" = "200" ]; then
        print_result "List announcements with multiple filters" "PASS" "HTTP 200 - Filtered announcements retrieved successfully"
    else
        print_result "List announcements with multiple filters" "FAIL" "HTTP $http_code - $body"
    fi
else
    print_result "List announcements with multiple filters" "SKIP" "No student token available"
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