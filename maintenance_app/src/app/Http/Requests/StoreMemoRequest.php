<?php

namespace App\Http\Requests;

use App\Enums\MemoPriority;
use App\Enums\MemoShift;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreMemoRequest extends FormRequest
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
            'title' => ['required', 'string', 'max:255'],
            'body' => ['nullable', 'string'],
            'machine_id' => ['nullable', 'integer', 'exists:machines,id'],
            'shift' => ['required', Rule::enum(MemoShift::class)],
            'priority' => ['required', Rule::enum(MemoPriority::class)],
            'category' => ['nullable', 'string', 'max:100'],
            'images' => ['nullable', 'array', 'max:8'],
            'images.*' => ['file', 'image', 'mimes:jpeg,jpg,png,webp,gif', 'max:5120'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'title' => '件名',
            'body' => '申し送り内容',
            'machine_id' => '関連設備',
            'shift' => '勤務帯',
            'priority' => '重要度',
            'category' => '分類',
            'images' => '写真',
            'images.*' => '写真',
        ];
    }
}
