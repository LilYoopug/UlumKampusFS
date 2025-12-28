#!/bin/bash

# =============================================================================
# Library Resource Endpoint Tests
# Tests all library resource-related API endpoints using curl
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
TEST_RESOURCE_ID=""
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

# Create test faculty user (for creating library resources)
timestamp=$(date +%s)
faculty_register_data='{
    "name": "Test Faculty Library '$timestamp'",
    "email": "testfacultylibrary'$timestamp'@example.com",
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
    FACULTY_EMAIL="testfacultylibrary$timestamp@example.com"
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
    "name": "Test Student Library '$timestamp'",
    "email": "teststudentlibrary'$timestamp'@example.com",
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
    STUDENT_EMAIL="teststudentlibrary$timestamp@example.com"
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

# Get faculty ID for library resources
if [ -n "$FACULTY_TOKEN" ]; then
    result=$(test_endpoint "GET" "/faculties" "" "Authorization: Bearer $FACULTY_TOKEN")
    body=$(echo "$result" | cut -d'|' -f2-)
    TEST_FACULTY_ID=$(echo "$body" | grep -o '"id":[0-9]*' | head -1 | cut -d':' -f2)
fi

# Get course ID for course-specific library resources
if [ -n "$FACULTY_TOKEN" ]; then
    result=$(test_endpoint "GET" "/courses" "" "Authorization: Bearer $FACULTY_TOKEN")
    body=$(echo "$result" | cut -d'|' -f2-)
    TEST_COURSE_ID=$(echo "$body" | grep -o '"id":[0-9]*' | head -1 | cut -d':' -f2)
fi

echo ""

# =============================================================================
# TEST 1: GET /api/library - List all library resources
# =============================================================================
echo -e "${YELLOW}========================================${NC}"
echo -e "${YELLOW}TEST 1: List All Library Resources${NC}"
echo -e "${YELLOW}========================================${NC}"
echo ""

if [ -n "$STUDENT_TOKEN" ]; then
    result=$(test_endpoint "GET" "/library" "" "Authorization: Bearer $STUDENT_TOKEN")
    http_code=$(echo "$result" | cut -d'|' -f1)
    body=$(echo "$result" | cut -d'|' -f2-)

    echo -e "       ${BLUE}Method:${NC} GET"
    echo -e "       ${BLUE}Endpoint:${NC} /library"
    echo -e "       ${BLUE}Status:${NC} $http_code"
    echo -e "       ${BLUE}Response:${NC} $(echo "$body" | head -c 200)..."
    echo ""

    if [ "$http_code" = "200" ]; then
        print_result "List all library resources" "PASS" "HTTP 200 - Library resources retrieved successfully"
    else
        print_result "List all library resources" "FAIL" "HTTP $http_code - $body"
    fi
else
    print_result "List all library resources" "SKIP" "No student token available"
fi

# =============================================================================
# TEST 2: GET /api/library with search parameter
# =============================================================================
echo -e "${YELLOW}========================================${NC}"
echo -e "${YELLOW}TEST 2: List Library Resources with Search${NC}"
echo -e "${YELLOW}========================================${NC}"
echo ""

if [ -n "$STUDENT_TOKEN" ]; then
    result=$(test_endpoint "GET" "/library?search=book" "" "Authorization: Bearer $STUDENT_TOKEN")
    http_code=$(echo "$result" | cut -d'|' -f1)
    body=$(echo "$result" | cut -d'|' -f2-)

    echo -e "       ${BLUE}Method:${NC} GET"
    echo -e "       ${BLUE}Endpoint:${NC} /library?search=book"
    echo -e "       ${BLUE}Status:${NC} $http_code"
    echo ""

    if [ "$http_code" = "200" ]; then
        print_result "List library resources with search" "PASS" "HTTP 200 - Search results retrieved successfully"
    else
        print_result "List library resources with search" "FAIL" "HTTP $http_code - $body"
    fi
else
    print_result "List library resources with search" "SKIP" "No student token available"
fi

# =============================================================================
# TEST 3: GET /api/library with resource_type filter
# =============================================================================
echo -e "${YELLOW}========================================${NC}"
echo -e "${YELLOW}TEST 3: List Library Resources by Resource Type${NC}"
echo -e "${YELLOW}========================================${NC}"
echo ""

if [ -n "$STUDENT_TOKEN" ]; then
    result=$(test_endpoint "GET" "/library?resource_type=book" "" "Authorization: Bearer $STUDENT_TOKEN")
    http_code=$(echo "$result" | cut -d'|' -f1)
    body=$(echo "$result" | cut -d'|' -f2-)

    echo -e "       ${BLUE}Method:${NC} GET"
    echo -e "       ${BLUE}Endpoint:${NC} /library?resource_type=book"
    echo -e "       ${BLUE}Status:${NC} $http_code"
    echo ""

    if [ "$http_code" = "200" ]; then
        print_result "List library resources by resource type" "PASS" "HTTP 200 - Filtered resources retrieved successfully"
    else
        print_result "List library resources by resource type" "FAIL" "HTTP $http_code - $body"
    fi
else
    print_result "List library resources by resource type" "SKIP" "No student token available"
fi

# =============================================================================
# TEST 4: GET /api/library with access_level filter
# =============================================================================
echo -e "${YELLOW}========================================${NC}"
echo -e "${YELLOW}TEST 4: List Library Resources by Access Level${NC}"
echo -e "${YELLOW}========================================${NC}"
echo ""

