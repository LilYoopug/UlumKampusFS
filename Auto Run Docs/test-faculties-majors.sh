#!/bin/bash

# =============================================================================
# Faculty and Major Endpoint Tests
# Tests all faculty and major-related API endpoints using curl
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
TEST_FACULTY_ID=""
TEST_MAJOR_ID=""
TEST_FACULTY_ID_FOR_MAJOR=""

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

echo ""

# =============================================================================
# FACULTY TESTS
# =============================================================================

# =============================================================================
# TEST 1: GET /api/faculties - List all faculties
# =============================================================================
echo -e "${YELLOW}========================================${NC}"
echo -e "${YELLOW}TEST 1: List All Faculties${NC}"
echo -e "${YELLOW}========================================${NC}"
echo ""

if [ -n "$ADMIN_TOKEN" ]; then
    result=$(test_endpoint "GET" "/faculties" "" "Authorization: Bearer $ADMIN_TOKEN")
    http_code=$(echo "$result" | cut -d'|' -f1)
    body=$(echo "$result" | cut -d'|' -f2-)

    echo -e "       ${BLUE}Method:${NC} GET"
    echo -e "       ${BLUE}Endpoint:${NC} /faculties"
    echo -e "       ${BLUE}Status:${NC} $http_code"
    echo -e "       ${BLUE}Response:${NC} $(echo "$body" | head -c 200)..."
    echo ""

    if [ "$http_code" = "200" ]; then
        print_result "List all faculties" "PASS" "HTTP 200 - Faculties retrieved successfully"
    else
        print_result "List all faculties" "FAIL" "HTTP $http_code - $body"
    fi
else
    print_result "List all faculties" "SKIP" "No admin token available"
fi

# =============================================================================
# TEST 2: GET /api/faculties with faculty token
# =============================================================================
echo -e "${YELLOW}========================================${NC}"
echo -e "${YELLOW}TEST 2: List All Faculties (Faculty)${NC}"
echo -e "${YELLOW}========================================${NC}"
echo ""

if [ -n "$FACULTY_TOKEN" ]; then
    result=$(test_endpoint "GET" "/faculties" "" "Authorization: Bearer $FACULTY_TOKEN")
    http_code=$(echo "$result" | cut -d'|' -f1)
    body=$(echo "$result" | cut -d'|' -f2-)

    echo -e "       ${BLUE}Method:${NC} GET"
    echo -e "       ${BLUE}Endpoint:${NC} /faculties"
    echo -e "       ${BLUE}Status:${NC} $http_code"
    echo ""

    if [ "$http_code" = "200" ]; then
        print_result "List all faculties (faculty)" "PASS" "HTTP 200 - Faculties retrieved successfully"
    else
        print_result "List all faculties (faculty)" "FAIL" "HTTP $http_code - $body"
    fi
else
    print_result "List all faculties (faculty)" "SKIP" "No faculty token available"
fi

# =============================================================================
# TEST 3: POST /api/faculties - Create new faculty
# =============================================================================
echo -e "${YELLOW}========================================${NC}"
echo -e "${YELLOW}TEST 3: Create New Faculty${NC}"
echo -e "${YELLOW}========================================${NC}"
echo ""

if [ -n "$ADMIN_TOKEN" ]; then
    timestamp=$(date +%s)
    create_faculty_data='{
        "name": "Test Faculty '$timestamp'",
        "code": "TF'$timestamp'",
        "description": "This is a test faculty created by automated testing",
        "dean": "Dr. Test Dean"
    }'

    result=$(test_endpoint "POST" "/faculties" "$create_faculty_data" "Authorization: Bearer $ADMIN_TOKEN")
    http_code=$(echo "$result" | cut -d'|' -f1)
    body=$(echo "$result" | cut -d'|' -f2-)

    echo -e "       ${BLUE}Method:${NC} POST"
    echo -e "       ${BLUE}Endpoint:${NC} /faculties"
    echo -e "       ${BLUE}Status:${NC} $http_code"
    echo -e "       ${BLUE}Response:${NC} $(echo "$body" | head -c 300)..."
    echo ""

    if [ "$http_code" = "201" ] || [ "$http_code" = "200" ]; then
        print_result "Create new faculty" "PASS" "HTTP $http_code - Faculty created successfully"
        # Extract the created faculty ID for later tests
        TEST_FACULTY_ID=$(echo "$body" | grep -o '"id":[0-9]*' | head -1 | cut -d':' -f2)
        TEST_FACULTY_ID_FOR_MAJOR=$TEST_FACULTY_ID
    else
        print_result "Create new faculty" "FAIL" "HTTP $http_code - $body"
    fi
else
    print_result "Create new faculty" "SKIP" "No admin token available"
fi

# =============================================================================
# TEST 4: POST /api/faculties with faculty token (should work)
# =============================================================================
echo -e "${YELLOW}========================================${NC}"
echo -e "${YELLOW}TEST 4: Create Faculty (Faculty)${NC}"
echo -e "${YELLOW}========================================${NC}"
echo ""

