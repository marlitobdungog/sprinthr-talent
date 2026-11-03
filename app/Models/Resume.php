<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Resume extends Model
{
    use HasUuids;

    protected $fillable = [
        'private_object_key', 'original_filename', 'mime_type', 'size_bytes',
        'checksum_sha256', 'version', 'parse_status', 'uploaded_at',
    ];

    protected function casts(): array
    {
        return ['uploaded_at' => 'datetime'];
    }

    public function candidate(): BelongsTo
    {
        return $this->belongsTo(Candidate::class);
    }
}
