<?php

namespace App\Http\Controllers;

use App\Services\BookingService;

class BookingAvailabilityController extends Controller
{
    public function __invoke(BookingService $bookings)
    {
        return response()->json($bookings->availability())->header('Cache-Control', 'no-store');
    }
}