if [ -n "$FACULTY_TOKEN" ]; then
    timestamp=$(date +%s)
    create_faculty_data2='{
        "name": "Test Faculty 2 '$timestamp'",
        "code": "TF2'$timestamp'",
        "description": "This is another test faculty created by faculty user"
    }'

    result=$(test_endpoint "POST" "/faculties" "$create_faculty_data2" "Authorization: Bearer $FACULTY_TOKEN")
    http_code=$(echo "$result" | cut -d'|' -f1)
    body=$(echo "$result" | cut -d'|' -f2-)

    echo -e "       ${BLUE}Method:${NC} POST"
    echo -e "       ${BLUE}Endpoint:${NC} /faculties"
    echo -e "       ${BLUE}Status:${NC} $http_code"
    echo ""

    if [ "$http_code" = "201" ] || [ "$http_code" = "200" ]; then
        print_result "Create faculty (faculty)" "PASS" "HTTP $http_code - Faculty created successfully"
        # Save this faculty ID if we didn't get one from admin
        if [ -z "$TEST_FACULTY_ID" ]; then
            TEST_FACULTY_ID=$(echo "$body" | grep -o '"id":[0-9]*' | head -1 | cut -d':' -f2)
            TEST_FACULTY_ID_FOR_MAJOR=$TEST_FACULTY_ID
        fi
    else
        print_result "Create faculty (faculty)" "FAIL" "HTTP $http_code - $body"
    fi
else
    print_result "Create faculty (faculty)" "SKIP" "No faculty token available"
fi

# =============================================================================
# TEST 5: GET /api/faculties/{id} - Get specific faculty
# =============================================================================
echo -e "${YELLOW}========================================${NC}"
echo -e "${YELLOW}TEST 5: Get Faculty by ID${NC}"
echo -e "${YELLOW}========================================${NC}"
echo ""

if [ -n "$ADMIN_TOKEN" ] && [ -n "$TEST_FACULTY_ID" ]; then
    result=$(test_endpoint "GET" "/faculties/$TEST_FACULTY_ID" "" "Authorization: Bearer $ADMIN_TOKEN")
    http_code=$(echo "$result" | cut -d'|' -f1)
    body=$(echo "$result" | cut -d'|' -f2-)

    echo -e "       ${BLUE}Method:${NC} GET"
    echo -e "       ${BLUE}Endpoint:${NC} /faculties/$TEST_FACULTY_ID"
    echo -e "       ${BLUE}Status:${NC} $http_code"
    echo ""

    if [ "$http_code" = "200" ]; then
        print_result "Get faculty by ID" "PASS" "HTTP 200 - Faculty retrieved successfully"
    else
        print_result "Get faculty by ID" "FAIL" "HTTP $http_code - $body"
    fi
elif [ -n "$ADMIN_TOKEN" ]; then
    # Try to get any faculty from the list
    result=$(test_endpoint "GET" "/faculties" "" "Authorization: Bearer $ADMIN_TOKEN")
    body=$(echo "$result" | cut -d'|' -f2-)
    TEST_FACULTY_ID=$(echo "$body" | grep -o '"id":[0-9]*' | head -1 | cut -d':' -f2)
    TEST_FACULTY_ID_FOR_MAJOR=$TEST_FACULTY_ID

    if [ -n "$TEST_FACULTY_ID" ]; then
        result=$(test_endpoint "GET" "/faculties/$TEST_FACULTY_ID" "" "Authorization: Bearer $ADMIN_TOKEN")
        http_code=$(echo "$result" | cut -d'|' -f1)
        body=$(echo "$result" | cut -d'|' -f2-)

        echo -e "       ${BLUE}Method:${NC} GET"
        echo -e "       ${BLUE}Endpoint:${NC} /faculties/$TEST_FACULTY_ID"
        echo -e "       ${BLUE}Status:${NC} $http_code"
        echo ""

        if [ "$http_code" = "200" ]; then
            print_result "Get faculty by ID" "PASS" "HTTP 200 - Faculty retrieved successfully"
        else
            print_result "Get faculty by ID" "FAIL" "HTTP $http_code - $body"
        fi
    else
        print_result "Get faculty by ID" "SKIP" "No faculty ID available"
    fi
else
    print_result "Get faculty by ID" "SKIP" "No admin token available"
fi

# =============================================================================
# TEST 6: PUT /api/faculties/{id} - Update faculty
# =============================================================================
echo -e "${YELLOW}========================================${NC}"
echo -e "${YELLOW}TEST 6: Update Faculty${NC}"
echo -e "${YELLOW}========================================${NC}"
echo ""

if [ -n "$ADMIN_TOKEN" ] && [ -n "$TEST_FACULTY_ID" ]; then
    update_faculty_data='{
        "name": "Updated Test Faculty",
        "description": "This faculty has been updated by automated testing",
        "dean": "Dr. Updated Dean"
    }'

    result=$(test_endpoint "PUT" "/faculties/$TEST_FACULTY_ID" "$update_faculty_data" "Authorization: Bearer $ADMIN_TOKEN")
    http_code=$(echo "$result" | cut -d'|' -f1)
    body=$(echo "$result" | cut -d'|' -f2-)

    echo -e "       ${BLUE}Method:${NC} PUT"
    echo -e "       ${BLUE}Endpoint:${NC} /faculties/$TEST_FACULTY_ID"
    echo -e "       ${BLUE}Status:${NC} $http_code"
    echo ""

    if [ "$http_code" = "200" ]; then
        print_result "Update faculty" "PASS" "HTTP 200 - Faculty updated successfully"
    else
        print_result "Update faculty" "FAIL" "HTTP $http_code - $body"
    fi
else
    print_result "Update faculty" "SKIP" "No admin token or faculty ID available"
fi

# =============================================================================
# TEST 7: GET /api/faculties/{id}/courses - Get courses by faculty
# =============================================================================
echo -e "${YELLOW}========================================${NC}"
echo -e "${YELLOW}TEST 7: Get Courses by Faculty${NC}"
echo -e "${YELLOW}========================================${NC}"
echo ""

