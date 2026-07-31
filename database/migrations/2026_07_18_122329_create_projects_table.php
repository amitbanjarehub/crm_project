<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('projects', function (Blueprint $table) {
            $table->id();

            /*
             * Record create hone ke baad ID ke basis par code banega:
             * PRJ-2026-0001
             */
            $table->string('project_code', 30)
                ->nullable()
                ->unique();

            $table->foreignId('client_id')
                ->constrained('clients')
                ->restrictOnDelete();

            $table->string('name');
            $table->text('description')->nullable();

            $table->foreignId('project_manager_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->string('priority', 20)
                ->default('medium')
                ->index();

            $table->string('status', 30)
                ->default('draft')
                ->index();

            $table->date('start_date')->nullable();
            $table->date('due_date')->nullable()->index();

            $table->decimal('budget', 15, 2)->nullable();

            $table->dateTime('completed_at')->nullable();

            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamps();
            $table->softDeletes();

            $table->index([
                'client_id',
                'status',
            ]);

            $table->index([
                'project_manager_id',
                'status',
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('projects');
    }
};