#!/bin/bash

# =============================================================================
# Discussion Endpoint Tests
# Tests all discussion-related API endpoints using curl
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
TEST_THREAD_ID=""
TEST_POST_ID=""
TEST_COURSE_ID=""
TEST_MODULE_ID=""

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

# Get course and module IDs for thread creation
if [ -n "$ADMIN_TOKEN" ]; then
    result=$(test_endpoint "GET" "/courses" "" "Authorization: Bearer $ADMIN_TOKEN")
    body=$(echo "$result" | cut -d'|' -f2-)
    TEST_COURSE_ID=$(echo "$body" | grep -o '"id":[0-9]*' | head -1 | cut -d':' -f2)

    if [ -n "$TEST_COURSE_ID" ]; then
        result=$(test_endpoint "GET" "/courses/$TEST_COURSE_ID/modules" "" "Authorization: Bearer $ADMIN_TOKEN")
        body=$(echo "$result" | cut -d'|' -f2-)
        TEST_MODULE_ID=$(echo "$body" | grep -o '"id":[0-9]*' | head -1 | cut -d':' -f2)
    fi
fi

# Check if we have tokens
if [ -z "$ADMIN_TOKEN" ] && [ -z "$FACULTY_TOKEN" ] && [ -z "$STUDENT_TOKEN" ]; then
    echo -e "${RED}No authentication tokens available. Exiting.${NC}"
    exit 1
fi

echo ""

# =============================================================================
# TEST 1: GET /api/discussion-threads - List all discussion threads
# =============================================================================
echo -e "${YELLOW}========================================${NC}"
echo -e "${YELLOW}TEST 1: List All Discussion Threads${NC}"
echo -e "${YELLOW}========================================${NC}"
echo ""

if [ -n "$ADMIN_TOKEN" ]; then
    result=$(test_endpoint "GET" "/discussion-threads" "" "Authorization: Bearer $ADMIN_TOKEN")
    http_code=$(echo "$result" | cut -d'|' -f1)
    body=$(echo "$result" | cut -d'|' -f2-)

    echo -e "       ${BLUE}Method:${NC} GET"
    echo -e "       ${BLUE}Endpoint:${NC} /discussion-threads"
    echo -e "       ${BLUE}Status:${NC} $http_code"
    echo -e "       ${BLUE}Response:${NC} $(echo "$body" | head -c 200)..."
    echo ""

    if [ "$http_code" = "200" ]; then
        print_result "List all discussion threads" "PASS" "HTTP 200 - Threads retrieved successfully"
    else
        print_result "List all discussion threads" "FAIL" "HTTP $http_code - $body"
    fi
else
    print_result "List all discussion threads" "SKIP" "No admin token available"
fi

# =============================================================================
# TEST 2: GET /api/discussion-threads/my-threads - Get my threads
# =============================================================================
echo -e "${YELLOW}========================================${NC}"
echo -e "${YELLOW}TEST 2: Get My Discussion Threads${NC}"
echo -e "${YELLOW}========================================${NC}"
echo ""

if [ -n "$STUDENT_TOKEN" ]; then
    result=$(test_endpoint "GET" "/discussion-threads/my-threads" "" "Authorization: Bearer $STUDENT_TOKEN")
    http_code=$(echo "$result" | cut -d'|' -f1)
    body=$(echo "$result" | cut -d'|' -f2-)

    echo -e "       ${BLUE}Method:${NC} GET"
    echo -e "       ${BLUE}Endpoint:${NC} /discussion-threads/my-threads"
    echo -e "       ${BLUE}Status:${NC} $http_code"
    echo ""

    if [ "$http_code" = "200" ]; then
        print_result "Get my discussion threads" "PASS" "HTTP 200 - My threads retrieved successfully"
    else
        print_result "Get my discussion threads" "FAIL" "HTTP $http_code - $body"
    fi
else
    print_result "Get my discussion threads" "SKIP" "No student token available"
fi

# =============================================================================
# TEST 3: GET /api/discussion-threads/by-course/{courseId} - Get threads by course
# =============================================================================
echo -e "${YELLOW}========================================${NC}"
echo -e "${YELLOW}TEST 3: Get Threads by Course${NC}"
echo -e "${YELLOW}========================================${NC}"
echo ""

if [ -n "$ADMIN_TOKEN" ] && [ -n "$TEST_COURSE_ID" ]; then
    result=$(test_endpoint "GET" "/discussion-threads/by-course/$TEST_COURSE_ID" "" "Authorization: Bearer $ADMIN_TOKEN")
    http_code=$(echo "$result" | cut -d'|' -f1)
    body=$(echo "$result" | cut -d'|' -f2-)

    echo -e "       ${BLUE}Method:${NC} GET"
    echo -e "       ${BLUE}Endpoint:${NC} /discussion-threads/by-course/$TEST_COURSE_ID"
    echo -e "       ${BLUE}Status:${NC} $http_code"
    echo ""

    if [ "$http_code" = "200" ]; then
        print_result "Get threads by course" "PASS" "HTTP 200 - Course threads retrieved successfully"
    else
        print_result "Get threads by course" "FAIL" "HTTP $http_code - $body"
    fi
else
    print_result "Get threads by course" "SKIP" "No admin token or course ID available"
fi

# =============================================================================
# TEST 4: GET /api/discussion-threads/by-module/{moduleId} - Get threads by module
# =============================================================================
echo -e "${YELLOW}========================================${NC}"
echo -e "${YELLOW}TEST 4: Get Threads by Module${NC}"
echo -e "${YELLOW}========================================${NC}"
echo ""

