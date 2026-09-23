<?php

namespace App\Jobs;

use App\Models\ConsultationMessage;
use App\Services\AITextTransformer;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Throwable;

class PolishConsultationMessageJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 2;

    public function __construct(
        public int $messageId,
    ) {}

    public function handle(AITextTransformer $transformer): void
    {
        $message = ConsultationMessage::query()->find($this->messageId);
        if (! $message) {
            return;
        }

        $source = $message->original_body ?: $message->body;

        try {
            $mild = $transformer->makeMild($source);
            $message->update([
                'body' => $mild,
                'ai_status' => 'done',
            ]);
        } catch (Throwable $e) {
            $message->update(['ai_status' => 'failed']);
            throw $e;
        }
    }

    public function failed(): void
    {
        ConsultationMessage::whereKey($this->messageId)->update(['ai_status' => 'failed']);
    }
}
