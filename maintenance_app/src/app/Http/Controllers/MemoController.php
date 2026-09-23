<?php

namespace App\Http\Controllers;

use App\Enums\MemoPriority;
use App\Enums\MemoShift;
use App\Http\Requests\StoreMemoRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class MemoController extends Controller
{
    protected string $baseUrl;

    public function __construct()
    {
        $this->baseUrl = config('services.genba_note.url', 'http://laravel.test/api');
    }

    public function index(Request $request): View
    {
        $response = Http::get("{$this->baseUrl}/memos", [
            'priority' => $request->input('priority'),
            'unacked' => $request->boolean('unacked'),
            'page' => $request->input('page', 1),
        ]);

        $data = $response->successful() ? $response->json() : [];
        $memos = $data['data'] ?? [];

        return view('memos.index', [
            'memos' => $memos,
            'priority' => $request->input('priority'),
            'unacked' => $request->boolean('unacked'),
            'priorities' => MemoPriority::cases(),
        ]);
    }

    public function create(): View
    {
        // 設備一覧も genba-note の API から取得する
        $machinesResponse = Http::get("{$this->baseUrl}/machines");
        $machines = $machinesResponse->successful() ? $machinesResponse->json() : [];

        return view('memos.create', [
            'machines' => $machines,
            'shifts' => MemoShift::cases(),
            'priorities' => MemoPriority::cases(),
        ]);
    }

    public function store(StoreMemoRequest $request): RedirectResponse
    {
        // HTTPクライアントでマルチパート（画像ファイル含む）を送信する場合の構築
        $http = Http::asMultipart();

        // テキストデータを追加
        foreach ($request->safe()->except('images') as $key => $value) {
            if (!is_null($value)) {
                $http->attach($key, $value);
            }
        }

        // 画像ファイルを添付
        foreach ($request->file('images', []) as $file) {
            $http->attach(
                'images[]',
                file_get_contents($file->getRealPath()),
                $file->getClientOriginalName()
            );
        }

        $response = $http->post("{$this->baseUrl}/memos");

        if ($response->successful()) {
            $memo = $response->json();
            return redirect()
                ->route('memos.show', $memo['id'] ?? 1)
                ->with('success', '申し送りを登録しました。');
        }

        return back()->withInput()->with('error', '申し送りの登録に失敗しました。');
    }

    public function show($id): View
    {
        $response = Http::get("{$this->baseUrl}/memos/{$id}");
        $memo = $response->successful() ? $response->json() : null;

        return view('memos.show', compact('memo'));
    }

    public function edit($id): View
    {
        $response = Http::get("{$this->baseUrl}/memos/{$id}");
        $memo = $response->successful() ? $response->json() : null;

        $machinesResponse = Http::get("{$this->baseUrl}/machines");
        $machines = $machinesResponse->successful() ? $machinesResponse->json() : [];

        return view('memos.edit', [
            'memo' => $memo,
            'machines' => $machines,
            'shifts' => MemoShift::cases(),
            'priorities' => MemoPriority::cases(),
        ]);
    }

    public function update(StoreMemoRequest $request, $id): RedirectResponse
    {
        // 更新時も同様にAPIへ送信（PUT/PATCH、またはファイルがある場合はPOST＋_method指定などAPI側の仕様に合わせる）
        $response = Http::put("{$this->baseUrl}/memos/{$id}", $request->safe()->except('images'));

        if ($response->successful()) {
            return redirect()
                ->route('memos.show', $id)
                ->with('success', '申し送りを更新しました。');
        }

        return back()->withInput()->with('error', '申し送りの更新に失敗しました。');
    }

    public function destroy($id): RedirectResponse
    {
        $response = Http::delete("{$this->baseUrl}/memos/{$id}");

        if ($response->successful()) {
            return redirect()
                ->route('memos.index')
                ->with('success', '申し送りを削除しました。');
        }

        return back()->with('error', '申し送りの削除に失敗しました。');
    }

    public function acknowledge(Request $request, $id): RedirectResponse
    {
        $response = Http::post("{$this->baseUrl}/memos/{$id}/acknowledge");

        if ($response->successful()) {
            return back()->with('success', '申し送りを確認しました。');
        }

        return back()->with('error', '確認処理に失敗しました。');
    }

    // 画像のストリーミング表示や削除も、genba-note側のAPI経由で画像URLを取得・転送する形に変更します
}