if [ -n "$ADMIN_TOKEN" ] && [ -n "$TEST_FACULTY_ID" ]; then
    result=$(test_endpoint "GET" "/faculties/$TEST_FACULTY_ID/courses" "" "Authorization: Bearer $ADMIN_TOKEN")
    http_code=$(echo "$result" | cut -d'|' -f1)
    body=$(echo "$result" | cut -d'|' -f2-)

    echo -e "       ${BLUE}Method:${NC} GET"
    echo -e "       ${BLUE}Endpoint:${NC} /faculties/$TEST_FACULTY_ID/courses"
    echo -e "       ${BLUE}Status:${NC} $http_code"
    echo ""

    if [ "$http_code" = "200" ]; then
        print_result "Get courses by faculty" "PASS" "HTTP 200 - Courses retrieved successfully"
    else
        print_result "Get courses by faculty" "FAIL" "HTTP $http_code - $body"
    fi
else
    print_result "Get courses by faculty" "SKIP" "No admin token or faculty ID available"
fi

# =============================================================================
# TEST 8: GET /api/faculties/{id}/majors - Get majors by faculty
# =============================================================================
echo -e "${YELLOW}========================================${NC}"
echo -e "${YELLOW}TEST 8: Get Majors by Faculty${NC}"
echo -e "${YELLOW}========================================${NC}"
echo ""

if [ -n "$ADMIN_TOKEN" ] && [ -n "$TEST_FACULTY_ID" ]; then
    result=$(test_endpoint "GET" "/faculties/$TEST_FACULTY_ID/majors" "" "Authorization: Bearer $ADMIN_TOKEN")
    http_code=$(echo "$result" | cut -d'|' -f1)
    body=$(echo "$result" | cut -d'|' -f2-)

    echo -e "       ${BLUE}Method:${NC} GET"
    echo -e "       ${BLUE}Endpoint:${NC} /faculties/$TEST_FACULTY_ID/majors"
    echo -e "       ${BLUE}Status:${NC} $http_code"
    echo ""

    if [ "$http_code" = "200" ]; then
        print_result "Get majors by faculty" "PASS" "HTTP 200 - Majors retrieved successfully"
    else
        print_result "Get majors by faculty" "FAIL" "HTTP $http_code - $body"
    fi
else
    print_result "Get majors by faculty" "SKIP" "No admin token or faculty ID available"
fi

# =============================================================================
# TEST 9: GET /api/faculty/dashboard - Faculty dashboard
# =============================================================================
echo -e "${YELLOW}========================================${NC}"
echo -e "${YELLOW}TEST 9: Faculty Dashboard${NC}"
echo -e "${YELLOW}========================================${NC}"
echo ""

if [ -n "$FACULTY_TOKEN" ]; then
    result=$(test_endpoint "GET" "/faculty/dashboard" "" "Authorization: Bearer $FACULTY_TOKEN")
    http_code=$(echo "$result" | cut -d'|' -f1)
    body=$(echo "$result" | cut -d'|' -f2-)

    echo -e "       ${BLUE}Method:${NC} GET"
    echo -e "       ${BLUE}Endpoint:${NC} /faculty/dashboard"
    echo -e "       ${BLUE}Status:${NC} $http_code"
    echo ""

    if [ "$http_code" = "200" ]; then
        print_result "Faculty dashboard" "PASS" "HTTP 200 - Dashboard retrieved successfully"
    else
        print_result "Faculty dashboard" "FAIL" "HTTP $http_code - $body"
    fi
else
    print_result "Faculty dashboard" "SKIP" "No faculty token available"
fi

# =============================================================================
# TEST 10: GET /api/faculty/my-courses - Faculty's courses
# =============================================================================
echo -e "${YELLOW}========================================${NC}"
echo -e "${YELLOW}TEST 10: Faculty My Courses${NC}"
echo -e "${YELLOW}========================================${NC}"
echo ""

if [ -n "$FACULTY_TOKEN" ]; then
    result=$(test_endpoint "GET" "/faculty/my-courses" "" "Authorization: Bearer $FACULTY_TOKEN")
    http_code=$(echo "$result" | cut -d'|' -f1)
    body=$(echo "$result" | cut -d'|' -f2-)

    echo -e "       ${BLUE}Method:${NC} GET"
    echo -e "       ${BLUE}Endpoint:${NC} /faculty/my-courses"
    echo -e "       ${BLUE}Status:${NC} $http_code"
    echo ""

    if [ "$http_code" = "200" ]; then
        print_result "Faculty my courses" "PASS" "HTTP 200 - Courses retrieved successfully"
    else
        print_result "Faculty my courses" "FAIL" "HTTP $http_code - $body"
    fi
else
    print_result "Faculty my courses" "SKIP" "No faculty token available"
fi

# =============================================================================
# TEST 11: GET /api/faculty/stats - Faculty stats
# =============================================================================
echo -e "${YELLOW}========================================${NC}"
echo -e "${YELLOW}TEST 11: Faculty Stats${NC}"
echo -e "${YELLOW}========================================${NC}"
echo ""

