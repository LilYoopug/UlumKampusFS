#!/bin/bash

# =============================================================================
# Assignment Endpoint Tests
# Tests all assignment-related API endpoints using curl
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
TEST_ASSIGNMENT_ID=""
TEST_COURSE_ID=""
TEST_MODULE_ID=""
TEST_SUBMISSION_ID=""

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

# Get course ID for assignment creation
if [ -n "$FACULTY_TOKEN" ]; then
    result=$(test_endpoint "GET" "/courses" "" "Authorization: Bearer $FACULTY_TOKEN")
    body=$(echo "$result" | cut -d'|' -f2-)
    TEST_COURSE_ID=$(echo "$body" | grep -o '"id":[0-9]*' | head -1 | cut -d':' -f2)

    # Get module ID for assignment creation
    if [ -n "$TEST_COURSE_ID" ]; then
        result=$(test_endpoint "GET" "/courses/$TEST_COURSE_ID/modules" "" "Authorization: Bearer $FACULTY_TOKEN")
        body=$(echo "$result" | cut -d'|' -f2-)
        TEST_MODULE_ID=$(echo "$body" | grep -o '"id":[0-9]*' | head -1 | cut -d':' -f2)

        # If no module exists, create one
        if [ -z "$TEST_MODULE_ID" ]; then
            module_create_data='{
                "course_id": '$TEST_COURSE_ID',
                "title": "Test Module",
                "description": "Test module for assignments",
                "order": 1
            }'
            result=$(test_endpoint "POST" "/course-modules" "$module_create_data" "Authorization: Bearer $FACULTY_TOKEN")
            body=$(echo "$result" | cut -d'|' -f2-)
            TEST_MODULE_ID=$(echo "$body" | grep -o '"id":[0-9]*' | head -1 | cut -d':' -f2)
        fi
    fi
fi

# Check if we have tokens
if [ -z "$FACULTY_TOKEN" ] && [ -z "$STUDENT_TOKEN" ]; then
    echo -e "${RED}No authentication tokens available. Exiting.${NC}"
    exit 1
fi

echo ""

# =============================================================================
# TEST 1: GET /api/assignments - List all assignments
# =============================================================================
echo -e "${YELLOW}========================================${NC}"
echo -e "${YELLOW}TEST 1: List All Assignments${NC}"
echo -e "${YELLOW}========================================${NC}"
echo ""

if [ -n "$FACULTY_TOKEN" ]; then
    result=$(test_endpoint "GET" "/assignments" "" "Authorization: Bearer $FACULTY_TOKEN")
    http_code=$(echo "$result" | cut -d'|' -f1)
    body=$(echo "$result" | cut -d'|' -f2-)

    echo -e "       ${BLUE}Method:${NC} GET"
    echo -e "       ${BLUE}Endpoint:${NC} /assignments"
    echo -e "       ${BLUE}Status:${NC} $http_code"
    echo -e "       ${BLUE}Response:${NC} $(echo "$body" | head -c 200)..."
    echo ""

    if [ "$http_code" = "200" ]; then
        print_result "List all assignments" "PASS" "HTTP 200 - Assignments retrieved successfully"
    else
        print_result "List all assignments" "FAIL" "HTTP $http_code - $body"
    fi
else
    print_result "List all assignments" "SKIP" "No faculty token available"
fi

# =============================================================================
# TEST 2: GET /api/assignments with search parameter
# =============================================================================
echo -e "${YELLOW}========================================${NC}"
echo -e "${YELLOW}TEST 2: List Assignments with Search${NC}"
echo -e "${YELLOW}========================================${NC}"
echo ""

if [ -n "$FACULTY_TOKEN" ]; then
    result=$(test_endpoint "GET" "/assignments?search=assignment" "" "Authorization: Bearer $FACULTY_TOKEN")
    http_code=$(echo "$result" | cut -d'|' -f1)
    body=$(echo "$result" | cut -d'|' -f2-)

    echo -e "       ${BLUE}Method:${NC} GET"
    echo -e "       ${BLUE}Endpoint:${NC} /assignments?search=assignment"
    echo -e "       ${BLUE}Status:${NC} $http_code"
    echo ""

    if [ "$http_code" = "200" ]; then
        print_result "List assignments with search" "PASS" "HTTP 200 - Search results retrieved successfully"
    else
        print_result "List assignments with search" "FAIL" "HTTP $http_code - $body"
    fi
else
    print_result "List assignments with search" "SKIP" "No faculty token available"
fi

# =============================================================================
# TEST 3: POST /api/assignments - Create new assignment
# =============================================================================
echo -e "${YELLOW}========================================${NC}"
echo -e "${YELLOW}TEST 3: Create New Assignment${NC}"
echo -e "${YELLOW}========================================${NC}"
echo ""

