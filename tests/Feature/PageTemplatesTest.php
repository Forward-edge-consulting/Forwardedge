<?php

namespace Tests\Feature;

use App\Models\Page;
use App\Support\PageBlueprint;
use App\Support\PageTemplates;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class PageTemplatesTest extends TestCase
{
    use RefreshDatabase;

    public function test_every_template_block_passes_its_own_validation_and_renders(): void
    {
        foreach (PageTemplates::keys() as $key) {
            $page = Page::create(['title' => "T $key", 'slug' => "t-$key", 'kind' => 'generic', 'is_published' => true]);
            PageTemplates::seed($page, $key);

            foreach ($page->blocks()->get() as $block) {
                $this->assertContains($block->type, PageBlueprint::allowedTypes(), "$key uses unknown block {$block->type}");
                $v = Validator::make(['data' => $block->data], collect(PageBlueprint::rulesFor($block->type))
                    ->mapWithKeys(fn ($r, $k) => ["data.$k" => $r])->all());
                // File inputs are not part of seeded data; only check what is present.
                $this->assertFalse($v->fails(), "$key / {$block->type}: " . json_encode($v->errors()->all()));

                $html = view('user.pages.block.' . $block->type, ['block' => $block, 'page' => $page])->render();
                // Event details stay blank until the page is linked to an event.
                if ($block->type !== 'event_facts') {
                    $this->assertNotEmpty(trim($html), "$key / {$block->type} rendered empty");
                }
            }
        }
    }
}
