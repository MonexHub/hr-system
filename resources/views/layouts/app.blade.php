<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }} - Career Portal</title>

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Additional Styles -->
    <style>
        /* Add any custom styles here */
    </style>
</head>
<body class="font-sans antialiased bg-gray-100">
<!-- Navigation -->
<nav class="bg-white shadow">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex">
                <!-- Logo -->
                <div class="flex-shrink-0 flex items-center">
                    <a href="{{ route('jobs.index') }}" class="text-xl font-bold text-gray-800">
                        {{ config('app.name', 'Laravel') }}
                    </a>
                </div>

                <!-- Navigation Links -->
                <div class="hidden sm:ml-6 sm:flex sm:items-center">
                    <a href="{{ route('jobs.index') }}"
                       class="px-3 py-2 text-sm font-medium text-gray-700 hover:text-gray-900">
                        Job Openings
                    </a>
                </div>
            </div>
        </div>
    </div>
</nav>

<!-- Page Content -->
<main>
    @yield('content')
</main>

<!-- Footer -->
<footer class="bg-white border-t border-gray-200">
    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
        <div class="text-center text-sm text-gray-500">
            © {{ date('Y') }} {{ config('app.name', 'Laravel') }}. All rights reserved.
        </div>
    </div>
</footer>

<!-- Scripts -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/alpinejs/3.13.3/cdn.js"></script>
</body>
</html>