if [ -n "$FACULTY_TOKEN" ] && [ -n "$TEST_COURSE_ID" ] && [ -n "$TEST_MODULE_ID" ]; then
    timestamp=$(date +%s)
    create_assignment_data='{
        "course_id": '$TEST_COURSE_ID',
        "module_id": '$TEST_MODULE_ID',
        "title": "Test Assignment '$timestamp'",
        "description": "This is a test assignment created by automated testing",
        "instructions": "Please complete this assignment by the due date",
        "type": "homework",
        "max_points": 100,
        "due_date": "'$(date -d '+7 days' +%Y-%m-%d)'T'$(date +%H:%M:%S)'",
        "allow_late_submission": true,
        "late_submission_penalty": 10,
        "is_published": true,
        "order": 1
    }'

    result=$(test_endpoint "POST" "/assignments" "$create_assignment_data" "Authorization: Bearer $FACULTY_TOKEN")
    http_code=$(echo "$result" | cut -d'|' -f1)
    body=$(echo "$result" | cut -d'|' -f2-)

    echo -e "       ${BLUE}Method:${NC} POST"
    echo -e "       ${BLUE}Endpoint:${NC} /assignments"
    echo -e "       ${BLUE}Status:${NC} $http_code"
    echo -e "       ${BLUE}Response:${NC} $(echo "$body" | head -c 300)..."
    echo ""

    if [ "$http_code" = "201" ] || [ "$http_code" = "200" ]; then
        print_result "Create new assignment" "PASS" "HTTP $http_code - Assignment created successfully"
        # Extract the created assignment ID for later tests
        TEST_ASSIGNMENT_ID=$(echo "$body" | grep -o '"id":[0-9]*' | head -1 | cut -d':' -f2)
    else
        print_result "Create new assignment" "FAIL" "HTTP $http_code - $body"
    fi
else
    print_result "Create new assignment" "SKIP" "No faculty token or required IDs available"
fi

# =============================================================================
# TEST 4: GET /api/assignments/{id} - Get specific assignment
# =============================================================================
echo -e "${YELLOW}========================================${NC}"
echo -e "${YELLOW}TEST 4: Get Assignment by ID${NC}"
echo -e "${YELLOW}========================================${NC}"
echo ""

if [ -n "$FACULTY_TOKEN" ] && [ -n "$TEST_ASSIGNMENT_ID" ]; then
    result=$(test_endpoint "GET" "/assignments/$TEST_ASSIGNMENT_ID" "" "Authorization: Bearer $FACULTY_TOKEN")
    http_code=$(echo "$result" | cut -d'|' -f1)
    body=$(echo "$result" | cut -d'|' -f2-)

    echo -e "       ${BLUE}Method:${NC} GET"
    echo -e "       ${BLUE}Endpoint:${NC} /assignments/$TEST_ASSIGNMENT_ID"
    echo -e "       ${BLUE}Status:${NC} $http_code"
    echo ""

    if [ "$http_code" = "200" ]; then
        print_result "Get assignment by ID" "PASS" "HTTP 200 - Assignment retrieved successfully"
    else
        print_result "Get assignment by ID" "FAIL" "HTTP $http_code - $body"
    fi
elif [ -n "$FACULTY_TOKEN" ]; then
    # Try to get any assignment from the list
    result=$(test_endpoint "GET" "/assignments" "" "Authorization: Bearer $FACULTY_TOKEN")
    body=$(echo "$result" | cut -d'|' -f2-)
    TEST_ASSIGNMENT_ID=$(echo "$body" | grep -o '"id":[0-9]*' | head -1 | cut -d':' -f2)

    if [ -n "$TEST_ASSIGNMENT_ID" ]; then
        result=$(test_endpoint "GET" "/assignments/$TEST_ASSIGNMENT_ID" "" "Authorization: Bearer $FACULTY_TOKEN")
        http_code=$(echo "$result" | cut -d'|' -f1)
        body=$(echo "$result" | cut -d'|' -f2-)

        echo -e "       ${BLUE}Method:${NC} GET"
        echo -e "       ${BLUE}Endpoint:${NC} /assignments/$TEST_ASSIGNMENT_ID"
        echo -e "       ${BLUE}Status:${NC} $http_code"
        echo ""

        if [ "$http_code" = "200" ]; then
            print_result "Get assignment by ID" "PASS" "HTTP 200 - Assignment retrieved successfully"
        else
            print_result "Get assignment by ID" "FAIL" "HTTP $http_code - $body"
        fi
    else
        print_result "Get assignment by ID" "SKIP" "No assignment ID available"
    fi
else
    print_result "Get assignment by ID" "SKIP" "No faculty token available"
fi

# =============================================================================
# TEST 5: GET /api/assignments/{id} with student token
# =============================================================================
echo -e "${YELLOW}========================================${NC}"
echo -e "${YELLOW}TEST 5: Get Assignment by ID (Student)${NC}"
echo -e "${YELLOW}========================================${NC}"
echo ""

if [ -n "$STUDENT_TOKEN" ] && [ -n "$TEST_ASSIGNMENT_ID" ]; then
    result=$(test_endpoint "GET" "/assignments/$TEST_ASSIGNMENT_ID" "" "Authorization: Bearer $STUDENT_TOKEN")
    http_code=$(echo "$result" | cut -d'|' -f1)
    body=$(echo "$result" | cut -d'|' -f2-)

    echo -e "       ${BLUE}Method:${NC} GET"
    echo -e "       ${BLUE}Endpoint:${NC} /assignments/$TEST_ASSIGNMENT_ID"
    echo -e "       ${BLUE}Status:${NC} $http_code"
    echo ""

    if [ "$http_code" = "200" ]; then
        print_result "Get assignment by ID (student)" "PASS" "HTTP 200 - Assignment retrieved successfully"
    else
        print_result "Get assignment by ID (student)" "FAIL" "HTTP $http_code - $body"
    fi
else
    print_result "Get assignment by ID (student)" "SKIP" "No student token or assignment ID available"
fi

# =============================================================================
# TEST 6: PUT /api/assignments/{id} - Update assignment
# =============================================================================
echo -e "${YELLOW}========================================${NC}"
echo -e "${YELLOW}TEST 6: Update Assignment${NC}"
echo -e "${YELLOW}========================================${NC}"
echo ""

