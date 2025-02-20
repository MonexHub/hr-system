<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Http\Middleware\ForceJsonResponse;



return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        apiPrefix: '/api', // This sets the prefix for API routes
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->api(prepend: [
            ForceJsonResponse::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
     try {
        $exceptions->shouldRenderJsonWhen(function (Request $request, \Throwable $e) {
            // Render JSON for API routes or when the request expects JSON
            return Str::startsWith($request->path(), 'api/') || $request->expectsJson();
        });
     } catch (\Throwable $th) {
            //throw $th;
     }
    })->create();
