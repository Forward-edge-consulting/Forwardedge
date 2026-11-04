<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class BlogDetail extends Model
{
     protected $fillable = ['blog_id', 'type', 'content', 'extras', 'order'];

    protected $casts = [
        'extras' => 'array',
    ];

    public function blog()
    {
        return $this->belongsTo(Blog::class);
    }

    public function contentArray(): array
    {
        if ($this->type !== 'list') {
            return [];
        }

        $decoded = json_decode($this->attributes['content'] ?? '', true);
        return is_array($decoded) ? array_values(array_filter($decoded, fn($item) => filled($item))) : [];
    }

    /** Paragraphs saved by the editor hold cleaned HTML; older ones are plain text. */
    public function isHtml(): bool
    {
        return ($this->extras['format'] ?? null) === 'html';
    }

    public function contentString(): string
    {
        $value = $this->attributes['content'] ?? '';

        if ($this->type === 'list') {
            return implode("\n", $this->contentArray());
        }

        return is_string($value) ? $value : (string) $value;
    }

    public function imageUrl(): ?string
    {
        if ($this->type !== 'image' || empty($this->attributes['content'])) {
            return null;
        }

        return Storage::url($this->attributes['content']);
    }

    public function quoteAuthor(): string
    {
        return $this->extras['author'] ?? $this->extras['cite'] ?? 'Forward Edge Consulting';
    }

    public function videoOrientation(): string
    {
        return $this->extras['orientation'] ?? 'landscape';
    }

    public function videoSource(): string
    {
        if (!empty($this->extras['source'])) {
            return $this->extras['source'];
        }

        $content = $this->attributes['content'] ?? '';
        return Str::startsWith((string) $content, ['http://', 'https://']) ? 'url' : 'upload';
    }

    public function videoUrl(): ?string
    {
        $content = $this->attributes['content'] ?? null;
        if (!$content) {
            return null;
        }

        if ($this->videoSource() === 'upload') {
            return Storage::url($content);
        }

        return $content;
    }

    public function videoIsLocal(): bool
    {
        return $this->videoSource() === 'upload' && !empty($this->attributes['content']);
    }

    public function videoIsExternal(): bool
    {
        return !$this->videoIsLocal();
    }

    public function rawContent()
    {
        return $this->attributes['content'] ?? null;
    }
}
