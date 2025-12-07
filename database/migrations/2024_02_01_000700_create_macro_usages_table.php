<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('macro_usages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('macro_id')->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('agent_id')->index();
            $table->string('ticket_code', 120)->index();
            $table->boolean('customized')->default(false);
            $table->string('feedback', 20)->nullable()->index();
            $table->text('rendered_content')->nullable();
            $table->timestamps();
            $table->index(['macro_id', 'agent_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('macro_usages');
    }
};