if [ -n "$FACULTY_TOKEN" ]; then
    result=$(test_endpoint "GET" "/faculty/stats" "" "Authorization: Bearer $FACULTY_TOKEN")
    http_code=$(echo "$result" | cut -d'|' -f1)
    body=$(echo "$result" | cut -d'|' -f2-)

    echo -e "       ${BLUE}Method:${NC} GET"
    echo -e "       ${BLUE}Endpoint:${NC} /faculty/stats"
    echo -e "       ${BLUE}Status:${NC} $http_code"
    echo ""

    if [ "$http_code" = "200" ]; then
        print_result "Faculty stats" "PASS" "HTTP 200 - Stats retrieved successfully"
    else
        print_result "Faculty stats" "FAIL" "HTTP $http_code - $body"
    fi
else
    print_result "Faculty stats" "SKIP" "No faculty token available"
fi

# =============================================================================
# TEST 12: GET /api/faculties without authentication
# =============================================================================
echo -e "${YELLOW}========================================${NC}"
echo -e "${YELLOW}TEST 12: List Faculties Without Authentication${NC}"
echo -e "${YELLOW}========================================${NC}"
echo ""

result=$(test_endpoint "GET" "/faculties" "" "")
http_code=$(echo "$result" | cut -d'|' -f1)
body=$(echo "$result" | cut -d'|' -f2-)

echo -e "       ${BLUE}Method:${NC} GET"
echo -e "       ${BLUE}Endpoint:${NC} /faculties"
echo -e "       ${BLUE}Status:${NC} $http_code"
echo ""

if [ "$http_code" = "401" ]; then
    print_result "List faculties without authentication" "PASS" "HTTP 401 - Unauthorized as expected"
else
    print_result "List faculties without authentication" "FAIL" "Expected 401, got $http_code - $body"
fi

# =============================================================================
# TEST 13: GET /api/faculties with invalid ID
# =============================================================================
echo -e "${YELLOW}========================================${NC}"
echo -e "${YELLOW}TEST 13: Get Faculty with Invalid ID${NC}"
echo -e "${YELLOW}========================================${NC}"
echo ""

if [ -n "$ADMIN_TOKEN" ]; then
    result=$(test_endpoint "GET" "/faculties/999999" "" "Authorization: Bearer $ADMIN_TOKEN")
    http_code=$(echo "$result" | cut -d'|' -f1)
    body=$(echo "$result" | cut -d'|' -f2-)

    echo -e "       ${BLUE}Method:${NC} GET"
    echo -e "       ${BLUE}Endpoint:${NC} /faculties/999999"
    echo -e "       ${BLUE}Status:${NC} $http_code"
    echo ""

    if [ "$http_code" = "404" ]; then
        print_result "Get faculty with invalid ID" "PASS" "HTTP 404 - Not found as expected"
    else
        print_result "Get faculty with invalid ID" "FAIL" "Expected 404, got $http_code - $body"
    fi
else
    print_result "Get faculty with invalid ID" "SKIP" "No admin token available"
fi

# =============================================================================
# TEST 14: PUT /api/faculties/{id} with student token (should fail)
# =============================================================================
echo -e "${YELLOW}========================================${NC}"
echo -e "${YELLOW}TEST 14: Update Faculty (Student - should fail)${NC}"
echo -e "${YELLOW}========================================${NC}"
echo ""

if [ -n "$STUDENT_TOKEN" ] && [ -n "$TEST_FACULTY_ID" ]; then
    update_faculty_data='{
        "name": "Unauthorized Update"
    }'

    result=$(test_endpoint "PUT" "/faculties/$TEST_FACULTY_ID" "$update_faculty_data" "Authorization: Bearer $STUDENT_TOKEN")
    http_code=$(echo "$result" | cut -d'|' -f1)
    body=$(echo "$result" | cut -d'|' -f2-)

    echo -e "       ${BLUE}Method:${NC} PUT"
    echo -e "       ${BLUE}Endpoint:${NC} /faculties/$TEST_FACULTY_ID"
    echo -e "       ${BLUE}Status:${NC} $http_code"
    echo ""

    if [ "$http_code" = "403" ]; then
        print_result "Update faculty (student)" "PASS" "HTTP 403 - Forbidden as expected"
    else
        print_result "Update faculty (student)" "FAIL" "Expected 403, got $http_code - $body"
    fi
else
    print_result "Update faculty (student)" "SKIP" "No student token or faculty ID available"
fi

# =============================================================================
# TEST 15: DELETE /api/faculties/{id} - Delete faculty
# =============================================================================
echo -e "${YELLOW}========================================${NC}"
echo -e "${YELLOW}TEST 15: Delete Faculty${NC}"
echo -e "${YELLOW}========================================${NC}"
echo ""

if [ -n "$ADMIN_TOKEN" ] && [ -n "$TEST_FACULTY_ID" ]; then
    result=$(test_endpoint "DELETE" "/faculties/$TEST_FACULTY_ID" "" "Authorization: Bearer $ADMIN_TOKEN")
    http_code=$(echo "$result" | cut -d'|' -f1)
    body=$(echo "$result" | cut -d'|' -f2-)

    echo -e "       ${BLUE}Method:${NC} DELETE"
    echo -e "       ${BLUE}Endpoint:${NC} /faculties/$TEST_FACULTY_ID"
    echo -e "       ${BLUE}Status:${NC} $http_code"
    echo ""

    if [ "$http_code" = "204" ] || [ "$http_code" = "200" ]; then
        print_result "Delete faculty" "PASS" "HTTP $http_code - Faculty deleted successfully"
    elif [ "$http_code" = "409" ]; then
        print_result "Delete faculty" "PASS" "HTTP 409 - Cannot delete with active dependencies (expected)"
    else
        print_result "Delete faculty" "FAIL" "HTTP $http_code - $body"
    fi
