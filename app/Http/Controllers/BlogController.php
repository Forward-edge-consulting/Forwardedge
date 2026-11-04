<?php

namespace App\Http\Controllers;

use App\Models\Blog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class BlogController extends Controller
{
    public function index(Request $request)
    {
        $blogs = Blog::with(['author', 'details'])->published()->orderByRaw('COALESCE(published_at, created_at) DESC')->paginate(6);
        if ($request->ajax()) {
            return response()->json([
                'blogs' => view('user.pages.blog', compact('blogs'))->render(),
                'pagination' => (string) $blogs->links('pagination::bootstrap-4'),
            ]);
        }

        // Blog categories with counts
        $categories = Blog::published()->whereNotNull('category')->where('category', '!=', '')->select('category', DB::raw('COUNT(*) as count'))
            ->groupBy('category')
            ->pluck('count', 'category');

        $latestPosts = Blog::published()->orderByRaw('COALESCE(published_at, created_at) DESC')->take(3)->get();

        return view('user.pages.blog', compact('blogs', 'categories', 'latestPosts'));
    }

    public function show($slug)
    {
        $blog = Blog::with(['author', 'details'])->where('slug', $slug)->firstOrFail();

        // Drafts and scheduled posts are visible only to a signed-in admin, as a preview.
        $isPreview = !$blog->isLive();
        if ($isPreview && auth()->user()?->role !== 'admin') {
            abort(404);
        }

        // Related posts: same category, excluding current blog
        $relatedBlogs = Blog::published()->where('category', $blog->category)
            ->where('id', '!=', $blog->id)
            ->latest()
            ->take(3)
            ->get();

        if ($blog->details->isEmpty() && !$isPreview) {
            return redirect()->route('blog')->with('error', 'No details available for this post yet.');
        }
        // Blog categories with counts
        $categories = Blog::published()->whereNotNull('category')->where('category', '!=', '')->select('category', DB::raw('COUNT(*) as count'))
            ->groupBy('category')
            ->pluck('count', 'category');

        $firstSection = $blog->details->first();
        $excerpt = Str::limit(strip_tags($blog->meta_description ?: ($blog->excerpt ?: ($firstSection->content ?? $blog->title))), 160);

        seo()->set([
            'title'       => ($blog->meta_title ?: $blog->title) . ' | ' . config('seo.site_name', config('app.name')),
            'description' => $excerpt,
            'image'       => $blog->thumbnail ? asset('storage/' . $blog->thumbnail) : null,
        ], true);

        $comments = \App\Models\BlogComment::with(['replies', 'user'])
            ->where('blog_id', $blog->id)
            ->whereNull('parent_id')
            ->latest()
            ->paginate(10);

        return view('user.pages.blog_details', compact('blog', 'relatedBlogs', 'categories', 'comments', 'isPreview'));
    }
}
