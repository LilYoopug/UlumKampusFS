<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * Base API Controller with common response methods
 *
 * Provides standardized response methods for all API controllers,
 * ensuring consistent JSON responses across the application.
 */
class ApiController
{
    /**
     * Return a successful JSON response
     *
     * @param mixed $data The data to return in the response
     * @param string|null $message Optional success message
     * @param int $status HTTP status code (default: 200)
     * @return JsonResponse
     */
    protected function success(mixed $data = null, ?string $message = null, int $status = 200): JsonResponse
    {
        $response = [
            'success' => true,
        ];

        if ($message !== null) {
            $response['message'] = $message;
        }

        if ($data !== null) {
            $response['data'] = $data;
        }

        return response()->json($response, $status);
    }

    /**
     * Return an error JSON response
     *
     * @param string $message Error message
     * @param int $status HTTP status code (default: 400)
     * @param mixed|null $errors Optional detailed error information
     * @return JsonResponse
     */
    protected function error(string $message, int $status = 400, mixed $errors = null): JsonResponse
    {
        $response = [
            'success' => false,
            'message' => $message,
        ];

        if ($errors !== null) {
            $response['errors'] = $errors;
        }

        return response()->json($response, $status);
    }

    /**
     * Return a 404 Not Found response
     *
     * @param string|null $message Custom not found message
     * @return JsonResponse
     */
    protected function notFound(?string $message = null): JsonResponse
    {
        return $this->error(
            $message ?? 'Resource not found',
            404
        );
    }

    /**
     * Return a 201 Created response
     *
     * @param mixed $data The created resource data
     * @param string|null $message Optional success message
     * @return JsonResponse
     */
    protected function created(mixed $data, ?string $message = null): JsonResponse
    {
        return $this->success($data, $message ?? 'Resource created successfully', 201);
    }

    /**
     * Return a 204 No Content response
     *
     * Used for successful DELETE operations
     *
     * @return JsonResponse
     */
    protected function noContent(): JsonResponse
    {
        return response()->json(null, 204);
    }

    /**
     * Return a validation error response
     *
     * @param array $errors Validation errors array
     * @param string|null $message Optional error message
     * @return JsonResponse
     */
    protected function validationError(array $errors, ?string $message = null): JsonResponse
    {
        return $this->error(
            $message ?? 'Validation failed',
            422,
            $errors
        );
    }

    /**
     * Return an unauthorized response (401)
     *
     * @param string|null $message Custom unauthorized message
     * @return JsonResponse
     */
    protected function unauthorized(?string $message = null): JsonResponse
    {
        return $this->error(
            $message ?? 'Unauthorized access',
            401
        );
    }

    /**
     * Return a forbidden response (403)
     *
     * @param string|null $message Custom forbidden message
     * @return JsonResponse
     */
    protected function forbidden(?string $message = null): JsonResponse
    {
        return $this->error(
            $message ?? 'Access forbidden',
            403
        );
    }

    /**
     * Return a conflict response (409)
     *
     * Used for duplicate resources or conflicting states
     *
     * @param string|null $message Custom conflict message
     * @return JsonResponse
     */
    protected function conflict(?string $message = null): JsonResponse
    {
        return $this->error(
            $message ?? 'Resource conflict',
            409
        );
    }

    /**
     * Return a paginated response
     *
     * Wraps paginated data with consistent structure
     *
     * @param LengthAwarePaginator|ResourceCollection $paginator The paginated data
     * @param string|null $message Optional success message
     * @return JsonResponse
     */
    protected function paginated(LengthAwarePaginator|ResourceCollection $paginator, ?string $message = null): JsonResponse
    {
        if ($paginator instanceof ResourceCollection) {
            $paginator = $paginator->toResponse(request())->getData(true);
            $data = $paginator['data'];
            $meta = array_diff_key($paginator, array_flip(['data']));
        } else {
            $data = $paginator->items();
            $meta = [
                'current_page' => $paginator->currentPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
                'last_page' => $paginator->lastPage(),
                'from' => $paginator->firstItem(),
                'to' => $paginator->lastItem(),
            ];
        }

        $response = [
            'success' => true,
            'data' => $data,
            'pagination' => $meta,
        ];

        if ($message !== null) {
            $response['message'] = $message;
        }

        return response()->json($response);
    }

    /**
     * Return a resource response using JsonResource
     *
     * @param JsonResource $resource The resource to transform
     * @param int $status HTTP status code (default: 200)
     * @return JsonResponse
     */
    protected function resource(JsonResource $resource, int $status = 200): JsonResponse
    {
        return $resource->response()->setStatusCode($status);
    }

    /**
     * Return a collection response using ResourceCollection
     *
     * @param ResourceCollection $collection The resource collection to transform
     * @param int $status HTTP status code (default: 200)
     * @return JsonResponse
     */
    protected function collection(ResourceCollection $collection, int $status = 200): JsonResponse
    {
        return $collection->response()->setStatusCode($status);
    }
}