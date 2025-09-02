<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('time_boxes', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            $table->string('title', 160);
            $table->string('type', 32)->index(); // App\Enums\TimeBoxType
            $table->dateTime('start_at');        // UTC
            $table->dateTime('end_at');          // UTC

            $table->boolean('allow_overlap')->default(false);
            $table->text('notes')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['user_id', 'start_at']);
            $table->index(['user_id', 'end_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('time_boxes');
    }
};
