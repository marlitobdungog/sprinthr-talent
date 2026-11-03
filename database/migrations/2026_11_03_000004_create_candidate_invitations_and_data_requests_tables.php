<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('candidate_invitations', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('candidate_id')->constrained()->cascadeOnDelete();
            $table->string('tenant_external_id', 120);
            $table->string('job_external_id', 120);
            $table->string('employer_name', 160);
            $table->string('role_title', 160);
            $table->text('role_summary')->nullable();
            $table->string('status', 20)->default('pending');
            $table->string('idempotency_key', 120)->nullable()->unique();
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('responded_at')->nullable();
            $table->timestamps();
            $table->index(['candidate_id', 'status', 'expires_at']);
        });

        Schema::create('candidate_data_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('candidate_id')->constrained()->cascadeOnDelete();
            $table->string('request_type', 24);
            $table->string('status', 24)->default('pending');
            $table->timestamp('requested_at');
            $table->timestamp('processed_at')->nullable();
            $table->json('retention_exceptions')->nullable();
            $table->timestamps();
            $table->index(['candidate_id', 'request_type', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('candidate_data_requests');
        Schema::dropIfExists('candidate_invitations');
    }
};
