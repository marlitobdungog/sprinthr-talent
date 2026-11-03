<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('resumes', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('candidate_id')->constrained()->cascadeOnDelete();
            $table->string('storage_disk', 40)->default('local');
            $table->string('private_object_key')->unique();
            $table->string('original_filename', 255);
            $table->string('mime_type', 100);
            $table->unsignedBigInteger('size_bytes');
            $table->char('checksum_sha256', 64);
            $table->unsignedInteger('version')->default(1);
            $table->string('parse_status', 32)->default('awaiting_parser');
            $table->timestamp('uploaded_at');
            $table->timestamps();
            $table->index(['candidate_id', 'uploaded_at']);
        });

        Schema::create('candidate_consent_events', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('candidate_id')->constrained()->cascadeOnDelete();
            $table->string('purpose', 40);
            $table->string('action', 20);
            $table->string('policy_version', 40);
            $table->timestamp('created_at')->useCurrent();
            $table->index(['candidate_id', 'purpose', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('candidate_consent_events');
        Schema::dropIfExists('resumes');
    }
};
