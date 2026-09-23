<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Child extends Model
{
    use HasFactory;

    protected $fillable = [
        'child_id', // ★ ここを追加
        'name',
        'birth_date',
        'allergies',          // ★ 追加
        'daily_precautions',  // ★ 追加
        'diagnosis',
        'sensory_tendencies',
        'panic_response_steps',
    ];

    public function supportPlans(): HasMany
    {
        return $this->hasMany(IndividualSupportPlan::class);
    }

    public function supportLogs(): HasMany
    {
        return $this->hasMany(SupportLog::class);
    }

    public function faceProfiles(): HasMany
    {
        return $this->hasMany(ChildFaceProfile::class);
    }

    public function documentations(): HasMany
    {
        return $this->hasMany(Documentation::class);
    }

    public function contactNotes(): HasMany
    {
        return $this->hasMany(ContactNote::class);
    }

    public function napChecks(): HasMany
    {
        return $this->hasMany(NapCheck::class);
    }

    public function dailyAttendances(): HasMany
    {
        return $this->hasMany(DailyAttendance::class);
    }

    public function growthAlbums(): HasMany
    {
        return $this->hasMany(GrowthAlbum::class);
    }

    public function careLogs(): HasMany
    {
        return $this->hasMany(CareLog::class);
    }

    public function allergyRecords(): HasMany
    {
        return $this->hasMany(ChildAllergy::class);
    }
}
