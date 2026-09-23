<?php

namespace App\Http\Controllers;

use App\Enums\ToolLogAction;
use App\Enums\ToolStatus;
use App\Http\Requests\StoreToolLogRequest;
use App\Http\Requests\StoreToolRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\View\View;

class ToolController extends Controller
{
    protected string $baseUrl;

    public function __construct()
    {
        $this->baseUrl = config('services.genba_note.url', 'http://laravel.test/api');
    }

    public function index(Request $request): View
    {
        $response = Http::get("{$this->baseUrl}/tools", [
            'q' => $request->input('q'),
            'status' => $request->input('status'),
            'page' => $request->input('page', 1),
        ]);

        $data = $response->successful() ? $response->json() : [];
        $tools = $data['data'] ?? [];

        return view('tools.index', [
            'tools' => $tools,
            'q' => $request->input('q'),
            'status' => $request->input('status'),
            'statuses' => ToolStatus::cases(),
        ]);
    }

    public function create(): View
    {
        return view('tools.create', [
            'statuses' => ToolStatus::cases(),
        ]);
    }

    public function store(StoreToolRequest $request): RedirectResponse
    {
        $response = Http::post("{$this->baseUrl}/tools", $request->validated());

        if ($response->successful()) {
            $tool = $response->json();
            return redirect()
                ->route('tools.show', $tool['id'] ?? 1)
                ->with('success', '工具を登録しました。');
        }

        return back()->withInput()->with('error', '工具の登録に失敗しました。');
    }

    public function show($id): View
    {
        $response = Http::get("{$this->baseUrl}/tools/{$id}");
        $tool = $response->successful() ? $response->json() : null;

        return view('tools.show', [
            'tool' => $tool,
            'actions' => ToolLogAction::cases(),
        ]);
    }

    public function edit($id): View
    {
        $response = Http::get("{$this->baseUrl}/tools/{$id}");
        $tool = $response->successful() ? $response->json() : null;

        return view('tools.edit', [
            'tool' => $tool,
            'statuses' => ToolStatus::cases(),
        ]);
    }

    public function update(StoreToolRequest $request, $id): RedirectResponse
    {
        $response = Http::put("{$this->baseUrl}/tools/{$id}", $request->validated());

        if ($response->successful()) {
            return redirect()
                ->route('tools.show', $id)
                ->with('success', '工具情報を更新しました。');
        }

        return back()->withInput()->with('error', '工具情報の更新に失敗しました。');
    }

    public function destroy($id): RedirectResponse
    {
        $response = Http::delete("{$this->baseUrl}/tools/{$id}");

        if ($response->successful()) {
            return redirect()
                ->route('tools.index')
                ->with('success', '工具を削除しました。');
        }

        return back()->with('error', '工具の削除に失敗しました。');
    }

    public function storeLog(StoreToolLogRequest $request, $id): RedirectResponse
    {
        // ログの記録や在庫数の増減ロジックは genba-note 側の API で一括処理し、
        // maintenance_app 側はリクエストを転送する形にします。
        $response = Http::post("{$this->baseUrl}/tools/{$id}/logs", $request->validated());

        if ($response->successful()) {
            return redirect()
                ->route('tools.show', $id)
                ->with('success', '工具の履歴を記録しました。');
        }

        return back()->withInput()->with('error', '工具履歴の記録に失敗しました。');
    }
}