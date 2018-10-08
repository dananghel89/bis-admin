<?php

namespace App\Http\Controllers\Admin;

use App\User;
use App\Http\Requests\SettingsRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class SettingsController extends Controller
{
    /**
     * Edit settings from admin.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $name = Auth::user()->name;
        $email = Auth::user()->email;
        $role = Auth::user()->getRole();

        return view('admin.settingsEdit', compact('name', 'email', 'role'));
    }

    /**
     * Edit settings from admin.
     *
     * @return \Illuminate\Http\Response
     */
    public function settingsEdit(SettingsRequest $request)
    {
        $user = User::find( Auth::user()->id );

        $user->name = $request->get('name');
        $user->email = $request->get('email');

        $password = $request->get('password');

        if ( !is_null($password) ) {
            $user->password = bcrypt($password);
        }

        $user->save();

        return back()->with('success', 'Account settings updated successfully.');
    }
}
