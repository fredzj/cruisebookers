<?php

/**
 * Class ProductsLoader
 * 
 * @package affiliate-productfeeds
 * @version 1.0.0
 * @since 2024
 * @license MIT
 * 
 * COPYRIGHT: 2024 Fred Onis - All rights reserved.
 * 
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 * 
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 * 
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 * 
 * @author Fred Onis
 */

class ProductsLoader {
    private $db;
    private $dbConfigPath;
    private $log;
    private $timeStart;

    public function __construct($dbConfigPath) {
		$this->dbConfigPath  = $dbConfigPath;
        $this->log = new Log();
        $this->registerExitHandler();
		$this->connectDatabase();
        $this->truncateTables();
    }

    /**
     * Register the exit handler.
     *
     * @return void
     */
    private function registerExitHandler(): void {
        $this->timeStart = microtime(true);
        register_shutdown_function([new ExitHandler($this->timeStart), 'handleExit']);
    }

	/**
	 * Connects to the database using the configuration file.
	 *
	 * This method reads the database configuration from the specified INI file,
	 * parses the configuration, and establishes a connection to the database.
	 * If the configuration file cannot be parsed, an exception is thrown.
	 *
	 * @throws Exception If the configuration file cannot be parsed.
	 * @return void
	 */
	private function connectDatabase(): void {
		if (($dbConfig = parse_ini_file($this->dbConfigPath, FALSE, INI_SCANNER_TYPED)) === FALSE) {
			throw new Exception("Parsing file " . $this->dbConfigPath	. " FAILED");
		}
		$this->db = new Database($dbConfig);
	}

    /**
     * Truncates the relevant database tables before importing new data.
     */
    private function truncateTables(): void {
		$this->db->truncate('affiliate_products_loaded_searchpage');
		$this->db->truncate('affiliate_products_loaded_productpage');
    }

	public function load(): void {
		$this->loadSearchPage();
		$this->loadProductPage();
	}
	
	private function loadSearchPage(): void {
		$sql			=	"
			INSERT INTO affiliate_products_loaded_searchpage
			SELECT
				null,
				null,
				merchant_id,
				m.name								AS	merchant_name,
				productfeed_id,
				product_id,
				title								AS	name,
				price,
				SUBSTRING_INDEX(images, '|', 1)		AS	image,
				dc.slug,
				cruiseline_category,
				null								AS	cruiseline_id,
				cruiseline_name,
				cruiseship_name,
				destination_continent_code,
				(SELECT continent_name FROM vendor_iso_3166_countrycodes WHERE continent_code = destination_continent_code LIMIT 1)
													AS	destination_continent_name,
				destination_country_code,
				(SELECT dutch_short_name FROM vendor_iso_3166_countrycodes WHERE alpha_2_code = destination_country_code)
													AS	destination_country_name,
				destination_port,
				cruiseline_subcategory				AS	destination_sailingarea_name,
				null								AS	destination_region_code,
				null								AS	destination_region_name,
				null								AS	destination_province_name,
				null								AS	destination_city_name,
				0									AS	holidaytype_is_all_inclusives,
				holidaytype_is_lastminutes,
				holidaytype_is_minicruise,
				holidaytype_is_rivercruise,
				holidaytype_is_rivercruise_danube,
				holidaytype_is_rivercruise_douro,
				holidaytype_is_rivercruise_moselle,
				holidaytype_is_rivercruise_nile,
				holidaytype_is_rivercruise_rhine,
				holidaytype_is_rivercruise_rhone,
				holidaytype_is_rivercruise_seine,
				holidaytype_is_rivercruise_volga,
				holidaytype_is_seacruise,
				holidaytype_is_seacruise_antarctic,
				holidaytype_is_seacruise_arctic,
				holidaytype_is_seacruise_bluecruise,
				holidaytype_is_seacruise_caribbean,
				holidaytype_is_seacruise_hurtigruten,
				holidaytype_is_seacruise_mediterranean,
				holidaytype_is_seacruise_sailing,
				holidaytype_is_seacruise_world,
				0									AS	holidaytype_is_herfstvakantie,
				0									AS	holidaytype_is_kerstvakantie,
				0									AS	holidaytype_is_meivakantie,
				0									AS	holidaytype_is_voorjaarsvakantie,
				0									AS	holidaytype_is_zomervakantie,
				departure_date						AS	offer_departure_date,
				departure_port						AS	offer_departure_port_naame,
				CASE
					WHEN	duration_days	IS NOT NULL	THEN	duration_days
					WHEN	duration_nights	IS NOT NULL	THEN	duration_nights + 1
					ELSE	null
				END									AS	offer_duration,
				duration_days						AS	offer_duration_days,
				duration_nights						AS	offer_duration_nights,
				null								AS	offer_duration_type,
				schoolholiday_id
			FROM
				affiliate_products_transformed_daisycon dc
			JOIN
				affiliate_networks_merchants m ON m.id = dc.merchant_id
			WHERE
				m.is_blocked < 1
			UNION ALL
			SELECT
				null,
				null,
				merchant_id,
				m.name								AS	merchant_name,
				productfeed_id,
				product_id,
				tt.name,
				price,
				SUBSTRING_INDEX(images, '|', 1)		AS	image,
				tt.slug,
				cruiseline_category,
				NULL								AS	cruiseline_id,
				cruiseline_name						AS	cruiseline_name,
				cruiseship_name						AS	cruiseship_name,
				(SELECT continent_code FROM vendor_iso_3166_countrycodes WHERE continent_name = destination_continent_name LIMIT 1)
													AS	destination_continent_code,
				destination_continent_name,
				destination_country_code,
				destination_country_name,
				null								AS	destination_port_name,
				cruiseline_subcategory				AS	destination_sailingarea_name,
				null								AS	destination_region_code,
				null								AS	destination_region_name,
				null								AS	destination_province_name,
				null								AS	destination_city_name,
				holidaytype_is_all_inclusives,
				holidaytype_is_lastminutes,
				holidaytype_is_minicruise,
				holidaytype_is_rivercruise,
				holidaytype_is_rivercruise_danube,
				holidaytype_is_rivercruise_douro,
				holidaytype_is_rivercruise_moselle,
				holidaytype_is_rivercruise_nile,
				holidaytype_is_rivercruise_rhine,
				holidaytype_is_rivercruise_rhone,
				holidaytype_is_rivercruise_seine,
				holidaytype_is_rivercruise_volga,
				holidaytype_is_seacruise,
				holidaytype_is_seacruise_antarctic,
				holidaytype_is_seacruise_arctic,
				holidaytype_is_seacruise_bluecruise,
				holidaytype_is_seacruise_caribbean,
				holidaytype_is_seacruise_hurtigruten,
				holidaytype_is_seacruise_mediterranean,
				holidaytype_is_seacruise_sailing,
				holidaytype_is_seacruise_world,
				holidaytype_is_herfstvakantie,
				holidaytype_is_kerstvakantie,
				holidaytype_is_meivakantie,
				holidaytype_is_voorjaarsvakantie,
				holidaytype_is_zomervakantie,
				offer_departure_date,
				null								AS	offer_departure_port_naame,
				offer_duration,
				CASE
					WHEN	offer_duration_days		IS NULL	THEN	offer_duration_nights + 1
					ELSE	offer_duration_days
				END									AS	offer_duration_days,
				CASE
					WHEN	offer_duration_nights	IS NULL	THEN	offer_duration_days - 1
					ELSE	offer_duration_nights
				END									AS	offer_duration_nights,
				offer_duration_type,
				offer_schoolholiday_id
			FROM
				affiliate_products_transformed_tradetracker tt
			JOIN
				affiliate_networks_merchants m ON m.id = tt.merchant_id
			WHERE
				m.is_blocked < 1";
		$this->db->execute($sql);
	}
	
