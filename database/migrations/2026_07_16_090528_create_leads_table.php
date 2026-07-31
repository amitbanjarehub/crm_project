<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('leads', function (Blueprint $table) {
            $table->id();

            $table->string('name');
            $table->string('phone', 25);
            $table->string('email')->nullable();
            $table->string('company')->nullable();

            $table->string('source', 50)
                ->default('other')
                ->index();

            $table->string('status', 30)
                ->default('new')
                ->index();

            $table->string('priority', 20)
                ->default('medium')
                ->index();

            $table->foreignId('assigned_to')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->dateTime('next_follow_up_at')
                ->nullable()
                ->index();

            $table->text('notes')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('leads');
    }
};