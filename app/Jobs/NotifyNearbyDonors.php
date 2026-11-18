<?php

namespace App\Jobs;

use App\Enums\EmergencyStatus;
use App\Enums\UserStatus;
use App\Models\DonorProfile;
use App\Models\EmergencyRequest;
use App\Notifications\EmergencyBloodRequestNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Notification;

/**
 * Notifies nearby donors about an active emergency request.
 *
 * Recipients: listed, available donors who accept contact requests, whose blood
 * group is red-cell compatible, located in the same district (same upazila first),
 * with a verified phone, and whose self-reported last donation is outside the
 * post-donation cooldown. This is a convenience filter — NOT a medical eligibility check.
 */
class NotifyNearbyDonors implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public function __construct(public int $emergencyRequestId) {}

    public function handle(): void
    {
        $request = EmergencyRequest::with('bloodGroup', 'district', 'upazila')->find($this->emergencyRequestId);

        if (! $request || $request->status !== EmergencyStatus::Active || $request->donors_notified_at) {
            return; // idempotent: never notify twice
        }

        // Extra guard on top of is_available: never alert someone still in their post-donation cooldown.
        $gapCutoff = now()->subMonthsNoOverflow(config('bloodlink.donation_cooldown_months'))->toDateString();

        $donors = DonorProfile::query()
            ->with('user')
            ->whereIn('blood_group_id', $request->bloodGroup->compatibleDonorGroupIds())
            ->where('district_id', $request->district_id)
            ->where('is_listed', true)
            ->where('is_available', true)
            ->where('accepts_contact_requests', true)
            ->where('user_id', '!=', $request->user_id)
            ->where(fn ($q) => $q->whereNull('last_donation_date')->orWhere('last_donation_date', '<=', $gapCutoff))
            ->whereHas('user', fn ($u) => $u->where('status', UserStatus::Active->value)
                ->whereNotNull('phone_verified_at'))
            // Closest first: same upazila, then exact blood group match.
            ->orderByRaw('upazila_id = ? DESC', [$request->upazila_id])
            ->orderByRaw('blood_group_id = ? DESC', [$request->blood_group_id])
            ->limit(config('bloodlink.max_notified_donors'))
            ->get();

        Notification::send($donors->pluck('user'), new EmergencyBloodRequestNotification($request));

        $request->forceFill(['donors_notified_at' => now(), 'notified_count' => $donors->count()])->save();
    }
}
