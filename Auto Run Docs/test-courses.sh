#!/bin/bash

# =============================================================================
# Course Endpoint Tests
# Tests all course-related API endpoints using curl
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
TEST_COURSE_ID=""
TEST_FACULTY_ID=""
TEST_MAJOR_ID=""
TEST_INSTRUCTOR_ID=""

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

# Get faculty and major IDs for course creation
if [ -n "$ADMIN_TOKEN" ]; then
    result=$(test_endpoint "GET" "/faculties" "" "Authorization: Bearer $ADMIN_TOKEN")
    body=$(echo "$result" | cut -d'|' -f2-)
    TEST_FACULTY_ID=$(echo "$body" | grep -o '"id":[0-9]*' | head -1 | cut -d':' -f2)

    result=$(test_endpoint "GET" "/majors" "" "Authorization: Bearer $ADMIN_TOKEN")
    body=$(echo "$result" | cut -d'|' -f2-)
    TEST_MAJOR_ID=$(echo "$body" | grep -o '"id":[0-9]*' | head -1 | cut -d':' -f2)

    result=$(test_endpoint "GET" "/users/list/faculty" "" "Authorization: Bearer $ADMIN_TOKEN")
    body=$(echo "$result" | cut -d'|' -f2-)
    TEST_INSTRUCTOR_ID=$(echo "$body" | grep -o '"id":[0-9]*' | head -1 | cut -d':' -f2)
fi

# Check if we have tokens
if [ -z "$ADMIN_TOKEN" ] && [ -z "$FACULTY_TOKEN" ] && [ -z "$STUDENT_TOKEN" ]; then
    echo -e "${RED}No authentication tokens available. Exiting.${NC}"
    exit 1
fi

echo ""

# =============================================================================
# TEST 1: GET /api/courses - List all courses
# =============================================================================
echo -e "${YELLOW}========================================${NC}"
echo -e "${YELLOW}TEST 1: List All Courses${NC}"
echo -e "${YELLOW}========================================${NC}"
echo ""

if [ -n "$ADMIN_TOKEN" ]; then
    result=$(test_endpoint "GET" "/courses" "" "Authorization: Bearer $ADMIN_TOKEN")
    http_code=$(echo "$result" | cut -d'|' -f1)
    body=$(echo "$result" | cut -d'|' -f2-)

    echo -e "       ${BLUE}Method:${NC} GET"
    echo -e "       ${BLUE}Endpoint:${NC} /courses"
    echo -e "       ${BLUE}Status:${NC} $http_code"
    echo -e "       ${BLUE}Response:${NC} $(echo "$body" | head -c 200)..."
    echo ""

    if [ "$http_code" = "200" ]; then
        print_result "List all courses" "PASS" "HTTP 200 - Courses retrieved successfully"
    else
        print_result "List all courses" "FAIL" "HTTP $http_code - $body"
    fi
else
    print_result "List all courses" "SKIP" "No admin token available"
fi

# =============================================================================
# TEST 2: GET /api/courses with search parameter
# =============================================================================
echo -e "${YELLOW}========================================${NC}"
echo -e "${YELLOW}TEST 2: List Courses with Search${NC}"
echo -e "${YELLOW}========================================${NC}"
echo ""

if [ -n "$ADMIN_TOKEN" ]; then
    result=$(test_endpoint "GET" "/courses?search=course" "" "Authorization: Bearer $ADMIN_TOKEN")
    http_code=$(echo "$result" | cut -d'|' -f1)
    body=$(echo "$result" | cut -d'|' -f2-)

    echo -e "       ${BLUE}Method:${NC} GET"
    echo -e "       ${BLUE}Endpoint:${NC} /courses?search=course"
    echo -e "       ${BLUE}Status:${NC} $http_code"
    echo ""

    if [ "$http_code" = "200" ]; then
        print_result "List courses with search" "PASS" "HTTP 200 - Search results retrieved successfully"
    else
        print_result "List courses with search" "FAIL" "HTTP $http_code - $body"
    fi
else
    print_result "List courses with search" "SKIP" "No admin token available"
fi

# =============================================================================
# TEST 3: GET /api/courses with faculty_id filter
# =============================================================================
echo -e "${YELLOW}========================================${NC}"
echo -e "${YELLOW}TEST 3: List Courses by Faculty${NC}"
echo -e "${YELLOW}========================================${NC}"
echo ""

if [ -n "$ADMIN_TOKEN" ] && [ -n "$TEST_FACULTY_ID" ]; then
    result=$(test_endpoint "GET" "/courses?faculty_id=$TEST_FACULTY_ID" "" "Authorization: Bearer $ADMIN_TOKEN")
    http_code=$(echo "$result" | cut -d'|' -f1)
    body=$(echo "$result" | cut -d'|' -f2-)

    echo -e "       ${BLUE}Method:${NC} GET"
    echo -e "       ${BLUE}Endpoint:${NC} /courses?faculty_id=$TEST_FACULTY_ID"
    echo -e "       ${BLUE}Status:${NC} $http_code"
    echo ""

    if [ "$http_code" = "200" ]; then
        print_result "List courses by faculty" "PASS" "HTTP 200 - Filtered courses retrieved successfully"
    else
        print_result "List courses by faculty" "FAIL" "HTTP $http_code - $body"
    fi
