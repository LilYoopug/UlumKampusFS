#!/bin/bash

# =============================================================================
# Authentication Endpoint Tests
# Tests all authentication-related API endpoints using curl
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

# Function to print test result
print_result() {
    local test_name="$1"
    local status="$2"
    local details="$3"

    TESTS_RUN=$((TESTS_RUN + 1))

    if [ "$status" = "PASS" ]; then
        echo -e "${GREEN}[PASS]${NC} $test_name"
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
# TEST 1: Health Check (Pre-flight)
# =============================================================================
echo -e "${YELLOW}========================================${NC}"
echo -e "${YELLOW}PRE-FLIGHT: Health Check${NC}"
echo -e "${YELLOW}========================================${NC}"
echo ""

health_response=$(curl -s "$BASE_URL/health" 2>/dev/null)
if echo "$health_response" | grep -q '"status":"ok"'; then
    print_result "Health Check" "PASS" "Server is operational"
else
    print_result "Health Check" "FAIL" "Server not responding correctly"
    exit 1
fi

# =============================================================================
# TEST 2: Register with valid data
# =============================================================================
echo -e "${YELLOW}========================================${NC}"
echo -e "${YELLOW}TEST: Register New User${NC}"
echo -e "${YELLOW}========================================${NC}"
echo ""

timestamp=$(date +%s)
register_data='{
    "name": "Test User '$timestamp'",
    "email": "testuser'$timestamp'@example.com",
    "password": "Password123!",
    "password_confirmation": "Password123!",
    "role": "student"
}'

result=$(test_endpoint "POST" "/register" "$register_data" "")
register_http_code=$(echo "$result" | cut -d'|' -f1)
register_body=$(echo "$result" | cut -d'|' -f2-)

echo -e "       ${BLUE}Method:${NC} POST"
echo -e "       ${BLUE}Endpoint:${NC} /register"
echo -e "       ${BLUE}Status:${NC} $register_http_code"
echo -e "       ${BLUE}Response:${NC} $register_body"
echo ""

if [ "$register_http_code" = "201" ] || [ "$register_http_code" = "200" ]; then
    print_result "Register new user" "PASS" "HTTP $register_http_code - User created successfully"
    # Extract access token if present
    ACCESS_TOKEN=$(echo "$register_body" | grep -o '"token":"[^"]*"' | cut -d'"' -f4)
    if [ -z "$ACCESS_TOKEN" ]; then
        ACCESS_TOKEN=$(echo "$register_body" | grep -o '"access_token":"[^"]*"' | cut -d'"' -f4)
    fi
    USER_EMAIL="testuser$timestamp@example.com"
    USER_PASSWORD="Password123!"
else
    print_result "Register new user" "FAIL" "HTTP $register_http_code - $register_body"
    ACCESS_TOKEN=""
    USER_EMAIL=""
    USER_PASSWORD=""
fi

# =============================================================================
# TEST 3: Register with missing fields
# =============================================================================
echo -e "${YELLOW}========================================${NC}"
echo -e "${YELLOW}TEST: Register with Missing Fields${NC}"
echo -e "${YELLOW}========================================${NC}"
echo ""

invalid_register_data='{"name": "Incomplete User", "email": "incomplete@example.com"}'

result=$(test_endpoint "POST" "/register" "$invalid_register_data" "")
register_invalid_http_code=$(echo "$result" | cut -d'|' -f1)
register_invalid_body=$(echo "$result" | cut -d'|' -f2-)

echo -e "       ${BLUE}Method:${NC} POST"
echo -e "       ${BLUE}Endpoint:${NC} /register"
echo -e "       ${BLUE}Status:${NC} $register_invalid_http_code"
echo -e "       ${BLUE}Response:${NC} $register_invalid_body"
echo ""

if [ "$register_invalid_http_code" = "422" ]; then
    print_result "Register with missing fields" "PASS" "HTTP 422 - Validation error as expected"
