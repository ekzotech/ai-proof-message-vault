<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('capsule_publications', function (Blueprint $table): void {
            $table->longText('accessible_text')->nullable();
        });

        DB::table('capsule_publications')
            ->join('capsules', 'capsules.id', '=', 'capsule_publications.capsule_id')
            ->select('capsule_publications.id', 'capsules.body')
            ->chunkById(200, function ($publications): void {
                foreach ($publications as $publication) {
                    DB::table('capsule_publications')->where('id', $publication->id)
                        ->update(['accessible_text' => $publication->body]);
                }
            }, 'capsule_publications.id', 'id');
    }

    public function down(): void
    {
        Schema::table('capsule_publications', function (Blueprint $table): void {
            $table->dropColumn('accessible_text');
        });
    }
};
