<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SearchController extends Controller
{
    public function index(Request $request)
    {
        // Default sorting
        $sortBy = $request->get('sort', 'price_asc');

        // Sorting logic
        $orderBy = match ($sortBy) {
            'price_asc' => ['price', 'asc'],
            'price_desc' => ['price', 'desc'],
            'name_asc' => ['name', 'asc'],
            'name_desc' => ['name', 'desc'],
            default => ['price', 'asc'],
        };

        // Start building the query
        $query = DB::table('affiliate_products_loaded_searchpage');

        // Apply filters
        if ($request->has('continent')) {
            $query->where('destination_continent_name', $request->get('continent'));
        }
        if ($request->has('country')) {
            $query->where('destination_country_name', $request->get('country'));
        }
        if ($request->has('cruiseline')) {
            $query->whereIn('cruiseline_name', $request->get('cruiseline'));
        }
        if ($request->has('cruiseship')) {
            $query->whereIn('cruiseship_name', $request->get('cruiseship'));
        }
        if ($request->has('merchant')) {
            $query->where('merchant_name', $request->get('merchant'));
        }

        // Fetch results with sorting and pagination
        $products = $query->orderBy($orderBy[0], $orderBy[1])->paginate(12);

        // Fetch distinct values for facets
        $facets = [
            'continents' => DB::table('affiliate_products_loaded_searchpage')
                ->select('destination_continent_name')
                ->whereNotNull('destination_continent_name') // Ignore null values
                ->distinct()
                ->orderBy('destination_continent_name', 'asc')
                ->pluck('destination_continent_name'),
        
            'countries' => DB::table('affiliate_products_loaded_searchpage')
                ->select('destination_country_name')
                ->whereNotNull('destination_country_name') // Ignore null values
                ->distinct()
                ->orderBy('destination_country_name', 'asc')
                ->pluck('destination_country_name'),

            'countriesByContinent' => DB::table('affiliate_products_loaded_searchpage')
            ->select('destination_continent_name', 'destination_country_name')
            ->whereNotNull('destination_continent_name')
            ->whereNotNull('destination_country_name')
            ->distinct()
            ->orderBy('destination_continent_name', 'asc')
            ->orderBy('destination_country_name', 'asc')
            ->get()
            ->groupBy('destination_continent_name'),
        
            'merchants' => DB::table('affiliate_products_loaded_searchpage')
                ->select('merchant_name')
                ->whereNotNull('merchant_name') // Ignore null values
                ->distinct()
                ->orderBy('merchant_name', 'asc')
                ->pluck('merchant_name'),
        ];
        
        $facets['cruiselines'] = DB::table('affiliate_products_loaded_searchpage')
        ->select('cruiseline_name')
        ->whereNotNull('cruiseline_name')
        ->distinct()
        ->orderBy('cruiseline_name', 'asc')
        ->pluck('cruiseline_name');
    
        $facets['cruiseshipsByCruiseline'] = DB::table('affiliate_products_loaded_searchpage')
            ->select('cruiseline_name', 'cruiseship_name')
            ->whereNotNull('cruiseline_name')
            ->distinct()
            ->orderBy('cruiseline_name', 'asc')
            ->orderBy('cruiseship_name', 'asc')
            ->get()
            ->groupBy('cruiseline_name');
        
        // Ensure all cruiselines are included, even if they don't have cruiseships
        foreach ($facets['cruiselines'] as $cruiseline) {
            if (!isset($facets['cruiseshipsByCruiseline'][$cruiseline])) {
                $facets['cruiseshipsByCruiseline'][$cruiseline] = collect([]);
            }
        }            

        if ($request->has('continent')) {
            $selectedContinents = $request->get('continent');
        
            // Get all countries within the selected continents
            $countriesInContinents = DB::table('affiliate_products_loaded_searchpage')
                ->select('destination_country_name')
                ->whereIn('destination_continent_name', $selectedContinents)
                ->whereNotNull('destination_country_name')
                ->distinct()
                ->pluck('destination_country_name');
        
            // Apply the filter for countries in the selected continents
            $query->whereIn('destination_country_name', $countriesInContinents);
        }
        
        if ($request->has('country')) {
            $query->whereIn('destination_country_name', $request->get('country'));
        }

        return view('search.index', [
            'products' => $products,
            'sortBy' => $sortBy,
            'facets' => $facets,
            'totalResults' => $products->total(),
        ]);
    }
}