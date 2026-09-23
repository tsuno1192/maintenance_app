<?php

namespace App\Http\Requests;

use App\Enums\MachineStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreMachineRequest extends FormRequest
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
        $machineId = $this->route('machine')?->id;

        return [
            'code' => ['required', 'string', 'max:50', Rule::unique('machines', 'code')->ignore($machineId)],
            'name' => ['required', 'string', 'max:255'],
            'area' => ['nullable', 'string', 'max:100'],
            'category' => ['nullable', 'string', 'max:100'],
            'manufacturer' => ['nullable', 'string', 'max:100'],
            'model' => ['nullable', 'string', 'max:100'],
            'installed_on' => ['nullable', 'date'],
            'status' => ['required', Rule::enum(MachineStatus::class)],
            'notes' => ['nullable', 'string'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'code' => '設備番号',
            'name' => '設備名',
            'area' => 'エリア',
            'category' => '分類',
            'manufacturer' => 'メーカー',
            'model' => '型式',
            'installed_on' => '設置日',
            'status' => '稼働状態',
            'notes' => '備考',
        ];
    }
}
