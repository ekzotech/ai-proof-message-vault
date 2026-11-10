<?php

namespace App\Policies;

use App\Models\Capsule;
use App\Models\User;

class CapsulePolicy
{
    public function create(User $user): bool
    {
        return true;
    }

    public function view(User $user, Capsule $capsule): bool
    {
        return $user->getKey() === $capsule->author_id;
    }

    public function update(User $user, Capsule $capsule): bool
    {
        return $this->view($user, $capsule) && ! $capsule->publication()->exists();
    }

    public function publish(User $user, Capsule $capsule): bool
    {
        return $this->view($user, $capsule) && $user->hasVerifiedEmail();
    }
}
