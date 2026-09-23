<?php

namespace App\Models;

use App\Enums\RepairReportStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RepairReport extends Model
{
    /** @use HasFactory<\Database\Factories\RepairReportFactory> */
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'trouble_id',
        'category_major',
        'category_middle',
        'category_minor',
        'title',
        'content',
        'investigation_result',
        'cause',
        'repaired_on',
        'used_spare_parts',
        'used_drawings',
        'trial_run_result',
        'operation_records',
        'status',
        'maintenance_staff_approved',
        'maintenance_staff_approved_at',
        'leader_approved',
        'leader_approved_at',
        'manager_approved',
        'manager_approved_at',
        'ops_leader_approved',
        'ops_leader_approved_at',
        'ops_manager_approved',
        'ops_manager_approved_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => RepairReportStatus::class,
            'repaired_on' => 'date',
            'maintenance_staff_approved' => 'boolean',
            'maintenance_staff_approved_at' => 'datetime',
            'leader_approved' => 'boolean',
            'leader_approved_at' => 'datetime',
            'manager_approved' => 'boolean',
            'manager_approved_at' => 'datetime',
            'ops_leader_approved' => 'boolean',
            'ops_leader_approved_at' => 'datetime',
            'ops_manager_approved' => 'boolean',
            'ops_manager_approved_at' => 'datetime',
        ];
    }

    public function trouble(): BelongsTo
    {
        return $this->belongsTo(Trouble::class);
    }
}
