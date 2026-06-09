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
        \App\Console\Commands\ChatbotLearnFromUnhandledCommand::class,
        \App\Console\Commands\ChatbotSelfLearningCommand::class,
    ];

    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        $schedule->command('trial:send-reminders')->daily();
        
        // تعلم من أخطاء المستخدمين (Unhandled Queries)
        $schedule->command('chatbot:learn-from-unhandled')->dailyAt('02:00')->withoutOverlapping();
        $schedule->command('chatbot:learn-from-unhandled')->dailyAt('14:00')->withoutOverlapping();
        
        // التعلم الذاتي المستقل (Self-Learning without user errors)
        $schedule->command('chatbot:self-learning')->dailyAt('03:00')->withoutOverlapping();
        $schedule->command('chatbot:self-learning')->dailyAt('15:00')->withoutOverlapping();
    }
}