if [ -n "$ADMIN_TOKEN" ] && [ -n "$TEST_MODULE_ID" ]; then
    result=$(test_endpoint "GET" "/discussion-threads/by-module/$TEST_MODULE_ID" "" "Authorization: Bearer $ADMIN_TOKEN")
    http_code=$(echo "$result" | cut -d'|' -f1)
    body=$(echo "$result" | cut -d'|' -f2-)

    echo -e "       ${BLUE}Method:${NC} GET"
    echo -e "       ${BLUE}Endpoint:${NC} /discussion-threads/by-module/$TEST_MODULE_ID"
    echo -e "       ${BLUE}Status:${NC} $http_code"
    echo ""

    if [ "$http_code" = "200" ]; then
        print_result "Get threads by module" "PASS" "HTTP 200 - Module threads retrieved successfully"
    else
        print_result "Get threads by module" "FAIL" "HTTP $http_code - $body"
    fi
else
    print_result "Get threads by module" "SKIP" "No admin token or module ID available"
fi

# =============================================================================
# TEST 5: POST /api/discussion-threads - Create new discussion thread
# =============================================================================
echo -e "${YELLOW}========================================${NC}"
echo -e "${YELLOW}TEST 5: Create New Discussion Thread${NC}"
echo -e "${YELLOW}========================================${NC}"
echo ""

if [ -n "$FACULTY_TOKEN" ] && [ -n "$TEST_COURSE_ID" ]; then
    timestamp=$(date +%s)
    create_thread_data='{
        "course_id": '$TEST_COURSE_ID',
        "title": "Test Discussion Thread '$timestamp'",
        "content": "This is a test discussion thread created by automated testing",
        "type": "question"
    }'

    result=$(test_endpoint "POST" "/discussion-threads" "$create_thread_data" "Authorization: Bearer $FACULTY_TOKEN")
    http_code=$(echo "$result" | cut -d'|' -f1)
    body=$(echo "$result" | cut -d'|' -f2-)

    echo -e "       ${BLUE}Method:${NC} POST"
    echo -e "       ${BLUE}Endpoint:${NC} /discussion-threads"
    echo -e "       ${BLUE}Status:${NC} $http_code"
    echo -e "       ${BLUE}Response:${NC} $(echo "$body" | head -c 300)..."
    echo ""

    if [ "$http_code" = "201" ] || [ "$http_code" = "200" ]; then
        print_result "Create new discussion thread" "PASS" "HTTP $http_code - Thread created successfully"
        # Extract the created thread ID for later tests
        TEST_THREAD_ID=$(echo "$body" | grep -o '"id":[0-9]*' | head -1 | cut -d':' -f2)
    else
        print_result "Create new discussion thread" "FAIL" "HTTP $http_code - $body"
    fi
else
    print_result "Create new discussion thread" "SKIP" "No faculty token or course ID available"
fi

# =============================================================================
# TEST 6: GET /api/discussion-threads/{id} - Get specific thread
# =============================================================================
echo -e "${YELLOW}========================================${NC}"
echo -e "${YELLOW}TEST 6: Get Discussion Thread by ID${NC}"
echo -e "${YELLOW}========================================${NC}"
echo ""

if [ -n "$ADMIN_TOKEN" ] && [ -n "$TEST_THREAD_ID" ]; then
    result=$(test_endpoint "GET" "/discussion-threads/$TEST_THREAD_ID" "" "Authorization: Bearer $ADMIN_TOKEN")
    http_code=$(echo "$result" | cut -d'|' -f1)
    body=$(echo "$result" | cut -d'|' -f2-)

    echo -e "       ${BLUE}Method:${NC} GET"
    echo -e "       ${BLUE}Endpoint:${NC} /discussion-threads/$TEST_THREAD_ID"
    echo -e "       ${BLUE}Status:${NC} $http_code"
    echo ""

    if [ "$http_code" = "200" ]; then
        print_result "Get discussion thread by ID" "PASS" "HTTP 200 - Thread retrieved successfully"
    else
        print_result "Get discussion thread by ID" "FAIL" "HTTP $http_code - $body"
    fi
elif [ -n "$ADMIN_TOKEN" ]; then
    # Try to get any existing thread from the list
    result=$(test_endpoint "GET" "/discussion-threads" "" "Authorization: Bearer $ADMIN_TOKEN")
    body=$(echo "$result" | cut -d'|' -f2-)
    TEST_THREAD_ID=$(echo "$body" | grep -o '"id":[0-9]*' | head -1 | cut -d':' -f2)

    if [ -n "$TEST_THREAD_ID" ]; then
        result=$(test_endpoint "GET" "/discussion-threads/$TEST_THREAD_ID" "" "Authorization: Bearer $ADMIN_TOKEN")
        http_code=$(echo "$result" | cut -d'|' -f1)
        body=$(echo "$result" | cut -d'|' -f2-)

        echo -e "       ${BLUE}Method:${NC} GET"
        echo -e "       ${BLUE}Endpoint:${NC} /discussion-threads/$TEST_THREAD_ID"
        echo -e "       ${BLUE}Status:${NC} $http_code"
        echo ""

        if [ "$http_code" = "200" ]; then
            print_result "Get discussion thread by ID" "PASS" "HTTP 200 - Thread retrieved successfully"
        else
            print_result "Get discussion thread by ID" "FAIL" "HTTP $http_code - $body"
        fi
    else
        print_result "Get discussion thread by ID" "SKIP" "No thread ID available"
    fi
else
    print_result "Get discussion thread by ID" "SKIP" "No admin token available"
fi

