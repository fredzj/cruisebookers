<?php
namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Blog;
use App\Models\AffiliateCruiseline;
use App\Models\AffiliateNetworkMerchant;
use App\Http\Controllers\VendorRijksoverheidTraveladviceController;

class SitemapController extends Controller
{
    public function index()
    {
        $blogs = Blog::orderBy('timestamp', 'desc')->get();

        $cruiselines = AffiliateCruiseline::nonBlocked()
            ->with(['cruiseships' => function($q){
                $q->nonBlocked()
                  ->whereNotNull('paragraph_destinations')
                  ->where('paragraph_destinations','<>','')
                  ->orderBy('name');
            }])
            ->orderBy('name')
            ->get();

        $merchants = AffiliateNetworkMerchant::nonBlocked()
            ->orderBy('name')
            ->get();

        $traveladvices = app(VendorRijksoverheidTraveladviceController::class)->all();

        return view('sitemap', compact('blogs','cruiselines','merchants','traveladvices'));
    }
}