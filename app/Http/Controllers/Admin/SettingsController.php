<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\SettingsRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class SettingsController extends Controller
{
    /**
     * The logged-in user's own account.
     */
    public function index(): View
    {
        $name = Auth::user()->name;
        $email = Auth::user()->email;
        $role = Auth::user()->getRole();

        return view('admin.settingsEdit', compact('name', 'email', 'role'));
    }

    /**
     * Save the user's own name, email and (optionally) password.
     */
    public function settingsEdit(SettingsRequest $request): RedirectResponse|JsonResponse
    {
        $user = $request->user();

        $user->name = $request->input('name');
        $user->email = $request->input('email');

        if ($request->filled('password')) {
            $user->password = Hash::make($request->input('password'));
        }

        $user->save();

        return $this->done($request, route('settings'), 'Account settings updated successfully.');
    }
}
