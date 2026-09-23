<?php

namespace App\Http\Requests\Machine;

use App\Enums\MachineStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * 設備新規登録時のバリデーション。
 */
class StoreMachineRequest extends FormRequest
{
    /**
     * 認可判定（ログイン済みであれば許可。詳細なロール制御はポリシーで拡張予定）。
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
            /**
             * QR 識別子: 英大文字・数字・ハイフンのみ（例: MCH-0001）
             */
            'qr_identifier' => [
                'required',
                'string',
                'max:64',
                'regex:/^[A-Z0-9]+(?:-[A-Z0-9]+)*$/',
                Rule::unique('machines', 'qr_identifier'),
            ],
            'manual_url' => ['nullable', 'url', 'max:2048'],
            'location' => ['nullable', 'string', 'max:120'],
            'status' => ['required', Rule::enum(MachineStatus::class)],
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
            'name' => '設備名',
            'qr_identifier' => 'QR識別子',
            'manual_url' => 'マニュアルURL',
            'location' => '設置場所',
            'status' => '稼働ステータス',
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
            'qr_identifier.regex' => 'QR識別子は英大文字・数字・ハイフンのみで入力してください（例: MCH-0001）。',
        ];
    }
}