if [ -n "$STUDENT_TOKEN" ]; then
    result=$(test_endpoint "GET" "/library?access_level=public" "" "Authorization: Bearer $STUDENT_TOKEN")
    http_code=$(echo "$result" | cut -d'|' -f1)
    body=$(echo "$result" | cut -d'|' -f2-)

    echo -e "       ${BLUE}Method:${NC} GET"
    echo -e "       ${BLUE}Endpoint:${NC} /library?access_level=public"
    echo -e "       ${BLUE}Status:${NC} $http_code"
    echo ""

    if [ "$http_code" = "200" ]; then
        print_result "List library resources by access level" "PASS" "HTTP 200 - Filtered resources retrieved successfully"
    else
        print_result "List library resources by access level" "FAIL" "HTTP $http_code - $body"
    fi
else
    print_result "List library resources by access level" "SKIP" "No student token available"
fi

# =============================================================================
# TEST 5: GET /api/library with course_id filter
# =============================================================================
echo -e "${YELLOW}========================================${NC}"
echo -e "${YELLOW}TEST 5: List Library Resources by Course${NC}"
echo -e "${YELLOW}========================================${NC}"
echo ""

if [ -n "$STUDENT_TOKEN" ] && [ -n "$TEST_COURSE_ID" ]; then
    result=$(test_endpoint "GET" "/library?course_id=$TEST_COURSE_ID" "" "Authorization: Bearer $STUDENT_TOKEN")
    http_code=$(echo "$result" | cut -d'|' -f1)
    body=$(echo "$result" | cut -d'|' -f2-)

    echo -e "       ${BLUE}Method:${NC} GET"
    echo -e "       ${BLUE}Endpoint:${NC} /library?course_id=$TEST_COURSE_ID"
    echo -e "       ${BLUE}Status:${NC} $http_code"
    echo ""

    if [ "$http_code" = "200" ]; then
        print_result "List library resources by course" "PASS" "HTTP 200 - Filtered resources retrieved successfully"
    else
        print_result "List library resources by course" "FAIL" "HTTP $http_code - $body"
    fi
else
    print_result "List library resources by course" "SKIP" "No student token or course ID available"
fi

# =============================================================================
# TEST 6: GET /api/library with faculty_id filter
# =============================================================================
echo -e "${YELLOW}========================================${NC}"
echo -e "${YELLOW}TEST 6: List Library Resources by Faculty${NC}"
echo -e "${YELLOW}========================================${NC}"
echo ""

if [ -n "$STUDENT_TOKEN" ] && [ -n "$TEST_FACULTY_ID" ]; then
    result=$(test_endpoint "GET" "/library?faculty_id=$TEST_FACULTY_ID" "" "Authorization: Bearer $STUDENT_TOKEN")
    http_code=$(echo "$result" | cut -d'|' -f1)
    body=$(echo "$result" | cut -d'|' -f2-)

    echo -e "       ${BLUE}Method:${NC} GET"
    echo -e "       ${BLUE}Endpoint:${NC} /library?faculty_id=$TEST_FACULTY_ID"
    echo -e "       ${BLUE}Status:${NC} $http_code"
    echo ""

    if [ "$http_code" = "200" ]; then
        print_result "List library resources by faculty" "PASS" "HTTP 200 - Filtered resources retrieved successfully"
    else
        print_result "List library resources by faculty" "FAIL" "HTTP $http_code - $body"
    fi
else
    print_result "List library resources by faculty" "SKIP" "No student token or faculty ID available"
fi

# =============================================================================
# TEST 7: GET /api/library with publication_year filter
# =============================================================================
echo -e "${YELLOW}========================================${NC}"
echo -e "${YELLOW}TEST 7: List Library Resources by Publication Year${NC}"
echo -e "${YELLOW}========================================${NC}"
echo ""

if [ -n "$STUDENT_TOKEN" ]; then
    result=$(test_endpoint "GET" "/library?publication_year=2023" "" "Authorization: Bearer $STUDENT_TOKEN")
    http_code=$(echo "$result" | cut -d'|' -f1)
    body=$(echo "$result" | cut -d'|' -f2-)

    echo -e "       ${BLUE}Method:${NC} GET"
    echo -e "       ${BLUE}Endpoint:${NC} /library?publication_year=2023"
    echo -e "       ${BLUE}Status:${NC} $http_code"
    echo ""

    if [ "$http_code" = "200" ]; then
        print_result "List library resources by publication year" "PASS" "HTTP 200 - Filtered resources retrieved successfully"
    else
        print_result "List library resources by publication year" "FAIL" "HTTP $http_code - $body"
    fi
else
    print_result "List library resources by publication year" "SKIP" "No student token available"
fi

# =============================================================================
# TEST 8: GET /api/library with tag filter
# =============================================================================
echo -e "${YELLOW}========================================${NC}"
echo -e "${YELLOW}TEST 8: List Library Resources by Tag${NC}"
echo -e "${YELLOW}========================================${NC}"
echo ""

