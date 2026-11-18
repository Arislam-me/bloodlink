<?php

namespace App\Policies;

use App\Enums\EmergencyStatus;
use App\Models\EmergencyRequest;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class EmergencyRequestPolicy
{
    /** Pending (unmoderated) requests are visible only to their owner and admins. */
    public function view(?User $user, EmergencyRequest $request): bool
    {
        if ($user && ($user->id === $request->user_id || $user->isAdmin())) {
            return true;
        }

        return $request->status !== EmergencyStatus::Pending;
    }

    public function create(User $user): Response
    {
        return $user->hasVerifiedPhone()
            ? Response::allow()
            : Response::deny('রক্তের অনুরোধ দিতে আগে মোবাইল নম্বর যাচাই করুন।');
    }

    /** Owner (or admin) can mark fulfilled / cancel while open. */
    public function manage(User $user, EmergencyRequest $request): bool
    {
        return ($user->id === $request->user_id || $user->isAdmin()) && $request->status->isOpen();
    }

    /** A verified donor offers to help with someone else's active request. */
    public function respond(User $user, EmergencyRequest $request): Response
    {
        if ($user->id === $request->user_id) {
            return Response::deny('এটি আপনার নিজের অনুরোধ।');
        }
        if (! $user->hasVerifiedPhone()) {
            return Response::deny('সাড়া দিতে আগে মোবাইল নম্বর যাচাই করুন।');
        }

        return $request->status === EmergencyStatus::Active && $request->expires_at->isFuture()
            ? Response::allow()
            : Response::deny('এই অনুরোধটি আর চালু নেই।');
    }

    /** Requester's phone (if they chose phone contact) is shown only to verified, signed-in users. */
    public function viewContact(User $user, EmergencyRequest $request): bool
    {
        return $user->id === $request->user_id || $user->isAdmin()
            || ($user->hasVerifiedPhone() && $request->status === EmergencyStatus::Active);
    }
}
