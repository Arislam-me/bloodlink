<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * After a donation the donor is automatically unavailable for a cooldown period
 * (config: bloodlink.donation_cooldown_months, default 4).
 *  - available_from:   date the donor becomes available again
 *  - auto_unavailable: true when the system (not the donor) turned availability off,
 *                      so the scheduler knows it may switch it back on.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('donor_profiles', function (Blueprint $table) {
            $table->date('available_from')->nullable()->after('is_available');
            $table->boolean('auto_unavailable')->default(false)->after('available_from');
            $table->index(['auto_unavailable', 'available_from']);
        });

        // Apply the rule to existing donors who donated within the cooldown window.
        $months = (int) config('bloodlink.donation_cooldown_months', 4);
        DB::table('donor_profiles')
            ->whereNotNull('last_donation_date')
            ->whereRaw('DATE_ADD(last_donation_date, INTERVAL ? MONTH) > CURDATE()', [$months])
            ->update([
                'is_available' => false,
                'auto_unavailable' => true,
                'available_from' => DB::raw("DATE_ADD(last_donation_date, INTERVAL {$months} MONTH)"),
            ]);
    }

    public function down(): void
    {
        Schema::table('donor_profiles', function (Blueprint $table) {
            $table->dropIndex(['auto_unavailable', 'available_from']);
            $table->dropColumn(['available_from', 'auto_unavailable']);
        });
    }
};