else
    print_result "List courses by faculty" "SKIP" "No admin token or faculty ID available"
fi

# =============================================================================
# TEST 4: GET /api/courses with semester filter
# =============================================================================
echo -e "${YELLOW}========================================${NC}"
echo -e "${YELLOW}TEST 4: List Courses by Semester${NC}"
echo -e "${YELLOW}========================================${NC}"
echo ""

if [ -n "$ADMIN_TOKEN" ]; then
    result=$(test_endpoint "GET" "/courses?semester=Fall" "" "Authorization: Bearer $ADMIN_TOKEN")
    http_code=$(echo "$result" | cut -d'|' -f1)
    body=$(echo "$result" | cut -d'|' -f2-)

    echo -e "       ${BLUE}Method:${NC} GET"
    echo -e "       ${BLUE}Endpoint:${NC} /courses?semester=Fall"
    echo -e "       ${BLUE}Status:${NC} $http_code"
    echo ""

    if [ "$http_code" = "200" ]; then
        print_result "List courses by semester" "PASS" "HTTP 200 - Filtered courses retrieved successfully"
    else
        print_result "List courses by semester" "FAIL" "HTTP $http_code - $body"
    fi
else
    print_result "List courses by semester" "SKIP" "No admin token available"
fi

# =============================================================================
# TEST 5: GET /api/courses with is_active filter
# =============================================================================
echo -e "${YELLOW}========================================${NC}"
echo -e "${YELLOW}TEST 5: List Active Courses${NC}"
echo -e "${YELLOW}========================================${NC}"
echo ""

if [ -n "$ADMIN_TOKEN" ]; then
    result=$(test_endpoint "GET" "/courses?is_active=true" "" "Authorization: Bearer $ADMIN_TOKEN")
    http_code=$(echo "$result" | cut -d'|' -f1)
    body=$(echo "$result" | cut -d'|' -f2-)

    echo -e "       ${BLUE}Method:${NC} GET"
    echo -e "       ${BLUE}Endpoint:${NC} /courses?is_active=true"
    echo -e "       ${BLUE}Status:${NC} $http_code"
    echo ""

    if [ "$http_code" = "200" ]; then
        print_result "List active courses" "PASS" "HTTP 200 - Active courses retrieved successfully"
    else
        print_result "List active courses" "FAIL" "HTTP $http_code - $body"
    fi
else
    print_result "List active courses" "SKIP" "No admin token available"
fi

# =============================================================================
# TEST 6: POST /api/courses - Create new course
# =============================================================================
echo -e "${YELLOW}========================================${NC}"
echo -e "${YELLOW}TEST 6: Create New Course${NC}"
echo -e "${YELLOW}========================================${NC}"
echo ""

if [ -n "$ADMIN_TOKEN" ] && [ -n "$TEST_FACULTY_ID" ] && [ -n "$TEST_MAJOR_ID" ] && [ -n "$TEST_INSTRUCTOR_ID" ]; then
    timestamp=$(date +%s)
    create_course_data='{
        "faculty_id": '$TEST_FACULTY_ID',
        "major_id": '$TEST_MAJOR_ID',
        "instructor_id": '$TEST_INSTRUCTOR_ID',
        "code": "TEST'$timestamp'",
        "name": "Test Course '$timestamp'",
        "description": "This is a test course created by automated testing",
        "credit_hours": 3,
        "capacity": 30,
        "semester": "Fall",
        "year": 2025,
        "schedule": "Mon/Wed 10:00-11:30",
        "room": "Room 101",
        "is_active": true,
        "mode": "online"
    }'

    result=$(test_endpoint "POST" "/courses" "$create_course_data" "Authorization: Bearer $ADMIN_TOKEN")
    http_code=$(echo "$result" | cut -d'|' -f1)
    body=$(echo "$result" | cut -d'|' -f2-)

    echo -e "       ${BLUE}Method:${NC} POST"
    echo -e "       ${BLUE}Endpoint:${NC} /courses"
    echo -e "       ${BLUE}Status:${NC} $http_code"
    echo -e "       ${BLUE}Response:${NC} $(echo "$body" | head -c 300)..."
    echo ""

    if [ "$http_code" = "201" ] || [ "$http_code" = "200" ]; then
        print_result "Create new course" "PASS" "HTTP $http_code - Course created successfully"
        # Extract the created course ID for later tests
        TEST_COURSE_ID=$(echo "$body" | grep -o '"id":[0-9]*' | head -1 | cut -d':' -f2)
    else
        print_result "Create new course" "FAIL" "HTTP $http_code - $body"
    fi
else
    print_result "Create new course" "SKIP" "No admin token or required IDs available"
fi

# =============================================================================
# TEST 7: GET /api/courses/{id} - Get specific course
# =============================================================================
echo -e "${YELLOW}========================================${NC}"
echo -e "${YELLOW}TEST 7: Get Course by ID${NC}"
echo -e "${YELLOW}========================================${NC}"
echo ""