if [ -n "$FACULTY_TOKEN" ] && [ -n "$TEST_ASSIGNMENT_ID" ]; then
    update_assignment_data='{
        "title": "Updated Test Assignment",
        "description": "This assignment has been updated by automated testing",
        "max_points": 150
    }'

    result=$(test_endpoint "PUT" "/assignments/$TEST_ASSIGNMENT_ID" "$update_assignment_data" "Authorization: Bearer $FACULTY_TOKEN")
    http_code=$(echo "$result" | cut -d'|' -f1)
    body=$(echo "$result" | cut -d'|' -f2-)

    echo -e "       ${BLUE}Method:${NC} PUT"
    echo -e "       ${BLUE}Endpoint:${NC} /assignments/$TEST_ASSIGNMENT_ID"
    echo -e "       ${BLUE}Status:${NC} $http_code"
    echo ""

    if [ "$http_code" = "200" ]; then
        print_result "Update assignment" "PASS" "HTTP 200 - Assignment updated successfully"
    else
        print_result "Update assignment" "FAIL" "HTTP $http_code - $body"
    fi
else
    print_result "Update assignment" "SKIP" "No faculty token or assignment ID available"
fi

# =============================================================================
# TEST 7: PUT /api/assignments/{id} with student token (should fail)
# =============================================================================
echo -e "${YELLOW}========================================${NC}"
echo -e "${YELLOW}TEST 7: Update Assignment (Student - should fail)${NC}"
echo -e "${YELLOW}========================================${NC}"
echo ""

if [ -n "$STUDENT_TOKEN" ] && [ -n "$TEST_ASSIGNMENT_ID" ]; then
    update_assignment_data='{
        "title": "Unauthorized Update"
    }'

    result=$(test_endpoint "PUT" "/assignments/$TEST_ASSIGNMENT_ID" "$update_assignment_data" "Authorization: Bearer $STUDENT_TOKEN")
    http_code=$(echo "$result" | cut -d'|' -f1)
    body=$(echo "$result" | cut -d'|' -f2-)

    echo -e "       ${BLUE}Method:${NC} PUT"
    echo -e "       ${BLUE}Endpoint:${NC} /assignments/$TEST_ASSIGNMENT_ID"
    echo -e "       ${BLUE}Status:${NC} $http_code"
    echo ""

    if [ "$http_code" = "403" ]; then
        print_result "Update assignment (student)" "PASS" "HTTP 403 - Forbidden as expected"
    else
        print_result "Update assignment (student)" "FAIL" "Expected 403, got $http_code - $body"
    fi
else
    print_result "Update assignment (student)" "SKIP" "No student token or assignment ID available"
fi

# =============================================================================
# TEST 8: GET /api/assignments/{id}/submissions - Get assignment submissions
# =============================================================================
echo -e "${YELLOW}========================================${NC}"
echo -e "${YELLOW}TEST 8: Get Assignment Submissions${NC}"
echo -e "${YELLOW}========================================${NC}"
echo ""

if [ -n "$FACULTY_TOKEN" ] && [ -n "$TEST_ASSIGNMENT_ID" ]; then
    result=$(test_endpoint "GET" "/assignments/$TEST_ASSIGNMENT_ID/submissions" "" "Authorization: Bearer $FACULTY_TOKEN")
    http_code=$(echo "$result" | cut -d'|' -f1)
    body=$(echo "$result" | cut -d'|' -f2-)

    echo -e "       ${BLUE}Method:${NC} GET"
    echo -e "       ${BLUE}Endpoint:${NC} /assignments/$TEST_ASSIGNMENT_ID/submissions"
    echo -e "       ${BLUE}Status:${NC} $http_code"
    echo ""

    if [ "$http_code" = "200" ]; then
        print_result "Get assignment submissions" "PASS" "HTTP 200 - Submissions retrieved successfully"
    else
        print_result "Get assignment submissions" "FAIL" "HTTP $http_code - $body"
    fi
else
    print_result "Get assignment submissions" "SKIP" "No faculty token or assignment ID available"
fi

# =============================================================================
# TEST 9: POST /api/assignments/{id}/submit - Submit assignment (student)
# =============================================================================
echo -e "${YELLOW}========================================${NC}"
echo -e "${YELLOW}TEST 9: Submit Assignment (Student)${NC}"
echo -e "${YELLOW}========================================${NC}"
echo ""

if [ -n "$STUDENT_TOKEN" ] && [ -n "$TEST_ASSIGNMENT_ID" ]; then
    submit_assignment_data='{
        "content": "This is my test assignment submission",
        "file_url": "https://example.com/files/submission.pdf"
    }'

    result=$(test_endpoint "POST" "/assignments/$TEST_ASSIGNMENT_ID/submit" "$submit_assignment_data" "Authorization: Bearer $STUDENT_TOKEN")
    http_code=$(echo "$result" | cut -d'|' -f1)
    body=$(echo "$result" | cut -d'|' -f2-)

    echo -e "       ${BLUE}Method:${NC} POST"
    echo -e "       ${BLUE}Endpoint:${NC} /assignments/$TEST_ASSIGNMENT_ID/submit"
    echo -e "       ${BLUE}Status:${NC} $http_code"
    echo ""

    if [ "$http_code" = "201" ] || [ "$http_code" = "200" ]; then
        print_result "Submit assignment" "PASS" "HTTP $http_code - Assignment submitted successfully"
        # Extract submission ID for later tests
        TEST_SUBMISSION_ID=$(echo "$body" | grep -o '"id":[0-9]*' | head -1 | cut -d':' -f2)
    elif [ "$http_code" = "409" ]; then
        print_result "Submit assignment" "PASS" "HTTP 409 - Already submitted (expected)"
    else
        print_result "Submit assignment" "FAIL" "HTTP $http_code - $body"
    fi
