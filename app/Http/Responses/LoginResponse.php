<?php

namespace App\Http\Responses;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Laravel\Fortify\Contracts\LoginResponse as LoginResponseContract;

class LoginResponse implements LoginResponseContract
{
    /**
     * Create an HTTP response that represents the object.
     *
     * @param  Request  $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function toResponse($request)
    {
        // 使用 Inertia::location() 進行外部重定向到 Blade 頁面
        // 這會強制瀏覽器進行完整的頁面重載
        return $request->wantsJson()
            ? response()->json(['two_factor' => false])
            : Inertia::location(config('fortify.home', '/'));
    }
}
