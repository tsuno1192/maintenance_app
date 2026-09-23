<?php

namespace App\Http\Controllers;

use App\Models\ChildAllergy;
use App\Services\AllergyGuardService;
use App\Support\ChildAccess;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AllergyController extends Controller
{
    public function index(Request $request): View
    {
        abort_unless($request->user()->isStaff(), 403);

        $children = ChildAccess::visibleChildrenQuery($request->user())
            ->with(['allergyRecords' => fn ($q) => $q->where('is_active', true)])
            ->paginate(30);

        return view('care.allergies.index', compact('children'));
    }

    public function store(Request $request): RedirectResponse
    {
        abort_unless($request->user()->isStaff(), 403);

        $validated = $request->validate([
            'child_id' => ['required', 'exists:children,id'],
            'allergen' => ['required', 'string', 'max:100'],
            'severity' => ['required', 'in:low,medium,high,critical'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        ChildAllergy::create([
            ...$validated,
            'is_active' => true,
        ]);

        return back()->with('success', 'アレルギー情報を登録しました。');
    }

    public function checkServing(Request $request, AllergyGuardService $guard): RedirectResponse
    {
        abort_unless($request->user()->isStaff(), 403);

        $validated = $request->validate([
            'child_id' => ['required', 'exists:children,id'],
            'meal_type' => ['required', 'in:lunch,snack'],
            'menu_allergens' => ['required', 'string', 'max:500'],
        ]);

        $child = ChildAccess::visibleChildrenQuery($request->user())->findOrFail($validated['child_id']);
        $menu = preg_split('/[,、\s]+/u', $validated['menu_allergens']) ?: [];

        $result = $guard->checkServing(
            $child,
            $request->user(),
            $validated['meal_type'],
            array_values(array_filter($menu))
        );

        if ($result['alert']) {
            return back()
                ->with('allergy_alert', true)
                ->with('allergy_matched', $result['matched'])
                ->with('allergy_child', $child->name)
                ->with('error', 'アレルギー照合で警告があります: '.implode(' / ', $result['matched']));
        }

        return back()->with('success', 'アレルギー照合OKです。配膳を続行できます。');
    }
}
