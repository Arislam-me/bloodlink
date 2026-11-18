<?php

namespace App\Policies;

use App\Models\DonationHistory;
use App\Models\User;

/**
 * Donation history is an admin-controlled record: donors can add entries and
 * ask for removal, but only administrators delete them (via Filament).
 */
class DonationHistoryPolicy
{
    public function requestRemoval(User $user, DonationHistory $donation): bool
    {
        return $user->id === $donation->user_id && $donation->removal_requested_at === null;
    }

    public function delete(User $user, DonationHistory $donation): bool
    {
        return $user->isAdmin();
    }
}
