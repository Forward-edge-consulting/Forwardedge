<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\Form;
use App\Models\Page;
use App\Models\User;
use App\Support\PageBlueprint;
use App\Support\PageTemplates;
use App\Support\RichText;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PageBuilderV2Test extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create(['role' => 'admin']);
    }

    private function page(array $overrides = []): Page
    {
        return Page::create(array_merge(['title' => 'Test page', 'slug' => 'test-page', 'status' => 'published'], $overrides));
    }

    private function addBlock(Page $page, string $type, array $data, int $order = 10)
    {
        return $page->blocks()->create(['type' => $type, 'data' => $data, 'order' => $order, 'is_published' => true]);
    }

    private function event(array $overrides = []): Event
    {
        return Event::create(array_merge([
            'title' => 'Cohort 4 Bootcamp', 'location' => 'Yaba, Lagos', 'venue' => 'Co-Creation Hub',
            'start_date' => now()->addDays(10), 'end_date' => now()->addDays(12), 'status' => 'published',
            'type' => 'training', 'short_description' => 'Hands-on.',
        ], $overrides));
    }

    // ---- text safety -------------------------------------------------------

    public function test_rich_text_documents_keep_structure_and_lose_anything_unsafe(): void
    {
        $clean = RichText::cleanDocument(
            'Loose intro <b>text</b>'
            . '<h1>Big</h1><h4>Small</h4>'
            . '<ul><li>One <script>alert(1)</script></li><li></li><li>Two <a href="javascript:x()">bad</a></li></ul>'
            . '<div onclick="x()">Boxed</div><blockquote>Quote</blockquote><iframe src="https://evil"></iframe>'
        );

        $this->assertStringContainsString('<p>Loose intro <strong>text</strong></p>', $clean);
        $this->assertStringContainsString('<h2>Big</h2>', $clean);
        $this->assertStringContainsString('<h3>Small</h3>', $clean);
        $this->assertStringContainsString('<ul><li>One</li><li>Two bad</li></ul>', $clean);
        $this->assertStringContainsString('<p>Boxed</p>', $clean);
        $this->assertStringContainsString('<blockquote>Quote</blockquote>', $clean);
        foreach (['script', 'iframe', 'javascript:', 'onclick', 'alert'] as $bad) {
            $this->assertStringNotContainsString($bad, $clean);
        }
    }

    // ---- templates ---------------------------------------------------------

    public function test_creating_a_page_from_a_template_adds_its_sections_in_order(): void
    {
        $this->actingAs($this->admin());

        $this->post(route('pb.pages.store'), ['title' => 'SME security', 'status' => 'draft', 'template' => 'marketing'])->assertRedirect();

        $page = Page::where('slug', 'sme-security')->firstOrFail();
        $this->assertSame(['page_header', 'stats', 'rich_text', 'feature_grid', 'faq_list', 'form', 'cta_band'], $page->blocks()->orderBy('order')->pluck('type')->all());
        $this->assertSame('draft', $page->status);
        $this->assertSame(Form::system('newsletter')->id, $page->blocks()->where('type', 'form')->first()->data['form_id']);
    }

    public function test_blank_and_unknown_templates(): void
    {
        $this->actingAs($this->admin());

        $this->post(route('pb.pages.store'), ['title' => 'Empty one', 'status' => 'draft', 'template' => 'blank'])->assertRedirect();
        $this->assertSame(0, Page::where('slug', 'empty-one')->first()->blocks()->count());

        $this->post(route('pb.pages.store'), ['title' => 'Bad one', 'status' => 'draft', 'template' => 'nonsense'])->assertSessionHasErrors('template');
        $this->assertNull(Page::where('slug', 'bad-one')->first());
    }

    public function test_every_template_produces_blocks_that_pass_their_own_validation(): void
    {
        $this->actingAs($this->admin());

        foreach (PageTemplates::keys() as $key) {
            $this->post(route('pb.pages.store'), ['title' => "Page $key", 'status' => 'draft', 'template' => $key])->assertRedirect();
            $page = Page::where('title', "Page $key")->firstOrFail();

            foreach ($page->blocks as $block) {
                $validator = validator(['data' => $block->data], PageBlueprint::prefixedRulesFor($block->type));
                $this->assertFalse($validator->fails(), "$key / {$block->type}: " . json_encode($validator->errors()->all()));
            }
        }
        $this->assertSame(1, Form::where('slug', 'interest')->count());   // created once, reused
    }

    public function test_the_section_picker_offers_current_sections_not_the_old_form_blocks(): void
    {
        $picker = PageBlueprint::pickerTypes();

        foreach (['page_header', 'rich_text', 'stats', 'form', 'events_grid', 'event_facts', 'cta_band', 'faq_list', 'faq'] as $type) {
            $this->assertContains($type, $picker);
        }
        $this->assertNotContains('form_dark', $picker);
        $this->assertContains('form_dark', PageBlueprint::allowedTypes());    // existing ones still render
    }

    // ---- saving and rendering sections ------------------------------------

    public function test_a_text_section_is_cleaned_on_save_and_requires_content(): void
    {
        $this->actingAs($this->admin());
        $page = $this->page();

        $this->postJson(route('pb.blocks.store', $page), ['type' => 'rich_text', 'data' => ['title' => 'Hi', 'body' => '<p>Safe</p><script>alert(1)</script>']])->assertOk();
        $this->assertSame('<p>Safe</p>', $page->blocks()->first()->data['body']);

        $this->postJson(route('pb.blocks.store', $page), ['type' => 'rich_text', 'data' => ['title' => 'No body']])->assertStatus(422);
    }

    public function test_every_new_section_renders_even_when_optional_settings_are_missing(): void
    {
        $page = $this->page();
        $form = Form::system('newsletter');
        $this->addBlock($page, 'page_header', ['title' => 'Header title', 'kicker' => 'Label', 'primary_text' => 'Go', 'primary_link' => '/consultancy'], 10);
        $this->addBlock($page, 'rich_text', ['body' => '<h2>Why</h2><p>Because.</p>'], 20);
        $this->addBlock($page, 'stats', ['items' => [['value' => '120', 'label' => 'Attendees']]], 30);          // no tone
        $this->addBlock($page, 'form', ['form_id' => $form->id], 40);                                           // no tone
        $this->addBlock($page, 'events_grid', ['show' => 'upcoming'], 50);
        $this->addBlock($page, 'event_facts', [], 60);
        $this->addBlock($page, 'cta_band', ['title' => 'Band title', 'primary_text' => 'Call', 'primary_link' => '/contact'], 70);
        $this->addBlock($page, 'faq_list', ['items' => [['q' => 'Is it free?', 'a' => 'Yes, the first call is.']]], 80);   // no title, no tone

        $html = $this->get('/test-page')->assertOk()->getContent();

        foreach (['Header title', 'Because.', 'Attendees', 'fe-form', 'No events are scheduled', 'Band title', 'Is it free?'] as $needle) {
            $this->assertStringContainsString($needle, $html, "missing: $needle");
        }
    }

    public function test_the_events_list_filters_by_when_and_type_and_hides_unpublished(): void
    {
        $page = $this->page();
        $this->event(['title' => 'Upcoming Workshop', 'type' => 'workshop']);
        $this->event(['title' => 'Upcoming Webinar', 'type' => 'webinar', 'start_date' => now()->addDays(20), 'end_date' => now()->addDays(20)]);
        $this->event(['title' => 'Hidden Draft', 'status' => 'draft']);
        $this->event(['title' => 'Old Summit', 'start_date' => now()->subDays(30), 'end_date' => now()->subDays(29)]);

        $block = $this->addBlock($page, 'events_grid', ['show' => 'upcoming', 'limit' => 5]);
        $html = $this->get('/test-page')->getContent();
        $this->assertStringContainsString('Upcoming Workshop', $html);
        $this->assertStringContainsString('Upcoming Webinar', $html);
        $this->assertStringNotContainsString('Hidden Draft', $html);
        $this->assertStringNotContainsString('Old Summit', $html);

        $block->update(['data' => ['show' => 'upcoming', 'event_type' => 'webinar']]);
        $html = $this->get('/test-page')->getContent();
        $this->assertStringContainsString('Upcoming Webinar', $html);
        $this->assertStringNotContainsString('Upcoming Workshop', $html);

        $block->update(['data' => ['show' => 'past']]);
        $html = $this->get('/test-page')->getContent();
        $this->assertStringContainsString('Old Summit', $html);
        $this->assertStringNotContainsString('Upcoming Workshop', $html);
    }

    public function test_event_facts_come_from_the_linked_event_and_drop_the_button_afterwards(): void
    {
        $event = $this->event();
        $page = $this->page(['pageable_type' => Event::class, 'pageable_id' => $event->id]);
        $this->addBlock($page, 'event_facts', ['button_text' => 'Express interest', 'button_link' => '#interest']);

        $html = $this->get('/test-page')->assertOk()->getContent();
        $this->assertStringContainsString('Co-Creation Hub, Yaba, Lagos', $html);
        $this->assertStringContainsString('Upcoming', $html);
        $this->assertStringContainsString('Express interest', $html);

        $event->update(['start_date' => now()->subDays(5), 'end_date' => now()->subDays(4)]);
        $html = $this->get('/test-page')->getContent();
        $this->assertStringContainsString('Completed', $html);
        $this->assertStringNotContainsString('Express interest', $html);
    }

    public function test_event_facts_without_a_linked_event_show_nothing_to_visitors_but_a_hint_in_the_editor_preview(): void
    {
        $page = $this->page();
        $this->addBlock($page, 'event_facts', []);

        $this->get('/test-page')->assertOk()->assertDontSee('Event details appear here');

        $this->actingAs($this->admin())->get(route('pb.blocks.preview', $page) . '?pb_edit=1')->assertOk()->assertSee('Event details appear here');
    }

    public function test_a_form_section_whose_form_was_deleted_disappears_instead_of_breaking_the_page(): void
    {
        $page = $this->page();
        $form = Form::create(['name' => 'Temp', 'fields' => [['key' => 'email', 'label' => 'Email', 'type' => 'email', 'required' => true]], 'status' => 'open']);
        $this->addBlock($page, 'form', ['form_id' => $form->id, 'title' => 'Tell us']);
        $this->addBlock($page, 'rich_text', ['body' => '<p>Still here.</p>'], 20);

        $this->get('/test-page')->assertOk()->assertSee('Tell us');

        $form->delete();
        $this->get('/test-page')->assertOk()->assertDontSee('Tell us')->assertSee('Still here.');
    }

    // ---- preview + click to edit ------------------------------------------

    public function test_click_to_edit_markers_exist_only_in_the_admin_preview(): void
    {
        $page = $this->page();
        $block = $this->addBlock($page, 'rich_text', ['body' => '<p>Hello</p>']);

        $this->get('/test-page')->assertOk()->assertDontSee('data-pb-block', false);

        $this->get(route('pb.blocks.preview', $page) . '?pb_edit=1')->assertRedirect();   // not signed in

        $this->actingAs($this->admin());
        $this->get(route('pb.blocks.preview', $page) . '?pb_edit=1')->assertOk()->assertSee('data-pb-block="' . $block->id . '"', false);
        $this->get(route('pb.blocks.preview', $page))->assertOk()->assertDontSee('data-pb-block', false);
    }

    public function test_the_block_editor_screen_loads_with_its_live_preview(): void
    {
        $page = $this->page();
        $this->addBlock($page, 'page_header', ['title' => 'Hello']);

        $this->actingAs($this->admin())->get(route('pb.blocks', $page))->assertOk()
            ->assertSee('Live preview')->assertSee('pb_edit=1', false)->assertSee('Page header');
    }
}
