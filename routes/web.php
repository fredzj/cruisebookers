<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AffiliateCruiselineController;
use App\Http\Controllers\AffiliateNetworkMerchantController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\VendorRijksoverheidTraveladviceController;
use App\Http\Controllers\ProductController;

Route::get('/', [HomeController::class, 'index'])->name('home');

Route::get('/cruisemaatschappijen', [AffiliateCruiselineController::class, 'index'])->name('cruiselines');
Route::get('/cruisemaatschappijen/{slug}', [AffiliateCruiselineController::class, 'show'])->name('cruiselines.show');

Route::get('/partners', [AffiliateNetworkMerchantController::class, 'index'])->name('partners');
Route::get('/partners/{slug}', [AffiliateNetworkMerchantController::class, 'show'])->name('partners.show');

Route::get('/reisadviezen', [VendorRijksoverheidTraveladviceController::class, 'index'])->name('traveladvices');
Route::get('/reisadviezen/{id}', [VendorRijksoverheidTraveladviceController::class, 'show'])->name('traveladvices.show');

Route::get('/cookieverklaring', function () {
    return view('cookieverklaring');
})->name('cookieverklaring');

Route::get('/disclaimer', function () {
    return view('disclaimer');
})->name('disclaimer');

Route::get('/privacyverklaring', function () {
    return view('privacyverklaring');
})->name('privacyverklaring');

Route::get('/search', [SearchController::class, 'index'])->name('search');
Route::get('/search/riviercruises', [SearchController::class, 'riverCruises'])->name('search.rivercruises');

Route::get('/sitemap', function () {
    $merchants = app(AffiliateNetworkMerchantController::class)->all();
    $cruiselines = app(AffiliateCruiselineController::class)->all();
    $traveladvices = app(VendorRijksoverheidTraveladviceController::class)->all();
    return view('sitemap', compact('merchants', 'cruiselines', 'traveladvices'));
})->name('sitemap');

Route::get('/product/{slug}', [ProductController::class, 'show'])->name('product.show');
