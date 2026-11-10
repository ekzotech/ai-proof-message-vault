<?php

namespace App\Livewire;

use App\Models\Capsule;
use App\Rules\CapsuleText;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;

class CapsuleDraftEditor extends Component
{
    public string $title = '';

    public string $body = '';

    public function save(): void
    {
        Gate::authorize('create', Capsule::class);

        $data = $this->validate([
            'title' => ['bail', 'required', 'string', 'max:'.CapsuleText::MAX_TITLE, new CapsuleText],
            'body' => ['bail', 'required', 'string', 'max:'.CapsuleText::MAX_BODY, new CapsuleText],
        ]);
        $capsule = auth()->user()->capsules()->create($data);

        $this->redirectRoute('capsules.edit', $capsule);
    }

    public function render(): View
    {
        Gate::authorize('create', Capsule::class);

        return view('livewire.capsule-draft-editor');
    }
}
