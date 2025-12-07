<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('macros', function (Blueprint $table) {
            $table->string('category', 120)->nullable()->after('scope');
            $table->string('created_by_name', 120)->nullable()->after('created_by');
        });
    }

    public function down(): void
    {
        Schema::table('macros', function (Blueprint $table) {
            $table->dropColumn(['category', 'created_by_name']);
        });
    }
};
