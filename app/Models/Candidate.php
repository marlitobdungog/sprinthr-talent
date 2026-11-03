<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Candidate extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'user_id', 'headline', 'coarse_location', 'work_preferences',
        'availability', 'summary', 'profile_version', 'discoverable',
    ];

    protected function casts(): array
    {
        return [
            'work_preferences' => 'array',
            'discoverable' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function skills(): HasMany
    {
        return $this->hasMany(CandidateSkill::class);
    }

    public function experiences(): HasMany
    {
        return $this->hasMany(CandidateExperience::class)->orderByDesc('started_on')->orderBy('display_order');
    }

    public function educations(): HasMany
    {
        return $this->hasMany(CandidateEducation::class)->orderByDesc('ended_year')->orderBy('display_order');
    }

    public function resume(): HasOne
    {
        return $this->hasOne(Resume::class)
            ->orderByDesc('uploaded_at')
            ->orderByDesc('created_at')
            ->orderByDesc('id');
    }

    public function invitations(): HasMany
    {
        return $this->hasMany(CandidateInvitation::class);
    }

    public function consentEvents(): HasMany
    {
        return $this->hasMany(CandidateConsentEvent::class);
    }

    public function dataRequests(): HasMany
    {
        return $this->hasMany(CandidateDataRequest::class);
    }

    public function completeness(): int
    {
        $checks = [
            filled($this->user?->name),
            filled($this->headline),
            filled($this->coarse_location),
            $this->skills()->exists(),
            $this->experiences()->exists(),
            $this->educations()->exists(),
            $this->resume()->exists(),
        ];

        return (int) round(count(array_filter($checks)) / count($checks) * 100);
    }
}
