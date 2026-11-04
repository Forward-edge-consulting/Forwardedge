<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\Page;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class PortalCleanupTest extends TestCase
{
    use RefreshDatabase;

    public static function retiredPaths(): array
    {
        return array_map(fn ($path) => [$path], [
            '/academy', '/shop', '/shop/data', '/course/old-course',
            '/scholarships', '/scholarships/apply/1', '/scholarships/thank-you',
            '/my-courses', '/student/courses/1/content', '/student/content/1/view',
            '/email-access/token/old-token', '/verify/old-token', '/user/1/photo',
            '/profile', '/register', '/login', '/forgot-password', '/verify-email',
            '/user/cart', '/user/wishlist', '/checkout', '/payment/callback',
            '/ctrl-panel-v2/courses', '/ctrl-panel-v2/course-contents',
            '/ctrl-panel-v2/enrollments', '/ctrl-panel-v2/scholarships/applications',
            '/ctrl-panel-v2/verifications', '/ctrl-panel-v2/orders',
            '/ctrl-panel-v2/transactions', '/ctrl-panel-v2/profile',
            '/ctrl-panel-v2/messages', '/ctrl-panel-v2/emails/contacts', '/ctrl-panel-v2/emails/campaigns',
        ]);
    }

    #[DataProvider('retiredPaths')]
    public function test_retired_pages_are_unavailable_even_to_admin(string $path): void
    {
        $this->actingAs(User::factory()->create(['role' => 'admin']))->get($path)->assertNotFound();
    }

    public function test_retired_write_endpoints_are_unavailable(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'admin']));
        foreach (['/register', '/my-courses', '/scholarships/apply/1', '/user/cart/add',
            '/ctrl-panel-v2/course-contents', '/ctrl-panel-v2/courses',
            '/course-content/1/reviews', '/verify/old-token', '/ctrl-panel-v2/emails/campaigns'] as $path) {
            $this->postJson($path, [])->assertNotFound();
        }
    }

    public function test_historical_student_credentials_cannot_authenticate(): void
    {
        $student = User::factory()->create(['role' => 'user']);
        $this->post('/ctrl-panel-v2/login', ['email' => $student->email, 'password' => 'password'])
            ->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_historical_student_sessions_cannot_manage_portal(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'user']))
            ->get('/ctrl-panel-v2/dashboard')->assertForbidden();
    }

    public function test_public_pages_render_without_retired_navigation(): void
    {
        foreach (['/', '/about', '/services', '/events',
            '/gallery', '/blog', '/contact', '/terms', '/privacy-policy', '/sitemap.xml'] as $path) {
            $this->get($path)->assertOk()->assertDontSee('/academy', false)
                ->assertDontSee('/shop', false)->assertDontSee('/scholarships', false)
                ->assertDontSee('/my-courses', false);
        }
    }

    public function test_admin_dashboard_and_builder_render_without_commerce(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'admin']));
        $page = Page::create(['title' => 'Company information', 'slug' => 'company-information', 'status' => 'published']);
        foreach (['/ctrl-panel-v2/dashboard', '/ctrl-panel-v2/page-builder', '/ctrl-panel-v2/page-builder/create',
            "/ctrl-panel-v2/page-builder/{$page->id}/edit", "/ctrl-panel-v2/page-builder/{$page->id}/blocks",
            '/ctrl-panel-v2/events/create', '/ctrl-panel-v2/events'] as $path) {
            $this->get($path)->assertOk()->assertDontSee('Scholarship')->assertDontSee('My Profile')
                ->assertDontSee('/ctrl-panel-v2/courses', false);
        }
    }

    public function test_course_pages_are_archived_and_retired_blocks_do_not_render(): void
    {
        $oldPage = Page::create(['title' => 'Retired program', 'slug' => 'retired-program', 'status' => 'published',
            'pageable_type' => 'App\\Models\\Course', 'pageable_id' => 1]);
        $page = Page::create(['title' => 'Current information', 'slug' => 'current-information', 'status' => 'published']);
        foreach (['pricing', 'table'] as $type) {
            $page->blocks()->create(['type' => $type, 'data' => ['title' => 'Retired block'], 'is_published' => true]);
        }
        $this->get('/retired-program')->assertNotFound();
        $this->get('/current-information')->assertOk()->assertDontSee('Retired block');
        $this->get('/sitemap.xml')->assertOk()->assertDontSee('retired-program');
        $this->actingAs(User::factory()->create(['role' => 'admin']));
        $this->get('/ctrl-panel-v2/page-builder')->assertOk()->assertDontSee('Retired program');
        $this->get("/ctrl-panel-v2/page-builder/{$oldPage->id}/blocks")->assertNotFound();
        $this->postJson("/ctrl-panel-v2/page-builder/{$page->id}/blocks", ['type' => 'pricing'])->assertUnprocessable();
        $this->postJson('/ctrl-panel-v2/page-builder/store', ['title' => 'Old', 'status' => 'draft', 'owner_type' => 'course', 'owner_id' => 1])->assertUnprocessable();
    }

    public function test_bootcamp_is_created_as_an_event_and_online_registration_is_retired(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin)->post('/ctrl-panel-v2/events', [
            'title' => 'Security bootcamp', 'slug' => 'security-bootcamp', 'type' => 'bootcamp', 'cta_type' => 'none',
            'status' => 'published', 'location' => 'Online', 'start_date' => '2026-12-01T09:00',
            'end_date' => '2026-12-05T17:00',
        ])->assertSessionHasNoErrors()->assertRedirect();
        $event = Event::where('slug', 'security-bootcamp')->firstOrFail();
        $this->assertSame('bootcamp', $event->type);
        auth()->logout();
        $this->get('/events/security-bootcamp')->assertOk();
        $this->get('/events/security-bootcamp/register')->assertRedirect('/events/security-bootcamp');
        $this->assertContains($this->post('/events/register', ['event_id' => $event->id, 'first_name' => 'Ada', 'last_name' => 'Okafor',
            'email' => 'ada@example.com'])->getStatusCode(), [404, 405]);
        $this->assertDatabaseCount('event_registrations', 0);
        $this->assertDatabaseCount('users', 1);
        $this->assertGuest();
    }

    public function test_draft_events_are_hidden_from_visitors(): void
    {
        Event::create(['title' => 'Draft event', 'slug' => 'draft-event', 'type' => 'training', 'status' => 'draft',
            'location' => 'Online', 'start_date' => now()->addDay(), 'end_date' => now()->addDays(2)]);
        $this->get('/events/draft-event')->assertNotFound();
        $this->get('/events')->assertOk()->assertDontSee('Draft event');
    }

    public function test_contact_details_replace_forms_and_admin_mail_is_removed(): void
    {
        foreach (['/', '/contact'] as $path) {
            $this->get($path)->assertOk()->assertSee('mailto:info@forwardedgeconsulting.com', false)
                ->assertSee('tel:+2347039955591', false)->assertSee('58 Iwaya Road')
                ->assertDontSee('cfName2')->assertDontSee('Drop Us a');
        }
        $this->postJson('/contact', [])->assertStatus(405);
        $this->actingAs(User::factory()->create(['role' => 'admin']))
            ->get('/ctrl-panel-v2/dashboard')->assertOk()->assertDontSee('Recent messages')
            ->assertDontSee('Email Engine')->assertDontSee('Unread messages');
    }

}
