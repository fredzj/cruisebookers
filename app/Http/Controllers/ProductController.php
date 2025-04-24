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

        if (!$product) {
            abort(404, 'Product not found');
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