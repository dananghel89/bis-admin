<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class PlanController extends Controller
{
    /**
     * Show the admin plans.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('admin.plans.plansIndex');
    }

    /**
     * Add plan from admin.
     *
     * @return \Illuminate\Http\Response
     */
    public function planAdd()
    {
        return view('admin.plans.planAdd');
    }

    /**
     * Show the admin plan.
     *
     * @return \Illuminate\Http\Response
     */
    public function planView($id)
    {
        return view('admin.plans.planView');
    }

    /**
     * Edit plan from admin.
     *
     * @return \Illuminate\Http\Response
     */
    public function planEdit($id)
    {
        return view('admin.plans.planEdit');
    }
}
