<?php

namespace App\Http\Controllers\Web;

use App\Enums\MachineStatus;
use App\Enums\MemoStatus;
use App\Enums\ToolStatus;
use App\Http\Controllers\Controller;
use App\Models\Machine;
use App\Models\Memo;
use App\Models\Tool;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

/**
 * ログイン後ダッシュボード（現場の状況サマリ）。
 */
class DashboardController extends Controller
{
    /**
     * ダッシュボードを表示する。
     */
    public function __invoke(Request $request): View
    {
        return view('dashboard', [
            'machineCounts' => [
                'operational' => Machine::query()->where('status', MachineStatus::Operational)->count(),
                'down' => Machine::query()->where('status', MachineStatus::Down)->count(),
                'maintenance' => Machine::query()->where('status', MachineStatus::Maintenance)->count(),
            ],
            'pendingMemos' => Memo::query()
                ->with(['machine', 'user'])
                ->where('status', MemoStatus::Pending)
                ->latest()
                ->limit(5)
                ->get(),
            'toolCounts' => [
                'available' => Tool::query()->where('status', ToolStatus::Available)->count(),
                'in_use' => Tool::query()->where('status', ToolStatus::InUse)->count(),
                'maintenance' => Tool::query()->where('status', ToolStatus::Maintenance)->count(),
            ],
        ]);
    }
}
