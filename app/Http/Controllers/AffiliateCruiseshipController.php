<?php
namespace App\Http\Controllers;

use App\Models\AffiliateCruiseline;
use App\Models\AffiliateCruiseship;
use Illuminate\Http\Request;

class AffiliateCruiseshipController extends Controller
{
    public function show($cruiselineSlug, $shipSlug)
    {
        $cruiseline = AffiliateCruiseline::where('slug', $cruiselineSlug)
                        ->nonBlocked()
                        ->firstOrFail();

        $ship = AffiliateCruiseship::where('slug', $shipSlug)
                  ->where('cruiseline_id', $cruiseline->id)
                  ->firstOrFail();

        return view('cruiseship-detail', [
            'ship'      => $ship,
            'cruiseline'=> $cruiseline,
        ]);
    }
}