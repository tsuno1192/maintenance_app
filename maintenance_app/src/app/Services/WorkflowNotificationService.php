<?php

namespace App\Services;

use App\Notifications\WorkflowStepAdvancedNotification;
use Illuminate\Notifications\AnonymousNotifiable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class WorkflowNotificationService
{
    /**
     * 次ステップの宛先（メール / Teams）へ通知する。
     */
    public function notifyNextStep(
        string $workflow,
        string $recipientKey,
        WorkflowStepAdvancedNotification $notification,
    ): void {
        if (! config('tmq.notifications.enabled', true)) {
            return;
        }

        $mail = $this->resolveMail($workflow, $recipientKey);
        $teams = $this->resolveTeamsWebhook($workflow, $recipientKey);

        if ($mail === null && $teams === null) {
            Log::info('Workflow notification skipped: no recipients configured.', [
                'workflow' => $workflow,
                'recipient_key' => $recipientKey,
            ]);

            return;
        }

        $notifiable = new AnonymousNotifiable;

        if ($mail !== null) {
            $notifiable->route('mail', $mail);
        }

        if ($teams !== null) {
            $notifiable->route('teams', $teams);
        }

        try {
            $notifiable->notify($notification);
        } catch (\Throwable $e) {
            Log::error('Workflow notification failed.', [
                'workflow' => $workflow,
                'recipient_key' => $recipientKey,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * @return list<string>|null
     */
    protected function resolveMail(string $workflow, string $recipientKey): ?array
    {
        $raw = config("tmq.notifications.{$workflow}.{$recipientKey}.mail")
            ?: config('tmq.notifications.default.mail');

        if (! is_string($raw) || trim($raw) === '') {
            return null;
        }

        $emails = collect(preg_split('/\s*,\s*/', $raw) ?: [])
            ->filter(fn (string $email) => Str::contains($email, '@'))
            ->values()
            ->all();

        return $emails === [] ? null : $emails;
    }

    protected function resolveTeamsWebhook(string $workflow, string $recipientKey): ?string
    {
        $url = config("tmq.notifications.{$workflow}.{$recipientKey}.teams_webhook")
            ?: config('tmq.notifications.default.teams_webhook');

        if (! is_string($url) || trim($url) === '') {
            return null;
        }

        return $url;
    }
}
