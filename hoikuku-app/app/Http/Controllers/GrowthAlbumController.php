<?php

namespace App\Http\Controllers;

use App\Jobs\GenerateGrowthAlbumJob;
use App\Models\GrowthAlbum;
use App\Support\ChildAccess;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class GrowthAlbumController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        $children = ChildAccess::visibleChildrenQuery($user)->get(['id', 'name']);

        $albums = GrowthAlbum::with(['child:id,name', 'items.documentation'])
            ->when($user->isParent(), fn ($q) => $q->where('child_id', $user->child_id)->whereNotNull('shared_at'))
            ->latest('year_month')
            ->paginate(20);

        return view('care.albums.index', compact('albums', 'children'));
    }

    public function store(Request $request): RedirectResponse
    {
        abort_unless(ChildAccess::canManageCare($request->user()), 403);

        $validated = $request->validate([
            'child_id' => ['required', 'exists:children,id'],
            'year_month' => ['required', 'regex:/^\d{4}-\d{2}$/'],
        ]);

        $album = GrowthAlbum::query()->updateOrCreate(
            [
                'child_id' => $validated['child_id'],
                'year_month' => $validated['year_month'],
            ],
            [
                'title' => $validated['year_month'].' の成長アルバム',
                'status' => GrowthAlbum::STATUS_PENDING,
                'shared_at' => null,
                'created_by' => $request->user()->id,
            ]
        );

        GenerateGrowthAlbumJob::dispatch($album->id);

        return redirect()->route('albums.index')->with('success', '成長アルバムを生成中です。');
    }
}
