<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class EventRegistration extends Model
{
    use HasFactory;

    protected $fillable = [
        'event_id',
        'first_name',
        'last_name',
        'email',
        'phone',
        'company',
        'job_title',
        'special_requirements',
        'status',
        'registration_code',
        'amount_paid',
        'payment_status',
        'payment_reference',
        'registered_at'
    ];

    protected $casts = [
        'amount_paid' => 'decimal:2',
        'registered_at' => 'datetime'
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($registration) {
            if (empty($registration->registration_code)) {
                $registration->registration_code = 'REG-' . strtoupper(Str::random(8));
            }
            if (empty($registration->registered_at)) {
                $registration->registered_at = now();
            }
        });
    }

    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    public function getFullNameAttribute()
    {
        return $this->first_name . ' ' . $this->last_name;
    }
}
