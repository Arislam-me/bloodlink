<?php

namespace App\Http\Controllers;

use App\Enums\ReportStatus;
use App\Http\Requests\ReportStoreRequest;
use App\Models\DonorProfile;
use App\Models\EmergencyRequest;
use App\Models\Report;
use Illuminate\Http\RedirectResponse;

class ReportController extends Controller
{
    public function store(ReportStoreRequest $request): RedirectResponse
    {
        $data = $request->validated();

        // Resolve the target by public id; reports point at the User (for donors) or the request.
        $target = $data['type'] === 'donor'
            ? DonorProfile::where('public_id', $data['target'])->firstOrFail()->user
            : EmergencyRequest::where('public_id', $data['target'])->firstOrFail();

        if ($target->is($request->user()) || ($target instanceof EmergencyRequest && $target->user_id === $request->user()->id)) {
            return back()->with('error', 'নিজের বিরুদ্ধে অভিযোগ করা যায় না।');
        }

        $duplicate = Report::where('reporter_id', $request->user()->id)
            ->where('reportable_type', $target->getMorphClass())->where('reportable_id', $target->getKey())
            ->whereIn('status', [ReportStatus::Open->value, ReportStatus::Reviewing->value])->exists();

        if ($duplicate) {
            return back()->with('warning', 'আপনি এটি আগেই রিপোর্ট করেছেন। আমরা বিষয়টি দেখছি।');
        }

        $report = new Report(['reason' => $data['reason'], 'details' => isset($data['details']) ? strip_tags($data['details']) : null]);
        $report->reporter()->associate($request->user());
        $report->reportable()->associate($target);
        $report->status = ReportStatus::Open;
        $report->save();

        return back()->with('success', 'ধন্যবাদ। আমাদের টিম আপনার রিপোর্ট দেখবে।');
    }
}
