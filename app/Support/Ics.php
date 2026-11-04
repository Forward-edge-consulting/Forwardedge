<?php

namespace App\Support;

use App\Models\Booking;

/** Builds a calendar invite (.ics) that Google, Outlook and Apple Calendar understand. */
class Ics
{
    public static function invite(Booking $booking, string $organizerName, string $organizerEmail, array $attendees, string $title): string
    {
        $fmt = fn ($d) => $d->copy()->utc()->format('Ymd\THis\Z');
        $desc = "Free security discovery call with Forward Edge Consulting.\n"
            . ($booking->meeting_link ? 'Join: ' . $booking->meeting_link . "\n" : '')
            . ($booking->note_to_visitor ? $booking->note_to_visitor . "\n" : '');

        $lines = [
            'BEGIN:VCALENDAR', 'VERSION:2.0', 'PRODID:-//Forward Edge Consulting//Bookings//EN', 'CALSCALE:GREGORIAN', 'METHOD:REQUEST',
            'BEGIN:VEVENT',
            'UID:' . $booking->uid,
            'SEQUENCE:0',
            'DTSTAMP:' . $fmt(now()),
            'DTSTART:' . $fmt($booking->starts_at),
            'DTEND:' . $fmt($booking->ends_at),
            'SUMMARY:' . self::esc($title),
            'DESCRIPTION:' . self::esc($desc),
            'STATUS:CONFIRMED',
            'ORGANIZER;CN=' . self::esc($organizerName) . ':mailto:' . $organizerEmail,
        ];
        if ($booking->meeting_link) {
            $lines[] = 'LOCATION:' . self::esc($booking->meeting_link);
            $lines[] = 'URL:' . $booking->meeting_link;
        }
        foreach ($attendees as $name => $email) {
            $lines[] = 'ATTENDEE;CN=' . self::esc(is_string($name) ? $name : $email) . ';ROLE=REQ-PARTICIPANT;PARTSTAT=ACCEPTED;RSVP=FALSE:mailto:' . $email;
        }
        $lines[] = 'BEGIN:VALARM';
        $lines[] = 'TRIGGER:-PT15M';
        $lines[] = 'ACTION:DISPLAY';
        $lines[] = 'DESCRIPTION:Reminder';
        $lines[] = 'END:VALARM';
        $lines[] = 'END:VEVENT';
        $lines[] = 'END:VCALENDAR';

        return self::fold($lines);
    }

    /** A subscribable feed of approved bookings (read-only, refreshed by the calendar app). */
    public static function feed(iterable $bookings, string $calendarName): string
    {
        $fmt = fn ($d) => $d->copy()->utc()->format('Ymd\THis\Z');
        $lines = ['BEGIN:VCALENDAR', 'VERSION:2.0', 'PRODID:-//Forward Edge Consulting//Bookings//EN', 'CALSCALE:GREGORIAN', 'METHOD:PUBLISH',
            'X-WR-CALNAME:' . self::esc($calendarName), 'REFRESH-INTERVAL;VALUE=DURATION:PT1H', 'X-PUBLISHED-TTL:PT1H'];

        foreach ($bookings as $b) {
            $lines = array_merge($lines, [
                'BEGIN:VEVENT',
                'UID:' . $b->uid,
                'DTSTAMP:' . $fmt($b->updated_at ?? now()),
                'DTSTART:' . $fmt($b->starts_at),
                'DTEND:' . $fmt($b->ends_at),
                'SUMMARY:' . self::esc('Discovery call: ' . ($b->name ?: $b->email)),
                'DESCRIPTION:' . self::esc(trim(($b->email ? 'Email: ' . $b->email . "\n" : '') . ($b->meeting_link ? 'Join: ' . $b->meeting_link : ''))),
                'STATUS:CONFIRMED',
                'END:VEVENT',
            ]);
        }
        $lines[] = 'END:VCALENDAR';

        return self::fold($lines);
    }

    protected static function esc(string $text): string
    {
        return str_replace(["\\", "\n", ',', ';'], ['\\\\', '\\n', '\\,', '\;'], str_replace("\r", '', $text));
    }

    /** Lines over 75 bytes must be folded, and every line ends with CRLF. */
    protected static function fold(array $lines): string
    {
        $out = '';
        foreach ($lines as $line) {
            while (strlen($line) > 74) {
                $out .= substr($line, 0, 74) . "\r\n ";
                $line = substr($line, 74);
            }
            $out .= $line . "\r\n";
        }

        return $out;
    }
}