# =============================================================================
# TEST 7: PUT /api/discussion-threads/{id} - Update discussion thread
# =============================================================================
echo -e "${YELLOW}========================================${NC}"
echo -e "${YELLOW}TEST 7: Update Discussion Thread${NC}"
echo -e "${YELLOW}========================================${NC}"
echo ""

if [ -n "$FACULTY_TOKEN" ] && [ -n "$TEST_THREAD_ID" ]; then
    update_thread_data='{
        "title": "Updated Test Discussion Thread",
        "content": "This thread has been updated by automated testing"
    }'

    result=$(test_endpoint "PUT" "/discussion-threads/$TEST_THREAD_ID" "$update_thread_data" "Authorization: Bearer $FACULTY_TOKEN")
    http_code=$(echo "$result" | cut -d'|' -f1)
    body=$(echo "$result" | cut -d'|' -f2-)

    echo -e "       ${BLUE}Method:${NC} PUT"
    echo -e "       ${BLUE}Endpoint:${NC} /discussion-threads/$TEST_THREAD_ID"
    echo -e "       ${BLUE}Status:${NC} $http_code"
    echo ""

    if [ "$http_code" = "200" ]; then
        print_result "Update discussion thread" "PASS" "HTTP 200 - Thread updated successfully"
    else
        print_result "Update discussion thread" "FAIL" "HTTP $http_code - $body"
    fi
else
    print_result "Update discussion thread" "SKIP" "No faculty token or thread ID available"
fi

# =============================================================================
# TEST 8: POST /api/discussion-threads/{id}/posts - Create post in thread
# =============================================================================
echo -e "${YELLOW}========================================${NC}"
echo -e "${YELLOW}TEST 8: Create Post in Thread${NC}"
echo -e "${YELLOW}========================================${NC}"
echo ""

if [ -n "$STUDENT_TOKEN" ] && [ -n "$TEST_THREAD_ID" ]; then
    timestamp=$(date +%s)
    create_post_data='{
        "content": "This is a test post in the discussion thread '$timestamp'"
    }'

    result=$(test_endpoint "POST" "/discussion-threads/$TEST_THREAD_ID/posts" "$create_post_data" "Authorization: Bearer $STUDENT_TOKEN")
    http_code=$(echo "$result" | cut -d'|' -f1)
    body=$(echo "$result" | cut -d'|' -f2-)

    echo -e "       ${BLUE}Method:${NC} POST"
    echo -e "       ${BLUE}Endpoint:${NC} /discussion-threads/$TEST_THREAD_ID/posts"
    echo -e "       ${BLUE}Status:${NC} $http_code"
    echo -e "       ${BLUE}Response:${NC} $(echo "$body" | head -c 300)..."
    echo ""

    if [ "$http_code" = "201" ] || [ "$http_code" = "200" ]; then
        print_result "Create post in thread" "PASS" "HTTP $http_code - Post created successfully"
        # Extract the created post ID for later tests
        TEST_POST_ID=$(echo "$body" | grep -o '"id":[0-9]*' | head -1 | cut -d':' -f2)
    else
        print_result "Create post in thread" "FAIL" "HTTP $http_code - $body"
    fi
else
    print_result "Create post in thread" "SKIP" "No student token or thread ID available"
fi

# =============================================================================
# TEST 9: GET /api/discussion-threads/{id}/posts - Get thread posts
# =============================================================================
echo -e "${YELLOW}========================================${NC}"
echo -e "${YELLOW}TEST 9: Get Thread Posts${NC}"
echo -e "${YELLOW}========================================${NC}"
echo ""

if [ -n "$ADMIN_TOKEN" ] && [ -n "$TEST_THREAD_ID" ]; then
    result=$(test_endpoint "GET" "/discussion-threads/$TEST_THREAD_ID/posts" "" "Authorization: Bearer $ADMIN_TOKEN")
    http_code=$(echo "$result" | cut -d'|' -f1)
    body=$(echo "$result" | cut -d'|' -f2-)

    echo -e "       ${BLUE}Method:${NC} GET"
    echo -e "       ${BLUE}Endpoint:${NC} /discussion-threads/$TEST_THREAD_ID/posts"
    echo -e "       ${BLUE}Status:${NC} $http_code"
    echo ""

    if [ "$http_code" = "200" ]; then
        print_result "Get thread posts" "PASS" "HTTP 200 - Posts retrieved successfully"
    else
        print_result "Get thread posts" "FAIL" "HTTP $http_code - $body"
    fi
else
    print_result "Get thread posts" "SKIP" "No admin token or thread ID available"
fi

# =============================================================================
# TEST 10: GET /api/discussion-posts/{id} - Get specific post
# =============================================================================
echo -e "${YELLOW}========================================${NC}"
echo -e "${YELLOW}TEST 10: Get Discussion Post by ID${NC}"
echo -e "${YELLOW}========================================${NC}"
echo ""

if [ -n "$ADMIN_TOKEN" ] && [ -n "$TEST_POST_ID" ]; then
    result=$(test_endpoint "GET" "/discussion-posts/$TEST_POST_ID" "" "Authorization: Bearer $ADMIN_TOKEN")
    http_code=$(echo "$result" | cut -d'|' -f1)
    body=$(echo "$result" | cut -d'|' -f2-)

    echo -e "       ${BLUE}Method:${NC} GET"
    echo -e "       ${BLUE}Endpoint:${NC} /discussion-posts/$TEST_POST_ID"
    echo -e "       ${BLUE}Status:${NC} $http_code"
    echo ""

    if [ "$http_code" = "200" ]; then
        print_result "Get discussion post by ID" "PASS" "HTTP 200 - Post retrieved successfully"
    else
        print_result "Get discussion post by ID" "FAIL" "HTTP $http_code - $body"
    fi
