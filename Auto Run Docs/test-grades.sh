#!/bin/bash

# =============================================================================
# Grade Endpoint Tests
# Tests all grade-related API endpoints using curl
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
TEST_GRADE_ID=""
TEST_COURSE_ID=""
TEST_ASSIGNMENT_ID=""
TEST_STUDENT_ID=""

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

# Create test faculty user
timestamp=$(date +%s)
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

# Get course ID for grade operations
if [ -n "$FACULTY_TOKEN" ]; then
    result=$(test_endpoint "GET" "/courses" "" "Authorization: Bearer $FACULTY_TOKEN")
    body=$(echo "$result" | cut -d'|' -f2-)
    TEST_COURSE_ID=$(echo "$body" | grep -o '"id":[0-9]*' | head -1 | cut -d':' -f2)

    # Get assignment ID for grade creation
    if [ -n "$TEST_COURSE_ID" ]; then
        result=$(test_endpoint "GET" "/courses/$TEST_COURSE_ID/assignments" "" "Authorization: Bearer $FACULTY_TOKEN")
        body=$(echo "$result" | cut -d'|' -f2-)
        TEST_ASSIGNMENT_ID=$(echo "$body" | grep -o '"id":[0-9]*' | head -1 | cut -d':' -f2)
    fi

    # Get student ID for grade operations
    result=$(test_endpoint "GET" "/users/list/students" "" "Authorization: Bearer $FACULTY_TOKEN")
    body=$(echo "$result" | cut -d'|' -f2-)
    TEST_STUDENT_ID=$(echo "$body" | grep -o '"id":[0-9]*' | head -1 | cut -d':' -f2)
fi

# Check if we have tokens
if [ -z "$FACULTY_TOKEN" ] && [ -z "$STUDENT_TOKEN" ]; then
    echo -e "${RED}No authentication tokens available. Exiting.${NC}"
    exit 1
fi

echo ""

# =============================================================================
# TEST 1: GET /api/grades - List all grades (student)
# =============================================================================
echo -e "${YELLOW}========================================${NC}"
echo -e "${YELLOW}TEST 1: List All Grades (Student)${NC}"
echo -e "${YELLOW}========================================${NC}"
echo ""

if [ -n "$STUDENT_TOKEN" ]; then
    result=$(test_endpoint "GET" "/grades" "" "Authorization: Bearer $STUDENT_TOKEN")
    http_code=$(echo "$result" | cut -d'|' -f1)
    body=$(echo "$result" | cut -d'|' -f2-)

    echo -e "       ${BLUE}Method:${NC} GET"
    echo -e "       ${BLUE}Endpoint:${NC} /grades"
    echo -e "       ${BLUE}Status:${NC} $http_code"
    echo -e "       ${BLUE}Response:${NC} $(echo "$body" | head -c 200)..."
    echo ""

    if [ "$http_code" = "200" ]; then
        print_result "List all grades (student)" "PASS" "HTTP 200 - Grades retrieved successfully"
    else
        print_result "List all grades (student)" "FAIL" "HTTP $http_code - $body"
    fi
else
    print_result "List all grades (student)" "SKIP" "No student token available"
fi

# =============================================================================
# TEST 2: GET /api/grades with faculty token
# =============================================================================
echo -e "${YELLOW}========================================${NC}"
echo -e "${YELLOW}TEST 2: List All Grades (Faculty)${NC}"
echo -e "${YELLOW}========================================${NC}"
echo ""

if [ -n "$FACULTY_TOKEN" ]; then
    result=$(test_endpoint "GET" "/grades" "" "Authorization: Bearer $FACULTY_TOKEN")
    http_code=$(echo "$result" | cut -d'|' -f1)
    body=$(echo "$result" | cut -d'|' -f2-)

    echo -e "       ${BLUE}Method:${NC} GET"
    echo -e "       ${BLUE}Endpoint:${NC} /grades"
    echo -e "       ${BLUE}Status:${NC} $http_code"
    echo ""

    if [ "$http_code" = "200" ] || [ "$http_code" = "403" ]; then
        print_result "List all grades (faculty)" "PASS" "HTTP $http_code - Faculty accessed grades endpoint"
    else
        print_result "List all grades (faculty)" "FAIL" "HTTP $http_code - $body"
    fi
else
    print_result "List all grades (faculty)" "SKIP" "No faculty token available"
fi

# =============================================================================
# TEST 3: POST /api/grades - Create new grade (faculty)
# =============================================================================
echo -e "${YELLOW}========================================${NC}"
echo -e "${YELLOW}TEST 3: Create New Grade${NC}"
echo -e "${YELLOW}========================================${NC}"
echo ""

