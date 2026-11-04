<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FormSubmission extends Model
{
    protected $fillable = [
        'form_id', 'email', 'name', 'data', 'source_type', 'source_label', 'source_url',
        'consent_given', 'consent_text', 'consented_at', 'ip_hash', 'exported_at', 'export_batch',
        'download_token', 'resource_sent_at', 'resource_error', 'resource_downloaded_at', 'download_count',
    ];

    protected $casts = [
        'data' => 'array',
        'consent_given' => 'boolean',
        'consented_at' => 'datetime',
        'exported_at' => 'datetime',
        'resource_sent_at' => 'datetime',
        'resource_downloaded_at' => 'datetime',
    ];

    public function form(): BelongsTo
    {
        return $this->belongsTo(Form::class);
    }

    /** Contacts ready for a marketing import: have an email, agreed to be emailed, not exported yet. */
    public function scopeReadyToExport(Builder $query): Builder
    {
        return $query->whereNotNull('email')->where('consent_given', true)->whereNull('exported_at');
    }

    public function scopeNotExported(Builder $query): Builder
    {
        return $query->whereNull('exported_at');
    }
}
