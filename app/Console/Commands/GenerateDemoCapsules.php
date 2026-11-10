<?php

namespace App\Console\Commands;

use App\Capsules\CapsuleState;
use App\Capsules\TransformationPipeline;
use App\Capsules\TransformationPreset;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class GenerateDemoCapsules extends Command
{
    protected $signature = 'vault:demo {--json : Output the generated demo account and links as JSON}';

    protected $description = 'Create synthetic demo capsules in a local or testing environment';

    public function handle(TransformationPipeline $pipeline): int
    {
        if (! app()->environment('local', 'testing')) {
            $this->error('Демо-данные разрешены только в local и testing.');

            return self::FAILURE;
        }

        $password = Str::random(24);
        $email = 'vault-demo-'.Str::lower(Str::random(12)).'@example.test';
        $links = DB::transaction(function () use ($email, $password, $pipeline): array {
            $author = User::create(['name' => 'Демо-автор', 'email' => $email, 'password' => Hash::make($password)]);
            $author->markEmailAsVerified();
            $links = [];

            foreach (CapsuleState::cases() as $state) {
                $opensAt = $state === CapsuleState::Waiting ? now()->addDay() : now()->subHour();
                $draft = $author->capsules()->create([
                    'title' => 'Демо · '.$state->label(),
                    'body' => "На рассвете мы открыли окна и записали первый звук города.\nA message for another morning.",
                    'opens_at' => $state === CapsuleState::Draft ? null : $opensAt,
                ]);
                if ($state === CapsuleState::Draft) {
                    continue;
                }

                $preset = TransformationPreset::Experimental;
                $result = $pipeline->transform($draft->body, $draft->transformation_seed, $preset);
                $publication = $draft->publication()->make([
                    'title' => $draft->title,
                    'opens_at' => $opensAt,
                    'algorithm_version' => $result->version,
                    'seed' => $draft->transformation_seed,
                    'preset' => $preset,
                    'segments' => $result->segments,
                    'accessible_text' => $draft->body,
                    'author_attested' => false,
                ]);
                $publication->issueReaderToken();
                $publication->revoked_at = $state === CapsuleState::Revoked ? now() : null;
                $publication->save();
                $links[$state->value] = route('capsules.read', $publication->reader_token);
            }

            return $links;
        });

        if ($this->option('json')) {
            $this->line(json_encode(compact('email', 'password', 'links'), JSON_THROW_ON_ERROR));
        } else {
            $this->line('Демо-аккаунт '.$email);
            $this->line('Пароль '.$password);
            foreach ($links as $state => $link) {
                $this->line(CapsuleState::from($state)->label().' '.$link);
            }
        }

        return self::SUCCESS;
    }
}