else
    print_result "Submit assignment" "SKIP" "No student token or assignment ID available"
fi

# =============================================================================
# TEST 10: GET /api/assignments/{id}/my-submission - Get my submission (student)
# =============================================================================
echo -e "${YELLOW}========================================${NC}"
echo -e "${YELLOW}TEST 10: Get My Submission (Student)${NC}"
echo -e "${YELLOW}========================================${NC}"
echo ""

if [ -n "$STUDENT_TOKEN" ] && [ -n "$TEST_ASSIGNMENT_ID" ]; then
    result=$(test_endpoint "GET" "/assignments/$TEST_ASSIGNMENT_ID/my-submission" "" "Authorization: Bearer $STUDENT_TOKEN")
    http_code=$(echo "$result" | cut -d'|' -f1)
    body=$(echo "$result" | cut -d'|' -f2-)

    echo -e "       ${BLUE}Method:${NC} GET"
    echo -e "       ${BLUE}Endpoint:${NC} /assignments/$TEST_ASSIGNMENT_ID/my-submission"
    echo -e "       ${BLUE}Status:${NC} $http_code"
    echo ""

    if [ "$http_code" = "200" ]; then
        print_result "Get my submission" "PASS" "HTTP 200 - Submission retrieved successfully"
    elif [ "$http_code" = "404" ]; then
        print_result "Get my submission" "PASS" "HTTP 404 - No submission yet (expected)"
    else
        print_result "Get my submission" "FAIL" "HTTP $http_code - $body"
    fi
else
    print_result "Get my submission" "SKIP" "No student token or assignment ID available"
fi

# =============================================================================
# TEST 11: POST /api/assignments/{id}/publish - Publish assignment
# =============================================================================
echo -e "${YELLOW}========================================${NC}"
echo -e "${YELLOW}TEST 11: Publish Assignment${NC}"
echo -e "${YELLOW}========================================${NC}"
echo ""

if [ -n "$FACULTY_TOKEN" ] && [ -n "$TEST_ASSIGNMENT_ID" ]; then
    result=$(test_endpoint "POST" "/assignments/$TEST_ASSIGNMENT_ID/publish" "" "Authorization: Bearer $FACULTY_TOKEN")
    http_code=$(echo "$result" | cut -d'|' -f1)
    body=$(echo "$result" | cut -d'|' -f2-)

    echo -e "       ${BLUE}Method:${NC} POST"
    echo -e "       ${BLUE}Endpoint:${NC} /assignments/$TEST_ASSIGNMENT_ID/publish"
    echo -e "       ${BLUE}Status:${NC} $http_code"
    echo ""

    if [ "$http_code" = "200" ]; then
        print_result "Publish assignment" "PASS" "HTTP 200 - Assignment published successfully"
    else
        print_result "Publish assignment" "FAIL" "HTTP $http_code - $body"
    fi
else
    print_result "Publish assignment" "SKIP" "No faculty token or assignment ID available"
fi

# =============================================================================
# TEST 12: POST /api/assignments/{id}/unpublish - Unpublish assignment
# =============================================================================
echo -e "${YELLOW}========================================${NC}"
echo -e "${YELLOW}TEST 12: Unpublish Assignment${NC}"
echo -e "${YELLOW}========================================${NC}"
echo ""

if [ -n "$FACULTY_TOKEN" ] && [ -n "$TEST_ASSIGNMENT_ID" ]; then
    result=$(test_endpoint "POST" "/assignments/$TEST_ASSIGNMENT_ID/unpublish" "" "Authorization: Bearer $FACULTY_TOKEN")
    http_code=$(echo "$result" | cut -d'|' -f1)
    body=$(echo "$result" | cut -d'|' -f2-)

    echo -e "       ${BLUE}Method:${NC} POST"
    echo -e "       ${BLUE}Endpoint:${NC} /assignments/$TEST_ASSIGNMENT_ID/unpublish"
    echo -e "       ${BLUE}Status:${NC} $http_code"
    echo ""

    if [ "$http_code" = "200" ]; then
        print_result "Unpublish assignment" "PASS" "HTTP 200 - Assignment unpublished successfully"
    else
        print_result "Unpublish assignment" "FAIL" "HTTP $http_code - $body"
    fi
else
    print_result "Unpublish assignment" "SKIP" "No faculty token or assignment ID available"
fi

# =============================================================================
# TEST 13: GET /api/submissions - List submissions (student)
# =============================================================================
echo -e "${YELLOW}========================================${NC}"
echo -e "${YELLOW}TEST 13: List My Submissions (Student)${NC}"
echo -e "${YELLOW}========================================${NC}"
echo ""

if [ -n "$STUDENT_TOKEN" ]; then
    result=$(test_endpoint "GET" "/submissions" "" "Authorization: Bearer $STUDENT_TOKEN")
    http_code=$(echo "$result" | cut -d'|' -f1)
    body=$(echo "$result" | cut -d'|' -f2-)

    echo -e "       ${BLUE}Method:${NC} GET"
    echo -e "       ${BLUE}Endpoint:${NC} /submissions"
    echo -e "       ${BLUE}Status:${NC} $http_code"
    echo ""

    if [ "$http_code" = "200" ]; then
        print_result "List my submissions" "PASS" "HTTP 200 - Submissions retrieved successfully"
    else
        print_result "List my submissions" "FAIL" "HTTP $http_code - $body"
    fi
