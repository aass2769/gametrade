<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\RegisterRequest;
use App\Models\User;
use App\Services\Authenticator;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function register(RegisterRequest $request): JsonResponse
    {
        try {
            $user = User::query()->create([
                'username' => $request->validated('username'),
                'password' => $request->validated('password'),
            ]);
        } catch (QueryException $exception) {
            $state = (string) ($exception->errorInfo[0] ?? '');
            $code = (int) ($exception->errorInfo[1] ?? 0);
            if ($state === '23000' && in_array($code, [1062, 19], true)) {
                throw ValidationException::withMessages([
                    'username' => '이미 사용 중인 아이디입니다.',
                ]);
            }

            throw $exception;
        }

        return response()->json([
            'token' => $user->createToken('api')->plainTextToken,
            'username' => $user->username,
        ], 201);
    }

    public function login(Request $request, Authenticator $authenticator): JsonResponse
    {
        $user = $authenticator->attempt(
            trim((string) $request->input('username', '')),
            (string) $request->input('password', ''),
        );

        if ($user === null) {
            return response()->json(['message' => Authenticator::FAILURE], 422);
        }

        return response()->json([
            'token' => $user->createToken('api')->plainTextToken,
            'username' => $user->username,
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()?->currentAccessToken()?->delete();

        return response()->json(['message' => '로그아웃했습니다.']);
    }
}
