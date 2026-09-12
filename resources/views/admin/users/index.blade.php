@extends('layouts.app')

@section('title', 'Role management | '.config('app.name'))

@section('content')
    <section class='page-shell page-section'>
        <a href='{{ route('admin.index') }}' class='inline-flex items-center gap-2 text-sm font-bold text-brand-700 hover:text-brand-900'>
            <span aria-hidden='true'>←</span> Back to moderation
        </a>

        <header class='mt-7 flex flex-wrap items-end justify-between gap-6'>
            <div>
                <p class='eyebrow'>Administrator access</p>
                <h1 class='page-title mt-5'>Role management</h1>
                <p class='mt-5 max-w-2xl text-lg leading-8 text-ink-600'>Assign community, moderation, and administration access while keeping the final administrator protected.</p>
            </div>
            <x-badge tone='warning'>Administrator only</x-badge>
        </header>

        @if (session('status') === 'role-updated')
            <x-notice class='mt-7' role='status'>User role updated.</x-notice>
        @endif

        @if ($errors->any())
            <x-notice tone='danger' class='mt-7' role='alert'>{{ $errors->first() }}</x-notice>
        @endif

        <dl class='mt-8 grid gap-4 sm:grid-cols-3'>
            @foreach ($roles as $role)
                <div class='surface-card p-5'>
                    <dt class='text-sm font-bold text-ink-600'>{{ $role->label() }}s</dt>
                    <dd class='mt-2 text-3xl font-extrabold tracking-tight'>{{ $roleCounts[$role->value] ?? 0 }}</dd>
                </div>
            @endforeach
        </dl>

        <form method='GET' action='{{ route('admin.users.index') }}' class='mt-8 rounded-3xl border border-brand-100 bg-brand-50/55 p-5 shadow-card' aria-label='Filter user accounts'>
            <div class='grid gap-4 md:grid-cols-[1fr_16rem]'>
                <div>
                    <label for='q' class='form-label'>Search users</label>
                    <input id='q' name='q' type='search' maxlength='100' value='{{ $filters['q'] ?? '' }}' placeholder='Name or email address' class='form-control'>
                </div>
                <div>
                    <label for='role' class='form-label'>Role</label>
                    <select id='role' name='role' class='form-control'>
                        <option value=''>All roles</option>
                        @foreach ($roles as $role)
                            <option value='{{ $role->value }}' @selected(($filters['role'] ?? null) === $role->value)>{{ $role->label() }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class='mt-5 flex flex-wrap gap-3'>
                <x-button type='submit'>Filter users</x-button>
                @if ($hasFilters)
                    <a href='{{ route('admin.users.index') }}' class='inline-flex min-h-12 items-center rounded-full px-4 text-sm font-bold text-brand-700 hover:bg-white'>Clear filters</a>
                @endif
            </div>
        </form>

        <div class='mt-6 overflow-hidden rounded-3xl border border-brand-100 bg-white shadow-card'>
            <div class='overflow-x-auto'>
                <table class='min-w-full border-collapse text-left'>
                    <thead class='bg-brand-950 text-sm text-white'>
                        <tr>
                            <th scope='col' class='px-5 py-4 font-bold'>Account</th>
                            <th scope='col' class='px-5 py-4 font-bold'>Contributions</th>
                            <th scope='col' class='px-5 py-4 font-bold'>Current role</th>
                            <th scope='col' class='px-5 py-4 font-bold'>Access</th>
                        </tr>
                    </thead>
                    <tbody class='divide-y divide-brand-100'>
                        @forelse ($users as $user)
                            <tr class='align-middle'>
                                <td class='min-w-64 px-5 py-5'>
                                    <div class='flex items-center gap-3'>
                                        <span class='grid size-10 shrink-0 place-items-center rounded-xl bg-brand-50 font-extrabold text-brand-700' aria-hidden='true'>{{ \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($user->name, 0, 1)) }}</span>
                                        <p>
                                            <strong class='block text-ink-950'>{{ $user->name }}</strong>
                                            <span class='text-sm text-ink-600'>{{ $user->email }}</span>
                                        </p>
                                    </div>
                                </td>
                                <td class='whitespace-nowrap px-5 py-5 text-sm text-ink-600'>{{ $user->posts_count }} posts<br>{{ $user->comments_count }} comments</td>
                                <td class='whitespace-nowrap px-5 py-5'>
                                    @php($roleTone = $user->isAdministrator() ? 'warning' : ($user->canModerateContent() ? 'brand' : 'neutral'))
                                    <x-badge :tone='$roleTone'>{{ $user->role->label() }}</x-badge>
                                </td>
                                <td class='min-w-64 px-5 py-4'>
                                    @if (auth()->user()->is($user))
                                        <p class='text-sm font-bold text-ink-600'>Your account <span class='block font-medium'>Role changes are disabled here.</span></p>
                                    @else
                                        <form method='POST' action='{{ route('admin.users.role.update', $user) }}' class='flex items-center gap-2'>
                                            @csrf
                                            @method('PATCH')
                                            <label for='user-role-{{ $user->id }}' class='sr-only'>Role for {{ $user->name }}</label>
                                            <select id='user-role-{{ $user->id }}' name='role' class='rounded-xl border-brand-200 py-2 text-sm focus:border-brand-500 focus:ring-brand-200'>
                                                @foreach ($roles as $role)
                                                    <option value='{{ $role->value }}' @selected($user->role === $role)>{{ $role->label() }}</option>
                                                @endforeach
                                            </select>
                                            <button type='submit' class='min-h-10 rounded-full px-3 text-sm font-bold text-brand-700 hover:bg-brand-50'>Save</button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan='4' class='px-5 py-12 text-center text-ink-600'>No users match these filters.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if ($users->hasPages())
                <div class='border-t border-brand-100 p-4'>{{ $users->links() }}</div>
            @endif
        </div>
    </section>
@endsection
