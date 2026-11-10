<?php

namespace App\Capsules;

use App\Models\Capsule;
use App\Models\CapsulePublication;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PublishCapsule
{
    public function __construct(private TransformationPipeline $pipeline) {}

    public function handle(User $author, Capsule $draft, int $version, TransformationPreset $preset): CapsulePublication
    {
        return DB::transaction(function () use ($author, $draft, $version, $preset): CapsulePublication {
            $owned = $author->capsules()->whereKey($draft->id);
            $claimed = (clone $owned)->where('lock_version', $version)
                ->whereDoesntHave('publication')->increment('lock_version');

            $current = $owned->firstOrFail();
            $existing = $current->publication()->first();

            if ($existing) {
                return $existing;
            }

            if ($claimed !== 1) {
                throw ValidationException::withMessages(['version' => 'Черновик изменился. Откройте свежий предпросмотр.']);
            }

            if ($current->opens_at === null || $current->opens_at <= now()) {
                throw ValidationException::withMessages(['opens_at' => 'Укажите дату и время открытия в будущем.']);
            }

            $result = $this->pipeline->transform($current->body, $current->transformation_seed, $preset);
            $publication = $current->publication()->make([
                'title' => $current->title,
                'opens_at' => $current->opens_at,
                'algorithm_version' => $result->version,
                'seed' => $current->transformation_seed,
                'preset' => $preset,
                'segments' => $result->segments,
                'author_attested' => true,
            ]);
            $publication->issueReaderToken();
            $publication->save();

            return $publication;
        });
    }
}