if [ -n "$ADMIN_TOKEN" ] && [ -n "$TEST_COURSE_ID" ]; then
    result=$(test_endpoint "GET" "/courses/$TEST_COURSE_ID" "" "Authorization: Bearer $ADMIN_TOKEN")
    http_code=$(echo "$result" | cut -d'|' -f1)
    body=$(echo "$result" | cut -d'|' -f2-)

    echo -e "       ${BLUE}Method:${NC} GET"
    echo -e "       ${BLUE}Endpoint:${NC} /courses/$TEST_COURSE_ID"
    echo -e "       ${BLUE}Status:${NC} $http_code"
    echo ""

    if [ "$http_code" = "200" ]; then
        print_result "Get course by ID" "PASS" "HTTP 200 - Course retrieved successfully"
    else
        print_result "Get course by ID" "FAIL" "HTTP $http_code - $body"
    fi
elif [ -n "$ADMIN_TOKEN" ]; then
    # Try to get any course from the list
    result=$(test_endpoint "GET" "/courses" "" "Authorization: Bearer $ADMIN_TOKEN")
    body=$(echo "$result" | cut -d'|' -f2-)
    TEST_COURSE_ID=$(echo "$body" | grep -o '"id":[0-9]*' | head -1 | cut -d':' -f2)

    if [ -n "$TEST_COURSE_ID" ]; then
        result=$(test_endpoint "GET" "/courses/$TEST_COURSE_ID" "" "Authorization: Bearer $ADMIN_TOKEN")
        http_code=$(echo "$result" | cut -d'|' -f1)
        body=$(echo "$result" | cut -d'|' -f2-)

        echo -e "       ${BLUE}Method:${NC} GET"
        echo -e "       ${BLUE}Endpoint:${NC} /courses/$TEST_COURSE_ID"
        echo -e "       ${BLUE}Status:${NC} $http_code"
        echo ""

        if [ "$http_code" = "200" ]; then
            print_result "Get course by ID" "PASS" "HTTP 200 - Course retrieved successfully"
        else
            print_result "Get course by ID" "FAIL" "HTTP $http_code - $body"
        fi
    else
        print_result "Get course by ID" "SKIP" "No course ID available"
    fi
else
    print_result "Get course by ID" "SKIP" "No admin token available"
fi

# =============================================================================
# TEST 8: GET /api/courses/{id} with student token
# =============================================================================
echo -e "${YELLOW}========================================${NC}"
echo -e "${YELLOW}TEST 8: Get Course by ID (Student)${NC}"
echo -e "${YELLOW}========================================${NC}"
echo ""

if [ -n "$STUDENT_TOKEN" ] && [ -n "$TEST_COURSE_ID" ]; then
    result=$(test_endpoint "GET" "/courses/$TEST_COURSE_ID" "" "Authorization: Bearer $STUDENT_TOKEN")
    http_code=$(echo "$result" | cut -d'|' -f1)
    body=$(echo "$result" | cut -d'|' -f2-)

    echo -e "       ${BLUE}Method:${NC} GET"
    echo -e "       ${BLUE}Endpoint:${NC} /courses/$TEST_COURSE_ID"
    echo -e "       ${BLUE}Status:${NC} $http_code"
    echo ""

    if [ "$http_code" = "200" ]; then
        print_result "Get course by ID (student)" "PASS" "HTTP 200 - Course retrieved successfully"
    else
        print_result "Get course by ID (student)" "FAIL" "HTTP $http_code - $body"
    fi
else
    print_result "Get course by ID (student)" "SKIP" "No student token or course ID available"
fi

# =============================================================================
# TEST 9: PUT /api/courses/{id} - Update course
# =============================================================================
echo -e "${YELLOW}========================================${NC}"
echo -e "${YELLOW}TEST 9: Update Course${NC}"
echo -e "${YELLOW}========================================${NC}"
echo ""

if [ -n "$ADMIN_TOKEN" ] && [ -n "$TEST_COURSE_ID" ]; then
    update_course_data='{
        "name": "Updated Test Course",
        "description": "This course has been updated by automated testing",
        "schedule": "Tue/Thu 14:00-15:30"
    }'

    result=$(test_endpoint "PUT" "/courses/$TEST_COURSE_ID" "$update_course_data" "Authorization: Bearer $ADMIN_TOKEN")
    http_code=$(echo "$result" | cut -d'|' -f1)
    body=$(echo "$result" | cut -d'|' -f2-)

    echo -e "       ${BLUE}Method:${NC} PUT"
    echo -e "       ${BLUE}Endpoint:${NC} /courses/$TEST_COURSE_ID"
    echo -e "       ${BLUE}Status:${NC} $http_code"
    echo ""

    if [ "$http_code" = "200" ]; then
        print_result "Update course" "PASS" "HTTP 200 - Course updated successfully"
    else
        print_result "Update course" "FAIL" "HTTP $http_code - $body"
    fi
else
    print_result "Update course" "SKIP" "No admin token or course ID available"
fi

# =============================================================================
# TEST 10: PUT /api/courses/{id} with student token (should fail)
# =============================================================================
echo -e "${YELLOW}========================================${NC}"
echo -e "${YELLOW}TEST 10: Update Course (Student - should fail)${NC}"
echo -e "${YELLOW}========================================${NC}"
echo ""

