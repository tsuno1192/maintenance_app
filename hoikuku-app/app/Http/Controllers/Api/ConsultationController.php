<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ConsultationTicket;
use App\Models\ConsultationMessage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;



class ConsultationController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();

        $query = ConsultationTicket::with(['messages', 'user', 'staff']);

        if ($user->isParent()) {
            $query->where('user_id', $user->id);
        } elseif (! $user->canManageFacility()) {
            abort(403);
        }

        $tickets = $query->orderBy('updated_at', 'desc')->get();

        return response()->json([
            'status' => 'success',
            'data' => $tickets,
        ]);
    }

    public function store(Request $request)
    {
        $user = Auth::user();
        abort_unless($user->isParent() || $user->canManageFacility(), 403);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'message' => 'required|string|max:5000',
        ]);

        [$ticket, $message] = DB::transaction(function () use ($user, $validated) {
            $ticket = ConsultationTicket::create([
                'user_id' => $user->id,
                'title' => $validated['title'],
                'status' => 'open',
            ]);

            $message = ConsultationMessage::create([
                'consultation_ticket_id' => $ticket->id,
                'user_id' => $user->id,
                'body' => $validated['message'],
            ]);

            return [$ticket, $message];
        });

        return response()->json([
            'status' => 'success',
            'message' => '相談チケットを作成しました。',
            'data' => [
                'ticket' => $ticket,
                'message' => $message,
            ],
        ], 201);
    }

    public function sendMessage(Request $request, $id)
    {
        $validated = $request->validate([
            'message' => 'required|string|max:5000',
        ]);

        $ticket = ConsultationTicket::findOrFail($id);
        $user = Auth::user();

        $canAccess = $ticket->user_id === $user->id || $user->canManageFacility();
        abort_unless($canAccess, 403);

        $message = ConsultationMessage::create([
            'consultation_ticket_id' => $ticket->id,
            'user_id' => $user->id,
            'body' => $validated['message'],
        ]);

        $ticket->touch();

        return response()->json([
            'status' => 'success',
            'data' => $message,
        ], 201);
    }

    public function updateStatus(Request $request, $id)
    {
        abort_unless(Auth::user()->canManageFacility(), 403);

        $validated = $request->validate([
            'status' => 'required|string|in:open,in_progress,closed',
        ]);

        $ticket = ConsultationTicket::findOrFail($id);

        $ticket->update([
            'status' => $validated['status'],
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'ステータスを更新しました。',
            'data' => $ticket,
        ]);
    }
}
