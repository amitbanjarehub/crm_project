<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(
            'lead_kanban_moves',
            function (Blueprint $table) {
                $table->id();

                $table->foreignId('lead_id')
                    ->constrained('leads')
                    ->cascadeOnDelete();

                $table->foreignId('user_id')
                    ->nullable()
                    ->constrained('users')
                    ->nullOnDelete();

                $table->string(
                    'group_by',
                    20
                );

                $table->string(
                    'from_column'
                )->nullable();

                $table->string(
                    'to_column'
                );

                $table->decimal(
                    'from_position',
                    20,
                    6
                )->nullable();

                $table->decimal(
                    'to_position',
                    20,
                    6
                )->nullable();

                $table->timestamps();

                $table->index([
                    'lead_id',
                    'created_at',
                ]);
            }
        );
    }

    public function down(): void
    {
        Schema::dropIfExists(
            'lead_kanban_moves'
        );
    }
};