<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $description = trim((string) $this->input('description', ''));

        $this->merge([
            'item_name' => trim((string) $this->input('item_name', '')),
            'description' => $description === '' ? null : $description,
        ]);
    }

    public function rules(): array
    {
        return [
            'server_id' => ['required', 'integer', 'min:1', Rule::exists('game_servers', 'id')],
            'item_name' => ['required', 'string', 'max:100'],
            'price' => ['required', 'integer', 'min:1', 'max:100000000'],
            'quantity' => ['required', 'integer', 'min:1', 'max:9999'],
            'description' => ['nullable', 'string', 'max:2000'],
        ];
    }

    public function messages(): array
    {
        return [
            'server_id.required' => '서버를 선택하세요.',
            'server_id.integer' => '서버를 선택하세요.',
            'server_id.min' => '서버를 선택하세요.',
            'server_id.exists' => '서버를 선택하세요.',
            'item_name.required' => '아이템명은 1자 이상 100자 이하로 입력해 주세요.',
            'item_name.max' => '아이템명은 1자 이상 100자 이하로 입력해 주세요.',
            'price.required' => '가격은 1원 이상 100,000,000원 이하의 정수로 입력해 주세요.',
            'price.integer' => '가격은 1원 이상 100,000,000원 이하의 정수로 입력해 주세요.',
            'price.min' => '가격은 1원 이상 100,000,000원 이하의 정수로 입력해 주세요.',
            'price.max' => '가격은 1원 이상 100,000,000원 이하의 정수로 입력해 주세요.',
            'quantity.required' => '수량은 1개 이상 9,999개 이하의 정수로 입력해 주세요.',
            'quantity.integer' => '수량은 1개 이상 9,999개 이하의 정수로 입력해 주세요.',
            'quantity.min' => '수량은 1개 이상 9,999개 이하의 정수로 입력해 주세요.',
            'quantity.max' => '수량은 1개 이상 9,999개 이하의 정수로 입력해 주세요.',
            'description.max' => '설명은 2,000자 이하로 입력해 주세요.',
        ];
    }
}
