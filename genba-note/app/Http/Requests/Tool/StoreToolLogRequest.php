<?php

namespace App\Http\Requests\Tool;

use App\Enums\ToolLogAction;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * 工具貸出／返却ログ作成時のバリデーション。
 */
class StoreToolLogRequest extends FormRequest
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
            'tool_id' => ['required', 'uuid', Rule::exists('tools', 'id')],
            'action' => ['required', Rule::enum(ToolLogAction::class)],
            'notes' => ['nullable', 'string', 'max:1000'],
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
            'tool_id' => '工具',
            'action' => 'アクション',
            'notes' => '備考',
            'current_location' => '現在位置',
        ];
    }
}
