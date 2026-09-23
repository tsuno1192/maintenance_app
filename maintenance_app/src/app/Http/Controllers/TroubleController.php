<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTroubleRequest;
use App\Models\Machine;
use App\Models\Trouble;
use App\Services\MaintenanceRequestPdfService;
use App\Services\TroubleRegistrationService;
use App\Services\TroubleWorkflowService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use InvalidArgumentException;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class TroubleController extends Controller
{
    public function __construct(
        private readonly TroubleRegistrationService $registrationService,
        private readonly TroubleWorkflowService $workflowService,
        private readonly MaintenanceRequestPdfService $pdfService,
    ) {}

    public function index(): View
    {
        $this->authorize('viewAny', Trouble::class);

        $troubles = Trouble::query()
            ->with('machine')
            ->withCount('todos')
            ->latest()
            ->paginate(15);

        return view('troubles.index', compact('troubles'));
    }

    public function create(): View
    {
        $this->authorize('create', Trouble::class);

        return view('troubles.create', [
            'groups' => [
                '運転Gr',
                '保全Gr',
                '電気Gr',
                '計装Gr',
                '機械Gr',
            ],
            'majorCategories' => [
                '設備',
                'ユーティリティ',
                '電気設備',
                '計装設備',
                'その他',
            ],
            'machines' => Machine::query()->orderBy('code')->get(),
        ]);
    }

    public function store(StoreTroubleRequest $request): RedirectResponse
    {
        $this->authorize('create', Trouble::class);

        $data = $request->validated();
        $data['reporter_user_id'] = $request->user()?->id;

        $trouble = $this->registrationService->register($data);

        return redirect()
            ->route('troubles.show', $trouble)
            ->with('success', 'トラブルを登録し、関連グループの TO DO を作成しました。リーダへ通知しました。');
    }

    public function show(Trouble $trouble): View
    {
        $this->authorize('view', $trouble);

        $trouble->load(['todos', 'reporter', 'repairReport', 'machine']);

        return view('troubles.show', compact('trouble'));
    }

    public function pdf(Trouble $trouble): Response
    {
        $this->authorize('downloadPdf', $trouble);

        return $this->pdfService->download($trouble);
    }

    public function approve(Trouble $trouble): RedirectResponse
    {
        $this->authorize('approve', $trouble);

        try {
            $updated = $this->workflowService->advance($trouble);
        } catch (InvalidArgumentException $e) {
            return back()->withErrors(['workflow' => $e->getMessage()]);
        } catch (Throwable $e) {
            report($e);

            return back()->withErrors(['workflow' => '承認処理中にエラーが発生しました。']);
        }

        $message = $updated->status->value === 'completed'
            ? '最終承認が完了しました。完了通知を送信しました。'
            : "承認しました。次の担当（{$updated->status->label()}）へ通知しました。";

        return redirect()
            ->route('troubles.show', $updated)
            ->with('success', $message);
    }
}
