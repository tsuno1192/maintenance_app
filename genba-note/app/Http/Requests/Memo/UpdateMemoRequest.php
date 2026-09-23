<?php

namespace App\Http\Requests\Memo;

use App\Enums\MemoStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * 申し送り更新時のバリデーション。
 */
class UpdateMemoRequest extends FormRequest
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
            'machine_id' => ['sometimes', 'required', 'uuid', Rule::exists('machines', 'id')],
            'message' => ['sometimes', 'required', 'string', 'min:3', 'max:5000'],
            'tags' => ['nullable', 'array', 'max:20'],
            'tags.*' => ['string', 'max:40'],
            'status' => ['sometimes', 'required', Rule::enum(MemoStatus::class)],
            'images' => ['nullable', 'array', 'max:5'],
            'images.*' => [
                'file',
                'image',
                'mimes:jpeg,jpg,png,webp',
                'max:5120',
            ],
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
            'machine_id' => '設備',
            'message' => '申し送り内容',
            'tags' => 'タグ',
            'status' => 'ステータス',
            'images' => '添付画像',
            'images.*' => '添付画像',
        ];
    }
}