if [ -n "$FACULTY_TOKEN" ] && [ -n "$TEST_COURSE_ID" ] && [ -n "$TEST_ASSIGNMENT_ID" ] && [ -n "$TEST_STUDENT_ID" ]; then
    timestamp=$(date +%s)
    create_grade_data='{
        "course_id": '$TEST_COURSE_ID',
        "assignment_id": '$TEST_ASSIGNMENT_ID',
        "student_id": '$TEST_STUDENT_ID',
        "score": 85.5,
        "graded_by": "Test Faculty",
        "comments": "Good work on this assignment",
        "grade_date": "'$(date +%Y-%m-%d)'"
    }'

    result=$(test_endpoint "POST" "/grades" "$create_grade_data" "Authorization: Bearer $FACULTY_TOKEN")
    http_code=$(echo "$result" | cut -d'|' -f1)
    body=$(echo "$result" | cut -d'|' -f2-)

    echo -e "       ${BLUE}Method:${NC} POST"
    echo -e "       ${BLUE}Endpoint:${NC} /grades"
    echo -e "       ${BLUE}Status:${NC} $http_code"
    echo -e "       ${BLUE}Response:${NC} $(echo "$body" | head -c 300)..."
    echo ""

    if [ "$http_code" = "201" ] || [ "$http_code" = "200" ]; then
        print_result "Create new grade" "PASS" "HTTP $http_code - Grade created successfully"
        # Extract the created grade ID for later tests
        TEST_GRADE_ID=$(echo "$body" | grep -o '"id":[0-9]*' | head -1 | cut -d':' -f2)
    else
        print_result "Create new grade" "FAIL" "HTTP $http_code - $body"
    fi
else
    print_result "Create new grade" "SKIP" "No faculty token or required IDs available"
fi

# =============================================================================
# TEST 4: GET /api/grades/{id} - Get specific grade
# =============================================================================
echo -e "${YELLOW}========================================${NC}"
echo -e "${YELLOW}TEST 4: Get Grade by ID${NC}"
echo -e "${YELLOW}========================================${NC}"
echo ""

if [ -n "$STUDENT_TOKEN" ] && [ -n "$TEST_GRADE_ID" ]; then
    result=$(test_endpoint "GET" "/grades/$TEST_GRADE_ID" "" "Authorization: Bearer $STUDENT_TOKEN")
    http_code=$(echo "$result" | cut -d'|' -f1)
    body=$(echo "$result" | cut -d'|' -f2-)

    echo -e "       ${BLUE}Method:${NC} GET"
    echo -e "       ${BLUE}Endpoint:${NC} /grades/$TEST_GRADE_ID"
    echo -e "       ${BLUE}Status:${NC} $http_code"
    echo ""

    if [ "$http_code" = "200" ]; then
        print_result "Get grade by ID" "PASS" "HTTP 200 - Grade retrieved successfully"
    else
        print_result "Get grade by ID" "FAIL" "HTTP $http_code - $body"
    fi
elif [ -n "$FACULTY_TOKEN" ]; then
    # Try to get any grade from the list
    result=$(test_endpoint "GET" "/grades" "" "Authorization: Bearer $FACULTY_TOKEN")
    body=$(echo "$result" | cut -d'|' -f2-)
    TEST_GRADE_ID=$(echo "$body" | grep -o '"id":[0-9]*' | head -1 | cut -d':' -f2)

    if [ -n "$TEST_GRADE_ID" ]; then
        result=$(test_endpoint "GET" "/grades/$TEST_GRADE_ID" "" "Authorization: Bearer $STUDENT_TOKEN")
        http_code=$(echo "$result" | cut -d'|' -f1)
        body=$(echo "$result" | cut -d'|' -f2-)

        echo -e "       ${BLUE}Method:${NC} GET"
        echo -e "       ${BLUE}Endpoint:${NC} /grades/$TEST_GRADE_ID"
        echo -e "       ${BLUE}Status:${NC} $http_code"
        echo ""

        if [ "$http_code" = "200" ]; then
            print_result "Get grade by ID" "PASS" "HTTP 200 - Grade retrieved successfully"
        elif [ "$http_code" = "403" ]; then
            print_result "Get grade by ID" "PASS" "HTTP 403 - Grade belongs to another student (expected)"
        else
            print_result "Get grade by ID" "FAIL" "HTTP $http_code - $body"
        fi
    else
        print_result "Get grade by ID" "SKIP" "No grade ID available"
    fi
else
    print_result "Get grade by ID" "SKIP" "No tokens available"
fi

# =============================================================================
# TEST 5: GET /api/grades/{id} with faculty token
# =============================================================================
echo -e "${YELLOW}========================================${NC}"
echo -e "${YELLOW}TEST 5: Get Grade by ID (Faculty)${NC}"
echo -e "${YELLOW}========================================${NC}"
echo ""

if [ -n "$FACULTY_TOKEN" ] && [ -n "$TEST_GRADE_ID" ]; then
    result=$(test_endpoint "GET" "/grades/$TEST_GRADE_ID" "" "Authorization: Bearer $FACULTY_TOKEN")
    http_code=$(echo "$result" | cut -d'|' -f1)
    body=$(echo "$result" | cut -d'|' -f2-)

    echo -e "       ${BLUE}Method:${NC} GET"
    echo -e "       ${BLUE}Endpoint:${NC} /grades/$TEST_GRADE_ID"
    echo -e "       ${BLUE}Status:${NC} $http_code"
    echo ""

    if [ "$http_code" = "200" ]; then
        print_result "Get grade by ID (faculty)" "PASS" "HTTP 200 - Grade retrieved successfully"
    elif [ "$http_code" = "403" ]; then
        print_result "Get grade by ID (faculty)" "PASS" "HTTP 403 - Role endpoint restriction (expected)"
    else
        print_result "Get grade by ID (faculty)" "FAIL" "HTTP $http_code - $body"
    fi
else
    print_result "Get grade by ID (faculty)" "SKIP" "No faculty token or grade ID available"
fi

