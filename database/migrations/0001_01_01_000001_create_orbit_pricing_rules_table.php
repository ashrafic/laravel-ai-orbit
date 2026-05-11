<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orbit_pricing_rules', function (Blueprint $table) {
            $table->id();
            $table->string('model')->index();
            $table->string('provider')->nullable()->index();
            $table->decimal('input_cost_per_1m', 12, 8)->default(0);
            $table->decimal('output_cost_per_1m', 12, 8)->default(0);
            $table->string('currency')->default('USD');
            $table->boolean('is_default')->default(false);
            $table->timestamps();

            $table->unique(['model', 'provider']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orbit_pricing_rules');
    }
};
