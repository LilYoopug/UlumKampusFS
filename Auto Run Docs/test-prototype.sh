#!/bin/bash

# Prototype Test Script for UlumKampusFS Backend API
# Tests the health endpoint and login endpoint

echo "====================================="
echo "UlumKampusFS API Prototype Test"
echo "====================================="
echo ""

# Color codes for output
GREEN='\033[0;32m'
RED='\033[0;31m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Server URL
BASE_URL="http://127.0.0.1:8000"

# Test 1: Health Endpoint
echo "Test 1: Health Endpoint"
echo "------------------------"
HEALTH_RESPONSE=$(curl -s -w "\n%{http_code}" "$BASE_URL/api/health")
HEALTH_CODE=$(echo "$HEALTH_RESPONSE" | tail -n 1)
HEALTH_BODY=$(echo "$HEALTH_RESPONSE" | head -n -1)

if [ "$HEALTH_CODE" = "200" ]; then
    echo -e "${GREEN}PASS${NC}: Health endpoint returned 200"
    echo "Response: $HEALTH_BODY"
else
    echo -e "${RED}FAIL${NC}: Health endpoint returned $HEALTH_CODE"
    echo "Response: $HEALTH_BODY"
fi
echo ""

# Test 2: Login Endpoint (with invalid credentials)
echo "Test 2: Login Endpoint - Invalid Credentials"
echo "----------------------------------------------"
LOGIN_RESPONSE=$(curl -s -w "\n%{http_code}" -X POST "$BASE_URL/api/auth/login" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{"email": "invalid@example.com", "password": "wrongpassword"}')
LOGIN_CODE=$(echo "$LOGIN_RESPONSE" | tail -n 1)
LOGIN_BODY=$(echo "$LOGIN_RESPONSE" | head -n -1)

if [ "$LOGIN_CODE" = "401" ] || [ "$LOGIN_CODE" = "422" ]; then
    echo -e "${GREEN}PASS${NC}: Login endpoint correctly rejected invalid credentials (code: $LOGIN_CODE)"
    echo "Response: $LOGIN_BODY"
else
    echo -e "${YELLOW}INFO${NC}: Login endpoint returned $LOGIN_CODE"
    echo "Response: $LOGIN_BODY"
fi
echo ""

# Test 3: Register Endpoint
echo "Test 3: Register Endpoint - Create New User"
echo "---------------------------------------------"
TIMESTAMP=$(date +%s)
REGISTER_RESPONSE=$(curl -s -w "\n%{http_code}" -X POST "$BASE_URL/api/auth/register" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d "{
    \"name\": \"Test User $TIMESTAMP\",
    \"email\": \"testuser$TIMESTAMP@example.com\",
    \"password\": \"password123\",
    \"password_confirmation\": \"password123\"
  }")
REGISTER_CODE=$(echo "$REGISTER_RESPONSE" | tail -n 1)
REGISTER_BODY=$(echo "$REGISTER_RESPONSE" | head -n -1)

if [ "$REGISTER_CODE" = "200" ] || [ "$REGISTER_CODE" = "201" ]; then
    echo -e "${GREEN}PASS${NC}: Register endpoint created new user (code: $REGISTER_CODE)"
    echo "Response: $REGISTER_BODY"
else
    echo -e "${YELLOW}INFO${NC}: Register endpoint returned $REGISTER_CODE"
    echo "Response: $REGISTER_BODY"
fi
echo ""

# Test 4: User Endpoint (without authentication - should fail)
echo "Test 4: User Endpoint - Without Authentication"
echo "-----------------------------------------------"
USER_RESPONSE=$(curl -s -w "\n%{http_code}" "$BASE_URL/api/user")
USER_CODE=$(echo "$USER_RESPONSE" | tail -n 1)
USER_BODY=$(echo "$USER_RESPONSE" | head -n -1)

if [ "$USER_CODE" = "401" ]; then
    echo -e "${GREEN}PASS${NC}: User endpoint correctly rejected unauthenticated request (code: $USER_CODE)"
else
    echo -e "${YELLOW}INFO${NC}: User endpoint returned $USER_CODE"
    echo "Response: $USER_BODY"
fi
echo ""

echo "====================================="
echo "Test Summary"
echo "====================================="
echo "All prototype tests completed."
echo ""