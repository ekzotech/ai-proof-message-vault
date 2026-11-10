<?php

namespace App\Models;

use App\Capsules\TransformationPreset;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['title', 'opens_at', 'algorithm_version', 'seed', 'preset', 'segments', 'author_attested'])]
#[Hidden(['seed', 'segments', 'reader_token', 'reader_token_hash'])]
class CapsulePublication extends Model
{
    protected function casts(): array
    {
        return [
            'opens_at' => 'immutable_datetime',
            'revoked_at' => 'immutable_datetime',
            'preset' => TransformationPreset::class,
            'segments' => 'encrypted:array',
            'reader_token' => 'encrypted',
            'author_attested' => 'boolean',
        ];
    }

    public function capsule(): BelongsTo
    {
        return $this->belongsTo(Capsule::class);
    }

    public function issueReaderToken(): void
    {
        $token = bin2hex(random_bytes(24));
        $this->reader_token = $token;
        $this->reader_token_hash = self::hashReaderToken($token);
    }

    public static function hashReaderToken(string $token): string
    {
        return hash('sha256', $token);
    }
}
