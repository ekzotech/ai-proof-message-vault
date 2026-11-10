<?php

namespace App\Policies;

use App\Models\Capsule;
use App\Models\User;

class CapsulePolicy
{
    public function view(User $user, Capsule $capsule): bool
    {
        return $user->getKey() === $capsule->author_id;
    }

    public function update(User $user, Capsule $capsule): bool
    {
        return $this->view($user, $capsule);
    }
}