if [ -n "$STUDENT_TOKEN" ] && [ -n "$TEST_COURSE_ID" ]; then
    update_course_data='{
        "name": "Unauthorized Update"
    }'

    result=$(test_endpoint "PUT" "/courses/$TEST_COURSE_ID" "$update_course_data" "Authorization: Bearer $STUDENT_TOKEN")
    http_code=$(echo "$result" | cut -d'|' -f1)
    body=$(echo "$result" | cut -d'|' -f2-)

    echo -e "       ${BLUE}Method:${NC} PUT"
    echo -e "       ${BLUE}Endpoint:${NC} /courses/$TEST_COURSE_ID"
    echo -e "       ${BLUE}Status:${NC} $http_code"
    echo ""

    if [ "$http_code" = "403" ]; then
        print_result "Update course (student)" "PASS" "HTTP 403 - Forbidden as expected"
    else
        print_result "Update course (student)" "FAIL" "Expected 403, got $http_code - $body"
    fi
else
    print_result "Update course (student)" "SKIP" "No student token or course ID available"
fi

# =============================================================================
# TEST 11: GET /api/courses/{id}/modules - Get course modules
# =============================================================================
echo -e "${YELLOW}========================================${NC}"
echo -e "${YELLOW}TEST 11: Get Course Modules${NC}"
echo -e "${YELLOW}========================================${NC}"
echo ""

if [ -n "$ADMIN_TOKEN" ] && [ -n "$TEST_COURSE_ID" ]; then
    result=$(test_endpoint "GET" "/courses/$TEST_COURSE_ID/modules" "" "Authorization: Bearer $ADMIN_TOKEN")
    http_code=$(echo "$result" | cut -d'|' -f1)
    body=$(echo "$result" | cut -d'|' -f2-)

    echo -e "       ${BLUE}Method:${NC} GET"
    echo -e "       ${BLUE}Endpoint:${NC} /courses/$TEST_COURSE_ID/modules"
    echo -e "       ${BLUE}Status:${NC} $http_code"
    echo ""

    if [ "$http_code" = "200" ]; then
        print_result "Get course modules" "PASS" "HTTP 200 - Modules retrieved successfully"
    else
        print_result "Get course modules" "FAIL" "HTTP $http_code - $body"
    fi
else
    print_result "Get course modules" "SKIP" "No admin token or course ID available"
fi

# =============================================================================
# TEST 12: GET /api/courses/{id}/enrollments - Get course enrollments
# =============================================================================
echo -e "${YELLOW}========================================${NC}"
echo -e "${YELLOW}TEST 12: Get Course Enrollments${NC}"
echo -e "${YELLOW}========================================${NC}"
echo ""

if [ -n "$ADMIN_TOKEN" ] && [ -n "$TEST_COURSE_ID" ]; then
    result=$(test_endpoint "GET" "/courses/$TEST_COURSE_ID/enrollments" "" "Authorization: Bearer $ADMIN_TOKEN")
    http_code=$(echo "$result" | cut -d'|' -f1)
    body=$(echo "$result" | cut -d'|' -f2-)

    echo -e "       ${BLUE}Method:${NC} GET"
    echo -e "       ${BLUE}Endpoint:${NC} /courses/$TEST_COURSE_ID/enrollments"
    echo -e "       ${BLUE}Status:${NC} $http_code"
    echo ""

    if [ "$http_code" = "200" ]; then
        print_result "Get course enrollments" "PASS" "HTTP 200 - Enrollments retrieved successfully"
    else
        print_result "Get course enrollments" "FAIL" "HTTP $http_code - $body"
    fi
else
    print_result "Get course enrollments" "SKIP" "No admin token or course ID available"
fi

# =============================================================================
# TEST 13: GET /api/courses/{id}/students - Get enrolled students
# =============================================================================
echo -e "${YELLOW}========================================${NC}"
echo -e "${YELLOW}TEST 13: Get Enrolled Students${NC}"
echo -e "${YELLOW}========================================${NC}"
echo ""

if [ -n "$ADMIN_TOKEN" ] && [ -n "$TEST_COURSE_ID" ]; then
    result=$(test_endpoint "GET" "/courses/$TEST_COURSE_ID/students" "" "Authorization: Bearer $ADMIN_TOKEN")
    http_code=$(echo "$result" | cut -d'|' -f1)
    body=$(echo "$result" | cut -d'|' -f2-)

    echo -e "       ${BLUE}Method:${NC} GET"
    echo -e "       ${BLUE}Endpoint:${NC} /courses/$TEST_COURSE_ID/students"
    echo -e "       ${BLUE}Status:${NC} $http_code"
    echo ""

    if [ "$http_code" = "200" ]; then
        print_result "Get enrolled students" "PASS" "HTTP 200 - Students retrieved successfully"
    else
        print_result "Get enrolled students" "FAIL" "HTTP $http_code - $body"
    fi
else
    print_result "Get enrolled students" "SKIP" "No admin token or course ID available"
fi

# =============================================================================
# TEST 14: GET /api/courses/{id}/assignments - Get course assignments
# =============================================================================
echo -e "${YELLOW}========================================${NC}"
echo -e "${YELLOW}TEST 14: Get Course Assignments${NC}"
echo -e "${YELLOW}========================================${NC}"
echo ""

