<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add polymorphic participant columns to the AI runs table.
     *
     * Additive only: the legacy user_id column is kept and remains written
     * for backward compatibility. Existing rows are backfilled from user_id
     * with the host application's user morph class, resolved from the
     * configured auth guard — never a hardcoded model class.
     */
    public function up(): void
    {
        if (! Schema::hasTable('orbit_ai_runs') || Schema::hasColumn('orbit_ai_runs', 'participant_id')) {
            return;
        }

        Schema::table('orbit_ai_runs', function (Blueprint $table) {
            $table->string('participant_type')->nullable();
            $table->unsignedBigInteger('participant_id')->nullable();
            $table->index(['participant_type', 'participant_id']);
        });

        $userModel = $this->userModel();

        if ($userModel === null) {
            return;
        }

        $participantType = (new $userModel)->getMorphClass();

        DB::table('orbit_ai_runs')
            ->whereNotNull('user_id')
            ->chunkById(500, function ($rows) use ($participantType): void {
                foreach ($rows as $row) {
                    if (! is_numeric($row->user_id)) {
                        continue;
                    }

                    DB::table('orbit_ai_runs')->where('id', $row->id)->update([
                        'participant_id' => (int) $row->user_id,
                        'participant_type' => $participantType,
                    ]);
                }
            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! Schema::hasTable('orbit_ai_runs') || ! Schema::hasColumn('orbit_ai_runs', 'participant_id')) {
            return;
        }

        Schema::table('orbit_ai_runs', function (Blueprint $table) {
            $table->dropIndex(['participant_type', 'participant_id']);
            $table->dropColumn(['participant_type', 'participant_id']);
        });
    }

    /**
     * Resolve the host application's user model class for the backfill.
     */
    private function userModel(): ?string
    {
        $guard = config('ai-orbit.auth_guard', 'web');
        $provider = config("auth.guards.{$guard}.provider", 'users');
        $model = config("auth.providers.{$provider}.model", 'App\\Models\\User');

        return is_string($model) && class_exists($model) ? $model : null;
    }
};
