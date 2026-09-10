@extends('layouts.app')

@section('title', 'Profile | '.config('app.name'))

@section('content')
    <section class="mx-auto max-w-3xl px-5 py-16">
        <p class="text-sm font-bold uppercase tracking-[0.16em] text-amber-700">Account settings</p>
        <h1 class="mt-3 text-4xl font-bold tracking-tight">Your profile</h1>
        <p class="mt-3 text-slate-600">Your current role is {{ auth()->user()->role->label() }}.</p>

        @if (session('status') === 'profile-updated')
            <p class="mt-6 rounded-xl bg-emerald-50 px-4 py-3 font-medium text-emerald-800" role="status">Profile updated.</p>
        @elseif (session('status') === 'password-updated')
            <p class="mt-6 rounded-xl bg-emerald-50 px-4 py-3 font-medium text-emerald-800" role="status">Password updated.</p>
        @endif

        <div class="mt-10 space-y-6">
            <section class="rounded-2xl border border-slate-200 bg-white p-7 shadow-sm">
                <h2 class="text-2xl font-bold">Profile information</h2>
                <form method="POST" action="{{ route('profile.update') }}" class="mt-6 space-y-5">
                    @csrf
                    @method('PATCH')

                    <x-form-field name="name" label="Name" :value="auth()->user()->name" autocomplete="name" :required="true" />
                    <x-form-field name="email" label="Email address" type="email" :value="auth()->user()->email" autocomplete="email" :required="true" />

                    <button type="submit" class="rounded-xl bg-slate-950 px-5 py-3 font-semibold text-white hover:bg-slate-800">Save profile</button>
                </form>
            </section>

            <section class="rounded-2xl border border-slate-200 bg-white p-7 shadow-sm">
                <h2 class="text-2xl font-bold">Change password</h2>
                <form method="POST" action="{{ route('password.update') }}" class="mt-6 space-y-5">
                    @csrf
                    @method('PUT')

                    <x-form-field name="current_password" label="Current password" type="password" autocomplete="current-password" bag="updatePassword" :required="true" />
                    <x-form-field name="password" label="New password" type="password" autocomplete="new-password" bag="updatePassword" :required="true" />
                    <x-form-field name="password_confirmation" label="Confirm new password" type="password" autocomplete="new-password" bag="updatePassword" :required="true" />

                    <button type="submit" class="rounded-xl bg-slate-950 px-5 py-3 font-semibold text-white hover:bg-slate-800">Update password</button>
                </form>
            </section>

            <section class="rounded-2xl border border-red-200 bg-white p-7 shadow-sm">
                <h2 class="text-2xl font-bold text-red-800">Delete account</h2>
                <p class="mt-2 text-slate-600">This permanently removes your account. Enter your password to confirm.</p>

                <form method="POST" action="{{ route('profile.destroy') }}" class="mt-6 space-y-5">
                    @csrf
                    @method('DELETE')

                    <x-form-field name="password" label="Current password" type="password" autocomplete="current-password" bag="deleteProfile" :required="true" />

                    <button type="submit" class="rounded-xl bg-red-700 px-5 py-3 font-semibold text-white hover:bg-red-600">Delete account</button>
                </form>
            </section>
        </div>
    </section>
@endsection