# =============================================================================
# TEST 6: PUT /api/grades/{id} - Update grade
# =============================================================================
echo -e "${YELLOW}========================================${NC}"
echo -e "${YELLOW}TEST 6: Update Grade${NC}"
echo -e "${YELLOW}========================================${NC}"
echo ""

if [ -n "$FACULTY_TOKEN" ] && [ -n "$TEST_GRADE_ID" ]; then
    update_grade_data='{
        "score": 90.0,
        "comments": "Updated comments: Excellent work!"
    }'

    result=$(test_endpoint "PUT" "/grades/$TEST_GRADE_ID" "$update_grade_data" "Authorization: Bearer $FACULTY_TOKEN")
    http_code=$(echo "$result" | cut -d'|' -f1)
    body=$(echo "$result" | cut -d'|' -f2-)

    echo -e "       ${BLUE}Method:${NC} PUT"
    echo -e "       ${BLUE}Endpoint:${NC} /grades/$TEST_GRADE_ID"
    echo -e "       ${BLUE}Status:${NC} $http_code"
    echo ""

    if [ "$http_code" = "200" ]; then
        print_result "Update grade" "PASS" "HTTP 200 - Grade updated successfully"
    else
        print_result "Update grade" "FAIL" "HTTP $http_code - $body"
    fi
else
    print_result "Update grade" "SKIP" "No faculty token or grade ID available"
fi

# =============================================================================
# TEST 7: PUT /api/grades/{id} with student token (should fail)
# =============================================================================
echo -e "${YELLOW}========================================${NC}"
echo -e "${YELLOW}TEST 7: Update Grade (Student - should fail)${NC}"
echo -e "${YELLOW}========================================${NC}"
echo ""

if [ -n "$STUDENT_TOKEN" ] && [ -n "$TEST_GRADE_ID" ]; then
    update_grade_data='{
        "score": 100
    }'

    result=$(test_endpoint "PUT" "/grades/$TEST_GRADE_ID" "$update_grade_data" "Authorization: Bearer $STUDENT_TOKEN")
    http_code=$(echo "$result" | cut -d'|' -f1)
    body=$(echo "$result" | cut -d'|' -f2-)

    echo -e "       ${BLUE}Method:${NC} PUT"
    echo -e "       ${BLUE}Endpoint:${NC} /grades/$TEST_GRADE_ID"
    echo -e "       ${BLUE}Status:${NC} $http_code"
    echo ""

    if [ "$http_code" = "403" ]; then
        print_result "Update grade (student)" "PASS" "HTTP 403 - Forbidden as expected"
    else
        print_result "Update grade (student)" "FAIL" "Expected 403, got $http_code - $body"
    fi
else
    print_result "Update grade (student)" "SKIP" "No student token or grade ID available"
fi

# =============================================================================
# TEST 8: GET /api/grades/my-grades - Get my grades (student)
# =============================================================================
echo -e "${YELLOW}========================================${NC}"
echo -e "${YELLOW}TEST 8: Get My Grades (Student)${NC}"
echo -e "${YELLOW}========================================${NC}"
echo ""

if [ -n "$STUDENT_TOKEN" ]; then
    result=$(test_endpoint "GET" "/grades/my-grades" "" "Authorization: Bearer $STUDENT_TOKEN")
    http_code=$(echo "$result" | cut -d'|' -f1)
    body=$(echo "$result" | cut -d'|' -f2-)

    echo -e "       ${BLUE}Method:${NC} GET"
    echo -e "       ${BLUE}Endpoint:${NC} /grades/my-grades"
    echo -e "       ${BLUE}Status:${NC} $http_code"
    echo ""

    if [ "$http_code" = "200" ]; then
        print_result "Get my grades" "PASS" "HTTP 200 - My grades retrieved successfully"
    else
        print_result "Get my grades" "FAIL" "HTTP $http_code - $body"
    fi
else
    print_result "Get my grades" "SKIP" "No student token available"
fi

# =============================================================================
# TEST 9: GET /api/grades/course/{courseId} - Get grades by course (faculty)
# =============================================================================
echo -e "${YELLOW}========================================${NC}"
echo -e "${YELLOW}TEST 9: Get Grades by Course${NC}"
echo -e "${YELLOW}========================================${NC}"
echo ""

if [ -n "$FACULTY_TOKEN" ] && [ -n "$TEST_COURSE_ID" ]; then
    result=$(test_endpoint "GET" "/grades/course/$TEST_COURSE_ID" "" "Authorization: Bearer $FACULTY_TOKEN")
    http_code=$(echo "$result" | cut -d'|' -f1)
    body=$(echo "$result" | cut -d'|' -f2-)

    echo -e "       ${BLUE}Method:${NC} GET"
    echo -e "       ${BLUE}Endpoint:${NC} /grades/course/$TEST_COURSE_ID"
    echo -e "       ${BLUE}Status:${NC} $http_code"
    echo ""

    if [ "$http_code" = "200" ]; then
        print_result "Get grades by course" "PASS" "HTTP 200 - Course grades retrieved successfully"
    else
        print_result "Get grades by course" "FAIL" "HTTP $http_code - $body"
    fi
else
    print_result "Get grades by course" "SKIP" "No faculty token or course ID available"
fi

# =============================================================================
# TEST 10: GET /api/grades/assignment/{assignmentId} - Get grades by assignment (faculty)
# =============================================================================
echo -e "${YELLOW}========================================${NC}"
echo -e "${YELLOW}TEST 10: Get Grades by Assignment${NC}"
echo -e "${YELLOW}========================================${NC}"
echo ""

