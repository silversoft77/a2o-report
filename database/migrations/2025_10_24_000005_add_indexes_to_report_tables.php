<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('log_events', function (Blueprint $table) {
            $table->index(['market_id', 'created_at'], 'idx_log_events_market_date');
            $table->index('session_id', 'idx_log_events_session');
        });

        Schema::table('log_service_titan_jobs', function (Blueprint $table) {
            $table->index(['market_id', 'created_at'], 'idx_log_jobs_market_date');
            $table->index('service_titan_job_id', 'idx_log_jobs_titan_id');
            $table->index('job_status', 'idx_log_jobs_status');
            $table->index('start', 'idx_log_jobs_start');
            $table->index('end', 'idx_log_jobs_end');
        });

        Schema::table('event_names', function (Blueprint $table) {
            $table->index('name', 'idx_event_names_name');
            $table->index('display_on_client', 'idx_event_names_display');
        });
    }

    public function down(): void
    {
        Schema::table('log_events', function (Blueprint $table) {
            $table->dropIndex('idx_log_events_market_date');
            $table->dropIndex('idx_log_events_session');
        });

        Schema::table('log_service_titan_jobs', function (Blueprint $table) {
            $table->dropIndex('idx_log_jobs_market_date');
            $table->dropIndex('idx_log_jobs_titan_id');
            $table->dropIndex('idx_log_jobs_status');
            $table->dropIndex('idx_log_jobs_start');
            $table->dropIndex('idx_log_jobs_end');
        });

        Schema::table('event_names', function (Blueprint $table) {
            $table->dropIndex('idx_event_names_name');
            $table->dropIndex('idx_event_names_display');
        });
    }
};