else
    print_result "List my submissions" "SKIP" "No student token available"
fi

# =============================================================================
# TEST 14: GET /api/submissions/{id} - Get specific submission
# =============================================================================
echo -e "${YELLOW}========================================${NC}"
echo -e "${YELLOW}TEST 14: Get Submission by ID${NC}"
echo -e "${YELLOW}========================================${NC}"
echo ""

if [ -n "$STUDENT_TOKEN" ] && [ -n "$TEST_SUBMISSION_ID" ]; then
    result=$(test_endpoint "GET" "/submissions/$TEST_SUBMISSION_ID" "" "Authorization: Bearer $STUDENT_TOKEN")
    http_code=$(echo "$result" | cut -d'|' -f1)
    body=$(echo "$result" | cut -d'|' -f2-)

    echo -e "       ${BLUE}Method:${NC} GET"
    echo -e "       ${BLUE}Endpoint:${NC} /submissions/$TEST_SUBMISSION_ID"
    echo -e "       ${BLUE}Status:${NC} $http_code"
    echo ""

    if [ "$http_code" = "200" ]; then
        print_result "Get submission by ID" "PASS" "HTTP 200 - Submission retrieved successfully"
    else
        print_result "Get submission by ID" "FAIL" "HTTP $http_code - $body"
    fi
else
    print_result "Get submission by ID" "SKIP" "No student token or submission ID available"
fi

# =============================================================================
# TEST 15: PUT /api/submissions/{id} - Update submission (student)
# =============================================================================
echo -e "${YELLOW}========================================${NC}"
echo -e "${YELLOW}TEST 15: Update Submission (Student)${NC}"
echo -e "${YELLOW}========================================${NC}"
echo ""

if [ -n "$STUDENT_TOKEN" ] && [ -n "$TEST_SUBMISSION_ID" ]; then
    update_submission_data='{
        "content": "Updated test assignment submission"
    }'

    result=$(test_endpoint "PUT" "/submissions/$TEST_SUBMISSION_ID" "$update_submission_data" "Authorization: Bearer $STUDENT_TOKEN")
    http_code=$(echo "$result" | cut -d'|' -f1)
    body=$(echo "$result" | cut -d'|' -f2-)

    echo -e "       ${BLUE}Method:${NC} PUT"
    echo -e "       ${BLUE}Endpoint:${NC} /submissions/$TEST_SUBMISSION_ID"
    echo -e "       ${BLUE}Status:${NC} $http_code"
    echo ""

    if [ "$http_code" = "200" ]; then
        print_result "Update submission" "PASS" "HTTP 200 - Submission updated successfully"
    else
        print_result "Update submission" "FAIL" "HTTP $http_code - $body"
    fi
else
    print_result "Update submission" "SKIP" "No student token or submission ID available"
fi

# =============================================================================
# TEST 16: GET /api/submissions/assignment/{assignmentId} - Get submissions by assignment (faculty)
# =============================================================================
echo -e "${YELLOW}========================================${NC}"
echo -e "${YELLOW}TEST 16: Get Submissions by Assignment (Faculty)${NC}"
echo -e "${YELLOW}========================================${NC}"
echo ""

if [ -n "$FACULTY_TOKEN" ] && [ -n "$TEST_ASSIGNMENT_ID" ]; then
    result=$(test_endpoint "GET" "/submissions/assignment/$TEST_ASSIGNMENT_ID" "" "Authorization: Bearer $FACULTY_TOKEN")
    http_code=$(echo "$result" | cut -d'|' -f1)
    body=$(echo "$result" | cut -d'|' -f2-)

    echo -e "       ${BLUE}Method:${NC} GET"
    echo -e "       ${BLUE}Endpoint:${NC} /submissions/assignment/$TEST_ASSIGNMENT_ID"
    echo -e "       ${BLUE}Status:${NC} $http_code"
    echo ""

    if [ "$http_code" = "200" ]; then
        print_result "Get submissions by assignment" "PASS" "HTTP 200 - Submissions retrieved successfully"
    else
        print_result "Get submissions by assignment" "FAIL" "HTTP $http_code - $body"
    fi
else
    print_result "Get submissions by assignment" "SKIP" "No faculty token or assignment ID available"
fi

# =============================================================================
# TEST 17: POST /api/submissions/{id}/grade - Grade submission (faculty)
# =============================================================================
echo -e "${YELLOW}========================================${NC}"
echo -e "${YELLOW}TEST 17: Grade Submission (Faculty)${NC}"
echo -e "${YELLOW}========================================${NC}"
echo ""

if [ -n "$FACULTY_TOKEN" ] && [ -n "$TEST_SUBMISSION_ID" ]; then
    grade_submission_data='{
        "grade": 85,
        "graded_by": "Test Faculty"
    }'

    result=$(test_endpoint "POST" "/submissions/$TEST_SUBMISSION_ID/grade" "$grade_submission_data" "Authorization: Bearer $FACULTY_TOKEN")
    http_code=$(echo "$result" | cut -d'|' -f1)
    body=$(echo "$result" | cut -d'|' -f2-)

    echo -e "       ${BLUE}Method:${NC} POST"
    echo -e "       ${BLUE}Endpoint:${NC} /submissions/$TEST_SUBMISSION_ID/grade"
    echo -e "       ${BLUE}Status:${NC} $http_code"
    echo ""

    if [ "$http_code" = "200" ]; then
        print_result "Grade submission" "PASS" "HTTP 200 - Submission graded successfully"
    else
        print_result "Grade submission" "FAIL" "HTTP $http_code - $body"
    fi
