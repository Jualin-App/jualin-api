<?php

namespace App\Http\Responses;

class ApiResponse
{
    public static function success(string $message, $data = null, int $status = 200)
    {
        return response()->json([
            'success' => true,
            'status_code' => $status,
            'message' => $message,
            'data' => $data
        ], $status);
    }

    public static function error(string $message, $errors = null, int $status = 400)
    {
        return response()->json([
            'success' => false,
            'status_code' => $status,
            'message' => $message,
            'errors' => $errors
        ], $status);
    }
}
