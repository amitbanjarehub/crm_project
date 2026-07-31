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
            'lead_statuses',
            function (Blueprint $table) {
                $table->id();

                $table->string(
                    'name',
                    100
                );

                $table->string(
                    'slug',
                    50
                )->unique();

                /*
                 * Special CRM workflow mapping:
                 * new, qualified, converted, lost.
                 *
                 * Normal custom status me null rahega.
                 */
                $table->string(
                    'system_key',
                    30
                )
                    ->nullable()
                    ->unique();

                $table->string(
                    'color',
                    7
                )->default('#64748B');

                $table->boolean(
                    'is_default'
                )->default(false);

                $table->boolean(
                    'is_active'
                )->default(true);

                /*
                 * Closed status select hone par
                 * next follow-up clear ho jayega.
                 */
                $table->boolean(
                    'is_closed'
                )->default(false);

                /*
                 * Core system row delete/slug-edit
                 * nahi hogi.
                 */
                $table->boolean(
                    'is_system'
                )->default(false);

                $table->unsignedInteger(
                    'sort_order'
                )->default(0);

                $table->timestamps();

                $table->index([
                    'is_active',
                    'sort_order',
                ]);
            }
        );

        Schema::create(
            'lead_priorities',
            function (Blueprint $table) {
                $table->id();

                $table->string(
                    'name',
                    100
                );

                $table->string(
                    'slug',
                    50
                )->unique();

                $table->string(
                    'color',
                    7
                )->default('#64748B');

                $table->boolean(
                    'is_default'
                )->default(false);

                $table->boolean(
                    'is_active'
                )->default(true);

                $table->boolean(
                    'is_system'
                )->default(false);

                $table->unsignedInteger(
                    'sort_order'
                )->default(0);

                $table->timestamps();

                $table->index([
                    'is_active',
                    'sort_order',
                ]);
            }
        );

        $now = now();

        /*
         * Existing static statuses ko database
         * master records me migrate karo.
         */
        DB::table(
            'lead_statuses'
        )->insert([
            [
                'name' => 'New',
                'slug' => 'new',
                'system_key' => 'new',
                'color' => '#2563EB',
                'is_default' => true,
                'is_active' => true,
                'is_closed' => false,
                'is_system' => true,
                'sort_order' => 10,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Contacted',
                'slug' => 'contacted',
                'system_key' => null,
                'color' => '#7C3AED',
                'is_default' => false,
                'is_active' => true,
                'is_closed' => false,
                'is_system' => false,
                'sort_order' => 20,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Follow-up',
                'slug' => 'follow_up',
                'system_key' => null,
                'color' => '#CA8A04',
                'is_default' => false,
                'is_active' => true,
                'is_closed' => false,
                'is_system' => false,
                'sort_order' => 30,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Qualified',
                'slug' => 'qualified',
                'system_key' => 'qualified',
                'color' => '#16A34A',
                'is_default' => false,
                'is_active' => true,
                'is_closed' => false,
                'is_system' => true,
                'sort_order' => 40,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Converted',
                'slug' => 'converted',
                'system_key' => 'converted',
                'color' => '#059669',
                'is_default' => false,
                'is_active' => true,
                'is_closed' => true,
                'is_system' => true,
                'sort_order' => 50,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Lost',
                'slug' => 'lost',
                'system_key' => 'lost',
                'color' => '#DC2626',
                'is_default' => false,
                'is_active' => true,
                'is_closed' => true,
                'is_system' => true,
                'sort_order' => 60,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);

        DB::table(
            'lead_priorities'
        )->insert([
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
         * Existing database me koi additional
         * custom value already hai to use bhi
         * master table me preserve karo.
         */
        if (Schema::hasTable('leads')) {
            $existingStatuses = DB::table('leads')
                ->whereNotNull('status')
                ->distinct()
                ->pluck('status');

            foreach (
                $existingStatuses
                as $statusSlug
            ) {
                DB::table(
                    'lead_statuses'
                )->insertOrIgnore([
                    'name' => Str::headline(
                        $statusSlug
                    ),

                    'slug' => $statusSlug,

                    'system_key' => null,

                    'color' => '#64748B',

                    'is_default' => false,

                    'is_active' => true,

                    'is_closed' => false,

                    'is_system' => false,

                    'sort_order' => 100,

                    'created_at' => $now,

                    'updated_at' => $now,
                ]);
            }

            $existingPriorities = DB::table('leads')
                ->whereNotNull('priority')
                ->distinct()
                ->pluck('priority');

            foreach (
                $existingPriorities
                as $prioritySlug
            ) {
                DB::table(
                    'lead_priorities'
                )->insertOrIgnore([
                    'name' => Str::headline(
                        $prioritySlug
                    ),

                    'slug' => $prioritySlug,

                    'color' => '#64748B',

                    'is_default' => false,

                    'is_active' => true,

                    'is_system' => false,

                    'sort_order' => 100,

                    'created_at' => $now,

                    'updated_at' => $now,
                ]);
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists(
            'lead_priorities'
        );

        Schema::dropIfExists(
            'lead_statuses'
        );
    }
};