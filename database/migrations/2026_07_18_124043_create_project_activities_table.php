<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('project_activities', function (Blueprint $table) {
            $table->id();

            $table->foreignId('project_id')
                ->constrained('projects')
                ->cascadeOnDelete();

            $table->foreignId('user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->string('action', 80)->index();

            $table->string('subject_type')->nullable();
            $table->unsignedBigInteger('subject_id')->nullable();

            $table->text('description');

            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();

            $table->timestamps();

            $table->index([
                'subject_type',
                'subject_id',
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('project_activities');
    }
};