<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProductController extends Controller
{
    /**
     * Display the product details.
     *
     * @param string $slug The slug of the product.
     * @return \Illuminate\View\View
     */
    public function show($slug)
    {
        // Fetch the product by slug
        $product = DB::table('affiliate_products_loaded_searchpage')
            ->where('slug', $slug)
            ->first();

        // Extract the merchant slug from the product slug (before the first dash)
        $merchantSlug = explode('-', $slug)[0];
        // Look up the merchant name using the merchant slug
        $merchant = DB::table('affiliate_networks_merchants')
            ->where('slug', $merchantSlug)
            ->first();
        $product->merchant_name = $merchant->name ?? null;

        if (!$product) {
            if ($merchant) {
                // Redirect to the search page with the merchant pre-selected
                return redirect()->route('search', ['merchant' => [$merchant->name]]);
            }
            // If no merchant is found, redirect to the search page without filters
            return redirect()->route('search');
        }
        
        // Fetch additional product data from affiliate_products_loaded_productpage
        $productPageData = DB::table('affiliate_products_loaded_productpage')
            ->where('slug', $slug)
            ->first();

        if ($productPageData) {
            $product->additional_data = $productPageData;

            // Parse gallery images if stored as a pipe-separated string
            if (isset($productPageData->images)) {
                $product->additional_data->images = explode('|', $productPageData->images);
            }
        }

        return view('product.show', compact('product'));
    }
}