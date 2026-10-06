<?php

namespace App\Http\Controllers;

use App\Enums\MemoPriority;
use App\Enums\MemoShift;
use App\Http\Requests\StoreMemoRequest;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class MemoController extends Controller
{
    protected string $baseUrl;

    public function __construct()
    {
        $this->baseUrl = config('services.genba_note.url', 'http://laravel.test/api');
    }

    public function index(Request $request): View
    {
        $query = \App\Models\Memo::query();

        if ($request->filled('priority')) {
            $query->where('priority', $request->input('priority'));
        }

        if ($request->boolean('unacked')) {
            $query->whereNull('acknowledged_at');
        }

        $memos = $query->latest()->paginate(10);

        return view('memos.index', [
            'memos' => $memos,
            'priority' => $request->input('priority'),
            'unacked' => $request->boolean('unacked'),
            'priorities' => MemoPriority::cases(),
        ]);
    }

    public function create(): View
    {
        try {
            $machinesResponse = Http::timeout(5)
                ->retry(3, 100)
                ->get("{$this->baseUrl}/machines");

            $machinesData = $machinesResponse->successful() ? $machinesResponse->json() : [];
            // ページネーションの 'data' 配列を取り出す（存在しない場合は空配列）
            $machines = $machinesData['data'] ?? [];
        } catch (ConnectionException $e) {
            Log::error('API Connection Timeout (Memo Create Machines): ' . $e->getMessage());
            $machines = [];
        }

        return view('memos.create', [
            'machines' => $machines,
            'shifts' => MemoShift::cases(),
            'priorities' => MemoPriority::cases(),
        ]);
    }

    public function store(StoreMemoRequest $request): RedirectResponse
    {
        try {
            $http = Http::asMultipart()->timeout(10)->retry(3, 100);

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

            return back()->withInput()->with('error', '申し送りの登録に失敗しました: ' . $response->body());
        } catch (ConnectionException $e) {
            Log::error('API Connection Error (Store Memo): ' . $e->getMessage());
            return back()->withInput()->with('error', 'APIサーバーへの接続がタイムアウトしました。');
        }
    }

    public function show($id): View
    {
        try {
            $response = Http::timeout(5)
                ->retry(3, 100)
                ->get("{$this->baseUrl}/memos/{$id}");
            $memo = $response->successful() ? $response->json() : null;
        } catch (ConnectionException $e) {
            Log::error('API Connection Error (Show Memo): ' . $e->getMessage());
            $memo = null;
        }

        return view('memos.show', compact('memo'));
    }

    public function edit($id): View
    {
        try {
            $response = Http::timeout(5)
                ->retry(3, 100)
                ->get("{$this->baseUrl}/memos/{$id}");
            $memo = $response->successful() ? $response->json() : null;

            $machinesResponse = Http::timeout(5)
                ->retry(3, 100)
                ->get("{$this->baseUrl}/machines");

            $machinesData = $machinesResponse->successful() ? $machinesResponse->json() : [];
            // ページネーションの 'data' 配列を取り出す
            $machines = $machinesData['data'] ?? [];
        } catch (ConnectionException $e) {
            Log::error('API Connection Error (Edit Memo): ' . $e->getMessage());
            $memo = null;
            $machines = [];
        }

        return view('memos.edit', [
            'memo' => $memo,
            'machines' => $machines,
            'shifts' => MemoShift::cases(),
            'priorities' => MemoPriority::cases(),
        ]);
    }

    public function update(StoreMemoRequest $request, $id): RedirectResponse
    {
        try {
            $response = Http::timeout(5)
                ->retry(3, 100)
                ->put("{$this->baseUrl}/memos/{$id}", $request->safe()->except('images'));

            if ($response->successful()) {
                return redirect()
                    ->route('memos.show', $id)
                    ->with('success', '申し送りを更新しました。');
            }

            return back()->withInput()->with('error', '申し送りの更新に失敗しました。');
        } catch (ConnectionException $e) {
            Log::error('API Connection Error (Update Memo): ' . $e->getMessage());
            return back()->withInput()->with('error', 'APIサーバーへの接続がタイムアウトしました。');
        }
    }

    public function destroy($id): RedirectResponse
    {
        try {
            $response = Http::timeout(5)
                ->retry(3, 100)
                ->delete("{$this->baseUrl}/memos/{$id}");

            if ($response->successful()) {
                return redirect()
                    ->route('memos.index')
                    ->with('success', '申し送りを削除しました。');
            }

            return back()->with('error', '申し送りの削除に失敗しました。');
        } catch (ConnectionException $e) {
            Log::error('API Connection Error (Destroy Memo): ' . $e->getMessage());
            return back()->with('error', 'APIサーバーへの接続がタイムアウトしました。');
        }
    }

    public function acknowledge(Request $request, int $id): RedirectResponse
    {
        $memo = \App\Models\Memo::findOrFail($id);

        // 既読日時（acknowledged_at）を現在時刻で更新
        $memo->update([
            'acknowledged_at' => now(),
        ]);

        return back()->with('success', '申し送りを確認しました。');
    }
}
