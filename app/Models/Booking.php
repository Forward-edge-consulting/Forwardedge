<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Booking extends Model
{
    protected $fillable = [
        'form_id', 'form_submission_id', 'name', 'email', 'details', 'starts_at', 'ends_at', 'status', 'active_slot',
        'meeting_link', 'note_to_visitor', 'decline_reason', 'decided_via', 'decided_at', 'internal_notes', 'uid',
    ];

    protected $casts = [
        'details' => 'array',
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'decided_at' => 'datetime',
    ];

    public function form(): BelongsTo
    {
        return $this->belongsTo(Form::class);
    }

    public function submission(): BelongsTo
    {
        return $this->belongsTo(FormSubmission::class, 'form_submission_id');
    }

    public function scopeActive(Builder $q): Builder
    {
        return $q->whereIn('status', ['pending', 'approved']);
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    /** "Tue 10 Nov 2026, 10:45 AM WAT" in the consultant's time zone. */
    public function whenLabel(): string
    {
        return app(\App\Services\BookingService::class)->label($this->starts_at);
    }
}
