<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DailyAttendance extends Model
{
    public const STATUS_ATTENDING = 'attending';

    public const STATUS_ABSENT = 'absent';

    public const STATUS_LATE = 'late';

    protected $fillable = [
        'child_id',
        'attendance_date',
        'status',
        'temperature',
        'temperature_reported_at',
        'pickup_eta',
        'pickup_note',
        'reported_by',
    ];

    protected function casts(): array
    {
        return [
            'attendance_date' => 'date',
            'temperature' => 'float',
            'temperature_reported_at' => 'datetime',
        ];
    }

    public function child(): BelongsTo
    {
        return $this->belongsTo(Child::class);
    }

    public function reporter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reported_by');
    }
}
