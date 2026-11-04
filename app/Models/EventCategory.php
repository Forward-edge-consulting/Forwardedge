<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class EventCategory extends Model
{
    protected $fillable = ['name', 'slug', 'sort_order'];

    protected static function booted(): void
    {
        static::saving(function (EventCategory $c) {
            $base = Str::slug($c->slug ?: $c->name) ?: 'category';
            $slug = $base;
            $i = 2;
            while (static::where('slug', $slug)->where('id', '!=', $c->id ?? 0)->exists()) {
                $slug = $base . '-' . $i++;
            }
            $c->slug = $slug;
        });

        // Deleting a category leaves its events uncategorised rather than deleting them.
        static::deleting(fn (EventCategory $c) => Event::where('category_id', $c->id)->update(['category_id' => null]));
    }

    public function events(): HasMany
    {
        return $this->hasMany(Event::class, 'category_id');
    }
}
