<?php

namespace App\Http\Controllers\Admin;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SearchUsersRequest;
use App\Http\Requests\Admin\UpdateUserRoleRequest;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class UserRoleController extends Controller
{
    public function index(SearchUsersRequest $request): View
    {
        $filters = $request->validated();

        $users = User::query()
            ->when($filters['q'] ?? null, function (Builder $query, string $term): void {
                $query->where(function (Builder $search) use ($term): void {
                    $search
                        ->where('name', 'like', '%'.$term.'%')
                        ->orWhere('email', 'like', '%'.$term.'%');
                });
            })
            ->when(
                $filters['role'] ?? null,
                fn (Builder $query, string $role) => $query->where('role', $role),
            )
            ->withCount(['posts', 'comments'])
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        $roleCounts = User::query()
            ->selectRaw('role, count(*) as aggregate')
            ->groupBy('role')
            ->pluck('aggregate', 'role');

        return view('admin.users.index', [
            'users' => $users,
            'roles' => UserRole::cases(),
            'filters' => $filters,
            'roleCounts' => $roleCounts,
            'hasFilters' => collect($filters)->contains(fn (mixed $value) => filled($value)),
        ]);
    }

    public function update(UpdateUserRoleRequest $request, User $user): RedirectResponse
    {
        $role = UserRole::from($request->validated('role'));

        DB::transaction(function () use ($role, $user): void {
            $administratorIds = User::query()
                ->where('role', UserRole::Administrator->value)
                ->lockForUpdate()
                ->pluck('id');

            $target = User::query()
                ->lockForUpdate()
                ->findOrFail($user->getKey());

            if (
                $target->role === UserRole::Administrator
                && $role !== UserRole::Administrator
                && $administratorIds->count() <= 1
            ) {
                throw ValidationException::withMessages([
                    'role' => 'The final administrator cannot be demoted.',
                ]);
            }

            $target->forceFill(['role' => $role])->save();
        });

        return redirect()
            ->route('admin.users.index')
            ->with('status', 'role-updated');
    }
}
