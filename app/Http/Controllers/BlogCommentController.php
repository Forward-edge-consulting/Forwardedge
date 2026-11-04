<?php

namespace App\Http\Controllers;

use App\Models\Blog;
use App\Models\BlogComment;
use App\Support\Turnstile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BlogCommentController extends Controller
{
    public function store(Request $request, string $slug)
    {
        $blog = Blog::where('slug', $slug)->firstOrFail();

        $user = Auth::user();

        if (!Turnstile::passes($request)) {
            return back()->withInput()->withErrors(['body' => Turnstile::FAILED_MESSAGE]);
        }

        $rules = [
            'body' => 'required|string|max:2000',
        ];

        if (!$user) {
            $rules['name'] = 'required|string|max:120';
            $rules['email'] = 'required|email|max:150';
        } else {
            $rules['name'] = 'nullable|string|max:120';
            $rules['email'] = 'nullable|email|max:150';
        }

        $data = $request->validate($rules);


        BlogComment::create([
            'blog_id' => $blog->id,
            'user_id' => $user?->id,
            'name' => $user?->name ?? $data['name'],
            'email' => $user?->email ?? $data['email'],
            'body' => $data['body'],
            'is_admin_reply' => false,
        ]);

        return back()->with('success', 'Comment added.');
    }

    public function reply(Request $request, string $slug, BlogComment $comment)
    {
        $blog = Blog::where('slug', $slug)->firstOrFail();

        if (!Auth::check() || Auth::user()->role !== 'admin') {
            abort(403, 'Only admins can reply');
        }

        $data = $request->validate([
            'body' => 'required|string|max:2000',
        ]);

        BlogComment::create([
            'blog_id' => $blog->id,
            'user_id' => Auth::id(),
            'parent_id' => $comment->id,
            'name' => Auth::user()->name,
            'email' => Auth::user()->email,
            'body' => $data['body'],
            'is_admin_reply' => true,
        ]);

        return back()->with('success', 'Reply posted.');
    }
}
