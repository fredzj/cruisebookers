<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Spatie\Sitemap\Sitemap;
use Spatie\Sitemap\Tags\Url;

class GenerateSitemap extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sitemap:generate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate the XML sitemap';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $sitemap = Sitemap::create()
            ->add(Url::create('/')->setPriority(1.0)->setChangeFrequency('daily'))
            ->add(Url::create('/cruisemaatschappijen')->setPriority(0.8)->setChangeFrequency('weekly'))
            ->add(Url::create('/partners')->setPriority(0.8)->setChangeFrequency('monthly'))
            ->add(Url::create('/reisadviezen')->setPriority(0.8)->setChangeFrequency('monthly'));

        // Add dynamic URLs (e.g., products, categories)
        foreach (\App\Models\AffiliateCruiseline::query()->nonBlocked()->get() as $cruiseline) {
            $sitemap->add(Url::create("/cruisemaatschappijen/{$cruiseline->slug}")
                ->setPriority(0.7)
                ->setChangeFrequency('weekly'));
        }
        foreach (\App\Models\AffiliateNetworkMerchant::query()->nonBlocked()->get() as $merchant) {
            $sitemap->add(Url::create("/partners/{$merchant->slug}")
                ->setPriority(0.7)
                ->setChangeFrequency('weekly'));
        }
        foreach (\App\Models\VendorRijksoverheidTraveladvice::all() as $traveladvice) {
            $sitemap->add(Url::create("/partners/{$traveladvice->id}")
                ->setPriority(0.7)
                ->setChangeFrequency('weekly'));
        }

        // Save the sitemap to the public directory
        $sitemap->writeToFile(public_path('sitemap.xml'));

        $this->info('Sitemap generated successfully!');
    }
}