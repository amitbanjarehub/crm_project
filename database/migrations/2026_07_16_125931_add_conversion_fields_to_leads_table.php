<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            $table->dateTime('converted_at')
                ->nullable()
                ->index()
                ->after('notes');

            $table->foreignId('converted_by')
                ->nullable()
                ->after('converted_at')
                ->constrained('users')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            $table->dropConstrainedForeignId('converted_by');
            $table->dropColumn('converted_at');
        });
    }
};