if [ -n "$STUDENT_TOKEN" ]; then
    result=$(test_endpoint "GET" "/library?tag=programming" "" "Authorization: Bearer $STUDENT_TOKEN")
    http_code=$(echo "$result" | cut -d'|' -f1)
    body=$(echo "$result" | cut -d'|' -f2-)

    echo -e "       ${BLUE}Method:${NC} GET"
    echo -e "       ${BLUE}Endpoint:${NC} /library?tag=programming"
    echo -e "       ${BLUE}Status:${NC} $http_code"
    echo ""

    if [ "$http_code" = "200" ]; then
        print_result "List library resources by tag" "PASS" "HTTP 200 - Filtered resources retrieved successfully"
    else
        print_result "List library resources by tag" "FAIL" "HTTP $http_code - $body"
    fi
else
    print_result "List library resources by tag" "SKIP" "No student token available"
fi

# =============================================================================
# TEST 9: POST /api/library - Create new library resource
# =============================================================================
echo -e "${YELLOW}========================================${NC}"
echo -e "${YELLOW}TEST 9: Create New Library Resource${NC}"
echo -e "${YELLOW}========================================${NC}"
echo ""

if [ -n "$FACULTY_TOKEN" ]; then
    timestamp=$(date +%s)
    create_resource_data='{
        "title": "Test Book '$timestamp'",
        "description": "This is a test book created during automated testing.",
        "resource_type": "book",
        "access_level": "public",
        "author": "Test Author",
        "publisher": "Test Publisher",
        "publication_year": 2023,
        "isbn": "978-0-'$timestamp'",
        "tags": "programming,testing,library",
        "is_published": true,
        "file_url": "https://example.com/files/test-book.pdf",
        "file_type": "pdf",
        "file_size": 2048576
    }'

    result=$(test_endpoint "POST" "/library" "$create_resource_data" "Authorization: Bearer $FACULTY_TOKEN")
    http_code=$(echo "$result" | cut -d'|' -f1)
    body=$(echo "$result" | cut -d'|' -f2-)

    echo -e "       ${BLUE}Method:${NC} POST"
    echo -e "       ${BLUE}Endpoint:${NC} /library"
    echo -e "       ${BLUE}Status:${NC} $http_code"
    echo -e "       ${BLUE}Response:${NC} $(echo "$body" | head -c 300)..."
    echo ""

    if [ "$http_code" = "201" ] || [ "$http_code" = "200" ]; then
        print_result "Create new library resource" "PASS" "HTTP $http_code - Library resource created successfully"
        # Extract the created resource ID for later tests
        TEST_RESOURCE_ID=$(echo "$body" | grep -o '"id":[0-9]*' | head -1 | cut -d':' -f2)
    else
        print_result "Create new library resource" "FAIL" "HTTP $http_code - $body"
    fi
else
    print_result "Create new library resource" "SKIP" "No faculty token available"
fi

# =============================================================================
# TEST 10: GET /api/library/{id} - Get library resource by ID
# =============================================================================
echo -e "${YELLOW}========================================${NC}"
echo -e "${YELLOW}TEST 10: Get Library Resource by ID${NC}"
echo -e "${YELLOW}========================================${NC}"
echo ""

if [ -n "$STUDENT_TOKEN" ] && [ -n "$TEST_RESOURCE_ID" ]; then
    result=$(test_endpoint "GET" "/library/$TEST_RESOURCE_ID" "" "Authorization: Bearer $STUDENT_TOKEN")
    http_code=$(echo "$result" | cut -d'|' -f1)
    body=$(echo "$result" | cut -d'|' -f2-)

    echo -e "       ${BLUE}Method:${NC} GET"
    echo -e "       ${BLUE}Endpoint:${NC} /library/$TEST_RESOURCE_ID"
    echo -e "       ${BLUE}Status:${NC} $http_code"
    echo ""

    if [ "$http_code" = "200" ]; then
        print_result "Get library resource by ID" "PASS" "HTTP 200 - Library resource retrieved successfully"
    else
        print_result "Get library resource by ID" "FAIL" "HTTP $http_code - $body"
    fi
elif [ -n "$STUDENT_TOKEN" ]; then
    # Try to get any resource from the list
    result=$(test_endpoint "GET" "/library" "" "Authorization: Bearer $STUDENT_TOKEN")
    body=$(echo "$result" | cut -d'|' -f2-)
    TEST_RESOURCE_ID=$(echo "$body" | grep -o '"id":[0-9]*' | head -1 | cut -d':' -f2)

    if [ -n "$TEST_RESOURCE_ID" ]; then
        result=$(test_endpoint "GET" "/library/$TEST_RESOURCE_ID" "" "Authorization: Bearer $STUDENT_TOKEN")
        http_code=$(echo "$result" | cut -d'|' -f1)
        body=$(echo "$result" | cut -d'|' -f2-)

        echo -e "       ${BLUE}Method:${NC} GET"
        echo -e "       ${BLUE}Endpoint:${NC} /library/$TEST_RESOURCE_ID"
        echo -e "       ${BLUE}Status:${NC} $http_code"
        echo ""

        if [ "$http_code" = "200" ]; then
            print_result "Get library resource by ID" "PASS" "HTTP 200 - Library resource retrieved successfully"
        else
            print_result "Get library resource by ID" "FAIL" "HTTP $http_code - $body"
        fi
    else
        print_result "Get library resource by ID" "SKIP" "No library resource ID available"
    fi
else
    print_result "Get library resource by ID" "SKIP" "No student token available"
fi

# =============================================================================
# TEST 11: GET /api/library/{id} with invalid ID
# =============================================================================
echo -e "${YELLOW}========================================${NC}"
echo -e "${YELLOW}TEST 11: Get Library Resource with Invalid ID${NC}"
echo -e "${YELLOW}========================================${NC}"
echo ""

