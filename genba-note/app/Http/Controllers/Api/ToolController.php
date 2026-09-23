<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Enums\ToolLogAction;
use App\Models\Tool;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class ToolController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $q = $request->string('q')->trim()->toString() ?: null;
        $status = $request->string('status')->trim()->toString() ?: null;

        $tools = Tool::query()
            ->withCount('logs')
            ->when($q, fn ($query) => $query->where(function ($inner) use ($q) {
                $inner->where('name', 'like', "%{$q}%")
                    ->orWhere('code', 'like', "%{$q}%")
                    ->orWhere('location', 'like', "%{$q}%");
            }))
            ->when($status, fn ($query) => $query->where('status', $status))
            ->orderBy('code')
            ->paginate(20);

        return response()->json($tools);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'max:50', 'unique:tools'],
            'name' => ['required', 'string', 'max:100'],
            'status' => ['required', 'string'],
            'quantity' => ['required', 'integer', 'min:0'],
        ]);

        $tool = Tool::create($validated);

        return response()->json($tool, 201);
    }

    public function show(Tool $tool): JsonResponse
    {
        $tool->load(['logs.user']);
        return response()->json($tool);
    }

    public function update(Request $request, Tool $tool): JsonResponse
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'max:50', 'unique:tools,code,' . $tool->id],
            'name' => ['required', 'string', 'max:100'],
            'status' => ['required', 'string'],
            'quantity' => ['required', 'integer', 'min:0'],
        ]);

        $tool->update($validated);

        return response()->json($tool);
    }

    public function storeLog(Request $request, Tool $tool): JsonResponse
    {
        $validated = $request->validate([
            'action' => ['required', 'string'],
            'quantity' => ['required', 'integer', 'min:1'],
            'note' => ['nullable', 'string'],
        ]);

        DB::transaction(function () use ($validated, $tool, $request) {
            $action = ToolLogAction::from($validated['action']);
            $quantity = (int) $validated['quantity'];

            $tool->logs()->create([
                'user_id' => $request->input('user_id', 1),
                'action' => $action,
                'quantity' => $quantity,
                'note' => $validated['note'] ?? null,
            ]);

            $updates = [];
            if ($resulting = $action->resultingStatus()) {
                $updates['status'] = $resulting;
            }

            if ($action === ToolLogAction::Adjust) {
                $updates['quantity'] = $quantity;
            } elseif ($action === ToolLogAction::Checkout) {
                $updates['quantity'] = max(0, $tool->quantity - $quantity);
            } elseif ($action === ToolLogAction::Checkin) {
                $updates['quantity'] = $tool->quantity + $quantity;
            }

            if ($updates !== []) {
                $tool->update($updates);
            }
        });

        return response()->json($tool->load('logs.user'));
    }

    public function destroy(Tool $tool): JsonResponse
    {
        $tool->delete();
        return response()->json(null, 204);
    }
}