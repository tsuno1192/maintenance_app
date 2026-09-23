<?php

namespace App\Http\Controllers;

use App\Jobs\ProcessInquiryAiJob;
use App\Models\Inquiry;
use Illuminate\Http\Request;

class InquiryController extends Controller
{
    public function store(Request $request)
    {
        $user = $request->user();
        abort_unless($user->isParent(), 403);

        $validated = $request->validate([
            'message' => 'required|string|max:5000',
            'child_id' => 'required|exists:children,id',
        ]);

        if ((int) $user->child_id !== (int) $validated['child_id']) {
            abort(403);
        }

        $inquiry = Inquiry::create([
            'child_id' => $validated['child_id'],
            'user_id' => $user->id,
            'original_message' => $validated['message'],
            'mild_message' => null,
            'ai_auto_response' => null,
            'status' => 'pending',
            'ai_status' => 'pending',
        ]);

        ProcessInquiryAiJob::dispatch($inquiry->id);

        return redirect()->back()->with('success', 'お問い合わせを受け付けました。AIが内容を整えています。');
    }
}
