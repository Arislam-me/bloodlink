<?php

namespace Tests\Feature;

use App\Enums\UserStatus;
use App\Models\Upazila;
use App\Models\User;
use App\Models\UserConsent;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedReference();
    }

    private function registrationData(array $overrides = []): array
    {
        return [
            'name' => 'Rahim Uddin',
            'email' => 'Rahim@Example.com',
            'phone' => '+880 1712-345678',
            'password' => 'secret123',
            'password_confirmation' => 'secret123',
            'blood_group_id' => $this->bloodGroup('B+')->id,
            'division_id' => $this->division->id,
            'district_id' => $this->district->id,
            'upazila_id' => $this->upazila->id,
            'area_name' => 'road 27',
            'facebook_url' => 'fb.com/rahim.uddin',
            'whatsapp_number' => '+880 1812-000000',
            'date_of_birth' => '1995-06-15',
            'weight_kg' => '62',
            'last_donation_date' => now()->subMonths(5)->toDateString(),
            'is_listed' => '1',
            'terms' => '1',
            ...$overrides,
        ];
    }

    public function test_registration_creates_user_profile_consents_and_sends_verifications(): void
    {
        Notification::fake();

        $this->post('/register', $this->registrationData())
            ->assertRedirect(route('phone.verify.notice'));

        $user = User::where('email', 'rahim@example.com')->firstOrFail();
        $this->assertAuthenticatedAs($user);
        $this->assertSame('01712345678', $user->phone, 'phone is normalized');
        $this->assertNull($user->phone_verified_at);
        $this->assertSame('B+', $user->donorProfile->bloodGroup->code);
        $this->assertSame($this->district->id, $user->donorProfile->district_id, 'district derived from upazila');
        $this->assertSame('Road 27', $user->donorProfile->area->name);
        $this->assertTrue($user->donorProfile->area->is_approved, 'typed areas are auto-approved');
        $this->assertSame('https://www.facebook.com/rahim.uddin', $user->donorProfile->facebook_url);
        $this->assertSame('01812000000', $user->donorProfile->whatsapp_number);
        $this->assertSame('1995-06-15', $user->donorProfile->date_of_birth->toDateString());
        $this->assertSame(62, $user->donorProfile->weight_kg);
        $this->assertSame(1, $user->donations()->count(), 'last donation date recorded in history');
        $this->assertSame(3, $user->consents()->count());
        $this->assertTrue($user->consents()->where('consent_type', UserConsent::TERMS)->value('granted'));

        // Phone OTP is the only verification: no email is sent at registration.
        Notification::assertNothingSent();
        $this->assertCount(1, $this->sms->sent);
        $this->assertSame('01712345678', $this->sms->sent[0]['phone']);

        // Everyone is listed; the phone is shown on unlock (consented via the terms).
        $this->assertTrue($user->donorProfile->is_listed);
        $this->assertTrue($user->donorProfile->share_phone_on_unlock);
        $this->assertTrue($user->consents()->where('consent_type', UserConsent::PHONE_ON_UNLOCK)->value('granted'));
    }

    public function test_bangla_names_are_accepted(): void
    {
        $this->post('/register', $this->registrationData(['name' => 'মোঃ রহিম উদ্দিন']))->assertSessionHasNoErrors();
        $this->assertSame('মোঃ রহিম উদ্দিন', User::first()->name);
    }

    public function test_email_verification_routes_no_longer_exist(): void
    {
        $this->actingAs(User::factory()->create())->get('/verify-email')->assertNotFound();
    }

    public function test_registration_validates_input(): void
    {
        $this->post('/register', $this->registrationData([
            'phone' => '12345', 'terms' => null, 'upazila_id' => 999999, 'name' => '<script>', 'facebook_url' => 'https://evil.example/x', 'whatsapp_number' => '123', 'date_of_birth' => now()->addDay()->toDateString(), 'weight_kg' => 5,
        ]))->assertSessionHasErrors(['phone', 'terms', 'upazila_id', 'name', 'facebook_url', 'whatsapp_number', 'date_of_birth', 'weight_kg']);

        $this->assertGuest();
    }

    public function test_upazila_must_belong_to_selected_district(): void
    {
        $gazipurUpazila = Upazila::where('name', 'Gazipur Sadar')->first();

        $this->post('/register', $this->registrationData(['upazila_id' => $gazipurUpazila->id]))
            ->assertSessionHasErrors('upazila_id');
    }

    public function test_honeypot_blocks_bots(): void
    {
        $this->post('/register', $this->registrationData(['website' => 'http://spam']))
            ->assertSessionHasErrors('website');
        $this->assertDatabaseCount('users', 0);
    }

    public function test_phone_otp_verification(): void
    {
        $this->post('/register', $this->registrationData());
        $user = User::first();
        $code = $this->sms->lastCode();
        $this->assertNotNull($code);

        $this->post('/verify-phone', ['code' => $code === '000000' ? '111111' : '000000'])->assertSessionHasErrors('code');
        $this->assertNull($user->fresh()->phone_verified_at);

        $this->post('/verify-phone', ['code' => $code])->assertRedirect();
        $this->assertNotNull($user->fresh()->phone_verified_at);
    }

    public function test_otp_locks_after_too_many_attempts(): void
    {
        $this->post('/register', $this->registrationData());
        $code = $this->sms->lastCode();
        $wrong = $code === '000000' ? '111111' : '000000';

        for ($i = 0; $i < 5; $i++) {
            $this->post('/verify-phone', ['code' => $wrong]);
        }
        // Even the correct code is now rejected; a new code must be requested.
        $this->post('/verify-phone', ['code' => $code])->assertSessionHasErrors('code');
        $this->assertNull(User::first()->phone_verified_at);
    }

    public function test_login_with_email_or_phone(): void
    {
        $user = User::factory()->create(['email' => 'a@example.com', 'phone' => '01812345678']);

        $this->post('/login', ['login' => 'a@example.com', 'password' => 'password'])->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($user);
        $this->post('/logout');

        $this->post('/login', ['login' => '01812-345678', 'password' => 'password'])->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($user);
    }

    public function test_login_is_rate_limited_after_failed_attempts(): void
    {
        User::factory()->create(['email' => 'a@example.com']);

        for ($i = 0; $i < 5; $i++) {
            $this->post('/login', ['login' => 'a@example.com', 'password' => 'wrong']);
        }
        $this->post('/login', ['login' => 'a@example.com', 'password' => 'password'])
            ->assertSessionHasErrors('login');
        $this->assertGuest();
    }

    public function test_suspended_users_cannot_sign_in_or_keep_sessions(): void
    {
        $user = User::factory()->suspended()->create(['email' => 's@example.com']);

        $this->post('/login', ['login' => 's@example.com', 'password' => 'password'])->assertSessionHasErrors('login');
        $this->assertGuest();

        // An existing session is terminated on the next request.
        $this->actingAs($user)->get('/dashboard')->assertRedirect(route('login'));
        $this->assertGuest();
    }

    public function test_deactivation_and_reactivation(): void
    {
        $user = User::factory()->create(['email' => 'd@example.com']);

        $this->actingAs($user)->post('/profile/deactivate', ['password' => 'password'])->assertRedirect(route('home'));
        $this->assertSame(UserStatus::Deactivated, $user->fresh()->status);
        $this->assertGuest();

        $this->post('/login', ['login' => 'd@example.com', 'password' => 'password'])->assertRedirect();
        $this->assertSame(UserStatus::Active, $user->fresh()->status);
    }

    public function test_password_reset_does_not_reveal_unknown_emails(): void
    {
        $this->post('/forgot-password', ['email' => 'nobody@example.com'])
            ->assertSessionHas('success')->assertSessionHasNoErrors();
    }
}
