<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\VendorRijksoverheidTraveladvice;
use Illuminate\Support\Facades\DB;

/**
 * Class VendorRijksoverheidTraveladviceController
 * 
 * Handles operations related to travel advice, including listing,
 * retrieving details, and filtering by countries with products.
 */
class VendorRijksoverheidTraveladviceController extends Controller
{
    /**
     * Display a listing of travel advice for countries with products.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        // Get the list of countries for which products exist
        $countriesWithProducts = DB::table('affiliate_products_loaded_searchpage')->pluck('destination_country_name');

        $traveladvices = DB::table('vendor_rijksoverheid_nl_traveladvice')
            ->join('vendor_rijksoverheid_nl_traveladvice_files', 'vendor_rijksoverheid_nl_traveladvice.id', '=', 'vendor_rijksoverheid_nl_traveladvice_files.id')
            ->select('vendor_rijksoverheid_nl_traveladvice.id', 'vendor_rijksoverheid_nl_traveladvice.location', 'vendor_rijksoverheid_nl_traveladvice_files.fileurl')
            ->where('vendor_rijksoverheid_nl_traveladvice_files.maptype', 'legend') // Filter for maptype 'legend'
            ->whereNotNull('vendor_rijksoverheid_nl_traveladvice_files.fileurl') // Ensure there is a map URL
            ->whereIn('vendor_rijksoverheid_nl_traveladvice.location', $countriesWithProducts) // Filter by countries with products
            ->orderBy('vendor_rijksoverheid_nl_traveladvice.location', 'asc') // Sort by country name
            ->get();

        return view('traveladvices', compact('traveladvices'));
    }

    /**
     * Retrieve all travel advice for countries with products.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function all()
    {
        // Get the list of countries for which products exist
        $countriesWithProducts = DB::table('affiliate_products_loaded_searchpage')->pluck('destination_country_name');

        // Return only travel advice for countries with products
        return VendorRijksoverheidTraveladvice::select('id', 'location')
            ->whereIn('location', $countriesWithProducts) // Filter by countries with products
            ->orderBy('id', 'asc') // Sort by id in ascending order
            ->get();
    }
    
    /**
     * Display the details of travel advice for a specific country.
     *
     * @param string $id The ID or location of the travel advice.
     * @return \Illuminate\View\View
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function show($id)
    {
        // Fetch travel advice details for the selected country
        $traveladvice = DB::table('vendor_rijksoverheid_nl_traveladvice')
            ->join('vendor_rijksoverheid_nl_traveladvice_files', 'vendor_rijksoverheid_nl_traveladvice.id', '=', 'vendor_rijksoverheid_nl_traveladvice_files.id')
            ->select(
                'vendor_rijksoverheid_nl_traveladvice.authorities',
                'vendor_rijksoverheid_nl_traveladvice.introduction',
                'vendor_rijksoverheid_nl_traveladvice.location',
                'vendor_rijksoverheid_nl_traveladvice.modificationdate',
                'vendor_rijksoverheid_nl_traveladvice.modifications',
                'vendor_rijksoverheid_nl_traveladvice.title',
                'vendor_rijksoverheid_nl_traveladvice_files.fileurl'
            )
            ->where('vendor_rijksoverheid_nl_traveladvice.location', $id)
            ->where('vendor_rijksoverheid_nl_traveladvice_files.maptype', 'legend')
            ->firstOrFail();

        // Fetch content blocks for the selected travel advice
        $contentblocks = DB::table('vendor_rijksoverheid_nl_traveladvice_contentblocks')
            ->where('id', $id)
            ->orderBy('sequence', 'asc')
            ->get();

        return view('traveladvice-detail', compact('traveladvice', 'contentblocks'));
    }
}