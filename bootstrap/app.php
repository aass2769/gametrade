<?php

use App\Exceptions\InvalidStatusTransition;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->redirectGuestsTo('/login');
        $middleware->redirectUsersTo('/');
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );

        $missing = function (Request $request) {
            $message = $request->is('items/*') || $request->is('api/*/items/*')
                ? '상품을 찾을 수 없습니다.'
                : '페이지를 찾을 수 없습니다.';

            if ($request->is('api/*') || $request->expectsJson()) {
                return response()->json(['message' => $message], 404);
            }

            return response()->view('errors.message', [
                'title' => '찾을 수 없음',
                'message' => $message,
            ], 404);
        };

        $exceptions->render(function (ModelNotFoundException $exception, Request $request) use ($missing) {
            return $missing($request);
        });

        $exceptions->render(function (NotFoundHttpException $exception, Request $request) use ($missing) {
            return $missing($request);
        });

        $exceptions->render(function (AuthorizationException $exception, Request $request) {
            $message = $exception->getMessage() !== '' ? $exception->getMessage() : '권한이 없습니다.';

            if ($request->is('api/*') || $request->expectsJson()) {
                return response()->json(['message' => $message], 403);
            }

            return response()->view('errors.message', [
                'title' => '권한 없음',
                'message' => $message,
            ], 403);
        });

        $exceptions->render(function (InvalidStatusTransition $exception, Request $request) {
            if ($request->is('api/*') || $request->expectsJson()) {
                return response()->json(['message' => $exception->getMessage()], 400);
            }

            return response()->view('errors.message', [
                'title' => '요청 오류',
                'message' => $exception->getMessage(),
            ], 400);
        });

        $exceptions->render(function (AuthenticationException $exception, Request $request) {
            if ($request->is('api/*') || $request->expectsJson()) {
                return response()->json(['message' => '인증이 필요합니다.'], 401);
            }

            return redirect()->guest('/login');
        });
    })->create();
