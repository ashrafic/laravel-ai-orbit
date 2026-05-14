<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orbit_prompt_lab_sessions', function (Blueprint $table) {
            $table->id();
            $table->text('prompt');
            $table->text('system_prompt')->nullable();
            $table->decimal('temperature', 3, 2)->default(1.0);
            $table->integer('max_tokens')->nullable();
            $table->decimal('top_p', 3, 2)->default(1.0);
            $table->json('context')->nullable();
            $table->json('slots');
            $table->json('results')->nullable();
            $table->json('tags')->nullable();
            $table->string('user_id')->nullable()->index();
            $table->decimal('total_cost', 12, 8)->nullable();
            $table->integer('total_latency_ms')->nullable();
            $table->string('status')->default('pending');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orbit_prompt_lab_sessions');
    }
};
