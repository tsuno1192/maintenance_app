<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\Machine\StoreMachineRequest;
use App\Http\Requests\Machine\UpdateMachineRequest;
use App\Models\Machine;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * 設備リソースの Web コントローラ雛形。
 */
class MachineController extends Controller
{
    /**
     * 設備一覧。
     */
    public function index(Request $request): View
    {
        $machines = Machine::query()
            ->withCount('memos')
            ->when($request->string('status')->toString(), fn ($q, $status) => $q->where('status', $status))
            ->when($request->string('q')->toString(), function ($q, $keyword) {
                $q->where(function ($inner) use ($keyword) {
                    $inner->where('name', 'like', "%{$keyword}%")
                        ->orWhere('qr_identifier', 'like', "%{$keyword}%")
                        ->orWhere('location', 'like', "%{$keyword}%");
                });
            })
            ->latest()
            ->paginate(12)
            ->withQueryString();

        return view('machines.index', compact('machines'));
    }

    /**
     * 設備詳細。
     */
    public function show(Machine $machine): View
    {
        $machine->load(['memos' => fn ($q) => $q->latest()->limit(20)]);

        return view('machines.show', compact('machine'));
    }

    /**
     * 作成フォーム（雛形）。
     */
    public function create(): View
    {
        return view('machines.create');
    }

    /**
     * 設備を登録する。
     */
    public function store(StoreMachineRequest $request): RedirectResponse
    {
        $machine = Machine::query()->create($request->validated());

        return redirect()
            ->route('machines.show', $machine)
            ->with('status', '設備を登録しました。');
    }

    /**
     * 編集フォーム（雛形）。
     */
    public function edit(Machine $machine): View
    {
        return view('machines.edit', compact('machine'));
    }

    /**
     * 設備を更新する。
     */
    public function update(UpdateMachineRequest $request, Machine $machine): RedirectResponse
    {
        $machine->update($request->validated());

        return redirect()
            ->route('machines.show', $machine)
            ->with('status', '設備を更新しました。');
    }

    /**
     * 設備を削除する。
     */
    public function destroy(Machine $machine): RedirectResponse
    {
        $machine->delete();

        return redirect()
            ->route('machines.index')
            ->with('status', '設備を削除しました。');
    }
}
