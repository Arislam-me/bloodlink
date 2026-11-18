<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('donor_profiles', function (Blueprint $table) {
            $table->id();
            // Non-sequential public identifier used in URLs to prevent enumeration/scraping.
            $table->ulid('public_id')->unique();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->foreignId('blood_group_id')->constrained()->restrictOnDelete();
            $table->foreignId('division_id')->constrained()->restrictOnDelete();
            $table->foreignId('district_id')->constrained()->restrictOnDelete();
            $table->foreignId('upazila_id')->constrained()->restrictOnDelete();
            $table->foreignId('area_id')->nullable()->constrained()->nullOnDelete();
            $table->date('last_donation_date')->nullable();
            $table->boolean('is_available')->default(true);
            $table->boolean('accepts_contact_requests')->default(true);
            // Explicit consent: show phone number directly on the unlocked contact profile.
            // When false, the phone is only revealed after the donor accepts a contact request.
            $table->boolean('share_phone_on_unlock')->default(false);
            $table->boolean('is_listed')->default(true); // donor can hide themselves from search entirely
            $table->string('bio', 500)->nullable();
            $table->timestamps();

            $table->index(['blood_group_id', 'district_id', 'upazila_id', 'is_available'], 'donor_search_idx');
            $table->index(['division_id', 'is_listed']);
            $table->index('last_donation_date');
        });

        Schema::create('donation_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->date('donated_on');
            $table->string('location', 150)->nullable(); // hospital / blood bank name
            $table->foreignId('emergency_request_id')->nullable(); // FK added after emergency_requests exists
            $table->string('notes', 500)->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->index(['user_id', 'donated_on']);
        });

        Schema::create('donor_profile_views', function (Blueprint $table) {
            $table->id();
            $table->foreignId('viewer_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('donor_profile_id')->constrained()->cascadeOnDelete();
            // Calendar month bucket in app timezone (Asia/Dhaka), e.g. "2026-11".
            $table->char('period', 7);
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent', 255)->nullable();
            $table->timestamps();

            // One row per viewer/donor/month: repeated views never count twice and
            // concurrent requests from multiple devices cannot create duplicates.
            $table->unique(['viewer_id', 'donor_profile_id', 'period'], 'unique_monthly_view');
            $table->index(['viewer_id', 'period']);
            $table->index(['period', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('donor_profile_views');
        Schema::dropIfExists('donation_histories');
        Schema::dropIfExists('donor_profiles');
    }
};
