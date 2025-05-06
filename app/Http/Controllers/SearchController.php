<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Class SearchController
 * 
 * Handles search functionality, including filtering, sorting, and retrieving
 * products and facets for the search page.
 */
class SearchController extends Controller
{
    /**
     * Display the search results based on filters and sorting.
     *
     * @param \Illuminate\Http\Request $request The HTTP request containing filters and sorting options.
     * @return \Illuminate\View\View The search results view with products and facets.
     */
    public function index(Request $request)
    {
        $query = DB::table('affiliate_products_loaded_searchpage');

        // Apply search term filter
        if ($request->has('search')) {
            $searchTerms = preg_split('/\s+/', $request->get('search')); // Split input by spaces
            $query->where(function ($q) use ($searchTerms) {
                foreach ($searchTerms as $term) {
                    $q->where(function ($subQuery) use ($term) {
                        $subQuery->where('merchant_name', 'LIKE', "%$term%")
                            ->orWhere('name', 'LIKE', "%$term%")
                            ->orWhere('cruiseline_category', 'LIKE', "%$term%")
                            ->orWhere('cruiseline_name', 'LIKE', "%$term%")
                            ->orWhere('cruiseship_name', 'LIKE', "%$term%")
                            ->orWhere('destination_continent_name', 'LIKE', "%$term%")
                            ->orWhere('destination_country_name', 'LIKE', "%$term%")
                            ->orWhere('destination_sailingarea_name', 'LIKE', "%$term%")
                            ->orWhere('destination_region_name', 'LIKE', "%$term%")
                            ->orWhere('destination_province_name', 'LIKE', "%$term%")
                            ->orWhere('destination_city_name', 'LIKE', "%$term%");
                    });
                }
            });
        }

        // Default sorting
        $sortBy = $request->get('sort', 'price_asc');

        // Sorting logic
        $orderBy = match ($sortBy) {
            'duration_asc' => ['offer_duration_days', 'asc'],
            'duration_desc' => ['offer_duration_days', 'desc'],
            'name_asc' => ['name', 'asc'],
            'name_desc' => ['name', 'desc'],
            'price_asc' => ['price', 'asc'],
            'price_desc' => ['price', 'desc'],
            default => ['price', 'asc'],
        };

        // Apply filters
        if ($request->has('continent')) {
            $query->where('destination_continent_name', $request->get('continent'));
        }
        if ($request->has('country')) {
            $query->where('destination_country_name', $request->get('country'));
        }
        if ($request->has('sailing_area')) {
            $query->where('destination_sailingarea_name', $request->get('sailing_area'));
        }
        if ($request->has('cruiseline')) {
            $query->whereIn('cruiseline_name', $request->get('cruiseline'));
        }
        if ($request->has('cruiseship')) {
            $query->whereIn('cruiseship_name', $request->get('cruiseship'));
        }
        if ($request->has('cruiseline_category')) {
            $query->where('cruiseline_category', $request->get('cruiseline_category'));
        }
        if ($request->has('departure_year')) {
            $query->whereYear('offer_departure_date', $request->get('departure_year'));
        }
        if ($request->has('departure_month')) {
            $query->whereMonth('offer_departure_date', $request->get('departure_month'));
        }
        if ($request->has('departure_month')) {
            $query->whereMonth('offer_departure_date', $request->get('departure_month'));
        }
        if ($request->has('holidaytype_is_rivercruise_danube')) {
            $query->where('holidaytype_is_rivercruise_danube', $request->get('holidaytype_is_rivercruise_danube'));
        }
        if ($request->has('holidaytype_is_rivercruise_douro')) {
            $query->where('holidaytype_is_rivercruise_douro', $request->get('holidaytype_is_rivercruise_douro'));
        }
        if ($request->has('holidaytype_is_rivercruise_moselle')) {
            $query->where('holidaytype_is_rivercruise_moselle', $request->get('holidaytype_is_rivercruise_moselle'));
        }
        if ($request->has('holidaytype_is_rivercruise_nile')) {
            $query->where('holidaytype_is_rivercruise_nile', $request->get('holidaytype_is_rivercruise_nile'));
        }
        if ($request->has('holidaytype_is_rivercruise_rhine')) {
            $query->where('holidaytype_is_rivercruise_rhine', $request->get('holidaytype_is_rivercruise_rhine'));
        }
        if ($request->has('holidaytype_is_rivercruise_rhone')) {
            $query->where('holidaytype_is_rivercruise_rhone', $request->get('holidaytype_is_rivercruise_rhone'));
        }
        if ($request->has('holidaytype_is_rivercruise_seine')) {
            $query->where('holidaytype_is_rivercruise_seine', $request->get('holidaytype_is_rivercruise_seine'));
        }
        if ($request->has('holidaytype_is_rivercruise_volga')) {
            $query->where('holidaytype_is_rivercruise_volga', $request->get('holidaytype_is_rivercruise_volga'));
        }
        if ($request->has('holidaytype_is_seacruise_bluecruise')) {
            $query->where('holidaytype_is_seacruise_bluecruise', $request->get('holidaytype_is_seacruise_bluecruise'));
        }
        if ($request->has('holidaytype_is_seacruise_caribbean')) {
            $query->where('holidaytype_is_seacruise_caribbean', $request->get('holidaytype_is_seacruise_caribbean'));
        }
        if ($request->has('holidaytype_is_seacruise_hurtigruten')) {
            $query->where('holidaytype_is_seacruise_hurtigruten', $request->get('holidaytype_is_seacruise_hurtigruten'));
        }
        if ($request->has('holidaytype_is_seacruise_mediterranean')) {
            $query->where('holidaytype_is_seacruise_mediterranean', $request->get('holidaytype_is_seacruise_mediterranean'));
        }
        if ($request->has('holidaytype_is_seacruise_sailing')) {
            $query->where('holidaytype_is_seacruise_sailing', $request->get('holidaytype_is_seacruise_sailing'));
        }
        if ($request->has('holidaytype_is_seacruise_world')) {
            $query->where('holidaytype_is_seacruise_world', $request->get('holidaytype_is_seacruise_world'));
        }
        if ($request->has('merchant')) {
            $query->where('merchant_name', $request->get('merchant'));
        }
        if ($request->has('duration')) {
            $durations = $request->input('duration');
            $query->where(function ($q) use ($durations) {
                foreach ($durations as $duration) {
                    if ($duration === '1-3') {
                        $q->orWhereBetween('offer_duration_days', [1, 3]);
                    } elseif ($duration === '4-7') {
                        $q->orWhereBetween('offer_duration_days', [4, 7]);
                    } elseif ($duration === '8-14') {
                        $q->orWhereBetween('offer_duration_days', [8, 14]);
                    } elseif ($duration === '15+') {
                        $q->orWhere('offer_duration_days', '>=', 15);
                    }
                }
            });
        }
    
        // Fetch results with sorting and pagination
        $products = $query->orderBy($orderBy[0], $orderBy[1])->paginate(12);

        // Fetch distinct values for facets
        $facets = [
            'cruiseline_categories' => DB::table('affiliate_products_loaded_searchpage')
                ->select('cruiseline_category')
                ->whereNotNull('cruiseline_category') // Ignore null values
                ->distinct()
                ->orderBy('cruiseline_category', 'desc')
                ->pluck('cruiseline_category'),

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

        $facets['sailingAreas'] = DB::table('affiliate_products_loaded_searchpage')
            ->select('destination_sailingarea_name')
            ->whereNotNull('destination_sailingarea_name') // Ensure sailing_area is not null
            ->where('destination_sailingarea_name', '!=', '') // Ensure sailing_area is not empty
            ->distinct()
            ->orderBy('destination_sailingarea_name', 'asc')
            ->pluck('destination_sailingarea_name');

        $facets['cruiselines'] = DB::table('affiliate_products_loaded_searchpage')
            ->select('cruiseline_name')
            ->whereNotNull('cruiseline_name')
            ->distinct()
            ->orderBy('cruiseline_name', 'asc')
            ->pluck('cruiseline_name');
    
        $facets['cruiseshipsByCruiseline'] = DB::table('affiliate_products_loaded_searchpage')
            ->select('cruiseline_name', 'cruiseship_name')
            ->whereNotNull('cruiseline_name')
            ->whereNotNull('cruiseship_name') // Ensure cruiseship_name is not null
            ->where('cruiseship_name', '!=', '') // Ensure cruiseship_name is not empty
            ->distinct()
            ->orderBy('cruiseline_name', 'asc')
            ->orderBy('cruiseship_name', 'asc')
            ->get()
            ->groupBy('cruiseline_name');
            
        $facets['departureYears'] = DB::table('affiliate_products_loaded_searchpage')
            ->select(DB::raw('YEAR(offer_departure_date) as year'))
            ->whereNotNull('offer_departure_date')
            ->where('offer_departure_date', '>=', now()) // Include only future dates
            ->distinct()
            ->orderBy('year', 'asc')
            ->pluck('year');
        
        $facets['monthsByYear'] = DB::table('affiliate_products_loaded_searchpage')
            ->select(DB::raw('YEAR(offer_departure_date) as year'), DB::raw('MONTH(offer_departure_date) as month'))
            ->whereNotNull('offer_departure_date')
            ->where('offer_departure_date', '>=', now()) // Include only future dates
            ->distinct()
            ->orderBy('year', 'asc')
            ->orderBy('month', 'asc')
            ->get()
            ->groupBy('year');     

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
            'searchTerm' => $request->get('search', ''),
        ]);
    }
}