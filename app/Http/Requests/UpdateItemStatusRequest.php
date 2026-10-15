<?php

namespace App\Http\Requests;

use App\Enums\ItemStatus;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

class UpdateItemStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status' => ['required', Rule::enum(ItemStatus::class)],
        ];
    }

    public function messages(): array
    {
        return [
            'status.required' => '허용되지 않는 상태 변경입니다.',
            'status.enum' => '허용되지 않는 상태 변경입니다.',
        ];
    }

    protected function failedValidation(Validator $validator): void
    {
        $message = '허용되지 않는 상태 변경입니다.';

        if ($this->is('api/*') || $this->expectsJson()) {
            throw new HttpResponseException(response()->json(['message' => $message], 400));
        }

        throw new HttpResponseException(response()->view('errors.message', [
            'title' => '요청 오류',
            'message' => $message,
        ], 400));
    }
}
