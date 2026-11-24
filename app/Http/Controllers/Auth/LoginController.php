<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = '/';

    /**
     * Custom generation logic.
     */
    protected function redirectTo()
    {
        return '/dashboard';
    }

    /**
     * An ajax login (public/js/ajax-form.js) gets the page to open next instead of
     * the trait's empty 204; a normal login keeps the trait's redirect.
     */
    protected function authenticated(Request $request, $user): ?JsonResponse
    {
        return $request->expectsJson()
            ? response()->json(['redirect' => redirect()->intended($this->redirectPath())->getTargetUrl()])
            : null;
    }
}
