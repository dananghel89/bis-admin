<?php

namespace App\Http\Middleware;

use Illuminate\Routing\Middleware\ThrottleRequests;

class ThrottleRequestsPerRoute extends ThrottleRequests
{
    /**
     * Laravel 5.8 keys guests by domain and IP only, so every throttled route would share
     * one counter. Adding the method and URI gives each route its own limit.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return string
     */
    protected function resolveRequestSignature($request)
    {
        $route = $request->route();

        return sha1($request->method() . '|' . ($route ? $route->uri() : '') . '|' . parent::resolveRequestSignature($request));
    }
}
