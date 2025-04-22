<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\AffiliateNetworkMerchant;

/**
 * Class AffiliateNetworkMerchantController
 * 
 * Handles operations related to affiliate network merchants, including listing,
 * retrieving details, and providing data for external use.
 */
class AffiliateNetworkMerchantController extends Controller
{
    /**
     * Display a listing of all non-blocked merchants.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $merchants = AffiliateNetworkMerchant::select('name', 'slug', 'url_merchant_logo')
            ->where('is_blocked', 0) // Select only merchants that are not blocked
            ->orderBy('name', 'asc') // Sort by name in ascending order
            ->get();

            return view('partners', compact('merchants'));
    }

    /**
     * Retrieve all non-blocked merchants for external use.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function all()
    {
        return AffiliateNetworkMerchant::select('slug', 'name')
            ->where('is_blocked', 0) // Select only merchants that are not blocked
            ->orderBy('name', 'asc') // Sort by name in ascending order
            ->get();
    }
    
    /**
     * Display the details of a specific merchant based on its slug.
     *
     * @param string $slug The slug of the merchant.
     * @return \Illuminate\View\View
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function show($slug)
    {
        $merchant = AffiliateNetworkMerchant::where('slug', $slug)->firstOrFail();

        // Check if the merchant has products
        $merchant->has_products = DB::table('affiliate_products_loaded_searchpage')
            ->where('merchant_name', $merchant->name)
            ->exists();

        return view('partner-detail', compact('merchant'));
    }
}
