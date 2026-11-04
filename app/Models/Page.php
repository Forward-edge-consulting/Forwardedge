<?php

namespace App\Models;

use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\{MorphTo, HasMany};

class Page extends Model
{
    protected $fillable = [
        'title',
        'slug',
        'status',
        'template',
        'meta',
        'published_at',
        'show_on_events',
        'pageable_type',  // Changed from owner_type
        'pageable_id'     // Changed from owner_id
    ];

    protected $casts = [
        'meta' => 'array',
        'published_at' => 'datetime',
        'show_on_events' => 'boolean',
    ];

    /** Course-owned pages remain archived and cannot be served or managed. */
    public function scopeForWebsite($query)
    {
        return $query->where(fn ($q) => $q->whereNull('pageable_type')->orWhere('pageable_type', Event::class));
    }

    public function blocks(): HasMany
    {
        return $this->hasMany(Block::class)
            ->whereNull('parent_id')
            ->orderBy('order');
    }

    public function pageable(): MorphTo
    {
        return $this->morphTo();
    }

    public function getUrlAttribute(): string
    {
        return route('page.show', $this->slug);
    }

    public function getPageableTypeDisplayAttribute(): string
    {
        if (!$this->pageable_type) {
            return 'Standalone';
        }

        return match ($this->pageable_type) {
            Event::class => 'Event',
            default => 'Unknown'
        };
    }

    protected static function booted()
    {
        static::saving(function (Page $page) {
            if (blank($page->slug) && filled($page->title)) {
                $base = Str::slug($page->title);
                $slug = $base;
                $i = 2;
                while (static::where('slug', $slug)->where('id', '!=', $page->id)->exists()) {
                    $slug = "{$base}-{$i}";
                    $i++;
                }
                $page->slug = $slug;
            }
        });
    }
}
