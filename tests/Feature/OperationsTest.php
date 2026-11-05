<?php

namespace Tests\Feature;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Facades\Schedule as ScheduleFacade;
use Tests\TestCase;

class OperationsTest extends TestCase
{
    public function test_scheduler_does_not_resurrect_old_mail_or_start_workers_by_default(): void
    {
        $schedule = new Schedule;
        ScheduleFacade::swap($schedule);
        config(['operations.run_queue_from_scheduler' => false]);
        require base_path('routes/console.php');
        $this->assertCount(0, $schedule->events());
    }

    public function test_opt_in_scheduler_worker_is_bounded_and_cannot_overlap(): void
    {
        $schedule = new Schedule;
        ScheduleFacade::swap($schedule);
        config(['operations.run_queue_from_scheduler' => true]);
        require base_path('routes/console.php');
        $this->assertCount(1, $schedule->events());
        $event = $schedule->events()[0];
        foreach (['--stop-when-empty', '--max-time=45', '--max-jobs=10', '--timeout=30', '--tries=3'] as $flag) {
            $this->assertStringContainsString($flag, $event->command);
        }
        $this->assertTrue($event->withoutOverlapping);
        $this->assertSame(5, $event->expiresAt);
    }

    public function test_smtp_has_a_finite_timeout(): void
    {
        $this->assertGreaterThan(0, config('mail.mailers.smtp.timeout'));
        $this->assertLessThan(30, config('mail.mailers.smtp.timeout'));
    }
}
