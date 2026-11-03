<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CandidateExperience extends Model
{
    protected $fillable = ['title', 'company', 'started_on', 'ended_on', 'current_role', 'description', 'display_order'];

    protected function casts(): array
    {
        return ['started_on' => 'date:Y-m-d', 'ended_on' => 'date:Y-m-d', 'current_role' => 'boolean'];
    }

    public function candidate(): BelongsTo
    {
        return $this->belongsTo(Candidate::class);
    }
}
