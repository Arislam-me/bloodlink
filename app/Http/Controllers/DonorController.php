<?php

namespace App\Http\Controllers;

use App\Http\Requests\DonorSearchRequest;
use App\Models\BloodGroup;
use App\Models\Division;
use App\Models\DonorProfile;
use App\Services\ContactViewService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DonorController extends Controller
{
    public function __construct(private readonly ContactViewService $views) {}

    /** Public search. Results never include contact details or exact addresses. */
    public function index(DonorSearchRequest $request): View
    {
        $filters = $request->filters();

        $donors = DonorProfile::query()
            ->searchable()
            ->filter($filters)
            ->sorted($filters['sort'] ?? null)
            ->with(['user:id,name,verified_at', 'bloodGroup:id,code', 'district:id,name', 'upazila:id,name'])
            ->paginate(config('bloodlink.search_per_page'))
            ->withQueryString();

        return view('donors.index', [
            'donors' => $donors,
            'filters' => $filters,
            'bloodGroups' => BloodGroup::options(),
            'divisions' => Division::options(),
        ]);
    }

    /**
     * Donor details. Everyone sees masked public info; a phone-verified member who
     * opens the profile sees the full name, area, phone and social media links.
     */
    public function show(Request $request, DonorProfile $donor): View
    {
        $this->authorize('view', $donor);
        $donor->load('user', 'bloodGroup', 'division', 'district', 'upazila', 'area');

        $viewer = $request->user();

        return view('donors.show', [
            'donor' => $donor,
            'unlocked' => $viewer && $this->views->hasUnlocked($viewer, $donor),
            'isOwner' => $viewer?->id === $donor->user_id,
            'donationCount' => $donor->user->donations()->count(),
        ]);
    }

    /** POST — records the view (for admin monitoring) and reveals the number. */
    public function unlock(Request $request, DonorProfile $donor): RedirectResponse
    {
        $this->authorize('unlock', $donor);

        $this->views->unlock($request->user(), $donor, $request);

        return redirect()->route('donors.show', $donor)->with('success', 'ডোনারের নম্বর দেখানো হচ্ছে।');
    }
}
