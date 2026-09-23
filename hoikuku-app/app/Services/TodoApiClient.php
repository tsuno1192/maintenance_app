<?php

namespace App\Services;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class TodoApiClient
{
    private string $baseUrl;

    private string $token;

    private int $timeout;

    public function __construct(?string $baseUrl = null, ?string $token = null, ?int $timeout = null)
    {
        $this->baseUrl = rtrim($baseUrl ?? (string) config('services.todo.base_url'), '/');
        $this->token = $token ?? (string) config('services.todo.token');
        $this->timeout = $timeout ?? (int) config('services.todo.timeout', 10);

        if ($this->baseUrl === '' || $this->token === '') {
            throw new RuntimeException('TODO_APP_URL / TODO_API_TOKEN が設定されていません。');
        }
    }

    /**
     * @param  array<string, mixed>  $query
     * @return array<string, mixed>
     */
    public function listTasks(array $query = []): array
    {
        return $this->request()
            ->get('/api/tasks', array_filter($query, fn ($v) => $v !== null && $v !== ''))
            ->throw()
            ->json();
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    public function createTask(array $payload): array
    {
        return $this->request()->post('/api/tasks', $payload)->throw()->json();
    }

    /**
     * @return array<string, mixed>
     */
    public function getTask(int|string $id): array
    {
        return $this->request()->get('/api/tasks/'.$id)->throw()->json();
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    public function updateTask(int|string $id, array $payload): array
    {
        return $this->request()->patch('/api/tasks/'.$id, $payload)->throw()->json();
    }

    private function request(): PendingRequest
    {
        return Http::baseUrl($this->baseUrl)
            ->acceptJson()
            ->withToken($this->token)
            ->timeout($this->timeout)
            ->retry(2, 200, throw: false);
    }
}