else
    print_result "Delete faculty" "SKIP" "No admin token or faculty ID available"
fi

echo ""
echo -e "${YELLOW}========================================${NC}"
echo -e "${YELLOW}MAJOR TESTS${NC}"
echo -e "${YELLOW}========================================${NC}"
echo ""

# =============================================================================
# MAJOR TESTS
# =============================================================================

# =============================================================================
# TEST 16: GET /api/majors - List all majors
# =============================================================================
echo -e "${YELLOW}========================================${NC}"
echo -e "${YELLOW}TEST 16: List All Majors${NC}"
echo -e "${YELLOW}========================================${NC}"
echo ""

if [ -n "$ADMIN_TOKEN" ]; then
    result=$(test_endpoint "GET" "/majors" "" "Authorization: Bearer $ADMIN_TOKEN")
    http_code=$(echo "$result" | cut -d'|' -f1)
    body=$(echo "$result" | cut -d'|' -f2-)

    echo -e "       ${BLUE}Method:${NC} GET"
    echo -e "       ${BLUE}Endpoint:${NC} /majors"
    echo -e "       ${BLUE}Status:${NC} $http_code"
    echo -e "       ${BLUE}Response:${NC} $(echo "$body" | head -c 200)..."
    echo ""

    if [ "$http_code" = "200" ]; then
        print_result "List all majors" "PASS" "HTTP 200 - Majors retrieved successfully"
    else
        print_result "List all majors" "FAIL" "HTTP $http_code - $body"
    fi
else
    print_result "List all majors" "SKIP" "No admin token available"
fi

# =============================================================================
# TEST 17: GET /api/majors with student token
# =============================================================================
echo -e "${YELLOW}========================================${NC}"
echo -e "${YELLOW}TEST 17: List All Majors (Student)${NC}"
echo -e "${YELLOW}========================================${NC}"
echo ""

if [ -n "$STUDENT_TOKEN" ]; then
    result=$(test_endpoint "GET" "/majors" "" "Authorization: Bearer $STUDENT_TOKEN")
    http_code=$(echo "$result" | cut -d'|' -f1)
    body=$(echo "$result" | cut -d'|' -f2-)

    echo -e "       ${BLUE}Method:${NC} GET"
    echo -e "       ${BLUE}Endpoint:${NC} /majors"
    echo -e "       ${BLUE}Status:${NC} $http_code"
    echo ""

    if [ "$http_code" = "200" ]; then
        print_result "List all majors (student)" "PASS" "HTTP 200 - Majors retrieved successfully"
    else
        print_result "List all majors (student)" "FAIL" "HTTP $http_code - $body"
    fi
else
    print_result "List all majors (student)" "SKIP" "No student token available"
fi

# =============================================================================
# TEST 18: POST /api/majors - Create new major
# =============================================================================
echo -e "${YELLOW}========================================${NC}"
echo -e "${YELLOW}TEST 18: Create New Major${NC}"
echo -e "${YELLOW}========================================${NC}"
echo ""

if [ -n "$ADMIN_TOKEN" ] && [ -n "$TEST_FACULTY_ID_FOR_MAJOR" ]; then
    timestamp=$(date +%s)
    create_major_data='{
        "faculty_id": '$TEST_FACULTY_ID_FOR_MAJOR',
        "name": "Test Major '$timestamp'",
        "code": "TM'$timestamp'",
        "description": "This is a test major created by automated testing",
        "degree_level": "bachelor",
        "duration_years": 4
    }'

    result=$(test_endpoint "POST" "/majors" "$create_major_data" "Authorization: Bearer $ADMIN_TOKEN")
    http_code=$(echo "$result" | cut -d'|' -f1)
    body=$(echo "$result" | cut -d'|' -f2-)

    echo -e "       ${BLUE}Method:${NC} POST"
    echo -e "       ${BLUE}Endpoint:${NC} /majors"
    echo -e "       ${BLUE}Status:${NC} $http_code"
    echo -e "       ${BLUE}Response:${NC} $(echo "$body" | head -c 300)..."
    echo ""

    if [ "$http_code" = "201" ] || [ "$http_code" = "200" ]; then
        print_result "Create new major" "PASS" "HTTP $http_code - Major created successfully"
        # Extract the created major ID for later tests
        TEST_MAJOR_ID=$(echo "$body" | grep -o '"id":[0-9]*' | head -1 | cut -d':' -f2)
    else
        print_result "Create new major" "FAIL" "HTTP $http_code - $body"
    fi
else
    print_result "Create new major" "SKIP" "No admin token or faculty ID available"
fi

# =============================================================================
# TEST 19: POST /api/majors with faculty token (should work)
# =============================================================================
echo -e "${YELLOW}========================================${NC}"
echo -e "${YELLOW}TEST 19: Create Major (Faculty)${NC}"
echo -e "${YELLOW}========================================${NC}"
echo ""

