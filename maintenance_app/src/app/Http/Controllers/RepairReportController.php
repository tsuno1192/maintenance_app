<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreRepairReportRequest;
use App\Models\RepairReport;
use App\Models\Trouble;
use App\Services\RepairReportWorkflowService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use InvalidArgumentException;
use Throwable;

class RepairReportController extends Controller
{
    public function __construct(
        private readonly RepairReportWorkflowService $workflowService,
    ) {}

    public function create(Trouble $trouble): View|RedirectResponse
    {
        $this->authorize('create', RepairReport::class);

        if ($trouble->repairReport()->exists()) {
            return redirect()
                ->route('repair-reports.show', $trouble->repairReport)
                ->with('success', '完了報告は既に登録されています。');
        }

        return view('repair_reports.create', [
            'trouble' => $trouble,
            'defaults' => [
                'category_major' => $trouble->category_major,
                'category_middle' => $trouble->category_middle,
                'category_minor' => $trouble->category_minor,
                'title' => $trouble->title,
                'content' => $trouble->content,
                'investigation_result' => $trouble->investigation,
                'cause' => $trouble->estimated_cause,
                'used_spare_parts' => $trouble->required_spare_parts,
                'repaired_on' => now()->toDateString(),
            ],
        ]);
    }

    public function store(StoreRepairReportRequest $request, Trouble $trouble): RedirectResponse
    {
        $this->authorize('create', RepairReport::class);

        try {
            $report = $this->workflowService->register($trouble, $request->validated());
        } catch (InvalidArgumentException $e) {
            return back()->withErrors(['workflow' => $e->getMessage()])->withInput();
        }

        return redirect()
            ->route('repair-reports.show', $report)
            ->with('success', '完了報告を登録しました。リーダへ通知しました。');
    }

    public function show(RepairReport $repairReport): View
    {
        $this->authorize('view', $repairReport);

        $repairReport->load('trouble');

        return view('repair_reports.show', [
            'report' => $repairReport,
        ]);
    }

    public function approve(RepairReport $repairReport): RedirectResponse
    {
        $this->authorize('approve', $repairReport);

        try {
            $updated = $this->workflowService->advance($repairReport);
        } catch (InvalidArgumentException $e) {
            return back()->withErrors(['workflow' => $e->getMessage()]);
        } catch (Throwable $e) {
            report($e);

            return back()->withErrors(['workflow' => '承認処理中にエラーが発生しました。']);
        }

        $message = $updated->status->value === 'completed'
            ? '完了報告の最終承認が完了しました。完了通知を送信しました。'
            : "承認しました。次の担当（{$updated->status->label()}）へ通知しました。";

        return redirect()
            ->route('repair-reports.show', $updated)
            ->with('success', $message);
    }
}
