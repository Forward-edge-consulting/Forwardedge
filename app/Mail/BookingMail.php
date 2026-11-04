<?php

namespace App\Mail;

use App\Models\Booking;
use App\Services\BookingService;
use App\Support\Ics;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\URL;

/** One mailable for every booking email: kind = requested | approved | declined | cancelled, audience = visitor | consultant. */
class BookingMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Booking $booking, public string $kind, public string $audience)
    {
    }

    public function build(): self
    {
        $svc = app(BookingService::class);
        $settings = $svc->settings();
        $when = $svc->label($this->booking->starts_at);
        $visitor = $this->audience === 'visitor';

        $subject = match ($this->kind) {
            'requested' => $visitor ? 'We received your discovery call request' : 'New discovery call request: ' . ($this->booking->name ?: $this->booking->email) . ', ' . $when,
            'approved' => $visitor ? 'Your discovery call is confirmed: ' . $when : 'Booked: ' . ($this->booking->name ?: $this->booking->email) . ', ' . $when,
            'declined' => 'About your discovery call request',
            default => 'Your discovery call has been cancelled',
        };

        $data = [
            'booking' => $this->booking,
            'kind' => $this->kind,
            'audience' => $this->audience,
            'when' => $when,
            'consultant' => $settings->get('consultant_name'),
            'rows' => $this->booking->details ?? [],
            'reviewUrl' => $this->kind === 'requested' && !$visitor
                ? URL::temporarySignedRoute('booking.review', now()->addDays(14), ['booking' => $this->booking->id])
                : null,
            'adminUrl' => route('admin.bookings.show', $this->booking),
            'bookAgainUrl' => route('consultancy') . '#book',
        ];

        $this->subject($subject)->view('emails.booking')->with($data);

        if ($this->kind === 'approved') {
            $fromEmail = (string) config('mail.from.address');
            $attendees = [(string) $settings->get('consultant_name') => (string) $settings->get('consultant_email')];
            if ($this->booking->email) {
                $attendees[(string) ($this->booking->name ?: $this->booking->email)] = $this->booking->email;
            }
            $ics = Ics::invite($this->booking, 'Forward Edge Consulting', $fromEmail, $attendees, 'Discovery call: Forward Edge Consulting');
            $this->attachData($ics, 'discovery-call.ics', ['mime' => 'text/calendar; charset=UTF-8; method=REQUEST']);
        }

        return $this;
    }
}
