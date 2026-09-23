<?php

namespace App\Notifications;

use App\Models\RepairReport;
use App\Models\Trouble;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class WorkflowStepAdvancedNotification extends Notification
{
    use Queueable;

    /**
     * @param  array<int, array{name: string, value: string}>  $facts
     */
    public function __construct(
        public readonly string $workflowType,
        public readonly string $subjectTitle,
        public readonly string $fromLabel,
        public readonly string $toLabel,
        public readonly string $message,
        public readonly string $actionUrl,
        public readonly array $facts = [],
    ) {}

    public static function forTrouble(Trouble $trouble, string $fromLabel, string $toLabel): self
    {
        return new self(
            workflowType: 'trouble',
            subjectTitle: $trouble->title,
            fromLabel: $fromLabel,
            toLabel: $toLabel,
            message: "トラブル承認ワークフローが「{$fromLabel}」から「{$toLabel}」へ進みました。",
            actionUrl: route('troubles.show', $trouble),
            facts: [
                ['name' => '件名', 'value' => $trouble->title],
                ['name' => '作成Gr', 'value' => (string) $trouble->created_group],
                ['name' => '報告者', 'value' => (string) $trouble->reporter_name],
                ['name' => '移行元', 'value' => $fromLabel],
                ['name' => '移行先', 'value' => $toLabel],
            ],
        );
    }

    public static function forRepairReport(RepairReport $report, string $fromLabel, string $toLabel): self
    {
        return new self(
            workflowType: 'repair_report',
            subjectTitle: $report->title,
            fromLabel: $fromLabel,
            toLabel: $toLabel,
            message: "完了報告の承認ワークフローが「{$fromLabel}」から「{$toLabel}」へ進みました。",
            actionUrl: route('repair-reports.show', $report),
            facts: [
                ['name' => '件名', 'value' => $report->title],
                ['name' => '関連トラブルID', 'value' => (string) $report->trouble_id],
                ['name' => '移行元', 'value' => $fromLabel],
                ['name' => '移行先', 'value' => $toLabel],
            ],
        );
    }

    /**
     * @return list<string>
     */
    public function via(object $notifiable): array
    {
        $channels = [];

        if ($notifiable->routeNotificationFor('mail', $this)) {
            $channels[] = 'mail';
        }

        if ($notifiable->routeNotificationFor('teams', $this)) {
            $channels[] = 'teams';
        }

        return $channels;
    }

    public function toMail(object $notifiable): MailMessage
    {
        $mail = (new MailMessage)
            ->subject("[TMQ] {$this->toLabel} への承認依頼: {$this->subjectTitle}")
            ->greeting('TMQ ワークフロー通知')
            ->line($this->message);

        foreach ($this->facts as $fact) {
            $mail->line("{$fact['name']}: {$fact['value']}");
        }

        return $mail
            ->action('詳細を確認', $this->actionUrl)
            ->line('本メールは TMQ から自動送信されています。');
    }

    /**
     * @return array{title: string, text: string, facts: array<int, array{name: string, value: string}>, url: string}
     */
    public function toTeams(object $notifiable): array
    {
        return [
            'title' => "[TMQ] {$this->toLabel} への承認依頼",
            'text' => $this->message,
            'facts' => $this->facts,
            'url' => $this->actionUrl,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'workflow_type' => $this->workflowType,
            'title' => $this->subjectTitle,
            'from' => $this->fromLabel,
            'to' => $this->toLabel,
            'url' => $this->actionUrl,
        ];
    }
}
