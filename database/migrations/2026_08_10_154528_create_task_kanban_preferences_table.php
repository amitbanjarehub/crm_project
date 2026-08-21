<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(
            'task_kanban_preferences',
            function (Blueprint $table) {
                $table->id();

                $table->foreignId('user_id')
                    ->constrained('users')
                    ->cascadeOnDelete();

                $table->string('group_by', 30)
                    ->default('status');

                $table->json('column_order')
                    ->nullable();

                $table->boolean('hide_empty_columns')
                    ->default(false);

                $table->timestamps();

                $table->unique('user_id');
            }
        );
    }

    public function down(): void
    {
        Schema::dropIfExists(
            'task_kanban_preferences'
        );
    }
};