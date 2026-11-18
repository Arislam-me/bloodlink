<?php

namespace App\Policies;

use App\Models\DonorProfile;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class DonorProfilePolicy
{
    /** Public (masked) details page: anyone, if the profile is publicly listed. */
    public function view(?User $user, DonorProfile $profile): bool
    {
        if ($user && ($user->id === $profile->user_id || $user->isAdmin())) {
            return true;
        }

        return $profile->is_listed && $profile->user?->isActive() && $profile->user?->hasVerifiedPhone();
    }

    /** Unlocking the contact profile (counts against the monthly quota). */
    public function unlock(User $user, DonorProfile $profile): Response
    {
        if (! $user->hasVerifiedPhone()) {
            return Response::deny('মোবাইল নম্বর যাচাই করলে তবেই ডোনারের যোগাযোগের তথ্য দেখতে পারবেন।');
        }

        if (! $user->isActive()) {
            return Response::deny('আপনার অ্যাকাউন্ট চালু নেই।');
        }

        return $this->view($user, $profile) ? Response::allow() : Response::denyAsNotFound();
    }

    public function update(User $user, DonorProfile $profile): bool
    {
        return $user->id === $profile->user_id;
    }
}
