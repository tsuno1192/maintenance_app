<?php

namespace App\Http\Controllers;

use App\Jobs\PolishContactNoteJob;
use App\Models\ContactNote;
use App\Support\ChildAccess;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ContactNoteController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        $children = ChildAccess::visibleChildrenQuery($user)->get(['id', 'name']);

        $notes = ContactNote::with(['child:id,name', 'user:id,name'])
            ->when($user->isParent(), fn ($q) => $q->where('child_id', $user->child_id)->whereNotNull('shared_at'))
            ->latest('note_date')
            ->latest('id')
            ->paginate(20);

        return view('care.contact-notes.index', compact('notes', 'children'));
    }

    public function store(Request $request): RedirectResponse
    {
        abort_unless(ChildAccess::canManageCare($request->user()), 403);

        $validated = $request->validate([
            'child_id' => ['required', 'exists:children,id'],
            'note_date' => ['required', 'date'],
            'raw_memo' => ['required', 'string', 'max:5000'],
        ]);

        $note = ContactNote::create([
            ...$validated,
            'user_id' => $request->user()->id,
            'status' => ContactNote::STATUS_PENDING,
        ]);

        PolishContactNoteJob::dispatch($note->id);

        return redirect()
            ->route('contact-notes.index')
            ->with('success', '連絡帳を登録しました。AI要約を生成中です。');
    }
}
