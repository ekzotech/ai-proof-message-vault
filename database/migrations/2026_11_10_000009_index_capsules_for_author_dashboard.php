<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('capsules', function (Blueprint $table): void {
            $table->index(['author_id', 'updated_at', 'id'], 'capsules_author_updated_id_index');
        });
    }

    public function down(): void
    {
        Schema::table('capsules', function (Blueprint $table): void {
            $table->dropIndex('capsules_author_updated_id_index');
        });
    }
};
