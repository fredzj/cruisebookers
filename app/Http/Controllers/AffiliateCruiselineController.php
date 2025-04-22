<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AffiliateCruiseline;

/**
 * Class AffiliateCruiselineController
 * 
 * Handles operations related to affiliate cruiselines, including listing,
 * retrieving details, and providing data for external use.
 */
class AffiliateCruiselineController extends Controller
{
    /**
     * Display a listing of all non-blocked cruiselines.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $cruiselines = AffiliateCruiseline::select('name', 'slogan', 'slug', 'url_logo')
            ->where('is_blocked', 0) // Select only non-blocked cruiselines
            ->orderBy('name', 'asc') // Sort by name in ascending order
            ->get();

        return view('cruiselines', compact('cruiselines'));
    }

    /**
     * Retrieve all non-blocked cruiselines for external use.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function all()
    {
        return AffiliateCruiseline::select('slug', 'name')
            ->where('is_blocked', 0)
            ->orderBy('name', 'asc') // Sort by name in ascending order
            ->get();
    }
    
    /**
     * Display the details of a specific cruiseline based on its slug.
     *
     * @param string $slug The slug of the cruiseline.
     * @return \Illuminate\View\View
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function show($slug)
    {
        $cruiseline = AffiliateCruiseline::where('slug', $slug)->firstOrFail();

        return view('cruiseline-detail', compact('cruiseline'));
    }
}