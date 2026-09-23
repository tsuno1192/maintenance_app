<?php

namespace App\Http\Controllers;

use App\Models\DailyAttendance;
use App\Support\ChildAccess;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DailyAttendanceController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        $date = $request->query('date', now()->toDateString());
        $children = ChildAccess::visibleChildrenQuery($user)->get(['id', 'name']);

        $attendances = DailyAttendance::with(['child:id,name', 'reporter:id,name'])
            ->whereDate('attendance_date', $date)
            ->when($user->isParent(), fn ($q) => $q->where('child_id', $user->child_id))
            ->orderBy('child_id')
            ->paginate(50);

        return view('care.attendance.index', compact('attendances', 'children', 'date'));
    }

    public function upsert(Request $request): RedirectResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'child_id' => ['required', 'exists:children,id'],
            'attendance_date' => ['required', 'date'],
            'status' => ['required', 'in:attending,absent,late'],
            'temperature' => ['nullable', 'numeric', 'between:34,42'],
            'pickup_eta' => ['nullable', 'date_format:H:i'],
            'pickup_note' => ['nullable', 'string', 'max:255'],
        ]);

        $childId = (int) $validated['child_id'];
        abort_unless(
            $user->isStaff() || ($user->isParent() && (int) $user->child_id === $childId),
            403
        );

        $payload = [
            'status' => $validated['status'],
            'pickup_eta' => $validated['pickup_eta'] ?? null,
            'pickup_note' => $validated['pickup_note'] ?? null,
            'reported_by' => $user->id,
        ];

        if (array_key_exists('temperature', $validated) && $validated['temperature'] !== null) {
            $payload['temperature'] = $validated['temperature'];
            $payload['temperature_reported_at'] = now();
        }

        DailyAttendance::updateOrCreate(
            [
                'child_id' => $childId,
                'attendance_date' => $validated['attendance_date'],
            ],
            $payload
        );

        return redirect()
            ->route('attendance.index', ['date' => $validated['attendance_date']])
            ->with('success', '登園・検温情報を更新しました。');
    }
}
