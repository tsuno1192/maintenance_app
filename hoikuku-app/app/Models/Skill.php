<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Skill extends Model
{
    use HasFactory;

    protected $fillable = ['name'];

    /**
     * この資格を持っているスタッフ（多対多）
     * ※スタッフモデルのクラス名が 'User' の場合は User::class に変更してください
     */
    public function staffs(): BelongsToMany
    {
        return $this->belongsToMany(
            \App\Models\User::class, // スタッフを管理しているモデル
            'staff_skills',          // 中間テーブル名
            'skill_id',              // 自モデルの外部キー
            'staff_id'               // 相手モデルの外部キー
        );
    }
}
