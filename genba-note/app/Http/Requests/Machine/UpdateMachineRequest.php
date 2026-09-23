<?php

namespace App\Http\Requests\Machine;

use App\Enums\MachineStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * 設備更新時のバリデーション。
 */
class UpdateMachineRequest extends FormRequest
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
        /** @var string|null $machineId */
        $machineId = $this->route('machine')?->id ?? $this->route('machine');

        return [
            'name' => ['sometimes', 'required', 'string', 'max:120'],
            'qr_identifier' => [
                'sometimes',
                'required',
                'string',
                'max:64',
                'regex:/^[A-Z0-9]+(?:-[A-Z0-9]+)*$/',
                Rule::unique('machines', 'qr_identifier')->ignore($machineId),
            ],
            'manual_url' => ['nullable', 'url', 'max:2048'],
            'location' => ['nullable', 'string', 'max:120'],
            'status' => ['sometimes', 'required', Rule::enum(MachineStatus::class)],
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
