<?php

namespace Tests\Feature;

use App\Models\Blog;
use App\Models\Form;
use App\Models\FormSubmission;
use App\Models\User;
use App\Support\RichText;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class BlogEditorTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create(['role' => 'admin']);
    }

    private function makePost(array $overrides = []): Blog
    {
        return Blog::create(array_merge([
            'title' => 'Phishing basics', 'slug' => 'phishing-basics', 'is_published' => true, 'category' => 'Awareness',
        ], $overrides));
    }

    private function save(Blog $blog, array $overrides = [])
    {
        return $this->putJson(route('admin.blogs.save', $blog->id), array_merge([
            'title' => $blog->title, 'signup_position' => 'end', 'blocks' => [],
        ], $overrides));
    }

    // ---- rich text safety --------------------------------------------------

    public function test_rich_text_keeps_bold_italic_links_and_drops_everything_else(): void
    {
        $dirty = '<p onclick="x()">Hello <b>world</b> <script>alert(1)</script><i>there</i> '
               . '<a href="javascript:alert(1)">bad</a> <a href="https://forwardedgeconsulting.com" onclick="x()">good</a>'
               . '<iframe src="https://evil"></iframe><span style="color:red">plain</span></p>';

        $clean = RichText::clean($dirty);

        $this->assertStringContainsString('<strong>world</strong>', $clean);
        $this->assertStringContainsString('<em>there</em>', $clean);
        $this->assertStringContainsString('<a href="https://forwardedgeconsulting.com" target="_blank" rel="noopener noreferrer">good</a>', $clean);
        $this->assertStringNotContainsString('script', $clean);
        $this->assertStringNotContainsString('alert', $clean);
        $this->assertStringNotContainsString('javascript:', $clean);
        $this->assertStringNotContainsString('onclick', $clean);
        $this->assertStringNotContainsString('iframe', $clean);
        $this->assertStringNotContainsString('style=', $clean);
        $this->assertStringContainsString('bad', $clean);     // text survives, link does not
        $this->assertStringContainsString('plain', $clean);
    }

    // ---- saving blocks -----------------------------------------------------

    public function test_new_post_opens_as_an_empty_draft_with_the_newsletter_form(): void
    {
        $this->actingAs($this->admin())->post(route('admin.blogs.store'))->assertRedirect();

        $blog = Blog::firstOrFail();
        $this->assertFalse($blog->is_published);
        $this->assertSame('draft', $blog->status);
        $this->assertSame(Form::system('newsletter')->id, $blog->signup_form_id);
    }

    public function test_saving_creates_updates_reorders_and_removes_blocks_keeping_ids(): void
    {
        $this->actingAs($this->admin());
        $blog = $this->makePost(['is_published' => false]);

        $first = $this->save($blog, ['blocks' => [
            ['type' => 'heading', 'content' => 'Why it matters'],
            ['type' => 'paragraph', 'content' => 'One <b>bold</b> claim.'],
            ['type' => 'paragraph', 'content' => '   '],                          // blank: dropped
            ['type' => 'list', 'content' => ['Call back', '', 'Two approvals']],
            ['type' => 'quote', 'content' => 'Slow down.', 'extras' => ['author' => 'Moyosore']],
            ['type' => 'button', 'content' => 'Book a call', 'extras' => ['url' => '/consultancy']],
            ['type' => 'button', 'content' => 'Bad link', 'extras' => ['url' => 'javascript:alert(1)']], // dropped
        ]])->assertOk()->json('blocks');

        $this->assertSame(['heading', 'paragraph', 'list', 'quote', 'button'], array_column($first, 'type'));
        $this->assertSame(['Call back', 'Two approvals'], $first[2]['content']);
        $ids = array_column($first, 'id');

        // reorder, edit one, delete one
        $second = $this->save($blog, ['blocks' => [
            ['id' => $ids[1], 'type' => 'paragraph', 'content' => 'Edited.'],
            ['id' => $ids[0], 'type' => 'heading', 'content' => 'Why it matters'],
        ]])->assertOk()->json('blocks');

        $this->assertSame([$ids[1], $ids[0]], array_column($second, 'id'));
        $this->assertSame(2, $blog->details()->count());
        $this->assertSame('Edited.', $blog->details()->orderBy('order')->first()->content);
        $this->assertSame('html', $blog->details()->first()->extras['format']);
    }

    public function test_the_image_upload_and_block_rules_reject_bad_paths(): void
    {
        Storage::fake('public');
        $this->actingAs($this->admin());
        $blog = $this->makePost(['is_published' => false]);

        $path = $this->post(route('admin.blogs.upload', $blog->id), ['image' => UploadedFile::fake()->image('a.png', 800, 400)])
            ->assertOk()->json('path');
        Storage::disk('public')->assertExists($path);

        $this->postJson(route('admin.blogs.upload', $blog->id), ['image' => UploadedFile::fake()->create('x.pdf', 10, 'application/pdf')])->assertStatus(422);

        $blocks = $this->save($blog, ['blocks' => [
            ['type' => 'image', 'content' => $path, 'extras' => ['caption' => 'Fig 1', 'alt' => 'A diagram']],
            ['type' => 'image', 'content' => '../../.env'],
            ['type' => 'image', 'content' => 'users/secret.png'],
        ]])->assertOk()->json('blocks');

        $this->assertCount(1, $blocks);
        $this->assertSame('A diagram', $blocks[0]['extras']['alt']);
    }

    // ---- who can see what --------------------------------------------------

    public function test_drafts_and_scheduled_posts_are_hidden_from_visitors_but_previewable_by_admins(): void
    {
        $draft = $this->makePost(['slug' => 'secret-draft', 'is_published' => false]);
        $draft->details()->create(['type' => 'paragraph', 'content' => 'Not yet.', 'order' => 1]);
        $later = $this->makePost(['slug' => 'next-week', 'published_at' => now()->addWeek()]);
        $later->details()->create(['type' => 'paragraph', 'content' => 'Soon.', 'order' => 1]);
        $live = $this->makePost(['slug' => 'live-now']);
        $live->details()->create(['type' => 'paragraph', 'content' => 'Hello readers.', 'order' => 1]);

        $this->get('/blog/secret-draft')->assertNotFound();
        $this->get('/blog/next-week')->assertNotFound();
        $this->get('/blog/live-now')->assertOk()->assertSee('Hello readers.');
        $this->get('/blog')->assertOk()->assertSee('Phishing basics')->assertDontSee('secret-draft');

        $this->actingAs($this->admin())->get('/blog/secret-draft')->assertOk()->assertSee('only visible to you');
    }

    public function test_a_scheduled_post_goes_live_when_its_time_arrives(): void
    {
        $this->actingAs($this->admin());
        $blog = $this->makePost(['is_published' => false]);

        $this->save($blog, ['publish' => 'schedule', 'published_at' => now()->subMinute()->toDateTimeString(), 'blocks' => []])
            ->assertStatus(422);   // schedule must be in the future

        $when = now()->addHour();
        $this->save($blog, ['publish' => 'schedule', 'published_at' => $when->toDateTimeString(),
            'blocks' => [['type' => 'paragraph', 'content' => 'Timed.']]])->assertOk()->assertJsonPath('blog_status', 'scheduled');
        $this->assertFalse($blog->fresh()->isLive());

        $this->travel(2)->hours();
        $this->assertTrue($blog->fresh()->isLive());
        $this->assertSame('published', $blog->fresh()->status);
    }

    public function test_autosave_is_refused_for_live_posts_but_explicit_update_works(): void
    {
        $this->actingAs($this->admin());
        $blog = $this->makePost();

        $this->save($blog, ['autosave' => true, 'blocks' => [['type' => 'paragraph', 'content' => 'Half done']]])->assertStatus(409);
        $this->assertSame(0, $blog->details()->count());

        $this->save($blog, ['publish' => 'keep', 'blocks' => [['type' => 'paragraph', 'content' => 'Finished.']]])->assertOk();
        $this->assertSame(1, $blog->details()->count());
    }

    // ---- newsletter placement ----------------------------------------------

    public function test_signup_form_appears_where_the_post_says_and_not_twice(): void
    {
        $form = Form::system('newsletter');
        $blog = $this->makePost(['slug' => 'with-signup', 'signup_form_id' => $form->id, 'signup_position' => 'after_paragraph', 'signup_after' => 2, 'signup_heading' => 'Join the list']);
        foreach (['First.', 'Second.', 'Third.'] as $i => $text) {
            $blog->details()->create(['type' => 'paragraph', 'content' => $text, 'order' => $i + 1, 'extras' => ['format' => 'html']]);
        }

        $html = $this->get('/blog/with-signup')->assertOk()->getContent();
        $this->assertSame(1, substr_count($html, 'fe-blog-signup"'));
        $this->assertStringContainsString('Join the list', $html);
        $this->assertGreaterThan(strpos($html, 'Second.'), strpos($html, 'fe-blog-signup"'));
        $this->assertLessThan(strpos($html, 'Third.'), strpos($html, 'fe-blog-signup"'));

        $blog->update(['signup_position' => 'none']);
        $this->assertStringNotContainsString('fe-blog-signup"', $this->get('/blog/with-signup')->getContent());

        // an explicit signup block replaces the automatic one
        $blog->update(['signup_position' => 'end']);
        $blog->details()->create(['type' => 'form', 'content' => null, 'order' => 4, 'extras' => ['form_id' => $form->id, 'heading' => 'In the middle']]);
        $html = $this->get('/blog/with-signup')->getContent();
        $this->assertSame(1, substr_count($html, 'fe-blog-signup"'));
        $this->assertStringContainsString('In the middle', $html);
    }

    public function test_signups_from_a_post_are_saved_with_that_post_as_the_source(): void
    {
        $form = Form::system('newsletter');
        $blog = $this->makePost(['slug' => 'source-test', 'title' => 'Source test']);

        $this->withHeader('Referer', url('/blog/source-test'))
            ->postJson(route('forms.submit', 'newsletter'), ['answers' => ['email' => 'amaka@gmail.com'], 'consent' => '1'])->assertOk();

        $row = FormSubmission::firstOrFail();
        $this->assertSame('Blog: Source test', $row->source_label);
        $this->assertTrue($row->consent_given);
    }

    public function test_older_plain_text_paragraphs_still_render_and_only_admins_reach_the_editor(): void
    {
        $blog = $this->makePost(['slug' => 'legacy']);
        $blog->details()->create(['type' => 'paragraph', 'content' => "Plain text <b>not html</b>\nsecond line", 'order' => 1]);

        $this->get('/blog/legacy')->assertOk()->assertSee('Plain text &lt;b&gt;not html&lt;/b&gt;', false);

        $this->get(route('admin.blogs.show', $blog->id))->assertRedirect();
        $this->actingAs($this->admin())->get(route('admin.blogs.show', $blog->id))->assertOk()->assertSee('Post title');
    }
}
