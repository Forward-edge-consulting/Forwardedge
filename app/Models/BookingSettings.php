<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/** One row of settings for the consultant's availability and who gets told about bookings. */
class BookingSettings extends Model
{
    protected $table = 'booking_settings';

    protected $fillable = ['data'];

    protected $casts = ['data' => 'array'];

    public static function defaults(): array
    {
        return [
            'consultant_name' => 'Moyosore Kukoyi',
            'consultant_email' => 'moyorcharles@gmail.com',
            'cc_emails' => 'moyor@forwardedgeconsulting.com',
            'timezone' => 'Africa/Lagos',
            'days' => [1, 2, 3, 4, 5],       // ISO weekdays, Monday = 1
            'start_time' => '10:00',
            'end_time' => '14:00',
            'slot_minutes' => 30,
            'buffer_minutes' => 15,
            'min_notice_hours' => 12,
            'max_days_ahead' => 30,
            'blocked_dates' => [],            // Y-m-d
            'feed_token' => '',
        ];
    }

    public static function current(): self
    {
        $row = static::first() ?? static::create(['data' => []]);
        if (empty($row->data['feed_token'] ?? '')) {
            $row->data = array_merge($row->data ?? [], ['feed_token' => Str::random(40)]);
            $row->save();
        }

        return $row;
    }

    public function get(string $key): mixed
    {
        $value = $this->data[$key] ?? null;

        return ($value === null || $value === '') && !in_array($key, ['cc_emails', 'blocked_dates'], true)
            ? (static::defaults()[$key] ?? null)
            : ($value ?? static::defaults()[$key]);
    }

    public function toArray(): array
    {
        return array_merge(static::defaults(), $this->data ?? []);
    }

    /** Everyone who should be copied on a booking email. */
    public function ccList(): array
    {
        return collect(preg_split('/[\s,;]+/', (string) $this->get('cc_emails')))
            ->filter(fn ($e) => filter_var($e, FILTER_VALIDATE_EMAIL))->values()->all();
    }
}
