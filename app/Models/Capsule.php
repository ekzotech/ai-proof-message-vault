<?php

namespace App\Models;

use Database\Factories\CapsuleFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

#[Fillable(['title', 'body', 'opens_at'])]
#[Hidden(['body', 'transformation_seed'])]
class Capsule extends Model
{
    /** @use HasFactory<CapsuleFactory> */
    use HasFactory;

    protected $attributes = ['lock_version' => 0];

    protected static function booted(): void
    {
        static::creating(function (Capsule $capsule): void {
            $capsule->transformation_seed ??= Str::random(32);
        });
    }

    protected function casts(): array
    {
        return ['body' => 'encrypted', 'lock_version' => 'integer', 'opens_at' => 'immutable_datetime'];
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
