<?php

namespace Tests\Feature;

use App\Mail\BookingMail;
use App\Models\Booking;
use App\Models\BookingSettings;
use App\Models\Form;
use App\Models\User;
use App\Services\BookingService;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class BookingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // A Monday morning in Lagos, so the next weekday is predictable.
        CarbonImmutable::setTestNow(CarbonImmutable::parse('2026-11-02 06:00:00', 'UTC'));
        \Illuminate\Support\Carbon::setTestNow(\Illuminate\Support\Carbon::parse('2026-11-02 06:00:00', 'UTC'));
    }

    protected function tearDown(): void
    {
        CarbonImmutable::setTestNow();
        \Illuminate\Support\Carbon::setTestNow();
        parent::tearDown();
    }

    /** Tuesday 3 Nov 2026, 10:45 WAT = 09:45 UTC */
    private function slot(string $local = '2026-11-03 10:45'): string
    {
        return CarbonImmutable::parse($local, 'Africa/Lagos')->utc()->format('Y-m-d\TH:i:s\Z');
    }

    private function request(string $slot, string $email = 'ada@gmail.com')
    {
        return $this->postJson('/f/security-discovery-call', [
            'answers' => ['slot' => $slot, 'name' => 'Ada Obi', 'email' => $email, 'company' => 'Obi Ltd', 'size' => '11–50 employees'],
            'consent' => '1',
        ]);
    }

    public function test_slots_follow_the_working_hours_buffer_and_notice(): void
    {
        $av = app(BookingService::class)->availability();
        $tue = collect($av['days'])->firstWhere('date', '2026-11-03');

        // 30 minute calls plus a 15 minute gap, 10:00 to 14:00
        $this->assertSame(['10:00 AM', '10:45 AM', '11:30 AM', '12:15 PM', '1:00 PM'], array_column($tue['slots'], 'label'));
        $this->assertSame('WAT', $av['zone_label']);
        // weekends are not offered
        $this->assertNull(collect($av['days'])->firstWhere('date', '2026-11-07'));
        // today (Monday, 07:00 Lagos) is inside the 12 hour notice window for the morning slots
        $mon = collect($av['days'])->firstWhere('date', '2026-11-02');
        $this->assertNull($mon);
    }

    public function test_availability_endpoint_is_public_json(): void
    {
        $this->getJson('/booking/availability')->assertOk()->assertJsonStructure(['timezone', 'zone_label', 'days' => [['date', 'label', 'slots']]]);
    }

    public function test_a_request_saves_the_lead_holds_the_slot_and_emails_everyone(): void
    {
        Mail::fake();
        $this->request($this->slot())->assertOk()->assertJsonPath('booking.when', 'Tue 3 Nov 2026, 10:45 AM WAT');

        $b = Booking::firstOrFail();
        $this->assertSame('pending', $b->status);
        $this->assertDatabaseHas('form_submissions', ['email' => 'ada@gmail.com']);
        Mail::assertSent(BookingMail::class, fn ($m) => $m->kind === 'requested' && $m->audience === 'visitor' && $m->hasTo('ada@gmail.com'));
        Mail::assertSent(BookingMail::class, fn ($m) => $m->kind === 'requested' && $m->audience === 'consultant'
            && $m->hasTo('moyorcharles@gmail.com') && $m->hasCc('moyor@forwardedgeconsulting.com'));
    }

    public function test_a_slot_cannot_be_booked_twice_and_nothing_is_saved_for_the_loser(): void
    {
        Mail::fake();
        $this->request($this->slot())->assertOk();
        $this->request($this->slot(), 'chidi@gmail.com')->assertStatus(422)->assertJsonPath('refresh_slots', true);

        $this->assertSame(1, Booking::count());
        $this->assertDatabaseMissing('form_submissions', ['email' => 'chidi@gmail.com']);
        // the neighbouring slot is inside the 15 minute gap only if it overlaps; the next grid slot is still free
        $this->request($this->slot('2026-11-03 11:30'), 'chidi@gmail.com')->assertOk();
    }

    public function test_times_outside_the_grid_or_hours_are_rejected(): void
    {
        $this->request($this->slot('2026-11-03 10:15'))->assertStatus(422);   // not on the grid
        $this->request($this->slot('2026-11-03 15:00'))->assertStatus(422);   // after hours
        $this->request($this->slot('2026-11-07 10:00'))->assertStatus(422);   // Saturday
        $this->request('garbage')->assertStatus(422);
        $this->assertSame(0, Booking::count());
    }

    public function test_the_consultant_can_approve_from_the_email_link(): void
    {
        Mail::fake();
        $this->request($this->slot())->assertOk();
        $b = Booking::firstOrFail();

        $review = URL::temporarySignedRoute('booking.review', now()->addDay(), ['booking' => $b->id]);
        $this->get($review)->assertOk()->assertSee('Ada Obi')->assertSee('Tue 3 Nov 2026, 10:45 AM WAT');
        $this->get(route('booking.review', $b))->assertForbidden();            // unsigned

        $decide = URL::temporarySignedRoute('booking.decide', now()->addDay(), ['booking' => $b->id]);
        $this->post($decide, ['action' => 'approve', 'meeting_link' => 'https://meet.google.com/abc-defg-hij'])->assertRedirect();

        $b->refresh();
        $this->assertSame('approved', $b->status);
        $this->assertSame('email', $b->decided_via);
        Mail::assertSent(BookingMail::class, fn ($m) => $m->kind === 'approved' && $m->audience === 'visitor');
        Mail::assertSent(BookingMail::class, fn ($m) => $m->kind === 'approved' && $m->audience === 'consultant');

        // deciding twice changes nothing
        $this->post($decide, ['action' => 'decline'])->assertRedirect();
        $this->assertSame('approved', $b->fresh()->status);
    }

    public function test_the_approval_email_carries_a_calendar_invite(): void
    {
        $this->request($this->slot())->assertOk();
        $b = Booking::firstOrFail();
        app(BookingService::class)->approve($b, 'https://meet.google.com/abc-defg-hij', null, 'admin');

        $mail = new BookingMail($b->fresh(), 'approved', 'visitor');
        $mail->build();
        $this->assertCount(1, $mail->rawAttachments);
        $ics = $mail->rawAttachments[0]['data'];
        $this->assertStringContainsString('BEGIN:VEVENT', $ics);
        $this->assertStringContainsString('DTSTART:20261103T094500Z', $ics);   // 10:45 WAT in UTC
        $this->assertStringContainsString('meet.google.com', $ics);
        $this->assertStringContainsString('mailto:moyorcharles@gmail.com', $ics);
    }

    public function test_declining_frees_the_slot(): void
    {
        Mail::fake();
        $this->request($this->slot())->assertOk();
        $b = Booking::firstOrFail();

        app(BookingService::class)->decline($b, 'Away that day.', 'admin');

        $this->assertSame('declined', $b->fresh()->status);
        $this->assertNull($b->fresh()->active_slot);
        Mail::assertSent(BookingMail::class, fn ($m) => $m->kind === 'declined');
        $this->request($this->slot(), 'chidi@gmail.com')->assertOk();
    }

    public function test_admin_can_list_approve_and_view_the_calendar(): void
    {
        Mail::fake();
        $admin = User::factory()->create(['role' => 'admin']);
        $this->request($this->slot())->assertOk();
        $b = Booking::firstOrFail();

        $this->actingAs($admin)->get(route('admin.bookings.index'))->assertOk()->assertSee('Ada Obi');
        $this->actingAs($admin)->get(route('admin.bookings.index', ['view' => 'calendar', 'month' => '2026-11']))->assertOk()->assertSee('Ada Obi');
        $this->actingAs($admin)->get(route('admin.bookings.show', $b))->assertOk();
        $this->actingAs($admin)->post(route('admin.bookings.approve', $b), ['meeting_link' => 'https://zoom.us/j/123'])->assertSessionHas('success');
        $this->assertSame('approved', $b->fresh()->status);
        $this->assertSame('admin', $b->fresh()->decided_via);
    }

    public function test_admin_settings_change_the_slots(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin)->put(route('admin.bookings.settings.update'), [
            'consultant_name' => 'Moyosore Kukoyi', 'consultant_email' => 'moyorcharles@gmail.com', 'cc_emails' => 'moyor@forwardedgeconsulting.com',
            'timezone' => 'Africa/Lagos', 'days' => [2], 'start_time' => '09:00', 'end_time' => '10:30',
            'slot_minutes' => 30, 'buffer_minutes' => 15, 'min_notice_hours' => 12, 'max_days_ahead' => 30, 'blocked_dates' => '2026-11-10',
        ])->assertSessionHasNoErrors();

        $days = collect(app(BookingService::class)->availability()['days']);
        $this->assertSame(['9:00 AM', '9:45 AM'], array_column($days->firstWhere('date', '2026-11-03')['slots'], 'label'));
        $this->assertNull($days->firstWhere('date', '2026-11-10'));     // blocked
        $this->assertNull($days->firstWhere('date', '2026-11-04'));     // Wednesday is off
    }

    public function test_calendar_feed_lists_only_approved_calls_and_needs_the_token(): void
    {
        Mail::fake();
        $this->request($this->slot())->assertOk();
        $this->request($this->slot('2026-11-03 11:30'), 'chidi@gmail.com')->assertOk();
        $first = Booking::orderBy('starts_at')->first();
        app(BookingService::class)->approve($first, null, null, 'admin');

        $token = BookingSettings::current()->get('feed_token');
        $res = $this->get('/booking/calendar/' . $token . '.ics')->assertOk();
        $this->assertStringContainsString('text/calendar', $res->headers->get('Content-Type'));
        $this->assertSame(1, substr_count($res->getContent(), 'BEGIN:VEVENT'));
        $this->get('/booking/calendar/wrongtoken.ics')->assertNotFound();
    }
}
