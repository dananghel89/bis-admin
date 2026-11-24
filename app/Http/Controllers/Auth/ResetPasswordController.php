<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\ResetsPasswords;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ResetPasswordController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Password Reset Controller
    |--------------------------------------------------------------------------
    |
    | This controller is responsible for handling password reset requests
    | and uses a simple trait to include this behavior. You're free to
    | explore this trait and override any methods you wish to tweak.
    |
    */

    use ResetsPasswords;

    /**
     * Where to redirect users after resetting their password.
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
     * After a reset the user is logged in and goes to the dashboard, where the
     * message shows. An ajax post (public/js/ajax-form.js) gets that URL as JSON.
     */
    protected function sendResetResponse(Request $request, $response): RedirectResponse|JsonResponse
    {
        if ($request->wantsJson()) {
            $request->session()->flash('success', trans($response));

            return response()->json(['message' => trans($response), 'redirect' => url($this->redirectPath())]);
        }

        return redirect($this->redirectPath())->with('success', trans($response));
    }
}
