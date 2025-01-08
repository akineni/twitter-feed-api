<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Http\Response;

class ResponseMacroServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Macro for success responses
        Response::macro('success', function ($data = null, $message = 'Operation successful', $status = 200) {
            return response()->json([
                'status' => true,
                'message' => $message,
                'data' => $data,
            ], $status);
        });

        // Macro for error responses
        Response::macro('error', function ($message = 'An error occurred', $status = 400, $errors = null) {
            return response()->json([
                'status' => false,
                'message' => $message,
                'errors' => $errors,
            ], $status);
        });
    }
}
