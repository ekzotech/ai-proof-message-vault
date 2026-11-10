<?php

namespace App\Models;

use Database\Factories\CapsuleFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['title', 'body'])]
#[Hidden(['body'])]
class Capsule extends Model
{
    /** @use HasFactory<CapsuleFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return ['body' => 'encrypted'];
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
