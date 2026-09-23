<?php

namespace App\Models;

use App\Enums\TroubleStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Trouble extends Model
{
    /** @use HasFactory<\Database\Factories\TroubleFactory> */
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'category_major',
        'category_middle',
        'category_minor',
        'title',
        'content',
        'investigation',
        'estimated_cause',
        'occurred_on',
        'repair_requested_on',
        'required_spare_parts',
        'required_drawings',
        'created_group',
        'reporter_name',
        'reporter_user_id',
        'machine_id',
        'status',
        'discoverer_approved',
        'discoverer_approved_at',
        'leader_approved',
        'leader_approved_at',
        'ops_manager_approved',
        'ops_manager_approved_at',
        'maintenance_leader_approved',
        'maintenance_leader_approved_at',
        'maintenance_manager_approved',
        'maintenance_manager_approved_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => TroubleStatus::class,
            'occurred_on' => 'date',
            'repair_requested_on' => 'date',
            'discoverer_approved' => 'boolean',
            'discoverer_approved_at' => 'datetime',
            'leader_approved' => 'boolean',
            'leader_approved_at' => 'datetime',
            'ops_manager_approved' => 'boolean',
            'ops_manager_approved_at' => 'datetime',
            'maintenance_leader_approved' => 'boolean',
            'maintenance_leader_approved_at' => 'datetime',
            'maintenance_manager_approved' => 'boolean',
            'maintenance_manager_approved_at' => 'datetime',
        ];
    }

    public function reporter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reporter_user_id');
    }

    public function machine(): BelongsTo
    {
        return $this->belongsTo(Machine::class);
    }

    public function repairReport(): HasOne
    {
        return $this->hasOne(RepairReport::class);
    }

    public function todos(): HasMany
    {
        return $this->hasMany(Todo::class);
    }
}
