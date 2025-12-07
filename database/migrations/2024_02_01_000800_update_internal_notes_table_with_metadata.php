<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('internal_notes', function (Blueprint $table) {
            $table->string('agent_name', 120)->nullable()->after('agent_id');
            $table->string('agent_email', 150)->nullable()->after('agent_name');
            $table->boolean('is_important')->default(false)->after('content');
            $table->json('mentions')->nullable()->after('is_important');
        });
    }

    public function down(): void
    {
        Schema::table('internal_notes', function (Blueprint $table) {
            $table->dropColumn(['agent_name', 'agent_email', 'is_important', 'mentions']);
        });
    }
};
