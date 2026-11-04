<?php

namespace Tests\Feature;

use App\Mail\FormResourceMail;
use App\Models\Form;
use App\Models\FormSubmission;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class FormResourceTest extends TestCase
{
    use RefreshDatabase;

    private function resourceForm(array $settings = []): Form
    {
        Storage::fake('private');
        Storage::disk('private')->put('form-resources/guide.pdf', '%PDF-1.4 test');

        return Form::create([
            'name' => 'Checklist download',
            'fields' => [
                ['key' => 'name', 'label' => 'Name', 'type' => 'text', 'required' => true, 'maps_to' => 'FULLNAME'],
                ['key' => 'email', 'label' => 'Email', 'type' => 'email', 'required' => true],
            ],
            'settings' => array_merge(Form::defaultSettings(), [
                'resource_enabled' => true, 'resource_title' => 'Security checklist', 'resource_kind' => 'file',
                'resource_path' => 'form-resources/guide.pdf', 'resource_filename' => 'checklist.pdf',
            ], $settings),
            'status' => 'open',
        ]);
    }

    private function submit(Form $form)
    {
        return $this->postJson('/f/' . $form->slug, ['answers' => ['name' => 'Ada Obi', 'email' => 'ada@gmail.com']]);
    }

    public function test_submit_emails_the_resource_and_returns_a_popup(): void
    {
        Mail::fake();
        $form = $this->resourceForm();

        $res = $this->submit($form)->assertOk();

        $res->assertJsonPath('popup.resource_title', 'Security checklist');
        $this->assertStringContainsString('ada@gmail.com', $res->json('popup.message'));
        Mail::assertSent(FormResourceMail::class, fn ($m) => $m->hasTo('ada@gmail.com'));
        $sub = FormSubmission::firstOrFail();
        $this->assertNotNull($sub->resource_sent_at);
        $this->assertNotNull($sub->download_token);
    }

    public function test_a_mail_failure_keeps_the_lead_and_offers_the_download(): void
    {
        $form = $this->resourceForm();
        Mail::shouldReceive('to')->andReturnSelf();
        Mail::shouldReceive('send')->andThrow(new \RuntimeException('smtp down'));

        $res = $this->submit($form)->assertOk();

                $sub = FormSubmission::firstOrFail();
        $this->assertNull($sub->resource_sent_at);
        $this->assertStringContainsString('smtp down', $sub->resource_error);
        $this->assertNotEmpty($res->json('popup.download_url'));
    }

    public function test_download_serves_the_file_and_counts_it(): void
    {
        Mail::fake();
        $this->submit($this->resourceForm());
        $sub = FormSubmission::firstOrFail();

        $this->get(route('forms.resource', $sub->download_token))->assertOk()->assertDownload('checklist.pdf');
        $this->get(route('forms.resource', $sub->download_token))->assertOk();

        $sub->refresh();
        $this->assertSame(2, $sub->download_count);
        $this->assertNotNull($sub->resource_downloaded_at);
    }

    public function test_unknown_and_expired_links_are_refused(): void
    {
        Mail::fake();
        $this->submit($this->resourceForm());
        $sub = FormSubmission::firstOrFail();

        $this->get('/r/' . str_repeat('x', 40))->assertNotFound();

        $sub->forceFill(['created_at' => now()->subDays(31)])->save();
        $this->get(route('forms.resource', $sub->download_token))->assertStatus(410);
    }

    public function test_forms_without_a_resource_behave_as_before(): void
    {
        Mail::fake();
        $form = $this->resourceForm(['resource_enabled' => false]);

        $this->submit($form)->assertOk()->assertJsonPath('popup', null);
        Mail::assertNothingSent();
    }

    public function test_admin_can_attach_a_pdf_and_resend_it(): void
    {
        Storage::fake('private');
        Mail::fake();
        $admin = User::factory()->create(['role' => 'admin']);
        $form = Form::create(['name' => 'Guide', 'fields' => [
            ['key' => 'email', 'label' => 'Email', 'type' => 'email', 'required' => true],
        ], 'settings' => Form::defaultSettings(), 'status' => 'open']);

        $this->actingAs($admin)->put(route('admin.forms.update', $form), [
            'name' => 'Guide', 'status' => 'open', 'fields_json' => json_encode($form->fields),
            'resource_enabled' => '1', 'resource_title' => 'Guide PDF', 'resource_kind' => 'file',
            'resource_file' => UploadedFile::fake()->create('guide.pdf', 200, 'application/pdf'),
        ])->assertSessionHasNoErrors();

        $form->refresh();
        $this->assertTrue($form->hasResource());
        Storage::disk('private')->assertExists($form->setting('resource_path'));

        $sub = FormSubmission::create(['form_id' => $form->id, 'email' => 'ada@gmail.com', 'name' => 'Ada', 'data' => []]);
        $this->actingAs($admin)->post(route('admin.responses.resend', $sub))->assertSessionHas('success');
        Mail::assertSent(FormResourceMail::class);
    }
}
