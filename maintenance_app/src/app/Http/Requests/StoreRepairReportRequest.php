<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreRepairReportRequest extends FormRequest
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
            'category_major' => ['required', 'string', 'max:100'],
            'category_middle' => ['nullable', 'string', 'max:100'],
            'category_minor' => ['nullable', 'string', 'max:100'],
            'title' => ['required', 'string', 'max:255'],
            'content' => ['nullable', 'string'],
            'investigation_result' => ['nullable', 'string'],
            'cause' => ['nullable', 'string'],
            'repaired_on' => ['required', 'date'],
            'used_spare_parts' => ['nullable', 'string'],
            'used_drawings' => ['nullable', 'string'],
            'trial_run_result' => ['required', 'string'],
            'operation_records' => ['nullable', 'string'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'category_major' => '大分類',
            'category_middle' => '中分類',
            'category_minor' => '小分類',
            'title' => '発生事象件名',
            'content' => '内容',
            'investigation_result' => '調査結果',
            'cause' => '発生原因',
            'repaired_on' => '補修日',
            'used_spare_parts' => '使用予備品',
            'used_drawings' => '使用図面',
            'trial_run_result' => '試運転結果',
            'operation_records' => '各種動作記録',
        ];
    }
}
