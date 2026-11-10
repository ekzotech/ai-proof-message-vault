<?php

namespace App\Livewire;

use Illuminate\Contracts\View\View;
use Livewire\Component;

class CapsuleExample extends Component
{
    public bool $opened = false;

    public function toggle(): void
    {
        $this->opened = ! $this->opened;
    }

    public function render(): View
    {
        return view('livewire.capsule-example');
    }
}
