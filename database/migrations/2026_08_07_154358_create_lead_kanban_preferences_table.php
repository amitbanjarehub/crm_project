<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(
            'lead_kanban_preferences',
            function (Blueprint $table) {
                $table->id();

                $table->foreignId('user_id')
                    ->unique()
                    ->constrained('users')
                    ->cascadeOnDelete();

                $table->string(
                    'group_by',
                    20
                )->default('status');

                /*
                 * Example:
                 *
                 * {
                 *   "status": ["new", "contacted"],
                 *   "priority": ["urgent", "high"]
                 * }
                 */
                $table->json(
                    'column_order'
                )->nullable();

                $table->json(
                    'collapsed_columns'
                )->nullable();

                $table->boolean(
                    'hide_empty_columns'
                )->default(false);

                $table->json(
                    'selected_filters'
                )->nullable();

                $table->timestamps();
            }
        );
    }

    public function down(): void
    {
        Schema::dropIfExists(
            'lead_kanban_preferences'
        );
    }
};