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
        // Generate the main sitemap
        $this->createMainSitemap();

        // Generate the Daisycon sitemap
        $this->createDaisyconSitemap();

        // Generate the TradeTracker sitemap
        $this->createTradeTrackerSitemap();
    }

    private function createMainSitemap()
    {
        // Create a new sitemap instance
        $sitemap = Sitemap::create();

        // Add static URLs
        $sitemap->add(Url::create('/')->setPriority(1.0)->setChangeFrequency('daily'));
        $sitemap->add(Url::create('/cruisemaatschappijen')->setPriority(0.8)->setChangeFrequency('weekly'));
        $sitemap->add(Url::create('/partners')->setPriority(0.8)->setChangeFrequency('monthly'));
        $sitemap->add(Url::create('/reisadviezen')->setPriority(0.8)->setChangeFrequency('monthly'));

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

        $this->info('Main sitemap generated successfully!');
    }

    private function createDaisyconSitemap()
    {
        $sitemap = Sitemap::create();

        // Add Daisycon products dynamically
        foreach (\App\Models\AffiliateProductsLoaded::whereHas('merchant', function ($query) {
            $query->where('affiliate_network_code', 'DC');
        })->get() as $product) {
            $sitemap->add(Url::create("/product/{$product->slug}")
            ->setPriority(0.6)
            ->setChangeFrequency('daily'));
        }

        // Save the sitemap to the public directory
        $sitemap->writeToFile(public_path('daisycon_sitemap.xml'));

        $this->info('Daisycon sitemap generated successfully!');
    }

    private function createTradeTrackerSitemap()
    {
        $sitemap = Sitemap::create();

        // Add TradeTracker products dynamically
        foreach (\App\Models\AffiliateProductsLoaded::whereHas('merchant', function ($query) {
            $query->where('affiliate_network_code', 'TT');
        })->get() as $product) {
            $sitemap->add(Url::create("/product/{$product->slug}")
            ->setPriority(0.6)
            ->setChangeFrequency('daily'));
        }

        // Save the sitemap to the public directory
        $sitemap->writeToFile(public_path('tradetracker_sitemap.xml'));

        $this->info('TradeTracker sitemap generated successfully!');
    }
}