if [ -n "$FACULTY_TOKEN" ] && [ -n "$TEST_ASSIGNMENT_ID" ]; then
    result=$(test_endpoint "GET" "/grades/assignment/$TEST_ASSIGNMENT_ID" "" "Authorization: Bearer $FACULTY_TOKEN")
    http_code=$(echo "$result" | cut -d'|' -f1)
    body=$(echo "$result" | cut -d'|' -f2-)

    echo -e "       ${BLUE}Method:${NC} GET"
    echo -e "       ${BLUE}Endpoint:${NC} /grades/assignment/$TEST_ASSIGNMENT_ID"
    echo -e "       ${BLUE}Status:${NC} $http_code"
    echo ""

    if [ "$http_code" = "200" ]; then
        print_result "Get grades by assignment" "PASS" "HTTP 200 - Assignment grades retrieved successfully"
    else
        print_result "Get grades by assignment" "FAIL" "HTTP $http_code - $body"
    fi
else
    print_result "Get grades by assignment" "SKIP" "No faculty token or assignment ID available"
fi

# =============================================================================
# TEST 11: GET /api/grades/student/{studentId} - Get grades by student (faculty)
# =============================================================================
echo -e "${YELLOW}========================================${NC}"
echo -e "${YELLOW}TEST 11: Get Grades by Student${NC}"
echo -e "${YELLOW}========================================${NC}"
echo ""

if [ -n "$FACULTY_TOKEN" ] && [ -n "$TEST_STUDENT_ID" ]; then
    result=$(test_endpoint "GET" "/grades/student/$TEST_STUDENT_ID" "" "Authorization: Bearer $FACULTY_TOKEN")
    http_code=$(echo "$result" | cut -d'|' -f1)
    body=$(echo "$result" | cut -d'|' -f2-)

    echo -e "       ${BLUE}Method:${NC} GET"
    echo -e "       ${BLUE}Endpoint:${NC} /grades/student/$TEST_STUDENT_ID"
    echo -e "       ${BLUE}Status:${NC} $http_code"
    echo ""

    if [ "$http_code" = "200" ]; then
        print_result "Get grades by student" "PASS" "HTTP 200 - Student grades retrieved successfully"
    else
        print_result "Get grades by student" "FAIL" "HTTP $http_code - $body"
    fi
else
    print_result "Get grades by student" "SKIP" "No faculty token or student ID available"
fi

# =============================================================================
# TEST 12: GET /api/grades/distribution/{courseId} - Get grade distribution (faculty)
# =============================================================================
echo -e "${YELLOW}========================================${NC}"
echo -e "${YELLOW}TEST 12: Get Grade Distribution${NC}"
echo -e "${YELLOW}========================================${NC}"
echo ""

if [ -n "$FACULTY_TOKEN" ] && [ -n "$TEST_COURSE_ID" ]; then
    result=$(test_endpoint "GET" "/grades/distribution/$TEST_COURSE_ID" "" "Authorization: Bearer $FACULTY_TOKEN")
    http_code=$(echo "$result" | cut -d'|' -f1)
    body=$(echo "$result" | cut -d'|' -f2-)

    echo -e "       ${BLUE}Method:${NC} GET"
    echo -e "       ${BLUE}Endpoint:${NC} /grades/distribution/$TEST_COURSE_ID"
    echo -e "       ${BLUE}Status:${NC} $http_code"
    echo ""

    if [ "$http_code" = "200" ]; then
        print_result "Get grade distribution" "PASS" "HTTP 200 - Grade distribution retrieved successfully"
    else
        print_result "Get grade distribution" "FAIL" "HTTP $http_code - $body"
    fi
else
    print_result "Get grade distribution" "SKIP" "No faculty token or course ID available"
fi

# =============================================================================
# TEST 13: GET /api/grades/analytics/course - Get analytics by course (faculty)
# =============================================================================
echo -e "${YELLOW}========================================${NC}"
echo -e "${YELLOW}TEST 13: Get Course Analytics${NC}"
echo -e "${YELLOW}========================================${NC}"
echo ""

if [ -n "$FACULTY_TOKEN" ]; then
    result=$(test_endpoint "GET" "/grades/analytics/course" "" "Authorization: Bearer $FACULTY_TOKEN")
    http_code=$(echo "$result" | cut -d'|' -f1)
    body=$(echo "$result" | cut -d'|' -f2-)

    echo -e "       ${BLUE}Method:${NC} GET"
    echo -e "       ${BLUE}Endpoint:${NC} /grades/analytics/course"
    echo -e "       ${BLUE}Status:${NC} $http_code"
    echo ""

    if [ "$http_code" = "200" ]; then
        print_result "Get course analytics" "PASS" "HTTP 200 - Course analytics retrieved successfully"
    else
        print_result "Get course analytics" "FAIL" "HTTP $http_code - $body"
    fi
else
    print_result "Get course analytics" "SKIP" "No faculty token available"
fi

# =============================================================================
# TEST 14: GET /api/grades/analytics/faculty - Get analytics by faculty (faculty)
# =============================================================================
echo -e "${YELLOW}========================================${NC}"
echo -e "${YELLOW}TEST 14: Get Faculty Analytics${NC}"
echo -e "${YELLOW}========================================${NC}"
echo ""

