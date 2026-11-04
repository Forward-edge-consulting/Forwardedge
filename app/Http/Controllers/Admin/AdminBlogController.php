<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Blog;
use App\Models\BlogDetail;
use App\Models\Form;
use App\Support\RichText;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class AdminBlogController extends Controller
{
    public const BLOCK_TYPES = ['paragraph', 'heading', 'quote', 'list', 'image', 'video', 'button', 'form'];

    public function index()
    {
        $blogs = Blog::with('author')->withCount('details')->latest()->get();

        return view('admin.pages.blog.index', compact('blogs'));
    }

    /** "New post" opens the editor straight away on an empty draft. */
    public function store(Request $request)
    {
        $blog = Blog::create([
            'title' => 'Untitled post',
            'slug' => $this->uniqueSlug('untitled-post'),
            'author_id' => Auth::id(),
            'is_published' => false,
            'signup_form_id' => Form::system('newsletter')->id,
            'signup_position' => 'end',
        ]);

        return redirect()->route('admin.blogs.show', $blog->id);
    }

    public function show($id)
    {
        $blog = Blog::with(['details', 'author'])->findOrFail($id);

        $forms = Form::orderByDesc('is_system')->orderBy('name')
            ->get(['id', 'name', 'slug', 'is_system'])
            ->filter(fn ($f) => $f->slug !== 'page-forms')->values();

        $categories = Blog::whereNotNull('category')->where('category', '!=', '')->distinct()->orderBy('category')->pluck('category');

        return view('admin.pages.blog.editor', [
            'blog' => $blog,
            'blocks' => $blog->details->map(fn (BlogDetail $d) => $this->toClient($d))->values(),
            'forms' => $forms,
            'categories' => $categories,
        ]);
    }

    /**
     * One endpoint for autosave, "Save", "Publish" and "Schedule".
     * Autosave is refused for posts that are live, so half-written edits never reach readers.
     */
    public function save(Request $request, $id)
    {
        $blog = Blog::findOrFail($id);

        if ($request->boolean('autosave') && $blog->isLive()) {
            return response()->json(['status' => 'error', 'message' => 'This post is live. Use Update to publish your changes.'], 409);
        }

        $data = $request->validate([
            'title' => 'required|string|max:180',
            'slug' => ['nullable', 'string', 'max:190'],
            'category' => 'nullable|string|max:80',
            'excerpt' => 'nullable|string|max:300',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:300',
            'signup_form_id' => 'nullable|integer|exists:forms,id',
            'signup_position' => 'required|in:none,end,after_paragraph',
            'signup_after' => 'nullable|integer|min:1|max:50',
            'signup_heading' => 'nullable|string|max:160',
            'publish' => 'nullable|in:keep,draft,publish,schedule',
            'published_at' => 'nullable|date',
            'blocks' => 'present|array|max:300',
            'blocks.*.id' => 'nullable|integer',
            'blocks.*.type' => ['required', Rule::in(self::BLOCK_TYPES)],
            'blocks.*.content' => 'nullable',
            'blocks.*.extras' => 'nullable|array',
        ]);

        $action = $data['publish'] ?? 'keep';
        if ($action === 'schedule' && (empty($data['published_at']) || now()->gte($data['published_at']))) {
            return response()->json([
                'status' => 'error',
                'message' => 'Pick a date and time in the future to schedule this post.',
                'errors' => ['published_at' => ['Pick a date and time in the future.']],
            ], 422);
        }

        DB::transaction(function () use ($blog, $data, $action) {
            $blog->fill([
                'title' => trim($data['title']),
                'category' => $data['category'] ?? null,
                'excerpt' => $data['excerpt'] ?? null,
                'meta_title' => $data['meta_title'] ?? null,
                'meta_description' => $data['meta_description'] ?? null,
                'signup_form_id' => $data['signup_form_id'] ?? null,
                'signup_position' => $data['signup_position'],
                'signup_after' => $data['signup_position'] === 'after_paragraph' ? ($data['signup_after'] ?? 3) : null,
                'signup_heading' => $data['signup_heading'] ?? null,
            ]);

            if (filled($data['slug'] ?? null)) {
                $blog->slug = $this->uniqueSlug($data['slug'], $blog->id);
            }

            match ($action) {
                'draft' => $blog->fill(['is_published' => false]),
                'publish' => $blog->fill([
                    'is_published' => true,
                    // keep the original date when updating a live post
                    'published_at' => $blog->isLive() && $blog->published_at ? $blog->published_at : now(),
                ]),
                'schedule' => $blog->fill(['is_published' => true, 'published_at' => $data['published_at']]),
                default => null,
            };

            $blog->save();
            $this->syncBlocks($blog, $data['blocks']);
        });

        $blog->refresh()->load('details');

        return response()->json([
            'status' => 'ok',
            'saved_at' => now()->format('g:ia'),
            'blog_status' => $blog->status,
            'slug' => $blog->slug,
            'published_at' => $blog->published_at?->format('Y-m-d\TH:i'),
            'public_url' => route('blogs.show', $blog->slug),
            'blocks' => $blog->details->map(fn (BlogDetail $d) => $this->toClient($d))->values(),
        ]);
    }

    /** Images for the post body. */
    public function upload(Request $request, $id)
    {
        Blog::findOrFail($id);
        $request->validate(['image' => 'required|image|mimes:jpg,jpeg,png,webp,gif|max:5120']);
        $path = $request->file('image')->store('blogs/content', 'public');

        return response()->json(['path' => $path, 'url' => Storage::url($path)]);
    }

    public function cover(Request $request, $id)
    {
        $blog = Blog::findOrFail($id);
        $request->validate(['image' => 'required|image|mimes:jpg,jpeg,png,webp|max:5120']);

        if ($blog->thumbnail) {
            Storage::disk('public')->delete($blog->thumbnail);
        }
        $blog->update(['thumbnail' => $request->file('image')->store('blogs/thumbnails', 'public')]);

        return response()->json(['url' => Storage::url($blog->thumbnail)]);
    }

    public function destroy($id)
    {
        $blog = Blog::findOrFail($id);

        if ($blog->thumbnail) {
            Storage::disk('public')->delete($blog->thumbnail);
        }
        foreach ($blog->details as $detail) {
            if ($detail->type === 'image' && $detail->content) {
                Storage::disk('public')->delete($detail->content);
            }
            if ($detail->type === 'video' && $detail->videoIsLocal()) {
                Storage::disk('public')->delete($detail->rawContent());
            }
        }

        $blog->delete();

        return redirect()->route('admin.blogs.index')->with('success', 'Post deleted.');
    }

    // ---------------------------------------------------------------------

    /** Shape one stored block for the editor. */
    protected function toClient(BlogDetail $d): array
    {
        $extras = $d->extras ?? [];
        $content = match ($d->type) {
            'list' => $d->contentArray(),
            'paragraph' => $d->isHtml() ? RichText::clean($d->contentString()) : nl2br(e($d->contentString())),
            'image' => $d->content,
            default => $d->contentString(),
        };

        return [
            'id' => $d->id,
            'type' => $d->type,
            'content' => $content,
            'extras' => $extras,
            'url' => $d->type === 'image' ? $d->imageUrl() : null,
        ];
    }

    /** Create, update and remove rows so the stored blocks match the editor, keeping ids stable. */
    protected function syncBlocks(Blog $blog, array $blocks): void
    {
        $existing = $blog->details()->get()->keyBy('id');
        $kept = [];
        $order = 0;

        foreach ($blocks as $block) {
            $normalized = $this->normalizeBlock($block, $existing->get($block['id'] ?? 0));
            if ($normalized === null) {
                continue; // empty blocks are dropped
            }

            $payload = $normalized + ['order' => ++$order];
            $row = isset($block['id']) ? $existing->get($block['id']) : null;

            if ($row) {
                $row->update($payload);
                $kept[] = $row->id;
            } else {
                $kept[] = $blog->details()->create($payload)->id;
            }
        }

        foreach ($existing as $id => $row) {
            if (!in_array($id, $kept, true)) {
                if ($row->type === 'image' && $row->content) {
                    Storage::disk('public')->delete($row->content);
                }
                $row->delete();
            }
        }
    }

    protected function normalizeBlock(array $block, ?BlogDetail $current): ?array
    {
        $type = $block['type'];
        $content = $block['content'] ?? null;
        $extras = is_array($block['extras'] ?? null) ? $block['extras'] : [];

        switch ($type) {
            case 'paragraph':
                $html = RichText::clean(is_string($content) ? $content : '');
                return $html === '' ? null : ['type' => $type, 'content' => $html, 'extras' => ['format' => 'html']];

            case 'heading':
                $text = trim(RichText::toText(is_string($content) ? $content : ''));
                return $text === '' ? null : ['type' => $type, 'content' => Str::limit($text, 200, ''), 'extras' => null];

            case 'quote':
                $text = trim(RichText::toText(is_string($content) ? $content : ''));
                $author = trim(strip_tags((string) ($extras['author'] ?? '')));
                return $text === '' ? null : ['type' => $type, 'content' => $text, 'extras' => $author !== '' ? ['author' => Str::limit($author, 120, '')] : null];

            case 'list':
                $items = collect(is_array($content) ? $content : preg_split('/\R/', (string) $content))
                    ->map(fn ($i) => trim(RichText::toText((string) $i)))->filter()->values()->all();
                return $items ? ['type' => $type, 'content' => json_encode($items), 'extras' => null] : null;

            case 'image':
                $path = is_string($content) ? ltrim($content, '/') : '';
                if ($path === '' || str_contains($path, '..') || !str_starts_with($path, 'blogs/')) {
                    return null;
                }
                $out = array_filter([
                    'caption' => Str::limit(trim(strip_tags((string) ($extras['caption'] ?? ''))), 200, ''),
                    'alt' => Str::limit(trim(strip_tags((string) ($extras['alt'] ?? ''))), 200, ''),
                ]);
                return ['type' => $type, 'content' => $path, 'extras' => $out ?: null];

            case 'video':
                // Uploaded videos from older posts stay as they are; new ones are links.
                if ($current && $current->type === 'video' && $current->videoIsLocal() && ($extras['source'] ?? '') === 'upload') {
                    return ['type' => $type, 'content' => $current->rawContent(), 'extras' => $current->extras];
                }
                $url = trim((string) $content);
                if (!filter_var($url, FILTER_VALIDATE_URL) || !preg_match('#^https?://#i', $url)) {
                    return null;
                }
                $orientation = ($extras['orientation'] ?? '') === 'portrait' ? 'portrait' : 'landscape';
                return ['type' => $type, 'content' => $url, 'extras' => ['source' => 'url', 'orientation' => $orientation]];

            case 'button':
                $label = trim(strip_tags((string) $content));
                $url = RichText::safeUrl($extras['url'] ?? '');
                return ($label === '' || $url === null) ? null : ['type' => $type, 'content' => Str::limit($label, 80, ''), 'extras' => ['url' => $url]];

            case 'form':
                $formId = (int) ($extras['form_id'] ?? 0);
                if (!$formId || !Form::whereKey($formId)->exists()) {
                    return null;
                }
                $heading = trim(strip_tags((string) ($extras['heading'] ?? '')));
                return ['type' => $type, 'content' => null, 'extras' => array_filter(['form_id' => $formId, 'heading' => Str::limit($heading, 160, '')])];
        }

        return null;
    }

    protected function uniqueSlug(string $value, ?int $ignoreId = null): string
    {
        $base = Str::slug($value) ?: 'post';
        $slug = $base;
        $i = 2;
        while (Blog::where('slug', $slug)->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))->exists()) {
            $slug = $base . '-' . $i++;
        }

        return $slug;
    }
}
