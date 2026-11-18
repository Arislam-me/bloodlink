<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DonorSearchTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedReference();
    }

    public function test_public_search_never_exposes_contact_details(): void
    {
        $donor = $this->donor('A+', [], null, ['name' => 'Karim Hossain', 'phone' => '01911111111', 'email' => 'karim@example.com']);

        $this->get('/donors?availability=any')
            ->assertOk()
            ->assertSee('Karim H.')
            ->assertDontSee('Karim Hossain')
            ->assertDontSee('01911111111')
            ->assertDontSee('karim@example.com')
            ->assertSee($donor->public_id);
    }

    public function test_filters_by_blood_group_location_and_availability(): void
    {
        $match = $this->donor('A+', [], null, ['name' => 'Match Person']);
        $this->donor('B+', [], null, ['name' => 'Wrong Group']);
        $this->donor('A+', [], $this->otherUpazila, ['name' => 'Other Thana']);
        $this->donor('A+', ['is_available' => false], null, ['name' => 'Busy Person']);

        $res = $this->get('/donors?'.http_build_query([
            'blood_group' => $this->bloodGroup('A+')->id,
            'district' => $this->district->id,
            'upazila' => $this->upazila->id,
            'availability' => 'available',
        ]))->assertOk();

        $res->assertSee('Match P.')->assertDontSee('Wrong G.')->assertDontSee('Other T.')->assertDontSee('Busy P.');
    }

    public function test_hidden_suspended_and_unverified_email_donors_are_excluded(): void
    {
        $this->donor('O-', ['is_listed' => false], null, ['name' => 'Hidden One']);
        $this->donor('O-', [], null, ['name' => 'Unverified One', 'phone_verified_at' => null]);
        $suspended = $this->donor('O-', [], null, ['name' => 'Suspended One']);
        $suspended->user->forceFill(['status' => 'suspended'])->save();

        $this->get('/donors?availability=any')->assertDontSee('Hidden O.')->assertDontSee('Unverified O.')->assertDontSee('Suspended O.');
    }

    public function test_invalid_filters_and_deep_pagination_are_rejected(): void
    {
        $this->get('/donors?blood_group=abc')->assertSessionHasErrors('blood_group');
        $this->get('/donors?page=500')->assertSessionHasErrors('page');
    }

    public function test_donor_details_page_masks_identity_for_guests(): void
    {
        $donor = $this->donor('AB+', ['share_phone_on_unlock' => true], null, ['name' => 'Salma Begum', 'phone' => '01722222222']);

        $this->get(route('donors.show', $donor))
            ->assertOk()->assertSee('Salma B.')->assertDontSee('Salma Begum')->assertDontSee('01722222222')
            ->assertSee('নম্বর দেখতে লগইন করুন');
    }

    public function test_numeric_ids_cannot_be_used_to_enumerate_donors(): void
    {
        $donor = $this->donor();
        $this->get('/donors/'.$donor->id)->assertNotFound();
    }

    public function test_location_api_returns_children(): void
    {
        $this->getJson("/api/locations/divisions/{$this->division->id}/districts")->assertOk()->assertJsonCount(2);
        $this->getJson("/api/locations/districts/{$this->district->id}/upazilas")->assertOk()->assertJsonFragment(['name' => 'Dhanmondi']);
    }

    public function test_search_is_rate_limited(): void
    {
        for ($i = 0; $i < 30; $i++) {
            $this->get('/donors');
        }
        $this->get('/donors')->assertStatus(429);
    }
}
