<?php

namespace App\Livewire;

use App\Models\Capsule;
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
            'title' => ['required', 'string', 'max:120'],
            'body' => ['required', 'string', 'max:12000'],
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
