<?php

namespace App\Http\Controllers;

use App\Services\ContactViewService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(Request $request, ContactViewService $views): View
    {
        $user = $request->user()->load('donorProfile.bloodGroup', 'donorProfile.district', 'donorProfile.upazila', 'donorProfile.area');

        return view('dashboard.index', [
            'user' => $user,
            'profile' => $user->donorProfile,
            'viewsThisMonth' => $views->usedThisMonth($user),
            'donationCount' => $user->donations()->count(),
            'myRequests' => $user->emergencyRequests()->with('bloodGroup', 'district', 'upazila')->latest()->limit(3)->get(),
            'notifications' => $user->notifications()->latest()->limit(6)->get(),
            'unreadCount' => $user->unreadNotifications()->count(),
        ]);
    }
}
