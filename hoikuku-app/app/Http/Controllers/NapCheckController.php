<?php

namespace App\Http\Controllers;

use App\Models\NapAlert;
use App\Models\NapCheck;
use App\Services\NapMonitoringService;
use App\Support\ChildAccess;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NapCheckController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        $children = ChildAccess::visibleChildrenQuery($user)->get(['id', 'name']);

        $checks = NapCheck::with(['child:id,name', 'alert'])
            ->when($user->isParent(), fn ($q) => $q->where('child_id', $user->child_id))
            ->latest('checked_at')
            ->paginate(30);

        $openAlerts = NapAlert::with('child:id,name')
            ->whereNull('acknowledged_at')
            ->when($user->isParent(), fn ($q) => $q->where('child_id', $user->child_id))
            ->latest('id')
            ->limit(20)
            ->get();

        return view('care.naps.index', compact('checks', 'children', 'openAlerts'));
    }

    public function store(Request $request, NapMonitoringService $monitor): RedirectResponse
    {
        abort_unless(ChildAccess::canManageCare($request->user()), 403);

        $validated = $request->validate([
            'child_id' => ['required', 'exists:children,id'],
            'posture' => ['required', 'in:back,side,stomach,unknown'],
            'breathing_status' => ['required', 'in:normal,irregular,none'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        $monitor->record([
            ...$validated,
            'user_id' => $request->user()->id,
            'sensor_source' => 'manual',
        ]);

        return redirect()->route('naps.index')->with('success', '午睡チェックを記録しました。');
    }

    public function acknowledge(Request $request, NapAlert $alert): RedirectResponse
    {
        abort_unless(ChildAccess::canManageCare($request->user()), 403);

        $alert->update([
            'acknowledged_at' => now(),
            'acknowledged_by' => $request->user()->id,
        ]);

        return back()->with('success', 'アラートを確認済みにしました。');
    }

    public function feed(Request $request)
    {
        abort_unless($request->user()->isStaff(), 403);

        $alerts = NapAlert::with('child:id,name')
            ->whereNull('acknowledged_at')
            ->latest('id')
            ->limit(30)
            ->get()
            ->map(fn (NapAlert $alert) => [
                'id' => $alert->id,
                'child_id' => $alert->child_id,
                'child_name' => $alert->child?->name,
                'message' => $alert->message,
                'created_at' => optional($alert->created_at)?->toIso8601String(),
                'ack_url' => route('naps.alerts.ack', $alert),
            ]);

        return response()->json([
            'status' => 'success',
            'data' => $alerts,
        ]);
    }
}
