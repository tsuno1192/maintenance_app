<?php

namespace App\Services;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class PythonEngineClient
{
    public function __construct(
        private readonly ?string $baseUrl = null,
    ) {}

    protected function url(string $path): string
    {
        $base = rtrim($this->baseUrl ?: config('tmq.python_engine.url', env('PYTHON_ENGINE_URL', 'http://python_engine:8000')), '/');

        return $base.'/'.ltrim($path, '/');
    }

    /**
     * @param  array<int, array<string, mixed>>  $troubles
     * @param  array<int, array<string, mixed>>|null  $monitoring
     * @return array<string, mixed>
     */
    public function planInspections(array $troubles, ?array $monitoring = null): array
    {
        $payload = [
            'troubles' => $troubles,
            'monitoring' => $monitoring,
            'options' => [
                'default_cycle_days' => (int) config('tmq.python_engine.default_cycle_days', 180),
                'min_cycle_days' => (int) config('tmq.python_engine.min_cycle_days', 7),
                'max_cycle_days' => (int) config('tmq.python_engine.max_cycle_days', 365),
            ],
        ];

        try {
            $response = Http::timeout((int) config('tmq.python_engine.timeout', 30))
                ->acceptJson()
                ->post($this->url('/inspection/plan'), $payload);
        } catch (ConnectionException $e) {
            throw new RuntimeException('Python Engine に接続できません: '.$e->getMessage(), 0, $e);
        }

        if (! $response->successful()) {
            throw new RuntimeException(
                'Python Engine エラー (HTTP '.$response->status().'): '.$response->body()
            );
        }

        /** @var array<string, mixed> $json */
        $json = $response->json();

        return $json;
    }

    public function health(): bool
    {
        try {
            $response = Http::timeout(5)->get($this->url('/health'));

            return $response->successful();
        } catch (ConnectionException) {
            return false;
        }
    }
}