if [ -n "$FACULTY_TOKEN" ] && [ -n "$TEST_FACULTY_ID_FOR_MAJOR" ]; then
    timestamp=$(date +%s)
    create_major_data2='{
        "faculty_id": '$TEST_FACULTY_ID_FOR_MAJOR',
        "name": "Test Major 2 '$timestamp'",
        "code": "TM2'$timestamp'",
        "description": "This is another test major created by faculty user",
        "degree_level": "master",
        "duration_years": 2
    }'

    result=$(test_endpoint "POST" "/majors" "$create_major_data2" "Authorization: Bearer $FACULTY_TOKEN")
    http_code=$(echo "$result" | cut -d'|' -f1)
    body=$(echo "$result" | cut -d'|' -f2-)

    echo -e "       ${BLUE}Method:${NC} POST"
    echo -e "       ${BLUE}Endpoint:${NC} /majors"
    echo -e "       ${BLUE}Status:${NC} $http_code"
    echo ""

    if [ "$http_code" = "201" ] || [ "$http_code" = "200" ]; then
        print_result "Create major (faculty)" "PASS" "HTTP $http_code - Major created successfully"
        # Save this major ID if we didn't get one from admin
        if [ -z "$TEST_MAJOR_ID" ]; then
            TEST_MAJOR_ID=$(echo "$body" | grep -o '"id":[0-9]*' | head -1 | cut -d':' -f2)
        fi
    else
        print_result "Create major (faculty)" "FAIL" "HTTP $http_code - $body"
    fi
else
    print_result "Create major (faculty)" "SKIP" "No faculty token or faculty ID available"
fi

# =============================================================================
# TEST 20: GET /api/majors/{id} - Get specific major
# =============================================================================
echo -e "${YELLOW}========================================${NC}"
echo -e "${YELLOW}TEST 20: Get Major by ID${NC}"
echo -e "${YELLOW}========================================${NC}"
echo ""

if [ -n "$ADMIN_TOKEN" ] && [ -n "$TEST_MAJOR_ID" ]; then
    result=$(test_endpoint "GET" "/majors/$TEST_MAJOR_ID" "" "Authorization: Bearer $ADMIN_TOKEN")
    http_code=$(echo "$result" | cut -d'|' -f1)
    body=$(echo "$result" | cut -d'|' -f2-)

    echo -e "       ${BLUE}Method:${NC} GET"
    echo -e "       ${BLUE}Endpoint:${NC} /majors/$TEST_MAJOR_ID"
    echo -e "       ${BLUE}Status:${NC} $http_code"
    echo ""

    if [ "$http_code" = "200" ]; then
        print_result "Get major by ID" "PASS" "HTTP 200 - Major retrieved successfully"
    else
        print_result "Get major by ID" "FAIL" "HTTP $http_code - $body"
    fi
elif [ -n "$ADMIN_TOKEN" ]; then
    # Try to get any major from the list
    result=$(test_endpoint "GET" "/majors" "" "Authorization: Bearer $ADMIN_TOKEN")
    body=$(echo "$result" | cut -d'|' -f2-)
    TEST_MAJOR_ID=$(echo "$body" | grep -o '"id":[0-9]*' | head -1 | cut -d':' -f2)

    if [ -n "$TEST_MAJOR_ID" ]; then
        result=$(test_endpoint "GET" "/majors/$TEST_MAJOR_ID" "" "Authorization: Bearer $ADMIN_TOKEN")
        http_code=$(echo "$result" | cut -d'|' -f1)
        body=$(echo "$result" | cut -d'|' -f2-)

        echo -e "       ${BLUE}Method:${NC} GET"
        echo -e "       ${BLUE}Endpoint:${NC} /majors/$TEST_MAJOR_ID"
        echo -e "       ${BLUE}Status:${NC} $http_code"
        echo ""

        if [ "$http_code" = "200" ]; then
            print_result "Get major by ID" "PASS" "HTTP 200 - Major retrieved successfully"
        else
            print_result "Get major by ID" "FAIL" "HTTP $http_code - $body"
        fi
    else
        print_result "Get major by ID" "SKIP" "No major ID available"
    fi
else
    print_result "Get major by ID" "SKIP" "No admin token available"
fi

# =============================================================================
# TEST 21: PUT /api/majors/{id} - Update major
# =============================================================================
echo -e "${YELLOW}========================================${NC}"
echo -e "${YELLOW}TEST 21: Update Major${NC}"
echo -e "${YELLOW}========================================${NC}"
echo ""

if [ -n "$ADMIN_TOKEN" ] && [ -n "$TEST_MAJOR_ID" ]; then
    update_major_data='{
        "name": "Updated Test Major",
        "description": "This major has been updated by automated testing",
        "degree_level": "doctorate",
        "duration_years": 5
    }'

    result=$(test_endpoint "PUT" "/majors/$TEST_MAJOR_ID" "$update_major_data" "Authorization: Bearer $ADMIN_TOKEN")
    http_code=$(echo "$result" | cut -d'|' -f1)
    body=$(echo "$result" | cut -d'|' -f2-)

    echo -e "       ${BLUE}Method:${NC} PUT"
    echo -e "       ${BLUE}Endpoint:${NC} /majors/$TEST_MAJOR_ID"
    echo -e "       ${BLUE}Status:${NC} $http_code"
    echo ""

    if [ "$http_code" = "200" ]; then
        print_result "Update major" "PASS" "HTTP 200 - Major updated successfully"
    else
        print_result "Update major" "FAIL" "HTTP $http_code - $body"
    fi
else
    print_result "Update major" "SKIP" "No admin token or major ID available"
fi

# =============================================================================
# TEST 22: GET /api/majors/{id}/courses - Get courses by major
# =============================================================================
echo -e "${YELLOW}========================================${NC}"
echo -e "${YELLOW}TEST 22: Get Courses by Major${NC}"
echo -e "${YELLOW}========================================${NC}"
echo ""

