<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('clients', function (Blueprint $table) {
            $table->id();

            /*
             * Ek lead sirf ek client me convert ho sakti hai.
             * Manual client ke liye lead_id null rahega.
             */
            $table->foreignId('lead_id')
                ->nullable()
                ->unique()
                ->constrained('leads')
                ->nullOnDelete();

            $table->string('name');
            $table->string('phone', 25);
            $table->string('email')->nullable();
            $table->string('company')->nullable();

            $table->string('status', 30)
                ->default('active')
                ->index();

            $table->foreignId('assigned_to')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->text('notes')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('clients');
    }
};