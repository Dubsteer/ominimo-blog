@extends('layouts.app')

@section('title', 'Profile | '.config('app.name'))

@section('content')
    <section class='page-shell page-section'>
        <div class='grid gap-10 lg:grid-cols-[0.65fr_1.35fr] lg:items-start'>
            <header class='lg:sticky lg:top-28'>
                <p class='eyebrow'>Account settings</p>
                <h1 class='page-title mt-5'>Your profile</h1>
                <p class='mt-5 max-w-md text-lg leading-8 text-ink-600'>Manage the details you use to take part in the community.</p>
                <div class='mt-6 inline-flex items-center gap-3 rounded-2xl border border-brand-100 bg-white px-4 py-3 shadow-card'>
                    <span class='grid size-10 place-items-center rounded-xl bg-brand-600 font-extrabold text-white' aria-hidden='true'>{{ \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr(auth()->user()->name, 0, 1)) }}</span>
                    <p class='text-sm font-semibold text-ink-600'>Your current role is {{ auth()->user()->role->label() }}.</p>
                </div>
            </header>

            <div>
                @if (session('status') === 'profile-updated')
                    <x-notice class='mb-6' role='status'>Profile updated.</x-notice>
                @elseif (session('status') === 'password-updated')
                    <x-notice class='mb-6' role='status'>Password updated.</x-notice>
                @endif

                <div class='space-y-6'>
                    <section class='surface-card p-6 sm:p-8'>
                        <div class='border-b border-brand-100 pb-5'>
                            <h2 class='text-2xl font-extrabold tracking-tight'>Profile information</h2>
                            <p class='mt-2 text-sm leading-6 text-ink-600'>Update the name and email address connected to your account.</p>
                        </div>
                        <form method='POST' action='{{ route('profile.update') }}' class='mt-6 space-y-5'>
                            @csrf
                            @method('PATCH')

                            <x-form-field name='name' label='Name' :value='auth()->user()->name' autocomplete='name' :required='true' />
                            <x-form-field name='email' label='Email address' type='email' :value='auth()->user()->email' autocomplete='email' :required='true' />

                            <x-button type='submit'>Save profile</x-button>
                        </form>
                    </section>

                    <section class='surface-card p-6 sm:p-8'>
                        <div class='border-b border-brand-100 pb-5'>
                            <h2 class='text-2xl font-extrabold tracking-tight'>Change password</h2>
                            <p class='mt-2 text-sm leading-6 text-ink-600'>Use a strong password you do not use elsewhere.</p>
                        </div>
                        <form method='POST' action='{{ route('password.update') }}' class='mt-6 space-y-5'>
                            @csrf
                            @method('PUT')

                            <x-form-field name='current_password' label='Current password' type='password' autocomplete='current-password' bag='updatePassword' :required='true' />
                            <x-form-field name='password' label='New password' type='password' autocomplete='new-password' bag='updatePassword' :required='true' />
                            <x-form-field name='password_confirmation' label='Confirm new password' type='password' autocomplete='new-password' bag='updatePassword' :required='true' />

                            <x-button type='submit'>Update password</x-button>
                        </form>
                    </section>

                    <section class='rounded-3xl border border-red-200 bg-red-50/60 p-6 sm:p-8'>
                        <h2 class='text-2xl font-extrabold tracking-tight text-red-900'>Delete account</h2>
                        <p class='mt-2 max-w-xl text-sm leading-6 text-red-900/75'>This permanently removes your account and cannot be reversed. Enter your password to confirm.</p>

                        <form method='POST' action='{{ route('profile.destroy') }}' class='mt-6 space-y-5'>
                            @csrf
                            @method('DELETE')

                            <x-form-field name='password' label='Current password' type='password' autocomplete='current-password' bag='deleteProfile' :required='true' />

                            <x-button type='submit' variant='danger'>Delete account</x-button>
                        </form>
                    </section>
                </div>
            </div>
        </div>
    </section>
@endsection
