<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('internal_note_mentions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('internal_note_id')->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('agent_id')->nullable();
            $table->string('mentioned_identifier', 150);
            $table->timestamp('notified_at')->nullable();
            $table->timestamps();
            $table->index(['mentioned_identifier']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('internal_note_mentions');
    }
};
