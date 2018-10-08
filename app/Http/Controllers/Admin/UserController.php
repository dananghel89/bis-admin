<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class UserController extends Controller
{
    /**
     * Show the admin users.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('admin.users.usersIndex');
    }

    /**
     * Add user from admin.
     *
     * @return \Illuminate\Http\Response
     */
    public function userAdd()
    {
        return view('admin.users.userAdd');
    }

    /**
     * Show the admin user.
     *
     * @return \Illuminate\Http\Response
     */
    public function userView($id)
    {
        return view('admin.users.userView');
    }

    /**
     * Edit user from admin.
     *
     * @return \Illuminate\Http\Response
     */
    public function userEdit($id)
    {
        return view('admin.users.userEdit');
    }
}
