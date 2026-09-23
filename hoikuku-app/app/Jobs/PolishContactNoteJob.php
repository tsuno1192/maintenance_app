<?php

namespace App\Jobs;

use App\Models\ContactNote;
use App\Services\ContactNotePolisher;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class PolishContactNoteJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 2;

    public function __construct(
        public int $contactNoteId,
    ) {}

    public function handle(ContactNotePolisher $polisher): void
    {
        $note = ContactNote::with('child')->find($this->contactNoteId);
        if (! $note) {
            return;
        }

        $polished = $polisher->polish($note->raw_memo, $note->child?->name ?? 'お子さま');

        $note->update([
            'polished_body' => $polished,
            'status' => ContactNote::STATUS_POLISHED,
            'shared_at' => now(),
        ]);
    }

    public function failed(): void
    {
        ContactNote::whereKey($this->contactNoteId)->update([
            'status' => ContactNote::STATUS_FAILED,
        ]);
    }
}
