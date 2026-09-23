<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Child;
use App\Models\ChildFaceProfile;
use App\Services\ChildFaceEnrollmentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ChildFaceProfileController extends Controller
{
    public function index(): View
    {
        abort_unless(auth()->user()?->isStaff(), 403);

        $children = Child::with(['faceProfiles' => fn ($q) => $q->latest()])
            ->orderBy('name')
            ->get();

        return view('admin.children.faces', compact('children'));
    }

    public function store(Request $request, Child $child, ChildFaceEnrollmentService $enrollment): RedirectResponse
    {
        abort_unless(auth()->user()?->isStaff(), 403);

        $validated = $request->validate([
            'photo' => [
                'required',
                'file',
                'max:5120',
                'mimes:jpg,jpeg,png,webp',
                'mimetypes:image/jpeg,image/png,image/webp',
            ],
        ]);

        $enrollment->enroll($child, $request->file('photo'), true);

        return redirect()
            ->route('admin.children.faces')
            ->with('success', "{$child->name} の参照顔写真を登録しました。");
    }

    public function destroy(ChildFaceProfile $faceProfile, ChildFaceEnrollmentService $enrollment): RedirectResponse
    {
        abort_unless(auth()->user()?->isStaff(), 403);

        $enrollment->delete($faceProfile);

        return redirect()
            ->route('admin.children.faces')
            ->with('success', '参照顔写真を削除しました。');
    }
}
