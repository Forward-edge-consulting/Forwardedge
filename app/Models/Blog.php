<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Blog extends Model
{
    protected $fillable = [
        'title',
        'slug',
        'thumbnail',
        'author_id',
        'category',
        'excerpt',
        'is_published',
        'published_at',
        'signup_form_id',
        'signup_position',
        'signup_after',
        'signup_heading',
        'meta_title',
        'meta_description',
    ];

    protected $casts = [
        'is_published' => 'boolean',
        'published_at' => 'datetime',
    ];

    public function details()
    {
        return $this->hasMany(BlogDetail::class)->orderBy('order');
    }

    public function author()
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function comments()
    {
        return $this->hasMany(BlogComment::class);
    }

    public function signupForm()
    {
        return $this->belongsTo(Form::class, 'signup_form_id');
    }

    /** Posts visitors may read: published, and not scheduled for later. */
    public function scopePublished(Builder $query): Builder
    {
        return $query->where('is_published', true)
            ->where(fn ($q) => $q->whereNull('published_at')->orWhere('published_at', '<=', now()));
    }

    public function isLive(): bool
    {
        return $this->is_published && (!$this->published_at || $this->published_at->lte(now()));
    }

    /** draft | scheduled | published */
    public function getStatusAttribute(): string
    {
        if (!$this->is_published) {
            return 'draft';
        }

        return $this->published_at && $this->published_at->isFuture() ? 'scheduled' : 'published';
    }

    /** The date shown to readers. */
    public function getDisplayDateAttribute()
    {
        return $this->published_at ?? $this->created_at;
    }
}
