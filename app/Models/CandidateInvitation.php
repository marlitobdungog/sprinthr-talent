<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CandidateInvitation extends Model
{
    use HasUuids;

    protected $fillable = ['status', 'responded_at'];

    protected function casts(): array
    {
        return ['expires_at' => 'datetime', 'responded_at' => 'datetime'];
    }

    public function candidate(): BelongsTo
    {
        return $this->belongsTo(Candidate::class);
    }
}
