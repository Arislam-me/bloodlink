<?php

namespace Tests\Feature;

use App\Models\Area;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProfileUpdateTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedReference();
    }

    /** @return array<string, mixed> */
    private function payload(array $overrides = []): array
    {
        return [
            'name' => 'Karim Hossain',
            'email' => 'karim@example.com',
            'phone' => '01712345678',
            'blood_group_id' => $this->bloodGroup('A+')->id,
            'division_id' => $this->division->id,
            'district_id' => $this->district->id,
            'upazila_id' => $this->upazila->id,
            'facebook_url' => 'https://facebook.com/karim.hossain',
            'date_of_birth' => '1990-01-20',
            'weight_kg' => 70,
            ...$overrides,
        ];
    }

    public function test_donor_can_type_their_own_area_name(): void
    {
        $profile = $this->donor('A+', [], null, ['email' => 'karim@example.com', 'phone' => '01712345678']);

        $this->actingAs($profile->user)->patch('/profile', $this->payload(['area_name' => 'জিগাতলা বাসস্ট্যান্ড']))
            ->assertSessionHasNoErrors()->assertSessionHas('success');

        $area = $profile->fresh()->area;
        $this->assertSame('জিগাতলা বাসস্ট্যান্ড', $area->name);
        $this->assertSame($this->upazila->id, $area->upazila_id);
        $this->assertTrue($area->is_approved, 'typed areas are auto-approved');
    }

    public function test_typing_an_existing_area_reuses_it(): void
    {
        $existing = Area::create(['upazila_id' => $this->upazila->id, 'name' => 'Dhanmondi 27', 'is_approved' => true]);
        $profile = $this->donor('A+', [], null, ['email' => 'karim@example.com', 'phone' => '01712345678']);

        $this->actingAs($profile->user)->patch('/profile', $this->payload(['area_name' => 'dhanmondi 27']));

        $this->assertSame($existing->id, $profile->fresh()->area_id);
        $this->assertSame(1, Area::count());
    }

    public function test_facebook_link_is_required_and_whatsapp_is_optional(): void
    {
        $profile = $this->donor('A+', [], null, ['email' => 'karim@example.com', 'phone' => '01712345678']);

        $this->actingAs($profile->user)->patch('/profile', $this->payload(['facebook_url' => '']))
            ->assertSessionHasErrors('facebook_url');

        $this->actingAs($profile->user)->patch('/profile', $this->payload(['facebook_url' => 'm.facebook.com/karim.h/', 'whatsapp_number' => '']))
            ->assertSessionHasNoErrors();
        $this->assertSame('https://www.facebook.com/karim.h', $profile->fresh()->facebook_url);
        $this->assertNull($profile->fresh()->whatsapp_number);
    }

    public function test_birthday_and_weight_are_required_and_saved(): void
    {
        $profile = $this->donor('A+', [], null, ['email' => 'karim@example.com', 'phone' => '01712345678']);

        $this->actingAs($profile->user)->patch('/profile', $this->payload(['date_of_birth' => '', 'weight_kg' => '']))
            ->assertSessionHasErrors(['date_of_birth', 'weight_kg']);

        $this->actingAs($profile->user)->patch('/profile', $this->payload(['date_of_birth' => '1988-03-10', 'weight_kg' => 75]))
            ->assertSessionHasNoErrors();
        $this->assertSame('1988-03-10', $profile->fresh()->date_of_birth->toDateString());
        $this->assertSame(75, $profile->fresh()->weight_kg);
    }

    public function test_changing_phone_requires_verifying_it_again(): void
    {
        $profile = $this->donor('A+', [], null, ['email' => 'karim@example.com', 'phone' => '01712345678']);

        $this->actingAs($profile->user)->patch('/profile', $this->payload(['phone' => '01899999999']))
            ->assertRedirect(route('phone.verify.notice'));

        $this->assertNull($profile->user->fresh()->phone_verified_at);
        $this->assertSame('01899999999', $this->sms->sent[0]['phone']);
    }
}
