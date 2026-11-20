<?php

namespace Tests\Feature;

use App\Models\Form;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class FormRedirectTest extends TestCase
{
    use RefreshDatabase;

    private function form(array $settings = []): Form
    {
        return Form::create([
            'name' => 'Enquiry',
            'fields' => [['key' => 'email', 'label' => 'Email', 'type' => 'email', 'required' => true]],
            'settings' => array_merge(Form::defaultSettings(), ['consent_required' => false], $settings),
            'status' => 'open',
        ]);
    }

    private function send(Form $form, array $extra = [])
    {
        return $this->postJson('/f/' . $form->slug, ['answers' => ['email' => 'ada@gmail.com']] + $extra);
    }

    public function test_configured_redirect_is_returned(): void
    {
        $this->send($this->form(['redirect_url' => '/thank-you']))->assertOk()->assertJsonPath('redirect', '/thank-you');
    }

    public function test_redirect_is_kept_alongside_the_download_popup(): void
    {
        Mail::fake();
        Storage::fake('private');
        Storage::disk('private')->put('form-resources/g.pdf', '%PDF-1.4');
        $form = $this->form([
            'redirect_url' => '/thank-you', 'resource_enabled' => true, 'resource_kind' => 'file',
            'resource_path' => 'form-resources/g.pdf', 'resource_filename' => 'g.pdf',
        ]);

        // Redirect first: no popup on the form page, it is kept for the next page instead.
        $this->send($form)->assertOk()->assertJsonPath('redirect', '/thank-you')->assertJsonPath('popup', null)
            ->assertSessionHas('form_popup', fn ($p) => str_contains($p['download_url'], '/r/'));

        // The next page opens it on arrival.
        $this->get('/about')->assertOk()->assertSee('window.feShowPopup({', false);
        // And only once.
        $this->get('/about')->assertOk()->assertDontSee('window.feShowPopup({', false);
    }

    public function test_without_a_redirect_the_popup_shows_on_the_same_page(): void
    {
        Mail::fake();
        Storage::fake('private');
        Storage::disk('private')->put('form-resources/g.pdf', '%PDF-1.4');
        $form = $this->form([
            'resource_enabled' => true, 'resource_kind' => 'file',
            'resource_path' => 'form-resources/g.pdf', 'resource_filename' => 'g.pdf',
        ]);

        $this->send($form)->assertOk()->assertJsonPath('redirect', null)->assertJsonPath('popup.button', 'Download your resource')
            ->assertJsonPath('popup.title', 'Your resource is ready')
            ->assertSessionMissing('form_popup');
    }

    public function test_without_a_redirect_the_visitor_returns_to_the_page_they_came_from(): void
    {
        $this->send($this->form(), ['_return' => '/blog/my-post'])->assertJsonPath('redirect', '/blog/my-post');
    }

    public function test_the_form_redirect_wins_over_the_return_page(): void
    {
        $this->send($this->form(['redirect_url' => '/thank-you']), ['_return' => '/blog/x'])->assertJsonPath('redirect', '/thank-you');
    }

    public function test_offsite_return_paths_are_ignored(): void
    {
        foreach (['https://evil.test', '//evil.test', '/\evil.test', 'blog'] as $bad) {
            $this->send($this->form(), ['_return' => $bad])->assertJsonPath('redirect', null);
        }
    }

    public function test_standalone_page_remembers_the_referring_page(): void
    {
        $form = $this->form();
        $this->get('/f/' . $form->slug, ['Referer' => url('/services/audit?x=1')])
            ->assertOk()->assertSee('name="_return" value="/services/audit?x=1"', false);
        $this->get('/f/' . $form->slug, ['Referer' => 'https://evil.test/page'])
            ->assertOk()->assertDontSee('name="_return"', false);
    }

    public function test_plain_post_follows_the_return_page(): void
    {
        $form = $this->form();
        $this->post('/f/' . $form->slug, ['answers' => ['email' => 'ada@gmail.com'], '_return' => '/blog/my-post'])
            ->assertRedirect('/blog/my-post');
    }
}
