<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class LicenseController extends Controller
{
    /**
     * Show the admin licenses.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('admin.licenses.licensesIndex');
    }

    /**
     * Add license from admin.
     *
     * @return \Illuminate\Http\Response
     */
    public function licenseAdd()
    {
        return view('admin.licenses.licenseAdd');
    }

    /**
     * Show the admin license.
     *
     * @return \Illuminate\Http\Response
     */
    public function licenseView($id)
    {
        return view('admin.licenses.licenseView');
    }

    /**
     * Edit license from admin.
     *
     * @return \Illuminate\Http\Response
     */
    public function licenseEdit($id)
    {
        return view('admin.licenses.licenseEdit');
    }
}