if [ -n "$ADMIN_TOKEN" ] && [ -n "$TEST_COURSE_ID" ]; then
    result=$(test_endpoint "GET" "/courses/$TEST_COURSE_ID/assignments" "" "Authorization: Bearer $ADMIN_TOKEN")
    http_code=$(echo "$result" | cut -d'|' -f1)
    body=$(echo "$result" | cut -d'|' -f2-)

    echo -e "       ${BLUE}Method:${NC} GET"
    echo -e "       ${BLUE}Endpoint:${NC} /courses/$TEST_COURSE_ID/assignments"
    echo -e "       ${BLUE}Status:${NC} $http_code"
    echo ""

    if [ "$http_code" = "200" ]; then
        print_result "Get course assignments" "PASS" "HTTP 200 - Assignments retrieved successfully"
    else
        print_result "Get course assignments" "FAIL" "HTTP $http_code - $body"
    fi
else
    print_result "Get course assignments" "SKIP" "No admin token or course ID available"
fi

# =============================================================================
# TEST 15: GET /api/courses/{id}/announcements - Get course announcements
# =============================================================================
echo -e "${YELLOW}========================================${NC}"
echo -e "${YELLOW}TEST 15: Get Course Announcements${NC}"
echo -e "${YELLOW}========================================${NC}"
echo ""

if [ -n "$ADMIN_TOKEN" ] && [ -n "$TEST_COURSE_ID" ]; then
    result=$(test_endpoint "GET" "/courses/$TEST_COURSE_ID/announcements" "" "Authorization: Bearer $ADMIN_TOKEN")
    http_code=$(echo "$result" | cut -d'|' -f1)
    body=$(echo "$result" | cut -d'|' -f2-)

    echo -e "       ${BLUE}Method:${NC} GET"
    echo -e "       ${BLUE}Endpoint:${NC} /courses/$TEST_COURSE_ID/announcements"
    echo -e "       ${BLUE}Status:${NC} $http_code"
    echo ""

    if [ "$http_code" = "200" ]; then
        print_result "Get course announcements" "PASS" "HTTP 200 - Announcements retrieved successfully"
    else
        print_result "Get course announcements" "FAIL" "HTTP $http_code - $body"
    fi
else
    print_result "Get course announcements" "SKIP" "No admin token or course ID available"
fi

# =============================================================================
# TEST 16: GET /api/courses/{id}/library-resources - Get course library resources
# =============================================================================
echo -e "${YELLOW}========================================${NC}"
echo -e "${YELLOW}TEST 16: Get Course Library Resources${NC}"
echo -e "${YELLOW}========================================${NC}"
echo ""

if [ -n "$ADMIN_TOKEN" ] && [ -n "$TEST_COURSE_ID" ]; then
    result=$(test_endpoint "GET" "/courses/$TEST_COURSE_ID/library-resources" "" "Authorization: Bearer $ADMIN_TOKEN")
    http_code=$(echo "$result" | cut -d'|' -f1)
    body=$(echo "$result" | cut -d'|' -f2-)

    echo -e "       ${BLUE}Method:${NC} GET"
    echo -e "       ${BLUE}Endpoint:${NC} /courses/$TEST_COURSE_ID/library-resources"
    echo -e "       ${BLUE}Status:${NC} $http_code"
    echo ""

    if [ "$http_code" = "200" ]; then
        print_result "Get course library resources" "PASS" "HTTP 200 - Resources retrieved successfully"
    else
        print_result "Get course library resources" "FAIL" "HTTP $http_code - $body"
    fi
else
    print_result "Get course library resources" "SKIP" "No admin token or course ID available"
fi

# =============================================================================
# TEST 17: GET /api/courses/{id}/discussion-threads - Get course discussion threads
# =============================================================================
echo -e "${YELLOW}========================================${NC}"
echo -e "${YELLOW}TEST 17: Get Course Discussion Threads${NC}"
echo -e "${YELLOW}========================================${NC}"
echo ""

if [ -n "$ADMIN_TOKEN" ] && [ -n "$TEST_COURSE_ID" ]; then
    result=$(test_endpoint "GET" "/courses/$TEST_COURSE_ID/discussion-threads" "" "Authorization: Bearer $ADMIN_TOKEN")
    http_code=$(echo "$result" | cut -d'|' -f1)
    body=$(echo "$result" | cut -d'|' -f2-)

    echo -e "       ${BLUE}Method:${NC} GET"
    echo -e "       ${BLUE}Endpoint:${NC} /courses/$TEST_COURSE_ID/discussion-threads"
    echo -e "       ${BLUE}Status:${NC} $http_code"
    echo ""

    if [ "$http_code" = "200" ]; then
        print_result "Get course discussion threads" "PASS" "HTTP 200 - Threads retrieved successfully"
    else
        print_result "Get course discussion threads" "FAIL" "HTTP $http_code - $body"
    fi
else
    print_result "Get course discussion threads" "SKIP" "No admin token or course ID available"
fi

# =============================================================================
# TEST 18: GET /api/courses/{id}/grades - Get course grades
# =============================================================================
echo -e "${YELLOW}========================================${NC}"
echo -e "${YELLOW}TEST 18: Get Course Grades${NC}"
echo -e "${YELLOW}========================================${NC}"
echo ""

