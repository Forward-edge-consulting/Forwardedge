<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Services\BookingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;

/** The page behind the Approve / Decline button in the consultant's email. Reached by a signed link, no login. */
class BookingReviewController extends Controller
{
    public function show(Booking $booking, BookingService $bookings)
    {
        return view('user.pages.booking_review', [
            'booking' => $booking,
            'when' => $bookings->label($booking->starts_at),
            'decideUrl' => URL::temporarySignedRoute('booking.decide', now()->addDays(14), ['booking' => $booking->id]),
            'settings' => $bookings->settings(),
        ]);
    }

    public function decide(Request $request, Booking $booking, BookingService $bookings)
    {
        $data = $request->validate([
            'action' => 'required|in:approve,decline',
            'meeting_link' => 'nullable|url|max:500',
            'note' => 'nullable|string|max:1000',
            'reason' => 'nullable|string|max:1000',
        ]);

        $done = $data['action'] === 'approve'
            ? $bookings->approve($booking, $data['meeting_link'] ?? null, $data['note'] ?? null, 'email')
            : $bookings->decline($booking, $data['reason'] ?? null, 'email');

        return redirect(URL::temporarySignedRoute('booking.review', now()->addDays(14), ['booking' => $booking->id]))
            ->with($done ? 'decided' : 'already', $done ? $data['action'] : $booking->status);
    }
}
