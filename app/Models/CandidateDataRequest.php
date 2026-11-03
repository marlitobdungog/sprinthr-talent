<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CandidateDataRequest extends Model
{
    protected $fillable = ['request_type', 'status', 'requested_at', 'processed_at', 'retention_exceptions'];

    protected function casts(): array
    {
        return ['requested_at' => 'datetime', 'processed_at' => 'datetime', 'retention_exceptions' => 'array'];
    }

    public function candidate(): BelongsTo
    {
        return $this->belongsTo(Candidate::class);
    }
}
