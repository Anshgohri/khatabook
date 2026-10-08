<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Error {{ $status ?? 500 }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="antialiased min-h-screen bg-gray-100 flex items-center justify-center">
    <div class="max-w-xl w-full p-6 text-center">
        <h1 class="text-6xl font-bold text-gray-900 mb-4">{{ $status ?? 500 }}</h1>
        <p class="text-xl text-gray-600 mb-8">{{ $exception->getMessage() ?: 'Whoops, something went wrong on our servers.' }}</p>
        <a href="{{ url()->previous() !== url()->current() ? url()->previous() : route('home') }}" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">
            Go Back
        </a>
    </div>
</body>
</html>
