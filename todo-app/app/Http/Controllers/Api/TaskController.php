<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Task;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'status' => ['sometimes', 'string', 'in:pending,in_progress,done,cancelled'],
            'source' => ['sometimes', 'string', 'max:100'],
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:100'],
        ]);

        $query = Task::query()->latest('id');

        if (! empty($validated['status'])) {
            $query->where('status', $validated['status']);
        }

        if (! empty($validated['source'])) {
            $query->where('source', $validated['source']);
        }

        $tasks = $query->paginate($validated['per_page'] ?? 20);

        return response()->json([
            'status' => 'success',
            'data' => $tasks->items(),
            'meta' => [
                'current_page' => $tasks->currentPage(),
                'last_page' => $tasks->lastPage(),
                'per_page' => $tasks->perPage(),
                'total' => $tasks->total(),
            ],
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'],
            'status' => ['sometimes', 'string', 'in:pending,in_progress,done,cancelled'],
            'due_at' => ['nullable', 'date'],
            'source' => ['nullable', 'string', 'max:100'],
            'external_ref' => ['nullable', 'string', 'max:100'],
        ]);

        $task = Task::create([
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'status' => $validated['status'] ?? 'pending',
            'due_at' => $validated['due_at'] ?? null,
            'source' => $validated['source'] ?? 'hoikuku-app',
            'external_ref' => $validated['external_ref'] ?? null,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Task created.',
            'data' => $task,
        ], 201);
    }

    public function show(Task $task): JsonResponse
    {
        return response()->json([
            'status' => 'success',
            'data' => $task,
        ]);
    }

    public function update(Request $request, Task $task): JsonResponse
    {
        $validated = $request->validate([
            'title' => ['sometimes', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'],
            'status' => ['sometimes', 'string', 'in:pending,in_progress,done,cancelled'],
            'due_at' => ['nullable', 'date'],
            'source' => ['nullable', 'string', 'max:100'],
            'external_ref' => ['nullable', 'string', 'max:100'],
        ]);

        $task->fill($validated)->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Task updated.',
            'data' => $task->fresh(),
        ]);
    }
}