else
    print_result "Register with missing fields" "FAIL" "Expected 422, got $register_invalid_http_code"
fi

# =============================================================================
# TEST 4: Register with existing email
# =============================================================================
echo -e "${YELLOW}========================================${NC}"
echo -e "${YELLOW}TEST: Register with Existing Email${NC}"
echo -e "${YELLOW}========================================${NC}"
echo ""

result=$(test_endpoint "POST" "/register" "$register_data" "")
register_duplicate_http_code=$(echo "$result" | cut -d'|' -f1)
register_duplicate_body=$(echo "$result" | cut -d'|' -f2-)

echo -e "       ${BLUE}Method:${NC} POST"
echo -e "       ${BLUE}Endpoint:${NC} /register"
echo -e "       ${BLUE}Status:${NC} $register_duplicate_http_code"
echo -e "       ${BLUE}Response:${NC} $register_duplicate_body"
echo ""

if [ "$register_duplicate_http_code" = "422" ]; then
    print_result "Register with existing email" "PASS" "HTTP 422 - Duplicate email rejected"
else
    print_result "Register with existing email" "FAIL" "Expected 422, got $register_duplicate_http_code"
fi

# =============================================================================
# TEST 5: Login with valid credentials
# =============================================================================
echo -e "${YELLOW}========================================${NC}"
echo -e "${YELLOW}TEST: Login with Valid Credentials${NC}"
echo -e "${YELLOW}========================================${NC}"
echo ""

# Use the registered user credentials
login_data='{
    "email": "'$USER_EMAIL'",
    "password": "'$USER_PASSWORD'"
}'

result=$(test_endpoint "POST" "/login" "$login_data" "")
login_http_code=$(echo "$result" | cut -d'|' -f1)
login_body=$(echo "$result" | cut -d'|' -f2-)

echo -e "       ${BLUE}Method:${NC} POST"
echo -e "       ${BLUE}Endpoint:${NC} /login"
echo -e "       ${BLUE}Status:${NC} $login_http_code"
echo -e "       ${BLUE}Response:${NC} $login_body"
echo ""

if [ "$login_http_code" = "200" ]; then
    print_result "Login with valid credentials" "PASS" "HTTP 200 - Login successful"
    # Update access token from login response
    ACCESS_TOKEN=$(echo "$login_body" | grep -o '"token":"[^"]*"' | cut -d'"' -f4)
    if [ -z "$ACCESS_TOKEN" ]; then
        ACCESS_TOKEN=$(echo "$login_body" | grep -o '"access_token":"[^"]*"' | cut -d'"' -f4)
    fi
else
    print_result "Login with valid credentials" "FAIL" "HTTP $login_http_code - $login_body"
fi

# =============================================================================
# TEST 6: Login with invalid credentials
# =============================================================================
echo -e "${YELLOW}========================================${NC}"
echo -e "${YELLOW}TEST: Login with Invalid Credentials${NC}"
echo -e "${YELLOW}========================================${NC}"
echo ""

invalid_login_data='{
    "email": "'$USER_EMAIL'",
    "password": "WrongPassword123!"
}'

result=$(test_endpoint "POST" "/login" "$invalid_login_data" "")
login_invalid_http_code=$(echo "$result" | cut -d'|' -f1)
login_invalid_body=$(echo "$result" | cut -d'|' -f2-)

echo -e "       ${BLUE}Method:${NC} POST"
echo -e "       ${BLUE}Endpoint:${NC} /login"
echo -e "       ${BLUE}Status:${NC} $login_invalid_http_code"
echo -e "       ${BLUE}Response:${NC} $login_invalid_body"
echo ""

if [ "$login_invalid_http_code" = "401" ]; then
    print_result "Login with invalid credentials" "PASS" "HTTP 401 - Invalid credentials rejected"
