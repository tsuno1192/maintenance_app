<?php

namespace App\Http\Controllers;

use App\Services\PythonEngineClient;
use App\Services\TroubleAnalyticsService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use RuntimeException;
use Throwable;

class AnalyticsController extends Controller
{
    public function __construct(
        private readonly TroubleAnalyticsService $analyticsService,
        private readonly PythonEngineClient $pythonEngine,
    ) {}

    public function index(Request $request): View
    {
        $from = $request->filled('from')
            ? Carbon::parse($request->string('from')->toString())->startOfDay()
            : null;
        $to = $request->filled('to')
            ? Carbon::parse($request->string('to')->toString())->endOfDay()
            : null;

        $aggregates = $this->analyticsService->aggregateByCategory($from, $to);
        $summary = $this->analyticsService->summary($aggregates);
        $engineOnline = $this->pythonEngine->health();

        return view('analytics.index', [
            'aggregates' => $aggregates,
            'summary' => $summary,
            'from' => $from?->toDateString(),
            'to' => $to?->toDateString(),
            'engineOnline' => $engineOnline,
            'plans' => session('inspection_plans'),
            'planMeta' => session('inspection_plan_meta'),
        ]);
    }

    public function planInspections(Request $request): RedirectResponse
    {
        $from = $request->filled('from')
            ? Carbon::parse($request->string('from')->toString())->startOfDay()
            : null;
        $to = $request->filled('to')
            ? Carbon::parse($request->string('to')->toString())->endOfDay()
            : null;

        $aggregates = $this->analyticsService->aggregateByCategory($from, $to);

        if ($aggregates->isEmpty()) {
            return back()->withErrors(['analytics' => '集計対象のトラブルがありません。']);
        }

        try {
            $result = $this->pythonEngine->planInspections($aggregates->all());
        } catch (RuntimeException $e) {
            return back()->withErrors(['analytics' => $e->getMessage()]);
        } catch (Throwable $e) {
            report($e);

            return back()->withErrors(['analytics' => '点検周期の自動計画に失敗しました。']);
        }

        return redirect()
            ->route('analytics.index', array_filter([
                'from' => $from?->toDateString(),
                'to' => $to?->toDateString(),
            ]))
            ->with('success', 'Python Engine による推奨点検計画を取得しました。')
            ->with('inspection_plans', $result['plans'] ?? [])
            ->with('inspection_plan_meta', [
                'generated_at' => $result['generated_at'] ?? now()->toIso8601String(),
                'monitoring_source' => $result['monitoring_source'] ?? 'sample',
                'notes' => $result['notes'] ?? null,
            ]);
    }
}
