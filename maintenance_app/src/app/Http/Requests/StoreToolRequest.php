<?php

namespace App\Http\Requests;

use App\Enums\ToolStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreToolRequest extends FormRequest
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
        $toolId = $this->route('tool')?->id;

        return [
            'code' => ['required', 'string', 'max:50', Rule::unique('tools', 'code')->ignore($toolId)],
            'name' => ['required', 'string', 'max:255'],
            'category' => ['nullable', 'string', 'max:100'],
            'location' => ['nullable', 'string', 'max:100'],
            'status' => ['required', Rule::enum(ToolStatus::class)],
            'quantity' => ['required', 'integer', 'min:0', 'max:9999'],
            'manufacturer' => ['nullable', 'string', 'max:100'],
            'purchased_on' => ['nullable', 'date'],
            'notes' => ['nullable', 'string'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'code' => '管理番号',
            'name' => '工具名',
            'category' => '分類',
            'location' => '保管場所',
            'status' => '状態',
            'quantity' => '在庫数',
            'manufacturer' => 'メーカー',
            'purchased_on' => '購入日',
            'notes' => '備考',
        ];
    }
}
