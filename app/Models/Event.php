<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Event extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'slug',
        'short_description',
        'thumbnail',
        'banner_image',
        'location',
        'venue',
        'start_date',
        'end_date',
        'timezone',
        'status',
        'type',
        'category_id',
        'highlights',
        'cta_type',
        'cta_label',
        'cta_url',
        'cta_form_id',
        'price',
        'max_attendees',
        'current_attendees',
        'organizer_name',
        'organizer_email',
        'contact_phone',
        'social_links',
        'meta_description',
        'meta_keywords',
        'is_featured'
    ];

    protected $casts = [
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'social_links' => 'array',
        'highlights' => 'array',
        'price' => 'decimal:2',
        'is_featured' => 'boolean'
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($event) {
            if (empty($event->slug)) {
                $event->slug = Str::slug($event->title);
            }
        });
    }

    /** Kept for the historical records. Online registration has been retired. */
    public function registrations()
    {
        return $this->hasMany(EventRegistration::class);
    }

    public function category()
    {
        return $this->belongsTo(EventCategory::class, 'category_id');
    }

    public function ctaForm()
    {
        return $this->belongsTo(Form::class, 'cta_form_id');
    }

    /** Published, and not hidden behind an unpublished or event-hidden page. */
    public function scopeVisibleOnSite($query)
    {
        return $query->where('status', 'published')->where(function ($q) {
            $q->whereDoesntHave('pages')->orWhereHas('pages', fn ($p) => $p->where('status', 'published')->where('show_on_events', true));
        });
    }

    /** Over once its end date (or, with no end date, its start date) has passed. */
    public function scopePast($query)
    {
        return $query->where(fn ($q) => $q->where('end_date', '<', now())
            ->orWhere(fn ($w) => $w->whereNull('end_date')->where('start_date', '<', now()->startOfDay())));
    }

    public function scopeNotPast($query)
    {
        return $query->where(fn ($q) => $q->where('end_date', '>=', now())
            ->orWhere(fn ($w) => $w->whereNull('end_date')->where('start_date', '>=', now()->startOfDay())));
    }

    public function getIsPastAttribute(): bool
    {
        $over = $this->end_date ?? $this->start_date;

        return $over ? ($this->end_date ? $over->isPast() : $over->lt(now()->startOfDay())) : false;
    }

    public function getCategoryLabelAttribute(): string
    {
        return $this->category?->name ?? ucfirst((string) $this->type);
    }

    /** "120 attendees" style chips for the past-events wall. */
    public function getHighlightLineAttribute(): ?string
    {
        $items = collect($this->highlights ?? [])->filter(fn ($h) => filled($h['value'] ?? null) && filled($h['label'] ?? null));

        return $items->isEmpty() ? null : $items->take(2)->map(fn ($h) => $h['value'] . ' ' . strtolower($h['label']))->implode(', ');
    }


    // Scopes
    public function scopePublished($query)
    {
        return $query->where('status', 'published');
    }

    public function scopeUpcoming($query)
    {
        return $query->where('start_date', '>', now());
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    // Accessors
    public function getFormattedPriceAttribute()
    {
        return $this->price ? '$' . number_format($this->price, 2) : 'Free';
    }

    public function getIsUpcomingAttribute()
    {
        return $this->start_date > now();
    }

    public function page()
    {
        return $this->morphOne(Page::class, 'pageable')->latestOfMany();
    }

    public function pages()
    {
        return $this->morphMany(Page::class, 'pageable');
    }
}
