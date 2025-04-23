<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AffiliateCruiseline;

class HomeController extends Controller
{
    public function index()
    {
        // Fetch all cruiselines with their name, slug, and logo
        $cruiselines = AffiliateCruiseline::nonBlocked()
            ->select('name', 'slug', 'url_logo')
            ->orderBy('name', 'asc')
            ->get();

        return view('home', compact('cruiselines'));
    }
}