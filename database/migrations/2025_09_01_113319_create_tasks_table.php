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
        Schema::create('tasks', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            $table->string('title', 160);
            $table->text('description')->nullable();

            // enums (armazenados como string, cast no Model)
            $table->string('type', 32)->index();     // App\Enums\TaskType
            $table->string('status', 32)->index();   // App\Enums\TaskStatus
            $table->string('priority', 16)->index(); // App\Enums\TaskPriority

            $table->dateTime('due_date')->nullable();                 // UTC
            $table->unsignedSmallInteger('estimated_minutes')->nullable();
            $table->unsignedSmallInteger('actual_minutes')->nullable();

            $table->json('labels')->nullable();       // tags livres
            $table->integer('order_index')->default(0);
            $table->dateTime('completed_at')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['user_id', 'status']);
            $table->index(['user_id', 'due_date']);
            $table->index(['user_id', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tasks');
    }
};
