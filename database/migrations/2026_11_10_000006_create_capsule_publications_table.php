<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('capsule_publications', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('capsule_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('title', 120);
            $table->timestamp('opens_at');
            $table->timestamp('revoked_at')->nullable();
            $table->unsignedInteger('algorithm_version');
            $table->string('seed', 32);
            $table->string('preset', 32);
            $table->longText('segments');
            $table->boolean('author_attested')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('capsule_publications');
    }
};