if [ -n "$STUDENT_TOKEN" ]; then
    result=$(test_endpoint "GET" "/library/9999999" "" "Authorization: Bearer $STUDENT_TOKEN")
    http_code=$(echo "$result" | cut -d'|' -f1)
    body=$(echo "$result" | cut -d'|' -f2-)

    echo -e "       ${BLUE}Method:${NC} GET"
    echo -e "       ${BLUE}Endpoint:${NC} /library/9999999"
    echo -e "       ${BLUE}Status:${NC} $http_code"
    echo ""

    if [ "$http_code" = "404" ]; then
        print_result "Get library resource with invalid ID" "PASS" "HTTP 404 - Not found as expected"
    else
        print_result "Get library resource with invalid ID" "FAIL" "Expected 404, got $http_code - $body"
    fi
else
    print_result "Get library resource with invalid ID" "SKIP" "No student token available"
fi

# =============================================================================
# TEST 12: PUT /api/library/{id} - Update library resource
# =============================================================================
echo -e "${YELLOW}========================================${NC}"
echo -e "${YELLOW}TEST 12: Update Library Resource${NC}"
echo -e "${YELLOW}========================================${NC}"
echo ""

if [ -n "$FACULTY_TOKEN" ] && [ -n "$TEST_RESOURCE_ID" ]; then
    update_resource_data='{
        "title": "Updated Test Book",
        "description": "This book has been updated during testing.",
        "author": "Updated Author"
    }'

    result=$(test_endpoint "PUT" "/library/$TEST_RESOURCE_ID" "$update_resource_data" "Authorization: Bearer $FACULTY_TOKEN")
    http_code=$(echo "$result" | cut -d'|' -f1)
    body=$(echo "$result" | cut -d'|' -f2-)

    echo -e "       ${BLUE}Method:${NC} PUT"
    echo -e "       ${BLUE}Endpoint:${NC} /library/$TEST_RESOURCE_ID"
    echo -e "       ${BLUE}Status:${NC} $http_code"
    echo ""

    if [ "$http_code" = "200" ]; then
        print_result "Update library resource" "PASS" "HTTP 200 - Library resource updated successfully"
    else
        print_result "Update library resource" "FAIL" "HTTP $http_code - $body"
    fi
else
    print_result "Update library resource" "SKIP" "No faculty token or resource ID available"
fi

# =============================================================================
# TEST 13: PUT /api/library/{id} by student (should fail)
# =============================================================================
echo -e "${YELLOW}========================================${NC}"
echo -e "${YELLOW}TEST 13: Update Library Resource by Student (should fail)${NC}"
echo -e "${YELLOW}========================================${NC}"
echo ""

if [ -n "$STUDENT_TOKEN" ] && [ -n "$TEST_RESOURCE_ID" ]; then
    update_resource_data='{
        "title": "Student Attempting to Update"
    }'

    result=$(test_endpoint "PUT" "/library/$TEST_RESOURCE_ID" "$update_resource_data" "Authorization: Bearer $STUDENT_TOKEN")
    http_code=$(echo "$result" | cut -d'|' -f1)
    body=$(echo "$result" | cut -d'|' -f2-)

    echo -e "       ${BLUE}Method:${NC} PUT"
    echo -e "       ${BLUE}Endpoint:${NC} /library/$TEST_RESOURCE_ID"
    echo -e "       ${BLUE}Status:${NC} $http_code"
    echo ""

    if [ "$http_code" = "403" ]; then
        print_result "Update library resource by student" "PASS" "HTTP 403 - Forbidden as expected"
    else
        print_result "Update library resource by student" "FAIL" "Expected 403, got $http_code - $body"
    fi
else
    print_result "Update library resource by student" "SKIP" "No student token or resource ID available"
fi

# =============================================================================
# TEST 14: POST /api/library/{id}/publish - Publish library resource
# =============================================================================
echo -e "${YELLOW}========================================${NC}"
echo -e "${YELLOW}TEST 14: Publish Library Resource${NC}"
echo -e "${YELLOW}========================================${NC}"
echo ""

if [ -n "$FACULTY_TOKEN" ] && [ -n "$TEST_RESOURCE_ID" ]; then
    result=$(test_endpoint "POST" "/library/$TEST_RESOURCE_ID/publish" "" "Authorization: Bearer $FACULTY_TOKEN")
    http_code=$(echo "$result" | cut -d'|' -f1)
    body=$(echo "$result" | cut -d'|' -f2-)

    echo -e "       ${BLUE}Method:${NC} POST"
    echo -e "       ${BLUE}Endpoint:${NC} /library/$TEST_RESOURCE_ID/publish"
    echo -e "       ${BLUE}Status:${NC} $http_code"
    echo ""

    if [ "$http_code" = "200" ]; then
        print_result "Publish library resource" "PASS" "HTTP 200 - Library resource published successfully"
    else
        print_result "Publish library resource" "FAIL" "HTTP $http_code - $body"
    fi
else
    print_result "Publish library resource" "SKIP" "No faculty token or resource ID available"
fi

# =============================================================================
# TEST 15: POST /api/library/{id}/unpublish - Unpublish library resource
# =============================================================================
echo -e "${YELLOW}========================================${NC}"
echo -e "${YELLOW}TEST 15: Unpublish Library Resource${NC}"
echo -e "${YELLOW}========================================${NC}"
echo ""