	private function loadProductPage(): void {
		$sql			=	"
			INSERT INTO affiliate_products_loaded_productpage
			SELECT
				null,
				null,
				merchant_id,
				productfeed_id,
				product_id,
				link								AS	url,
				images,
				slug,
				description,
				null								AS	categorypath,
				category							AS	categories,
				null								AS	subcategories,
				null								AS	subsubcategories,
				null								AS	accommodation_descriptionlong,
				null								AS	accommodation_descriptionshort,
				null								AS	accommodation_extrainfo,
				null								AS	accommodation_facilities,
				null								AS	accommodation_is_childrenallowed,
				null								AS	accommodation_rating,
				null								AS	accommodation_stars,
				null								AS	accommodation_usps,
				departure_continent,
				departure_country,
				departure_city,
				destination_region					AS	destination_region_name,
				null								AS	destination_province_name,
				destination_city					AS	destination_city_name,
				null								AS	destination_latitude,
				null								AS	destination_longitude,
				null								AS	offer_excludedfromprice,
				null								AS	offer_includedinprice,
				null								AS	offer_price,
				null								AS	offer_price_type,
				schoolholiday_id
			FROM
				affiliate_products_transformed_daisycon
			UNION ALL
			SELECT
				null,
				null,
				merchant_id,
				productfeed_id,
				product_id,
				url,
				images,
				slug,
				description,
				categorypath,
				categories,
				subcategories,
				subsubcategories,
				accommodation_descriptionlong,
				accommodation_descriptionshort,
				accommodation_extrainfo,
				accommodation_facilities,
				accommodation_is_childrenallowed,
				accommodation_rating,
				accommodation_stars,
				accommodation_usps,
				null								AS	departure_continent,
				null								AS	departure_country,
				null								AS	departure_city,
				destination_region_name,
				destination_province_name,
				destination_city_name,
				destination_latitude,
				destination_longitude,
				offer_excludedfromprice,
				offer_includedinprice,
				offer_price,
				offer_price_type,
				offer_schoolholiday_id
			FROM
				affiliate_products_transformed_tradetracker";
		$this->db->execute($sql);
	}
}