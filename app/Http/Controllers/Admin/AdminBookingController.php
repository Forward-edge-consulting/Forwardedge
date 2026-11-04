<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\BookingSettings;
use App\Services\BookingService;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;

class AdminBookingController extends Controller
{
    public function index(Request $request, BookingService $svc)
    {
        $tab = in_array($request->query('tab'), ['pending', 'upcoming', 'past', 'all'], true) ? $request->query('tab') : 'pending';
        $view = $request->query('view') === 'calendar' ? 'calendar' : 'list';

        $counts = [
            'pending' => Booking::where('status', 'pending')->count(),
            'upcoming' => Booking::where('status', 'approved')->where('starts_at', '>=', now())->count(),
        ];

        if ($view === 'calendar') {
            $tz = $svc->timezone();
            $month = CarbonImmutable::createFromFormat('Y-m', (string) $request->query('month', now($tz)->format('Y-m')), $tz)?->startOfMonth()
                ?? CarbonImmutable::now($tz)->startOfMonth();
            $items = Booking::active()
                ->whereBetween('starts_at', [$month->startOfWeek()->utc(), $month->endOfMonth()->endOfWeek()->utc()])
                ->orderBy('starts_at')->get()
                ->groupBy(fn ($b) => CarbonImmutable::instance($b->starts_at)->setTimezone($tz)->format('Y-m-d'));

            return view('admin.pages.bookings.index', compact('tab', 'view', 'counts', 'month', 'items', 'tz') + ['bookings' => null, 'zone' => $svc->zoneLabel()]);
        }

        $bookings = Booking::query()
            ->when($tab === 'pending', fn ($q) => $q->where('status', 'pending')->orderBy('starts_at'))
            ->when($tab === 'upcoming', fn ($q) => $q->where('status', 'approved')->where('starts_at', '>=', now())->orderBy('starts_at'))
            ->when($tab === 'past', fn ($q) => $q->where('starts_at', '<', now())->orderByDesc('starts_at'))
            ->when($tab === 'all', fn ($q) => $q->orderByDesc('starts_at'))
            ->paginate(25)->withQueryString();

        return view('admin.pages.bookings.index', compact('tab', 'view', 'counts', 'bookings') + ['month' => null, 'items' => null, 'tz' => $svc->timezone(), 'zone' => $svc->zoneLabel()]);
    }

    public function show(Booking $booking)
    {
        $booking->load('submission', 'form');

        return view('admin.pages.bookings.show', compact('booking'));
    }

    public function approve(Request $request, Booking $booking, BookingService $svc)
    {
        $data = $request->validate(['meeting_link' => 'nullable|url|max:500', 'note' => 'nullable|string|max:1000']);

        return $svc->approve($booking, $data['meeting_link'] ?? null, $data['note'] ?? null, 'admin')
            ? back()->with('success', 'Approved. The visitor and CyberMo have been emailed a calendar invite.')
            : back()->with('error', 'This request has already been dealt with.');
    }

    public function decline(Request $request, Booking $booking, BookingService $svc)
    {
        $data = $request->validate(['reason' => 'nullable|string|max:1000']);

        return $svc->decline($booking, $data['reason'] ?? null, 'admin')
            ? back()->with('success', 'Declined. The time is free again and the visitor has been told.')
            : back()->with('error', 'This request has already been dealt with.');
    }

    public function cancel(Booking $booking, BookingService $svc)
    {
        return $svc->cancel($booking) ? back()->with('success', 'Booking cancelled.') : back()->with('error', 'This booking cannot be cancelled.');
    }

    public function notes(Request $request, Booking $booking)
    {
        $booking->update(['internal_notes' => $request->validate(['internal_notes' => 'nullable|string|max:3000'])['internal_notes'] ?? null]);

        return back()->with('success', 'Notes saved.');
    }

    public function settings()
    {
        $settings = BookingSettings::current();

        return view('admin.pages.bookings.settings', ['s' => $settings->toArray(), 'feedUrl' => route('booking.feed', $settings->get('feed_token'))]);
    }

    public function updateSettings(Request $request)
    {
        $data = $request->validate([
            'consultant_name' => 'required|string|max:120',
            'consultant_email' => 'required|email|max:190',
            'cc_emails' => 'nullable|string|max:500',
            'timezone' => ['required', 'string', function ($attr, $value, $fail) {
                if (!in_array($value, \DateTimeZone::listIdentifiers(), true)) {
                    $fail('Choose a valid time zone.');
                }
            }],
            'days' => 'required|array|min:1',
            'days.*' => 'integer|between:1,7',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'slot_minutes' => 'required|integer|between:10,180',
            'buffer_minutes' => 'required|integer|between:0,120',
            'min_notice_hours' => 'required|integer|between:0,720',
            'max_days_ahead' => 'required|integer|between:1,180',
            'blocked_dates' => 'nullable|string|max:2000',
        ]);

        $blocked = collect(preg_split('/[\s,;]+/', (string) ($data['blocked_dates'] ?? '')))
            ->filter(fn ($d) => preg_match('/^\d{4}-\d{2}-\d{2}$/', $d))->unique()->values()->all();

        $settings = BookingSettings::current();
        $settings->data = array_merge($settings->data ?? [], $data, ['days' => array_map('intval', $data['days']), 'blocked_dates' => $blocked, 'cc_emails' => $data['cc_emails'] ?? '']);
        $settings->save();

        return back()->with('success', 'Booking settings saved. New bookings use them straight away.');
    }

    public function regenerateFeed()
    {
        $settings = BookingSettings::current();
        $settings->data = array_merge($settings->data ?? [], ['feed_token' => \Illuminate\Support\Str::random(40)]);
        $settings->save();

        return back()->with('success', 'New calendar link created. Re-subscribe in the calendar app; the old link no longer works.');
    }
}
