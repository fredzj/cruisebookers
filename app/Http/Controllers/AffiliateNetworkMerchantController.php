<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AffiliateNetworkMerchant;

class AffiliateNetworkMerchantController extends Controller
{
    public function index()
    {
        $merchants = AffiliateNetworkMerchant::select('name', 'slug', 'url_merchant_logo')
            ->where('is_blocked', 0) // Select only merchants that are not blocked
            ->orderBy('name', 'asc') // Sort by name in ascending order
            ->get();

            return view('partners', compact('merchants'));
    }

    public function all()
    {
        return AffiliateNetworkMerchant::select('slug', 'name')
            ->where('is_blocked', 0) // Select only merchants that are not blocked
            ->orderBy('name', 'asc') // Sort by name in ascending order
            ->get();
    }
    
    public function show($slug)
    {
        $merchant = AffiliateNetworkMerchant::where('slug', $slug)->firstOrFail();

        return view('partner-detail', compact('merchant'));
    }
}
