<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class AlertController extends Controller
{
    /**
     * Show the admin alerts.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('admin.alerts.alertsIndex');
    }

    /**
     * Add alert from admin.
     *
     * @return \Illuminate\Http\Response
     */
    public function alertAdd()
    {
        return view('admin.alerts.alertAdd');
    }

    /**
     * Show the admin alert.
     *
     * @return \Illuminate\Http\Response
     */
    public function alertView($id)
    {
        return view('admin.alerts.alertView');
    }

    /**
     * Edit alert from admin.
     *
     * @return \Illuminate\Http\Response
     */
    public function alertEdit($id)
    {
        return view('admin.alerts.alertEdit');
    }
}
