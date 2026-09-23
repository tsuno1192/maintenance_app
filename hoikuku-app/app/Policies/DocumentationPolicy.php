<?php

namespace App\Policies;

use App\Models\Documentation;
use App\Models\User;

class DocumentationPolicy
{
    /**
     * 一覧閲覧（スタッフ・カウンセラーは全体、保護者は自分の児童分）
     */
    public function viewAny(User $user): bool
    {
        return $user->isStaff()
            || $user->isCounselor()
            || $user->isParent();
    }

    /**
     * 個別閲覧・写真配信
     * 未紐付け写真は保護者には見せない
     */
    public function view(User $user, Documentation $documentation): bool
    {
        if ($user->isStaff() || $user->isCounselor()) {
            return true;
        }

        if ($documentation->child_id === null) {
            return false;
        }

        return $user->isParent()
            && (int) $user->child_id === (int) $documentation->child_id;
    }

    /**
     * 新規作成（スタッフ・管理者のみ）
     */
    public function create(User $user): bool
    {
        return $user->isStaff();
    }

    /**
     * 顔認証レビュー一覧
     */
    public function review(User $user): bool
    {
        return $user->isStaff();
    }

    /**
     * 児童の手動紐付け
     */
    public function assignChild(User $user, Documentation $documentation): bool
    {
        return $user->isStaff();
    }
}
