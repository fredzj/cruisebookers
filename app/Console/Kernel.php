protected function schedule(Schedule $schedule)
{
    $schedule->command('sitemap:generate')->dailyAt('08:42');
}