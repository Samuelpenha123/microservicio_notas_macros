<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (! Schema::hasTable('internal_notes') || ! Schema::hasColumn('internal_notes', 'ticket_id')) {
            return;
        }

        Schema::table('internal_notes', function (Blueprint $table) {
            $table->string('ticket_code', 120)->nullable()->after('agent_id');
        });

        DB::table('internal_notes')
            ->whereNotNull('ticket_id')
            ->update(['ticket_code' => DB::raw('ticket_id')]);

        Schema::table('internal_notes', function (Blueprint $table) {
            $table->dropColumn('ticket_id');
            $table->index(['agent_id', 'ticket_code'], 'internal_notes_agent_id_ticket_code_index');
        });

        DB::statement('ALTER TABLE internal_notes MODIFY ticket_code VARCHAR(120) NOT NULL');
    }

    public function down(): void
    {
        if (! Schema::hasTable('internal_notes') || Schema::hasColumn('internal_notes', 'ticket_id')) {
            return;
        }

        Schema::table('internal_notes', function (Blueprint $table) {
            $table->unsignedBigInteger('ticket_id')->nullable()->after('agent_id');
        });

        DB::table('internal_notes')
            ->whereNotNull('ticket_code')
            ->update(['ticket_id' => DB::raw('ticket_code')]);

        Schema::table('internal_notes', function (Blueprint $table) {
            $table->dropIndex('internal_notes_agent_id_ticket_code_index');
            $table->dropColumn('ticket_code');
            $table->index(['agent_id', 'ticket_id'], 'internal_notes_agent_id_ticket_id_index');
        });

        DB::statement('ALTER TABLE internal_notes MODIFY ticket_id BIGINT UNSIGNED NOT NULL');
    }
};
