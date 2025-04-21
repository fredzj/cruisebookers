<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AffiliateCruiseline;

class AffiliateCruiselineController extends Controller
{
    public function index()
    {
        $cruiselines = AffiliateCruiseline::select('name', 'slogan', 'slug', 'url_logo')
            ->where('is_blocked', 0) // Select only non-blocked cruiselines
            ->orderBy('name', 'asc') // Sort by name in ascending order
            ->get();

        return view('cruiselines', compact('cruiselines'));
    }

    public function all()
    {
        return AffiliateCruiseline::select('slug', 'name')
            ->where('is_blocked', 0)
            ->orderBy('name', 'asc') // Sort by name in ascending order
            ->get();
    }
    public function show($slug)
    {
        $cruiseline = AffiliateCruiseline::where('slug', $slug)->firstOrFail();

        return view('cruiseline-detail', compact('cruiseline'));
    }
}