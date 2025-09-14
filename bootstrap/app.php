<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        apiPrefix: 'api',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withProviders([
        \App\Providers\SandboxRoutingServiceProvider::class,
    ])
    ->withMiddleware(function (Middleware $middleware): void {
        // 명확한 미들웨어 설정
        $middleware->alias([
            'auth.web-or-token' => \App\Http\Middleware\SimpleAuth::class,
            'rate.limit' => \App\Http\Middleware\ApiRateLimit::class,
            'platform.admin' => \App\Http\Middleware\PlatformAdminMiddleware::class,
            'loginRequired.auth' => \App\Http\Middleware\LoginRequiredAuth::class,
        ]);

        // SimpleAuth를 CSRF 검증보다 먼저 실행하도록 우선순위 설정
        $middleware->priority([
            \App\Http\Middleware\SimpleAuth::class,
            \Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // 단순한 예외 처리
        $exceptions->render(function (\App\Exceptions\ApiException $e) {
            return $e->render();
        });

        $exceptions->render(function (\Exception $e, \Illuminate\Http\Request $request) {
            if ($request->expectsJson()) {
                return \App\Exceptions\ApiException::serverError()->render();
            }
        });
    })->create();
