<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('macros', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('content');
            $table->enum('scope', ['personal', 'global'])->default('personal');
            $table->unsignedBigInteger('created_by')->index();
            $table->timestamps();
            $table->index(['scope', 'created_by']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('macros');
    }
};
