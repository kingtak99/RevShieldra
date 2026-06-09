<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array<int, class-string>
     */
    protected $commands = [
        // Keep route-based console commands in routes/console.php as well.
        \App\Console\Commands\ChatbotAutoLearnCommand::class,
    ];

    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        $schedule->command('trial:send-reminders')->daily();
        $schedule->command('chatbot:auto-learn')->dailyAt('02:00')->withoutOverlapping();
        $schedule->command('chatbot:auto-learn')->dailyAt('14:00')->withoutOverlapping();
    }
}
