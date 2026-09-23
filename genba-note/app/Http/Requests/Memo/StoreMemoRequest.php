<?php

namespace App\Http\Requests\Memo;

use App\Enums\MemoStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * 申し送り作成時のバリデーション。
 *
 * 画像は複数枚対応。MIME・サイズ・枚数を現場運用前提で厳しめに制限する。
 */
class StoreMemoRequest extends FormRequest
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
            'machine_id' => ['required', 'uuid', Rule::exists('machines', 'id')],
            'message' => ['required', 'string', 'min:3', 'max:5000'],
            'tags' => ['nullable', 'array', 'max:20'],
            'tags.*' => ['string', 'max:40'],
            'status' => ['nullable', Rule::enum(MemoStatus::class)],
            'images' => ['nullable', 'array', 'max:5'],
            'images.*' => [
                'file',
                'image',
                'mimes:jpeg,jpg,png,webp',
                'max:5120', // 5MB
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

    /**
     * カスタムエラーメッセージ。
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'images.max' => '添付画像は最大5枚までです。',
            'images.*.max' => '各画像は5MB以下にしてください。',
            'images.*.mimes' => '画像は jpeg / png / webp 形式のみアップロードできます。',
        ];
    }
}