if [ -n "$FACULTY_TOKEN" ] && [ -n "$TEST_RESOURCE_ID" ]; then
    result=$(test_endpoint "POST" "/library/$TEST_RESOURCE_ID/unpublish" "" "Authorization: Bearer $FACULTY_TOKEN")
    http_code=$(echo "$result" | cut -d'|' -f1)
    body=$(echo "$result" | cut -d'|' -f2-)

    echo -e "       ${BLUE}Method:${NC} POST"
    echo -e "       ${BLUE}Endpoint:${NC} /library/$TEST_RESOURCE_ID/unpublish"
    echo -e "       ${BLUE}Status:${NC} $http_code"
    echo ""

    if [ "$http_code" = "200" ]; then
        print_result "Unpublish library resource" "PASS" "HTTP 200 - Library resource unpublished successfully"
    else
        print_result "Unpublish library resource" "FAIL" "HTTP $http_code - $body"
    fi
else
    print_result "Unpublish library resource" "SKIP" "No faculty token or resource ID available"
fi

# =============================================================================
# TEST 16: POST /api/library/{id}/download - Download library resource
# =============================================================================
echo -e "${YELLOW}========================================${NC}"
echo -e "${YELLOW}TEST 16: Download Library Resource${NC}"
echo -e "${YELLOW}========================================${NC}"
echo ""

if [ -n "$STUDENT_TOKEN" ] && [ -n "$TEST_RESOURCE_ID" ]; then
    result=$(test_endpoint "POST" "/library/$TEST_RESOURCE_ID/download" "" "Authorization: Bearer $STUDENT_TOKEN")
    http_code=$(echo "$result" | cut -d'|' -f1)
    body=$(echo "$result" | cut -d'|' -f2-)

    echo -e "       ${BLUE}Method:${NC} POST"
    echo -e "       ${BLUE}Endpoint:${NC} /library/$TEST_RESOURCE_ID/download"
    echo -e "       ${BLUE}Status:${NC} $http_code"
    echo ""

    if [ "$http_code" = "200" ]; then
        print_result "Download library resource" "PASS" "HTTP 200 - Download link generated successfully"
    else
        print_result "Download library resource" "FAIL" "HTTP $http_code - $body"
    fi
else
    print_result "Download library resource" "SKIP" "No student token or resource ID available"
fi

# =============================================================================
# TEST 17: DELETE /api/library/{id} - Delete library resource
# =============================================================================
echo -e "${YELLOW}========================================${NC}"
echo -e "${YELLOW}TEST 17: Delete Library Resource${NC}"
echo -e "${YELLOW}========================================${NC}"
echo ""

if [ -n "$FACULTY_TOKEN" ] && [ -n "$TEST_RESOURCE_ID" ]; then
    result=$(test_endpoint "DELETE" "/library/$TEST_RESOURCE_ID" "" "Authorization: Bearer $FACULTY_TOKEN")
    http_code=$(echo "$result" | cut -d'|' -f1)
    body=$(echo "$result" | cut -d'|' -f2-)

    echo -e "       ${BLUE}Method:${NC} DELETE"
    echo -e "       ${BLUE}Endpoint:${NC} /library/$TEST_RESOURCE_ID"
    echo -e "       ${BLUE}Status:${NC} $http_code"
    echo ""

    if [ "$http_code" = "204" ] || [ "$http_code" = "200" ]; then
        print_result "Delete library resource" "PASS" "HTTP $http_code - Library resource deleted successfully"
    else
        print_result "Delete library resource" "FAIL" "HTTP $http_code - $body"
    fi
else
    print_result "Delete library resource" "SKIP" "No faculty token or resource ID available"
fi

# =============================================================================
# TEST 18: GET /api/library without authentication
# =============================================================================
echo -e "${YELLOW}========================================${NC}"
echo -e "${YELLOW}TEST 18: List Library Resources Without Authentication${NC}"
echo -e "${YELLOW}========================================${NC}"
echo ""

result=$(test_endpoint "GET" "/library" "" "")
http_code=$(echo "$result" | cut -d'|' -f1)
body=$(echo "$result" | cut -d'|' -f2-)

echo -e "       ${BLUE}Method:${NC} GET"
echo -e "       ${BLUE}Endpoint:${NC} /library"
echo -e "       ${BLUE}Status:${NC} $http_code"
echo ""

if [ "$http_code" = "401" ]; then
    print_result "List library resources without authentication" "PASS" "HTTP 401 - Unauthorized as expected"
else
    print_result "List library resources without authentication" "FAIL" "Expected 401, got $http_code - $body"
fi

# =============================================================================
# TEST 19: POST /api/library without authentication
# =============================================================================
echo -e "${YELLOW}========================================${NC}"
echo -e "${YELLOW}TEST 19: Create Library Resource Without Authentication${NC}"
echo -e "${YELLOW}========================================${NC}"
echo ""

timestamp=$(date +%s)
create_resource_no_auth='{
    "title": "Unauthorized Resource '$timestamp'",
    "description": "This should not be created without authentication.",
    "resource_type": "book"
}'

result=$(test_endpoint "POST" "/library" "$create_resource_no_auth" "")
http_code=$(echo "$result" | cut -d'|' -f1)
body=$(echo "$result" | cut -d'|' -f2-)

