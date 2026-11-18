<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Social contact links shown to members who open a donor's profile.
 * Facebook is required for new registrations (enforced in validation; nullable
 * here so existing profiles stay valid), WhatsApp is optional.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('donor_profiles', function (Blueprint $table) {
            $table->string('facebook_url', 255)->nullable()->after('bio');
            $table->string('whatsapp_number', 20)->nullable()->after('facebook_url');
        });
    }

    public function down(): void
    {
        Schema::table('donor_profiles', function (Blueprint $table) {
            $table->dropColumn(['facebook_url', 'whatsapp_number']);
        });
    }
};
