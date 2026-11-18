<?php

namespace App\Http\Controllers;

use App\Models\DonationHistory;
use App\Services\DonorProfileService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DonationHistoryController extends Controller
{
    public function __construct(private readonly DonorProfileService $profiles) {}

    public function index(Request $request): View
    {
        $user = $request->user();

        return view('donations.index', [
            'donations' => $user->donations()->with('emergencyRequest')->paginate(15),
            'profile' => $user->donorProfile,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'donated_on' => ['required', 'date', 'before_or_equal:today', 'after:1950-01-01'],
            'location' => ['nullable', 'string', 'max:150'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        $exists = $request->user()->donations()->whereDate('donated_on', $data['donated_on'])->exists();
        if ($exists) {
            return back()->withErrors(['donated_on' => 'এই তারিখে আপনার একটি রক্তদান আগেই লেখা আছে।'])->withInput();
        }

        $this->profiles->addDonation($request->user(), $data);

        $profile = $request->user()->donorProfile()->first();
        $message = $profile?->inCooldown()
            ? 'রক্তদান যোগ হয়েছে, ধন্যবাদ! '.$profile->available_from->translatedFormat('j F Y').' পর্যন্ত আপনি বিশ্রামে থাকবেন, তাই এই সময় আপনাকে "অপ্রস্তুত" দেখানো হবে।'
            : 'রক্তদান যোগ হয়েছে। আপনার শেষ রক্তদানের তারিখ আপডেট হয়েছে।';

        return back()->with('success', $message);
    }

    /** Ask an admin to remove a wrong entry; the entry stays until an admin approves. */
    public function requestRemoval(Request $request, DonationHistory $donation): RedirectResponse
    {
        $this->authorize('requestRemoval', $donation);

        $data = $request->validate(['reason' => ['required', 'string', 'min:5', 'max:500']], [
            'reason.required' => 'কেন মুছতে চান, তা সংক্ষেপে লিখুন।',
        ]);

        $donation->forceFill([
            'removal_requested_at' => now(),
            'removal_reason' => strip_tags($data['reason']),
        ])->save();

        return back()->with('success', 'মুছে ফেলার অনুরোধ পাঠানো হয়েছে। অ্যাডমিন দেখে সিদ্ধান্ত নেবেন।');
    }
}
