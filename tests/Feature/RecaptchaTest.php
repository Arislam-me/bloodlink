<?php

namespace Tests\Feature;

use App\Models\User;
use App\Rules\Recaptcha;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class RecaptchaTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['services.recaptcha.enabled' => true, 'services.recaptcha.site_key' => 'site-key', 'services.recaptcha.secret_key' => 'secret-key']);
    }

    public function test_login_and_registration_pages_show_the_recaptcha_box(): void
    {
        $this->get('/login')->assertSee('g-recaptcha')->assertSee('site-key');
        $this->get('/register')->assertSee('g-recaptcha');
    }

    public function test_login_is_rejected_without_a_recaptcha_token(): void
    {
        Http::fake();
        User::factory()->create(['email' => 'a@example.com']);

        $this->post('/login', ['login' => 'a@example.com', 'password' => 'password'])
            ->assertSessionHasErrors('g-recaptcha-response');
        $this->assertGuest();
        Http::assertNothingSent();
    }

    public function test_login_is_rejected_when_google_says_the_token_is_invalid(): void
    {
        Http::fake([Recaptcha::VERIFY_URL => Http::response(['success' => false])]);
        User::factory()->create(['email' => 'a@example.com']);

        $this->post('/login', ['login' => 'a@example.com', 'password' => 'password', 'g-recaptcha-response' => 'bad'])
            ->assertSessionHasErrors('g-recaptcha-response');
        $this->assertGuest();
    }

    public function test_login_succeeds_with_a_valid_token(): void
    {
        Http::fake([Recaptcha::VERIFY_URL => Http::response(['success' => true])]);
        $user = User::factory()->create(['email' => 'a@example.com']);

        $this->post('/login', ['login' => 'a@example.com', 'password' => 'password', 'g-recaptcha-response' => 'good'])
            ->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($user);

        Http::assertSent(fn ($request) => $request['secret'] === 'secret-key' && $request['response'] === 'good');
    }

    public function test_registration_requires_recaptcha(): void
    {
        Http::fake();

        $this->post('/register', ['name' => 'Test User'])->assertSessionHasErrors('g-recaptcha-response');
    }

    public function test_admin_login_page_shows_the_recaptcha_widget(): void
    {
        $this->get('/admin/login')->assertOk()->assertSee('recaptcha/api.js', false);
    }
}
