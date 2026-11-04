<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\EventCategory;
use App\Models\Form;
use App\Models\Page;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EventsShowcaseTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create(['role' => 'admin']);
    }

    private function category(string $name = 'Workshop'): EventCategory
    {
        return EventCategory::create(['name' => $name]);
    }

    private function event(array $o = []): Event
    {
        return Event::create(array_merge([
            'title' => 'Cohort 4 Bootcamp', 'location' => 'Yaba, Lagos', 'venue' => 'Co-Creation Hub', 'type' => 'training',
            'start_date' => now()->addDays(10), 'end_date' => now()->addDays(12), 'status' => 'published',
            'short_description' => 'Hands-on training.',
        ], $o));
    }

    private function past(array $o = []): Event
    {
        return $this->event(array_merge(['title' => 'Summit 2025', 'start_date' => now()->subDays(40), 'end_date' => now()->subDays(39)], $o));
    }

    // ---- admin: saving ----------------------------------------------------

    public function test_an_admin_saves_an_event_with_a_category_cleaned_highlights_and_a_default_interest_form(): void
    {
        $this->actingAs($this->admin());
        $cat = EventCategory::where('slug', 'academic-programme')->firstOrFail();   // seeded by the migration

        $this->post(route('admin.events.store'), [
            'title' => 'Cyber Law Short Course', 'category_id' => $cat->id, 'status' => 'published', 'location' => 'Abuja',
            'start_date' => '2026-12-01T10:00', 'end_date' => '2026-12-03T16:00', 'cta_type' => 'interest',
            'highlights_json' => json_encode([['value' => '120', 'label' => 'Attendees'], ['value' => '', 'label' => 'Empty'], ['value' => '<b>8</b>', 'label' => 'Speakers']]),
        ])->assertSessionHasNoErrors()->assertRedirect();

        $e = Event::where('slug', 'cyber-law-short-course')->firstOrFail();
        $this->assertSame($cat->id, $e->category_id);
        $this->assertSame('academic-programme', $e->type);
        $this->assertSame([['value' => '120', 'label' => 'Attendees'], ['value' => '8', 'label' => 'Speakers']], $e->highlights);
        $this->assertSame(Form::where('slug', 'interest')->value('id'), $e->cta_form_id);
        $this->assertSame('Academic programme', $e->category_label);
    }

    public function test_a_link_button_needs_a_web_address_and_duplicate_names_get_their_own_link(): void
    {
        $this->actingAs($this->admin());
        $base = ['title' => 'Dup', 'status' => 'draft', 'location' => 'Online', 'start_date' => '2026-12-01T10:00'];

        $this->post(route('admin.events.store'), $base + ['cta_type' => 'link'])->assertSessionHasErrors('cta_url');
        $this->post(route('admin.events.store'), $base + ['cta_type' => 'link', 'cta_url' => 'not a url'])->assertSessionHasErrors('cta_url');
        $this->post(route('admin.events.store'), $base + ['cta_type' => 'link', 'cta_url' => 'https://tickets.example.com/x'])->assertSessionHasNoErrors();
        $this->post(route('admin.events.store'), $base + ['cta_type' => 'none'])->assertSessionHasNoErrors();

        $this->assertSame(['dup', 'dup-2'], Event::orderBy('id')->pluck('slug')->all());
        $this->assertTrue(Event::first()->end_date->isSameDay('2026-12-01'));   // a blank end becomes the end of the start day
    }

    public function test_categories_can_be_managed_and_deleting_one_keeps_its_events(): void
    {
        $this->actingAs($this->admin());

        $this->post(route('admin.event-categories.store'), ['name' => 'Outreach'])->assertSessionHasNoErrors();
        $this->post(route('admin.event-categories.store'), ['name' => 'Outreach'])->assertSessionHasErrors('name');
        $cat = EventCategory::where('name', 'Outreach')->firstOrFail();
        $this->put(route('admin.event-categories.update', $cat), ['name' => 'Community outreach'])->assertSessionHasNoErrors();
        $this->assertSame('Community outreach', $cat->fresh()->name);

        $event = $this->event(['category_id' => $cat->id]);
        $this->delete(route('admin.event-categories.destroy', $cat))->assertRedirect();
        $this->assertNotNull(Event::find($event->id));
        $this->assertNull($event->fresh()->category_id);
    }

    // ---- admin: the page --------------------------------------------------

    public function test_building_a_page_from_a_template_uses_the_events_own_title_and_summary(): void
    {
        $this->actingAs($this->admin());
        $event = $this->event(['highlights' => [['value' => '120', 'label' => 'Attendees']]]);

        $this->post(route('admin.events.page.create', $event), ['template' => 'event_upcoming'])->assertRedirect();

        $page = $event->pages()->firstOrFail();
        $this->assertSame('draft', $page->status);
        $this->assertSame(['page_header', 'event_facts', 'rich_text', 'steps', 'form', 'events_grid'], $page->blocks()->orderBy('order')->pluck('type')->all());
        $header = $page->blocks()->where('type', 'page_header')->first()->data;
        $this->assertSame('Cohort 4 Bootcamp', $header['title']);
        $this->assertSame('Hands-on training.', $header['subtitle']);

        $this->post(route('admin.events.page.create', $event), ['template' => 'event_recap'])->assertSessionHas('error');   // only one page
    }

    public function test_recap_sections_are_added_to_the_end_of_an_existing_page(): void
    {
        $this->actingAs($this->admin());
        $event = $this->past(['highlights' => []]);
        $this->post(route('admin.events.page.create', $event), ['template' => 'event_upcoming']);
        $before = $event->pages()->first()->blocks()->count();

        $this->post(route('admin.events.page.recap', $event))->assertRedirect();

        $blocks = $event->pages()->first()->blocks()->orderBy('order')->get();
        $this->assertSame($before + 2, $blocks->count());
        $this->assertSame(['stats', 'rich_text'], $blocks->slice(-2)->pluck('type')->values()->all());
    }

    // ---- public -----------------------------------------------------------

    public function test_the_events_page_separates_upcoming_from_past_and_filters_by_category(): void
    {
        $ws = EventCategory::where('slug', 'workshop')->firstOrFail();
        $this->event(['title' => 'Upcoming Workshop', 'category_id' => $ws->id]);
        $this->event(['title' => 'Upcoming Bootcamp', 'category_id' => null, 'start_date' => now()->addDays(30), 'end_date' => now()->addDays(32)]);
        $this->past(['title' => 'Old Summit', 'highlights' => [['value' => '300', 'label' => 'Attendees']]]);
        $this->event(['title' => 'Hidden Draft', 'status' => 'draft']);

        $html = $this->get('/events')->assertOk()->getContent();
        $this->assertStringContainsString('Upcoming Workshop', $html);
        $this->assertStringContainsString('Upcoming Bootcamp', $html);
        $this->assertStringNotContainsString('Old Summit', $html);
        $this->assertStringNotContainsString('Hidden Draft', $html);

        $past = $this->get('/events?when=past')->assertOk()->getContent();
        $this->assertStringContainsString('Old Summit', $past);
        $this->assertStringContainsString('300 attendees', $past);       // the bragging line
        $this->assertStringNotContainsString('Upcoming Workshop', $past);

        $only = $this->get('/events?when=upcoming&category=' . $ws->slug)->getContent();
        $this->assertStringContainsString('Upcoming Workshop', $only);
        $this->assertStringNotContainsString('Upcoming Bootcamp', $only);
    }

    public function test_with_nothing_upcoming_the_wall_of_past_events_is_shown_first(): void
    {
        $this->past();

        $this->get('/events')->assertOk()->assertSee('Summit 2025');
    }

    public function test_an_upcoming_event_without_its_own_page_gets_facts_highlights_and_one_button(): void
    {
        $event = $this->event(['cta_type' => 'interest', 'highlights' => [['value' => '40', 'label' => 'Places']]]);

        $html = $this->get('/events/' . $event->slug)->assertOk()->getContent();
        $this->assertStringContainsString('Co-Creation Hub', $html);
        $this->assertStringContainsString('Upcoming', $html);
        $this->assertStringContainsString('40', $html);
        $this->assertStringContainsString('href="#interest"', $html);
        $this->assertStringContainsString('id="interest"', $html);
        $this->assertStringContainsString('fe-form', $html);
    }

    public function test_a_link_button_goes_to_the_external_site_and_vanishes_after_the_event(): void
    {
        $up = $this->event(['title' => 'Ticketed', 'cta_type' => 'link', 'cta_label' => 'Get tickets', 'cta_url' => 'https://tickets.example.com/x']);
        $this->get('/events/' . $up->slug)->assertSee('Get tickets')->assertSee('https://tickets.example.com/x', false);

        $past = $this->past(['title' => 'Over', 'cta_type' => 'link', 'cta_label' => 'Get tickets', 'cta_url' => 'https://tickets.example.com/y']);
        $html = $this->get('/events/' . $past->slug)->assertOk()->getContent();
        $this->assertStringNotContainsString('Get tickets', $html);
        $this->assertStringContainsString('Completed', $html);
    }

    public function test_an_event_with_a_published_page_sends_visitors_to_that_page(): void
    {
        $event = $this->event();
        $page = Page::create(['title' => 'Cohort 4', 'slug' => 'cohort-4', 'status' => 'published', 'pageable_type' => Event::class, 'pageable_id' => $event->id]);

        $this->get('/events/' . $event->slug)->assertRedirect(route('page.show', 'cohort-4'));
    }

    public function test_online_registration_links_are_retired_but_do_not_break(): void
    {
        $event = $this->event();

        $this->get('/events/' . $event->slug . '/register')->assertRedirect('/events/' . $event->slug);
        $this->get('/events/upcoming')->assertRedirect();
        $this->actingAs($this->admin())->get('/ctrl-panel-v2/events/registrations')->assertRedirect();
    }

    // ---- model rules ------------------------------------------------------

    public function test_past_means_the_end_date_has_passed(): void
    {
        $ongoing = $this->event(['title' => 'Ongoing', 'start_date' => now()->subDay(), 'end_date' => now()->addDay()]);
        $today = $this->event(['title' => 'Today only', 'start_date' => now()->startOfDay()->setTime(9, 0), 'end_date' => now()->endOfDay()]);
        $yesterday = $this->event(['title' => 'Yesterday', 'start_date' => now()->subDay()->startOfDay(), 'end_date' => now()->subDay()->endOfDay()]);

        $this->assertFalse($ongoing->is_past);
        $this->assertFalse($today->is_past);
        $this->assertTrue($yesterday->is_past);
        $this->assertEqualsCanonicalizing(['Yesterday'], Event::past()->pluck('title')->all());
        $this->assertEqualsCanonicalizing(['Ongoing', 'Today only'], Event::notPast()->pluck('title')->all());
    }

    public function test_admin_event_screens_render(): void
    {
        $this->actingAs($this->admin());
        $event = $this->event();

        $this->get(route('admin.events.list'))->assertOk()->assertSee('Cohort 4 Bootcamp');
        $this->get(route('admin.events.create'))->assertOk()->assertSee('Call to action');
        $this->get(route('admin.events.dashboard', $event))->assertOk()->assertSee('Build the event page');
        $this->get(route('admin.event-categories.index'))->assertOk();
        $this->get(route('dashboard'))->assertOk()->assertSee('Next event')->assertSee('Cohort 4 Bootcamp');
    }
}
