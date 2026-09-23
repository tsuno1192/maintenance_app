<?php

namespace App\Http\Controllers;

use App\Enums\ToolStatus;
use App\Enums\TroubleStatus;
use App\Models\Machine;
use App\Models\Memo;
use App\Models\Todo;
use App\Models\Tool;
use App\Models\Trouble;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        $openTroubles = Trouble::query()
            ->where('status', '!=', TroubleStatus::Completed)
            ->count();

        $pendingTodos = Todo::query()
            ->where('is_completed', false)
            ->count();

        $unackedMemos = Memo::query()
            ->whereNull('acknowledged_at')
            ->count();

        $toolsInUse = Tool::query()
            ->where('status', ToolStatus::InUse)
            ->count();

        $recentTroubles = Trouble::query()
            ->with('machine')
            ->latest()
            ->limit(5)
            ->get();

        $recentMemos = Memo::query()
            ->with(['user', 'machine'])
            ->latest()
            ->limit(5)
            ->get();

        return view('dashboard', [
            'stats' => [
                'troubles' => $openTroubles,
                'todos' => $pendingTodos,
                'memos' => $unackedMemos,
                'tools' => $toolsInUse,
                'machines' => Machine::query()->count(),
            ],
            'recentTroubles' => $recentTroubles,
            'recentMemos' => $recentMemos,
        ]);
    }
}
