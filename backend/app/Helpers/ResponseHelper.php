<?php

namespace App\Helpers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\Json\ResourceCollection;

class ResponseHelper
{
    /**
     * Return a success JSON response
     *
     * @param mixed $data
     * @param string|null $message
     * @param int $status
     * @return JsonResponse
     */
    public static function success($data = null, ?string $message = null, int $status = 200): JsonResponse
    {
        $response = ['success' => true];

        if ($data !== null) {
            $response['data'] = $data;
        }

        if ($message !== null) {
            $response['message'] = $message;
        }

        return response()->json($response, $status);
    }

    /**
     * Return an error JSON response
     *
     * @param string $message
     * @param array|null $errors
     * @param int $status
     * @return JsonResponse
     */
    public static function error(string $message, ?array $errors = null, int $status = 400): JsonResponse
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
     * Return a paginated success response
     *
     * @param ResourceCollection $collection
     * @param string|null $message
     * @return JsonResponse
     */
    public static function paginated(ResourceCollection $collection, ?string $message = null): JsonResponse
    {
        $response = [
            'success' => true,
            'data' => $collection->items(),
            'pagination' => [
                'total' => $collection->total(),
                'count' => $collection->count(),
                'per_page' => $collection->perPage(),
                'current_page' => $collection->currentPage(),
                'total_pages' => $collection->lastPage(),
                'has_more' => $collection->hasMorePages(),
            ],
        ];

        if ($message !== null) {
            $response['message'] = $message;
        }

        return response()->json($response);
    }

    /**
     * Return a created resource response
     *
     * @param JsonResource $resource
     * @param string|null $message
     * @return JsonResponse
     */
    public static function created(JsonResource $resource, ?string $message = null): JsonResponse
    {
        return self::success(
            $resource,
            $message ?? 'Resource created successfully',
            201
        );
    }

    /**
     * Return a no content response
     *
     * @return JsonResponse
     */
    public static function noContent(): JsonResponse
    {
        return response()->json(null, 204);
    }

    /**
     * Return an unauthorized response
     *
     * @param string $message
     * @return JsonResponse
     */
    public static function unauthorized(string $message = 'Unauthorized'): JsonResponse
    {
        return self::error($message, null, 401);
    }

    /**
     * Return a forbidden response
     *
     * @param string $message
     * @return JsonResponse
     */
    public static function forbidden(string $message = 'Forbidden'): JsonResponse
    {
        return self::error($message, null, 403);
    }

    /**
     * Return a not found response
     *
     * @param string $message
     * @return JsonResponse
     */
    public static function notFound(string $message = 'Resource not found'): JsonResponse
    {
        return self::error($message, null, 404);
    }

    /**
     * Return a validation error response
     *
     * @param array $errors
     * @param string $message
     * @return JsonResponse
     */
    public static function validationError(array $errors, string $message = 'Validation failed'): JsonResponse
    {
        return self::error($message, $errors, 422);
    }
}
