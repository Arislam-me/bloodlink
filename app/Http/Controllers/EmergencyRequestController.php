<?php

namespace App\Http\Controllers;

use App\Enums\EmergencyStatus;
use App\Http\Requests\EmergencyRequestStoreRequest;
use App\Models\BloodGroup;
use App\Models\Division;
use App\Models\EmergencyRequest;
use App\Models\EmergencyRequestResponse;
use App\Notifications\EmergencyResponseReceived;
use App\Services\EmergencyRequestService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EmergencyRequestController extends Controller
{
    public function __construct(private readonly EmergencyRequestService $service) {}

    public function index(Request $request): View
    {
        $filters = $request->validate([
            'blood_group' => ['nullable', 'integer', 'exists:blood_groups,id'],
            'division' => ['nullable', 'integer', 'exists:divisions,id'],
            'district' => ['nullable', 'integer', 'exists:districts,id'],
            'upazila' => ['nullable', 'integer', 'exists:upazilas,id'],
            'scope' => ['nullable', 'in:active,mine'],
        ]);

        $scope = ($filters['scope'] ?? null) === 'mine' && $request->user() ? 'mine' : 'active';

        $query = $scope === 'mine'
            ? $request->user()->emergencyRequests()->latest()
            : EmergencyRequest::active()->latest(); // newest requests first

        return view('emergency.index', [
            'requests' => $query->filter($filters)->with('bloodGroup', 'district', 'upazila')
                ->withCount('responses')->paginate(12)->withQueryString(),
            'filters' => $filters,
            'scope' => $scope,
            'bloodGroups' => BloodGroup::options(),
            'divisions' => Division::options(),
        ]);
    }

    public function create(Request $request): View
    {
        $this->authorize('create', EmergencyRequest::class);

        return view('emergency.create', [
            'bloodGroups' => BloodGroup::options(),
            'divisions' => Division::options(),
        ]);
    }

    public function store(EmergencyRequestStoreRequest $request): RedirectResponse
    {
        $this->authorize('create', EmergencyRequest::class);

        $data = $request->payload();
        $data['details'] = isset($data['details']) ? strip_tags($data['details']) : null;

        $emergency = $this->service->create($request->user(), $data);

        return redirect()->route('emergency.show', $emergency)->with('success',
            $emergency->status === EmergencyStatus::Active
                ? "অনুরোধটি প্রকাশ হয়েছে। কাছাকাছি {$emergency->bloodGroup->code} রক্তের ডোনারদের জানানো হচ্ছে।"
                : 'অনুরোধটি জমা হয়েছে। অ্যাডমিন যাচাই করার পর (সাধারণত কয়েক মিনিটে) ডোনারদের জানানো হবে।');
    }

    public function show(Request $request, EmergencyRequest $emergency): View
    {
        $this->authorize('view', $emergency);
        $emergency->load('bloodGroup', 'division', 'district', 'upazila', 'user:id,name,verified_at');
        $user = $request->user();
        $isOwner = $user?->id === $emergency->user_id;

        return view('emergency.show', [
            'emergency' => $emergency,
            'isOwner' => $isOwner,
            'canViewContact' => $user && $user->can('viewContact', $emergency),
            'hasResponded' => $user ? $emergency->responses()->where('donor_id', $user->id)->exists() : false,
            'responses' => $isOwner || $user?->isAdmin()
                ? $emergency->responses()->with('donor.donorProfile.bloodGroup', 'donor.donorProfile.upazila')->latest()->get()
                : collect(),
            'responseCount' => $emergency->responses()->count(),
        ]);
    }

    /** A donor offers to help. The requester sees the donor's public profile and can unlock/contact. */
    public function respond(Request $request, EmergencyRequest $emergency): RedirectResponse
    {
        $this->authorize('respond', $emergency);
        $data = $request->validate(['message' => ['nullable', 'string', 'max:500']]);

        $response = EmergencyRequestResponse::firstOrCreate(
            ['emergency_request_id' => $emergency->id, 'donor_id' => $request->user()->id],
            ['message' => isset($data['message']) ? strip_tags($data['message']) : null],
        );

        if ($response->wasRecentlyCreated) {
            $emergency->user->notify(new EmergencyResponseReceived($response));
        }

        return back()->with('success', 'ধন্যবাদ! অনুরোধকারীকে জানানো হয়েছে যে আপনি রক্ত দিতে পারবেন।');
    }

    public function fulfil(EmergencyRequest $emergency): RedirectResponse
    {
        $this->authorize('manage', $emergency);
        $this->service->fulfil($emergency);

        return back()->with('success', 'অনুরোধটি পূরণ হয়েছে বলে চিহ্নিত করা হলো। ধন্যবাদ!');
    }

    public function cancel(EmergencyRequest $emergency): RedirectResponse
    {
        $this->authorize('manage', $emergency);
        $this->service->cancel($emergency);

        return back()->with('success', 'অনুরোধটি বাতিল করা হয়েছে।');
    }
}