echo -e "       ${BLUE}Method:${NC} POST"
echo -e "       ${BLUE}Endpoint:${NC} /library"
echo -e "       ${BLUE}Status:${NC} $http_code"
echo ""

if [ "$http_code" = "401" ]; then
    print_result "Create library resource without authentication" "PASS" "HTTP 401 - Unauthorized as expected"
else
    print_result "Create library resource without authentication" "FAIL" "Expected 401, got $http_code - $body"
fi

# =============================================================================
# TEST 20: POST /api/library - Create course-specific library resource
# =============================================================================
echo -e "${YELLOW}========================================${NC}"
echo -e "${YELLOW}TEST 20: Create Course-Specific Library Resource${NC}"
echo -e "${YELLOW}========================================${NC}"
echo ""

if [ -n "$FACULTY_TOKEN" ] && [ -n "$TEST_COURSE_ID" ]; then
    timestamp=$(date +%s)
    create_course_resource_data='{
        "title": "Course Resource '$timestamp'",
        "description": "This is a course-specific library resource.",
        "resource_type": "document",
        "access_level": "course",
        "course_id": '$TEST_COURSE_ID',
        "is_published": true,
        "file_url": "https://example.com/files/course-resource.pdf",
        "file_type": "pdf"
    }'

    result=$(test_endpoint "POST" "/library" "$create_course_resource_data" "Authorization: Bearer $FACULTY_TOKEN")
    http_code=$(echo "$result" | cut -d'|' -f1)
    body=$(echo "$result" | cut -d'|' -f2-)

    echo -e "       ${BLUE}Method:${NC} POST"
    echo -e "       ${BLUE}Endpoint:${NC} /library"
    echo -e "       ${BLUE}Status:${NC} $http_code"
    echo ""

    if [ "$http_code" = "201" ] || [ "$http_code" = "200" ]; then
        print_result "Create course-specific library resource" "PASS" "HTTP $http_code - Library resource created successfully"
        # Extract the created resource ID for later tests
        TEST_RESOURCE_ID=$(echo "$body" | grep -o '"id":[0-9]*' | head -1 | cut -d':' -f2)
    else
        print_result "Create course-specific library resource" "FAIL" "HTTP $http_code - $body"
    fi
else
    print_result "Create course-specific library resource" "SKIP" "No faculty token or course ID available"
fi

# =============================================================================
# TEST 21: POST /api/library - Create faculty-specific library resource
# =============================================================================
echo -e "${YELLOW}========================================${NC}"
echo -e "${YELLOW}TEST 21: Create Faculty-Specific Library Resource${NC}"
echo -e "${YELLOW}========================================${NC}"
echo ""

if [ -n "$FACULTY_TOKEN" ] && [ -n "$TEST_FACULTY_ID" ]; then
    timestamp=$(date +%s)
    create_faculty_resource_data='{
        "title": "Faculty Resource '$timestamp'",
        "description": "This is a faculty-specific library resource.",
        "resource_type": "article",
        "access_level": "faculty",
        "faculty_id": '$TEST_FACULTY_ID',
        "is_published": true,
        "author": "Test Faculty Author",
        "publisher": "Academic Press"
    }'

    result=$(test_endpoint "POST" "/library" "$create_faculty_resource_data" "Authorization: Bearer $FACULTY_TOKEN")
    http_code=$(echo "$result" | cut -d'|' -f1)
    body=$(echo "$result" | cut -d'|' -f2-)

    echo -e "       ${BLUE}Method:${NC} POST"
    echo -e "       ${BLUE}Endpoint:${NC} /library"
    echo -e "       ${BLUE}Status:${NC} $http_code"
    echo ""

    if [ "$http_code" = "201" ] || [ "$http_code" = "200" ]; then
        print_result "Create faculty-specific library resource" "PASS" "HTTP $http_code - Library resource created successfully"
    else
        print_result "Create faculty-specific library resource" "FAIL" "HTTP $http_code - $body"
    fi
else
    print_result "Create faculty-specific library resource" "SKIP" "No faculty token or faculty ID available"
fi

# =============================================================================
# TEST 22: POST /api/library - Create library resource with external link
# =============================================================================
echo -e "${YELLOW}========================================${NC}"
echo -e "${YELLOW}TEST 22: Create Library Resource with External Link${NC}"
echo -e "${YELLOW}========================================${NC}"
echo ""

if [ -n "$FACULTY_TOKEN" ]; then
    timestamp=$(date +%s)
    create_link_resource_data='{
        "title": "External Link Resource '$timestamp'",
        "description": "This is a library resource with an external link.",
        "resource_type": "link",
        "access_level": "public",
        "is_published": true,
        "external_link": "https://example.com/external-resource",
        "author": "External Author"
    }'

    result=$(test_endpoint "POST" "/library" "$create_link_resource_data" "Authorization: Bearer $FACULTY_TOKEN")
    http_code=$(echo "$result" | cut -d'|' -f1)
    body=$(echo "$result" | cut -d'|' -f2-)

    echo -e "       ${BLUE}Method:${NC} POST"
    echo -e "       ${BLUE}Endpoint:${NC} /library"
    echo -e "       ${BLUE}Status:${NC} $http_code"
    echo ""

    if [ "$http_code" = "201" ] || [ "$http_code" = "200" ]; then
        print_result "Create library resource with external link" "PASS" "HTTP $http_code - Library resource created successfully"
    else
        print_result "Create library resource with external link" "FAIL" "HTTP $http_code - $body"
    fi
