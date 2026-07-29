<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Database\QueryException;
use Illuminate\Validation\ValidationException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->api(prepend: [
            \Illuminate\Routing\Middleware\ThrottleRequests::class.':api',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*'),
        );

        $exceptions->serializeUsing(function (\Throwable $e) {
            $isProduction = app()->environment('production');

            if ($e instanceof ValidationException) {
                $first = collect($e->errors())->first();
                return [
                    'error' => is_array($first) ? reset($first) : $first,
                ];
            }

            if ($e instanceof QueryException) {
                return [
                    'error' => 'Error al consultar la base de datos. Intenta de nuevo.',
                ];
            }

            $message = $e->getMessage();
            if ($isProduction) {
                if (str_contains($message, 'SQLSTATE') ||
                    str_contains($message, 'connection') ||
                    str_contains($message, 'database') ||
                    str_contains($message, 'table') ||
                    str_contains($message, 'column') ||
                    str_contains($message, 'driver') ||
                    str_contains($message, 'Class "') ||
                    preg_match('/^[A-Z_]+Exception/', class_basename($e))) {
                    return [
                        'error' => 'Ocurrió un error inesperado. Intenta de nuevo.',
                    ];
                }
                return [
                    'error' => $message ?: 'Ocurrió un error inesperado. Intenta de nuevo.',
                ];
            }

            return [
                'error' => $message,
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ];
        });
    })->create();
