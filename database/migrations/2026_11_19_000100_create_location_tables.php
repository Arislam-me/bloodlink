<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Bangladesh administrative hierarchy: Division > District > Upazila (or city thana) > Area.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('blood_groups', function (Blueprint $table) {
            $table->id();
            $table->string('code', 3)->unique(); // A+, A-, B+, ...
            $table->unsignedTinyInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('divisions', function (Blueprint $table) {
            $table->id();
            $table->string('name', 60)->unique();
            $table->string('bn_name', 60)->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->timestamps();
        });

        Schema::create('districts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('division_id')->constrained()->cascadeOnUpdate()->restrictOnDelete();
            $table->string('name', 60);
            $table->string('bn_name', 60)->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->timestamps();
            $table->unique(['division_id', 'name']);
        });

        Schema::create('upazilas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('district_id')->constrained()->cascadeOnUpdate()->restrictOnDelete();
            $table->string('name', 80);
            $table->string('bn_name', 80)->nullable();
            $table->boolean('is_city_thana')->default(false);
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->timestamps();
            $table->unique(['district_id', 'name']);
        });

        Schema::create('areas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('upazila_id')->constrained()->cascadeOnUpdate()->restrictOnDelete();
            $table->string('name', 100);
            // Areas typed by users are created unapproved; admins can approve/rename/merge.
            $table->boolean('is_approved')->default(true)->index();
            $table->timestamps();
            $table->unique(['upazila_id', 'name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('areas');
        Schema::dropIfExists('upazilas');
        Schema::dropIfExists('districts');
        Schema::dropIfExists('divisions');
        Schema::dropIfExists('blood_groups');
    }
};
