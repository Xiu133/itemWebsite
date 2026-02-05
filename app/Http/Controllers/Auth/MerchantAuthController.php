<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\Auth\AuthService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class MerchantAuthController extends Controller
{
    protected $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    public function showLoginForm()
    {
        return Inertia::render('Auth/MerchantLogin');
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $credentials = $request->only('email', 'password');
        $credentials['remember'] = $request->boolean('remember');

        if (!$this->authService->attemptLogin($credentials, 'merchant')) {
            return back()->withErrors([
                'email' => '帳號或密碼錯誤，或此帳號不是商家帳號',
            ]);
        }

        $request->session()->regenerate();

        // 商家登入後跳轉到首頁
        return Inertia::location('/');
    }

    public function showRegisterForm()
    {
        return Inertia::render('Auth/MerchantRegister');
    }

    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $data = $request->only(['name', 'email', 'password', 'password_confirmation']);

        $user = $this->authService->registerMerchant($data);

        Auth::login($user);

        // 商家註冊後跳轉到首頁
        return Inertia::location('/');
    }

    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
