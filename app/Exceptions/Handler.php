<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

class Handler extends ExceptionHandler
{
    protected $dontReport = [];

    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            //
        });
    }

    public function render($request, Throwable $e)
    {
        // Force JSON response for API clients
        if ($request->expectsJson()) {

            $status = $e instanceof HttpExceptionInterface
                ? $e->getStatusCode()
                : 500;

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'type' => class_basename($e),
                'errors' => null,
                'trace' => config('app.debug') ? $e->getTrace() : null,
            ], $status);
        }

        return parent::render($request, $e);
    }
}