if [ -n "$ADMIN_TOKEN" ] && [ -n "$TEST_MAJOR_ID" ]; then
    result=$(test_endpoint "GET" "/majors/$TEST_MAJOR_ID/courses" "" "Authorization: Bearer $ADMIN_TOKEN")
    http_code=$(echo "$result" | cut -d'|' -f1)
    body=$(echo "$result" | cut -d'|' -f2-)

    echo -e "       ${BLUE}Method:${NC} GET"
    echo -e "       ${BLUE}Endpoint:${NC} /majors/$TEST_MAJOR_ID/courses"
    echo -e "       ${BLUE}Status:${NC} $http_code"
    echo ""

    if [ "$http_code" = "200" ]; then
        print_result "Get courses by major" "PASS" "HTTP 200 - Courses retrieved successfully"
    else
        print_result "Get courses by major" "FAIL" "HTTP $http_code - $body"
    fi
else
    print_result "Get courses by major" "SKIP" "No admin token or major ID available"
fi

# =============================================================================
# TEST 23: GET /api/majors/{id}/faculty - Get faculty for major
# =============================================================================
echo -e "${YELLOW}========================================${NC}"
echo -e "${YELLOW}TEST 23: Get Faculty for Major${NC}"
echo -e "${YELLOW}========================================${NC}"
echo ""

if [ -n "$ADMIN_TOKEN" ] && [ -n "$TEST_MAJOR_ID" ]; then
    result=$(test_endpoint "GET" "/majors/$TEST_MAJOR_ID/faculty" "" "Authorization: Bearer $ADMIN_TOKEN")
    http_code=$(echo "$result" | cut -d'|' -f1)
    body=$(echo "$result" | cut -d'|' -f2-)

    echo -e "       ${BLUE}Method:${NC} GET"
    echo -e "       ${BLUE}Endpoint:${NC} /majors/$TEST_MAJOR_ID/faculty"
    echo -e "       ${BLUE}Status:${NC} $http_code"
    echo ""

    if [ "$http_code" = "200" ]; then
        print_result "Get faculty for major" "PASS" "HTTP 200 - Faculty retrieved successfully"
    else
        print_result "Get faculty for major" "FAIL" "HTTP $http_code - $body"
    fi
else
    print_result "Get faculty for major" "SKIP" "No admin token or major ID available"
fi

# =============================================================================
# TEST 24: GET /api/majors without authentication
# =============================================================================
echo -e "${YELLOW}========================================${NC}"
echo -e "${YELLOW}TEST 24: List Majors Without Authentication${NC}"
echo -e "${YELLOW}========================================${NC}"
echo ""

result=$(test_endpoint "GET" "/majors" "" "")
http_code=$(echo "$result" | cut -d'|' -f1)
body=$(echo "$result" | cut -d'|' -f2-)

echo -e "       ${BLUE}Method:${NC} GET"
echo -e "       ${BLUE}Endpoint:${NC} /majors"
echo -e "       ${BLUE}Status:${NC} $http_code"
echo ""

if [ "$http_code" = "401" ]; then
    print_result "List majors without authentication" "PASS" "HTTP 401 - Unauthorized as expected"
else
    print_result "List majors without authentication" "FAIL" "Expected 401, got $http_code - $body"
fi

# =============================================================================
# TEST 25: GET /api/majors with invalid ID
# =============================================================================
echo -e "${YELLOW}========================================${NC}"
echo -e "${YELLOW}TEST 25: Get Major with Invalid ID${NC}"
echo -e "${YELLOW}========================================${NC}"
echo ""

if [ -n "$ADMIN_TOKEN" ]; then
    result=$(test_endpoint "GET" "/majors/999999" "" "Authorization: Bearer $ADMIN_TOKEN")
    http_code=$(echo "$result" | cut -d'|' -f1)
    body=$(echo "$result" | cut -d'|' -f2-)

    echo -e "       ${BLUE}Method:${NC} GET"
    echo -e "       ${BLUE}Endpoint:${NC} /majors/999999"
    echo -e "       ${BLUE}Status:${NC} $http_code"
    echo ""

    if [ "$http_code" = "404" ]; then
        print_result "Get major with invalid ID" "PASS" "HTTP 404 - Not found as expected"
    else
        print_result "Get major with invalid ID" "FAIL" "Expected 404, got $http_code - $body"
    fi
else
    print_result "Get major with invalid ID" "SKIP" "No admin token available"
fi

# =============================================================================
# TEST 26: PUT /api/majors/{id} with student token (should fail)
# =============================================================================
echo -e "${YELLOW}========================================${NC}"
echo -e "${YELLOW}TEST 26: Update Major (Student - should fail)${NC}"
echo -e "${YELLOW}========================================${NC}"
echo ""

if [ -n "$STUDENT_TOKEN" ] && [ -n "$TEST_MAJOR_ID" ]; then
    update_major_data='{
        "name": "Unauthorized Update"
    }'

    result=$(test_endpoint "PUT" "/majors/$TEST_MAJOR_ID" "$update_major_data" "Authorization: Bearer $STUDENT_TOKEN")
    http_code=$(echo "$result" | cut -d'|' -f1)
    body=$(echo "$result" | cut -d'|' -f2-)

    echo -e "       ${BLUE}Method:${NC} PUT"
    echo -e "       ${BLUE}Endpoint:${NC} /majors/$TEST_MAJOR_ID"
    echo -e "       ${BLUE}Status:${NC} $http_code"
    echo ""

    if [ "$http_code" = "403" ]; then
        print_result "Update major (student)" "PASS" "HTTP 403 - Forbidden as expected"
    else
        print_result "Update major (student)" "FAIL" "Expected 403, got $http_code - $body"
    fi
