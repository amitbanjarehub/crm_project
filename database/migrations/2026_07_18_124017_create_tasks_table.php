<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tasks', function (Blueprint $table) {
            $table->id();

            $table->foreignId('project_id')
                ->constrained('projects')
                ->cascadeOnDelete();

            $table->foreignId('project_service_id')
                ->constrained('project_services')
                ->cascadeOnDelete();

            $table->foreignId('parent_task_id')
                ->nullable()
                ->constrained('tasks')
                ->nullOnDelete();

            $table->string('title');
            $table->text('description')->nullable();

            $table->foreignId('assigned_to')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->string('priority', 20)
                ->default('medium')
                ->index();

            $table->string('status', 30)
                ->default('to_do')
                ->index();

            $table->unsignedTinyInteger('progress_percent')
                ->default(0);

            /*
             * Phase 2 review support.
             */
            $table->boolean('requires_review')
                ->default(true);

            $table->foreignId('reviewer_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->dateTime('submitted_for_review_at')
                ->nullable();

            $table->dateTime('reviewed_at')
                ->nullable();

            $table->text('review_note')->nullable();

            $table->date('start_date')->nullable();
            $table->dateTime('due_at')->nullable()->index();

            $table->decimal('estimated_hours', 8, 2)
                ->nullable();

            $table->dateTime('completed_at')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index([
                'assigned_to',
                'status',
                'due_at',
            ]);

            $table->index([
                'project_id',
                'status',
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tasks');
    }
};