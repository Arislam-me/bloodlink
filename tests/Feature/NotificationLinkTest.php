<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class NotificationLinkTest extends TestCase
{
    use RefreshDatabase;

    private function notify(User $user, string $url): string
    {
        $id = (string) Str::uuid();
        $user->notifications()->create([
            'id' => $id,
            'type' => 'App\\Notifications\\Example',
            'data' => ['title' => 'x', 'body' => 'y', 'url' => $url],
        ]);

        return $id;
    }

    public function test_link_to_a_removed_page_falls_back_to_dashboard(): void
    {
        $user = User::factory()->create();
        $id = $this->notify($user, url('/contact-requests'));

        $this->actingAs($user)->get(route('notifications.open', $id))
            ->assertRedirect(route('dashboard'))
            ->assertSessionHas('info');

        $this->assertNotNull($user->notifications()->find($id)->read_at);
    }

    public function test_link_to_an_existing_page_is_followed(): void
    {
        $user = User::factory()->create();
        $id = $this->notify($user, url('/donations'));

        $this->actingAs($user)->get(route('notifications.open', $id))->assertRedirect(url('/donations'));
    }

    public function test_external_links_are_never_followed(): void
    {
        $user = User::factory()->create();
        $id = $this->notify($user, 'https://evil.example/phish');

        $this->actingAs($user)->get(route('notifications.open', $id))->assertRedirect(route('dashboard'));
    }

    public function test_missing_pages_show_a_bangla_error_page(): void
    {
        $this->get('/this-page-does-not-exist')->assertNotFound()->assertSee('পেজটি পাওয়া যায়নি');
    }
}
