<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Routing\Middleware\ThrottleRequests;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        api: [
            'v1' => __DIR__.'/../routes/api/v1.php',
        ],
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $apiMiddleware[] = ThrottleRequests::class;

        $middleware->api(append: $apiMiddleware);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->shouldRenderJsonWhen(function (Request $request, Throwable $exception) {
            if ($request->is('api/*')) {
                return true;
            }

            return $request->expectsJson();
        });
})->create();
