<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'username' => trim((string) $this->input('username', '')),
        ]);

        if ($this->filled('password_confirmation') && ! $this->exists('password_confirm')) {
            $this->merge([
                'password_confirm' => $this->input('password_confirmation'),
            ]);
        }
    }

    public function rules(): array
    {
        return [
            'username' => [
                'required',
                'regex:/^[A-Za-z0-9_]{4,20}$/',
                Rule::unique('users', 'username'),
            ],
            'password' => [
                'required',
                'string',
                function (string $attribute, mixed $value, \Closure $fail): void {
                    $password = (string) $value;
                    $length = mb_strlen($password);
                    if ($length < 8 || $length > 64 || strlen($password) > 72) {
                        $fail('비밀번호는 8자 이상 64자 이하로 입력해 주세요.');
                    }
                },
            ],
            'password_confirm' => ['required', 'same:password'],
        ];
    }

    public function messages(): array
    {
        return [
            'username.required' => '아이디는 4자 이상 20자 이하의 영문, 숫자, 밑줄만 사용할 수 있습니다.',
            'username.regex' => '아이디는 4자 이상 20자 이하의 영문, 숫자, 밑줄만 사용할 수 있습니다.',
            'username.unique' => '이미 사용 중인 아이디입니다.',
            'password.required' => '비밀번호는 8자 이상 64자 이하로 입력해 주세요.',
            'password_confirm.required' => '비밀번호 확인이 일치하지 않습니다.',
            'password_confirm.same' => '비밀번호 확인이 일치하지 않습니다.',
        ];
    }
}