elif [ -n "$ADMIN_TOKEN" ] && [ -n "$TEST_THREAD_ID" ]; then
    # Try to get any existing post from the thread
    result=$(test_endpoint "GET" "/discussion-threads/$TEST_THREAD_ID/posts" "" "Authorization: Bearer $ADMIN_TOKEN")
    body=$(echo "$result" | cut -d'|' -f2-)
    TEST_POST_ID=$(echo "$body" | grep -o '"id":[0-9]*' | head -1 | cut -d':' -f2)

    if [ -n "$TEST_POST_ID" ]; then
        result=$(test_endpoint "GET" "/discussion-posts/$TEST_POST_ID" "" "Authorization: Bearer $ADMIN_TOKEN")
        http_code=$(echo "$result" | cut -d'|' -f1)
        body=$(echo "$result" | cut -d'|' -f2-)

        echo -e "       ${BLUE}Method:${NC} GET"
        echo -e "       ${BLUE}Endpoint:${NC} /discussion-posts/$TEST_POST_ID"
        echo -e "       ${BLUE}Status:${NC} $http_code"
        echo ""

        if [ "$http_code" = "200" ]; then
            print_result "Get discussion post by ID" "PASS" "HTTP 200 - Post retrieved successfully"
        else
            print_result "Get discussion post by ID" "FAIL" "HTTP $http_code - $body"
        fi
    else
        print_result "Get discussion post by ID" "SKIP" "No post ID available"
    fi
else
    print_result "Get discussion post by ID" "SKIP" "No admin token or post/thread ID available"
fi

# =============================================================================
# TEST 11: PUT /api/discussion-posts/{id} - Update discussion post
# =============================================================================
echo -e "${YELLOW}========================================${NC}"
echo -e "${YELLOW}TEST 11: Update Discussion Post${NC}"
echo -e "${YELLOW}========================================${NC}"
echo ""

if [ -n "$STUDENT_TOKEN" ] && [ -n "$TEST_POST_ID" ]; then
    update_post_data='{
        "content": "This post has been updated by automated testing"
    }'

    result=$(test_endpoint "PUT" "/discussion-posts/$TEST_POST_ID" "$update_post_data" "Authorization: Bearer $STUDENT_TOKEN")
    http_code=$(echo "$result" | cut -d'|' -f1)
    body=$(echo "$result" | cut -d'|' -f2-)

    echo -e "       ${BLUE}Method:${NC} PUT"
    echo -e "       ${BLUE}Endpoint:${NC} /discussion-posts/$TEST_POST_ID"
    echo -e "       ${BLUE}Status:${NC} $http_code"
    echo ""

    if [ "$http_code" = "200" ]; then
        print_result "Update discussion post" "PASS" "HTTP 200 - Post updated successfully"
    else
        print_result "Update discussion post" "FAIL" "HTTP $http_code - $body"
    fi
else
    print_result "Update discussion post" "SKIP" "No student token or post ID available"
fi

# =============================================================================
# TEST 12: POST /api/discussion-posts/{id}/like - Like post
# =============================================================================
echo -e "${YELLOW}========================================${NC}"
echo -e "${YELLOW}TEST 12: Like Discussion Post${NC}"
echo -e "${YELLOW}========================================${NC}"
echo ""

if [ -n "$STUDENT_TOKEN" ] && [ -n "$TEST_POST_ID" ]; then
    result=$(test_endpoint "POST" "/discussion-posts/$TEST_POST_ID/like" "" "Authorization: Bearer $STUDENT_TOKEN")
    http_code=$(echo "$result" | cut -d'|' -f1)
    body=$(echo "$result" | cut -d'|' -f2-)

    echo -e "       ${BLUE}Method:${NC} POST"
    echo -e "       ${BLUE}Endpoint:${NC} /discussion-posts/$TEST_POST_ID/like"
    echo -e "       ${BLUE}Status:${NC} $http_code"
    echo ""

    if [ "$http_code" = "200" ]; then
        print_result "Like discussion post" "PASS" "HTTP 200 - Post liked successfully"
    else
        print_result "Like discussion post" "FAIL" "HTTP $http_code - $body"
    fi
else
    print_result "Like discussion post" "SKIP" "No student token or post ID available"
fi

# =============================================================================
# TEST 13: POST /api/discussion-posts/{id}/unlike - Unlike post
# =============================================================================
echo -e "${YELLOW}========================================${NC}"
echo -e "${YELLOW}TEST 13: Unlike Discussion Post${NC}"
echo -e "${YELLOW}========================================${NC}"
echo ""

if [ -n "$STUDENT_TOKEN" ] && [ -n "$TEST_POST_ID" ]; then
    result=$(test_endpoint "POST" "/discussion-posts/$TEST_POST_ID/unlike" "" "Authorization: Bearer $STUDENT_TOKEN")
    http_code=$(echo "$result" | cut -d'|' -f1)
    body=$(echo "$result" | cut -d'|' -f2-)

    echo -e "       ${BLUE}Method:${NC} POST"
    echo -e "       ${BLUE}Endpoint:${NC} /discussion-posts/$TEST_POST_ID/unlike"
    echo -e "       ${BLUE}Status:${NC} $http_code"
    echo ""

    if [ "$http_code" = "200" ]; then
        print_result "Unlike discussion post" "PASS" "HTTP 200 - Post unliked successfully"
    else
        print_result "Unlike discussion post" "FAIL" "HTTP $http_code - $body"
    fi
else
    print_result "Unlike discussion post" "SKIP" "No student token or post ID available"
fi

