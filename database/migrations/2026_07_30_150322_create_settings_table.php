<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('settings', function (Blueprint $table) {
            $table->id();

            /*
             * Setting kis section ki hai:
             * general, regional, branding, login
             */
            $table->string(
                'group_name',
                50
            )->default('general');

            /*
             * Unique setting identifier:
             * company_name, timezone, company_logo...
             */
            $table->string(
                'setting_key',
                100
            )->unique();

            /*
             * Text, file path, colour, boolean sab
             * isi field me store honge.
             */
            $table->longText(
                'setting_value'
            )->nullable();

            $table->string(
                'type',
                30
            )->default('text');

            /*
             * Future public website/API me setting
             * expose karni hai ya nahi.
             */
            $table->boolean(
                'is_public'
            )->default(false);

            $table->timestamps();

            $table->index([
                'group_name',
                'setting_key',
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};