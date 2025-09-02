<?php

namespace App\Policies;

use App\Models\{TimeBox, User};

class TimeBoxPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, TimeBox $timeBox): bool
    {
        return $user->id === $timeBox->user_id;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, TimeBox $timeBox): bool
    {
        return $user->id === $timeBox->user_id;
    }

    public function delete(User $user, TimeBox $timeBox): bool
    {
        return $user->id === $timeBox->user_id;
    }

    public function restore(User $user, TimeBox $timeBox): bool
    {
        return $user->id === $timeBox->user_id;
    }

    public function forceDelete(User $user, TimeBox $timeBox): bool
    {
        return $user->id === $timeBox->user_id;
    }
}
