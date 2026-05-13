<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orbit_budget_alerts', function (Blueprint $table) {
            $table->id();
            $table->decimal('threshold_amount', 12, 2);
            $table->string('period')->default('monthly');
            $table->json('channels')->nullable();
            $table->boolean('enabled')->default(true);
            $table->timestamp('last_triggered_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orbit_budget_alerts');
    }
};
