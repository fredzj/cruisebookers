<?php

namespace App\Http\Controllers;

use App\Services\TravelAdviceApiClient;
use Illuminate\Support\Facades\DB;
use Throwable;

/**
 * Class VendorRijksoverheidTraveladviceController
 *
 * Handles the travel advice pages. Travel advice data is no longer read from
 * the local database but fetched from the private Nederland Wereldwijd widget
 * API (travlr.nl). Only the whitelist of countries that actually have products
 * is still derived from the local product data.
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
        $whitelist = $this->productCountryAlpha3Codes();

        $error = null;
        $countries = [];

        try {
            $countries = TravelAdviceApiClient::fromConfig()->countries($whitelist);
        } catch (Throwable $e) {
            report($e);
            $error = 'De reisadviezen zijn momenteel niet beschikbaar.';
        }

        return view('traveladvices', compact('countries', 'error'));
    }

    /**
     * Retrieve all travel advice countries with products (for the sitemap).
     *
     * @return \Illuminate\Support\Collection<int, object>
     */
    public function all()
    {
        $whitelist = $this->productCountryAlpha3Codes();

        try {
            $countries = TravelAdviceApiClient::fromConfig()->countries($whitelist);
        } catch (Throwable $e) {
            report($e);

            return collect();
        }

        return collect($countries)
            ->map(static fn (array $c): object => (object) [
                'iso_code' => (string) ($c['iso_code'] ?? ''),
                'location' => (string) ($c['location'] ?? ''),
            ])
            ->filter(static fn (object $c): bool => $c->iso_code !== '')
            ->sortBy('location')
            ->values();
    }

    /**
     * Display the travel advice for a specific country.
     *
     * @param  string  $iso  ISO 3166-1 alpha-3 country code (e.g. BEL).
     * @return \Illuminate\View\View
     */
    public function show(string $iso)
    {
        $iso = strtoupper(trim($iso));

        if (preg_match('/^[A-Z]{2,3}$/', $iso) !== 1) {
            abort(404);
        }

        try {
            $data = TravelAdviceApiClient::fromConfig()->country($iso);
        } catch (Throwable $e) {
            report($e);
            abort(503);
        }

        if ($data['country'] === null) {
            abort(404);
        }

        return view('traveladvice-detail', [
            'country' => $data['country'],
            'advices' => $data['advices'],
        ]);
    }

    /**
     * Resolve the ISO 3166-1 alpha-3 codes of countries that have products.
     *
     * @return list<string>
     */
    private function productCountryAlpha3Codes(): array
    {
        $alpha2 = DB::table('affiliate_products_loaded_searchpage')
            ->whereNotNull('destination_country_code')
            ->where('destination_country_code', '<>', '')
            ->distinct()
            ->pluck('destination_country_code')
            ->all();

        if ($alpha2 === []) {
            return [];
        }

        return DB::table('vendor_iso_3166_countrycodes')
            ->whereIn('alpha_2_code', $alpha2)
            ->pluck('alpha_3_code')
            ->map(static fn ($code): string => strtoupper((string) $code))
            ->unique()
            ->values()
            ->all();
    }
}