elif [ "$login_invalid_http_code" = "422" ]; then
    print_result "Login with invalid credentials" "PASS" "HTTP 422 - Validation error"
else
    print_result "Login with invalid credentials" "FAIL" "Expected 401 or 422, got $login_invalid_http_code"
fi

# =============================================================================
# TEST 7: Login with non-existent user
# =============================================================================
echo -e "${YELLOW}========================================${NC}"
echo -e "${YELLOW}TEST: Login with Non-existent User${NC}"
echo -e "${YELLOW}========================================${NC}"
echo ""

nonexistent_login_data='{
    "email": "nonexistent@example.com",
    "password": "Password123!"
}'

result=$(test_endpoint "POST" "/login" "$nonexistent_login_data" "")
login_nonexist_http_code=$(echo "$result" | cut -d'|' -f1)
login_nonexist_body=$(echo "$result" | cut -d'|' -f2-)

echo -e "       ${BLUE}Method:${NC} POST"
echo -e "       ${BLUE}Endpoint:${NC} /login"
echo -e "       ${BLUE}Status:${NC} $login_nonexist_http_code"
echo -e "       ${BLUE}Response:${NC} $login_nonexist_body"
echo ""

if [ "$login_nonexist_http_code" = "401" ]; then
    print_result "Login with non-existent user" "PASS" "HTTP 401 - Non-existent user rejected"
else
    print_result "Login with non-existent user" "FAIL" "Expected 401, got $login_nonexist_http_code"
fi

# =============================================================================
# TEST 8: Logout with valid token
# =============================================================================
echo -e "${YELLOW}========================================${NC}"
echo -e "${YELLOW}TEST: Logout with Valid Token${NC}"
echo -e "${YELLOW}========================================${NC}"
echo ""

if [ -n "$ACCESS_TOKEN" ]; then
    logout_result=$(test_endpoint "POST" "/logout" "" "Authorization: Bearer $ACCESS_TOKEN")
    logout_http_code=$(echo "$logout_result" | cut -d'|' -f1)
    logout_body=$(echo "$logout_result" | cut -d'|' -f2-)

    echo -e "       ${BLUE}Method:${NC} POST"
    echo -e "       ${BLUE}Endpoint:${NC} /logout"
    echo -e "       ${BLUE}Status:${NC} $logout_http_code"
    echo -e "       ${BLUE}Response:${NC} $logout_body"
    echo ""

    if [ "$logout_http_code" = "200" ]; then
        print_result "Logout with valid token" "PASS" "HTTP 200 - Logout successful"
    else
        print_result "Logout with valid token" "FAIL" "HTTP $logout_http_code - $logout_body"
    fi
else
    print_result "Logout with valid token" "SKIP" "No access token available from previous tests"
    echo ""
fi

# =============================================================================
# TEST 9: Logout without token
# =============================================================================
echo -e "${YELLOW}========================================${NC}"
echo -e "${YELLOW}TEST: Logout without Token${NC}"
echo -e "${YELLOW}========================================${NC}"
echo ""

result=$(test_endpoint "POST" "/logout" "" "")
logout_no_token_http_code=$(echo "$result" | cut -d'|' -f1)
logout_no_token_body=$(echo "$result" | cut -d'|' -f2-)

echo -e "       ${BLUE}Method:${NC} POST"
echo -e "       ${BLUE}Endpoint:${NC} /logout"
echo -e "       ${BLUE}Status:${NC} $logout_no_token_http_code"
echo -e "       ${BLUE}Response:${NC} $logout_no_token_body"
echo ""

if [ "$logout_no_token_http_code" = "401" ]; then
    print_result "Logout without token" "PASS" "HTTP 401 - Unauthorized as expected"
else
    print_result "Logout without token" "FAIL" "Expected 401, got $logout_no_token_http_code"
fi

# =============================================================================
# TEST 10: Forgot Password with valid email
# =============================================================================
echo -e "${YELLOW}========================================${NC}"
echo -e "${YELLOW}TEST: Forgot Password${NC}"
echo -e "${YELLOW}========================================${NC}"
echo ""

