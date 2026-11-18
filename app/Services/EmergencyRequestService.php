<?php

namespace App\Services;

use App\Enums\EmergencyStatus;
use App\Jobs\NotifyNearbyDonors;
use App\Models\EmergencyRequest;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Validation\ValidationException;

class EmergencyRequestService
{
    public function __construct(private readonly DonorProfileService $profiles) {}

    /**
     * Requests are auto-approved: they go live and nearby donors are notified
     * immediately. Admins can still cancel fake ones from the panel or via reports.
     *
     * @param  array<string, mixed>  $data  validated
     */
    public function create(User $user, array $data): EmergencyRequest
    {
        $open = $user->emergencyRequests()
            ->whereIn('status', [EmergencyStatus::Pending->value, EmergencyStatus::Active->value])
            ->where('expires_at', '>', now())->count();

        if ($open >= config('bloodlink.max_active_emergency_requests')) {
            throw ValidationException::withMessages([
                'blood_group_id' => 'আপনার '.$open.'টি অনুরোধ এখনো খোলা আছে। আগে পূরণ হওয়া অনুরোধগুলো বন্ধ করুন।',
            ]);
        }

        $request = new EmergencyRequest([
            ...$data,
            ...$this->profiles->locationFromUpazila((int) $data['upazila_id']),
        ]);
        $request->user()->associate($user);
        $request->status = EmergencyStatus::Pending;
        $request->expires_at = Carbon::parse($data['needed_at'])->addHours(config('bloodlink.emergency_expiry_grace_hours'));
        $request->save();

        $this->activate($request);

        return $request;
    }

    public function activate(EmergencyRequest $request, ?User $approver = null): void
    {
        $request->forceFill([
            'status' => EmergencyStatus::Active,
            'approved_at' => now(),
            'approved_by' => $approver?->id,
        ])->save();

        NotifyNearbyDonors::dispatch($request->id);
    }

    public function fulfil(EmergencyRequest $request): void
    {
        $request->forceFill(['status' => EmergencyStatus::Fulfilled, 'units_fulfilled' => $request->units_needed])->save();
    }

    public function cancel(EmergencyRequest $request): void
    {
        $request->forceFill(['status' => EmergencyStatus::Cancelled])->save();
    }

    /** Called by the scheduler. @return int number of requests expired */
    public function expireOverdue(): int
    {
        return EmergencyRequest::whereIn('status', [EmergencyStatus::Pending->value, EmergencyStatus::Active->value])
            ->where('expires_at', '<=', now())
            ->update(['status' => EmergencyStatus::Expired->value, 'updated_at' => now()]);
    }
}
