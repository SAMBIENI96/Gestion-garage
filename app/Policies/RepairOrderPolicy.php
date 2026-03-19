<?php

namespace App\Policies;

use App\Models\User;
use App\Models\RepairOrder;

class RepairOrderPolicy
{
    public function updateStatut(User $user, RepairOrder $repair): bool
    {
        return $user->isMecanicien() && $repair->assigned_to === $user->id;
    }

    public function assign(User $user): bool
    {
        return $user->isPatron();
    }

    public function view(User $user, RepairOrder $repair): bool
    {
        if ($user->isPatron() || $user->isAccueil()) return true;
        return $user->isMecanicien() && $repair->assigned_to === $user->id;
    }
}
