<?php

namespace App\Console\Commands;

use App\Models\DonorProfile;
use App\Notifications\DonorAvailableAgain;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

/**
 * Switches donors back to "available" once their post-donation cooldown ends.
 * Only touches profiles the system turned off (auto_unavailable); a donor who
 * chose "unavailable" themselves stays unavailable.
 */
#[Signature('bloodlink:restore-availability')]
#[Description('Mark donors available again after their post-donation cooldown')]
class RestoreDonorAvailability extends Command
{
    public function handle(): int
    {
        $count = 0;

        DonorProfile::with('user')
            ->where('auto_unavailable', true)
            ->whereDate('available_from', '<=', today())
            ->chunkById(200, function ($profiles) use (&$count) {
                foreach ($profiles as $profile) {
                    $profile->forceFill(['is_available' => true, 'available_from' => null, 'auto_unavailable' => false])->save();
                    $profile->user?->notify(new DonorAvailableAgain);
                    $count++;
                }
            });

        $this->info("Restored availability for {$count} donor(s).");

        return self::SUCCESS;
    }
}