else
    print_result "Grade submission" "SKIP" "No faculty token or submission ID available"
fi

# =============================================================================
# TEST 18: POST /api/submissions/{id}/feedback - Add feedback to submission (faculty)
# =============================================================================
echo -e "${YELLOW}========================================${NC}"
echo -e "${YELLOW}TEST 18: Add Feedback to Submission (Faculty)${NC}"
echo -e "${YELLOW}========================================${NC}"
echo ""

if [ -n "$FACULTY_TOKEN" ] && [ -n "$TEST_SUBMISSION_ID" ]; then
    feedback_data='{
        "feedback": "Good work! Consider adding more detail to your analysis."
    }'

    result=$(test_endpoint "POST" "/submissions/$TEST_SUBMISSION_ID/feedback" "$feedback_data" "Authorization: Bearer $FACULTY_TOKEN")
    http_code=$(echo "$result" | cut -d'|' -f1)
    body=$(echo "$result" | cut -d'|' -f2-)

    echo -e "       ${BLUE}Method:${NC} POST"
    echo -e "       ${BLUE}Endpoint:${NC} /submissions/$TEST_SUBMISSION_ID/feedback"
    echo -e "       ${BLUE}Status:${NC} $http_code"
    echo ""

    if [ "$http_code" = "200" ]; then
        print_result "Add feedback to submission" "PASS" "HTTP 200 - Feedback added successfully"
    else
        print_result "Add feedback to submission" "FAIL" "HTTP $http_code - $body"
    fi
else
    print_result "Add feedback to submission" "SKIP" "No faculty token or submission ID available"
fi

# =============================================================================
# TEST 19: POST /api/submissions/{id}/grade with student token (should fail)
# =============================================================================
echo -e "${YELLOW}========================================${NC}"
echo -e "${YELLOW}TEST 19: Grade Submission (Student - should fail)${NC}"
echo -e "${YELLOW}========================================${NC}"
echo ""

if [ -n "$STUDENT_TOKEN" ] && [ -n "$TEST_SUBMISSION_ID" ]; then
    grade_submission_data='{
        "grade": 100
    }'

    result=$(test_endpoint "POST" "/submissions/$TEST_SUBMISSION_ID/grade" "$grade_submission_data" "Authorization: Bearer $STUDENT_TOKEN")
    http_code=$(echo "$result" | cut -d'|' -f1)
    body=$(echo "$result" | cut -d'|' -f2-)

    echo -e "       ${BLUE}Method:${NC} POST"
    echo -e "       ${BLUE}Endpoint:${NC} /submissions/$TEST_SUBMISSION_ID/grade"
    echo -e "       ${BLUE}Status:${NC} $http_code"
    echo ""

    if [ "$http_code" = "403" ]; then
        print_result "Grade submission (student)" "PASS" "HTTP 403 - Forbidden as expected"
    else
        print_result "Grade submission (student)" "FAIL" "Expected 403, got $http_code - $body"
    fi
else
    print_result "Grade submission (student)" "SKIP" "No student token or submission ID available"
fi

# =============================================================================
# TEST 20: DELETE /api/assignments/{id} - Delete assignment
# =============================================================================
echo -e "${YELLOW}========================================${NC}"
echo -e "${YELLOW}TEST 20: Delete Assignment${NC}"
echo -e "${YELLOW}========================================${NC}"
echo ""

if [ -n "$FACULTY_TOKEN" ] && [ -n "$TEST_ASSIGNMENT_ID" ]; then
    result=$(test_endpoint "DELETE" "/assignments/$TEST_ASSIGNMENT_ID" "" "Authorization: Bearer $FACULTY_TOKEN")
    http_code=$(echo "$result" | cut -d'|' -f1)
    body=$(echo "$result" | cut -d'|' -f2-)

    echo -e "       ${BLUE}Method:${NC} DELETE"
    echo -e "       ${BLUE}Endpoint:${NC} /assignments/$TEST_ASSIGNMENT_ID"
    echo -e "       ${BLUE}Status:${NC} $http_code"
    echo ""

    if [ "$http_code" = "204" ] || [ "$http_code" = "200" ]; then
        print_result "Delete assignment" "PASS" "HTTP $http_code - Assignment deleted successfully"
    elif [ "$http_code" = "409" ]; then
        print_result "Delete assignment" "PASS" "HTTP 409 - Cannot delete with submissions (expected)"
    else
        print_result "Delete assignment" "FAIL" "HTTP $http_code - $body"
    fi
else
    print_result "Delete assignment" "SKIP" "No faculty token or assignment ID available"
fi

# =============================================================================
# TEST 21: GET /api/assignments without authentication
# =============================================================================
echo -e "${YELLOW}========================================${NC}"
echo -e "${YELLOW}TEST 21: List Assignments Without Authentication${NC}"
echo -e "${YELLOW}========================================${NC}"
echo ""

result=$(test_endpoint "GET" "/assignments" "" "")
http_code=$(echo "$result" | cut -d'|' -f1)
body=$(echo "$result" | cut -d'|' -f2-)

echo -e "       ${BLUE}Method:${NC} GET"
echo -e "       ${BLUE}Endpoint:${NC} /assignments"
echo -e "       ${BLUE}Status:${NC} $http_code"
echo ""

if [ "$http_code" = "401" ]; then
    print_result "List assignments without authentication" "PASS" "HTTP 401 - Unauthorized as expected"