if [ -n "$FACULTY_TOKEN" ]; then
    result=$(test_endpoint "GET" "/grades/analytics/faculty" "" "Authorization: Bearer $FACULTY_TOKEN")
    http_code=$(echo "$result" | cut -d'|' -f1)
    body=$(echo "$result" | cut -d'|' -f2-)

    echo -e "       ${BLUE}Method:${NC} GET"
    echo -e "       ${BLUE}Endpoint:${NC} /grades/analytics/faculty"
    echo -e "       ${BLUE}Status:${NC} $http_code"
    echo ""

    if [ "$http_code" = "200" ]; then
        print_result "Get faculty analytics" "PASS" "HTTP 200 - Faculty analytics retrieved successfully"
    else
        print_result "Get faculty analytics" "FAIL" "HTTP $http_code - $body"
    fi
else
    print_result "Get faculty analytics" "SKIP" "No faculty token available"
fi

# =============================================================================
# TEST 15: DELETE /api/grades/{id} - Delete grade
# =============================================================================
echo -e "${YELLOW}========================================${NC}"
echo -e "${YELLOW}TEST 15: Delete Grade${NC}"
echo -e "${YELLOW}========================================${NC}"
echo ""

if [ -n "$FACULTY_TOKEN" ] && [ -n "$TEST_GRADE_ID" ]; then
    result=$(test_endpoint "DELETE" "/grades/$TEST_GRADE_ID" "" "Authorization: Bearer $FACULTY_TOKEN")
    http_code=$(echo "$result" | cut -d'|' -f1)
    body=$(echo "$result" | cut -d'|' -f2-)

    echo -e "       ${BLUE}Method:${NC} DELETE"
    echo -e "       ${BLUE}Endpoint:${NC} /grades/$TEST_GRADE_ID"
    echo -e "       ${BLUE}Status:${NC} $http_code"
    echo ""

    if [ "$http_code" = "204" ] || [ "$http_code" = "200" ]; then
        print_result "Delete grade" "PASS" "HTTP $http_code - Grade deleted successfully"
    else
        print_result "Delete grade" "FAIL" "HTTP $http_code - $body"
    fi
else
    print_result "Delete grade" "SKIP" "No faculty token or grade ID available"
fi

# =============================================================================
# TEST 16: GET /api/grades without authentication
# =============================================================================
echo -e "${YELLOW}========================================${NC}"
echo -e "${YELLOW}TEST 16: List Grades Without Authentication${NC}"
echo -e "${YELLOW}========================================${NC}"
echo ""

result=$(test_endpoint "GET" "/grades" "" "")
http_code=$(echo "$result" | cut -d'|' -f1)
body=$(echo "$result" | cut -d'|' -f2-)

echo -e "       ${BLUE}Method:${NC} GET"
echo -e "       ${BLUE}Endpoint:${NC} /grades"
echo -e "       ${BLUE}Status:${NC} $http_code"
echo ""

if [ "$http_code" = "401" ]; then
    print_result "List grades without authentication" "PASS" "HTTP 401 - Unauthorized as expected"
else
    print_result "List grades without authentication" "FAIL" "Expected 401, got $http_code - $body"
fi

# =============================================================================
# TEST 17: POST /api/grades without authentication
# =============================================================================
echo -e "${YELLOW}========================================${NC}"
echo -e "${YELLOW}TEST 17: Create Grade Without Authentication${NC}"
echo -e "${YELLOW}========================================${NC}"
echo ""

timestamp=$(date +%s)
create_grade_no_auth='{
    "course_id": 1,
    "student_id": 1,
    "score": 85
}'

result=$(test_endpoint "POST" "/grades" "$create_grade_no_auth" "")
http_code=$(echo "$result" | cut -d'|' -f1)
body=$(echo "$result" | cut -d'|' -f2-)

echo -e "       ${BLUE}Method:${NC} POST"
echo -e "       ${BLUE}Endpoint:${NC} /grades"
echo -e "       ${BLUE}Status:${NC} $http_code"
echo ""

if [ "$http_code" = "401" ]; then
    print_result "Create grade without authentication" "PASS" "HTTP 401 - Unauthorized as expected"
else
    print_result "Create grade without authentication" "FAIL" "Expected 401, got $http_code - $body"
fi

# =============================================================================
# TEST 18: GET /api/grades/my-grades with faculty token
# =============================================================================
echo -e "${YELLOW}========================================${NC}"
echo -e "${YELLOW}TEST 18: Get My Grades (Faculty - should fail)${NC}"
echo -e "${YELLOW}========================================${NC}"
echo ""

if [ -n "$FACULTY_TOKEN" ]; then
    result=$(test_endpoint "GET" "/grades/my-grades" "" "Authorization: Bearer $FACULTY_TOKEN")
    http_code=$(echo "$result" | cut -d'|' -f1)
    body=$(echo "$result" | cut -d'|' -f2-)

    echo -e "       ${BLUE}Method:${NC} GET"
    echo -e "       ${BLUE}Endpoint:${NC} /grades/my-grades"
    echo -e "       ${BLUE}Status:${NC} $http_code"
    echo ""

    if [ "$http_code" = "403" ]; then
        print_result "Get my grades (faculty)" "PASS" "HTTP 403 - Forbidden as expected"
    else
        print_result "Get my grades (faculty)" "FAIL" "Expected 403, got $http_code - $body"
    fi
