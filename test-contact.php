<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$request = Illuminate\Http\Request::create('/contact', 'POST', [
    'name' => 'John Doe',
    'phone' => '1234567890',
    'email' => 'john@example.com',
    'inquiry_type' => 'General Inquiry',
    'message' => 'Hello there',
    '_token' => csrf_token(),
]);

$response = app()->handle($request);
echo "Status: " . $response->getStatusCode() . "\n";
if ($response->getStatusCode() == 302) {
    echo "Redirect: " . $response->headers->get('Location') . "\n";
    if (session()->has('success')) echo "Success: " . session('success') . "\n";
    if (session()->has('errors')) echo "Errors: " . session('errors')->first() . "\n";
} else {
    echo $response->getContent();
}