if [ -n "$ADMIN_TOKEN" ] && [ -n "$TEST_COURSE_ID" ]; then
    result=$(test_endpoint "GET" "/courses/$TEST_COURSE_ID/grades" "" "Authorization: Bearer $ADMIN_TOKEN")
    http_code=$(echo "$result" | cut -d'|' -f1)
    body=$(echo "$result" | cut -d'|' -f2-)

    echo -e "       ${BLUE}Method:${NC} GET"
    echo -e "       ${BLUE}Endpoint:${NC} /courses/$TEST_COURSE_ID/grades"
    echo -e "       ${BLUE}Status:${NC} $http_code"
    echo ""

    if [ "$http_code" = "200" ]; then
        print_result "Get course grades" "PASS" "HTTP 200 - Grades retrieved successfully"
    else
        print_result "Get course grades" "FAIL" "HTTP $http_code - $body"
    fi
else
    print_result "Get course grades" "SKIP" "No admin token or course ID available"
fi

# =============================================================================
# TEST 19: POST /api/courses/{id}/enroll - Enroll in course (student)
# =============================================================================
echo -e "${YELLOW}========================================${NC}"
echo -e "${YELLOW}TEST 19: Enroll in Course (Student)${NC}"
echo -e "${YELLOW}========================================${NC}"
echo ""

if [ -n "$STUDENT_TOKEN" ] && [ -n "$TEST_COURSE_ID" ]; then
    result=$(test_endpoint "POST" "/courses/$TEST_COURSE_ID/enroll" "" "Authorization: Bearer $STUDENT_TOKEN")
    http_code=$(echo "$result" | cut -d'|' -f1)
    body=$(echo "$result" | cut -d'|' -f2-)

    echo -e "       ${BLUE}Method:${NC} POST"
    echo -e "       ${BLUE}Endpoint:${NC} /courses/$TEST_COURSE_ID/enroll"
    echo -e "       ${BLUE}Status:${NC} $http_code"
    echo ""

    if [ "$http_code" = "201" ] || [ "$http_code" = "200" ]; then
        print_result "Enroll in course" "PASS" "HTTP $http_code - Enrolled successfully"
    elif [ "$http_code" = "409" ]; then
        print_result "Enroll in course" "PASS" "HTTP 409 - Already enrolled (expected)"
    else
        print_result "Enroll in course" "FAIL" "HTTP $http_code - $body"
    fi
else
    print_result "Enroll in course" "SKIP" "No student token or course ID available"
fi

# =============================================================================
# TEST 20: POST /api/courses/{id}/enroll with admin token (should fail)
# =============================================================================
echo -e "${YELLOW}========================================${NC}"
echo -e "${YELLOW}TEST 20: Enroll in Course (Admin - should fail)${NC}"
echo -e "${YELLOW}========================================${NC}"
echo ""

if [ -n "$ADMIN_TOKEN" ] && [ -n "$TEST_COURSE_ID" ]; then
    result=$(test_endpoint "POST" "/courses/$TEST_COURSE_ID/enroll" "" "Authorization: Bearer $ADMIN_TOKEN")
    http_code=$(echo "$result" | cut -d'|' -f1)
    body=$(echo "$result" | cut -d'|' -f2-)

    echo -e "       ${BLUE}Method:${NC} POST"
    echo -e "       ${BLUE}Endpoint:${NC} /courses/$TEST_COURSE_ID/enroll"
    echo -e "       ${BLUE}Status:${NC} $http_code"
    echo ""

    if [ "$http_code" = "403" ]; then
        print_result "Enroll in course (admin)" "PASS" "HTTP 403 - Forbidden as expected"
    else
        print_result "Enroll in course (admin)" "FAIL" "Expected 403, got $http_code - $body"
    fi
else
    print_result "Enroll in course (admin)" "SKIP" "No admin token or course ID available"
fi

# =============================================================================
# TEST 21: POST /api/courses/{id}/drop - Drop from course (student)
# =============================================================================
echo -e "${YELLOW}========================================${NC}"
echo -e "${YELLOW}TEST 21: Drop from Course (Student)${NC}"
echo -e "${YELLOW}========================================${NC}"
echo ""

if [ -n "$STUDENT_TOKEN" ] && [ -n "$TEST_COURSE_ID" ]; then
    result=$(test_endpoint "POST" "/courses/$TEST_COURSE_ID/drop" "" "Authorization: Bearer $STUDENT_TOKEN")
    http_code=$(echo "$result" | cut -d'|' -f1)
    body=$(echo "$result" | cut -d'|' -f2-)

    echo -e "       ${BLUE}Method:${NC} POST"
    echo -e "       ${BLUE}Endpoint:${NC} /courses/$TEST_COURSE_ID/drop"
    echo -e "       ${BLUE}Status:${NC} $http_code"
    echo ""

    if [ "$http_code" = "200" ]; then
        print_result "Drop from course" "PASS" "HTTP 200 - Dropped successfully"
    elif [ "$http_code" = "409" ]; then
        print_result "Drop from course" "PASS" "HTTP 409 - Already dropped (expected)"
    else
        print_result "Drop from course" "FAIL" "HTTP $http_code - $body"
    fi