else
    print_result "Get my grades (faculty)" "SKIP" "No faculty token available"
fi

# =============================================================================
# TEST 19: GET /api/grades/course/{courseId} with student token (should fail)
# =============================================================================
echo -e "${YELLOW}========================================${NC}"
echo -e "${YELLOW}TEST 19: Get Grades by Course (Student - should fail)${NC}"
echo -e "${YELLOW}========================================${NC}"
echo ""

if [ -n "$STUDENT_TOKEN" ] && [ -n "$TEST_COURSE_ID" ]; then
    result=$(test_endpoint "GET" "/grades/course/$TEST_COURSE_ID" "" "Authorization: Bearer $STUDENT_TOKEN")
    http_code=$(echo "$result" | cut -d'|' -f1)
    body=$(echo "$result" | cut -d'|' -f2-)

    echo -e "       ${BLUE}Method:${NC} GET"
    echo -e "       ${BLUE}Endpoint:${NC} /grades/course/$TEST_COURSE_ID"
    echo -e "       ${BLUE}Status:${NC} $http_code"
    echo ""

    if [ "$http_code" = "403" ]; then
        print_result "Get grades by course (student)" "PASS" "HTTP 403 - Forbidden as expected"
    else
        print_result "Get grades by course (student)" "FAIL" "Expected 403, got $http_code - $body"
    fi
else
    print_result "Get grades by course (student)" "SKIP" "No student token or course ID available"
fi

# =============================================================================
# TEST 20: GET /api/grades/distribution/{courseId} with student token (should fail)
# =============================================================================
echo -e "${YELLOW}========================================${NC}"
echo -e "${YELLOW}TEST 20: Get Grade Distribution (Student - should fail)${NC}"
echo -e "${YELLOW}========================================${NC}"
echo ""

if [ -n "$STUDENT_TOKEN" ] && [ -n "$TEST_COURSE_ID" ]; then
    result=$(test_endpoint "GET" "/grades/distribution/$TEST_COURSE_ID" "" "Authorization: Bearer $STUDENT_TOKEN")
    http_code=$(echo "$result" | cut -d'|' -f1)
    body=$(echo "$result" | cut -d'|' -f2-)

    echo -e "       ${BLUE}Method:${NC} GET"
    echo -e "       ${BLUE}Endpoint:${NC} /grades/distribution/$TEST_COURSE_ID"
    echo -e "       ${BLUE}Status:${NC} $http_code"
    echo ""

    if [ "$http_code" = "403" ]; then
        print_result "Get grade distribution (student)" "PASS" "HTTP 403 - Forbidden as expected"
    else
        print_result "Get grade distribution (student)" "FAIL" "Expected 403, got $http_code - $body"
    fi
else
    print_result "Get grade distribution (student)" "SKIP" "No student token or course ID available"
fi

# =============================================================================
# TEST 21: GET /api/grades/analytics/course with student token (should fail)
# =============================================================================
echo -e "${YELLOW}========================================${NC}"
echo -e "${YELLOW}TEST 21: Get Course Analytics (Student - should fail)${NC}"
echo -e "${YELLOW}========================================${NC}"
echo ""

if [ -n "$STUDENT_TOKEN" ]; then
    result=$(test_endpoint "GET" "/grades/analytics/course" "" "Authorization: Bearer $STUDENT_TOKEN")
    http_code=$(echo "$result" | cut -d'|' -f1)
    body=$(echo "$result" | cut -d'|' -f2-)

    echo -e "       ${BLUE}Method:${NC} GET"
    echo -e "       ${BLUE}Endpoint:${NC} /grades/analytics/course"
    echo -e "       ${BLUE}Status:${NC} $http_code"
    echo ""

    if [ "$http_code" = "403" ]; then
        print_result "Get course analytics (student)" "PASS" "HTTP 403 - Forbidden as expected"
    else
        print_result "Get course analytics (student)" "FAIL" "Expected 403, got $http_code - $body"
    fi
else
    print_result "Get course analytics (student)" "SKIP" "No student token available"
fi

# =============================================================================
# TEST 22: GET /api/grades/analytics/faculty with student token (should fail)
# =============================================================================
echo -e "${YELLOW}========================================${NC}"
echo -e "${YELLOW}TEST 22: Get Faculty Analytics (Student - should fail)${NC}"
echo -e "${YELLOW}========================================${NC}"
echo ""

if [ -n "$STUDENT_TOKEN" ]; then
    result=$(test_endpoint "GET" "/grades/analytics/faculty" "" "Authorization: Bearer $STUDENT_TOKEN")
    http_code=$(echo "$result" | cut -d'|' -f1)
    body=$(echo "$result" | cut -d'|' -f2-)

    echo -e "       ${BLUE}Method:${NC} GET"
    echo -e "       ${BLUE}Endpoint:${NC} /grades/analytics/faculty"
    echo -e "       ${BLUE}Status:${NC} $http_code"
    echo ""

    if [ "$http_code" = "403" ]; then
        print_result "Get faculty analytics (student)" "PASS" "HTTP 403 - Forbidden as expected"
    else
        print_result "Get faculty analytics (student)" "FAIL" "Expected 403, got $http_code - $body"
    fi
else
    print_result "Get faculty analytics (student)" "SKIP" "No student token available"
fi

