<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

abstract class Controller
{
    /**
     * Finish a successful form post: flash the message and go to $url.
     *
     * Ajax posts (public/js/ajax-form.js) get {message, redirect} and the script
     * goes there; the flashed message shows on that page either way.
     */
    protected function done(Request $request, string $url, string $message): RedirectResponse|JsonResponse
    {
        $request->session()->flash('success', $message);

        return $request->expectsJson()
            ? response()->json(['message' => $message, 'redirect' => $url])
            : redirect($url);
    }
}