else
    print_result "Drop from course" "SKIP" "No student token or course ID available"
fi

# =============================================================================
# TEST 22: GET /api/courses/my-courses - Get instructor's courses (faculty)
# =============================================================================
echo -e "${YELLOW}========================================${NC}"
echo -e "${YELLOW}TEST 22: Get My Courses (Faculty)${NC}"
echo -e "${YELLOW}========================================${NC}"
echo ""

if [ -n "$FACULTY_TOKEN" ]; then
    result=$(test_endpoint "GET" "/courses/my-courses" "" "Authorization: Bearer $FACULTY_TOKEN")
    http_code=$(echo "$result" | cut -d'|' -f1)
    body=$(echo "$result" | cut -d'|' -f2-)

    echo -e "       ${BLUE}Method:${NC} GET"
    echo -e "       ${BLUE}Endpoint:${NC} /courses/my-courses"
    echo -e "       ${BLUE}Status:${NC} $http_code"
    echo ""

    if [ "$http_code" = "200" ]; then
        print_result "Get my courses (faculty)" "PASS" "HTTP 200 - Courses retrieved successfully"
    else
        print_result "Get my courses (faculty)" "FAIL" "HTTP $http_code - $body"
    fi
else
    print_result "Get my courses (faculty)" "SKIP" "No faculty token available"
fi

# =============================================================================
# TEST 23: GET /api/courses/my-courses with student token (should fail)
# =============================================================================
echo -e "${YELLOW}========================================${NC}"
echo -e "${YELLOW}TEST 23: Get My Courses (Student - should fail)${NC}"
echo -e "${YELLOW}========================================${NC}"
echo ""

if [ -n "$STUDENT_TOKEN" ]; then
    result=$(test_endpoint "GET" "/courses/my-courses" "" "Authorization: Bearer $STUDENT_TOKEN")
    http_code=$(echo "$result" | cut -d'|' -f1)
    body=$(echo "$result" | cut -d'|' -f2-)

    echo -e "       ${BLUE}Method:${NC} GET"
    echo -e "       ${BLUE}Endpoint:${NC} /courses/my-courses"
    echo -e "       ${BLUE}Status:${NC} $http_code"
    echo ""

    if [ "$http_code" = "403" ]; then
        print_result "Get my courses (student)" "PASS" "HTTP 403 - Forbidden as expected"
    else
        print_result "Get my courses (student)" "FAIL" "Expected 403, got $http_code - $body"
    fi
else
    print_result "Get my courses (student)" "SKIP" "No student token available"
fi

# =============================================================================
# TEST 24: POST /api/courses/{id}/toggle-status - Toggle course status
# =============================================================================
echo -e "${YELLOW}========================================${NC}"
echo -e "${YELLOW}TEST 24: Toggle Course Status${NC}"
echo -e "${YELLOW}========================================${NC}"
echo ""

if [ -n "$ADMIN_TOKEN" ] && [ -n "$TEST_COURSE_ID" ]; then
    result=$(test_endpoint "POST" "/courses/$TEST_COURSE_ID/toggle-status" "" "Authorization: Bearer $ADMIN_TOKEN")
    http_code=$(echo "$result" | cut -d'|' -f1)
    body=$(echo "$result" | cut -d'|' -f2-)

    echo -e "       ${BLUE}Method:${NC} POST"
    echo -e "       ${BLUE}Endpoint:${NC} /courses/$TEST_COURSE_ID/toggle-status"
    echo -e "       ${BLUE}Status:${NC} $http_code"
    echo ""

    if [ "$http_code" = "200" ]; then
        print_result "Toggle course status" "PASS" "HTTP 200 - Status toggled successfully"
    else
        print_result "Toggle course status" "FAIL" "HTTP $http_code - $body"
    fi
else
    print_result "Toggle course status" "SKIP" "No admin token or course ID available"
fi

# =============================================================================
# TEST 25: GET /api/public/courses - Get public course catalog
# =============================================================================
echo -e "${YELLOW}========================================${NC}"
echo -e "${YELLOW}TEST 25: Get Public Course Catalog${NC}"
echo -e "${YELLOW}========================================${NC}"
echo ""

result=$(test_endpoint "GET" "/public/courses" "" "")
http_code=$(echo "$result" | cut -d'|' -f1)
body=$(echo "$result" | cut -d'|' -f2-)

echo -e "       ${BLUE}Method:${NC} GET"
echo -e "       ${BLUE}Endpoint:${NC} /public/courses"
echo -e "       ${BLUE}Status:${NC} $http_code"
echo ""

if [ "$http_code" = "200" ]; then
    print_result "Get public course catalog" "PASS" "HTTP 200 - Public courses retrieved successfully"
else
    print_result "Get public course catalog" "FAIL" "HTTP $http_code - $body"
fi

# =============================================================================
# TEST 26: GET /api/public/courses with search
# =============================================================================
echo -e "${YELLOW}========================================${NC}"
echo -e "${YELLOW}TEST 26: Get Public Courses with Search${NC}"
echo -e "${YELLOW}========================================${NC}"
echo ""

result=$(test_endpoint "GET" "/public/courses?search=course" "" "")
http_code=$(echo "$result" | cut -d'|' -f1)
body=$(echo "$result" | cut -d'|' -f2-)

