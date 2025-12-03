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
        Schema::create('internal_notes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('agent_id')->index();
            $table->string('ticket_code', 120)->index();
            $table->text('content');
            $table->timestamps();
            $table->index(['agent_id', 'ticket_code']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('internal_notes');
    }
};
