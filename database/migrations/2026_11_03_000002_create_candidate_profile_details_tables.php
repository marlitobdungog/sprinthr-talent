<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('candidate_skills', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('candidate_id')->constrained()->cascadeOnDelete();
            $table->string('name', 80);
            $table->string('normalized_name', 80);
            $table->unsignedTinyInteger('experience_years')->nullable();
            $table->timestamps();
            $table->unique(['candidate_id', 'normalized_name']);
            $table->index(['normalized_name', 'candidate_id']);
        });

        Schema::create('candidate_experiences', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('candidate_id')->constrained()->cascadeOnDelete();
            $table->string('title', 140);
            $table->string('company', 140);
            $table->date('started_on')->nullable();
            $table->date('ended_on')->nullable();
            $table->boolean('current_role')->default(false);
            $table->text('description')->nullable();
            $table->unsignedSmallInteger('display_order')->default(0);
            $table->timestamps();
            $table->index(['candidate_id', 'display_order']);
        });

        Schema::create('candidate_educations', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('candidate_id')->constrained()->cascadeOnDelete();
            $table->string('institution', 160);
            $table->string('qualification', 160)->nullable();
            $table->unsignedSmallInteger('started_year')->nullable();
            $table->unsignedSmallInteger('ended_year')->nullable();
            $table->unsignedSmallInteger('display_order')->default(0);
            $table->timestamps();
            $table->index(['candidate_id', 'display_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('candidate_educations');
        Schema::dropIfExists('candidate_experiences');
        Schema::dropIfExists('candidate_skills');
    }
};
