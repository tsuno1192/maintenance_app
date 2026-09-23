<?php

namespace App\Notifications\Channels;

use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TeamsWebhookChannel
{
    /**
     * Microsoft Teams Incoming Webhook へ Adaptive Card / MessageCard を送信する。
     */
    public function send(mixed $notifiable, Notification $notification): void
    {
        if (! method_exists($notification, 'toTeams')) {
            return;
        }

        /** @var array{title?: string, text?: string, facts?: array<int, array{name: string, value: string}>, url?: string}|null $payload */
        $payload = $notification->toTeams($notifiable);

        if ($payload === null) {
            return;
        }

        $webhookUrl = $notifiable->routeNotificationFor('teams', $notification)
            ?? data_get($payload, 'webhook_url');

        if (! is_string($webhookUrl) || $webhookUrl === '') {
            Log::info('Teams webhook skipped: URL not configured.', [
                'notification' => $notification::class,
            ]);

            return;
        }

        $card = [
            '@type' => 'MessageCard',
            '@context' => 'http://schema.org/extensions',
            'summary' => $payload['title'] ?? 'TMQ Notification',
            'themeColor' => '0F6A5A',
            'title' => $payload['title'] ?? 'TMQ Notification',
            'text' => $payload['text'] ?? '',
            'sections' => [
                [
                    'facts' => collect($payload['facts'] ?? [])
                        ->map(fn (array $fact) => [
                            'name' => $fact['name'],
                            'value' => $fact['value'],
                        ])
                        ->values()
                        ->all(),
                ],
            ],
        ];

        if (! empty($payload['url'])) {
            $card['potentialAction'] = [
                [
                    '@type' => 'OpenUri',
                    'name' => '詳細を開く',
                    'targets' => [
                        ['os' => 'default', 'uri' => $payload['url']],
                    ],
                ],
            ];
        }

        $response = Http::timeout(10)->post($webhookUrl, $card);

        if (! $response->successful()) {
            Log::warning('Teams webhook failed.', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
        }
    }
}
