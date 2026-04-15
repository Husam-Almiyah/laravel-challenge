<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Instrument+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @livewireStyles
    </head>
    <body class="font-sans antialiased bg-slate-50 text-slate-900">
        <div class="min-h-screen">
            <!-- Navigation -->
            <nav class="sticky top-0 z-40 w-full border-b border-slate-200 bg-white/80 backdrop-blur-md">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div class="flex justify-between h-16">
                        <div class="flex">
                            <!-- Logo -->
                            <div class="shrink-0 flex items-center">
                                <a href="/" class="text-2xl font-bold bg-gradient-to-r from-indigo-600 to-violet-600 bg-clip-text text-transparent">
                                    Ajeer Boost
                                </a>
                            </div>

                            <!-- Navigation Links -->
                            <div class="hidden space-x-8 sm:-my-px sm:ml-10 sm:flex">
                                <a href="/" class="inline-flex items-center px-1 pt-1 border-b-2 border-transparent text-sm font-medium leading-5 text-slate-500 hover:text-slate-700 hover:border-slate-300 transition duration-150 ease-in-out">
                                    Home
                                </a>
                                @auth
                                    @if(auth()->user()->isAdmin())
                                        <a href="/admin" class="inline-flex items-center px-1 pt-1 border-b-2 border-transparent text-sm font-medium leading-5 text-slate-500 hover:text-slate-700 hover:border-slate-300 transition duration-150 ease-in-out">
                                            Admin Dashboard
                                        </a>
                                    @else
                                        <a href="/dashboard" class="inline-flex items-center px-1 pt-1 border-b-2 border-transparent text-sm font-medium leading-5 text-slate-500 hover:text-slate-700 hover:border-slate-300 transition duration-150 ease-in-out">
                                            Dashboard
                                        </a>
                                        <a href="/catalog" class="inline-flex items-center px-1 pt-1 border-b-2 border-transparent text-sm font-medium leading-5 text-slate-500 hover:text-slate-700 hover:border-slate-300 transition duration-150 ease-in-out">
                                            Services
                                        </a>
                                    @endif
                                @endauth
                            </div>
                        </div>

                        <div class="hidden sm:flex sm:items-center sm:ml-6 space-x-4">
                            @auth
                                <!-- Cart Counter -->
                                <livewire:client.cart-counter />
                                
                                <div class="relative ml-3">
                                    <span class="text-sm font-medium text-slate-700">{{ auth()->user()->name }}</span>
                                    <form method="POST" action="{{ route('logout') }}" class="inline ml-4">
                                        @csrf
                                        <button type="submit" class="text-sm text-slate-500 hover:text-red-600 transition duration-150">
                                            Logout
                                        </button>
                                    </form>
                                </div>
                            @else
                                <a href="{{ route('login') }}" class="text-sm font-medium text-slate-500 hover:text-slate-700">Login</a>
                                <a href="{{ route('register') }}" class="px-4 py-2 rounded-lg bg-indigo-600 text-white text-sm font-semibold hover:bg-indigo-700 transition duration-150">Get Trial</a>
                            @endauth
                        </div>
                    </div>
                </div>
            </nav>

            <!-- Page Content -->
            <main class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                {{ $slot }}
            </main>
        </div>

        @livewireScripts
    </body>
</html>
