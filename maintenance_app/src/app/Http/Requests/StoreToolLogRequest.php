<?php

namespace App\Http\Requests;

use App\Enums\ToolLogAction;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreToolLogRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'action' => ['required', Rule::enum(ToolLogAction::class)],
            'quantity' => ['required', 'integer', 'min:1', 'max:9999'],
            'note' => ['nullable', 'string', 'max:1000'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'action' => '操作',
            'quantity' => '数量',
            'note' => 'メモ',
        ];
    }
}
