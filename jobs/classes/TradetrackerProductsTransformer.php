<?php

/**
 * Class TradetrackerProductsTransformer
 * 
 * This class is responsible for transforming product data from the TradeTracker affiliate network.
 * It retrieves product data from the database, validates and transforms it, and saves the transformed
 * data into the appropriate database tables.
 * 
 * FUNCTIONALITY:
 * - Initializes database connections and output columns.
 * - Retrieves merchants and their associated product data.
 * - Validates product data, including country, continent, accommodation type, and school holidays.
 * - Transforms the product data into the required format for TradeTracker accommodation and offer tables.
 * - Saves the transformed data into the database.
 * 
 * USAGE:
 * - Instantiate the class with the database configuration path.
 * - Call the `transform()` method to process and save the transformed data.
 * 
 * DEPENDENCIES:
 * - `SchoolholidayValidator`: Validates school holiday data for products.
 * - `Database`: Handles database operations such as fetching and saving data.
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
require_once __DIR__ . '/ImageValidator.php';

class TradetrackerProductsTransformer {
	private $cruiselines;
    private $db;
    private $dbConfigPath;
	private $imageValidator;
    private $log;
	private $properties;
    private $timeStart;
	private $variations = [];

    public function __construct($dbConfigPath) {
		$this->dbConfigPath  = $dbConfigPath;
        $this->log = new Log();
        $this->registerExitHandler();
		$this->connectDatabase();
        $this->truncateTable();
		$this->imageValidator = new ImageValidator($this->db);
		$this->cruiselines = $this->fetchCruiselines();
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
    private function truncateTable(): void {
		$this->db->truncate('affiliate_products_transformed_tradetracker');
    }
	
	private function fetchCruiselines(): array {
		$sql			=	"
		SELECT			id,
						name,
						short_name
		FROM			affiliate_cruiselines
		WHERE			is_blocked	=	0
		ORDER BY		name";
        $fetched_rows	=	$this->db->select($sql);
		
		$cruiselines = [];
		foreach ($fetched_rows as $fetched_row) {
			$cruiseline_id	=	$fetched_row['id'];
			$cruiselines[$cruiseline_id]['name']		=	$fetched_row['name'];
			$cruiselines[$cruiseline_id]['short_name']	=	$fetched_row['short_name'];
		}
		return $cruiselines;
	}

    public function transform(): void {
		foreach ($this->getMerchants() as $merchant) {
			$products = $this->getProducts($merchant['id']);
			$this->log->info('Transforming ' . count($products) . ' products for merchant ' . $merchant['name']);
			foreach ($products as $product) {
				if ($this->validateData($merchant, $product)) {
					$this->transformAndSaveData($product);
				}
			}
		}
    }

    /**
     * Retrieves extracted TradeTracker merchants from the database.
     * 
     * @return array The result set containing merchants.
     */
    private function getMerchants(): array {
		$sql			=	"
		SELECT			DISTINCT
						m.id,
						m.name,
						m.domain_name,
						m.slug
		FROM			affiliate_networks_merchants m
		JOIN			affiliate_networks_merchants_productfeeds p	ON	p.merchant_id	=	m.id
		WHERE		(	
						m.date_blocked								IS	NULL	OR
						m.date_blocked								>	CURDATE()
					)
		AND				m.affiliate_network_code					=	'TT'
		AND				m.is_blocked								=	0
		AND				p.is_blocked								=	0
		ORDER BY		m.name";
        return $this->db->select($sql);
	}

    /**
     * Retrieves extracted TradeTracker products from the database.
     * 
     * @return array The result set containing products.
     */
    private function getProducts(int $merchantID): array {
		$sql			=	"
		SELECT			p.merchant_id,
						p.productfeed_id,
						p.product_id,
						p.campaign_id,
						p.name,
						p.currency,
						p.price,
						p.url,
						p.images,
						p.description,
						p.categories,
						p.properties,
						p.variations
		FROM			affiliate_networks_merchants m
		JOIN			affiliate_networks_merchants_productfeeds f	ON	f.merchant_id		=	m.id
		JOIN			affiliate_products_extracted_tradetracker p	ON	p.merchant_id		=	m.id	AND
																		p.productfeed_id	=	f.id
		WHERE			p.merchant_id								=	$merchantID
		AND				f.is_blocked								=	0
		AND				f.is_blocked_for_transformation				=	0
		AND		(
					(	m.domain_name	=	'oceanwide-expeditions.com'												)	||
					(	m.domain_name	=	'sunweb.nl'																)	||
					(	m.domain_name	=	'bbi-travel.nl'		&&	LOWER(p.categories)	LIKE		'%hurtigruten%'	
																&&	LOWER(p.categories)	NOT LIKE	'%excursie%'	
																&&	LOWER(p.categories)	NOT LIKE	'%verlenging%'	)	||
					(	m.domain_name	=	'dejongintra.nl'	&&	LOWER(p.categories)	LIKE		'%cruise%'		)	||
					(	m.domain_name	=	'merapi.nl'			&&	LOWER(p.categories)	LIKE		'%cruise%'		)	||
					(	m.domain_name	=	'oad.nl'			&&	LOWER(p.categories)	LIKE		'%cruise%'		)	||
					(	m.domain_name	=	'stipreizen.nl'		&&	LOWER(p.categories)	LIKE		'%cruise%'		)	||
					(	m.domain_name	=	'traveldeal.nl'		&&	LOWER(p.categories)	LIKE		'%cruise%'		)	||
					(	m.domain_name	=	'corendon.nl'		&&	LOWER(p.name)		LIKE		'%cruise%'		)	||
					(	m.domain_name	=	'fital.nl'			&&	LOWER(p.name)		LIKE		'%cruise%'		)	||
					(	m.domain_name	=	'tui.nl'			&&	LOWER(p.name)		LIKE		'%cruise%'		)
				)
		ORDER BY		p.product_id";
		return $this->db->select($sql);
    }

	/**
	 * Validates the product data.
	 * 
	 * This method validates the product data by checking the country, school holidays, 
	 * and ensuring that required fields like images are not empty.
	 * 
	 * @param array $merchant The merchant data.
	 * @param array $product The product data to validate.
	 * @return bool True if the product data is valid, false otherwise.
	 */
	private function validateData($merchant, &$product): bool {
		$this->transformProperties($product['properties']);
		$this->validateDefaults($merchant, $product);

		if (empty($product['images'])) {
			$this->log->error('images is empty for product ' . $product['product_id']);
			return false;
		}
		return true;
	}

	/**
	 * Transforms product properties from JSON to an associative array.
	 * 
	 * @param array $properties The JSON-encoded properties.
	 * @return void
	 */
	private function transformProperties(string $properties): void {
		$this->properties = [];
		$data = json_decode($properties, true);
		if (isset($data['property']) && is_array($data['property'])) {
		    foreach ($data['property'] as $property) {
				// Set name
				if (isset($property['@attributes'])) {
		        	$name = strtolower($property['@attributes']['name']) ?? null;
				} elseif (isset($property['@attributes'])) {
		        	$name = strtolower($property['name']) ?? null;
				} else {
					$name = null;
				}
				// Set value
		        $value = $property['value'] ?? null;
			
				if ($name && $value) {
					if (is_array($value)) {
						$this->properties[$name] = trim(implode('|', array_filter($value)), ',');
					} else {
						$this->properties[$name] = trim($value, ',');
					}
				}
		    }
		}
	}

	private function validateDefaults($merchant, &$product): void {
		$product['holidaytype_is_herfstvakantie'] = false;
		$product['holidaytype_is_kerstvakantie'] = false;
		$product['holidaytype_is_meivakantie'] = false;
		$product['holidaytype_is_voorjaarsvakantie'] = false;
		$product['holidaytype_is_zomervakantie'] = false;

		$this->imageValidator->getImages($product['images'], $this->properties, $this->variations);
		
		$this->properties['arrivaldate'] = $this->validateDate($this->properties['arrivaldate'] ?? null);
		$this->properties['departuredate'] = $this->validateDate($this->properties['departuredate'] ?? null);
		$this->variations['departuredate'] = $this->validateDate($this->variations['departuredate'] ?? null);

		$this->validateContinent();
		$this->validateCountry();

//		if (array_key_exists('departuredate', $this->properties)) {
//			$type = $this->schoolholidayValidator->getSchoolholidayForDate($this->properties['departuredate']);
//			if (!empty($type)) $product["holidaytype_is_$type"] = true;
//		}

		switch ($merchant['domain_name']) {
			case 'bbi-travel.nl':
				$this->validateBBITravel($product);
				break;
			case 'corendon.nl':
			case 'stipreizen.nl':
				$this->validateCorendon($product);
				break;
			case 'dejongintra.nl':
				$this->validateDeJongIntra();
				break;
			case 'fital.nl':
				$this->validateFital($product);
				break;
			case 'oceanwide-expeditions.com':
				$this->validateOceanwide();
				break;
			case 'sunweb.nl':
				$this->validateSunweb();
				break;
			case 'merapi.nl':
			case 'oad.nl':
			case 'traveldeal.nl':
			case 'tui.nl':
			default:
		}
		$this->validateCruiseLineName($product);
		$this->validateCruiseLineCategory($product);
		$this->validateCruiseShipName($product);
		
		$product['slug']	=	$merchant['slug'] . '-' . $this->slugify($product['name']) . '-' . urlencode($product['product_id']);
	}
	
	private function slugify($string, $separator = '-'): string {
		// Check if the intl extension is loaded
		if (!extension_loaded('intl')) {
			// Fallback for environments where intl is not available
			// This fallback is less robust for complex character sets
			$string = strtolower($string);
			$string = preg_replace('/[^\w\s-]/', '', $string); // Remove non-alphanumeric characters except whitespace and hyphens
			$string = preg_replace('/\s+/', $separator, $string); // Replace whitespace with separator
			$string = preg_replace('/-+/', $separator, $string); // Replace multiple hyphens with single separator
			$string = trim($string, $separator); // Trim leading/trailing separators
			return $string;
		}
	
		// Use Transliterator for robust transliteration (e.g., accented characters to ASCII)
		// Any-Latin: Transliterates to Latin script.
		// Latin-ASCII: Transliterates Latin script to ASCII (removes diacritics).
		// NFD: Normalization Form D (Decomposition)
		// [:Nonspacing Mark:] Remove: Removes non-spacing marks (diacritics).
		// NFC: Normalization Form C (Composition)
		// Lower(): Converts to lowercase.
		$transliteratorRules = 'Any-Latin; Latin-ASCII; NFD; [:Nonspacing Mark:] Remove; NFC; Lower();';
		$transliteratedString = transliterator_transliterate($transliteratorRules, $string);
	
		if ($transliteratedString === false) {
			// Fallback if transliteration fails for some reason
			$transliteratedString = strtolower($string);
		}
	
		// Replace non-alphanumeric characters (except the separator itself) with the separator
		// The pattern [^\w-] matches any character that is not a word character (alphanumeric or underscore) or a hyphen.
		// We add the separator to the exclusion if it's not a hyphen to avoid replacing it.
		$pattern = '/[^\w' . ($separator === '-' ? '-' : preg_quote($separator, '/')) . ']+/';
		$slug = preg_replace($pattern, $separator, $transliteratedString);
	
		// Remove leading and trailing separators
		$slug = trim($slug, $separator);
	
		// Replace multiple consecutive separators with a single separator
		$slug = preg_replace('/' . preg_quote($separator, '/') . '{2,}/', $separator, $slug);
	
		return $slug;
	}
	
	private function validateBBITravel(&$product) {
		$product['description']	=	htmlspecialchars_decode($product['description']);
		
		$product['description']	=	str_replace("\r", '<br/>', $product['description']);
		$product['description']	=	str_replace("\n", '<br/>', $product['description']);
		$product['description']	=	str_replace('Bij te boeken activiteiten en excursies', '<br/><strong>Bij te boeken activiteiten en excursies</strong>', $product['description']);
		$product['description']	=	str_replace('Kijk op het tabblad Excursies', 'Kijk op het tabblad <strong>[Excursies]</strong>', $product['description']);
		$product['description']	=	str_replace('Let op: De korting is nog niet verwerkt in de prijsopgave. Neem voor de actuele prijzen contact met ons op.', 'Let op: De korting is nog niet verwerkt in de prijsopgave. Neem voor de actuele prijzen contact met ons op.<br/>', $product['description']);
		$product['description']	=	str_replace('(Let op: Momenteel zijn de excursies voor 2025 nog niet te reserveren en gelden de genoemde tarieven als indicatie).', '(Let op: Momenteel zijn de excursies voor 2025 nog niet te reserveren en gelden de genoemde tarieven als indicatie).<br/>', $product['description']);
		$product['description']	=	str_replace('Reisschema', '<br/><strong>Reisschema</strong>', $product['description']);
		$product['description']	=	str_replace('Tariefsoorten Hurtigruten', '<br/><strong>Tariefsoorten Hurtigruten</strong>', $product['description']);
		$product['description']	=	str_replace('Vervoer<br/>', '<br/><strong>Vervoer</strong><br/>', $product['description']);
		$product['description']	=	str_replace('Vervoer <br/>', '<br/><strong>Vervoer</strong><br/>', $product['description']);
		$product['description']	=	str_replace('Voorwaarden aanbieding:', '<br/><strong>Voorwaarden aanbieding:</strong>', $product['description']);
		$product['description']	=	str_replace('Voorwaarden Single aanbieding', '<br/><strong>Voorwaarden Single aanbieding:</strong>', $product['description']);
		
		if (mb_stripos($product['name'], 'Hurtigruten Expeditie Alaska') !== false) {
			$this->properties['continent']		=	'Noord-Amerika';
			$this->properties['country_code']	=	'US';
			$this->properties['country_name']	=	'United States';
			$this->properties['region_code']	=	'US-AK';
			$this->properties['region_name']	=	'Alaska';
		}
		if (mb_stripos($product['name'], 'Hurtigruten Expeditie Antarctica') !== false) {
			$this->properties['continent']		=	'Antarctica';
			$this->properties['country_code']	=	null;
			$this->properties['country_name']	=	null;
		}
		if (mb_stripos($product['name'], 'Hurtigruten Expeditie Galapagos') !== false) {
			$this->properties['continent']		=	'Zuid-Amerika';
			$this->properties['country_code']	=	'EC';
			$this->properties['country_name']	=	'Ecuador';
			$this->properties['region_code']	=	'EC-W';
			$this->properties['region_name']	=	'Galápagos';
		}
		if (mb_stripos($product['name'], 'Hurtigruten Expeditie Groenland') !== false) {
			$this->properties['continent']		=	'Noord-Amerika';
			$this->properties['country_code']	=	'GL';
			$this->properties['country_name']	=	'Groenland';
		}
		if (mb_stripos($product['name'], 'Hurtigruten Expeditie IJsland') !== false) {
			$this->properties['continent']		=	'Europa';
			$this->properties['country_code']	=	'IS';
			$this->properties['country_name']	=	'IJsland';
		}
		if (mb_stripos($product['name'], 'Hurtigruten Expeditie Spitsbergen') !== false) {
			$this->properties['continent']		=	'Europa';
			$this->properties['country_code']	=	'SJ';
			$this->properties['country_name']	=	'Spitsbergen en Jan Mayen';
		}
	}
	
	private function validateCorendon(&$product) {
		if ($this->properties['descriptionlong']	==	$product['description']) {
			$this->properties['descriptionlong']	=	null;
		}
//		if ($this->properties['descriptionshort']	==	$product['description']) {
//			$this->properties['descriptionshort']	=	null;
//		}
		$product['description']	=	htmlspecialchars_decode($product['description']);
//		$this->properties['descriptionshort']		=	html_entity_decode(str_replace('&apos;', "'", $this->properties['descriptionshort']));
	}
	
	private function validateDeJongIntra() {
		$product['description']	=	null;
		//$this->properties['descriptionlong']	=	htmlspecialchars_decode($this->properties['descriptionlong']);
		$this->properties['duration']		=	$this->properties['numberofdays'];
		$this->properties['numberofdays']	=	null;
		
		if ($this->properties['durationtype']	==	'days') {
			$this->properties['duration_days']		=	$this->properties['duration'];
		}
		if ($this->properties['durationtype']	==	'nights') {
			$this->properties['duration_nights']	=	$this->properties['duration'];
		}
		
		$this->properties['usps']			=	$this->properties['uniquefeatures'];
		$this->properties['uniquefeatures']	=	null;
	}
	
	private function validateFital(&$product) {
		//$product['description']	=	html_entity_decode($product['description']);

		if ($this->properties['durationtype']	==	'dagen') {
			$this->properties['duration_days']		=	$this->properties['duration'];
		}
		if ($this->properties['durationtype']	==	'nachten') {
			$this->properties['duration_nights']	=	$this->properties['duration'];
		}
	}
	
	private function validateOceanwide() {
		$this->properties['departuredate']	=	$this->properties['arrivaldate'];
		$this->properties['arrivaldate']	=	null;
		
		$cabins	=	[];
		$cabins_prices	= explode('|', $this->properties['cabins_price']);
		$cabins_titles	= explode('|', $this->properties['cabins_title']);
		foreach ($cabins_titles as $key => $cabins_title) {
			$cabins[]	=	$cabins_title . ' (€' . $cabins_prices[$key] . ')';
		}
		$this->properties['extrainfo']	=	implode(', ', $cabins);
	}
	
	private function validateSunweb() {
		$this->properties['departuredate']	=	$this->properties['arrivaldate'];
		$this->properties['arrivaldate']	=	null;

		if ($this->properties['durationtype']	==	'days') {
			$this->properties['duration_days']		=	$this->properties['duration'];
		}
		if ($this->properties['durationtype']	==	'nights') {
			$this->properties['duration_nights']	=	$this->properties['duration'];
		}
	}
	
	private function validateDate(?string $unvalidatedDate): ?string {
		if (is_null($unvalidatedDate)) {
			return null;
		}
		
		$formats	=	[	'j-n-Y',	// Date format is    "D-M-YYYY" or
							'd-n-Y',	// Date format is   "DD-M-YYYY" or
							'j-m-Y',	// Date format is   "D-MM-YYYY" or
							'd-m-Y',	// Date format is  "DD-MM-YYYY" or
							'd-M-Y',	// Date format is "DD-MMM-YYYY" or
							'd/m/Y',	// Date format is  "DD/MM/YYYY" or
							'm/d/Y',	// Date format is  "MM/DD/YYYY" or
							'Y-m-d',	// Date format is  "YYYY-MM-DD" or
							'Ymd',		// Date format is    "YYYYMMDD"
						];
		$date		=	null;
		foreach ($formats as $format) {
			$date = DateTime::createFromFormat($format, mb_substr($unvalidatedDate, 0, 10));
			if ($date) {
				break;
			}
		}
		($date)	?	$validatedDate = $date->format('Y-m-d')
				:	$validatedDate = null;
				
		return $validatedDate;
	}
	
	private function validateContinent(): void {
		switch ($this->properties['continent'] ?? null) {
			case 'Antarctica':
				break;
			case 'Arctis':
				switch ($this->properties['city']) {
					case 'Akureyri':
						$this->properties['continent']	=	'Europa';
						break;
					case 'Constable Pynt':
						$this->properties['continent']	=	'Noord-Amerika';
						break;
					case 'Keflavik':
						$this->properties['continent']	=	'Europa';
						break;
					case 'Longyearbyen':
						$this->properties['continent']	=	'Europa';
						break;
					default:
				}
				break;
			case 'Europa':
				break;
			default:
				switch ($this->properties['country'] ?? null) {
					case 'Bahrein|Qatar|Verenigde Arabische Emiraten':
					case 'Egypte':
					case 'Gran Canaria':
					case 'Kaapverdië|Spanje':
					case 'Lanzarote':
					case 'Mauritius':
					case 'Tenerife':
					case 'Zuid-Afrika':
						$this->properties['continent']	=	'Afrika';
						break;
					case 'Bahrein':
					case 'China':
					case 'Hong Kong':
					case 'Indonesië':
					case 'Indonesië|Maleisië|Singapore':
					case 'Japan':
					case 'Maleisië':
					case 'Oman|Verenigde Arabische Emiraten':
					case 'Qatar':
					case 'Singapore':
					case 'Taiwan':
					case 'Thailand':
					case 'Verenigde Arabische Emiraten':
					case 'Zuid-Korea':
						$this->properties['continent']	=	'Azië';
						break;
					case 'België':
					case 'België|Duitsland|Frankrijk|Nederland|Verenigd Koninkrijk':
					case 'Cyprus|Griekenland':
					case 'Cyprus|Griekenland|Turkije':
					case 'Denemarken':
					case 'Denemarken|Duitsland|Estland|Finland|Nederland|Noorwegen|Verenigd Koninkrijk|Zweden':
					case 'Denemarken|Duitsland|Estland|Finland|Nederland|Noorwegen|Zweden':
					case 'Denemarken|Duitsland|Estland|Finland|Nederland|Polen|Zweden':
					case 'Denemarken|Duitsland|IJsland|Noorwegen|Spitsbergen':
					case 'Denemarken|Duitsland|Noorwegen':
					case 'Denemarken|Estland|Finland|Nederland|Noorwegen|Zweden':
					case 'Denemarken|Nederland|Noorwegen':
					case 'Dubrovnik':
					case 'Duitsland':
					case 'Duitsland|Hongarije|Oostenrijk|Slowakije':
					case 'Duitsland|IJsland|Verenigd Koninkrijk':
					case 'Duitsland|Noorwegen':
					case 'Engeland':
					case 'Faeröer Eilanden|Groenland|IJsland|Nederland|Noorwegen|Verenigd Koninkrijk':
					case 'Finland':
					case 'Frankrijk':
					case 'Frankrijk|Gibraltar|Nederland|Portugal|Spanje|Verenigd Koninkrijk':
					case 'Frankrijk|Griekenland|Italië|Malta':
					case 'Frankrijk|Italië|Malta|Spanje':
					case 'Frankrijk|Italië|Portugal|Spanje':
					case 'Frankrijk|Italië|Spanje':
					case 'Gibraltar|Marokko|Portugal|Spanje':
					case 'Gibraltar|Marokko|Spanje':
					case 'Gibraltar|Spanje':
					case 'Great Britain':
					case 'Griekenland':
					case 'Griekenland|Italië':
					case 'Griekenland|Italië|Kroatië':
					case 'Griekenland|Italië|Malta':
					case 'Griekenland|Italië|Malta|Portugal|Spanje|Tunesië':
					case 'Griekenland|Italië|Montenegro':
					case 'Griekenland|Italië|Spanje|Turkije':
					case 'Griekenland|Italië|Turkije':
					case 'Griekenland|Turkije':
					case 'Groot-Brittannië':
					case 'Hurtigruten':
					case 'Ierland|IJsland|Nederland':
					case 'Ierland|IJsland|Nederland|Verenigd Koninkrijk':
					case 'Ierland|Isle of Man|Nederland|Verenigd Koninkrijk':
					case 'Ierland|Nederland|Verenigd Koninkrijk':
					case 'IJsland':
					case 'IJsland|Nederland|Noorwegen':
					case 'IJsland|Nederland|Verenigd Koninkrijk':
					case 'Italië':
					case 'Italië|Spanje':
					case 'Kroatië':
					case 'Madeira':
					case 'Malta':
					case 'Marokko|Spanje':
					case 'Marokko|Portugal|Spanje':
					case 'Monaco':
					case 'Nederland':
					case 'Nederland|Noorwegen':
					case 'Nederland|Noorwegen|Verenigd Koninkrijk':
					case 'Nederland|Portugal|Spanje|Verenigd Koninkrijk':
					case 'Noorwegen':
					case 'Oostenrijk':
					case 'Palermo':
					case 'Portugal':
					case 'Portugal|Spanje':
					case 'Sardinië':
					case 'Sicily':
					case 'Spanje':
					case 'Turkije':
					case 'Venetië':
					case 'Zweden':
					case 'Zwitserland':
						$this->properties['continent']	=	'Europa';
						break;
					case 'Alaska':
					case 'Amerikaanse Maagdeneilanden|Bahamas|Britse Maagdeneilanden|Dominicaanse Republiek|Verenigde Staten':
					case 'Amerikaanse Maagdeneilanden|Bahamas|St Maarten|Verenigde Staten':
					case 'Antigua en Barbuda':
					case 'Antigua en Barbuda|Aruba|Barbados|Bonaire|Britse Maagdeneilanden|Curacao|Dominicaanse Republiek|Grenada|St Kitts & Nevis|St Lucia|St Maarten|Trinidad & Tobago':
					case 'Aruba|Bonaire|Britse Maagdeneilanden|Curacao|Dominicaanse Republiek|St Kitts & Nevis|St Lucia':
					case 'Aruba|Bonaire|Curacao|Verenigde Staten':
					case 'Aruba|Colombia|Costa Rica|Honduras|Jamaica|Panama|Verenigde Staten':
					case 'Aruba|Curacao|Jamaica|Kaaiman Eilanden|Mexico|Verenigde Staten':
					case 'Bahamas|Belize|Honduras|Mexico|Verenigde Staten':
					case 'Bahamas|Dominicaanse Republiek|Puerto Rico|Verenigde Staten':
					case 'Bahamas|Haïti|Jamaica|Verenigde Staten':
					case 'Bahamas|Honduras|Mexico|Verenigde Staten':
					case 'Bahamas|Jamaica|Kaaiman Eilanden|Mexico|Verenigde Staten':
					case 'Bahamas|Puerto Rico|St Maarten|Verenigde Staten':
					case 'Bahamas|Verenigde Staten':
					case 'Barbados':
					case 'Barbados|Britse Maagdeneilanden|Dominicaanse Republiek|Guadeloupe|St Kitts & Nevis|St Vincent en de Grenadines':
					case 'Bermuda':
					case 'Canada':
					case 'Canada|Verenigde Staten':
					case 'Curaçao':
					case 'Dominicaanse Republiek':
					case 'Dominicaanse Republiek|Turks en Caicos Eilanden':
					case 'FL USA':
					case 'Florida':
					case 'Groenland|IJsland':
					case 'Guadeloupe':
					case 'Los Angeles':
					case 'Martinique':
					case 'Panama':
					case 'Puerto Rico':
					case 'Sint Maarten':
					case 'USA':
					case 'Verenigde Staten':
						$this->properties['continent']	=	'Noord-Amerika';
						break;
					case 'Australië':
					case 'Fiji':
					case 'Frans-Polynesië':
					case 'Nieuw-Zeeland':
						$this->properties['continent']	=	'Oceanië';
						break;
					case 'Argentinië':
					case 'Brazilië':
					case 'Chile':
					case 'Colombia':
					case 'Peru':
					case 'Uruguay':
						$this->properties['continent']	=	'Zuid-Amerika';
						break;
					default:
				}
		}
	}
	
	private function validateCountry(): void {
		if (($pos = mb_strpos($this->properties['country'] ?? null, '|')) !== false) {
			$this->properties['country_name'] = mb_substr($this->properties['country'], 0, $pos);
		} else {
			$this->properties['country_name'] = $this->properties['country'] ?? null;
		}
		switch ($this->properties['country_name']) {
			case 'Verenigde Arabische Emiraten':
				$this->properties['country_code'] = 'AE';
				$this->properties['country_name'] = 'Verenigde Arabische Emiraten';
				break;
			case 'Antigua en Barbuda':
				$this->properties['country_code'] = 'AG';
				$this->properties['country_name'] = 'Antigua en Barbuda';
				break;
			case 'Antarctica':
				$this->properties['country_code'] = 'AQ';
				$this->properties['country_name'] = 'Antarctica';
				break;
			case 'Argentinië':
				$this->properties['country_code'] = 'AR';
				$this->properties['country_name'] = 'Argentinië';
				break;
			case 'Oostenrijk':
				$this->properties['country_code'] = 'AT';
				$this->properties['country_name'] = 'Oostenrijk';
				break;
			case 'Australië':
				$this->properties['country_code'] = 'AU';
				$this->properties['country_name'] = 'Australië';
				break;
			case 'Aruba':
				$this->properties['country_code'] = 'AW';
				$this->properties['country_name'] = 'Aruba';
				break;
			case 'Barbados':
				$this->properties['country_code'] = 'BB';
				$this->properties['country_name'] = 'Barbados';
				break;
			case 'België':
				$this->properties['country_code'] = 'BE';
				$this->properties['country_name'] = 'België';
				break;
			case 'Bahrein':
				$this->properties['country_code'] = 'BH';
				$this->properties['country_name'] = 'Bahrein';
				break;
			case 'Bermuda':
				$this->properties['country_code'] = 'BM';
				$this->properties['country_name'] = 'Bermuda';
				break;
			case 'Brazilië':
				$this->properties['country_code'] = 'BR';
				$this->properties['country_name'] = 'Brazilië';
				break;
			case 'Bahamas':
				$this->properties['country_code'] = 'BS';
				$this->properties['country_name'] = 'Bahamas';
				break;
			case 'Canada':
				$this->properties['country_code'] = 'CA';
				$this->properties['country_name'] = 'Canada';
				break;
			case 'Zwitserland':
				$this->properties['country_code'] = 'CH';
				$this->properties['country_name'] = 'Zwitserland';
				break;
			case 'Chile':
				$this->properties['country_code'] = 'CL';
				$this->properties['country_name'] = 'Chili';
				break;
			case 'China':
				$this->properties['country_code'] = 'CN';
				$this->properties['country_name'] = 'China';
				break;
			case 'Colombia':
				$this->properties['country_code'] = 'CO';
				$this->properties['country_name'] = 'Colombia';
				break;
			case 'Kaapverdië':
				$this->properties['country_code'] = 'CV';
				$this->properties['country_name'] = 'Kaapverdië';
				break;
			case 'Curaçao':
				$this->properties['country_code'] = 'CW';
				$this->properties['country_name'] = 'Curaçao';
				break;
			case 'Cyprus':
				$this->properties['country_code'] = 'CY';
				$this->properties['country_name'] = 'Cyprus';
				break;
			case 'Duitsland':
				$this->properties['country_code'] = 'DE';
				$this->properties['country_name'] = 'Duitsland';
				break;
			case 'Faeröer Eilanden':
			case 'Denemarken':
				$this->properties['country_code'] = 'DK';
				$this->properties['country_name'] = 'Denemarken';
				break;
			case 'Dominicaanse Republiek':
				$this->properties['country_code'] = 'DO';
				$this->properties['country_name'] = 'Dominicaanse Republiek';
				break;
			case 'Egypte':
				$this->properties['country_code'] = 'EG';
				$this->properties['country_name'] = 'Egypte';
				break;
			case 'Gran Canaria':
			case 'Lanzarote':
			case 'Spanje':
				$this->properties['country_code'] = 'ES';
				$this->properties['country_name'] = 'Spanje';
				break;
			case 'Finland':
				$this->properties['country_code'] = 'FI';
				$this->properties['country_name'] = 'Finland';
				break;
			case 'Fiji':
				$this->properties['country_code'] = 'FJ';
				$this->properties['country_name'] = 'Fiji';
				break;
			case 'Frankrijk':
				$this->properties['country_code'] = 'FR';
				$this->properties['country_name'] = 'Frankrijk';
				break;
			case 'Engeland':
			case 'Gibraltar':
			case 'Great Britain':
			case 'Groot-Brittannië':
			case 'Tenerife':
				$this->properties['country_code'] = 'GB';
				$this->properties['country_name'] = 'Verenigd Koninkrijk';
				break;
			case 'Groenland':
				$this->properties['country_code'] = 'GL';
				$this->properties['country_name'] = 'Groenland';
				break;
			case 'Guadeloupe':
				$this->properties['country_code'] = 'GP';
				$this->properties['country_name'] = 'Guadeloupe';
				break;
			case 'Griekenland':
				$this->properties['country_code'] = 'GR';
				$this->properties['country_name'] = 'Griekenland';
				break;
			case 'Hong Kong':
				$this->properties['country_code'] = 'HK';
				$this->properties['country_name'] = 'Hong Kong';
				break;
			case 'Dubrovnik':
			case 'Kroatië':
				$this->properties['country_code'] = 'HR';
				$this->properties['country_name'] = 'Kroatië';
				break;
			case 'Indonesië':
				$this->properties['country_code'] = 'ID';
				$this->properties['country_name'] = 'Indonesië';
				break;
			case 'Ierland':
				$this->properties['country_code'] = 'IE';
				$this->properties['country_name'] = 'Ierland';
				break;
			case 'IJsland':
				$this->properties['country_code'] = 'IS';
				$this->properties['country_name'] = 'IJsland';
				break;
			case 'Italië':
			case 'Palermo':
			case 'Sardinië':
			case 'Sicily':
			case 'Venetië':
				$this->properties['country_code'] = 'IT';
				$this->properties['country_name'] = 'Italië';
				break;
			case 'Japan':
				$this->properties['country_code'] = 'JP';
				$this->properties['country_name'] = 'Japan';
				break;
			case 'Zuid-Korea':
				$this->properties['country_code'] = 'KR';
				$this->properties['country_name'] = 'Zuid-Korea';
				break;
			case 'Marokko':
				$this->properties['country_code'] = 'MA';
				$this->properties['country_name'] = 'Marokko';
				break;
			case 'Monaco':
				$this->properties['country_code'] = 'MC';
				$this->properties['country_name'] = 'Monaco';
				break;
			case 'Martinique':
				$this->properties['country_code'] = 'MQ';
				$this->properties['country_name'] = 'Martinique';
				break;
			case 'Malta':
				$this->properties['country_code'] = 'MT';
				$this->properties['country_name'] = 'Malta';
				break;
			case 'Mauritius':
				$this->properties['country_code'] = 'MU';
				$this->properties['country_name'] = 'Mauritius';
				break;
			case 'Maleisië':
				$this->properties['country_code'] = 'MY';
				$this->properties['country_name'] = 'Maleisië';
				break;
			case 'Nederland':
				$this->properties['country_code'] = 'NL';
				$this->properties['country_name'] = 'Nederland';
				break;
			case 'Hurtigruten':
			case 'Noorwegen':
				$this->properties['country_code'] = 'NO';
				$this->properties['country_name'] = 'Noorwegen';
				break;
			case 'Nieuw-Zeeland':
				$this->properties['country_code'] = 'NZ';
				$this->properties['country_name'] = 'Nieuw-Zeeland';
				break;
			case 'Oman':
				$this->properties['country_code'] = 'OM';
				$this->properties['country_name'] = 'Oman';
				break;
			case 'Panama':
				$this->properties['country_code'] = 'PA';
				$this->properties['country_name'] = 'Panama';
				break;
			case 'Peru':
				$this->properties['country_code'] = 'PE';
				$this->properties['country_name'] = 'Peru';
				break;
			case 'Frans-Polynesië':
				$this->properties['country_code'] = 'PF';
				$this->properties['country_name'] = 'Frans-Polynesië';
				break;
			case 'Puerto Rico':
				$this->properties['country_code'] = 'PR';
				$this->properties['country_name'] = 'Puerto Rico';
				break;
			case 'Madeira':
			case 'Portugal':
				$this->properties['country_code'] = 'PT';
				$this->properties['country_name'] = 'Portugal';
				break;
			case 'Zweden':
				$this->properties['country_code'] = 'SE';
				$this->properties['country_name'] = 'Zweden';
				break;
			case 'Singapore':
				$this->properties['country_code'] = 'SG';
				$this->properties['country_name'] = 'Singapore';
				break;
			case 'Sint Maarten':
				$this->properties['country_code'] = 'SX';
				$this->properties['country_name'] = 'Sint Maarten';
				break;
			case 'Thailand':
				$this->properties['country_code'] = 'TH';
				$this->properties['country_name'] = 'Thailand';
				break;
			case 'Turkije':
				$this->properties['country_code'] = 'TR';
				$this->properties['country_name'] = 'Turkije';
				break;
			case 'Taiwan':
				$this->properties['country_code'] = 'TW';
				$this->properties['country_name'] = 'Taiwan';
				break;
			case 'Qatar':
				$this->properties['country_code'] = 'QA';
				$this->properties['country_name'] = 'Qatar';
				break;
			case 'Alaska':
			case 'Amerikaanse Maagdeneilanden':
			case 'FL USA':
			case 'Florida':
			case 'Los Angeles':
			case 'USA':
			case 'Verenigde Staten':
				$this->properties['country_code'] = 'US';
				$this->properties['country_name'] = 'Verenigde Staten';
				break;
			case 'Uruguay':
				$this->properties['country_code'] = 'UY';
				$this->properties['country_name'] = 'Uruguay';
				break;
			case 'Zuid-Afrika':
				$this->properties['country_code'] = 'ZA';
				$this->properties['country_name'] = 'Zuid-Afrika';
				break;
			default:
				switch ($this->properties['city']) {
					case 'Constable Pynt':
						$this->properties['country_code'] = 'GL';
						$this->properties['country_name'] = 'Groenland';
						break;
					case 'Akureyri':
					case 'Keflavik':
						$this->properties['country_code'] = 'IS';
						$this->properties['country_name'] = 'IJsland';
						break;
					case 'Longyearbyen':
						$this->properties['country_code'] = 'SJ';
						$this->properties['country_name'] = 'Spitsbergen';
						break;
					default:
				}
		}
	}

	private function validateCruiseLineCategory($product): void {

		// Initialize cruiseline category
		$this->properties['cruiseline_category']		=	null;
		$this->properties['cruiseline_subcategory']		=	null;

		// Initialize cruiseline subcategories
		$this->properties['minicruise']					= 0;
		$this->properties['rivercruise']				= 0;
		$this->properties['rivercruise_danube']			= 0;
		$this->properties['rivercruise_douro']			= 0;
		$this->properties['rivercruise_moselle']		= 0;
		$this->properties['rivercruise_nile']			= 0;
		$this->properties['rivercruise_rhine']			= 0;
		$this->properties['rivercruise_wolga']			= 0;
		$this->properties['seacruise']					= 0;
		$this->properties['seacruise_antarctic']		= 0;
		$this->properties['seacruise_arctic']			= 0;
		$this->properties['seacruise_bluecruise']		= 0;
		$this->properties['seacruise_caribbean']		= 0;
		$this->properties['seacruise_hurtigruten']		= 0;
		$this->properties['seacruise_mediterranean']	= 0;
		$this->properties['seacruise_sailing']			= 0;
		$this->properties['seacruise_world']			= 0;
		
		// Determine cruiseline category by holiday category
		if (mb_stripos($product['categories'], 'riviercruise')		!== false) {
			$this->properties['cruiseline_category']	=	'riviercruise';
			$this->properties['rivercruise']			=	1;
			$this->properties['seacruise']				=	0;
		} elseif (mb_stripos($product['categories'], 'minicruise')	!== false) {
			$this->properties['cruiseline_category']	=	'minicruise';
		} elseif (mb_stripos($product['categories'], 'hurtigruten')	!== false) {
			$this->properties['cruiseline_category']	=	'zeecruise';
		} else {
			// Determine cruiseline category by cruiseline name
			switch ($this->properties['cruiseline_name']) {
				case 'AIDA Cruises':
				case 'Azamara':
				case 'Carnival Cruise Lines':
				case 'Celebrity Cruises':
				case 'Costa Cruises':
				case 'Cunard Line':
				case 'Disney Cruise Line':
				case 'Hapag-Lloyd Cruises':
				case 'Holland America Line':
				case 'Hurtigruten':
				case 'Marella Cruises':
				case 'MSC Cruises':
				case 'Norwegian Cruise Line':
				case 'Oceania Cruises':
				case 'Oceanwide Expeditions':
				case 'P&O Cruises':
				case 'PONANT':
				case 'Princess Cruises':
				case 'Regent Seven Seas Cruises':
				case 'Royal Caribbean':
				case 'Seabourn':
				case 'SeaDream Yacht Club':
				case 'Silversea Cruises':
				case 'Star Clippers':
				case 'TUI Cruises':
					$this->properties['cruiseline_category']	=	'zeecruise';
					$this->properties['seacruise']				=	1;
					break;
				case 'AmaWaterways':
				case 'Crucemundo':
				case 'Crystal River Cruises':
				case 'DCS Touristik':
				case 'Dutch Cruise Line':
				case 'Feenstra Rijn Lijn':
				case 'Nicko Cruises':
				case 'Phoenix Reisen':
				case 'Uniworld':
				case 'Viva Cruises':
					$this->properties['cruiseline_category']	=	'riviercruise';
					$this->properties['rivercruise']			=	1;
					break;
				default:
					if (mb_stripos($product['name'], 'adriatic cruise')	!== false	||
						mb_stripos($product['name'], 'blue cruise')		!== false	||
						mb_stripos($product['name'], 'hurtigruten')		!== false	||
						$product['merchant_id']							==	'12263') {
						$this->properties['cruiseline_category']	=	'zeecruise';
						$this->properties['seacruise']				=	1;
					} elseif (mb_stripos($product['name'], 'fietscruise')		!== false) {
						$this->properties['cruiseline_category']	=	'riviercruise';
						$this->properties['rivercruise']			=	1;
					} else {
						$this->properties['cruiseline_category']	=	'zeecruise';
						$this->properties['seacruise']				=	1;
					}
			}
		}
		
		// Determine cruiseline subcategory by cruiseline name
		if (stripos($this->properties['cruiseline_name'], 'Star Clippers') 
															!== FALSE) {
			$this->properties['seacruise_sailing']			= 1;
		}
		
		// Determine cruiseline subcategory by product name
		if (stripos($product['name'], 'kerstmarktcruise')	!== FALSE	||
			stripos($product['name'], 'riviercruise')		!== FALSE) {
			$this->properties['rivercruise']				= 1;
		}
		if (stripos($product['name'], 'donau')				!== FALSE	||
			stripos($product['name'], 'vanuit passau')		!== FALSE) {
			$this->properties['rivercruise_danube']			= 1;
			$this->properties['rivercruise']				= 1;
		}
		if (stripos($product['name'], 'douro')				!== FALSE) {
			$this->properties['rivercruise_douro']			= 1;
			$this->properties['rivercruise']				= 1;
		}
		if (stripos($product['name'], 'moezel')				!== FALSE) {
			$this->properties['rivercruise_moselle']		= 1;
			$this->properties['rivercruise']				= 1;
		}
		if (stripos($product['name'], 'nijl') 				!== FALSE) {
			$this->properties['rivercruise_nile']			= 1;
			$this->properties['rivercruise']				= 1;
		}
		if (stripos($product['name'], 'rijn')				!== FALSE	||
			stripos($product['name'], 'vanuit basel')		!== FALSE	||
			stripos($product['name'], 'vanuit cologne')		!== FALSE	||
			stripos($product['name'], 'vanuit düsseldorf')	!== FALSE) {
			$this->properties['rivercruise_rhine']			= 1;
			$this->properties['rivercruise']				= 1;
		}
		if (stripos($product['name'], 'rhone') 				!== FALSE	||
			stripos($product['name'], 'rhône')				!== FALSE	||
			stripos($product['name'], 'saône')				!== FALSE) {
			$this->properties['rivercruise_rhone']			= 1;
			$this->properties['rivercruise']				= 1;
		}
		if (stripos($product['name'], 'seine') 				!== FALSE) {
			$this->properties['rivercruise_seine']			= 1;
			$this->properties['rivercruise']				= 1;
		}
		if (stripos($product['name'], 'wolga') 				!== FALSE) {
			$this->properties['rivercruise_volga']			= 1;
			$this->properties['rivercruise']				= 1;
		}
		if (stripos($product['name'], 'antarctica')			!== FALSE) {
			$this->properties['seacruise_antarctic']		= 1;
			$this->properties['seacruise']					= 1;
			$this->properties['cruiseline_subcategory']		= 'Zuidpoolgebied';
		}
		if (stripos($product['name'], 'blue cruise')		!== FALSE) {
			$this->properties['seacruise_bluecruise']		= 1;
			$this->properties['seacruise']					= 1;
		}
		if (stripos($product['name'], 'caribbean')			!== FALSE	||
			stripos($product['name'], 'caribische')			!== FALSE) {
			$this->properties['seacruise_caribbean']		= 1;
			$this->properties['seacruise']					= 1;
			$this->properties['cruiseline_subcategory']		= 'Caribisch zeegebied';
		}
		if (stripos($product['name'], 'hurtigruten')		!== FALSE) {
			$this->properties['seacruise_hurtigruten']		= 1;
		}
		if (stripos($product['name'], 'mediterraanse')		!== FALSE	||
			stripos($product['name'], 'mediterrane')		!== FALSE	||
			stripos($product['name'], 'middellandse zee')	!== FALSE) {
			$this->properties['seacruise_mediterranean']	= 1;
			$this->properties['seacruise']					= 1;
			$this->properties['cruiseline_subcategory']		= 'Middellandse zeegebied';
		}
		if (stripos($product['name'], 'minicruise')			!== FALSE) {
			$this->properties['minicruise']					= 1;
		}
		if (stripos($product['name'], 'wereldcruise')		!== FALSE) {
			$this->properties['seacruise_world']			= 1;
			$this->properties['seacruise']					= 1;
			$this->properties['cruiseline_subcategory']		= 'Wereldcruise';
		}
	}
	
	private function validateCruiseLineName($product): void {
		// Initialize cruiseline name
		$this->properties['cruiseline_name']		=	$this->properties['brand'] ?? null;

		// Standardize cruise line names
		if ($this->properties['cruiseline_name'] == 'Cunard') {
			$this->properties['cruiseline_name'] = 'Cunard Line';
		}
		
		// 
		if (isset($this->properties['cruiseline_name'])) {
			return;
		}
		
		// Determine cruiseline name by detection in product name or description
		$haystack	=	$product['name'] . ' ' . $product['description'];
/*
		foreach ($this->cruiselines as $id => $cruiseline) {
			if (mb_stripos($haystack, $cruiseline['name'])			!== FALSE	||
				!empty($cruiseline['short_name']) && mb_stripos($haystack, $cruiseline['short_name'])	!== FALSE) {
				$this->properties['cruiseline_id']		=	$id;
				$this->properties['cruiseline_name']	=	$cruiseline['name'];
			}
		}
*/
	
		if (mb_stripos($haystack, 'AIDA')					!== FALSE) {
			$this->properties['cruiseline_name']	=	'AIDA Cruises';
		}
		if (mb_stripos($haystack, 'Celebrity')				!== FALSE) {
			$this->properties['cruiseline_name']	=	'Celebrity Cruises';
		}
		if (mb_stripos($haystack, 'Costa ')					!== FALSE) {
			$this->properties['cruiseline_name']	=	'Costa Cruises';
		}
		if (mb_stripos($haystack, 'Feenstra ')				!== FALSE) {
			$this->properties['cruiseline_name']	=	'Feenstra Rijn Lijn';
		}
		if (mb_stripos($haystack, 'Hurtigruten ')			!== FALSE) {
			$this->properties['cruiseline_name']	=	'Hurtigruten';
		}
		if (mb_stripos($haystack, 'MSC')						!== FALSE) {
			$this->properties['cruiseline_name']	=	'MSC Cruises';
		}
		if (mb_stripos($haystack, 'Norwegian')				!== FALSE) {
			$this->properties['cruiseline_name']	=	'Norwegian Cruise Line';
		}

		// Determine cruiseline name by detection of cruiseship name in product name
		if (mb_stripos($haystack, 'MS Albertina')			!== FALSE	||
			mb_stripos($haystack, 'MS Crucestar')			!== FALSE	||
			mb_stripos($haystack, 'MS Crucevita')			!== FALSE	||
			mb_stripos($haystack, 'MS Douro Cruiser')		!== FALSE	||
			mb_stripos($haystack, 'MS Fidelio')				!== FALSE	||
			mb_stripos($haystack, 'MS Monarch Countess')		!== FALSE	||
			mb_stripos($haystack, 'MS Princess Isabella')	!== FALSE	||
			mb_stripos($haystack, 'MS Swiss Crystal')		!== FALSE	||
			mb_stripos($haystack, 'MS Swiss Splendor')		!== FALSE) {
			$this->properties['cruiseline_name']	=	'Crucemundo';
		}
		if (mb_stripos($haystack, 'DCS Amethyst 1')			!== FALSE	||
			mb_stripos($haystack, 'DCS Amethyst 2')			!== FALSE) {
			$this->properties['cruiseline_name']	=	'DCS Touristik';
		}
		if (mb_stripos($haystack, 'Dutch Grace')			!== FALSE	||
			mb_stripos($haystack, 'MS Brahms')				!== FALSE	||
			mb_stripos($haystack, 'MS Dutch Empress')		!== FALSE	||
			mb_stripos($haystack, 'MS Dutch Grace')			!== FALSE	||
			mb_stripos($haystack, 'MS Dutch Largo')			!== FALSE	||
			mb_stripos($haystack, 'MS Dutch Symphonie')		!== FALSE	||
			mb_stripos($haystack, 'MS Dutch Symphony')		!== FALSE	||
			mb_stripos($haystack, 'MS Johannes Brahms')		!== FALSE	||
			mb_stripos($haystack, 'MS Princess')				!== FALSE) {
			$this->properties['cruiseline_name']	=	'Dutch Cruise Line';
		}
		if (mb_stripos($haystack, 'mps Horizon')				!== FALSE	||
			mb_stripos($haystack, 'mps Johann Strauss')		!== FALSE	||
			mb_stripos($haystack, 'mps Rembrandt van Rijn')	!== FALSE	||
			mb_stripos($haystack, 'mps Salvinia')			!== FALSE	||
			mb_stripos($haystack, 'mps Serenade 1')			!== FALSE	||
			mb_stripos($haystack, 'mps Serenade 2')			!== FALSE) {
			$this->properties['cruiseline_name']	=	'Feenstra Rijn Lijn';
		}
		if (mb_stripos($haystack, 'Holland America')			!== FALSE	||
			mb_stripos($haystack, 'Nieuw Statendam')			!== FALSE	||
			mb_stripos($haystack, 'Oosterdam')				!== FALSE	||
			mb_stripos($haystack, 'Westerdam')				!== FALSE	||
			mb_stripos($haystack, 'Zuiderdam')				!== FALSE) {
			$this->properties['cruiseline_name']	=	'Holland America Line';
		}
		if (mb_stripos($haystack, 'Marella Discovery')		!== FALSE	||
			mb_stripos($haystack, 'Marella Discovery 2')		!== FALSE) {
			$this->properties['cruiseline_name']	=	'Marella Cruises';
		}
		if (mb_stripos($haystack, 'Hondius')					!== FALSE) {
			$this->properties['cruiseline_name']	=	'Oceanwide Expeditions';
		}
		if (mb_stripos($haystack, 'Rhein Prinzessin')		!== FALSE) {
			$this->properties['cruiseline_name']	=	'Phoenix Reisen';
		}
		if (mb_stripos($haystack, 'MS De Amsterdam')		!== FALSE	||
			mb_stripos($haystack, 'MS De Holland')			!== FALSE	||
			mb_stripos($haystack, 'MS De Nassau')			!== FALSE	||
			mb_stripos($haystack, 'MS De Willemstad')		!== FALSE	||
			mb_stripos($haystack, 'MS Sir Winston')			!== FALSE) {
			$this->properties['cruiseline_name']	=	'River Cruises Holding';
		}
		if (mb_stripos($haystack, 'of the Seas')				!== FALSE	||
			mb_stripos($haystack, 'o t Seas')				!== FALSE) {
			$this->properties['cruiseline_name']	=	'Royal Caribbean';
		}
		if (mb_stripos($haystack, 'Mein Schiff')				!== FALSE) {
			$this->properties['cruiseline_name']	=	'TUI Cruises';
		}
	}
	
	private function validateCruiseShipName($product): void {
		// Initialize cruiseship name
		$this->properties['cruiseship_name']	=	$this->properties['ship_name'] ?? null;
		
		if (isset($this->properties['cruiseship_name'])) {
			return;
		}
		// Determine cruiseship name by  detection of cruiseship name in product name
		$haystack	=	$product['name'] . ' ' . $product['description'];

		// AIDA Cruises
		if (mb_stripos($haystack, 'AIDAbella')					!== FALSE) {
			$this->properties['cruiseship_name']	=	'AIDAbella';
		}
		if (mb_stripos($haystack, 'AIDAblu')					!== FALSE) {
			$this->properties['cruiseship_name']	=	'AIDAblu';
		}
		if (mb_stripos($haystack, 'AIDAcosma')					!== FALSE) {
			$this->properties['cruiseship_name']	=	'AIDAcosma';
		}
		if (mb_stripos($haystack, 'AIDAdiva')					!== FALSE) {
			$this->properties['cruiseship_name']	=	'AIDAdiva';
		}
		if (mb_stripos($haystack, 'AIDAluna')					!== FALSE) {
			$this->properties['cruiseship_name']	=	'AIDAluna';
		}
		if (mb_stripos($haystack, 'AIDAmar')					!== FALSE) {
			$this->properties['cruiseship_name']	=	'AIDAmar';
		}
		if (mb_stripos($haystack, 'AIDAnova')					!== FALSE) {
			$this->properties['cruiseship_name']	=	'AIDAnova';
		}
		if (mb_stripos($haystack, 'AIDAperla')					!== FALSE) {
			$this->properties['cruiseship_name']	=	'AIDAperla';
		}
		if (mb_stripos($haystack, 'AIDAprima')					!== FALSE) {
			$this->properties['cruiseship_name']	=	'AIDAprima';
		}
		if (mb_stripos($haystack, 'AIDAsol')					!== FALSE) {
			$this->properties['cruiseship_name']	=	'AIDAsol';
		}
		if (mb_stripos($haystack, 'AIDAstella')					!== FALSE) {
			$this->properties['cruiseship_name']	=	'AIDAstella';
		}
		
		// Celebrity Cruises
		if (mb_stripos($haystack, 'Celebrity Apex')				!== FALSE) {
			$this->properties['cruiseship_name']	=	'Celebrity Apex';
		}
		if (mb_stripos($haystack, 'Celebrity Ascent')			!== FALSE) {
			$this->properties['cruiseship_name']	=	'Celebrity Apex';
		}
		if (mb_stripos($haystack, 'Celebrity Beyond')			!== FALSE) {
			$this->properties['cruiseship_name']	=	'Celebrity Apex';
		}
		if (mb_stripos($haystack, 'Celebrity Constellation')	!== FALSE) {
			$this->properties['cruiseship_name']	=	'Celebrity Apex';
		}
		if (mb_stripos($haystack, 'Celebrity Eclipse')			!== FALSE) {
			$this->properties['cruiseship_name']	=	'Celebrity Apex';
		}
		if (mb_stripos($haystack, 'Celebrity Edge')				!== FALSE) {
			$this->properties['cruiseship_name']	=	'Celebrity Apex';
		}
		if (mb_stripos($haystack, 'Celebrity Equinox')			!== FALSE) {
			$this->properties['cruiseship_name']	=	'Celebrity Apex';
		}
		if (mb_stripos($haystack, 'Celebrity Flora')			!== FALSE) {
			$this->properties['cruiseship_name']	=	'Celebrity Apex';
		}
		if (mb_stripos($haystack, 'Celebrity Infinity')			!== FALSE) {
			$this->properties['cruiseship_name']	=	'Celebrity Apex';
		}
		if (mb_stripos($haystack, 'Celebrity Millenium')		!== FALSE) {
			$this->properties['cruiseship_name']	=	'Celebrity Apex';
		}
		if (mb_stripos($haystack, 'Celebrity Reflection')		!== FALSE) {
			$this->properties['cruiseship_name']	=	'Celebrity Apex';
		}
		if (mb_stripos($haystack, 'Celebrity Silhouette')		!== FALSE) {
			$this->properties['cruiseship_name']	=	'Celebrity Apex';
		}
		if (mb_stripos($haystack, 'Celebrity Solstice')			!== FALSE) {
			$this->properties['cruiseship_name']	=	'Celebrity Apex';
		}
		if (mb_stripos($haystack, 'Celebrity Summit')			!== FALSE) {
			$this->properties['cruiseship_name']	=	'Celebrity Apex';
		}
		if (mb_stripos($haystack, 'Celebrity Xcel')				!== FALSE) {
			$this->properties['cruiseship_name']	=	'Celebrity Apex';
		}

		// Costa Cruises

		// Crucemundo
		if (mb_stripos($haystack, 'MS Albertina')				!== FALSE) {
			$this->properties['cruiseship_name']	=	'MS Albertina';
		}
		if (mb_stripos($haystack, 'MS Crucestar')				!== FALSE) {
			$this->properties['cruiseship_name']	=	'MS Crucestar';
		}
		if (mb_stripos($haystack, 'MS Crucevita')				!== FALSE) {
			$this->properties['cruiseship_name']	=	'MS Crucevita';
		}
		if (mb_stripos($haystack, 'MS Douro Cruiser')			!== FALSE) {
			$this->properties['cruiseship_name']	=	'MS Douro Cruiser';
		}
		if (mb_stripos($haystack, 'MS Fidelio')					!== FALSE) {
			$this->properties['cruiseship_name']	=	'MS Fidelio';
		}
		if (mb_stripos($haystack, 'MS Monarch Countess')		!== FALSE) {
			$this->properties['cruiseship_name']	=	'MS Monarch Countess';
		}
		if (mb_stripos($haystack, 'MS Princess Isabella')		!== FALSE) {
			$this->properties['cruiseship_name']	=	'MS Princess Isabella';
		}
		if (mb_stripos($haystack, 'MS Swiss Crystal')			!== FALSE) {
			$this->properties['cruiseship_name']	=	'MS Swiss Crystal';
		}
		if (mb_stripos($haystack, 'MS Swiss Splendor')			!== FALSE) {
			$this->properties['cruiseship_name']	=	'MS Swiss Splendor';
		}

		// DCS Touristik
		if (mb_stripos($haystack, 'DCS Amethyst 1')				!== FALSE) {
			$this->properties['cruiseship_name']	=	'DCS Amethyst 1';
		}
		if (mb_stripos($haystack, 'DCS Amethyst 2')				!== FALSE) {
			$this->properties['cruiseship_name']	=	'DCS Amethyst 2';
		}

		// Dutch Cruise Line
		if (mb_stripos($haystack, 'MS Dutch Empress')			!== FALSE) {
			$this->properties['cruiseship_name']	=	'MS Dutch Empress';
		}
		if (mb_stripos($haystack, 'Dutch Grace')				!== FALSE	||
			mb_stripos($haystack, 'MS Dutch Grace')				!== FALSE) {
			$this->properties['cruiseship_name']	=	'MS Dutch Grace';
		}
		if (mb_stripos($haystack, 'MS Dutch Largo')				!== FALSE) {
			$this->properties['cruiseship_name']	=	'MS Dutch Largo';
		}
		if (mb_stripos($haystack, 'MS Dutch Symphonie')			!== FALSE	||
			mb_stripos($haystack, 'MS Dutch Symphony')			!== FALSE) {
			$this->properties['cruiseship_name']	=	'MS Dutch Symphony';
		}
		if (mb_stripos($haystack, 'MS Brahms')					!== FALSE	||
			mb_stripos($haystack, 'MS Johannes Brahms')			!== FALSE) {
			$this->properties['cruiseship_name']	=	'MS Johannes Brahms';
		}
		if (mb_stripos($haystack, 'MS Princess')				!== FALSE) {
			$this->properties['cruiseship_name']	=	'MS Princess';
		}

		// Feenstra Rijn Lijn
		if (mb_stripos($haystack, 'mps Horizon')				!== FALSE) {
			$this->properties['cruiseship_name']	=	'mps Horizon';
		}
		if (mb_stripos($haystack, 'mps Johann Strauss')			!== FALSE) {
			$this->properties['cruiseship_name']	=	'mps Johann Strauss';
		}
		if (mb_stripos($haystack, 'mps Rembrandt van Rijn')		!== FALSE) {
			$this->properties['cruiseship_name']	=	'mps Rembrandt van Rijn';
		}
		if (mb_stripos($haystack, 'mps Salvinia')				!== FALSE) {
			$this->properties['cruiseship_name']	=	'mps Salvinia';
		}
		if (mb_stripos($haystack, 'mps Serenade 1')				!== FALSE) {
			$this->properties['cruiseship_name']	=	'mps Serenade 1';
		}
		if (mb_stripos($haystack, 'mps Serenade 2')				!== FALSE) {
			$this->properties['cruiseship_name']	=	'mps Serenade 2';
		}

		// Holland America Line
		if (mb_stripos($haystack, 'Nieuw Statendam')			!== FALSE) {
			$this->properties['cruiseship_name']	=	'Nieuw Statendam';
		}
		if (mb_stripos($haystack, 'Oosterdam')					!== FALSE) {
			$this->properties['cruiseship_name']	=	'Oosterdam';
		}
		if (mb_stripos($haystack, 'Westerdam')					!== FALSE) {
			$this->properties['cruiseship_name']	=	'Westerdam';
		}
		if (mb_stripos($haystack, 'Zuiderdam')					!== FALSE) {
			$this->properties['cruiseship_name']	=	'Zuiderdam';
		}
		
		// Marella Cruises
		if (mb_stripos($haystack, 'Marella Discovery')			!== FALSE) {
			$this->properties['cruiseship_name']	=	'Marella Discovery';
		}
		if (mb_stripos($haystack, 'Marella Discovery 2')		!== FALSE) {
			$this->properties['cruiseship_name']	=	'Marella Discovery 2';
		}
		
		// River Cruises Holding
		if (mb_stripos($haystack, 'MS De Amsterdam')			!== FALSE) {
			$this->properties['cruiseship_name']	=	'MS De Amsterdam';
		}
		if (mb_stripos($haystack, 'MS De Holland')				!== FALSE) {
			$this->properties['cruiseship_name']	=	'MS De Holland';
		}
		if (mb_stripos($haystack, 'MS De Nassau')				!== FALSE) {
			$this->properties['cruiseship_name']	=	'MS De Nassau';
		}
		if (mb_stripos($haystack, 'MS De Willemstad')			!== FALSE) {
			$this->properties['cruiseship_name']	=	'MS  De Willemstad';
		}
		if (mb_stripos($haystack, 'MS Sir Winston')				!== FALSE) {
			$this->properties['cruiseship_name']	=	'MS Sir Winston';
		}
		
		// TUI Cruises		
		if (mb_stripos($haystack, 'Mein Schiff 1')				!== FALSE) {
			$this->properties['cruiseship_name']	=	'Mein Schiff 1';
		}
		if (mb_stripos($haystack, 'Mein Schiff 2')				!== FALSE) {
			$this->properties['cruiseship_name']	=	'Mein Schiff 2';
		}
		if (mb_stripos($haystack, 'Mein Schiff 3')				!== FALSE) {
			$this->properties['cruiseship_name']	=	'Mein Schiff 3';
		}
		if (mb_stripos($haystack, 'Mein Schiff 4')				!== FALSE) {
			$this->properties['cruiseship_name']	=	'Mein Schiff 4';
		}
		if (mb_stripos($haystack, 'Mein Schiff 5')				!== FALSE) {
			$this->properties['cruiseship_name']	=	'Mein Schiff 5';
		}
		if (mb_stripos($haystack, 'Mein Schiff 6')				!== FALSE) {
			$this->properties['cruiseship_name']	=	'Mein Schiff 6';
		}
		if (mb_stripos($haystack, 'Mein Schiff 7')				!== FALSE) {
			$this->properties['cruiseship_name']	=	'Mein Schiff 7';
		}
		if (mb_stripos($haystack, 'Mein Schiff Flow')			!== FALSE) {
			$this->properties['cruiseship_name']	=	'Mein Schiff Flow';
		}
		if (mb_stripos($haystack, 'Mein Schiff Relax')			!== FALSE) {
			$this->properties['cruiseship_name']	=	'Mein Schiff Relax';
		}

		// MSC Cruises
		if (mb_stripos($haystack, 'MSC Armonia')				!== FALSE) {
			$this->properties['cruiseship_name']	=	'MSC Armonia';
		}
		if (mb_stripos($haystack, 'MSC Bellissima')				!== FALSE) {
			$this->properties['cruiseship_name']	=	'MSC Bellissima';
		}
		if (mb_stripos($haystack, 'MSC Divina')					!== FALSE) {
			$this->properties['cruiseship_name']	=	'MSC Divina';
		}
		if (mb_stripos($haystack, 'MSC Euribia')				!== FALSE) {
			$this->properties['cruiseship_name']	=	'MSC Euribia';
		}
		if (mb_stripos($haystack, 'MSC Fantasia')				!== FALSE) {
			$this->properties['cruiseship_name']	=	'MSC Fantasia';
		}
		if (mb_stripos($haystack, 'MSC Grandiosa')				!== FALSE) {
			$this->properties['cruiseship_name']	=	'MSC Grandiosa';
		}
		if (mb_stripos($haystack, 'MSC Lirica')					!== FALSE) {
			$this->properties['cruiseship_name']	=	'MSC Lirica';
		}
		if (mb_stripos($haystack, 'MSC Magnifica')				!== FALSE) {
			$this->properties['cruiseship_name']	=	'MSC Magnifica';
		}
		if (mb_stripos($haystack, 'MSC Meraviglia')				!== FALSE) {
			$this->properties['cruiseship_name']	=	'MSC Meraviglia';
		}
		if (mb_stripos($haystack, 'MSC Musica')					!== FALSE) {
			$this->properties['cruiseship_name']	=	'MSC Musica';
		}
		if (mb_stripos($haystack, 'MSC Opera')					!== FALSE) {
			$this->properties['cruiseship_name']	=	'MSC Opera';
		}
		if (mb_stripos($haystack, 'MSC Orchestra')				!== FALSE) {
			$this->properties['cruiseship_name']	=	'MSC Orchestra';
		}
		if (mb_stripos($haystack, 'MSC Poesia')					!== FALSE) {
			$this->properties['cruiseship_name']	=	'MSC Poesia';
		}
		if (mb_stripos($haystack, 'MSC Preziosa')				!== FALSE) {
			$this->properties['cruiseship_name']	=	'MSC Preziosa';
		}
		if (mb_stripos($haystack, 'MSC Seascape')				!== FALSE) {
			$this->properties['cruiseship_name']	=	'MSC Seascape';
		}
		if (mb_stripos($haystack, 'MSC Seashore')				!== FALSE) {
			$this->properties['cruiseship_name']	=	'MSC Seashore';
		}
		if (mb_stripos($haystack, 'MSC Seaside')				!== FALSE) {
			$this->properties['cruiseship_name']	=	'MSC Seaside';
		}
		if (mb_stripos($haystack, 'MSC Seaview')				!== FALSE) {
			$this->properties['cruiseship_name']	=	'MSC Seaview';
		}
		if (mb_stripos($haystack, 'MSC Sinfonia')				!== FALSE) {
			$this->properties['cruiseship_name']	=	'MSC Sinfonia';
		}
		if (mb_stripos($haystack, 'MSC Splendida')				!== FALSE) {
			$this->properties['cruiseship_name']	=	'MSC Splendida';
		}
		if (mb_stripos($haystack, 'MSC Virtuosa')				!== FALSE) {
			$this->properties['cruiseship_name']	=	'MSC Virtuosa';
		}
		if (mb_stripos($haystack, 'MSC World America')			!== FALSE) {
			$this->properties['cruiseship_name']	=	'MSC World America';
		}
		if (mb_stripos($haystack, 'MSC World Asia')				!== FALSE) {
			$this->properties['cruiseship_name']	=	'MSC World Asia';
		}
		if (mb_stripos($haystack, 'MSC World Europa')			!== FALSE) {
			$this->properties['cruiseship_name']	=	'MSC World Europa';
		}

		// Norwegian Cruise Line
		if (mb_stripos($haystack, 'Norwegian Aqua')				!== FALSE) {
			$this->properties['cruiseship_name']	=	'Norwegian Aqua';
		}
		if (mb_stripos($haystack, 'Norwegian Bliss')			!== FALSE) {
			$this->properties['cruiseship_name']	=	'Norwegian Bliss';
		}
		if (mb_stripos($haystack, 'Norwegian Breakaway')		!== FALSE) {
			$this->properties['cruiseship_name']	=	'Norwegian Breakaway';
		}
		if (mb_stripos($haystack, 'Norwegian Dawn')				!== FALSE) {
			$this->properties['cruiseship_name']	=	'Norwegian Dawn';
		}
		if (mb_stripos($haystack, 'Norwegian Encore')			!== FALSE) {
			$this->properties['cruiseship_name']	=	'Norwegian Encore';
		}
		if (mb_stripos($haystack, 'Norwegian Epic')				!== FALSE) {
			$this->properties['cruiseship_name']	=	'Norwegian Epic';
		}
		if (mb_stripos($haystack, 'Norwegian Escape')			!== FALSE) {
			$this->properties['cruiseship_name']	=	'Norwegian Escape';
		}
		if (mb_stripos($haystack, 'Norwegian Gem')				!== FALSE) {
			$this->properties['cruiseship_name']	=	'Norwegian Gem';
		}
		if (mb_stripos($haystack, 'Norwegian Getaway')			!== FALSE) {
			$this->properties['cruiseship_name']	=	'Norwegian Getaway';
		}
		if (mb_stripos($haystack, 'Norwegian Jade')				!== FALSE) {
			$this->properties['cruiseship_name']	=	'Norwegian Jade';
		}
		if (mb_stripos($haystack, 'Norwegian Jewel')			!== FALSE) {
			$this->properties['cruiseship_name']	=	'Norwegian Jewel';
		}
		if (mb_stripos($haystack, 'Norwegian Joy')				!== FALSE) {
			$this->properties['cruiseship_name']	=	'Norwegian Joy';
		}
		if (mb_stripos($haystack, 'Norwegian Luna')				!== FALSE) {
			$this->properties['cruiseship_name']	=	'Norwegian Luna';
		}
		if (mb_stripos($haystack, 'Norwegian Pearl')			!== FALSE) {
			$this->properties['cruiseship_name']	=	'Norwegian Pearl';
		}
		if (mb_stripos($haystack, 'Norwegian Prima')			!== FALSE) {
			$this->properties['cruiseship_name']	=	'Norwegian Prima';
		}
		if (mb_stripos($haystack, 'Norwegian Sky')				!== FALSE) {
			$this->properties['cruiseship_name']	=	'Norwegian Sky';
		}
		if (mb_stripos($haystack, 'Norwegian Spirit')			!== FALSE) {
			$this->properties['cruiseship_name']	=	'Norwegian Spirit';
		}
		if (mb_stripos($haystack, 'Norwegian Star')				!== FALSE) {
			$this->properties['cruiseship_name']	=	'Norwegian Star';
		}
		if (mb_stripos($haystack, 'Norwegian Sun')				!== FALSE) {
			$this->properties['cruiseship_name']	=	'Norwegian Sun';
		}
		if (mb_stripos($haystack, 'Norwegian Viva')				!== FALSE) {
			$this->properties['cruiseship_name']	=	'Norwegian Viva';
		}
		if (mb_stripos($haystack, 'Pride of America')			!== FALSE) {
			$this->properties['cruiseship_name']	=	'Pride of America';
		}

		// Oceanwide Expeditions
		if (mb_strpos($haystack, 'Hondius')						!== FALSE) {
			$this->properties['cruiseship_name']	=	'Hondius';
		}
		
		// Phoenix Reisen
		if (mb_strpos($haystack, 'Rhein Prinzessin')			!== FALSE) {
			$this->properties['cruiseship_name']	=	'Rhein Prinzessin';
		}

		// Royal Caribbean
		if (mb_strpos($haystack, 'Allure of the Seas')			!== FALSE) {
			$this->properties['cruiseship_name']	=	'Allure of the Seas';
		}
		if (mb_strpos($haystack, 'Brilliance o t Seas')			!== FALSE) {
			$this->properties['cruiseship_name']	=	'Brilliance of the Seas';
		}
		if (mb_strpos($haystack, 'Icon of the Seas')			!== FALSE) {
			$this->properties['cruiseship_name']	=	'Icon of the Seas';
		}
		if (mb_strpos($haystack, 'Odyssey of the Seas')			!== FALSE) {
			$this->properties['cruiseship_name']	=	'Odyssey of the Seas';
		}
		if (mb_strpos($haystack, 'Star of the Seas')			!== FALSE) {
			$this->properties['cruiseship_name']	=	'Star of the Seas';
		}
		if (mb_strpos($haystack, 'Symphony of the Seas')		!== FALSE) {
			$this->properties['cruiseship_name']	=	'Symphony of the Seas';
		}
		if (mb_strpos($haystack, 'Voyager of the Seas')			!== FALSE) {
			$this->properties['cruiseship_name']	=	'Voyager of the Seas';
		}
	}

	private function transformAndSaveData($product): void {
		$data = [
			'merchant_id'								=> $product['merchant_id'],
			'productfeed_id'							=> $product['productfeed_id'],
			'product_id'								=> $product['product_id'],
			'campaign_id'								=> $product['campaign_id'],
			'name'										=> $product['name'],
			'currency'									=> $product['currency'],
			'price'										=> $product['price'],
			'url'										=> $product['url'],
			'images'									=> $product['images'],
			'description'								=> $product['description'],
			'categorypath'								=> $this->properties['categorypath']			?? null,
			'categories'								=> $product['categories'],
			'subcategories'								=> $this->properties['subcategories']			?? null,
			'subsubcategories'							=> $this->properties['subsubcategories']		?? null,
			'cruiseline_category'						=> $this->properties['cruiseline_category']		?? null,
			'cruiseline_subcategory'					=> $this->properties['cruiseline_subcategory']	?? null,
			'cruiseline_id'								=> $this->properties['cruiseline_id']			?? null,
			'cruiseline_name'							=> $this->properties['cruiseline_name']			?? null,
			'cruiseship_name'							=> $this->properties['cruiseship_name']			?? null,
			'slug'										=> $product['slug']								?? null,
			'accommodation_descriptionlong'				=> $this->properties['descriptionlong']			?? null,
			'accommodation_descriptionshort'			=> $this->properties['descriptionshort']		?? null,
			'accommodation_extrainfo'					=> $this->properties['extrainfo']				?? null,
			'accommodation_facilities'					=> $this->properties['facilities']				?? null,
			'accommodation_is_childrenallowed'			=> $this->properties['childrenallowed']			?? 0,
			'accommodation_rating'						=> $this->properties['rating']					?? null,
			'accommodation_stars'						=> $this->properties['stars']					?? null,
			'accommodation_type'						=> $this->properties['accommodationtype']		?? null,
			'accommodation_usps'						=> $this->properties['usps']					?? null,
			'destination_continent_name'				=> $this->properties['continent']				?? null,
			'destination_country_code'					=> $this->properties['country_code']			?? null,
			'destination_country_name'					=> $this->properties['country_name']			?? null,
			'destination_region_code'					=> null,	
			'destination_region_name'					=> $this->properties['region']					?? null,
			'destination_province_name'					=> $this->properties['province']				?? null,
			'destination_city_name'						=> $this->properties['city']					?? null,
			'destination_latitude'						=> $this->properties['latitude']				?? null,
			'destination_longitude'						=> $this->properties['longitude']				?? null,
			'holidaytype_is_all_inclusives'				=> $this->properties['all_inclusive']			?? 0,
			'holidaytype_is_lastminutes'				=> $this->properties['lastminute']				?? 0,
			'holidaytype_is_minicruise'					=> $this->properties['minicruise']				?? 0,
			'holidaytype_is_rivercruise'				=> $this->properties['rivercruise']				?? 0,
			'holidaytype_is_rivercruise_danube'			=> $this->properties['rivercruise_danube']		?? 0,
			'holidaytype_is_rivercruise_douro'			=> $this->properties['rivercruise_douro']		?? 0,
			'holidaytype_is_rivercruise_moselle'		=> $this->properties['rivercruise_moselle']		?? 0,
			'holidaytype_is_rivercruise_nile'			=> $this->properties['rivercruise_nile']		?? 0,
			'holidaytype_is_rivercruise_rhine'			=> $this->properties['rivercruise_rhine']		?? 0,
			'holidaytype_is_rivercruise_rhone'			=> $this->properties['rivercruise_rhone']		?? 0,
			'holidaytype_is_rivercruise_seine'			=> $this->properties['rivercruise_seine']		?? 0,
			'holidaytype_is_rivercruise_volga'			=> $this->properties['rivercruise_volga']		?? 0,
			'holidaytype_is_seacruise'					=> $this->properties['seacruise']				?? 0,
			'holidaytype_is_seacruise_antarctic'		=> $this->properties['seacruise_antarctic']		?? 0,
			'holidaytype_is_seacruise_arctic'			=> $this->properties['seacruise_arctic']		?? 0,
			'holidaytype_is_seacruise_bluecruise'		=> $this->properties['seacruise_bluecruise']	?? 0,
			'holidaytype_is_seacruise_caribbean'		=> $this->properties['seacruise_caribbean']		?? 0,
			'holidaytype_is_seacruise_hurtigruten'		=> $this->properties['seacruise_hurtigruten']	?? 0,
			'holidaytype_is_seacruise_mediterranean'	=> $this->properties['seacruise_mediterranean']	?? 0,
			'holidaytype_is_seacruise_sailing'			=> $this->properties['seacruise_sailing']		?? 0,
			'holidaytype_is_seacruise_world'			=> $this->properties['seacruise_world']			?? 0,
			'holidaytype_is_herfstvakantie'				=> 0,
			'holidaytype_is_kerstvakantie'				=> 0,
			'holidaytype_is_meivakantie'				=> 0,
			'holidaytype_is_voorjaarsvakantie'			=> 0,
			'holidaytype_is_zomervakantie'				=> 0,
			'offer_departure_airport'					=> $this->properties['departureairport']		?? null,
			'offer_departure_date'						=> $this->properties['departuredate']			?? null,
			'offer_duration'							=> $this->properties['duration']				?? null,
			'offer_duration_days'						=> $this->properties['duration_days']			?? null,
			'offer_duration_nights'						=> $this->properties['duration_nights']			?? null,
			'offer_duration_type'						=> $this->properties['durationtype']			?? null,
			'offer_excludedfromprice'					=> $this->properties['acm_costs']				?? null,
			'offer_flight_included'						=> $this->properties['flightincluded']			?? null,
			'offer_iata_arrival'						=> $this->properties['iataarrival']				?? null,
			'offer_iata_departure'						=> $this->properties['iatadeparture']			?? null,
			'offer_includedinprice'						=> $this->properties['includedinprice']			?? null,
			'offer_isocode_departure'					=> $this->properties['isocodedeparture']		?? null,
			'offer_price'								=> $this->properties['fromprice']				?? null,
			'offer_price_type'							=> $this->properties['pricetype']				?? null,
			'offer_service_type'						=> $this->properties['servicetype']				?? null,
			'offer_schoolholiday_id'					=> $this->properties['schoolholiday_id']		?? null,
			'offer_transport_type'						=> $this->properties['transporttype']			?? null
		];
		$this->db->insertData('affiliate_products_transformed_tradetracker', $data);
	}
}