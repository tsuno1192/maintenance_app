<?php

namespace App\Http\Controllers;

use App\Jobs\PolishConsultationMessageJob;
use App\Models\ConsultationMessage;
use App\Models\ConsultationTicket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ConsultationController extends Controller
{
    public function create()
    {
        abort_unless(Auth::user()->isParent(), 403);

        return view('consultations.create');
    }

    public function store(Request $request)
    {
        abort_unless(Auth::user()->isParent(), 403);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'body' => 'required|string|max:5000',
        ]);

        $ticket = ConsultationTicket::create([
            'user_id' => Auth::id(),
            'title' => $validated['title'],
            'status' => 'open',
        ]);

        $message = ConsultationMessage::create([
            'consultation_ticket_id' => $ticket->id,
            'user_id' => Auth::id(),
            'body' => $validated['body'],
            'original_body' => $validated['body'],
            'ai_status' => 'pending',
        ]);

        PolishConsultationMessageJob::dispatch($message->id);

        return redirect()
            ->route('consultations.create')
            ->with('success', '相談を受け付けました。AIが表現を整えています。');
    }

    public function indexForStaff()
    {
        abort_unless(Auth::user()->canManageFacility(), 403);

        $tickets = ConsultationTicket::with(['user', 'messages' => fn ($q) => $q->latest('id')->limit(20)])
            ->latest()
            ->paginate(20);

        return view('admin.consultations.index', compact('tickets'));
    }
}
