<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

/**
 * Base API Controller
 * Provides common functionality for all API controllers
 * Matches CodeIgniter REST_Controller response format
 */
class ApiController extends Controller
{
    /**
     * Send success response
     *
     * @param mixed $data
     * @param string|null $message
     * @param int $statusCode
     * @return JsonResponse
     */
    protected function success($data = null, $message = null, $statusCode = 200): JsonResponse
    {
        $response = [
            'status' => true,
        ];

        if ($data !== null) {
            $response['data'] = $data;
        }

        if ($message !== null) {
            $response['msg'] = $message;
        }

        return response()->json($response, $statusCode);
    }

    /**
     * Send error response
     *
     * @param string $message
     * @param mixed $data
     * @param int $statusCode
     * @return JsonResponse
     */
    protected function error($message, $data = null, $statusCode = 200): JsonResponse
    {
        $response = [
            'status' => false,
            'msg' => $message,
        ];

        if ($data !== null) {
            $response['data'] = $data;
        }

        return response()->json($response, $statusCode);
    }

    /**
     * Get locale from route
     *
     * @return string
     */
    protected function getLocale(): string
    {
        return request()->route('locale') ?? app()->getLocale();
    }

    /**
     * Get customer ID from request
     *
     * @return int
     */
    protected function getCustomerId(): int
    {
        $customerId = request()->get('customerid') ?? request()->input('customerid', 0);
        
        if ($customerId === '' || $customerId === null) {
            return 0;
        }

        return (int) $customerId;
    }
}

