<?php

namespace App\Services;

use App\Mail\BookingMail;
use App\Models\Booking;
use App\Models\BookingSettings;
use App\Models\Form;
use App\Models\FormSubmission;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

/**
 * Availability, taking a slot, and the approve / decline decision. Times are stored in UTC and
 * worked out in the consultant's time zone (Africa/Lagos by default).
 */
class BookingService
{
    protected BookingSettings $settings;

    public function __construct()
    {
        $this->settings = BookingSettings::current();
    }

    public function settings(): BookingSettings
    {
        return $this->settings;
    }

    public function timezone(): string
    {
        return (string) $this->settings->get('timezone');
    }

    /** Minutes one call blocks: the call itself plus the buffer after it. */
    protected function block(): int
    {
        return (int) $this->settings->get('slot_minutes') + (int) $this->settings->get('buffer_minutes');
    }

    /** All start times (UTC) the consultant works on one calendar day, before notice and bookings are applied. */
    protected function gridForDay(CarbonImmutable $day): array
    {
        $tz = $this->timezone();
        $day = $day->setTimezone($tz)->startOfDay();

        if (!in_array($day->dayOfWeekIso, array_map('intval', (array) $this->settings->get('days')), true)) {
            return [];
        }
        if (in_array($day->format('Y-m-d'), (array) $this->settings->get('blocked_dates'), true)) {
            return [];
        }

        [$sh, $sm] = array_map('intval', explode(':', (string) $this->settings->get('start_time')));
        [$eh, $em] = array_map('intval', explode(':', (string) $this->settings->get('end_time')));
        $cursor = $day->setTime($sh, $sm);
        $end = $day->setTime($eh, $em);
        $length = (int) $this->settings->get('slot_minutes');

        $slots = [];
        while ($cursor->addMinutes($length) <= $end) {
            $slots[] = $cursor->utc();
            $cursor = $cursor->addMinutes($this->block());
        }

        return $slots;
    }

    /** Is this start time bookable right now? */
    public function isAvailable(CarbonInterface $start): bool
    {
        $start = CarbonImmutable::instance($start)->utc();
        if ($start < now()->addHours((int) $this->settings->get('min_notice_hours'))) {
            return false;
        }
        if ($start > now()->addDays((int) $this->settings->get('max_days_ahead'))) {
            return false;
        }

        $onGrid = collect($this->gridForDay($start))->contains(fn ($s) => $s->equalTo($start));

        return $onGrid && !$this->isTaken($start);
    }

    /** Check and lock the slot inside the submit transaction, before anything is saved. */
    public function claimable(CarbonInterface $start): bool
    {
        $start = CarbonImmutable::instance($start)->utc();

        return $this->isAvailable($start) && !$this->isTaken($start, true);
    }

    protected function isTaken(CarbonImmutable $start, bool $lock = false): bool
    {
        $query = Booking::active()
            ->where('starts_at', '>', $start->subMinutes($this->block()))
            ->where('starts_at', '<', $start->addMinutes($this->block()));

        return ($lock ? $query->lockForUpdate() : $query)->exists();
    }

    /** What the public picker shows: the next bookable days with their slots. */
    public function availability(): array
    {
        $tz = $this->timezone();
        $today = CarbonImmutable::now($tz)->startOfDay();
        $taken = Booking::active()->where('starts_at', '>=', now()->subDay())->pluck('starts_at')->map(fn ($d) => CarbonImmutable::instance($d)->utc());
        $earliest = now()->addHours((int) $this->settings->get('min_notice_hours'));
        $days = [];

        for ($i = 0; $i <= (int) $this->settings->get('max_days_ahead'); $i++) {
            $day = $today->addDays($i);
            $slots = collect($this->gridForDay($day))
                ->filter(fn ($s) => $s >= $earliest)
                ->reject(fn ($s) => $taken->contains(fn ($t) => $t > $s->subMinutes($this->block()) && $t < $s->addMinutes($this->block())))
                ->map(fn ($s) => ['value' => $s->format('Y-m-d\TH:i:s\Z'), 'label' => $s->setTimezone($tz)->format('g:i A')])
                ->values()->all();

            if ($slots) {
                $days[] = ['date' => $day->format('Y-m-d'), 'label' => $day->format('D j M'), 'long' => $day->format('l j F'), 'slots' => $slots];
            }
        }

        return ['timezone' => $tz, 'zone_label' => $this->zoneLabel(), 'days' => $days];
    }