else
    print_result "Create library resource with external link" "SKIP" "No faculty token available"
fi

# =============================================================================
# TEST 23: GET /api/library/{id} without authentication
# =============================================================================
echo -e "${YELLOW}========================================${NC}"
echo -e "${YELLOW}TEST 23: Get Library Resource Without Authentication${NC}"
echo -e "${YELLOW}========================================${NC}"
echo ""

if [ -n "$TEST_RESOURCE_ID" ]; then
    result=$(test_endpoint "GET" "/library/$TEST_RESOURCE_ID" "" "")
    http_code=$(echo "$result" | cut -d'|' -f1)
    body=$(echo "$result" | cut -d'|' -f2-)

    echo -e "       ${BLUE}Method:${NC} GET"
    echo -e "       ${BLUE}Endpoint:${NC} /library/$TEST_RESOURCE_ID"
    echo -e "       ${BLUE}Status:${NC} $http_code"
    echo ""

    if [ "$http_code" = "401" ]; then
        print_result "Get library resource without authentication" "PASS" "HTTP 401 - Unauthorized as expected"
    else
        print_result "Get library resource without authentication" "FAIL" "Expected 401, got $http_code - $body"
    fi
else
    print_result "Get library resource without authentication" "SKIP" "No library resource ID available"
fi

# =============================================================================
# TEST 24: PUT /api/library/{id} without authentication
# =============================================================================
echo -e "${YELLOW}========================================${NC}"
echo -e "${YELLOW}TEST 24: Update Library Resource Without Authentication${NC}"
echo -e "${YELLOW}========================================${NC}"
echo ""

if [ -n "$TEST_RESOURCE_ID" ]; then
    update_resource_no_auth='{
        "title": "Unauthorized Update"
    }'

    result=$(test_endpoint "PUT" "/library/$TEST_RESOURCE_ID" "$update_resource_no_auth" "")
    http_code=$(echo "$result" | cut -d'|' -f1)
    body=$(echo "$result" | cut -d'|' -f2-)

    echo -e "       ${BLUE}Method:${NC} PUT"
    echo -e "       ${BLUE}Endpoint:${NC} /library/$TEST_RESOURCE_ID"
    echo -e "       ${BLUE}Status:${NC} $http_code"
    echo ""

    if [ "$http_code" = "401" ]; then
        print_result "Update library resource without authentication" "PASS" "HTTP 401 - Unauthorized as expected"
    else
        print_result "Update library resource without authentication" "FAIL" "Expected 401, got $http_code - $body"
    fi
else
    print_result "Update library resource without authentication" "SKIP" "No library resource ID available"
fi

# =============================================================================
# TEST 25: DELETE /api/library/{id} without authentication
# =============================================================================
echo -e "${YELLOW}========================================${NC}"
echo -e "${YELLOW}TEST 25: Delete Library Resource Without Authentication${NC}"
echo -e "${YELLOW}========================================${NC}"
echo ""

if [ -n "$TEST_RESOURCE_ID" ]; then
    result=$(test_endpoint "DELETE" "/library/$TEST_RESOURCE_ID" "" "")
    http_code=$(echo "$result" | cut -d'|' -f1)
    body=$(echo "$result" | cut -d'|' -f2-)

    echo -e "       ${BLUE}Method:${NC} DELETE"
    echo -e "       ${BLUE}Endpoint:${NC} /library/$TEST_RESOURCE_ID"
    echo -e "       ${BLUE}Status:${NC} $http_code"
    echo ""

    if [ "$http_code" = "401" ]; then
        print_result "Delete library resource without authentication" "PASS" "HTTP 401 - Unauthorized as expected"
    else
        print_result "Delete library resource without authentication" "FAIL" "Expected 401, got $http_code - $body"
    fi
else
    print_result "Delete library resource without authentication" "SKIP" "No library resource ID available"
fi

# =============================================================================
# TEST 26: POST /api/library/{id}/publish by student (should fail)
# =============================================================================
echo -e "${YELLOW}========================================${NC}"
echo -e "${YELLOW}TEST 26: Publish Library Resource by Student (should fail)${NC}"
echo -e "${YELLOW}========================================${NC}"
echo ""

if [ -n "$STUDENT_TOKEN" ] && [ -n "$TEST_RESOURCE_ID" ]; then
    result=$(test_endpoint "POST" "/library/$TEST_RESOURCE_ID/publish" "" "Authorization: Bearer $STUDENT_TOKEN")
    http_code=$(echo "$result" | cut -d'|' -f1)
    body=$(echo "$result" | cut -d'|' -f2-)

    echo -e "       ${BLUE}Method:${NC} POST"
    echo -e "       ${BLUE}Endpoint:${NC} /library/$TEST_RESOURCE_ID/publish"
    echo -e "       ${BLUE}Status:${NC} $http_code"
    echo ""

    if [ "$http_code" = "403" ]; then
        print_result "Publish library resource by student" "PASS" "HTTP 403 - Forbidden as expected"
    else
        print_result "Publish library resource by student" "FAIL" "Expected 403, got $http_code - $body"
    fi
else
    print_result "Publish library resource by student" "SKIP" "No student token or resource ID available"
fi

# =============================================================================
# TEST 27: POST /api/library/{id}/unpublish by student (should fail)
# =============================================================================
echo -e "${YELLOW}========================================${NC}"
echo -e "${YELLOW}TEST 27: Unpublish Library Resource by Student (should fail)${NC}"
echo -e "${YELLOW}========================================${NC}"
echo ""