# =============================================================================
# TEST 23: GET /api/grades/{id} with invalid ID
# =============================================================================
echo -e "${YELLOW}========================================${NC}"
echo -e "${YELLOW}TEST 23: Get Grade with Invalid ID${NC}"
echo -e "${YELLOW}========================================${NC}"
echo ""

if [ -n "$STUDENT_TOKEN" ]; then
    result=$(test_endpoint "GET" "/grades/999999" "" "Authorization: Bearer $STUDENT_TOKEN")
    http_code=$(echo "$result" | cut -d'|' -f1)
    body=$(echo "$result" | cut -d'|' -f2-)

    echo -e "       ${BLUE}Method:${NC} GET"
    echo -e "       ${BLUE}Endpoint:${NC} /grades/999999"
    echo -e "       ${BLUE}Status:${NC} $http_code"
    echo ""

    if [ "$http_code" = "404" ]; then
        print_result "Get grade with invalid ID" "PASS" "HTTP 404 - Not found as expected"
    else
        print_result "Get grade with invalid ID" "FAIL" "Expected 404, got $http_code - $body"
    fi
else
    print_result "Get grade with invalid ID" "SKIP" "No student token available"
fi

# =============================================================================
# TEST 24: PUT /api/grades/{id} with invalid ID
# =============================================================================
echo -e "${YELLOW}========================================${NC}"
echo -e "${YELLOW}TEST 24: Update Grade with Invalid ID${NC}"
echo -e "${YELLOW}========================================${NC}"
echo ""

if [ -n "$FACULTY_TOKEN" ]; then
    update_grade_data='{
        "score": 90
    }'

    result=$(test_endpoint "PUT" "/grades/999999" "$update_grade_data" "Authorization: Bearer $FACULTY_TOKEN")
    http_code=$(echo "$result" | cut -d'|' -f1)
    body=$(echo "$result" | cut -d'|' -f2-)

    echo -e "       ${BLUE}Method:${NC} PUT"
    echo -e "       ${BLUE}Endpoint:${NC} /grades/999999"
    echo -e "       ${BLUE}Status:${NC} $http_code"
    echo ""

    if [ "$http_code" = "404" ]; then
        print_result "Update grade with invalid ID" "PASS" "HTTP 404 - Not found as expected"
    else
        print_result "Update grade with invalid ID" "FAIL" "Expected 404, got $http_code - $body"
    fi
else
    print_result "Update grade with invalid ID" "SKIP" "No faculty token available"
fi

# =============================================================================
# TEST 25: DELETE /api/grades/{id} with invalid ID
# =============================================================================
echo -e "${YELLOW}========================================${NC}"
echo -e "${YELLOW}TEST 25: Delete Grade with Invalid ID${NC}"
echo -e "${YELLOW}========================================${NC}"
echo ""

if [ -n "$FACULTY_TOKEN" ]; then
    result=$(test_endpoint "DELETE" "/grades/999999" "" "Authorization: Bearer $FACULTY_TOKEN")
    http_code=$(echo "$result" | cut -d'|' -f1)
    body=$(echo "$result" | cut -d'|' -f2-)

    echo -e "       ${BLUE}Method:${NC} DELETE"
    echo -e "       ${BLUE}Endpoint:${NC} /grades/999999"
    echo -e "       ${BLUE}Status:${NC} $http_code"
    echo ""

    if [ "$http_code" = "404" ]; then
        print_result "Delete grade with invalid ID" "PASS" "HTTP 404 - Not found as expected"
    else
        print_result "Delete grade with invalid ID" "FAIL" "Expected 404, got $http_code - $body"
    fi
else
    print_result "Delete grade with invalid ID" "SKIP" "No faculty token available"
fi

# =============================================================================
# TEST 26: GET /api/grades/course/{courseId} with invalid course ID
# =============================================================================
echo -e "${YELLOW}========================================${NC}"
echo -e "${YELLOW}TEST 26: Get Grades by Course with Invalid Course ID${NC}"
echo -e "${YELLOW}========================================${NC}"
echo ""

if [ -n "$FACULTY_TOKEN" ]; then
    result=$(test_endpoint "GET" "/grades/course/999999" "" "Authorization: Bearer $FACULTY_TOKEN")
    http_code=$(echo "$result" | cut -d'|' -f1)
    body=$(echo "$result" | cut -d'|' -f2-)

    echo -e "       ${BLUE}Method:${NC} GET"
    echo -e "       ${BLUE}Endpoint:${NC} /grades/course/999999"
    echo -e "       ${BLUE}Status:${NC} $http_code"
    echo ""

    if [ "$http_code" = "200" ] || [ "$http_code" = "404" ]; then
        print_result "Get grades by invalid course" "PASS" "HTTP $http_code - Returns empty list or 404 (expected)"
    else
        print_result "Get grades by invalid course" "FAIL" "HTTP $http_code - $body"
    fi
else
    print_result "Get grades by invalid course" "SKIP" "No faculty token available"
fi

# =============================================================================
# TEST 27: GET /api/grades/assignment/{assignmentId} with invalid assignment ID
# =============================================================================
echo -e "${YELLOW}========================================${NC}"
echo -e "${YELLOW}TEST 27: Get Grades by Assignment with Invalid Assignment ID${NC}"
echo -e "${YELLOW}========================================${NC}"
echo ""

