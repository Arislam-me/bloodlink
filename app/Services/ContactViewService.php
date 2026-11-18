<?php

namespace App\Services;

use App\Filament\Resources\Users\UserResource;
use App\Models\DonorProfile;
use App\Models\DonorProfileView;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Records which donor phone numbers each member opens. There is no viewing limit;
 * instead admins can see per-user counts and are alerted when someone opens more
 * than `bloodlink.view_alert_threshold` different numbers in one calendar month.
 *
 * - One row per viewer/donor/month (unique index), so re-opening is not counted twice.
 * - Months follow Asia/Dhaka time.
 */
class ContactViewService
{
    public function period(?Carbon $at = null): string
    {
        return ($at ?? now())->timezone(config('app.timezone'))->format('Y-m');
    }

    public function alertThreshold(): int
    {
        return (int) config('bloodlink.view_alert_threshold');
    }

    /** Unique donor numbers the viewer opened this month. */
    public function usedThisMonth(User $viewer): int
    {
        return DonorProfileView::where('viewer_id', $viewer->id)->where('period', $this->period())->count();
    }

    public function hasUnlocked(User $viewer, DonorProfile $profile): bool
    {
        if ($viewer->id === $profile->user_id) {
            return true; // owners always see their own profile
        }

        return DonorProfileView::where('viewer_id', $viewer->id)
            ->where('donor_profile_id', $profile->id)
            ->where('period', $this->period())
            ->exists();
    }

    /**
     * Open a donor's phone number for the current month.
     *
     * @return bool true if this was a new donor for this month, false if already opened
     */
    public function unlock(User $viewer, DonorProfile $profile, Request $request): bool
    {
        if ($viewer->id === $profile->user_id || $this->hasUnlocked($viewer, $profile)) {
            return false;
        }

        try {
            DonorProfileView::create([
                'viewer_id' => $viewer->id,
                'donor_profile_id' => $profile->id,
                'period' => $this->period(),
                'ip_address' => $request->ip(),
                'user_agent' => Str::limit((string) $request->userAgent(), 250, ''),
            ]);
        } catch (QueryException $e) {
            // Unique-index race: another request inserted the same row first.
            if ((string) $e->getCode() === '23000') {
                return false;
            }
            throw $e;
        }

        $this->alertAdminsIfThresholdCrossed($viewer);

        return true;
    }

    /**
     * Alert every admin (Filament bell) exactly once per month, at the moment a
     * member goes past the threshold. Counts are unique per month, so the
     * "threshold + 1" value is reached only once.
     */
    private function alertAdminsIfThresholdCrossed(User $viewer): void
    {
        $count = $this->usedThisMonth($viewer);
        if ($count !== $this->alertThreshold() + 1) {
            return;
        }

        $admins = User::where('role', 'admin')->where('status', 'active')->get();
        if ($admins->isEmpty()) {
            return;
        }

        DB::afterCommit(fn () => Notification::make()
            ->title("{$viewer->name} opened more than {$this->alertThreshold()} donor numbers this month")
            ->body("{$viewer->phone} · {$count} different donors in ".now()->format('F Y').'. Check for scraping or misuse.')
            ->warning()
            ->icon('heroicon-o-exclamation-triangle')
            ->actions([
                Action::make('review')->label('Review user')->button()
                    ->url(UserResource::getUrl('edit', ['record' => $viewer], panel: 'admin')),
            ])
            ->sendToDatabase($admins));
    }
}
