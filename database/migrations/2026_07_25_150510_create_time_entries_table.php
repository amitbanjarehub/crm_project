<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('time_entries', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            /*
             * User ka system role snapshot.
             * Future me user ka role change hone par bhi
             * old report ka original role preserve rahega.
             */
            $table->foreignId('role_id')
                ->nullable()
                ->constrained('roles')
                ->nullOnDelete();

            $table->foreignId('task_id')
                ->nullable()
                ->constrained('tasks')
                ->nullOnDelete();

            $table->foreignId('project_id')
                ->nullable()
                ->constrained('projects')
                ->nullOnDelete();

            $table->foreignId('project_service_id')
                ->nullable()
                ->constrained('project_services')
                ->nullOnDelete();

            /*
             * MySQL nullable unique field.
             *
             * Running/Paused entry ke liye active_key=user_id.
             * Stopped entry ke liye active_key=null.
             *
             * Isse ek user ke do active timers database
             * level par bhi create nahi honge.
             */
            $table->unsignedBigInteger('active_key')
                ->nullable()
                ->unique();

            $table->string('status', 20)
                ->default('running')
                ->index();

            $table->dateTime('started_at')->index();

            /*
             * Current running segment kab start/resume hua.
             */
            $table->dateTime('last_started_at')
                ->nullable();

            $table->dateTime('paused_at')
                ->nullable();

            $table->dateTime('stopped_at')
                ->nullable()
                ->index();

            /*
             * Sirf actual worked seconds.
             * Pause duration isme include nahi hogi.
             */
            $table->unsignedBigInteger('total_seconds')
                ->default(0);

            $table->text('notes')->nullable();

            /*
             * Historical reporting snapshots.
             */
            $table->string('user_name_snapshot')
                ->nullable();

            $table->string('role_name_snapshot')
                ->nullable();

            $table->string('member_role_snapshot')
                ->nullable();

            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->foreignId('stopped_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->string('stop_reason', 255)
                ->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index([
                'user_id',
                'status',
                'started_at',
            ]);

            $table->index([
                'project_id',
                'started_at',
            ]);

            $table->index([
                'task_id',
                'started_at',
            ]);

            $table->index([
                'role_id',
                'started_at',
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('time_entries');
    }
};