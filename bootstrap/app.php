<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->web(append: [
            \App\Http\Middleware\HandleInertiaRequests::class,
            \Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets::class,
        ]);

        // 使用自訂的 RedirectIfAuthenticated 以支援 Inertia 到 Blade 頁面的重定向
        $middleware->alias([
            'guest' => \App\Http\Middleware\RedirectIfAuthenticated::class,
        ]);

        // 排除綠界金流和物流回調路由的 CSRF 驗證
        // 注意：api 路由本身不需要 CSRF，但保留這裡以防萬一
        $middleware->validateCsrfTokens(except: [
            'api/ecpay/notify',
            'api/ecpay/callback',
            'ecpay-logistics/status-notify',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