forgot_password_data='{
    "email": "'$USER_EMAIL'"
}'

result=$(test_endpoint "POST" "/forgot-password" "$forgot_password_data" "")
forgot_password_http_code=$(echo "$result" | cut -d'|' -f1)
forgot_password_body=$(echo "$result" | cut -d'|' -f2-)

echo -e "       ${BLUE}Method:${NC} POST"
echo -e "       ${BLUE}Endpoint:${NC} /forgot-password"
echo -e "       ${BLUE}Status:${NC} $forgot_password_http_code"
echo -e "       ${BLUE}Response:${NC} $forgot_password_body"
echo ""

if [ "$forgot_password_http_code" = "200" ]; then
    print_result "Forgot password" "PASS" "HTTP 200 - Password reset initiated"
else
    print_result "Forgot password" "FAIL" "HTTP $forgot_password_http_code - $forgot_password_body"
fi

# =============================================================================
# TEST 11: Forgot Password with non-existent email
# =============================================================================
echo -e "${YELLOW}========================================${NC}"
echo -e "${YELLOW}TEST: Forgot Password with Non-existent Email${NC}"
echo -e "${YELLOW}========================================${NC}"
echo ""

forgot_password_nonexist_data='{
    "email": "nonexistent@example.com"
}'

result=$(test_endpoint "POST" "/forgot-password" "$forgot_password_nonexist_data" "")
forgot_password_nonexist_http_code=$(echo "$result" | cut -d'|' -f1)
forgot_password_nonexist_body=$(echo "$result" | cut -d'|' -f2-)

echo -e "       ${BLUE}Method:${NC} POST"
echo -e "       ${BLUE}Endpoint:${NC} /forgot-password"
echo -e "       ${BLUE}Status:${NC} $forgot_password_nonexist_http_code"
echo -e "       ${BLUE}Response:${NC} $forgot_password_nonexist_body"
echo ""

# Some implementations return 200 even for non-existent emails for security
if [ "$forgot_password_nonexist_http_code" = "200" ] || [ "$forgot_password_nonexist_http_code" = "422" ]; then
    print_result "Forgot password with non-existent email" "PASS" "HTTP $forgot_password_nonexist_http_code - Handled appropriately"
else
    print_result "Forgot password with non-existent email" "FAIL" "Unexpected status code: $forgot_password_nonexist_http_code"
fi

# =============================================================================
# TEST 12: Reset Password (requires token from email - this is a placeholder)
# =============================================================================
echo -e "${YELLOW}========================================${NC}"
echo -e "${YELLOW}TEST: Reset Password (Placeholder)${NC}"
echo -e "${YELLOW}========================================${NC}"
echo ""

reset_password_data='{
    "token": "placeholder_token",
    "email": "'$USER_EMAIL'",
    "password": "NewPassword123!",
    "password_confirmation": "NewPassword123!"
}'

result=$(test_endpoint "POST" "/reset-password" "$reset_password_data" "")
reset_password_http_code=$(echo "$result" | cut -d'|' -f1)
reset_password_body=$(echo "$result" | cut -d'|' -f2-)

echo -e "       ${BLUE}Method:${NC} POST"
echo -e "       ${BLUE}Endpoint:${NC} /reset-password"
echo -e "       ${BLUE}Status:${NC} $reset_password_http_code"
echo -e "       ${BLUE}Response:${NC} $reset_password_body"
echo ""

if [ "$reset_password_http_code" = "422" ] || [ "$reset_password_http_code" = "400" ]; then
    print_result "Reset password" "PASS" "HTTP $reset_password_http_code - Invalid token rejected (expected for placeholder)"
else
    print_result "Reset password" "FAIL" "Expected 422 or 400 for placeholder token, got $reset_password_http_code"
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