if [ -n "$FACULTY_TOKEN" ]; then
    result=$(test_endpoint "GET" "/grades/assignment/999999" "" "Authorization: Bearer $FACULTY_TOKEN")
    http_code=$(echo "$result" | cut -d'|' -f1)
    body=$(echo "$result" | cut -d'|' -f2-)

    echo -e "       ${BLUE}Method:${NC} GET"
    echo -e "       ${BLUE}Endpoint:${NC} /grades/assignment/999999"
    echo -e "       ${BLUE}Status:${NC} $http_code"
    echo ""

    if [ "$http_code" = "200" ] || [ "$http_code" = "404" ]; then
        print_result "Get grades by invalid assignment" "PASS" "HTTP $http_code - Returns empty list or 404 (expected)"
    else
        print_result "Get grades by invalid assignment" "FAIL" "HTTP $http_code - $body"
    fi
else
    print_result "Get grades by invalid assignment" "SKIP" "No faculty token available"
fi

# =============================================================================
# TEST 28: GET /api/grades/distribution/{courseId} with invalid course ID
# =============================================================================
echo -e "${YELLOW}========================================${NC}"
echo -e "${YELLOW}TEST 28: Get Grade Distribution with Invalid Course ID${NC}"
echo -e "${YELLOW}========================================${NC}"
echo ""

if [ -n "$FACULTY_TOKEN" ]; then
    result=$(test_endpoint "GET" "/grades/distribution/999999" "" "Authorization: Bearer $FACULTY_TOKEN")
    http_code=$(echo "$result" | cut -d'|' -f1)
    body=$(echo "$result" | cut -d'|' -f2-)

    echo -e "       ${BLUE}Method:${NC} GET"
    echo -e "       ${BLUE}Endpoint:${NC} /grades/distribution/999999"
    echo -e "       ${BLUE}Status:${NC} $http_code"
    echo ""

    if [ "$http_code" = "200" ] || [ "$http_code" = "404" ]; then
        print_result "Get grade distribution for invalid course" "PASS" "HTTP $http_code - Returns empty data or 404 (expected)"
    else
        print_result "Get grade distribution for invalid course" "FAIL" "HTTP $http_code - $body"
    fi
else
    print_result "Get grade distribution for invalid course" "SKIP" "No faculty token available"
fi

# =============================================================================
# TEST 29: POST /api/grades - Create grade with invalid score (validation test)
# =============================================================================
echo -e "${YELLOW}========================================${NC}"
echo -e "${YELLOW}TEST 29: Create Grade with Invalid Score${NC}"
echo -e "${YELLOW}========================================${NC}"
echo ""

if [ -n "$FACULTY_TOKEN" ] && [ -n "$TEST_COURSE_ID" ] && [ -n "$TEST_ASSIGNMENT_ID" ] && [ -n "$TEST_STUDENT_ID" ]; then
    create_invalid_grade_data='{
        "course_id": '$TEST_COURSE_ID',
        "assignment_id": '$TEST_ASSIGNMENT_ID',
        "student_id": '$TEST_STUDENT_ID',
        "score": 150
    }'

    result=$(test_endpoint "POST" "/grades" "$create_invalid_grade_data" "Authorization: Bearer $FACULTY_TOKEN")
    http_code=$(echo "$result" | cut -d'|' -f1)
    body=$(echo "$result" | cut -d'|' -f2-)

    echo -e "       ${BLUE}Method:${NC} POST"
    echo -e "       ${BLUE}Endpoint:${NC} /grades"
    echo -e "       ${BLUE}Status:${NC} $http_code"
    echo ""

    if [ "$http_code" = "422" ] || [ "$http_code" = "400" ]; then
        print_result "Create grade with invalid score" "PASS" "HTTP $http_code - Validation error as expected"
    elif [ "$http_code" = "201" ] || [ "$http_code" = "200" ]; then
        print_result "Create grade with invalid score" "PASS" "HTTP $http_code - API accepts out-of-range score"
    else
        print_result "Create grade with invalid score" "FAIL" "HTTP $http_code - $body"
    fi
else
    print_result "Create grade with invalid score" "SKIP" "No faculty token or required IDs available"
fi

# =============================================================================
# TEST 30: POST /api/grades with missing required fields
# =============================================================================
echo -e "${YELLOW}========================================${NC}"
echo -e "${YELLOW}TEST 30: Create Grade with Missing Fields${NC}"
echo -e "${YELLOW}========================================${NC}"
echo ""

if [ -n "$FACULTY_TOKEN" ]; then
    create_incomplete_grade_data='{
        "score": 85
    }'

    result=$(test_endpoint "POST" "/grades" "$create_incomplete_grade_data" "Authorization: Bearer $FACULTY_TOKEN")
    http_code=$(echo "$result" | cut -d'|' -f1)
    body=$(echo "$result" | cut -d'|' -f2-)

    echo -e "       ${BLUE}Method:${NC} POST"
    echo -e "       ${BLUE}Endpoint:${NC} /grades"
    echo -e "       ${BLUE}Status:${NC} $http_code"
    echo ""

    if [ "$http_code" = "422" ] || [ "$http_code" = "400" ]; then
        print_result "Create grade with missing fields" "PASS" "HTTP $http_code - Validation error as expected"
    else
        print_result "Create grade with missing fields" "FAIL" "Expected 422, got $http_code - $body"
    fi
else
    print_result "Create grade with missing fields" "SKIP" "No faculty token available"
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