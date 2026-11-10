<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('capsule_publications', function (Blueprint $table): void {
            $table->text('reader_token')->nullable();
            $table->string('reader_token_hash', 64)->nullable()->unique();
        });
    }

    public function down(): void
    {
        Schema::table('capsule_publications', function (Blueprint $table): void {
            $table->dropUnique(['reader_token_hash']);
            $table->dropColumn(['reader_token', 'reader_token_hash']);
        });
    }
};
