<?php

namespace App\Policies;

use App\Models\User;
use App\Models\WhatsappAccount;

class WhatsappAccountPolicy
{
    public function view(User $user, WhatsappAccount $account): bool
    {
        if ($user->isSuperAdmin()) return true;

        $owner = $user->getBusinessOwner() ?? $user;
        return $account->user_id === $owner->id;
    }

    public function update(User $user, WhatsappAccount $account): bool
    {
        if ($user->isSuperAdmin()) return true;

        return $account->user_id === $user->id;
    }

    public function delete(User $user, WhatsappAccount $account): bool
    {
        if ($user->isSuperAdmin()) return true;

        return $account->user_id === $user->id;
    }
}