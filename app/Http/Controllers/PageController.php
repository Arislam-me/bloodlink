<?php

namespace App\Http\Controllers;

use App\Enums\EmergencyStatus;
use App\Models\BloodGroup;
use App\Models\Division;
use App\Models\DonationHistory;
use App\Models\DonorProfile;
use App\Models\EmergencyRequest;
use Illuminate\View\View;

class PageController extends Controller
{
    public function home(): View
    {
        // Real aggregate counts only, cached briefly to keep the home page cheap.
        $stats = cache()->remember('home.stats', 300, fn () => [
            'donors' => DonorProfile::searchable()->count(),
            'available' => DonorProfile::searchable()->where('is_available', true)->count(),
            'fulfilled' => EmergencyRequest::where('status', EmergencyStatus::Fulfilled->value)->count(),
            'donations' => DonationHistory::count(),
        ]);

        return view('pages.home', [
            'stats' => $stats,
            'bloodGroups' => BloodGroup::options(),
            'divisions' => Division::options(),
            'urgent' => EmergencyRequest::active()->with('bloodGroup', 'district', 'upazila')
                ->latest()->limit(3)->get(),
        ]);
    }
}
