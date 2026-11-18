<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('emergency_requests', function (Blueprint $table) {
            $table->id();
            $table->ulid('public_id')->unique();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('blood_group_id')->constrained()->restrictOnDelete();
            $table->string('patient_condition', 150)->nullable();
            $table->string('hospital_name', 150);
            $table->string('hospital_address', 255);
            $table->foreignId('division_id')->constrained()->restrictOnDelete();
            $table->foreignId('district_id')->constrained()->restrictOnDelete();
            $table->foreignId('upazila_id')->constrained()->restrictOnDelete();
            // Hospital (public place) coordinates picked on OpenStreetMap — never a home address.
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->unsignedTinyInteger('units_needed');
            $table->unsignedTinyInteger('units_fulfilled')->default(0);
            $table->dateTime('needed_at');
            $table->string('contact_method', 20); // in_app | phone
            $table->string('contact_phone', 20)->nullable();
            $table->text('details')->nullable();
            $table->string('status', 20)->default('pending')->index(); // pending|active|fulfilled|cancelled|expired
            $table->dateTime('expires_at')->index();
            $table->timestamp('approved_at')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('donors_notified_at')->nullable();
            $table->unsignedInteger('notified_count')->default(0);
            $table->timestamps();

            $table->index(['status', 'blood_group_id', 'district_id']);
        });

        Schema::table('donation_histories', function (Blueprint $table) {
            $table->foreign('emergency_request_id')->references('id')->on('emergency_requests')->nullOnDelete();
        });

        Schema::create('contact_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('requester_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('donor_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('emergency_request_id')->nullable()->constrained()->nullOnDelete();
            $table->string('message', 1000);
            $table->string('status', 20)->default('pending')->index(); // pending|accepted|declined|cancelled|expired
            $table->string('response_message', 500)->nullable();
            $table->timestamp('responded_at')->nullable();
            $table->dateTime('expires_at');
            $table->timestamps();

            $table->index(['donor_id', 'status']);
            $table->index(['requester_id', 'status']);
        });

        Schema::create('emergency_request_responses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('emergency_request_id')->constrained()->cascadeOnDelete();
            $table->foreignId('donor_id')->constrained('users')->cascadeOnDelete();
            $table->string('message', 500)->nullable();
            $table->timestamps();
            $table->unique(['emergency_request_id', 'donor_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('emergency_request_responses');
        Schema::dropIfExists('contact_requests');
        Schema::table('donation_histories', fn (Blueprint $t) => $t->dropForeign(['emergency_request_id']));
        Schema::dropIfExists('emergency_requests');
    }
};