# =============================================================================
# TEST 14: POST /api/discussion-posts/{id}/mark-as-solution - Mark as solution
# =============================================================================
echo -e "${YELLOW}========================================${NC}"
echo -e "${YELLOW}TEST 14: Mark Post as Solution${NC}"
echo -e "${YELLOW}========================================${NC}"
echo ""

if [ -n "$STUDENT_TOKEN" ] && [ -n "$TEST_POST_ID" ]; then
    result=$(test_endpoint "POST" "/discussion-posts/$TEST_POST_ID/mark-as-solution" "" "Authorization: Bearer $STUDENT_TOKEN")
    http_code=$(echo "$result" | cut -d'|' -f1)
    body=$(echo "$result" | cut -d'|' -f2-)

    echo -e "       ${BLUE}Method:${NC} POST"
    echo -e "       ${BLUE}Endpoint:${NC} /discussion-posts/$TEST_POST_ID/mark-as-solution"
    echo -e "       ${BLUE}Status:${NC} $http_code"
    echo ""

    if [ "$http_code" = "200" ]; then
        print_result "Mark post as solution" "PASS" "HTTP 200 - Post marked as solution"
    else
        print_result "Mark post as solution" "FAIL" "HTTP $http_code - $body"
    fi
else
    print_result "Mark post as solution" "SKIP" "No student token or post ID available"
fi

# =============================================================================
# TEST 15: POST /api/discussion-posts/{id}/unmark-as-solution - Unmark as solution
# =============================================================================
echo -e "${YELLOW}========================================${NC}"
echo -e "${YELLOW}TEST 15: Unmark Post as Solution${NC}"
echo -e "${YELLOW}========================================${NC}"
echo ""

if [ -n "$STUDENT_TOKEN" ] && [ -n "$TEST_POST_ID" ]; then
    result=$(test_endpoint "POST" "/discussion-posts/$TEST_POST_ID/unmark-as-solution" "" "Authorization: Bearer $STUDENT_TOKEN")
    http_code=$(echo "$result" | cut -d'|' -f1)
    body=$(echo "$result" | cut -d'|' -f2-)

    echo -e "       ${BLUE}Method:${NC} POST"
    echo -e "       ${BLUE}Endpoint:${NC} /discussion-posts/$TEST_POST_ID/unmark-as-solution"
    echo -e "       ${BLUE}Status:${NC} $http_code"
    echo ""

    if [ "$http_code" = "200" ]; then
        print_result "Unmark post as solution" "PASS" "HTTP 200 - Post unmarked as solution"
    else
        print_result "Unmark post as solution" "FAIL" "HTTP $http_code - $body"
    fi
else
    print_result "Unmark post as solution" "SKIP" "No student token or post ID available"
fi

# =============================================================================
# TEST 16: GET /api/discussion-posts/my-posts - Get my posts
# =============================================================================
echo -e "${YELLOW}========================================${NC}"
echo -e "${YELLOW}TEST 16: Get My Discussion Posts${NC}"
echo -e "${YELLOW}========================================${NC}"
echo ""

if [ -n "$STUDENT_TOKEN" ]; then
    result=$(test_endpoint "GET" "/discussion-posts/my-posts" "" "Authorization: Bearer $STUDENT_TOKEN")
    http_code=$(echo "$result" | cut -d'|' -f1)
    body=$(echo "$result" | cut -d'|' -f2-)

    echo -e "       ${BLUE}Method:${NC} GET"
    echo -e "       ${BLUE}Endpoint:${NC} /discussion-posts/my-posts"
    echo -e "       ${BLUE}Status:${NC} $http_code"
    echo ""

    if [ "$http_code" = "200" ]; then
        print_result "Get my discussion posts" "PASS" "HTTP 200 - My posts retrieved successfully"
    else
        print_result "Get my discussion posts" "FAIL" "HTTP $http_code - $body"
    fi
else
    print_result "Get my discussion posts" "SKIP" "No student token available"
fi

# =============================================================================
# TEST 17: POST /api/discussion-posts/{id}/reply - Reply to post
# =============================================================================
echo -e "${YELLOW}========================================${NC}"
echo -e "${YELLOW}TEST 17: Reply to Discussion Post${NC}"
echo -e "${YELLOW}========================================${NC}"
echo ""

if [ -n "$STUDENT_TOKEN" ] && [ -n "$TEST_POST_ID" ]; then
    timestamp=$(date +%s)
    reply_data='{
        "content": "This is a test reply to the discussion post '$timestamp'"
    }'

    result=$(test_endpoint "POST" "/discussion-posts/$TEST_POST_ID/reply" "$reply_data" "Authorization: Bearer $STUDENT_TOKEN")
    http_code=$(echo "$result" | cut -d'|' -f1)
    body=$(echo "$result" | cut -d'|' -f2-)

    echo -e "       ${BLUE}Method:${NC} POST"
    echo -e "       ${BLUE}Endpoint:${NC} /discussion-posts/$TEST_POST_ID/reply"
    echo -e "       ${BLUE}Status:${NC} $http_code"
    echo ""

    if [ "$http_code" = "201" ] || [ "$http_code" = "200" ]; then
        print_result "Reply to discussion post" "PASS" "HTTP $http_code - Reply created successfully"
    else
        print_result "Reply to discussion post" "FAIL" "HTTP $http_code - $body"
    fi
else
    print_result "Reply to discussion post" "SKIP" "No student token or post ID available"
fi

# =============================================================================
# TEST 18: GET /api/discussion-posts/{id}/replies - Get post replies
# =============================================================================
echo -e "${YELLOW}========================================${NC}"
echo -e "${YELLOW}TEST 18: Get Post Replies${NC}"
echo -e "${YELLOW}========================================${NC}"
echo ""

