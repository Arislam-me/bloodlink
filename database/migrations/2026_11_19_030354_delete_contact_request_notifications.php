<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * The contact-request feature was removed (phone numbers now open on unlock),
 * so its old notifications point at a page that no longer exists. Remove them.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::table('notifications')->whereIn('type', [
            'App\\Notifications\\ContactRequestReceived',
            'App\\Notifications\\ContactRequestAnswered',
        ])->delete();
    }

    public function down(): void
    {
        // Deleted notifications cannot be restored.
    }
};
