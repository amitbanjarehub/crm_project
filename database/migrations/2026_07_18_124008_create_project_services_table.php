<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('project_services', function (Blueprint $table) {
            $table->id();

            $table->foreignId('project_id')
                ->constrained('projects')
                ->cascadeOnDelete();

            $table->string('name');
            $table->text('description')->nullable();

            $table->foreignId('assigned_to')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->string('priority', 20)
                ->default('medium')
                ->index();

            $table->string('status', 30)
                ->default('pending')
                ->index();

            $table->date('start_date')->nullable();
            $table->date('due_date')->nullable()->index();

            $table->unsignedInteger('sort_order')
                ->default(0);

            $table->dateTime('completed_at')->nullable();

            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamps();
            $table->softDeletes();

            $table->index([
                'project_id',
                'status',
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('project_services');
    }
};