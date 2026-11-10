<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('capsules', function (Blueprint $table): void {
            $table->string('transformation_seed', 32)->nullable();
        });

        DB::table('capsules')->select('id')->chunkById(200, function ($capsules): void {
            foreach ($capsules as $capsule) {
                DB::table('capsules')->where('id', $capsule->id)
                    ->update(['transformation_seed' => Str::random(32)]);
            }
        });
    }

    public function down(): void
    {
        Schema::table('capsules', function (Blueprint $table): void {
            $table->dropColumn('transformation_seed');
        });
    }
};
