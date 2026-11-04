<?php

namespace Tests\Feature;

use App\Models\Form;
use App\Models\FormSubmission;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class FormsTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create(['role' => 'admin']);
    }

    private function makeForm(array $overrides = []): Form
    {
        return Form::create(array_merge([
            'name' => 'Speaker application',
            'fields' => [
                ['key' => 'name', 'label' => 'Full name', 'type' => 'text', 'required' => true, 'maps_to' => 'FULLNAME'],
                ['key' => 'email', 'label' => 'Email', 'type' => 'email', 'required' => true],
                ['key' => 'topic', 'label' => 'Topic', 'type' => 'select', 'required' => true, 'options' => ['Phishing', 'Incident response']],
                ['key' => 'tools', 'label' => 'Tools', 'type' => 'checkbox', 'required' => false, 'options' => ['Wireshark', 'Burp']],
            ],
            'settings' => array_merge(Form::defaultSettings(), ['consent_required' => true, 'success_message' => 'Received, thank you.']),
            'status' => 'open',
        ], $overrides));
    }

    private function answers(array $extra = []): array
    {
        return array_merge([
            'answers' => ['name' => 'Adaeze Okonkwo', 'email' => 'adaeze@gmail.com', 'topic' => 'Phishing', 'tools' => ['Burp']],
            'consent' => '1',
        ], $extra);
    }

    // ---- public submission -------------------------------------------------

    public function test_a_response_is_stored_with_its_labels_consent_and_source(): void
    {
        Mail::fake();
        $form = $this->makeForm();

        $this->withHeader('Referer', url('/consultancy'))
            ->postJson(route('forms.submit', $form->slug), $this->answers())
            ->assertOk()->assertJsonPath('message', 'Received, thank you.');

        $row = FormSubmission::firstOrFail();
        $this->assertSame('adaeze@gmail.com', $row->email);
        $this->assertSame('Adaeze Okonkwo', $row->name);
        $this->assertTrue($row->consent_given);
        $this->assertNotNull($row->consented_at);
        $this->assertSame('/consultancy', $row->source_url);
        $this->assertSame('Full name', $row->data[0]['label']);
        $this->assertSame(['Burp'], collect($row->data)->firstWhere('key', 'tools')['value']);
        $this->assertNotNull($row->ip_hash);
        $this->assertStringNotContainsString('127.0.0.1', $row->ip_hash);
    }

    public function test_invalid_answers_are_rejected_and_nothing_is_stored(): void
    {
        $form = $this->makeForm();

        $this->postJson(route('forms.submit', $form->slug), ['answers' => ['name' => '', 'email' => 'nope', 'topic' => 'Other'], 'consent' => '1'])
            ->assertStatus(422)->assertJsonValidationErrors(['answers.name', 'answers.email', 'answers.topic']);
        $this->postJson(route('forms.submit', $form->slug), $this->answers(['consent' => null]))
            ->assertStatus(422)->assertJsonValidationErrors(['consent']);

        $this->assertSame(0, FormSubmission::count());
    }

    public function test_bots_that_fill_the_hidden_field_are_dropped_silently(): void
    {
        $form = $this->makeForm();

        $this->postJson(route('forms.submit', $form->slug), $this->answers(['hp_field' => 'buy pills']))->assertOk();

        $this->assertSame(0, FormSubmission::count());
    }

    public function test_a_closed_form_does_not_accept_responses(): void
    {
        $form = $this->makeForm(['status' => 'closed']);

        $this->postJson(route('forms.submit', $form->slug), $this->answers())->assertStatus(410);
        $this->assertSame(0, FormSubmission::count());

        $expired = $this->makeForm(['name' => 'Old', 'closes_at' => now()->subDay()]);
        $this->postJson(route('forms.submit', $expired->slug), $this->answers())->assertStatus(410);
    }

    public function test_a_mail_failure_never_loses_the_lead(): void
    {
        $form = $this->makeForm(['settings' => array_merge(Form::defaultSettings(), ['notify_email' => 'team@forwardedgeconsulting.com', 'auto_reply' => true])]);
        Mail::shouldReceive('to')->andThrow(new \RuntimeException('SMTP down'));

        $this->postJson(route('forms.submit', $form->slug), $this->answers(['consent' => null]))->assertOk();

        $this->assertSame(1, FormSubmission::count());
    }

    public function test_shared_form_page_renders_but_built_in_forms_are_not_public(): void
    {
        $form = $this->makeForm();

        $this->get(route('forms.show', $form->slug))->assertOk()->assertSee('Speaker application')->assertSee('Full name');
        $this->get('/f/newsletter')->assertNotFound();
    }

    // ---- newsletter entry points -------------------------------------------

    public function test_newsletter_signup_is_saved_locally_and_marked_with_consent(): void
    {
        Mail::fake();

        $this->withHeader('Referer', url('/'))->postJson(route('newsletter.subscribe'), [
            'source' => 'builder_form',
            'block_id' => 'footer-newsletter',
            'fields' => [['label' => 'Email', 'name' => 'email', 'type' => 'email', 'value' => 'Tunde@Gmail.com']],
            'consent' => true,
            'consent_text' => 'I agree to receive emails from Forward Edge and accept the Privacy Policy.',
        ])->assertOk();

        $row = FormSubmission::firstOrFail();
        $this->assertSame('newsletter', $row->form->slug);
        $this->assertSame('tunde@gmail.com', $row->email);
        $this->assertTrue($row->consent_given);
        $this->assertSame('Home page', $row->source_label);
    }

    public function test_newsletter_signup_without_consent_is_kept_but_flagged(): void
    {
        Mail::fake();

        $this->postJson(route('newsletter.subscribe'), [
            'source' => 'builder_form',
            'block_id' => 'blog-newsletter',
            'fields' => [['label' => 'Email', 'name' => 'email', 'type' => 'email', 'value' => 'ngozi@gmail.com']],
        ])->assertOk();

        $this->assertFalse(FormSubmission::firstOrFail()->consent_given);
    }

    // ---- admin -------------------------------------------------------------

    public function test_only_admins_can_see_forms_and_responses(): void
    {
        $this->get(route('admin.responses.index'))->assertRedirect();
        $this->actingAs(User::factory()->create(['role' => 'user']))->get(route('admin.responses.index'))->assertForbidden();
        $this->actingAs($this->admin())->get(route('admin.responses.index'))->assertOk();
        $this->get(route('admin.forms.index'))->assertOk()->assertSee('Newsletter');
    }

    public function test_an_admin_can_build_a_form_and_the_builder_cleans_the_fields(): void
    {
        $this->actingAs($this->admin());

        $this->post(route('admin.forms.store'), [
            'name' => 'Partner enquiry',
            'status' => 'open',
            'fields_json' => json_encode([
                ['key' => 'Bad Key!', 'label' => '', 'type' => 'banana', 'required' => 'true'],
                ['label' => 'Pick one', 'type' => 'radio', 'options' => ['A', 'A', ' B ', '']],
            ]),
            'consent_required' => '1',
        ])->assertRedirect();

        $form = Form::where('slug', 'partner-enquiry')->firstOrFail();
        $this->assertSame('text', $form->fields[0]['type']);
        $this->assertSame('Short answer', $form->fields[0]['label']);
        $this->assertTrue($form->fields[0]['required']);
        $this->assertSame(['A', 'B'], $form->fields[1]['options']);
        $this->assertTrue($form->setting('consent_required'));
    }

    public function test_built_in_forms_cannot_be_deleted(): void
    {
        $this->actingAs($this->admin());

        $this->delete(route('admin.forms.destroy', Form::system('newsletter')))->assertForbidden();
        $this->assertNotNull(Form::where('slug', 'newsletter')->first());
    }

    private function seedLeads(): Form
    {
        $form = $this->makeForm();
        $base = ['form_id' => $form->id, 'data' => [], 'consent_text' => 'ok'];

        FormSubmission::create(array_merge($base, ['email' => 'a@gmail.com', 'name' => 'Ada Obi', 'consent_given' => true, 'consented_at' => now(), 'data' => [
            ['key' => 'name', 'label' => 'Full name', 'type' => 'text', 'maps_to' => 'FULLNAME', 'value' => 'Ada Obi'],
            ['key' => 'topic', 'label' => 'Topic', 'type' => 'select', 'maps_to' => '', 'value' => '=HYPERLINK("http://evil")'],
        ]]));
        // same person again: one row in the export
        FormSubmission::create(array_merge($base, ['email' => 'a@gmail.com', 'consent_given' => true, 'consented_at' => now()]));
        FormSubmission::create(array_merge($base, ['email' => 'b@gmail.com', 'consent_given' => false]));   // no consent
        FormSubmission::create(array_merge($base, ['email' => null, 'consent_given' => true]));             // no email

        return $form;
    }

    public function test_export_only_includes_consented_contacts_once_and_marks_them(): void
    {
        $this->seedLeads();
        $this->actingAs($this->admin());

        $response = $this->post(route('admin.responses.export'), ['scope' => 'new']);
        $response->assertOk()->assertHeader('content-type', 'text/csv; charset=UTF-8');
        $csv = $response->streamedContent();
        $lines = array_values(array_filter(explode("\n", trim($csv))));

        $this->assertStringStartsWith('EMAIL,FIRSTNAME,LASTNAME,SMS', $lines[0]);
        $this->assertCount(2, $lines);                       // header + a@gmail.com once
        $this->assertStringContainsString('a@gmail.com,Ada,Obi', $lines[1]);
        $this->assertStringNotContainsString('b@gmail.com', $csv);
        $this->assertStringContainsString("'=HYPERLINK", $csv);   // formula injection neutralised

        $this->assertSame(2, FormSubmission::whereNotNull('exported_at')->count());
        $this->assertSame(0, FormSubmission::readyToExport()->count());

        $this->post(route('admin.responses.export'), ['scope' => 'new'])->assertRedirect()->assertSessionHas('error');
    }

    public function test_an_export_can_be_undone(): void
    {
        $this->seedLeads();
        $this->actingAs($this->admin());
        $this->post(route('admin.responses.export'), ['scope' => 'new'])->streamedContent();

        $batch = FormSubmission::whereNotNull('export_batch')->value('export_batch');
        $this->post(route('admin.responses.undo', $batch))->assertRedirect();

        $this->assertSame(2, FormSubmission::readyToExport()->count());
    }

    public function test_forgetting_a_person_removes_every_response_for_that_email(): void
    {
        $this->seedLeads();
        $this->actingAs($this->admin());

        $this->post(route('admin.responses.forget'), ['email' => 'A@gmail.com'])->assertRedirect();

        $this->assertSame(0, FormSubmission::where('email', 'a@gmail.com')->count());
        $this->assertSame(2, FormSubmission::count());
    }

    public function test_responses_screen_filters_and_shows_a_response(): void
    {
        $this->seedLeads();
        $this->actingAs($this->admin());

        $this->get(route('admin.responses.index', ['status' => 'no_consent']))->assertOk()->assertSee('b@gmail.com')->assertDontSee('Ada Obi');
        $this->get(route('admin.responses.show', FormSubmission::where('email', 'b@gmail.com')->first()))->assertOk()->assertSee('None recorded');
    }
}
