#!/usr/bin/env python3
"""
Parse api-routes.json to extract all API endpoints and organize them by resource.
Output will be saved as a JSON file with organized endpoints.
"""

import json
import re
from pathlib import Path
from collections import defaultdict

# Path to the api-routes.json file
API_ROUTES_FILE = Path(__file__).parent.parent / "api-routes.json"
OUTPUT_FILE = Path(__file__).parent / "organized-endpoints.json"

def extract_resource(uri):
    """Extract the resource name from a URI."""
    # Remove 'api/' prefix if present
    uri = uri.replace('api/', '')

    # Get the first segment
    parts = uri.split('/')
    if parts and parts[0]:
        return parts[0]
    return 'root'

def parse_method(method_string):
    """Parse the method string to extract individual methods."""
    if '|' in method_string:
        return method_string.split('|')
    return [method_string]

def extract_role_middleware(middleware):
    """Extract role requirements from middleware."""
    roles = []
    for m in middleware:
        if 'RoleMiddleware:' in m:
            # Extract role(s) after 'RoleMiddleware:'
            role_part = m.split('RoleMiddleware:')[1]
            roles.extend(role_part.split(','))
    return roles

def is_auth_required(middleware):
    """Check if authentication is required."""
    return any('Authenticate' in m for m in middleware)

def parse_routes(routes):
    """Parse the routes and organize by resource."""
    organized = defaultdict(lambda: defaultdict(list))

    for route in routes:
        uri = route.get('uri', '')
        method = route.get('method', '')
        action = route.get('action', '')
        middleware = route.get('middleware', [])

        # Skip non-API routes (docs, web routes, etc.)
        if not uri.startswith('api/') and uri != '/':
            continue

        # Skip root route
        if uri == '/':
            continue

        # Skip docs routes
        if uri.startswith('docs/'):
            continue

        # Skip web-only routes (those without 'api' in middleware)
        if 'api' not in middleware:
            continue

        resource = extract_resource(uri)
        methods = parse_method(method)
        roles = extract_role_middleware(middleware)
        auth_required = is_auth_required(middleware)

        endpoint_info = {
            'uri': uri,
            'methods': methods,
            'action': action,
            'auth_required': auth_required,
            'roles': roles,
            'middleware': middleware
        }

        # Group by HTTP method within each resource
        for m in methods:
            organized[resource][m].append(endpoint_info)

    return dict(organized)

def main():
    """Main function to parse and organize routes."""
    print(f"Reading routes from: {API_ROUTES_FILE}")

    with open(API_ROUTES_FILE, 'r') as f:
        routes = json.load(f)

    print(f"Found {len(routes)} total routes")

    organized = parse_routes(routes)

    # Count endpoints per resource
    total_endpoints = 0
    for resource, methods in organized.items():
        count = sum(len(endpoints) for endpoints in methods.values())
        total_endpoints += count
        print(f"  {resource}: {count} endpoint(s)")

    print(f"\nTotal API endpoints: {total_endpoints}")

    # Save organized output
    output = {
        'total_endpoints': total_endpoints,
        'resources': organized
    }

    with open(OUTPUT_FILE, 'w') as f:
        json.dump(output, f, indent=2)

    print(f"\nOrganized endpoints saved to: {OUTPUT_FILE}")

    # Also create a summary markdown file
    summary_file = Path(__file__).parent / "organized-endpoints-summary.md"
    with open(summary_file, 'w') as f:
        f.write("# API Endpoints Summary\n\n")
        f.write(f"Total API Endpoints: {total_endpoints}\n\n")
        f.write("## Resources\n\n")

        for resource in sorted(organized.keys()):
            f.write(f"### {resource}\n\n")

            for method in sorted(organized[resource].keys()):
                f.write(f"#### {method}\n\n")
                for endpoint in organized[resource][method]:
                    f.write(f"- `{endpoint['uri']}`\n")
                    f.write(f"  - Action: `{endpoint['action']}`\n")
                    if endpoint['roles']:
                        f.write(f"  - Roles: {', '.join(endpoint['roles'])}\n")
                    f.write("\n")

    print(f"Summary saved to: {summary_file}")

if __name__ == '__main__':
    main()