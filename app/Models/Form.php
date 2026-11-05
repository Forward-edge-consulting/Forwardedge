<?php

namespace App\Models;

use App\Support\FormFields;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Form extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'slug', 'description', 'fields', 'settings', 'status', 'closes_at', 'is_system'];

    protected $casts = [
        'fields' => 'array',
        'settings' => 'array',
        'closes_at' => 'datetime',
        'is_system' => 'boolean',
    ];

    public static function defaultSettings(): array
    {
        return [
            'submit_label' => 'Send',
            'success_message' => 'Thanks. We have received your response.',
            'redirect_url' => '',
            'consent_required' => true,
            'consent_text' => 'I agree to be contacted by Forward Edge and accept the Privacy Policy.',
            'notify_email' => '',
            'auto_reply' => true,
            'auto_reply_subject' => 'Thank you for getting in touch',
            'auto_reply_body' => 'We have received your response and will be in touch shortly.',
            // Resource sent after submit (lead magnet). Empty file/link means nothing is sent.
            'resource_enabled' => false,
            'resource_title' => '',
            'resource_kind' => 'file',
            'resource_path' => '',
            'resource_filename' => '',
            'resource_url' => '',
            'resource_email_subject' => 'Your resource from Forward Edge',
            'resource_email_body' => 'Thank you for your interest. Your resource is ready to download using the button below.',
            'popup_title' => 'Check your email',
            'popup_message' => 'We have sent your resource to {email}. If it does not arrive in a few minutes, check your spam folder.',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (Form $form) {
            if (blank($form->slug)) {
                $form->slug = Str::slug($form->name) ?: 'form';
            }
            if (!$form->exists || $form->isDirty('slug')) {
                $base = Str::slug($form->slug) ?: 'form';
                $slug = $base;
                $i = 2;
                while (static::where('slug', $slug)->where('id', '!=', $form->id ?? 0)->exists()) {
                    $slug = $base . '-' . $i++;
                }
                $form->slug = $slug;
            }
            $form->fields = FormFields::normalize($form->fields ?? []);
        });
    }

    /** The built-in forms that receive footer / blog / legacy page-builder signups. */
    public static function system(string $slug): self
    {
        return static::firstOrCreate(
            ['slug' => $slug],
            [
                'name' => $slug === 'newsletter' ? 'Newsletter' : 'Page forms',
                'fields' => [],
                'settings' => static::defaultSettings(),
                'status' => 'open',
                'is_system' => true,
            ]
        );
    }

    public function submissions(): HasMany
    {
        return $this->hasMany(FormSubmission::class);
    }

    public function setting(string $key, mixed $default = null): mixed
    {
        return ($this->settings[$key] ?? null) !== null && ($this->settings[$key] ?? '') !== ''
            ? $this->settings[$key]
            : ($default ?? (static::defaultSettings()[$key] ?? null));
    }

    /** True when this form sends a file or link after someone submits. */
    public function hasResource(): bool
    {
        if (!$this->setting('resource_enabled', false)) {
            return false;
        }

        return $this->setting('resource_kind') === 'link'
            ? filled($this->setting('resource_url'))
            : filled($this->setting('resource_path'));
    }

    public function isOpen(): bool
    {
        if ($this->status !== 'open') {
            return false;
        }

        return !$this->closes_at || $this->closes_at->isFuture();
    }

    public function getPublicUrlAttribute(): string
    {
        return route('forms.show', $this->slug);
    }
}
