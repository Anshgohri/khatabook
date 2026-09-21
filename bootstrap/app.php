<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->trustProxies(at: '*');
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );

        $exceptions->render(function (\League\Flysystem\UnableToRetrieveMetadata|\League\Flysystem\FilesystemException $e, Request $request) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'image' => __('The uploaded file is no longer available on the server. Please select the file again.'),
                'bill_image' => __('The uploaded file is no longer available on the server. Please select the file again.'),
                'file' => __('The uploaded file is no longer available on the server. Please select the file again.'),
            ]);
        });

        $exceptions->render(function (\Symfony\Component\HttpKernel\Exception\HttpExceptionInterface $e, Request $request) {
            if ($request->header('X-Livewire') || $request->is('api/*') || $request->expectsJson()) {
                return null;
            }

            $status = $e->getStatusCode();

            return response()->view('errors.error', [
                'status' => $status,
                'exception' => $e,
            ], $status);
        });
    })->create();
