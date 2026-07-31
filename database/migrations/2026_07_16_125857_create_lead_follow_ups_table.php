<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lead_follow_ups', function (Blueprint $table) {
            $table->id();

            $table->foreignId('lead_id')
                ->constrained('leads')
                ->cascadeOnDelete();

            $table->foreignId('user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->string('type', 30)->index();

            /*
             * Actual call, meeting ya message kab hua.
             */
            $table->dateTime('followed_up_at')->index();

            $table->string('outcome', 50)
                ->nullable()
                ->index();

            $table->text('notes');

            /*
             * Is conversation ke baad next follow-up kab karna hai.
             */
            $table->dateTime('next_follow_up_at')
                ->nullable()
                ->index();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lead_follow_ups');
    }
};