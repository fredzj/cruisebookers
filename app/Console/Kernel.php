protected function schedule(Schedule $schedule)
{
    $schedule->command('sitemap:generate')->dailyAt('02:00');
}