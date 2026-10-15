<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Hash;

class Authenticator
{
    private const DUMMY_PASSWORD_HASH = '$2y$12$.5ndwfRuN0rO0GDoGimpjOg1Xcaf8htFY1V/kiPRJBaT8D2jEnAte';

    public const FAILURE = '아이디 또는 비밀번호가 올바르지 않습니다.';

    public function attempt(string $username, string $password): ?User
    {
        $user = User::query()->where('username', $username)->first();
        $hash = $user?->getAuthPassword() ?? self::DUMMY_PASSWORD_HASH;

        if ($user === null || ! Hash::check($password, $hash)) {
            return null;
        }

        return $user;
    }
}
