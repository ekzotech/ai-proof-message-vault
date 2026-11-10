<?php

namespace App\Livewire;

use App\Models\Capsule;
use App\Rules\CapsuleText;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Locked;
use Livewire\Component;

class CapsuleDraftEditor extends Component
{
    #[Locked]
    public ?int $capsuleId = null;

    public string $title = '';

    public string $body = '';

    public function mount(?Capsule $capsule = null): void
    {
        if ($capsule?->exists) {
            Gate::authorize('update', $capsule);
            $this->capsuleId = $capsule->id;
            $this->title = $capsule->title;
            $this->body = $capsule->body;
        }
    }

    public function save(): void
    {
        $capsule = $this->authorizedCapsule();

        $data = $this->validate([
            'title' => ['bail', 'required', 'string', 'max:'.CapsuleText::MAX_TITLE, new CapsuleText],
            'body' => ['bail', 'required', 'string', 'max:'.CapsuleText::MAX_BODY, new CapsuleText],
        ]);
        if ($capsule) {
            $capsule->update($data);
            session()->flash('status', 'Черновик сохранён.');
        } else {
            $capsule = auth()->user()->capsules()->create($data);
        }

        $this->redirectRoute('capsules.edit', $capsule);
    }

    public function render(): View
    {
        $this->authorizedCapsule();

        return view('livewire.capsule-draft-editor');
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