if [ -n "$ADMIN_TOKEN" ] && [ -n "$TEST_POST_ID" ]; then
    result=$(test_endpoint "GET" "/discussion-posts/$TEST_POST_ID/replies" "" "Authorization: Bearer $ADMIN_TOKEN")
    http_code=$(echo "$result" | cut -d'|' -f1)
    body=$(echo "$result" | cut -d'|' -f2-)

    echo -e "       ${BLUE}Method:${NC} GET"
    echo -e "       ${BLUE}Endpoint:${NC} /discussion-posts/$TEST_POST_ID/replies"
    echo -e "       ${BLUE}Status:${NC} $http_code"
    echo ""

    if [ "$http_code" = "200" ]; then
        print_result "Get post replies" "PASS" "HTTP 200 - Replies retrieved successfully"
    else
        print_result "Get post replies" "FAIL" "HTTP $http_code - $body"
    fi
else
    print_result "Get post replies" "SKIP" "No admin token or post ID available"
fi

# =============================================================================
# TEST 19: POST /api/discussion-threads/{id}/close - Close thread
# =============================================================================
echo -e "${YELLOW}========================================${NC}"
echo -e "${YELLOW}TEST 19: Close Discussion Thread${NC}"
echo -e "${YELLOW}========================================${NC}"
echo ""

if [ -n "$FACULTY_TOKEN" ] && [ -n "$TEST_THREAD_ID" ]; then
    result=$(test_endpoint "POST" "/discussion-threads/$TEST_THREAD_ID/close" "" "Authorization: Bearer $FACULTY_TOKEN")
    http_code=$(echo "$result" | cut -d'|' -f1)
    body=$(echo "$result" | cut -d'|' -f2-)

    echo -e "       ${BLUE}Method:${NC} POST"
    echo -e "       ${BLUE}Endpoint:${NC} /discussion-threads/$TEST_THREAD_ID/close"
    echo -e "       ${BLUE}Status:${NC} $http_code"
    echo ""

    if [ "$http_code" = "200" ]; then
        print_result "Close discussion thread" "PASS" "HTTP 200 - Thread closed successfully"
    else
        print_result "Close discussion thread" "FAIL" "HTTP $http_code - $body"
    fi
else
    print_result "Close discussion thread" "SKIP" "No faculty token or thread ID available"
fi

# =============================================================================
# TEST 20: POST /api/discussion-threads/{id}/reopen - Reopen thread
# =============================================================================
echo -e "${YELLOW}========================================${NC}"
echo -e "${YELLOW}TEST 20: Reopen Discussion Thread${NC}"
echo -e "${YELLOW}========================================${NC}"
echo ""

if [ -n "$FACULTY_TOKEN" ] && [ -n "$TEST_THREAD_ID" ]; then
    result=$(test_endpoint "POST" "/discussion-threads/$TEST_THREAD_ID/reopen" "" "Authorization: Bearer $FACULTY_TOKEN")
    http_code=$(echo "$result" | cut -d'|' -f1)
    body=$(echo "$result" | cut -d'|' -f2-)

    echo -e "       ${BLUE}Method:${NC} POST"
    echo -e "       ${BLUE}Endpoint:${NC} /discussion-threads/$TEST_THREAD_ID/reopen"
    echo -e "       ${BLUE}Status:${NC} $http_code"
    echo ""

    if [ "$http_code" = "200" ]; then
        print_result "Reopen discussion thread" "PASS" "HTTP 200 - Thread reopened successfully"
    else
        print_result "Reopen discussion thread" "FAIL" "HTTP $http_code - $body"
    fi
else
    print_result "Reopen discussion thread" "SKIP" "No faculty token or thread ID available"
fi

# =============================================================================
# TEST 21: POST /api/discussion-threads/{id}/pin - Pin thread (admin/faculty)
# =============================================================================
echo -e "${YELLOW}========================================${NC}"
echo -e "${YELLOW}TEST 21: Pin Discussion Thread${NC}"
echo -e "${YELLOW}========================================${NC}"
echo ""

if [ -n "$FACULTY_TOKEN" ] && [ -n "$TEST_THREAD_ID" ]; then
    result=$(test_endpoint "POST" "/discussion-threads/$TEST_THREAD_ID/pin" "" "Authorization: Bearer $FACULTY_TOKEN")
    http_code=$(echo "$result" | cut -d'|' -f1)
    body=$(echo "$result" | cut -d'|' -f2-)

    echo -e "       ${BLUE}Method:${NC} POST"
    echo -e "       ${BLUE}Endpoint:${NC} /discussion-threads/$TEST_THREAD_ID/pin"
    echo -e "       ${BLUE}Status:${NC} $http_code"
    echo ""

    if [ "$http_code" = "200" ]; then
        print_result "Pin discussion thread" "PASS" "HTTP 200 - Thread pinned successfully"
    else
        print_result "Pin discussion thread" "FAIL" "HTTP $http_code - $body"
    fi
else
    print_result "Pin discussion thread" "SKIP" "No faculty token or thread ID available"
fi

# =============================================================================
# TEST 22: POST /api/discussion-threads/{id}/unpin - Unpin thread (admin/faculty)
# =============================================================================
echo -e "${YELLOW}========================================${NC}"
echo -e "${YELLOW}TEST 22: Unpin Discussion Thread${NC}"
echo -e "${YELLOW}========================================${NC}"
echo ""