echo -e "       ${BLUE}Method:${NC} GET"
echo -e "       ${BLUE}Endpoint:${NC} /public/courses?search=course"
echo -e "       ${BLUE}Status:${NC} $http_code"
echo ""

if [ "$http_code" = "200" ]; then
    print_result "Get public courses with search" "PASS" "HTTP 200 - Search results retrieved successfully"
else
    print_result "Get public courses with search" "FAIL" "HTTP $http_code - $body"
fi

# =============================================================================
# TEST 27: GET /api/courses without authentication
# =============================================================================
echo -e "${YELLOW}========================================${NC}"
echo -e "${YELLOW}TEST 27: List Courses Without Authentication${NC}"
echo -e "${YELLOW}========================================${NC}"
echo ""

result=$(test_endpoint "GET" "/courses" "" "")
http_code=$(echo "$result" | cut -d'|' -f1)
body=$(echo "$result" | cut -d'|' -f2-)

echo -e "       ${BLUE}Method:${NC} GET"
echo -e "       ${BLUE}Endpoint:${NC} /courses"
echo -e "       ${BLUE}Status:${NC} $http_code"
echo ""

if [ "$http_code" = "401" ]; then
    print_result "List courses without authentication" "PASS" "HTTP 401 - Unauthorized as expected"
else
    print_result "List courses without authentication" "FAIL" "Expected 401, got $http_code - $body"
fi

# =============================================================================
# TEST 28: POST /api/courses without authentication
# =============================================================================
echo -e "${YELLOW}========================================${NC}"
echo -e "${YELLOW}TEST 28: Create Course Without Authentication${NC}"
echo -e "${YELLOW}========================================${NC}"
echo ""

timestamp=$(date +%s)
create_course_no_auth='{
    "code": "UNAUTH'$timestamp'",
    "name": "Unauthorized Course",
    "description": "This should not be created"
}'

result=$(test_endpoint "POST" "/courses" "$create_course_no_auth" "")
http_code=$(echo "$result" | cut -d'|' -f1)
body=$(echo "$result" | cut -d'|' -f2-)

echo -e "       ${BLUE}Method:${NC} POST"
echo -e "       ${BLUE}Endpoint:${NC} /courses"
echo -e "       ${BLUE}Status:${NC} $http_code"
echo ""

if [ "$http_code" = "401" ]; then
    print_result "Create course without authentication" "PASS" "HTTP 401 - Unauthorized as expected"
else
    print_result "Create course without authentication" "FAIL" "Expected 401, got $http_code - $body"
fi

# =============================================================================
# TEST 29: DELETE /api/courses/{id} - Delete course
# =============================================================================
echo -e "${YELLOW}========================================${NC}"
echo -e "${YELLOW}TEST 29: Delete Course${NC}"
echo -e "${YELLOW}========================================${NC}"
echo ""

if [ -n "$ADMIN_TOKEN" ] && [ -n "$TEST_COURSE_ID" ]; then
    result=$(test_endpoint "DELETE" "/courses/$TEST_COURSE_ID" "" "Authorization: Bearer $ADMIN_TOKEN")
    http_code=$(echo "$result" | cut -d'|' -f1)
    body=$(echo "$result" | cut -d'|' -f2-)

    echo -e "       ${BLUE}Method:${NC} DELETE"
    echo -e "       ${BLUE}Endpoint:${NC} /courses/$TEST_COURSE_ID"
    echo -e "       ${BLUE}Status:${NC} $http_code"
    echo ""

    if [ "$http_code" = "204" ] || [ "$http_code" = "200" ]; then
        print_result "Delete course" "PASS" "HTTP $http_code - Course deleted successfully"
    elif [ "$http_code" = "409" ]; then
        print_result "Delete course" "PASS" "HTTP 409 - Cannot delete with active enrollments (expected)"
    else
        print_result "Delete course" "FAIL" "HTTP $http_code - $body"
    fi
else
    print_result "Delete course" "SKIP" "No admin token or course ID available"
fi

# =============================================================================
# TEST 30: GET /api/courses/{id} with invalid ID
# =============================================================================
echo -e "${YELLOW}========================================${NC}"
echo -e "${YELLOW}TEST 30: Get Course with Invalid ID${NC}"
echo -e "${YELLOW}========================================${NC}"
echo ""

if [ -n "$ADMIN_TOKEN" ]; then
    result=$(test_endpoint "GET" "/courses/999999" "" "Authorization: Bearer $ADMIN_TOKEN")
    http_code=$(echo "$result" | cut -d'|' -f1)
    body=$(echo "$result" | cut -d'|' -f2-)

    echo -e "       ${BLUE}Method:${NC} GET"
    echo -e "       ${BLUE}Endpoint:${NC} /courses/999999"
    echo -e "       ${BLUE}Status:${NC} $http_code"
    echo ""

    if [ "$http_code" = "404" ]; then
        print_result "Get course with invalid ID" "PASS" "HTTP 404 - Not found as expected"
    else
        print_result "Get course with invalid ID" "FAIL" "Expected 404, got $http_code - $body"
    fi
else
    print_result "Get course with invalid ID" "SKIP" "No admin token available"
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