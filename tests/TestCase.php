<?php

namespace Tests;

use App\Models\BloodGroup;
use App\Models\District;
use App\Models\Division;
use App\Models\DonorProfile;
use App\Models\Upazila;
use App\Models\User;
use App\Services\Sms\SmsSender;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected FakeSms $sms;

    protected Division $division;

    protected District $district;

    protected Upazila $upazila;

    protected Upazila $otherUpazila;

    protected function setUp(): void
    {
        parent::setUp();

        $this->sms = new FakeSms;
        $this->app->instance(SmsSender::class, $this->sms);
    }

    /** Minimal reference data: 8 blood groups + Dhaka > Dhaka > two thanas, and a second district. */
    protected function seedReference(): void
    {
        foreach (BloodGroup::CODES as $i => $code) {
            BloodGroup::create(['code' => $code, 'sort_order' => $i]);
        }
        $this->division = Division::create(['name' => 'Dhaka']);
        $this->district = District::create(['division_id' => $this->division->id, 'name' => 'Dhaka']);
        $this->upazila = Upazila::create(['district_id' => $this->district->id, 'name' => 'Dhanmondi', 'is_city_thana' => true]);
        $this->otherUpazila = Upazila::create(['district_id' => $this->district->id, 'name' => 'Mirpur Model', 'is_city_thana' => true]);
        $gazipur = District::create(['division_id' => $this->division->id, 'name' => 'Gazipur']);
        Upazila::create(['district_id' => $gazipur->id, 'name' => 'Gazipur Sadar']);
    }

    protected function bloodGroup(string $code): BloodGroup
    {
        return BloodGroup::where('code', $code)->firstOrFail();
    }

    /** A fully verified, listed, available donor. */
    protected function donor(string $group = 'O+', array $profile = [], ?Upazila $upazila = null, array $user = []): DonorProfile
    {
        $upazila ??= $this->upazila;

        return DonorProfile::factory()->inUpazila($upazila->load('district'))->create([
            'user_id' => User::factory()->create($user)->id,
            'blood_group_id' => $this->bloodGroup($group)->id,
            'is_available' => true,
            'is_listed' => true,
            'accepts_contact_requests' => true,
            'share_phone_on_unlock' => false,
            'last_donation_date' => null,
            ...$profile,
        ]);
    }
}

/** Captures SMS instead of sending them. */
class FakeSms implements SmsSender
{
    /** @var array<int, array{phone:string, message:string}> */
    public array $sent = [];

    public function send(string $phone, string $message): bool
    {
        $this->sent[] = compact('phone', 'message');

        return true;
    }

    public function lastCode(): ?string
    {
        $last = end($this->sent);

        return $last && preg_match('/\b(\d{6})\b/', $last['message'], $m) ? $m[1] : null;
    }
}
