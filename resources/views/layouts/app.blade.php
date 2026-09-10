<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>@yield('title', config('app.name'))</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet">

        @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
            @vite(['resources/css/app.css', 'resources/js/app.js'])
        @endif
    </head>
    <body class="min-h-screen bg-stone-50 font-sans text-slate-950 antialiased">
        <header class="border-b border-slate-200 bg-white">
            <nav class="mx-auto flex max-w-6xl flex-wrap items-center justify-between gap-6 px-5 py-4" aria-label="Primary navigation">
                <a href="{{ route('home') }}" class="text-lg font-bold tracking-tight text-slate-950">
                    Ominimo Claims Experience Hub
                </a>

                <div class="flex items-center gap-4 text-sm font-semibold">
                    <a href="{{ route('posts.index') }}" class="text-slate-700 hover:text-slate-950">Posts</a>
                    @auth
                        <a href="{{ route('dashboard') }}" class="text-slate-700 hover:text-slate-950">Dashboard</a>
                        <a href="{{ route('posts.create') }}" class="text-slate-700 hover:text-slate-950">New post</a>
                        <a href="{{ route('profile.edit') }}" class="text-slate-700 hover:text-slate-950">Profile</a>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="rounded-full bg-slate-950 px-4 py-2 text-white hover:bg-slate-800">
                                Log out
                            </button>
                        </form>
                    @else
                        <a href="{{ route('login') }}" class="text-slate-700 hover:text-slate-950">Log in</a>
                        <a href="{{ route('register') }}" class="rounded-full bg-amber-400 px-4 py-2 text-slate-950 hover:bg-amber-300">
                            Create account
                        </a>
                    @endauth
                </div>
            </nav>
        </header>

        <main>
            @yield('content')
        </main>
    </body>
</html>
