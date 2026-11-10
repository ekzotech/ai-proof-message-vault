<?php

namespace App\Livewire;

use App\Models\Capsule;
use App\Rules\CapsuleText;
use App\Support\LocalOpeningTime;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Attributes\Locked;
use Livewire\Component;

class CapsuleDraftEditor extends Component
{
    #[Locked]
    public ?int $capsuleId = null;

    #[Locked]
    public int $version = 0;

    public string $title = '';

    public string $body = '';

    #[Locked]
    public string $timezone = 'UTC';

    public string $opening = '';

    public string $openingOffset = '';

    public function mount(?Capsule $capsule = null): void
    {
        $this->timezone = auth()->user()?->timezone ?? 'UTC';

        if ($capsule?->exists) {
            Gate::authorize('update', $capsule);
            $this->capsuleId = $capsule->id;
            $this->version = $capsule->lock_version;
            $this->title = $capsule->title;
            $this->body = $capsule->body;
            if ($capsule->opens_at) {
                $local = $capsule->opens_at->setTimezone($this->timezone);
                $this->opening = $local->format('Y-m-d\TH:i');
                $this->openingOffset = $local->format('P');
            }
        }
    }

    public function save(): void
    {
        $capsule = $this->authorizedCapsule();

        $data = $this->validate([
            'title' => ['bail', 'required', 'string', 'max:'.CapsuleText::MAX_TITLE, new CapsuleText],
            'body' => ['bail', 'required', 'string', 'max:'.CapsuleText::MAX_BODY, new CapsuleText],
            'opening' => ['nullable', 'string', 'max:16'],
            'openingOffset' => ['nullable', 'string', 'max:6'],
        ]);
        $data['opens_at'] = null;

        if ($this->opening !== '') {
            $choices = LocalOpeningTime::candidates($this->opening, $this->timezone);

            if ($choices === []) {
                $this->addError('opening', 'Такого времени в выбранном часовом поясе нет. Проверьте дату и время.');

                return;
            }

            if (count($choices) > 1 && ! isset($choices[$this->openingOffset])) {
                $this->addError('openingOffset', 'Выберите смещение UTC для этого времени.');

                return;
            }

            $data['opens_at'] = $choices[$this->openingOffset] ?? reset($choices);
        }
        if ($capsule) {
            $capsule->fill($data);
            $updated = auth()->user()->capsules()
                ->whereKey($capsule->id)
                ->where('lock_version', $this->version)
                ->whereDoesntHave('publication')
                ->update([...$capsule->getDirty(), 'lock_version' => $this->version + 1]);

            if ($updated !== 1) {
                $this->addError('conflict', 'Черновик уже изменён в другой вкладке. Скопируйте свой текст и откройте свежую версию.');

                return;
            }

            session()->flash('status', 'Черновик сохранён.');
        } else {
            $author = auth()->user();
            $capsule = RateLimiter::attempt(
                'capsules:create:'.$author->id,
                12,
                fn () => $author->capsules()->create($data),
                60,
            );

            if ($capsule === false) {
                $this->addError('creationLimit', 'Слишком много новых черновиков. Попробуйте через минуту.');

                return;
            }
        }

        $this->redirectRoute('capsules.edit', $capsule);
    }

    public function render(): View
    {
        $this->authorizedCapsule();

        return view('livewire.capsule-draft-editor', [
            'offsets' => array_keys(LocalOpeningTime::candidates($this->opening, $this->timezone)),
        ]);
    }

    public function updatedOpening(): void
    {
        $this->openingOffset = '';
    }

    private function authorizedCapsule(): ?Capsule
    {
        if ($this->capsuleId === null) {
            Gate::authorize('create', Capsule::class);

            return null;
        }

        $capsule = Capsule::findOrFail($this->capsuleId);
        Gate::authorize('update', $capsule);

        return $capsule;
    }
}
