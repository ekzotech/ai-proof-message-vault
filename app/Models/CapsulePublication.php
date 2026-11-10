<?php

namespace App\Models;

use App\Capsules\TransformationPreset;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['title', 'opens_at', 'algorithm_version', 'seed', 'preset', 'segments', 'author_attested'])]
#[Hidden(['seed', 'segments'])]
class CapsulePublication extends Model
{
    protected function casts(): array
    {
        return [
            'opens_at' => 'immutable_datetime',
            'revoked_at' => 'immutable_datetime',
            'preset' => TransformationPreset::class,
            'segments' => 'encrypted:array',
            'author_attested' => 'boolean',
        ];
    }

    public function capsule(): BelongsTo
    {
        return $this->belongsTo(Capsule::class);
    }
}
