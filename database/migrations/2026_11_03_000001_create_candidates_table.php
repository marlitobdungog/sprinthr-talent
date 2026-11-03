<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('candidates', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('headline', 140)->nullable();
            $table->string('coarse_location', 120)->nullable();
            $table->json('work_preferences')->nullable();
            $table->string('availability', 40)->nullable();
            $table->text('summary')->nullable();
            $table->unsignedInteger('profile_version')->default(1);
            $table->boolean('discoverable')->default(false)->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('candidates');
    }
};
