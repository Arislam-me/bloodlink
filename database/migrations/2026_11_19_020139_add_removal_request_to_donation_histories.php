<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Donors can no longer delete donation history themselves; they request removal
 * and an administrator approves (deletes) or rejects it.
 *
 * Also applies the simplified donor settings to existing profiles: everyone is
 * listed and reachable, and the phone is shown to verified members who unlock
 * the profile (profiles hidden by a pending data-deletion request stay hidden).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('donation_histories', function (Blueprint $table) {
            $table->timestamp('removal_requested_at')->nullable()->after('verified_by')->index();
            $table->string('removal_reason', 500)->nullable()->after('removal_requested_at');
        });

        $hidden = DB::table('data_deletion_requests')->where('status', 'pending')->pluck('user_id');

        DB::table('donor_profiles')->whereNotIn('user_id', $hidden)->update([
            'is_listed' => true,
            'accepts_contact_requests' => true,
            'share_phone_on_unlock' => true,
        ]);
    }

    public function down(): void
    {
        Schema::table('donation_histories', function (Blueprint $table) {
            $table->dropIndex(['removal_requested_at']);
            $table->dropColumn(['removal_requested_at', 'removal_reason']);
        });
    }
};