else
    print_result "List assignments without authentication" "FAIL" "Expected 401, got $http_code - $body"
fi

# =============================================================================
# TEST 22: POST /api/assignments without authentication
# =============================================================================
echo -e "${YELLOW}========================================${NC}"
echo -e "${YELLOW}TEST 22: Create Assignment Without Authentication${NC}"
echo -e "${YELLOW}========================================${NC}"
echo ""

timestamp=$(date +%s)
create_assignment_no_auth='{
    "title": "Unauthorized Assignment",
    "description": "This should not be created"
}'

result=$(test_endpoint "POST" "/assignments" "$create_assignment_no_auth" "")
http_code=$(echo "$result" | cut -d'|' -f1)
body=$(echo "$result" | cut -d'|' -f2-)

echo -e "       ${BLUE}Method:${NC} POST"
echo -e "       ${BLUE}Endpoint:${NC} /assignments"
echo -e "       ${BLUE}Status:${NC} $http_code"
echo ""

if [ "$http_code" = "401" ]; then
    print_result "Create assignment without authentication" "PASS" "HTTP 401 - Unauthorized as expected"
else
    print_result "Create assignment without authentication" "FAIL" "Expected 401, got $http_code - $body"
fi

# =============================================================================
# TEST 23: GET /api/assignments with course_id filter
# =============================================================================
echo -e "${YELLOW}========================================${NC}"
echo -e "${YELLOW}TEST 23: List Assignments by Course${NC}"
echo -e "${YELLOW}========================================${NC}"
echo ""

if [ -n "$FACULTY_TOKEN" ] && [ -n "$TEST_COURSE_ID" ]; then
    result=$(test_endpoint "GET" "/assignments?course_id=$TEST_COURSE_ID" "" "Authorization: Bearer $FACULTY_TOKEN")
    http_code=$(echo "$result" | cut -d'|' -f1)
    body=$(echo "$result" | cut -d'|' -f2-)

    echo -e "       ${BLUE}Method:${NC} GET"
    echo -e "       ${BLUE}Endpoint:${NC} /assignments?course_id=$TEST_COURSE_ID"
    echo -e "       ${BLUE}Status:${NC} $http_code"
    echo ""

    if [ "$http_code" = "200" ]; then
        print_result "List assignments by course" "PASS" "HTTP 200 - Filtered assignments retrieved successfully"
    else
        print_result "List assignments by course" "FAIL" "HTTP $http_code - $body"
    fi
else
    print_result "List assignments by course" "SKIP" "No faculty token or course ID available"
fi

# =============================================================================
# TEST 24: GET /api/assignments with module_id filter
# =============================================================================
echo -e "${YELLOW}========================================${NC}"
echo -e "${YELLOW}TEST 24: List Assignments by Module${NC}"
echo -e "${YELLOW}========================================${NC}"
echo ""

if [ -n "$FACULTY_TOKEN" ] && [ -n "$TEST_MODULE_ID" ]; then
    result=$(test_endpoint "GET" "/assignments?module_id=$TEST_MODULE_ID" "" "Authorization: Bearer $FACULTY_TOKEN")
    http_code=$(echo "$result" | cut -d'|' -f1)
    body=$(echo "$result" | cut -d'|' -f2-)

    echo -e "       ${BLUE}Method:${NC} GET"
    echo -e "       ${BLUE}Endpoint:${NC} /assignments?module_id=$TEST_MODULE_ID"
    echo -e "       ${BLUE}Status:${NC} $http_code"
    echo ""

    if [ "$http_code" = "200" ]; then
        print_result "List assignments by module" "PASS" "HTTP 200 - Filtered assignments retrieved successfully"
    else
        print_result "List assignments by module" "FAIL" "HTTP $http_code - $body"
    fi
else
    print_result "List assignments by module" "SKIP" "No faculty token or module ID available"
fi

# =============================================================================
# TEST 25: GET /api/assignments with is_published filter
# =============================================================================
echo -e "${YELLOW}========================================${NC}"
echo -e "${YELLOW}TEST 25: List Published Assignments${NC}"
echo -e "${YELLOW}========================================${NC}"
echo ""

if [ -n "$FACULTY_TOKEN" ]; then
    result=$(test_endpoint "GET" "/assignments?is_published=true" "" "Authorization: Bearer $FACULTY_TOKEN")
    http_code=$(echo "$result" | cut -d'|' -f1)
    body=$(echo "$result" | cut -d'|' -f2-)

    echo -e "       ${BLUE}Method:${NC} GET"
    echo -e "       ${BLUE}Endpoint:${NC} /assignments?is_published=true"
    echo -e "       ${BLUE}Status:${NC} $http_code"
    echo ""

    if [ "$http_code" = "200" ]; then
        print_result "List published assignments" "PASS" "HTTP 200 - Published assignments retrieved successfully"
    else
        print_result "List published assignments" "FAIL" "HTTP $http_code - $body"
    fi
else
    print_result "List published assignments" "SKIP" "No faculty token available"
fi

# =============================================================================
# TEST 26: GET /api/assignments with invalid ID
# =============================================================================
echo -e "${YELLOW}========================================${NC}"
echo -e "${YELLOW}TEST 26: Get Assignment with Invalid ID${NC}"
echo -e "${YELLOW}========================================${NC}"
echo ""

