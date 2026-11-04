<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ConsultancyTest extends TestCase
{
    use RefreshDatabase;

    public function test_consultancy_page_embeds_the_builder_form_video_and_self_check(): void
    {
        $this->get('/consultancy')->assertOk()->assertSee('Moyosore Kukoyi')
            ->assertSee('CyberMo')->assertSee('Book your free discovery call')
            ->assertSee('data-video="GO-uoVM3MV8"', false)->assertSee('data-open-booking', false)
            ->assertSee('action="' . url('/f/security-discovery-call') . '"', false)
            ->assertDontSee('?subject=Security', false)->assertDontSee('PLACEHOLDER');
        $this->get('/sitemap.xml')->assertOk()->assertSee('/consultancy', false);
    }

    public function test_page_falls_back_to_email_when_the_form_is_closed(): void
    {
        \App\Models\Form::where('slug', 'security-discovery-call')->update(['status' => 'closed']);
        $this->get('/consultancy')->assertOk()
            ->assertSee('mailto:info@forwardedgeconsulting.com?subject=', false)->assertDontSee('id="booking"', false);
    }

    public function test_hero_contains_three_static_images_and_accessible_controls(): void
    {
        $this->get('/')->assertOk()->assertSee('data-hero-slideshow', false)
            ->assertSee('bootcamp-community.jpg')->assertSee('security-monitoring.jpg')
            ->assertSee('Pause slideshow')->assertSee('Next image');
    }

    public function test_newsletter_requests_are_rate_limited_before_external_services(): void
    {
        for ($i = 0; $i < 5; $i++) {
            $this->postJson('/newsletter/subscribe', [])->assertUnprocessable();
        }
        $this->postJson('/newsletter/subscribe', [])->assertStatus(429);
    }
}
