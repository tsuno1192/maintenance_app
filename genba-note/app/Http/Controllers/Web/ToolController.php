<?php

namespace App\Http\Controllers\Web;

use App\Enums\ToolLogAction;
use App\Enums\ToolStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Tool\StoreToolLogRequest;
use App\Http\Requests\Tool\StoreToolRequest;
use App\Http\Requests\Tool\UpdateToolRequest;
use App\Models\Tool;
use App\Models\ToolLog;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * 工具リソースの Web コントローラ雛形。
 */
class ToolController extends Controller
{
    /**
     * 工具一覧。
     */
    public function index(Request $request): View
    {
        $tools = Tool::query()
            ->when($request->string('status')->toString(), fn ($q, $status) => $q->where('status', $status))
            ->when($request->string('q')->toString(), function ($q, $keyword) {
                $q->where(function ($inner) use ($keyword) {
                    $inner->where('name', 'like', "%{$keyword}%")
                        ->orWhere('serial_number', 'like', "%{$keyword}%")
                        ->orWhere('current_location', 'like', "%{$keyword}%");
                });
            })
            ->latest()
            ->paginate(12)
            ->withQueryString();

        return view('tools.index', compact('tools'));
    }

    /**
     * 作成フォーム（雛形）。
     */
    public function create(): View
    {
        return view('tools.create');
    }

    /**
     * 工具を登録する。
     */
    public function store(StoreToolRequest $request): RedirectResponse
    {
        $tool = Tool::query()->create($request->validated());

        return redirect()
            ->route('tools.show', $tool)
            ->with('status', '工具を登録しました。');
    }

    /**
     * 工具詳細。
     */
    public function show(Tool $tool): View
    {
        $tool->load(['logs' => fn ($q) => $q->with('user')->latest()->limit(20)]);

        return view('tools.show', compact('tool'));
    }

    /**
     * 編集フォーム（雛形）。
     */
    public function edit(Tool $tool): View
    {
        return view('tools.edit', compact('tool'));
    }

    /**
     * 工具を更新する。
     */
    public function update(UpdateToolRequest $request, Tool $tool): RedirectResponse
    {
        $tool->update($request->validated());

        return redirect()
            ->route('tools.show', $tool)
            ->with('status', '工具を更新しました。');
    }

    /**
     * 工具を削除する。
     */
    public function destroy(Tool $tool): RedirectResponse
    {
        $tool->delete();

        return redirect()
            ->route('tools.index')
            ->with('status', '工具を削除しました。');
    }

    /**
     * 貸出／返却ログを記録し、工具ステータスを同期する。
     */
    public function storeLog(StoreToolLogRequest $request): RedirectResponse
    {
        $tool = DB::transaction(function () use ($request) {
            $data = $request->validated();
            $tool = Tool::query()->lockForUpdate()->findOrFail($data['tool_id']);

            ToolLog::query()->create([
                'tool_id' => $tool->id,
                'user_id' => $request->user()->id,
                'action' => $data['action'],
                'notes' => $data['notes'] ?? null,
            ]);

            $action = $data['action'] instanceof ToolLogAction
                ? $data['action']
                : ToolLogAction::from($data['action']);

            $tool->update([
                'status' => $action === ToolLogAction::Borrowed
                    ? ToolStatus::InUse
                    : ToolStatus::Available,
                'current_location' => $data['current_location']
                    ?? ($action === ToolLogAction::Returned ? '工具室' : $tool->current_location),
            ]);

            return $tool;
        });

        return redirect()
            ->route('tools.show', $tool)
            ->with('status', '工具履歴を記録しました。');
    }
}