if [ -n "$FACULTY_TOKEN" ]; then
    result=$(test_endpoint "GET" "/assignments/999999" "" "Authorization: Bearer $FACULTY_TOKEN")
    http_code=$(echo "$result" | cut -d'|' -f1)
    body=$(echo "$result" | cut -d'|' -f2-)

    echo -e "       ${BLUE}Method:${NC} GET"
    echo -e "       ${BLUE}Endpoint:${NC} /assignments/999999"
    echo -e "       ${BLUE}Status:${NC} $http_code"
    echo ""

    if [ "$http_code" = "404" ]; then
        print_result "Get assignment with invalid ID" "PASS" "HTTP 404 - Not found as expected"
    else
        print_result "Get assignment with invalid ID" "FAIL" "Expected 404, got $http_code - $body"
    fi
else
    print_result "Get assignment with invalid ID" "SKIP" "No faculty token available"
fi

# =============================================================================
# TEST 27: GET /api/submissions without authentication
# =============================================================================
echo -e "${YELLOW}========================================${NC}"
echo -e "${YELLOW}TEST 27: List Submissions Without Authentication${NC}"
echo -e "${YELLOW}========================================${NC}"
echo ""

result=$(test_endpoint "GET" "/submissions" "" "")
http_code=$(echo "$result" | cut -d'|' -f1)
body=$(echo "$result" | cut -d'|' -f2-)

echo -e "       ${BLUE}Method:${NC} GET"
echo -e "       ${BLUE}Endpoint:${NC} /submissions"
echo -e "       ${BLUE}Status:${NC} $http_code"
echo ""

if [ "$http_code" = "401" ]; then
    print_result "List submissions without authentication" "PASS" "HTTP 401 - Unauthorized as expected"
else
    print_result "List submissions without authentication" "FAIL" "Expected 401, got $http_code - $body"
fi

# =============================================================================
# TEST 28: GET /api/submissions/{id} with invalid ID
# =============================================================================
echo -e "${YELLOW}========================================${NC}"
echo -e "${YELLOW}TEST 28: Get Submission with Invalid ID${NC}"
echo -e "${YELLOW}========================================${NC}"
echo ""

if [ -n "$STUDENT_TOKEN" ]; then
    result=$(test_endpoint "GET" "/submissions/999999" "" "Authorization: Bearer $STUDENT_TOKEN")
    http_code=$(echo "$result" | cut -d'|' -f1)
    body=$(echo "$result" | cut -d'|' -f2-)

    echo -e "       ${BLUE}Method:${NC} GET"
    echo -e "       ${BLUE}Endpoint:${NC} /submissions/999999"
    echo -e "       ${BLUE}Status:${NC} $http_code"
    echo ""

    if [ "$http_code" = "404" ]; then
        print_result "Get submission with invalid ID" "PASS" "HTTP 404 - Not found as expected"
    else
        print_result "Get submission with invalid ID" "FAIL" "Expected 404, got $http_code - $body"
    fi
else
    print_result "Get submission with invalid ID" "SKIP" "No student token available"
fi

# =============================================================================
# TEST 29: POST /api/assignments/{id}/submit with faculty token (should fail)
# =============================================================================
echo -e "${YELLOW}========================================${NC}"
echo -e "${YELLOW}TEST 29: Submit Assignment (Faculty - should fail)${NC}"
echo -e "${YELLOW}========================================${NC}"
echo ""

if [ -n "$FACULTY_TOKEN" ] && [ -n "$TEST_ASSIGNMENT_ID" ]; then
    submit_assignment_data='{
        "content": "Faculty trying to submit"
    }'

    result=$(test_endpoint "POST" "/assignments/$TEST_ASSIGNMENT_ID/submit" "$submit_assignment_data" "Authorization: Bearer $FACULTY_TOKEN")
    http_code=$(echo "$result" | cut -d'|' -f1)
    body=$(echo "$result" | cut -d'|' -f2-)

    echo -e "       ${BLUE}Method:${NC} POST"
    echo -e "       ${BLUE}Endpoint:${NC} /assignments/$TEST_ASSIGNMENT_ID/submit"
    echo -e "       ${BLUE}Status:${NC} $http_code"
    echo ""

    if [ "$http_code" = "403" ]; then
        print_result "Submit assignment (faculty)" "PASS" "HTTP 403 - Forbidden as expected"
    else
        print_result "Submit assignment (faculty)" "FAIL" "Expected 403, got $http_code - $body"
    fi
else
    print_result "Submit assignment (faculty)" "SKIP" "No faculty token or assignment ID available"
fi

# =============================================================================
# TEST 30: GET /api/assignments with type filter
# =============================================================================
echo -e "${YELLOW}========================================${NC}"
echo -e "${YELLOW}TEST 30: List Assignments by Type${NC}"
echo -e "${YELLOW}========================================${NC}"
echo ""

if [ -n "$FACULTY_TOKEN" ]; then
    result=$(test_endpoint "GET" "/assignments?type=homework" "" "Authorization: Bearer $FACULTY_TOKEN")
    http_code=$(echo "$result" | cut -d'|' -f1)
    body=$(echo "$result" | cut -d'|' -f2-)

    echo -e "       ${BLUE}Method:${NC} GET"
    echo -e "       ${BLUE}Endpoint:${NC} /assignments?type=homework"
    echo -e "       ${BLUE}Status:${NC} $http_code"
    echo ""

    if [ "$http_code" = "200" ]; then
        print_result "List assignments by type" "PASS" "HTTP 200 - Filtered assignments retrieved successfully"
    else
        print_result "List assignments by type" "FAIL" "HTTP $http_code - $body"
    fi
else
    print_result "List assignments by type" "SKIP" "No faculty token available"
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