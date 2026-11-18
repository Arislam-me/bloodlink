<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Date of birth and body weight (kg), required for new registrations
 * (enforced in validation; nullable here so existing profiles stay valid).
 * Only the age (never the full birth date) is shown to other members.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('donor_profiles', function (Blueprint $table) {
            $table->date('date_of_birth')->nullable()->after('blood_group_id');
            $table->unsignedSmallInteger('weight_kg')->nullable()->after('date_of_birth');
        });
    }

    public function down(): void
    {
        Schema::table('donor_profiles', function (Blueprint $table) {
            $table->dropColumn(['date_of_birth', 'weight_kg']);
        });
    }
};
