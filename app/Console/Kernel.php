protected function schedule(Schedule $schedule)
{
    $schedule->command('sitemap:generate')->dailyAt('02:00');
    $schedule->command('transform:daisycon')->dailyAt('01:00');
}