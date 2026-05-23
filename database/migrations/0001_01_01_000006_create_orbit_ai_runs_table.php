<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orbit_ai_runs', function (Blueprint $table) {
            $table->id();
            $table->uuid('invocation_id')->nullable()->index();
            $table->string('operation')->index();
            $table->string('status')->default('running')->index();
            $table->string('provider')->nullable()->index();
            $table->string('model')->nullable()->index();
            $table->string('agent_class')->nullable()->index();
            $table->string('user_id')->nullable()->index();
            $table->string('conversation_id')->nullable()->index();
            $table->unsignedInteger('input_tokens')->default(0);
            $table->unsignedInteger('output_tokens')->default(0);
            $table->decimal('cost', 12, 6)->default(0);
            $table->boolean('priced')->default(false)->index();
            $table->boolean('missing_pricing')->default(false)->index();
            $table->unsignedInteger('latency_ms')->nullable();
            $table->json('payload')->nullable();
            $table->json('usage')->nullable();
            $table->json('events')->nullable();
            $table->text('error')->nullable();
            $table->timestamp('started_at')->nullable()->index();
            $table->timestamp('completed_at')->nullable()->index();
            $table->timestamps();

            $table->unique('invocation_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orbit_ai_runs');
    }
};
