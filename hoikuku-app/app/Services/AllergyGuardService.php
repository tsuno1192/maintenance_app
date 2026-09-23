<?php

namespace App\Services;

use App\Models\Child;
use App\Models\ChildAllergy;
use App\Models\MealServiceCheck;
use App\Models\User;
use Illuminate\Support\Collection;

class AllergyGuardService
{
    /**
     * @param  list<string>  $menuAllergens
     * @return array{alert: bool, matched: list<string>, check: MealServiceCheck}
     */
    public function checkServing(Child $child, User $staff, string $mealType, array $menuAllergens): array
    {
        $normalizedMenu = collect($menuAllergens)
            ->map(fn ($item) => mb_strtolower(trim((string) $item)))
            ->filter()
            ->unique()
            ->values();

        /** @var Collection<int, ChildAllergy> $allergies */
        $allergies = $child->allergyRecords()
            ->where('is_active', true)
            ->get(['id', 'allergen', 'severity']);

        $matched = $allergies
            ->filter(function (ChildAllergy $allergy) use ($normalizedMenu) {
                $name = mb_strtolower(trim($allergy->allergen));

                return $normalizedMenu->contains(fn ($menu) => $menu === $name || str_contains($menu, $name) || str_contains($name, $menu));
            })
            ->pluck('allergen')
            ->values()
            ->all();

        $check = MealServiceCheck::create([
            'child_id' => $child->id,
            'user_id' => $staff->id,
            'meal_type' => $mealType,
            'menu_allergens' => $normalizedMenu->all(),
            'matched_allergens' => $matched,
            'alert_triggered' => count($matched) > 0,
            'acknowledged' => false,
            'served_at' => now(),
        ]);

        return [
            'alert' => count($matched) > 0,
            'matched' => $matched,
            'check' => $check,
        ];
    }
}
