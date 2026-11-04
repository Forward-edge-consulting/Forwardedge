<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\BookingSettings;
use App\Support\Ics;

/** Private calendar feed of approved bookings. The consultant subscribes once and his calendar stays up to date. */
class BookingFeedController extends Controller
{
    public function __invoke(string $token)
    {
        $settings = BookingSettings::current();
        abort_unless(hash_equals((string) $settings->get('feed_token'), $token), 404);

        $bookings = Booking::where('status', 'approved')->where('starts_at', '>=', now()->subDays(30))->orderBy('starts_at')->get();

        return response(Ics::feed($bookings, 'Forward Edge discovery calls'), 200, [
            'Content-Type' => 'text/calendar; charset=UTF-8',
            'Content-Disposition' => 'inline; filename="discovery-calls.ics"',
            'Cache-Control' => 'no-store',
        ]);
    }
}
