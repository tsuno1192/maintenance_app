<?php

namespace App\Http\Requests\Tool;

use App\Enums\ToolStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * 工具新規登録時のバリデーション。
 */
class StoreToolRequest extends FormRequest
{
    /**
     * 認可判定。
     */
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * バリデーションルール。
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:120'],
            'serial_number' => [
                'required',
                'string',
                'max:64',
                'regex:/^[A-Z0-9]+(?:-[A-Z0-9]+)*$/',
                Rule::unique('tools', 'serial_number'),
            ],
            'status' => ['required', Rule::enum(ToolStatus::class)],
            'current_location' => ['nullable', 'string', 'max:120'],
        ];
    }

    /**
     * 属性名の日本語化。
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'name' => '工具名',
            'serial_number' => 'シリアル番号',
            'status' => 'ステータス',
            'current_location' => '現在位置',
        ];
    }

    /**
     * カスタムエラーメッセージ。
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'serial_number.regex' => 'シリアル番号は英大文字・数字・ハイフンのみで入力してください。',
        ];
    }
}