else
    print_result "Update major (student)" "SKIP" "No student token or major ID available"
fi

# =============================================================================
# TEST 27: POST /api/majors without faculty_id (validation error)
# =============================================================================
echo -e "${YELLOW}========================================${NC}"
echo -e "${YELLOW}TEST 27: Create Major Without Faculty ID${NC}"
echo -e "${YELLOW}========================================${NC}"
echo ""

if [ -n "$ADMIN_TOKEN" ]; then
    timestamp=$(date +%s)
    create_major_invalid='{
        "name": "Invalid Major '$timestamp'",
        "code": "IV'$timestamp'",
        "description": "This should fail validation"
    }'

    result=$(test_endpoint "POST" "/majors" "$create_major_invalid" "Authorization: Bearer $ADMIN_TOKEN")
    http_code=$(echo "$result" | cut -d'|' -f1)
    body=$(echo "$result" | cut -d'|' -f2-)

    echo -e "       ${BLUE}Method:${NC} POST"
    echo -e "       ${BLUE}Endpoint:${NC} /majors"
    echo -e "       ${BLUE}Status:${NC} $http_code"
    echo ""

    if [ "$http_code" = "422" ]; then
        print_result "Create major without faculty_id" "PASS" "HTTP 422 - Validation error as expected"
    else
        print_result "Create major without faculty_id" "FAIL" "Expected 422, got $http_code - $body"
    fi
else
    print_result "Create major without faculty_id" "SKIP" "No admin token available"
fi

# =============================================================================
# TEST 28: DELETE /api/majors/{id} - Delete major
# =============================================================================
echo -e "${YELLOW}========================================${NC}"
echo -e "${YELLOW}TEST 28: Delete Major${NC}"
echo -e "${YELLOW}========================================${NC}"
echo ""

if [ -n "$ADMIN_TOKEN" ] && [ -n "$TEST_MAJOR_ID" ]; then
    result=$(test_endpoint "DELETE" "/majors/$TEST_MAJOR_ID" "" "Authorization: Bearer $ADMIN_TOKEN")
    http_code=$(echo "$result" | cut -d'|' -f1)
    body=$(echo "$result" | cut -d'|' -f2-)

    echo -e "       ${BLUE}Method:${NC} DELETE"
    echo -e "       ${BLUE}Endpoint:${NC} /majors/$TEST_MAJOR_ID"
    echo -e "       ${BLUE}Status:${NC} $http_code"
    echo ""

    if [ "$http_code" = "204" ] || [ "$http_code" = "200" ]; then
        print_result "Delete major" "PASS" "HTTP $http_code - Major deleted successfully"
    elif [ "$http_code" = "409" ]; then
        print_result "Delete major" "PASS" "HTTP 409 - Cannot delete with active dependencies (expected)"
    else
        print_result "Delete major" "FAIL" "HTTP $http_code - $body"
    fi
else
    print_result "Delete major" "SKIP" "No admin token or major ID available"
fi

# =============================================================================
# TEST 29: POST /api/faculties with missing fields (validation error)
# =============================================================================
echo -e "${YELLOW}========================================${NC}"
echo -e "${YELLOW}TEST 29: Create Faculty with Missing Fields${NC}"
echo -e "${YELLOW}========================================${NC}"
echo ""

if [ -n "$ADMIN_TOKEN" ]; then
    create_faculty_invalid='{
        "name": "Incomplete Faculty"
    }'

    result=$(test_endpoint "POST" "/faculties" "$create_faculty_invalid" "Authorization: Bearer $ADMIN_TOKEN")
    http_code=$(echo "$result" | cut -d'|' -f1)
    body=$(echo "$result" | cut -d'|' -f2-)

    echo -e "       ${BLUE}Method:${NC} POST"
    echo -e "       ${BLUE}Endpoint:${NC} /faculties"
    echo -e "       ${BLUE}Status:${NC} $http_code"
    echo ""

    if [ "$http_code" = "422" ]; then
        print_result "Create faculty with missing fields" "PASS" "HTTP 422 - Validation error as expected"
    else
        print_result "Create faculty with missing fields" "FAIL" "Expected 422, got $http_code - $body"
    fi
else
    print_result "Create faculty with missing fields" "SKIP" "No admin token available"
fi

# =============================================================================
# TEST 30: GET /api/faculty/dashboard with student token (should fail)
# =============================================================================
echo -e "${YELLOW}========================================${NC}"
echo -e "${YELLOW}TEST 30: Faculty Dashboard (Student - should fail)${NC}"
echo -e "${YELLOW}========================================${NC}"
echo ""

if [ -n "$STUDENT_TOKEN" ]; then
    result=$(test_endpoint "GET" "/faculty/dashboard" "" "Authorization: Bearer $STUDENT_TOKEN")
    http_code=$(echo "$result" | cut -d'|' -f1)
    body=$(echo "$result" | cut -d'|' -f2-)

    echo -e "       ${BLUE}Method:${NC} GET"
    echo -e "       ${BLUE}Endpoint:${NC} /faculty/dashboard"
    echo -e "       ${BLUE}Status:${NC} $http_code"
    echo ""

    if [ "$http_code" = "403" ]; then
        print_result "Faculty dashboard (student)" "PASS" "HTTP 403 - Forbidden as expected"
    else
        print_result "Faculty dashboard (student)" "FAIL" "Expected 403, got $http_code - $body"
    fi
else
    print_result "Faculty dashboard (student)" "SKIP" "No student token available"
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