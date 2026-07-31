<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(
            'task_statuses',
            function (Blueprint $table) {
                $table->id();

                $table->string('name', 100);

                $table->string('slug', 50)
                    ->unique();

                /*
                 * Core workflow identifier:
                 *
                 * to_do
                 * in_progress
                 * in_review
                 * blocked
                 * completed
                 * cancelled
                 */
                $table->string('system_key', 30)
                    ->nullable()
                    ->unique();

                $table->string('color', 7)
                    ->default('#64748B');

                $table->boolean('is_default')
                    ->default(false);

                $table->boolean('is_active')
                    ->default(true);

                /*
                 * Closed status wali task ko normal
                 * editing aur timer se lock kiya jayega.
                 */
                $table->boolean('is_closed')
                    ->default(false);

                /*
                 * Status ko user dropdown se manually
                 * select kar sakta hai ya nahi.
                 *
                 * Blocked aur In Review false honge.
                 */
                $table->boolean('is_manual_selectable')
                    ->default(true);

                /*
                 * System status delete, deactivate
                 * aur slug-edit nahi hoga.
                 */
                $table->boolean('is_system')
                    ->default(false);

                $table->unsignedInteger('sort_order')
                    ->default(0);

                $table->timestamps();

                $table->index([
                    'is_active',
                    'sort_order',
                ]);

                $table->index([
                    'is_closed',
                    'is_manual_selectable',
                ]);
            }
        );

        Schema::create(
            'task_priorities',
            function (Blueprint $table) {
                $table->id();

                $table->string('name', 100);

                $table->string('slug', 50)
                    ->unique();

                $table->string('color', 7)
                    ->default('#64748B');

                $table->boolean('is_default')
                    ->default(false);

                $table->boolean('is_active')
                    ->default(true);

                $table->boolean('is_system')
                    ->default(false);

                $table->unsignedInteger('sort_order')
                    ->default(0);

                $table->timestamps();

                $table->index([
                    'is_active',
                    'sort_order',
                ]);
            }
        );

        $now = now();

        /*
         * Current static Task statuses database
         * master records me copy karo.
         */
        DB::table('task_statuses')->insert([
            [
                'name' => 'To Do',
                'slug' => 'to_do',
                'system_key' => 'to_do',
                'color' => '#64748B',
                'is_default' => true,
                'is_active' => true,
                'is_closed' => false,
                'is_manual_selectable' => true,
                'is_system' => true,
                'sort_order' => 10,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'In Progress',
                'slug' => 'in_progress',
                'system_key' => 'in_progress',
                'color' => '#2563EB',
                'is_default' => false,
                'is_active' => true,
                'is_closed' => false,
                'is_manual_selectable' => true,
                'is_system' => true,
                'sort_order' => 20,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'In Review',
                'slug' => 'in_review',
                'system_key' => 'in_review',
                'color' => '#7C3AED',
                'is_default' => false,
                'is_active' => true,
                'is_closed' => false,
                'is_manual_selectable' => false,
                'is_system' => true,
                'sort_order' => 30,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Blocked',
                'slug' => 'blocked',
                'system_key' => 'blocked',
                'color' => '#EA580C',
                'is_default' => false,
                'is_active' => true,
                'is_closed' => false,
                'is_manual_selectable' => false,
                'is_system' => true,
                'sort_order' => 40,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Completed',
                'slug' => 'completed',
                'system_key' => 'completed',
                'color' => '#059669',
                'is_default' => false,
                'is_active' => true,
                'is_closed' => true,
                'is_manual_selectable' => true,
                'is_system' => true,
                'sort_order' => 50,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Cancelled',
                'slug' => 'cancelled',
                'system_key' => 'cancelled',
                'color' => '#DC2626',
                'is_default' => false,
                'is_active' => true,
                'is_closed' => true,
                'is_manual_selectable' => true,
                'is_system' => true,
                'sort_order' => 60,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);

        DB::table('task_priorities')->insert([
            [
                'name' => 'Low',
                'slug' => 'low',
                'color' => '#64748B',
                'is_default' => false,
                'is_active' => true,
                'is_system' => false,
                'sort_order' => 10,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Medium',
                'slug' => 'medium',
                'color' => '#2563EB',
                'is_default' => true,
                'is_active' => true,
                'is_system' => true,
                'sort_order' => 20,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'High',
                'slug' => 'high',
                'color' => '#EA580C',
                'is_default' => false,
                'is_active' => true,
                'is_system' => false,
                'sort_order' => 30,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Urgent',
                'slug' => 'urgent',
                'color' => '#DC2626',
                'is_default' => false,
                'is_active' => true,
                'is_system' => false,
                'sort_order' => 40,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);

        /*
         * Current database me koi extra status ya
         * priority already save hai to preserve karo.
         */
        if (Schema::hasTable('tasks')) {
            $existingStatuses = DB::table('tasks')
                ->whereNotNull('status')
                ->distinct()
                ->pluck('status');

            foreach ($existingStatuses as $statusSlug) {
                DB::table('task_statuses')
                    ->insertOrIgnore([
                        'name' =>
                            Str::headline($statusSlug),

                        'slug' =>
                            $statusSlug,

                        'system_key' =>
                            null,

                        'color' =>
                            '#64748B',

                        'is_default' =>
                            false,

                        'is_active' =>
                            true,

                        'is_closed' =>
                            false,

                        'is_manual_selectable' =>
                            true,

                        'is_system' =>
                            false,

                        'sort_order' =>
                            100,

                        'created_at' =>
                            $now,

                        'updated_at' =>
                            $now,
                    ]);
            }

            $existingPriorities = DB::table('tasks')
                ->whereNotNull('priority')
                ->distinct()
                ->pluck('priority');

            foreach ($existingPriorities as $prioritySlug) {
                DB::table('task_priorities')
                    ->insertOrIgnore([
                        'name' =>
                            Str::headline($prioritySlug),

                        'slug' =>
                            $prioritySlug,

                        'color' =>
                            '#64748B',

                        'is_default' =>
                            false,

                        'is_active' =>
                            true,

                        'is_system' =>
                            false,

                        'sort_order' =>
                            100,

                        'created_at' =>
                            $now,

                        'updated_at' =>
                            $now,
                    ]);
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists(
            'task_priorities'
        );

        Schema::dropIfExists(
            'task_statuses'
        );
    }
};