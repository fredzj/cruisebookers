<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\AffiliateCruiseship; // if needed for type hinting

class TransformDaisyconData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'transform:daisycon';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Transform data from Daisycon';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('Starting data transformation...');

        // Get all extracted records (you may adjust the query as needed)
        $extractedRecords = DB::table('affiliate_products_extracted_daisycon')->get();
        $this->info("Found {$extractedRecords->count()} records.");

        foreach ($extractedRecords as $record) {
            // Transform each row into an associative array matching the target table columns
            $transformedData = $this->transformRecord($record);

            // Insert into the transformed table
            DB::table('affiliate_products_transformed_daisycon_new')->insert($transformedData);
        }

        $this->info('Data transformation complete.');
        return 0;
    }

    /**
     * Transforms a single record from the extracted table into the target table format.
     *
     * @param object $record
     * @return array
     */
    protected function transformRecord($record): array
    {
        return [
            // timestamp will be set automatically by the table default
            'merchant_id'                           => $record->merchant_id,
            'productfeed_id'                        => $record->productfeed_id,
            'product_id'                            => $record->product_id,
            'category'                              => $this->transformCategory($record),
            'cruiseline_category'                   => $this->transformCruiselineCategory($record),
            'cruiseline_subcategory'                => $this->transformCruiselineSubcategory($record),
            'cruiseline_id'                         => null,  // Not transformed here
            'cruiseline_name'                       => $this->transformCruiselineName($record),
            'cruiseship_name'                       => $this->transformCruiseshipName($record),
            'departure_city'                        => $record->departure_city,
            'departure_continent'                   => $record->departure_continent,
            'departure_country'                     => $this->transformDepartureCountry($record),
            'departure_date'                        => $record->departure_date,
            'departure_port'                        => $record->departure_port,
            'description'                           => str_replace('&amp;', '&', $record->description),
            'destination_city'                      => $record->destination_city,
            'destination_continent_code'            => $this->transformDestinationContinentCode($record),
            'destination_country_code'              => $this->transformDestinationCountryCode($record),
            'destination_country_name'              => null, // set to null per the definition
            'destination_port'                      => $record->destination_port,
            'destination_region'                    => null, // Not transformed here
            'duration_days'                         => $this->transformDurationDays($record),
            'duration_nights'                       => $this->transformDurationNights($record),
            'holidaytype_is_lastminutes'            => $this->transformHolidaytypeIsLastminutes($record),
            'holidaytype_is_minicruise'             => $this->transformHolidaytypeIsMinicruise($record),
            'holidaytype_is_rivercruise'            => $this->transformHolidaytypeIsRivercruise($record),
            'holidaytype_is_rivercruise_danube'       => $this->transformHolidaytypeIsRivercruiseDanube($record),
            'holidaytype_is_rivercruise_douro'        => $this->transformHolidaytypeIsRivercruiseDouro($record),
            'holidaytype_is_rivercruise_moselle'      => $this->transformHolidaytypeIsRivercruiseMoselle($record),
            'holidaytype_is_rivercruise_nile'         => $this->transformHolidaytypeIsRivercruiseNile($record),
            'holidaytype_is_rivercruise_rhine'        => $this->transformHolidaytypeIsRivercruiseRhine($record),
            'holidaytype_is_rivercruise_rhone'        => $this->transformHolidaytypeIsRivercruiseRhone($record),
            'holidaytype_is_rivercruise_seine'        => $this->transformHolidaytypeIsRivercruiseSeine($record),
            'holidaytype_is_rivercruise_volga'        => $this->transformHolidaytypeIsRivercruiseVolga($record),
            'holidaytype_is_seacruise'                => $this->transformHolidaytypeIsSeacruise($record),
            'holidaytype_is_seacruise_antarctic'      => $this->transformHolidaytypeIsSeacruiseAntarctic($record),
            'holidaytype_is_seacruise_arctic'         => 0,
            'holidaytype_is_seacruise_bluecruise'       => $this->transformHolidaytypeIsSeacruiseBluecruise($record),
            'holidaytype_is_seacruise_caribbean'      => $this->transformHolidaytypeIsSeacruiseCaribbean($record),
            'holidaytype_is_seacruise_hurtigruten'      => $this->transformHolidaytypeIsSeacruiseHurtigruten($record),
            'holidaytype_is_seacruise_mediterranean'    => $this->transformHolidaytypeIsSeacruiseMediterranean($record),
            'holidaytype_is_seacruise_sailing'         => $this->transformHolidaytypeIsSeacruiseSailing($record),
            'holidaytype_is_seacruise_world'           => $this->transformHolidaytypeIsSeacruiseWorld($record),
            'holidaytype_is_herfstvakantie'            => 0,
            'holidaytype_is_kerstvakantie'             => 0,
            'holidaytype_is_meivakantie'               => 0,
            'holidaytype_is_voorjaarsvakantie'         => 0,
            'holidaytype_is_zomervakantie'             => 0,
            'images'                                  => $record->image_location,
            'link'                                    => $record->link,
            'price'                                   => $record->price,
            'schoolholiday_id'                        => $this->transformSchoolholidayId($record),
            'sku'                                     => $record->sku,
            'slug'                                    => $this->transformSlug($record),
            'title'                                   => str_replace('&amp;', '&', $record->title)
        ];
    }

    /**
     * Transform the category field.
     * If merchant_id is 10764 or 17747, use 'Cruises', otherwise return the extracted category.
     *
     * @param object $record
     * @return string
     */
    protected function transformCategory($record): string
    {
        if ($record->merchant_id == 10764 || $record->merchant_id == 17747) {
            return 'Cruises';
        }
        return $record->category;
    }

    /**
     * Transform cruiseline category based on the title and category.
     *
     * @param object $record
     * @return string
     */
    protected function transformCruiselineCategory($record): string
    {
        $category = strtolower($record->category);
        $title = strtolower($record->title);
        $travel_tour_operator = strtolower($record->travel_tour_operator);
        if (
            strpos($category, 'riviercruise') !== false ||
            strpos($title, 'donau') !== false ||
            strpos($title, 'douro') !== false ||
            strpos($title, 'kerstmarktcruise') !== false ||
            strpos($title, 'moezel') !== false ||
            strpos($title, 'nijl') !== false ||
            strpos($title, 'rhone') !== false ||
            strpos($title, 'rhône') !== false ||
            strpos($title, 'rijn') !== false ||
            strpos($title, 'riviercruise') !== false ||
            strpos($title, 'saône') !== false ||
            strpos($title, 'seine') !== false ||
            strpos($title, 'vanuit passau') !== false ||
            strpos($title, 'wolga') !== false ||
            strpos($travel_tour_operator, 'oad') !== false
        ) {
            return 'riviercruise';
        }
        return 'zeecruise';
    }

    /**
     * Transform the cruiseline subcategory.
     *
     * @param object $record
     * @return string|null
     */
    protected function transformCruiselineSubcategory($record): ?string
    {
         $title = $record->title;
         if ($record->travel_tour_operator !== 'OAD') {
             if (strpos($title, 'antarctic') !== false) {
                 return 'Zuidpoolgebied';
             }
             if (strpos($title, 'Caribbean') !== false || strpos($title, 'caribische') !== false) {
                 return 'Caribisch zeegebied';
             }
             if (strpos($record->destination_region, 'Middellandse Zee') !== false) {
                 return 'Middellandse zeegebied';
             }
             if (strpos($title, 'mediterraans') !== false || strpos($title, 'mediterrane') !== false || strpos($title, 'middellandse zee') !== false) {
                 return 'Middellandse zeegebied';
             }
             if (strpos($title, 'Transatlantisch') !== false) {
                 return 'Transatlantische cruise';
             }
             if (strpos($title, 'wereldcruise') !== false) {
                 return 'Wereldcruise';
             }
         }
         return null;
    }

    /**
     * Transform the cruiseline name.
     *
     * @param object $record
     * @return string
     */
    protected function transformCruiselineName($record): string
    {
         if (strpos($record->travel_tour_operator, 'TUI Cruises') !== false) {
             return 'TUI Cruises';
         }
         return $record->travel_tour_operator;
    }

    /**
     * Transform the cruiseship name by checking which AffiliateCruiseship model name appears in the title.
     *
     * This function queries AffiliateCruiseship and returns the name if it is found as a substring (case‑insensitive)
     * in the extracted record's title.
     *
     * @param object $record
     * @return string|null
     */
    protected function transformCruiseshipName($record): ?string
    {
        // Lowercase the title for case-insensitive matching.
        $title = strtolower($record->title);
        
        // Get all AffiliateCruiseship names.
        $ships = \App\Models\AffiliateCruiseship::select('name')->get();
        
        foreach ($ships as $ship) {
            if (!empty($ship->name) && strpos($title, strtolower($ship->name)) !== false) {
                return $ship->name;
            }
        }

        //if (strpos($title, 'aidabella') !== false) return 'AIDAbella';
        return null;
    }

    /**
     * Transform departure country into a proper name.
     *
     * @param object $record
     * @return string
     */
    protected function transformDepartureCountry($record): string
    {
         if ($record->departure_country === 'VS') {
             return 'Verenigde Staten';
         }
         return $record->departure_country;
    }

    /**
     * Transform destination continent code.
     *
     * @param object $record
     * @return string|null
     */
    protected function transformDestinationContinentCode($record): ?string
    {
        if ($record->destination_city    === 'Baltra (Galapagos)') return 'ZA';	// Error in XML productfeed from cruisereizen.nl
        if ($record->destination_city    === 'Chioggia') return 'EU';			// Error in XML productfeed from cruisereizen.nl
        if ($record->destination_city    === 'Dili') return 'AZ';
        if ($record->destination_city    === 'Hong Kong') return 'AZ';
        if ($record->destination_city    === 'Sint Maarten') return 'NA';
        if ($record->destination_country === 'AE') return 'AZ';
        if ($record->destination_country === 'AG') return 'NA';
		if ($record->destination_country === 'AN') return 'NA';
		if ($record->destination_country === 'AR') return 'ZA';
		if ($record->destination_country === 'AT') return 'EU';
		if ($record->destination_country === 'AU') return 'OC';
		if ($record->destination_country === 'AQ') return 'AQ';			
		if ($record->destination_country === 'BB') return 'NA';
		if ($record->destination_country === 'BE') return 'EU';
		if ($record->destination_country === 'BR') return 'ZA';
		if ($record->destination_country === 'BZ') return 'NA';
		if ($record->destination_country === 'CA') return 'NA';
		if ($record->destination_country === 'CL') return 'ZA';
		if ($record->destination_country === 'CN') return 'AZ';
		if ($record->destination_country === 'CO') return 'ZA';
		if ($record->destination_country === 'CR') return 'NA';
		if ($record->destination_country === 'CV') return 'AF';
		if ($record->destination_country === 'DE') return 'EU';
		if ($record->destination_country === 'DK') return 'EU';
		if ($record->destination_country === 'DO') return 'NA';
		if ($record->destination_country === 'EC') return 'ZA';
		if ($record->destination_country === 'EG') return 'AF';
		if ($record->destination_country === 'ES') return 'EU';
		if ($record->destination_country === 'FI') return 'EU';
		if ($record->destination_country === 'FJ') return 'OC';
		if ($record->destination_country === 'FM') return 'OC';
		if ($record->destination_country === 'FR') return 'EU';
		if ($record->destination_country === 'GB') return 'EU';
		if ($record->destination_country === 'GD') return 'NA';
		if ($record->destination_country === 'GH') return 'AF';
		if ($record->destination_country === 'GL') return 'NA';
		if ($record->destination_country === 'GP') return 'NA';
		if ($record->destination_country === 'GR') return 'EU';
		if ($record->destination_country === 'HR') return 'EU';
		if ($record->destination_country === 'ID') return 'AZ';
		if ($record->destination_country === 'IE') return 'EU';
		if ($record->destination_country === 'IL') return 'AZ';
		if ($record->destination_country === 'IN') return 'AZ';
		if ($record->destination_country === 'IS') return 'AZ';
		if ($record->destination_country === 'IT') return 'EU';
		if ($record->destination_country === 'JM') return 'NA';
		if ($record->destination_country === 'JP') return 'AZ';
		if ($record->destination_country === 'KI') return 'OC';
		if ($record->destination_country === 'KN') return 'NA';
		if ($record->destination_country === 'KR') return 'AZ';
		if ($record->destination_country === 'LC') return 'NA';
		if ($record->destination_country === 'MA') return 'AF';
		if ($record->destination_country === 'MC') return 'EU';
		if ($record->destination_country === 'MG') return 'AF';
		if ($record->destination_country === 'MQ') return 'NA';
		if ($record->destination_country === 'MT') return 'EU';
		if ($record->destination_country === 'MU') return 'AF';
		if ($record->destination_country === 'NA') return 'AF';
		if ($record->destination_country === 'NC') return 'OC';
		if ($record->destination_country === 'NL') return 'EU';
		if ($record->destination_country === 'NO') return 'EU';
		if ($record->destination_country === 'NZ') return 'OC';
		if ($record->destination_country === 'PA') return 'NA';
		if ($record->destination_country === 'PE') return 'ZA';
		if ($record->destination_country === 'PF') return 'OC';
		if ($record->destination_country === 'PR') return 'NA';
		if ($record->destination_country === 'PT') return 'EU';
		if ($record->destination_country === 'PW') return 'OC';
		if ($record->destination_country === 'QA') return 'AZ';
		if ($record->destination_country === 'SA') return 'AZ';
		if ($record->destination_country === 'SC') return 'AF';
		if ($record->destination_country === 'SE') return 'EU';
		if ($record->destination_country === 'SG') return 'AZ';
		if ($record->destination_country === 'SN') return 'AF';
		if ($record->destination_country === 'SX') return 'NA';
		if ($record->destination_country === 'TH') return 'AZ';
		if ($record->destination_country === 'TN') return 'AF';
		if ($record->destination_country === 'TO') return 'OC';
		if ($record->destination_country === 'TR') return 'EU';
		if ($record->destination_country === 'TT') return 'ZA';
		if ($record->destination_country === 'TW') return 'AZ';
		if ($record->destination_country === 'TZ') return 'AF';
		if ($record->destination_country === 'US') return 'NA';
		if ($record->destination_country === 'UY') return 'ZA';
		if ($record->destination_country === 'VI') return 'NA';
		if ($record->destination_country === 'VS') return 'NA';
		if ($record->destination_country === 'WS') return 'OC';
		if ($record->destination_country === 'ZA') return 'AF';
        return null;
    }

    /**
     * Transform destination country code.
     *
     * @param object $record
     * @return string|null
     */
    protected function transformDestinationCountryCode($record): ?string
    {
        if ($record->destination_country === 'VS') return 'US';
		if ($record->destination_country === 'AN' && $record->destination_city === 'Curacao') return 'CW';
		if ($record->destination_country === 'AN' && $record->destination_city === 'Philipsburg') return 'SX';
		if ($record->destination_city    === 'Baltra (Galapagos)') return 'EC';											        // Error in XML productfeed from cruisereizen.nl
		if ($record->destination_city    === 'Bayonne' && $record->destination_continent = 'Canarische Eilanden') return 'ES';	// Error in XML productfeed from cruisereizen.nl
		if ($record->destination_city    === 'Bayonne' && $record->destination_continent = 'Noord-Amerika') return 'US';		// Error in XML productfeed from cruisereizen.nl
		if ($record->destination_city    === 'Chioggia') return 'IT';														    // Error in XML productfeed from cruisereizen.nl
		if ($record->destination_city    === 'Soufriere') return 'VC';													        // Error in XML productfeed from cruisereizen.nl
		if ($record->destination_city    === 'Dili') return 'TL';
		if ($record->destination_city    === 'Hong Kong') return 'HK';
		if ($record->destination_city    === 'Marne La Vallée') return 'FR';
		if ($record->destination_city    === 'Nice') return 'FR';
		if ($record->destination_city    === 'NYC') return 'US';
		if ($record->destination_city    === 'Philadelphia') return 'US';
		if ($record->destination_city    === 'Pitlochry') return 'GB';
		if ($record->destination_city    === 'Sint Maarten') return 'SX';
		if ($record->destination_city    === 'Vieux fort') return 'LC';
        return $record->destination_country;
    }

    /**
     * Transform duration days.
     *
     * @param object $record
     * @return int|null
     */
    protected function transformDurationDays($record)
    {
         if ($record->merchant_id == 10764) {
             $parts = explode(' ', $record->title);
             return isset($parts[0]) ? intval($parts[0]) : (int)$record->duration_days;
         }
         return (int)$record->duration_days;
    }

    /**
     * Transform duration nights.
     *
     * @param object $record
     * @return int|null
     */
    protected function transformDurationNights($record)
    {
         if ($record->merchant_id == 10764) {
             $parts = explode(' ', $record->title);
             return isset($parts[0]) ? intval($parts[0]) - 1 : (int)$record->duration_nights;
         }
         return (int)$record->duration_nights;
    }

    /**
     * Determine if the product is last minute.
     *
     * @param object $record
     * @return int
     */
    protected function transformHolidaytypeIsLastminutes($record): int
    {
         // Example: Check if departure_date minus 42 days is before today.
         return (strtotime($record->departure_date) - strtotime('-42 days')) < time() ? 1 : 0;
    }

    /**
     * Transform holidaytype_is_minicruise flag.
     *
     * @param object $record
     * @return int
     */
    protected function transformHolidaytypeIsMinicruise($record): int
    {
         return strpos($record->title, 'minicruise') !== false ? 1 : 0;
    }

    /**
     * Transform holidaytype_is_rivercruise flag.
     *
     * @param object $record
     * @return int
     */
    protected function transformHolidaytypeIsRivercruise($record): int
    {
         $title = strtolower($record->title);
         if (
             strpos($title, 'donau') !== false ||
             strpos($title, 'douro') !== false ||
             strpos($title, 'kerstmarktcruise') !== false ||
             strpos($title, 'moezel') !== false ||
             strpos($title, 'nijl') !== false ||
             strpos($title, 'rhone') !== false ||
             strpos($title, 'rhône') !== false ||
             strpos($title, 'rijn') !== false ||
             strpos(strtolower($record->category), 'riviercruise') !== false ||
             strpos($title, 'riviercruise') !== false ||
             strpos($title, 'saône') !== false ||
             strpos($title, 'seine') !== false ||
             strpos($title, 'vanuit passau') !== false ||
             strpos($title, 'wolga') !== false
         ) {
             return 1;
         }
         return 0;
    }

    protected function transformHolidaytypeIsRivercruiseDanube($record): int
    {
         return (strpos(strtolower($record->title), 'donau') !== false || strpos(strtolower($record->title), 'vanuit passau') !== false) ? 1 : 0;
    }
    
    protected function transformHolidaytypeIsRivercruiseDouro($record): int
    {
         return strpos(strtolower($record->title), 'douro') !== false ? 1 : 0;
    }
    
    protected function transformHolidaytypeIsRivercruiseMoselle($record): int
    {
         return strpos(strtolower($record->title), 'moezel') !== false ? 1 : 0;
    }
    
    protected function transformHolidaytypeIsRivercruiseNile($record): int
    {
         return strpos(strtolower($record->title), 'nijl') !== false ? 1 : 0;
    }
    
    protected function transformHolidaytypeIsRivercruiseRhine($record): int
    {
         return strpos(strtolower($record->title), 'rijn') !== false ? 1 : 0;
    }
    
    protected function transformHolidaytypeIsRivercruiseRhone($record): int
    {
         $title = strtolower($record->title);
         return (strpos($title, 'rhone') !== false || strpos($title, 'rhône') !== false || strpos($title, 'saône') !== false) ? 1 : 0;
    }
    
    protected function transformHolidaytypeIsRivercruiseSeine($record): int
    {
         return strpos(strtolower($record->title), 'seine') !== false ? 1 : 0;
    }
    
    protected function transformHolidaytypeIsRivercruiseVolga($record): int
    {
         return strpos(strtolower($record->title), 'wolga') !== false ? 1 : 0;
    }
    
    protected function transformHolidaytypeIsSeacruise($record): int
    {
         $title = strtolower($record->title);
         if (
             strpos($title, 'antarctica') !== false ||
             strpos($title, 'blue cruise') !== false ||
             strpos($title, 'caribbean') !== false ||
             strpos($title, 'caribische') !== false ||
             strpos($record->destination_region, 'Middellandse Zee') !== false ||
             strpos($title, 'mediterraans') !== false ||
             strpos($title, 'mediterrane') !== false ||
             strpos($title, 'middellandse zee') !== false ||
             strpos($title, 'wereldcruise') !== false ||
             strpos($title, 'zeecruise') !== false
         ) {
             return 1;
         }
         return 0;
    }
    
    protected function transformHolidaytypeIsSeacruiseAntarctic($record): int
    {
         return strpos(strtolower($record->title), 'antarctica') !== false ? 1 : 0;
    }
    
    protected function transformHolidaytypeIsSeacruiseBluecruise($record): int
    {
         return strpos(strtolower($record->title), 'blue cruise') !== false ? 1 : 0;
    }
    
    protected function transformHolidaytypeIsSeacruiseCaribbean($record): int
    {
         return (strpos(strtolower($record->title), 'caribbean') !== false || strpos(strtolower($record->title), 'caribische') !== false) ? 1 : 0;
    }
    
    protected function transformHolidaytypeIsSeacruiseHurtigruten($record): int
    {
         return strpos(strtolower($record->title), 'hurtigruten') !== false ? 1 : 0;
    }
    
    protected function transformHolidaytypeIsSeacruiseMediterranean($record): int
    {
         if (
             strpos($record->destination_region, 'Middellandse Zee') !== false ||
             strpos(strtolower($record->title), 'mediterraans') !== false ||
             strpos(strtolower($record->title), 'mediterrane') !== false ||
             strpos(strtolower($record->title), 'middellandse zee') !== false
         ) {
             return 1;
         }
         return 0;
    }
    
    protected function transformHolidaytypeIsSeacruiseSailing($record): int
    {
         return strpos(strtolower($record->title), 'sailing') !== false ? 1 : 0;
    }
    
    protected function transformHolidaytypeIsSeacruiseWorld($record): int
    {
         return strpos(strtolower($record->title), 'wereldcruise') !== false ? 1 : 0;
    }
    
    /**
     * Find the matching school holiday ID if any.
     *
     * @param object $record
     * @return int|null
     */
    protected function transformSchoolholidayId($record): ?int
    {
         $holiday = DB::table('school_holidays')
             ->where('start_date', '<=', $record->departure_date)
             ->where('end_date', '>=', $record->departure_date)
             ->first();
         return $holiday ? $holiday->id : null;
    }
    
    /**
     * Generate a slug for the transformed record.
     *
     * @param object $record
     * @return string
     */
    protected function transformSlug($record): string
    {
         $merchantSlug = DB::table('affiliate_networks_merchants')
             ->where('id', $record->merchant_id)
             ->value('slug');
         $titleSlug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $record->title)));
         return $merchantSlug . '-' . $titleSlug . '-' . $record->product_id;
    }
}