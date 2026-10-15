<?php

namespace App\Http\Controllers;

use App\Http\Requests\RegisterRequest;
use App\Models\User;
use App\Services\Authenticator;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function create(): View
    {
        return view('auth.register');
    }

    public function store(RegisterRequest $request): RedirectResponse
    {
        $user = $this->createUser($request->validated());
        Auth::login($user);

        return redirect()->route('items.index')->with('status', '가입했습니다.');
    }

    public function loginForm(): View
    {
        return view('auth.login');
    }

    public function login(Request $request, Authenticator $authenticator): RedirectResponse
    {
        $username = trim((string) $request->input('username', ''));
        $user = $authenticator->attempt($username, (string) $request->input('password', ''));

        if ($user === null) {
            return back()
                ->withInput(['username' => $username])
                ->withErrors(['login' => Authenticator::FAILURE]);
        }

        Auth::login($user);

        return redirect()->route('items.index');
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('items.index');
    }

    /**
     * @param  array{username: string, password: string}  $data
     */
    private function createUser(array $data): User
    {
        try {
            return User::query()->create([
                'username' => $data['username'],
                'password' => $data['password'],
            ]);
        } catch (QueryException $exception) {
            if ($this->isDuplicateUsername($exception)) {
                throw ValidationException::withMessages([
                    'username' => '이미 사용 중인 아이디입니다.',
                ]);
            }

            throw $exception;
        }
    }

    private function isDuplicateUsername(QueryException $exception): bool
    {
        $state = (string) ($exception->errorInfo[0] ?? '');
        $code = (int) ($exception->errorInfo[1] ?? 0);

        return $state === '23000' && in_array($code, [1062, 19], true);
    }
}
