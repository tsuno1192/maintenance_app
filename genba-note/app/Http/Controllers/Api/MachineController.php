<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Machine;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class MachineController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $q = $request->string('q')->trim()->toString() ?: null;
        $area = $request->string('area')->trim()->toString() ?: null;
        $status = $request->string('status')->trim()->toString() ?: null;

        $machines = Machine::query()
            ->withCount(['memos', 'troubles'])
            ->when($q, fn ($query) => $query->where(function ($inner) use ($q) {
                $inner->where('name', 'like', "%{$q}%")
                    ->orWhere('code', 'like', "%{$q}%")
                    ->orWhere('model', 'like', "%{$q}%");
            }))
            ->when($area, fn ($query) => $query->where('area', $area))
            ->when($status, fn ($query) => $query->where('status', $status))
            ->orderBy('code')
            ->paginate(20);

        return response()->json($machines);
    }

    public function store(Request $request): JsonResponse
    {
        // バリデーションは FormRequest を使ってもOKです
        $validated = $request->validate([
            'code' => ['required', 'string', 'max:50', 'unique:machines'],
            'name' => ['required', 'string', 'max:100'],
            'status' => ['required', 'string'],
        ]);

        $machine = Machine::create($validated);

        return response()->json($machine, 201);
    }

    public function show(Machine $machine): JsonResponse
    {
        $machine->load([
            'memos' => fn ($q) => $q->with('user')->latest()->limit(10),
            'troubles' => fn ($q) => $q->latest()->limit(10),
        ]);

        return response()->json($machine);
    }

    public function update(Request $request, Machine $machine): JsonResponse
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'max:50', 'unique:machines,code,' . $machine->id],
            'name' => ['required', 'string', 'max:100'],
            'status' => ['required', 'string'],
        ]);

        $machine->update($validated);

        return response()->json($machine);
    }

    public function destroy(Machine $machine): JsonResponse
    {
        $machine->delete();

        return response()->json(null, 204);
    }
}