<!DOCTYPE html>
<html lang='{{ str_replace('_', '-', app()->getLocale()) }}'>
    <head>
        <meta charset='utf-8'>
        <meta name='viewport' content='width=device-width, initial-scale=1'>
        <meta name='csrf-token' content='{{ csrf_token() }}'>
        <meta name='description' content='A privacy-safe community for sharing experiences and questions about the insurance claim journey.'>

        <title>@yield('title', config('app.name'))</title>

        <link rel='preconnect' href='https://fonts.bunny.net'>
        <link href='https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700,800' rel='stylesheet'>

        @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
            @vite(['resources/css/app.css', 'resources/js/app.js'])
        @endif
    </head>
    <body class='min-h-screen bg-canvas font-sans text-ink-950 antialiased'>
        @php
            $homeUrl = route('home');
            $createPostUrl = route('posts.create');
            $loginUrl = route('login');
            $registerUrl = route('register');
            $adminUrl = route('admin.index');
        @endphp
        <a href='#main-content' class='fixed left-4 top-4 z-50 -translate-y-24 rounded-full bg-ink-950 px-5 py-3 font-bold text-white transition focus:translate-y-0'>
            Skip to content
        </a>

        <div class='flex min-h-screen flex-col overflow-hidden'>
            <header class='relative z-30 border-b border-brand-100 bg-white/95 backdrop-blur'>
                <nav class='page-shell flex min-h-20 items-center justify-between gap-6' aria-label='Primary navigation'>
                    <x-brand :href='$homeUrl' />

                    <div class='hidden items-center gap-1 md:flex'>
                        <a href='{{ route('posts.index') }}' @class([
                            'rounded-full px-4 py-2.5 text-sm font-bold transition hover:bg-brand-50 hover:text-brand-700',
                            'bg-brand-50 text-brand-700' => request()->routeIs('posts.*'),
                            'text-ink-600' => ! request()->routeIs('posts.*'),
                        ])>Stories</a>

                        @auth
                            <a href='{{ route('dashboard') }}' @class([
                                'rounded-full px-4 py-2.5 text-sm font-bold transition hover:bg-brand-50 hover:text-brand-700',
                                'bg-brand-50 text-brand-700' => request()->routeIs('dashboard'),
                                'text-ink-600' => ! request()->routeIs('dashboard'),
                            ])>Dashboard</a>
                            <a href='{{ route('profile.edit') }}' @class([
                                'rounded-full px-4 py-2.5 text-sm font-bold transition hover:bg-brand-50 hover:text-brand-700',
                                'bg-brand-50 text-brand-700' => request()->routeIs('profile.*'),
                                'text-ink-600' => ! request()->routeIs('profile.*'),
                            ])>Profile</a>
                            @if (auth()->user()->canModerateContent())
                                <a href='{{ $adminUrl }}' @class([
                                    'rounded-full px-4 py-2.5 text-sm font-bold transition hover:bg-brand-50 hover:text-brand-700',
                                    'bg-brand-50 text-brand-700' => request()->routeIs('admin.*'),
                                    'text-ink-600' => ! request()->routeIs('admin.*'),
                                ])>Moderation</a>
                            @endif
                            <x-button :href='$createPostUrl' size='sm'>New post <span aria-hidden='true'>+</span></x-button>
                            <form method='POST' action='{{ route('logout') }}'>
                                @csrf
                                <x-button type='submit' variant='quiet' size='sm'>Log out</x-button>
                            </form>
                        @else
                            <x-button :href='$loginUrl' variant='quiet' size='sm'>Log in</x-button>
                            <x-button :href='$registerUrl' size='sm'>Join the hub</x-button>
                        @endauth
                    </div>

                    <details class='group relative md:hidden'>
                        <summary class='grid size-11 cursor-pointer list-none place-items-center rounded-full border border-brand-200 text-ink-950 transition hover:bg-brand-50' aria-label='Open navigation'>
                            <svg class='size-5 group-open:hidden' aria-hidden='true' viewBox='0 0 24 24' fill='none' stroke='currentColor' stroke-width='2' stroke-linecap='round'>
                                <path d='M4 7h16M4 12h16M4 17h16' />
                            </svg>
                            <svg class='hidden size-5 group-open:block' aria-hidden='true' viewBox='0 0 24 24' fill='none' stroke='currentColor' stroke-width='2' stroke-linecap='round'>
                                <path d='m6 6 12 12M18 6 6 18' />
                            </svg>
                        </summary>
                        <div class='absolute right-0 top-14 w-64 rounded-2xl border border-brand-100 bg-white p-3 shadow-soft'>
                            <a href='{{ route('posts.index') }}' class='block rounded-xl px-4 py-3 font-bold text-ink-800 hover:bg-brand-50'>Stories</a>
                            @auth
                                <a href='{{ route('dashboard') }}' class='block rounded-xl px-4 py-3 font-bold text-ink-800 hover:bg-brand-50'>Dashboard</a>
                                <a href='{{ route('profile.edit') }}' class='block rounded-xl px-4 py-3 font-bold text-ink-800 hover:bg-brand-50'>Profile</a>
                                @if (auth()->user()->canModerateContent())
                                    <a href='{{ $adminUrl }}' class='block rounded-xl px-4 py-3 font-bold text-brand-700 hover:bg-brand-50'>Moderation</a>
                                @endif
                                <a href='{{ route('posts.create') }}' class='block rounded-xl px-4 py-3 font-bold text-brand-700 hover:bg-brand-50'>Create a post</a>
                                <form method='POST' action='{{ route('logout') }}' class='mt-1 border-t border-brand-100 pt-1'>
                                    @csrf
                                    <button type='submit' class='w-full rounded-xl px-4 py-3 text-left font-bold text-ink-600 hover:bg-brand-50'>Log out</button>
                                </form>
                            @else
                                <a href='{{ route('login') }}' class='block rounded-xl px-4 py-3 font-bold text-ink-800 hover:bg-brand-50'>Log in</a>
                                <a href='{{ route('register') }}' class='mt-1 block rounded-xl bg-brand-600 px-4 py-3 text-center font-bold text-white'>Join the hub</a>
                            @endauth
                        </div>
                    </details>
                </nav>
            </header>

            <main id='main-content' class='flex-1'>
                @yield('content')
            </main>

            <footer class='border-t border-brand-100 bg-white'>
                <div class='page-shell grid gap-8 py-10 sm:grid-cols-[1fr_auto] sm:items-end'>
                    <div>
                        <x-brand :href='$homeUrl' compact />
                        <p class='mt-4 max-w-xl text-sm leading-6 text-ink-600'>
                            A community for privacy-safe discussion about claim journeys. We do not process claims, policies, or payments.
                        </p>
                    </div>
                    <div class='flex flex-wrap gap-x-5 gap-y-2 text-sm font-bold text-ink-600'>
                        <a href='{{ route('posts.index') }}' class='hover:text-brand-700'>Browse stories</a>
                        @guest
                            <a href='{{ route('register') }}' class='hover:text-brand-700'>Create account</a>
                        @endguest
                    </div>
                </div>
            </footer>
        </div>
    </body>
</html>
