<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('capsules', function (Blueprint $table): void {
            $table->timestamp('opens_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('capsules', function (Blueprint $table): void {
            $table->dropColumn('opens_at');
        });
    }
};
