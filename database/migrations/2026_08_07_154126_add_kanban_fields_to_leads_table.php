<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            /*
             * Status grouping ke andar Lead ki position.
             */
            $table->decimal(
                'status_kanban_position',
                20,
                6
            )
                ->nullable()
                ->after('status');

            /*
             * Priority grouping ke andar Lead ki position.
             */
            $table->decimal(
                'priority_kanban_position',
                20,
                6
            )
                ->nullable()
                ->after('priority');

            /*
             * Optimistic concurrency ke liye.
             * Har successful Kanban move par increment hoga.
             */
            $table->unsignedBigInteger(
                'kanban_version'
            )
                ->default(0)
                ->after('priority_kanban_position');

            $table->index(
                [
                    'status',
                    'status_kanban_position',
                ],
                'leads_status_kanban_index'
            );

            $table->index(
                [
                    'priority',
                    'priority_kanban_position',
                ],
                'leads_priority_kanban_index'
            );
        });

        /*
         * Existing Leads ko stable initial position do.
         * 1000 ka gap future drag-drop insertion ke liye hai.
         */
        DB::table('leads')
            ->select('id')
            ->orderBy('id')
            ->chunkById(
                500,
                function ($leads) {
                    foreach ($leads as $lead) {
                        $position =
                            (float) $lead->id
                            * 1000;

                        DB::table('leads')
                            ->where(
                                'id',
                                $lead->id
                            )
                            ->update([
                                'status_kanban_position' =>
                                    $position,

                                'priority_kanban_position' =>
                                    $position,
                            ]);
                    }
                }
            );
    }

    public function down(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            $table->dropIndex('leads_status_kanban_index');

            $table->dropIndex(
                'leads_priority_kanban_index'
            );

            $table->dropColumn([
                'status_kanban_position',
                'priority_kanban_position',
                'kanban_version',
            ]);
        });
    }
};