if [ -n "$STUDENT_TOKEN" ] && [ -n "$TEST_RESOURCE_ID" ]; then
    result=$(test_endpoint "POST" "/library/$TEST_RESOURCE_ID/unpublish" "" "Authorization: Bearer $STUDENT_TOKEN")
    http_code=$(echo "$result" | cut -d'|' -f1)
    body=$(echo "$result" | cut -d'|' -f2-)

    echo -e "       ${BLUE}Method:${NC} POST"
    echo -e "       ${BLUE}Endpoint:${NC} /library/$TEST_RESOURCE_ID/unpublish"
    echo -e "       ${BLUE}Status:${NC} $http_code"
    echo ""

    if [ "$http_code" = "403" ]; then
        print_result "Unpublish library resource by student" "PASS" "HTTP 403 - Forbidden as expected"
    else
        print_result "Unpublish library resource by student" "FAIL" "Expected 403, got $http_code - $body"
    fi
else
    print_result "Unpublish library resource by student" "SKIP" "No student token or resource ID available"
fi

# =============================================================================
# TEST 28: DELETE /api/library/{id} by student (should fail)
# =============================================================================
echo -e "${YELLOW}========================================${NC}"
echo -e "${YELLOW}TEST 28: Delete Library Resource by Student (should fail)${NC}"
echo -e "${YELLOW}========================================${NC}"
echo ""

if [ -n "$STUDENT_TOKEN" ] && [ -n "$TEST_RESOURCE_ID" ]; then
    result=$(test_endpoint "DELETE" "/library/$TEST_RESOURCE_ID" "" "Authorization: Bearer $STUDENT_TOKEN")
    http_code=$(echo "$result" | cut -d'|' -f1)
    body=$(echo "$result" | cut -d'|' -f2-)

    echo -e "       ${BLUE}Method:${NC} DELETE"
    echo -e "       ${BLUE}Endpoint:${NC} /library/$TEST_RESOURCE_ID"
    echo -e "       ${BLUE}Status:${NC} $http_code"
    echo ""

    if [ "$http_code" = "403" ]; then
        print_result "Delete library resource by student" "PASS" "HTTP 403 - Forbidden as expected"
    else
        print_result "Delete library resource by student" "FAIL" "Expected 403, got $http_code - $body"
    fi
else
    print_result "Delete library resource by student" "SKIP" "No student token or resource ID available"
fi

# =============================================================================
# TEST 29: POST /api/library - Create library resource with DOI
# =============================================================================
echo -e "${YELLOW}========================================${NC}"
echo -e "${YELLOW}TEST 29: Create Library Resource with DOI${NC}"
echo -e "${YELLOW}========================================${NC}"
echo ""

if [ -n "$FACULTY_TOKEN" ]; then
    timestamp=$(date +%s)
    create_doi_resource_data='{
        "title": "DOI Resource '$timestamp'",
        "description": "This is a library resource with a DOI.",
        "resource_type": "article",
        "access_level": "public",
        "is_published": true,
        "author": "Research Author",
        "publisher": "Academic Journal",
        "publication_year": 2024,
        "doi": "10.1000/xyz'$timestamp'"
    }'

    result=$(test_endpoint "POST" "/library" "$create_doi_resource_data" "Authorization: Bearer $FACULTY_TOKEN")
    http_code=$(echo "$result" | cut -d'|' -f1)
    body=$(echo "$result" | cut -d'|' -f2-)

    echo -e "       ${BLUE}Method:${NC} POST"
    echo -e "       ${BLUE}Endpoint:${NC} /library"
    echo -e "       ${BLUE}Status:${NC} $http_code"
    echo ""

    if [ "$http_code" = "201" ] || [ "$http_code" = "200" ]; then
        print_result "Create library resource with DOI" "PASS" "HTTP $http_code - Library resource created successfully"
    else
        print_result "Create library resource with DOI" "FAIL" "HTTP $http_code - $body"
    fi
else
    print_result "Create library resource with DOI" "SKIP" "No faculty token available"
fi

# =============================================================================
# TEST 30: GET /api/library - List library resources with multiple filters
# =============================================================================
echo -e "${YELLOW}========================================${NC}"
echo -e "${YELLOW}TEST 30: List Library Resources with Multiple Filters${NC}"
echo -e "${YELLOW}========================================${NC}"
echo ""

if [ -n "$STUDENT_TOKEN" ]; then
    result=$(test_endpoint "GET" "/library?resource_type=book&access_level=public&publication_year=2023" "" "Authorization: Bearer $STUDENT_TOKEN")
    http_code=$(echo "$result" | cut -d'|' -f1)
    body=$(echo "$result" | cut -d'|' -f2-)

    echo -e "       ${BLUE}Method:${NC} GET"
    echo -e "       ${BLUE}Endpoint:${NC} /library?resource_type=book&access_level=public&publication_year=2023"
    echo -e "       ${BLUE}Status:${NC} $http_code"
    echo ""

    if [ "$http_code" = "200" ]; then
        print_result "List library resources with multiple filters" "PASS" "HTTP 200 - Filtered resources retrieved successfully"
    else
        print_result "List library resources with multiple filters" "FAIL" "HTTP $http_code - $body"
    fi
else
    print_result "List library resources with multiple filters" "SKIP" "No student token available"
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