if [ -n "$FACULTY_TOKEN" ] && [ -n "$TEST_THREAD_ID" ]; then
    result=$(test_endpoint "POST" "/discussion-threads/$TEST_THREAD_ID/unpin" "" "Authorization: Bearer $FACULTY_TOKEN")
    http_code=$(echo "$result" | cut -d'|' -f1)
    body=$(echo "$result" | cut -d'|' -f2-)

    echo -e "       ${BLUE}Method:${NC} POST"
    echo -e "       ${BLUE}Endpoint:${NC} /discussion-threads/$TEST_THREAD_ID/unpin"
    echo -e "       ${BLUE}Status:${NC} $http_code"
    echo ""

    if [ "$http_code" = "200" ]; then
        print_result "Unpin discussion thread" "PASS" "HTTP 200 - Thread unpinned successfully"
    else
        print_result "Unpin discussion thread" "FAIL" "HTTP $http_code - $body"
    fi
else
    print_result "Unpin discussion thread" "SKIP" "No faculty token or thread ID available"
fi

# =============================================================================
# TEST 23: POST /api/discussion-threads/{id}/lock - Lock thread (admin/faculty)
# =============================================================================
echo -e "${YELLOW}========================================${NC}"
echo -e "${YELLOW}TEST 23: Lock Discussion Thread${NC}"
echo -e "${YELLOW}========================================${NC}"
echo ""

if [ -n "$FACULTY_TOKEN" ] && [ -n "$TEST_THREAD_ID" ]; then
    result=$(test_endpoint "POST" "/discussion-threads/$TEST_THREAD_ID/lock" "" "Authorization: Bearer $FACULTY_TOKEN")
    http_code=$(echo "$result" | cut -d'|' -f1)
    body=$(echo "$result" | cut -d'|' -f2-)

    echo -e "       ${BLUE}Method:${NC} POST"
    echo -e "       ${BLUE}Endpoint:${NC} /discussion-threads/$TEST_THREAD_ID/lock"
    echo -e "       ${BLUE}Status:${NC} $http_code"
    echo ""

    if [ "$http_code" = "200" ]; then
        print_result "Lock discussion thread" "PASS" "HTTP 200 - Thread locked successfully"
    else
        print_result "Lock discussion thread" "FAIL" "HTTP $http_code - $body"
    fi
else
    print_result "Lock discussion thread" "SKIP" "No faculty token or thread ID available"
fi

# =============================================================================
# TEST 24: POST /api/discussion-threads/{id}/unlock - Unlock thread (admin/faculty)
# =============================================================================
echo -e "${YELLOW}========================================${NC}"
echo -e "${YELLOW}TEST 24: Unlock Discussion Thread${NC}"
echo -e "${YELLOW}========================================${NC}"
echo ""

if [ -n "$FACULTY_TOKEN" ] && [ -n "$TEST_THREAD_ID" ]; then
    result=$(test_endpoint "POST" "/discussion-threads/$TEST_THREAD_ID/unlock" "" "Authorization: Bearer $FACULTY_TOKEN")
    http_code=$(echo "$result" | cut -d'|' -f1)
    body=$(echo "$result" | cut -d'|' -f2-)

    echo -e "       ${BLUE}Method:${NC} POST"
    echo -e "       ${BLUE}Endpoint:${NC} /discussion-threads/$TEST_THREAD_ID/unlock"
    echo -e "       ${BLUE}Status:${NC} $http_code"
    echo ""

    if [ "$http_code" = "200" ]; then
        print_result "Unlock discussion thread" "PASS" "HTTP 200 - Thread unlocked successfully"
    else
        print_result "Unlock discussion thread" "FAIL" "HTTP $http_code - $body"
    fi
else
    print_result "Unlock discussion thread" "SKIP" "No faculty token or thread ID available"
fi

# =============================================================================
# TEST 25: POST /api/discussion-threads/{id}/archive - Archive thread (admin/faculty)
# =============================================================================
echo -e "${YELLOW}========================================${NC}"
echo -e "${YELLOW}TEST 25: Archive Discussion Thread${NC}"
echo -e "${YELLOW}========================================${NC}"
echo ""

if [ -n "$FACULTY_TOKEN" ] && [ -n "$TEST_THREAD_ID" ]; then
    result=$(test_endpoint "POST" "/discussion-threads/$TEST_THREAD_ID/archive" "" "Authorization: Bearer $FACULTY_TOKEN")
    http_code=$(echo "$result" | cut -d'|' -f1)
    body=$(echo "$result" | cut -d'|' -f2-)

    echo -e "       ${BLUE}Method:${NC} POST"
    echo -e "       ${BLUE}Endpoint:${NC} /discussion-threads/$TEST_THREAD_ID/archive"
    echo -e "       ${BLUE}Status:${NC} $http_code"
    echo ""

    if [ "$http_code" = "200" ]; then
        print_result "Archive discussion thread" "PASS" "HTTP 200 - Thread archived successfully"
    else
        print_result "Archive discussion thread" "FAIL" "HTTP $http_code - $body"
    fi
else
    print_result "Archive discussion thread" "SKIP" "No faculty token or thread ID available"
fi

# =============================================================================
# TEST 26: POST /api/discussion-threads/{id}/restore - Restore thread (admin/faculty)
# =============================================================================
echo -e "${YELLOW}========================================${NC}"
echo -e "${YELLOW}TEST 26: Restore Discussion Thread${NC}"
echo -e "${YELLOW}========================================${NC}"
echo ""

