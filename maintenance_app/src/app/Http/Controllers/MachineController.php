<?php

namespace App\Http\Controllers;

use App\Enums\MachineStatus;
use App\Http\Requests\StoreMachineRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\View\View;

class MachineController extends Controller
{
    protected string $baseUrl;

    public function __construct()
    {
        // genba-note の APIベースURL（環境変数や設定ファイルで管理）
        $this->baseUrl = config('services.genba_note.url', 'http://laravel.test/api');
    }

    public function index(Request $request): View
    {
        // genba-note の API へ検索・フィルターパラメータを渡してリクエスト
        $response = Http::get("{$this->baseUrl}/machines", [
            'q' => $request->input('q'),
            'area' => $request->input('area'),
            'status' => $request->input('status'),
            'page' => $request->input('page', 1),
        ]);

        $data = $response->successful() ? $response->json() : [];
        $machines = $data['data'] ?? []; // APIのレスポンス構造（Resource等）に合わせて調整
        
        // エリアの一覧などもAPI経由、あるいは共通マスタとして取得するのが理想ですが、
        // 必要に応じてAPIから取得するか固定値・別エンドポイントから取得します。
        return view('machines.index', [
            'machines' => $machines,
            'areas' => [], // 必要に応じて genba-note から取得
            'q' => $request->input('q'),
            'area' => $request->input('area'),
            'status' => $request->input('status'),
            'statuses' => MachineStatus::cases(),
        ]);
    }

    public function create(): View
    {
        return view('machines.create', [
            'statuses' => MachineStatus::cases(),
        ]);
    }

    public function store(StoreMachineRequest $request): RedirectResponse
    {
        $response = Http::post("{$this->baseUrl}/machines", $request->validated());

        if ($response->successful()) {
            $machine = $response->json();
            return redirect()
                ->route('machines.show', $machine['id'] ?? 1)
                ->with('success', '設備を登録しました。');
        }

        return back()->withInput()->with('error', '設備の登録に失敗しました。');
    }

    public function show($id): View
    {
        $response = Http::get("{$this->baseUrl}/machines/{$id}");
        $machine = $response->successful() ? $response->json() : null;

        return view('machines.show', compact('machine'));
    }

    public function edit($id): View
    {
        $response = Http::get("{$this->baseUrl}/machines/{$id}");
        $machine = $response->successful() ? $response->json() : null;

        return view('machines.edit', [
            'machine' => $machine,
            'statuses' => MachineStatus::cases(),
        ]);
    }

    public function update(StoreMachineRequest $request, $id): RedirectResponse
    {
        $response = Http::put("{$this->baseUrl}/machines/{$id}", $request->validated());

        if ($response->successful()) {
            return redirect()
                ->route('machines.show', $id)
                ->with('success', '設備情報を更新しました。');
        }

        return back()->withInput()->with('error', '設備情報の更新に失敗しました。');
    }

    public function destroy($id): RedirectResponse
    {
        $response = Http::delete("{$this->baseUrl}/machines/{$id}");

        if ($response->successful()) {
            return redirect()
                ->route('machines.index')
                ->with('success', '設備を削除しました。');
        }

        return back()->with('error', '設備の削除に失敗しました。');
    }
}