    public function parse(?string $value): ?CarbonImmutable
    {
        try {
            return $value ? CarbonImmutable::parse($value)->utc() : null;
        } catch (\Throwable) {
            return null;
        }
    }

    public function label(CarbonInterface $start): string
    {
        return CarbonImmutable::instance($start)->setTimezone($this->timezone())->format('D j M Y, g:i A') . ' ' . $this->zoneLabel();
    }

    public function zoneLabel(): string
    {
        return $this->timezone() === 'Africa/Lagos' ? 'WAT' : CarbonImmutable::now($this->timezone())->format('T');
    }

    /**
     * Take the slot for a submission. Must run inside the same transaction as the submission so a
     * lost race rolls the lead back too. Returns null when the slot is no longer free.
     */
    public function create(Form $form, FormSubmission $submission, CarbonImmutable $start, array $rows): ?Booking
    {
        if (!$this->isAvailable($start) || $this->isTaken($start, true)) {
            return null;
        }

        $booking = Booking::create([
            'form_id' => $form->id,
            'form_submission_id' => $submission->id,
            'name' => $submission->name,
            'email' => $submission->email,
            'details' => $rows,
            'starts_at' => $start,
            'ends_at' => $start->addMinutes((int) $this->settings->get('slot_minutes')),
            'status' => 'pending',
            'active_slot' => (string) $start->timestamp,
            'uid' => Str::uuid() . '@forwardedgeconsulting.com',
        ]);

        // Mail runs after the visitor has their answer. A failure is logged and never loses the booking.
        dispatch(function () use ($booking) {
            $this->send($booking, 'requested', 'visitor');
            $this->send($booking, 'requested', 'consultant');
        })->afterResponse();

        return $booking;
    }

    public function approve(Booking $booking, ?string $link, ?string $note, string $via): bool
    {
        if (!$booking->isPending()) {
            return false;
        }

        $booking->update([
            'status' => 'approved',
            'meeting_link' => $link ?: null,
            'note_to_visitor' => $note ?: null,
            'decided_via' => $via,
            'decided_at' => now(),
        ]);

        $this->send($booking, 'approved', 'visitor');
        $this->send($booking, 'approved', 'consultant');

        return true;
    }

    public function decline(Booking $booking, ?string $reason, string $via): bool
    {
        if (!$booking->isPending()) {
            return false;
        }

        $booking->update([
            'status' => 'declined',
            'active_slot' => null,       // frees the slot for someone else
            'decline_reason' => $reason ?: null,
            'decided_via' => $via,
            'decided_at' => now(),
        ]);

        $this->send($booking, 'declined', 'visitor');

        return true;
    }

    public function cancel(Booking $booking): bool
    {
        if (!$booking->isApproved() && !$booking->isPending()) {
            return false;
        }
        $wasApproved = $booking->isApproved();
        $booking->update(['status' => 'cancelled', 'active_slot' => null, 'decided_at' => now()]);
        if ($wasApproved) {
            $this->send($booking, 'cancelled', 'visitor');
        }

        return true;
    }

    /** Best effort. Returns whether the email went out. */
    public function send(Booking $booking, string $kind, string $audience): bool
    {
        $to = $audience === 'visitor' ? $booking->email : $this->settings->get('consultant_email');
        if (!$to) {
            return false;
        }

        try {
            $mail = Mail::to($to);
            if ($audience === 'consultant' && $this->settings->ccList()) {
                $mail->cc($this->settings->ccList());
            }
            $mail->send(new BookingMail($booking, $kind, $audience));

            return true;
        } catch (\Throwable $e) {
            Log::warning('Booking email failed', ['booking' => $booking->id, 'kind' => $kind, 'to' => $audience, 'error' => $e->getMessage()]);

            return false;
        }
    }
}
