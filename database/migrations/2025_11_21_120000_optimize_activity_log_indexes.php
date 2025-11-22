<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $connection = config('activitylog.database_connection');
        $table = config('activitylog.table_name');

        if (! Schema::connection($connection)->hasTable($table)) {
            return;
        }

        DB::connection($connection)->statement("CREATE INDEX IF NOT EXISTS activity_subject_index ON {$table} (subject_type, subject_id)");
        DB::connection($connection)->statement("CREATE INDEX IF NOT EXISTS activity_causer_index ON {$table} (causer_type, causer_id)");
        DB::connection($connection)->statement("CREATE INDEX IF NOT EXISTS activity_event_index ON {$table} (event)");
        DB::connection($connection)->statement("CREATE INDEX IF NOT EXISTS activity_created_at_index ON {$table} (created_at)");
        DB::connection($connection)->statement("CREATE INDEX IF NOT EXISTS activity_batch_uuid_index ON {$table} (batch_uuid)");
    }

    public function down(): void
    {
        $connection = config('activitylog.database_connection');
        $table = config('activitylog.table_name');

        if (! Schema::connection($connection)->hasTable($table)) {
            return;
        }

        DB::connection($connection)->statement("DROP INDEX IF EXISTS activity_subject_index");
        DB::connection($connection)->statement("DROP INDEX IF EXISTS activity_causer_index");
        DB::connection($connection)->statement("DROP INDEX IF EXISTS activity_event_index");
        DB::connection($connection)->statement("DROP INDEX IF EXISTS activity_created_at_index");
        DB::connection($connection)->statement("DROP INDEX IF EXISTS activity_batch_uuid_index");
    }
};
