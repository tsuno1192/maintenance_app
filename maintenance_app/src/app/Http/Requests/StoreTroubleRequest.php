<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTroubleRequest extends FormRequest
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
            'investigation' => ['nullable', 'string'],
            'estimated_cause' => ['nullable', 'string'],
            'occurred_on' => ['nullable', 'date'],
            'repair_requested_on' => ['nullable', 'date', 'after_or_equal:occurred_on'],
            'required_spare_parts' => ['nullable', 'string'],
            'required_drawings' => ['nullable', 'string'],
            'created_group' => ['required', 'string', 'max:100'],
            'reporter_name' => ['required', 'string', 'max:100'],
            'machine_id' => ['nullable', 'integer', 'exists:machines,id'],
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
            'title' => '件名',
            'content' => '内容',
            'investigation' => '調査内容',
            'estimated_cause' => '推定原因',
            'occurred_on' => '発生日',
            'repair_requested_on' => '補修依頼日',
            'required_spare_parts' => '必要予備品',
            'required_drawings' => '必要図面',
            'created_group' => '作成Gr',
            'reporter_name' => '報告者',
            'machine_id' => '関連設備',
        ];
    }
}
