<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use League\Flysystem\FilesystemException;
use League\Flysystem\UnableToRetrieveMetadata;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

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

        $exceptions->render(function (UnableToRetrieveMetadata|FilesystemException $e, Request $request) {
            throw ValidationException::withMessages([
                'image' => __('The uploaded file is no longer available on the server. Please select the file again.'),
                'bill_image' => __('The uploaded file is no longer available on the server. Please select the file again.'),
                'file' => __('The uploaded file is no longer available on the server. Please select the file again.'),
            ]);
        });

        $exceptions->render(function (ValidationException $e, Request $request) {
            if ($request->expectsJson()) {
                return null;
            }
            if ($request->is('register') && $request->isMethod('post')) {
                return redirect()->route('register')
                    ->withInput($request->except($e->errorBag))
                    ->withErrors($e->errors(), $request->input('_error_bag', $e->errorBag));
            }
            if ($request->is('login') && $request->isMethod('post')) {
                return redirect()->route('login')
                    ->withInput($request->except($e->errorBag))
                    ->withErrors($e->errors(), $request->input('_error_bag', $e->errorBag));
            }

            return null; // Let the default handler take over
        });

        $exceptions->render(function (HttpExceptionInterface $e, Request $request) {
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