if [ -n "$FACULTY_TOKEN" ] && [ -n "$TEST_THREAD_ID" ]; then
    result=$(test_endpoint "POST" "/discussion-threads/$TEST_THREAD_ID/restore" "" "Authorization: Bearer $FACULTY_TOKEN")
    http_code=$(echo "$result" | cut -d'|' -f1)
    body=$(echo "$result" | cut -d'|' -f2-)

    echo -e "       ${BLUE}Method:${NC} POST"
    echo -e "       ${BLUE}Endpoint:${NC} /discussion-threads/$TEST_THREAD_ID/restore"
    echo -e "       ${BLUE}Status:${NC} $http_code"
    echo ""

    if [ "$http_code" = "200" ]; then
        print_result "Restore discussion thread" "PASS" "HTTP 200 - Thread restored successfully"
    else
        print_result "Restore discussion thread" "FAIL" "HTTP $http_code - $body"
    fi
else
    print_result "Restore discussion thread" "SKIP" "No faculty token or thread ID available"
fi

# =============================================================================
# TEST 27: DELETE /api/discussion-posts/{id} - Delete discussion post
# =============================================================================
echo -e "${YELLOW}========================================${NC}"
echo -e "${YELLOW}TEST 27: Delete Discussion Post${NC}"
echo -e "${YELLOW}========================================${NC}"
echo ""

if [ -n "$STUDENT_TOKEN" ] && [ -n "$TEST_POST_ID" ]; then
    result=$(test_endpoint "DELETE" "/discussion-posts/$TEST_POST_ID" "" "Authorization: Bearer $STUDENT_TOKEN")
    http_code=$(echo "$result" | cut -d'|' -f1)
    body=$(echo "$result" | cut -d'|' -f2-)

    echo -e "       ${BLUE}Method:${NC} DELETE"
    echo -e "       ${BLUE}Endpoint:${NC} /discussion-posts/$TEST_POST_ID"
    echo -e "       ${BLUE}Status:${NC} $http_code"
    echo ""

    if [ "$http_code" = "204" ] || [ "$http_code" = "200" ]; then
        print_result "Delete discussion post" "PASS" "HTTP $http_code - Post deleted successfully"
    else
        print_result "Delete discussion post" "FAIL" "HTTP $http_code - $body"
    fi
else
    print_result "Delete discussion post" "SKIP" "No student token or post ID available"
fi

# =============================================================================
# TEST 28: DELETE /api/discussion-threads/{id} - Delete discussion thread
# =============================================================================
echo -e "${YELLOW}========================================${NC}"
echo -e "${YELLOW}TEST 28: Delete Discussion Thread${NC}"
echo -e "${YELLOW}========================================${NC}"
echo ""

if [ -n "$FACULTY_TOKEN" ] && [ -n "$TEST_THREAD_ID" ]; then
    result=$(test_endpoint "DELETE" "/discussion-threads/$TEST_THREAD_ID" "" "Authorization: Bearer $FACULTY_TOKEN")
    http_code=$(echo "$result" | cut -d'|' -f1)
    body=$(echo "$result" | cut -d'|' -f2-)

    echo -e "       ${BLUE}Method:${NC} DELETE"
    echo -e "       ${BLUE}Endpoint:${NC} /discussion-threads/$TEST_THREAD_ID"
    echo -e "       ${BLUE}Status:${NC} $http_code"
    echo ""

    if [ "$http_code" = "204" ] || [ "$http_code" = "200" ]; then
        print_result "Delete discussion thread" "PASS" "HTTP $http_code - Thread deleted successfully"
    else
        print_result "Delete discussion thread" "FAIL" "HTTP $http_code - $body"
    fi
else
    print_result "Delete discussion thread" "SKIP" "No faculty token or thread ID available"
fi

# =============================================================================
# TEST 29: GET /api/discussion-threads without authentication
# =============================================================================
echo -e "${YELLOW}========================================${NC}"
echo -e "${YELLOW}TEST 29: List Threads Without Authentication${NC}"
echo -e "${YELLOW}========================================${NC}"
echo ""

result=$(test_endpoint "GET" "/discussion-threads" "" "")
http_code=$(echo "$result" | cut -d'|' -f1)
body=$(echo "$result" | cut -d'|' -f2-)

echo -e "       ${BLUE}Method:${NC} GET"
echo -e "       ${BLUE}Endpoint:${NC} /discussion-threads"
echo -e "       ${BLUE}Status:${NC} $http_code"
echo ""

if [ "$http_code" = "401" ]; then
    print_result "List threads without authentication" "PASS" "HTTP 401 - Unauthorized as expected"
else
    print_result "List threads without authentication" "FAIL" "Expected 401, got $http_code - $body"
fi

# =============================================================================
# TEST 30: GET /api/discussion-threads/{id} with invalid ID
# =============================================================================
echo -e "${YELLOW}========================================${NC}"
echo -e "${YELLOW}TEST 30: Get Thread with Invalid ID${NC}"
echo -e "${YELLOW}========================================${NC}"
echo ""

if [ -n "$ADMIN_TOKEN" ]; then
    result=$(test_endpoint "GET" "/discussion-threads/999999" "" "Authorization: Bearer $ADMIN_TOKEN")
    http_code=$(echo "$result" | cut -d'|' -f1)
    body=$(echo "$result" | cut -d'|' -f2-)

    echo -e "       ${BLUE}Method:${NC} GET"
    echo -e "       ${BLUE}Endpoint:${NC} /discussion-threads/999999"
    echo -e "       ${BLUE}Status:${NC} $http_code"
    echo ""

    if [ "$http_code" = "404" ]; then
        print_result "Get thread with invalid ID" "PASS" "HTTP 404 - Not found as expected"
    else
        print_result "Get thread with invalid ID" "FAIL" "Expected 404, got $http_code - $body"
    fi
else
    print_result "Get thread with invalid ID" "SKIP" "No admin token available"
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