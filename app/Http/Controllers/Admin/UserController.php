<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\UserRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

/**
 * Staff manage every account (routes need `can:manageUsers`).
 */
class UserController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('search'));
        $role = $request->query('role');

        $users = User::query()
            ->withCount('properties')
            ->when($search !== '', fn ($query) => $query->where(fn ($q) => $q
                ->where('name', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%")))
            ->when(in_array($role, ['0', '1'], true), fn ($query) => $query->where('role', (int) $role))
            ->orderBy('role')
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        return view('admin.users.usersIndex', compact('users', 'search', 'role'));
    }

    public function userAdd(): View
    {
        return view('admin.users.userForm', ['user' => new User(['role' => User::EDITOR])]);
    }

    public function userAddSave(UserRequest $request): RedirectResponse|JsonResponse
    {
        $user = User::create($request->safe()->only(['name', 'email', 'role', 'password']));

        return $this->done($request, route('userView', $user), "{$user->roleLabel()} account for {$user->name} created.");
    }

    public function userView(User $user): View
    {
        $user->load(['properties' => fn ($query) => $query->orderBy('name')]);

        return view('admin.users.userView', compact('user'));
    }

    public function userEdit(User $user): View
    {
        return view('admin.users.userForm', compact('user'));
    }

    public function userEditSave(UserRequest $request, User $user): RedirectResponse|JsonResponse
    {
        $user->fill($request->safe()->only(['name', 'email', 'role']));

        if ($request->filled('password')) {
            $user->password = Hash::make($request->input('password'));
        }

        $user->save();

        return $this->done($request, route('userView', $user), 'Account updated.');
    }

    /**
     * Staff can't delete their own account or the last staff account. Deleting an
     * architect also removes their property assignments (database cascade).
     */
    public function userDelete(Request $request, User $user): RedirectResponse|JsonResponse
    {
        $error = match (true) {
            $user->is($request->user()) => 'You can\'t delete your own account.',
            $user->isStaff() && User::where('role', User::ADMIN)->count() === 1 => 'You can\'t delete the last staff account.',
            default => null,
        };

        if ($error) {
            return $request->expectsJson()
                ? response()->json(['message' => $error], 422)
                : back()->with('error', $error);
        }

        $user->delete();

        return $this->done($request, route('users'), "Account for {$user->name} deleted.");
    }
}
