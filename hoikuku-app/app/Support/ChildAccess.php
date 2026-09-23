<?php

namespace App\Support;

use App\Models\Child;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

class ChildAccess
{
    public static function canView(User $user, Child $child): bool
    {
        if ($user->isStaff() || $user->isCounselor()) {
            return true;
        }

        return $user->isParent() && (int) $user->child_id === (int) $child->id;
    }

    public static function canManageCare(User $user): bool
    {
        return $user->isStaff();
    }

    /**
     * @return Builder<Child>
     */
    public static function visibleChildrenQuery(User $user): Builder
    {
        $query = Child::query()->orderBy('name');

        if ($user->isParent()) {
            $query->where('id', $user->child_id);
        }

        return $query;
    }
}
