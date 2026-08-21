<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('tasks', function (Blueprint $table) {

            if (!Schema::hasColumn('tasks', 'status_kanban_position')) {
                $table->unsignedBigInteger('status_kanban_position')
                    ->default(0)
                    ->after('status');
            }

            if (!Schema::hasColumn('tasks', 'priority_kanban_position')) {
                $table->unsignedBigInteger('priority_kanban_position')
                    ->default(0)
                    ->after('priority');
            }
        });

        Schema::table('tasks', function (Blueprint $table) {

            // Indexes ko separately handle karna better hai.
            $indexes = collect(Schema::getIndexes('tasks'))
                ->pluck('name')
                ->toArray();

            if (
                !in_array(
                    'tasks_status_status_kanban_position_index',
                    $indexes,
                    true
                )
            ) {
                $table->index([
                    'status',
                    'status_kanban_position',
                ]);
            }

            if (
                !in_array(
                    'tasks_priority_priority_kanban_position_index',
                    $indexes,
                    true
                )
            ) {
                $table->index([
                    'priority',
                    'priority_kanban_position',
                ]);
            }
        });
    }

    public function down(): void
    {
        Schema::table('tasks', function (Blueprint $table) {

            $indexes = collect(Schema::getIndexes('tasks'))
                ->pluck('name')
                ->toArray();

            if (
                in_array(
                    'tasks_status_status_kanban_position_index',
                    $indexes,
                    true
                )
            ) {
                $table->dropIndex(
                    'tasks_status_status_kanban_position_index'
                );
            }

            if (
                in_array(
                    'tasks_priority_priority_kanban_position_index',
                    $indexes,
                    true
                )
            ) {
                $table->dropIndex(
                    'tasks_priority_priority_kanban_position_index'
                );
            }

            if (
                Schema::hasColumn(
                    'tasks',
                    'status_kanban_position'
                )
            ) {
                $table->dropColumn(
                    'status_kanban_position'
                );
            }

            if (
                Schema::hasColumn(
                    'tasks',
                    'priority_kanban_position'
                )
            ) {
                $table->dropColumn(
                    'priority_kanban_position'
                );
            }
        });
    }
};