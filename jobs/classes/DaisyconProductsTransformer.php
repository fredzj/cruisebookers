<?php

/**
 * Class DaisyconProductsTransformer
 * 
 * USAGE:
 * - Instantiate the class with the database configuration path.
 * - Call the `transform()` method to process and save the transformed data.
 * 
 * DEPENDENCIES:
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

class DaisyconProductsTransformer {
    private $db;
    private $dbConfigPath;
    private $log;
    private $timeStart;

    public function __construct($dbConfigPath) {
		$this->dbConfigPath  = $dbConfigPath;
        $this->log = new Log();
        $this->registerExitHandler();
		$this->connectDatabase();
        $this->truncateTable();
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
     * Truncates the relevant database table before importing new data.
     */
    private function truncateTable(): void {
		$this->db->truncate('affiliate_products_transformed_daisycon');
    }

	public function transform(): void {
		$sql			=	"
			INSERT INTO affiliate_products_transformed_daisycon
			SELECT
				NULL,
				NULL,
				merchant_id,
				productfeed_id,
				product_id,
				CASE
					WHEN merchant_id = 10764 THEN 'Cruises' -- cruisereizen.nl
					WHEN merchant_id = 17747 THEN 'Cruises' -- cruiseonline.com
					ELSE category -- oad.nl
				END AS category,
				CASE
					WHEN LOCATE('donau', title) > 0 THEN 'riviercruise'
					WHEN LOCATE('douro', title) > 0 THEN 'riviercruise'
					WHEN LOCATE('kerstmarktcruise', title) > 0 THEN 'riviercruise'
					WHEN LOCATE('moezel', title) > 0 THEN 'riviercruise'
					WHEN LOCATE('nijl', title) > 0 THEN 'riviercruise'
					WHEN LOCATE('rhone', title) > 0 THEN 'riviercruise'
					WHEN LOCATE('rhône', title) > 0 THEN 'riviercruise'
					WHEN LOCATE('rijn', title) > 0 THEN 'riviercruise'
					WHEN LOCATE('riviercruise', category) > 0 THEN 'riviercruise'
					WHEN LOCATE('riviercruise', title) > 0 THEN 'riviercruise'
					WHEN LOCATE('saône', title) > 0 THEN 'riviercruise'
					WHEN LOCATE('seine', title) > 0 THEN 'riviercruise'
					WHEN LOCATE('vanuit passau', title) > 0 THEN 'riviercruise'
					WHEN LOCATE('wolga', title) > 0 THEN 'riviercruise'
					WHEN travel_tour_operator = 'OAD' THEN 'riviercruise'
					ELSE 'zeecruise'
				END AS cruiseline_category,
				CASE
					WHEN travel_tour_operator <> 'OAD' AND LOCATE('antarctic', title) > 0 THEN 'Zuidpoolgebied'
					WHEN travel_tour_operator <> 'OAD' AND LOCATE('Caribbean', title) > 0 THEN 'Caribisch zeegebied'
					WHEN travel_tour_operator <> 'OAD' AND LOCATE('caribische', title) > 0 THEN 'Caribisch zeegebied'
					WHEN travel_tour_operator <> 'OAD' AND LOCATE('Middellandse Zee', destination_region) > 0 THEN 'Middellandse zeegebied'
					WHEN travel_tour_operator <> 'OAD' AND LOCATE('mediterraans', title) > 0 THEN 'Middellandse zeegebied'
					WHEN travel_tour_operator <> 'OAD' AND LOCATE('mediterrane', title) > 0 THEN 'Middellandse zeegebied'
					WHEN travel_tour_operator <> 'OAD' AND LOCATE('middellandse zee', title) > 0 THEN 'Middellandse zeegebied'
					WHEN travel_tour_operator <> 'OAD' AND LOCATE('Transatlantisch', title) > 0 THEN 'Transatlantische cruise'
					WHEN travel_tour_operator <> 'OAD' AND LOCATE('wereldcruise', title) > 0 THEN 'Wereldcruise'
					ELSE NULL
				END AS cruiseline_subcategory,
				NULL AS cruiseline_id,
				CASE
					WHEN LOCATE('TUI Cruises', travel_tour_operator ) > 0 THEN 'TUI Cruises'
					ELSE travel_tour_operator
				END AS cruiseline_name,
				CASE
					WHEN LOCATE('AIDAbella', title) > 0 THEN 'AIDAbella'
					WHEN LOCATE('AIDAblu', title) > 0 THEN 'AIDAblu'
					WHEN LOCATE('AIDAcosma', title) > 0 THEN 'AIDAcosma'
					WHEN LOCATE('AIDAdiva', title) > 0 THEN 'AIDAdiva'
					WHEN LOCATE('AIDAluna', title) > 0 THEN 'AIDAluna'
					WHEN LOCATE('AIDAmar', title) > 0 THEN 'AIDAmar'
					WHEN LOCATE('AIDAnova', title) > 0 THEN 'AIDAnova'
					WHEN LOCATE('AIDAperla', title) > 0 THEN 'AIDAperla'
					WHEN LOCATE('AIDAprima', title) > 0 THEN 'AIDAprima'
					WHEN LOCATE('AIDAsol', title) > 0 THEN 'AIDAsol'
					WHEN LOCATE('AIDAstella', title) > 0 THEN 'AIDAstella'
					WHEN LOCATE('Azamara Journey', title) > 0 THEN 'Azamara Journey'
					WHEN LOCATE('Azamara Onward', title) > 0 THEN 'Azamara Onward'
					WHEN LOCATE('Azamara Pursuit', title) > 0 THEN 'Azamara Pursuit'
					WHEN LOCATE('Azamara Quest', title) > 0 THEN 'Azamara Quest'
					WHEN LOCATE('Carnival Breeze', title) > 0 THEN 'Carnival Breeze'
					WHEN LOCATE('Carnival Celebration', title) > 0 THEN 'Carnival Celebration'
					WHEN LOCATE('Carnival Conquest', title) > 0 THEN 'Carnival Conquest'
					WHEN LOCATE('Carnival Dream', title) > 0 THEN 'Carnival Dream'
					WHEN LOCATE('Carnival Elation', title) > 0 THEN 'Carnival Elation'
					WHEN LOCATE('Carnival Firenze', title) > 0 THEN 'Carnival Firenze'
					WHEN LOCATE('Carnival Freedom', title) > 0 THEN 'Carnival Freedom'
					WHEN LOCATE('Carnival Glory', title) > 0 THEN 'Carnival Glory'
					WHEN LOCATE('Carnival Horizon', title) > 0 THEN 'Carnival Horizon'
					WHEN LOCATE('Carnival Jubilee', title) > 0 THEN 'Carnival Jubilee'
					WHEN LOCATE('Carnival Legend', title) > 0 THEN 'Carnival Legend'
					WHEN LOCATE('Carnival Liberty', title) > 0 THEN 'Carnival Liberty'
					WHEN LOCATE('Carnival Luminosa', title) > 0 THEN 'Carnival Luminosa'
					WHEN LOCATE('Carnival Magic', title) > 0 THEN 'Carnival Magic'
					WHEN LOCATE('Carnival Mardi Gras', title) > 0 THEN 'Carnival Mardi Gras'
					WHEN LOCATE('Carnival Miracle', title) > 0 THEN 'Carnival Miracle'
					WHEN LOCATE('Carnival Panorama', title) > 0 THEN 'Carnival Panorama'
					WHEN LOCATE('Carnival Paradise', title) > 0 THEN 'Carnival Paradise'
					WHEN LOCATE('Carnival Pride', title) > 0 THEN 'Carnival Pride'
					WHEN LOCATE('Carnival Radiance', title) > 0 THEN 'Carnival Radiance'
					WHEN LOCATE('Carnival Spirit', title) > 0 THEN 'Carnival Spirit'
					WHEN LOCATE('Carnival Splendor', title) > 0 THEN 'Carnival Splendor'
					WHEN LOCATE('Carnival Sunrise', title) > 0 THEN 'Carnival Sunrise'
					WHEN LOCATE('Carnival Sunshine', title) > 0 THEN 'Carnival Sunshine'
					WHEN LOCATE('Carnival Valor', title) > 0 THEN 'Carnival Valor'
					WHEN LOCATE('Carnival Venezia', title) > 0 THEN 'Carnival Venezia'
					WHEN LOCATE('Carnival Vista', title) > 0 THEN 'Carnival Vista'
					WHEN LOCATE('Celebrity Apex', title) > 0 THEN 'Celebrity Apex'
					WHEN LOCATE('Celebrity Ascent', title) > 0 THEN 'Celebrity Ascent'
					WHEN LOCATE('Celebrity Beyond', title) > 0 THEN 'Celebrity Beyond'
					WHEN LOCATE('Celebrity Constellation', title) > 0 THEN 'Celebrity Constellation'
					WHEN LOCATE('Celebrity Eclipse', title) > 0 THEN 'Celebrity Eclipse'
					WHEN LOCATE('Celebrity Edge', title) > 0 THEN 'Celebrity Edge'
					WHEN LOCATE('Celebrity Equinox', title) > 0 THEN 'Celebrity Equinox'
					WHEN LOCATE('Celebrity Flora', title) > 0 THEN 'Celebrity Flora'
					WHEN LOCATE('Celebrity Infinity', title) > 0 THEN 'Celebrity Infinity'
					WHEN LOCATE('Celebrity Millennium', title) > 0 THEN 'Celebrity Millennium'
					WHEN LOCATE('Celebrity Reflection', title) > 0 THEN 'Celebrity Reflection'
					WHEN LOCATE('Celebrity Silhouette', title) > 0 THEN 'Celebrity Silhouette'
					WHEN LOCATE('Celebrity Solstice', title) > 0 THEN 'Celebrity Solstice'
					WHEN LOCATE('Celebrity Summit', title) > 0 THEN 'Celebrity Summit'
					WHEN LOCATE('Celebrity Xcel', title) > 0 THEN 'Celebrity Xcel'
					WHEN LOCATE('Costa Deliziosa', title) > 0 THEN 'Costa Deliziosa'
					WHEN LOCATE('Costa Diadema', title) > 0 THEN 'Costa Diadema'
					WHEN LOCATE('Costa Fascinosa', title) > 0 THEN 'Costa Fascinosa'
					WHEN LOCATE('Costa Favolosa', title) > 0 THEN 'Costa Favolosa'
					WHEN LOCATE('Costa Fortuna', title) > 0 THEN 'Costa Fortuna'
					WHEN LOCATE('Costa Pacifica', title) > 0 THEN 'Costa Pacifica'
					WHEN LOCATE('Costa Smeralda', title) > 0 THEN 'Costa Smeralda'
					WHEN LOCATE('Costa Toscana', title) > 0 THEN 'Costa Toscana'
					WHEN LOCATE('Queen Anne', title) > 0 THEN 'Queen Anne'
					WHEN LOCATE('Queen Elizabeth', title) > 0 THEN 'Queen Elizabeth'
					WHEN LOCATE('Queen Mary 2', title) > 0 THEN 'Queen Mary 2'
					WHEN LOCATE('Queen Victoria', title) > 0 THEN 'Queen Victoria'
					WHEN LOCATE('Disney Dream', title) > 0 THEN 'Disney Dream'
					WHEN LOCATE('Disney Fantasy', title) > 0 THEN 'Disney Fantasy'
					WHEN LOCATE('Disney Magic', title) > 0 THEN 'Disney Magic'
					WHEN LOCATE('Disney Treasure', title) > 0 THEN 'Disney Treasure'
					WHEN LOCATE('Disney Wish', title) > 0 THEN 'Disney Wish'
					WHEN LOCATE('Disney Wonder', title) > 0 THEN 'Disney Wonder'
					WHEN LOCATE('Explora II', title) > 0 THEN 'Explora II'
					WHEN LOCATE('Explora I', title) > 0 THEN 'Explora I'
					WHEN LOCATE('Eurodam', title) > 0 THEN 'Eurodam'
					WHEN LOCATE('HANSEATIC inspiration', title) > 0 THEN 'HANSEATIC inspiration'
					WHEN LOCATE('HANSEATIC nature', title) > 0 THEN 'HANSEATIC nature'
					WHEN LOCATE('HANSEATIC spirit', title) > 0 THEN 'HANSEATIC spirit'
					WHEN LOCATE('Koningsdam', title) > 0 THEN 'Koningsdam'
					WHEN LOCATE('MS Europa 2', title) > 0 THEN 'MS Europa 2'
					WHEN LOCATE('MS Europa', title) > 0 THEN 'MS Europa'
					WHEN LOCATE('Nieuw Amsterdam', title) > 0 THEN 'Nieuw Amsterdam'
					WHEN LOCATE('Nieuw Statendam', title) > 0 THEN 'Nieuw Statendam'
					WHEN LOCATE('Noordam', title) > 0 THEN 'Noordam'
					WHEN LOCATE('Oosterdam', title) > 0 THEN 'Oosterdam'
--					WHEN LOCATE('Rotterdam', title) > 0 THEN 'Rotterdam'
					WHEN LOCATE('Volendam', title) > 0 THEN 'Volendam'
					WHEN LOCATE('Westerdam', title) > 0 THEN 'Westerdam'
					WHEN LOCATE('Zaandam', title) > 0 THEN 'Zaandam'
					WHEN LOCATE('Zuiderdam', title) > 0 THEN 'Zuiderdam'
					WHEN LOCATE('Mein Schiff 1', title) > 0 THEN 'Mein Schiff 1'
					WHEN LOCATE('Mein Schiff 2', title) > 0 THEN 'Mein Schiff 2'
					WHEN LOCATE('Mein Schiff 3', title) > 0 THEN 'Mein Schiff 3'
					WHEN LOCATE('Mein Schiff 4', title) > 0 THEN 'Mein Schiff 4'
					WHEN LOCATE('Mein Schiff 5', title) > 0 THEN 'Mein Schiff 5'
					WHEN LOCATE('Mein Schiff 6', title) > 0 THEN 'Mein Schiff 6'
					WHEN LOCATE('Mein Schiff 7', title) > 0 THEN 'Mein Schiff 7'
					WHEN LOCATE('Mein Schiff Flow', title) > 0 THEN 'Mein Schiff Flow'
					WHEN LOCATE('Mein Schiff Relax', title) > 0 THEN 'Mein Schiff Relax'
					WHEN LOCATE('MSC Armonia', title) > 0 THEN 'MSC Armonia'
					WHEN LOCATE('MSC Bellissima', title) > 0 THEN 'MSC Bellissima'
					WHEN LOCATE('MSC Divina', title) > 0 THEN 'MSC Divina'
					WHEN LOCATE('MSC Euribia', title) > 0 THEN 'MSC Euribia'
					WHEN LOCATE('MSC Fantasia', title) > 0 THEN 'MSC Fantasia'
					WHEN LOCATE('MSC Grandiosa', title) > 0 THEN 'MSC Grandiosa'
					WHEN LOCATE('MSC Lirica', title) > 0 THEN 'MSC Lirica'
					WHEN LOCATE('MSC Magnifica', title) > 0 THEN 'MSC Magnifica'
					WHEN LOCATE('MSC Meraviglia', title) > 0 THEN 'MSC Meraviglia'
					WHEN LOCATE('MSC Musica', title) > 0 THEN 'MSC Musica'
					WHEN LOCATE('MSC Opera', title) > 0 THEN 'MSC Opera'
					WHEN LOCATE('MSC Orchestra', title) > 0 THEN 'MSC Orchestra'
					WHEN LOCATE('MSC Poesia', title) > 0 THEN 'MSC Poesia'
					WHEN LOCATE('MSC Preziosa', title) > 0 THEN 'MSC Preziosa'
					WHEN LOCATE('MSC Seascape', title) > 0 THEN 'MSC Seascape'
					WHEN LOCATE('MSC Seashore', title) > 0 THEN 'MSC Seashore'
					WHEN LOCATE('MSC Seaside', title) > 0 THEN 'MSC Seaside'
					WHEN LOCATE('MSC Seaview', title) > 0 THEN 'MSC Seaview'
					WHEN LOCATE('MSC Sinfonia', title) > 0 THEN 'MSC Sinfonia'
					WHEN LOCATE('MSC Splendida', title) > 0 THEN 'MSC Splendida'
					WHEN LOCATE('MSC Virtuosa', title) > 0 THEN 'MSC Virtuosa'
					WHEN LOCATE('MSC World America', title) > 0 THEN 'MSC World America'
					WHEN LOCATE('MSC World Europa', title) > 0 THEN 'MSC World Europa'
					WHEN LOCATE('Norwegian Aqua', title) > 0 THEN 'Norwegian Aqua'
					WHEN LOCATE('Norwegian Bliss', title) > 0 THEN 'Norwegian Bliss'
					WHEN LOCATE('Norwegian Breakaway', title) > 0 THEN 'Norwegian Breakaway'
					WHEN LOCATE('Norwegian Dawn', title) > 0 THEN 'Norwegian Dawn'
					WHEN LOCATE('Norwegian Encore', title) > 0 THEN 'Norwegian Encore'
					WHEN LOCATE('Norwegian Epic', title) > 0 THEN 'Norwegian Epic'
					WHEN LOCATE('Norwegian Escape', title) > 0 THEN 'Norwegian Escape'
					WHEN LOCATE('Norwegian Gem', title) > 0 THEN 'Norwegian Gem'
					WHEN LOCATE('Norwegian Getaway', title) > 0 THEN 'Norwegian Getaway'
					WHEN LOCATE('Norwegian Jade', title) > 0 THEN 'Norwegian Jade'
					WHEN LOCATE('Norwegian Jewel', title) > 0 THEN 'Norwegian Jewel'
					WHEN LOCATE('Norwegian Joy', title) > 0 THEN 'Norwegian Joy'
					WHEN LOCATE('Norwegian Luna', title) > 0 THEN 'Norwegian Luna'
					WHEN LOCATE('Norwegian Pearl', title) > 0 THEN 'Norwegian Pearl'
					WHEN LOCATE('Norwegian Prima', title) > 0 THEN 'Norwegian Prima'
					WHEN LOCATE('Norwegian Sky', title) > 0 THEN 'Norwegian Sky'
					WHEN LOCATE('Norwegian Spirit', title) > 0 THEN 'Norwegian Spirit'
					WHEN LOCATE('Norwegian Star', title) > 0 THEN 'Norwegian Star'
					WHEN LOCATE('Norwegian Sun', title) > 0 THEN 'Norwegian Sun'
					WHEN LOCATE('Norwegian Viva', title) > 0 THEN 'Norwegian Viva'
					WHEN LOCATE('Pride of America', title) > 0 THEN 'Pride of America' 
					WHEN LOCATE('MS Insignia', title) > 0 THEN 'Oceania Insignia'
					WHEN LOCATE('MS Nautica', title) > 0 THEN 'Oceania Nautica'
					WHEN LOCATE('MS Regatta', title) > 0 THEN 'Oceania Regatta'
					WHEN LOCATE('Oceania Allura', title) > 0 THEN 'Oceania Allura'
					WHEN LOCATE('Oceania Insignia', title) > 0 THEN 'Oceania Insignia'
					WHEN LOCATE('Oceania Marina', title) > 0 THEN 'Oceania Marina'
					WHEN LOCATE('Oceania Nautica', title) > 0 THEN 'Oceania Nautica'
					WHEN LOCATE('Oceania Regatta', title) > 0 THEN 'Oceania Regatta'
					WHEN LOCATE('Oceania Riviera', title) > 0 THEN 'Oceania Riviera'
					WHEN LOCATE('Oceania Sirena', title) > 0 THEN 'Oceania Sirena'
					WHEN LOCATE('Oceania Vista', title) > 0 THEN 'Oceania Vista'
					WHEN LOCATE('Arcadia', title) > 0 THEN 'Arcadia'
					WHEN LOCATE('Arvia', title) > 0 THEN 'Arvia'
					WHEN LOCATE('Aurora', title) > 0 THEN 'Aurora'
					WHEN LOCATE('Azura', title) > 0 THEN 'Azura'
					WHEN LOCATE('Britannia', title) > 0 THEN 'Britannia'
					WHEN LOCATE('Iona', title) > 0 AND LOCATE('vanuit Iona', title) = 0 THEN 'Iona'
					WHEN LOCATE('Ventura', title) > 0 THEN 'Ventura'
					WHEN LOCATE('L''Austral', title) > 0 THEN 'L''Austral'
					WHEN LOCATE('Le Bellot', title) > 0 THEN 'Le Bellot'
					WHEN LOCATE('Le Boréal', title) > 0 THEN 'Le Boréal'
					WHEN LOCATE('Le Bougainville', title) > 0 THEN 'Le Bougainville'
					WHEN LOCATE('Le Champlain', title) > 0 THEN 'Le Champlain'
					WHEN LOCATE('Le Commandant Charcot', title) > 0 THEN 'Le Commandant Charcot'
					WHEN LOCATE('Le Dumont d''Urville', title) > 0 THEN 'Le Dumont d''Urville'
					WHEN LOCATE('Le Jacques Cartier', title) > 0 THEN 'Le Jacques Cartier'
					WHEN LOCATE('Le Laperouse', title) > 0 THEN 'Le Laperouse'
					WHEN LOCATE('Le Lyrial', title) > 0 THEN 'Le Lyrial'
					WHEN LOCATE('Le Ponant', title) > 0 THEN 'Le Ponant'
					WHEN LOCATE('Le Soléal', title) > 0 THEN 'Le Soléal'
					WHEN LOCATE('Paul Gauguin', title) > 0 THEN 'Paul Gauguin'
					WHEN LOCATE('Caribbean Princess', title) > 0 THEN 'Caribbean Princess'
					WHEN LOCATE('Coral Princess', title) > 0 THEN 'Coral Princess'
					WHEN LOCATE('Crown Princess', title) > 0 THEN 'Crown Princess'
					WHEN LOCATE('Diamond Princess', title) > 0 THEN 'Diamond Princess'
					WHEN LOCATE('Discovery Princess', title) > 0 THEN 'Discovery Princess'
					WHEN LOCATE('Emerald Princess', title) > 0 THEN 'Emerald Princess'
					WHEN LOCATE('Enchanted Princess', title) > 0 THEN 'Enchanted Princess'
					WHEN LOCATE('Grand Princess', title) > 0 THEN 'Grand Princess'
					WHEN LOCATE('Island Princess', title) > 0 THEN 'Island Princess'
					WHEN LOCATE('Majestic Princess', title) > 0 THEN 'Majestic Princess'
					WHEN LOCATE('Regal Princess', title) > 0 THEN 'Regal Princess'
					WHEN LOCATE('Royal Princess', title) > 0 THEN 'Royal Princess'
					WHEN LOCATE('Ruby Princess', title) > 0 THEN 'Ruby Princess'
					WHEN LOCATE('Sapphire Princess', title) > 0 THEN 'Sapphire Princess'
					WHEN LOCATE('Sky Princess', title) > 0 THEN 'Sky Princess'
					WHEN LOCATE('Star Princess', title) > 0 THEN 'Star Princess'
					WHEN LOCATE('Sun Princess', title) > 0 THEN 'Sun Princess'
					WHEN LOCATE('Seven Seas Explorer', title) > 0 THEN 'Seven Seas Explorer'
					WHEN LOCATE('Seven Seas Grandeur', title) > 0 THEN 'Seven Seas Grandeur'
					WHEN LOCATE('Seven Seas Mariner', title) > 0 THEN 'Seven Seas Mariner'
					WHEN LOCATE('Seven Seas Navigator', title) > 0 THEN 'Seven Seas Navigator'
					WHEN LOCATE('Seven Seas Splendor', title) > 0 THEN 'Seven Seas Splendor'
					WHEN LOCATE('Seven Seas Voyager', title) > 0 THEN 'Seven Seas Voyager'
					WHEN LOCATE('Adventure of the Seas', title) > 0 THEN 'Adventure of the Seas'
					WHEN LOCATE('Allure of the Seas', title) > 0 THEN 'Allure of the Seas'
					WHEN LOCATE('Anthem of the Seas', title) > 0 THEN 'Anthem of the Seas'
					WHEN LOCATE('Brilliance of the Seas', title) > 0 THEN 'Brilliance of the Seas'
					WHEN LOCATE('Enchantment of the Seas', title) > 0 THEN 'Enchantment of the Seas'
					WHEN LOCATE('Explorer of the Seas', title) > 0 THEN 'Explorer of the Seas'
					WHEN LOCATE('Freedom of the Seas', title) > 0 THEN 'Freedom of the Seas'
					WHEN LOCATE('Grandeur of the Seas', title) > 0 THEN 'Grandeur of the Seas'
					WHEN LOCATE('Harmony of the Seas', title) > 0 THEN 'Harmony of the Seas'
					WHEN LOCATE('Icon of the Seas', title) > 0 THEN 'Icon of the Seas'
					WHEN LOCATE('Independence of the Seas', title) > 0 THEN 'Independence of the Seas'
					WHEN LOCATE('Jewel of the Seas', title) > 0 THEN 'Jewel of the Seas'
					WHEN LOCATE('Legend of the Seas', title) > 0 THEN 'Legend of the Seas'
					WHEN LOCATE('Liberty of the Seas', title) > 0 THEN 'Liberty of the Seas'
					WHEN LOCATE('Mariner of the Seas', title) > 0 THEN 'Mariner of the Seas'
					WHEN LOCATE('Navigator of the Seas', title) > 0 THEN 'Navigator of the Seas'
					WHEN LOCATE('Oasis of the Seas', title) > 0 THEN 'Oasis of the Seas'
					WHEN LOCATE('Odyssey of the Seas', title) > 0 THEN 'Odyssey of the Seas'
					WHEN LOCATE('Ovation of the Seas', title) > 0 THEN 'Ovation of the Seas'
					WHEN LOCATE('Quantum of the Seas', title) > 0 THEN 'Quantum of the Seas'
					WHEN LOCATE('Radiance of the Seas', title) > 0 THEN 'Radiance of the Seas'
					WHEN LOCATE('Rhapsody of the Seas', title) > 0 THEN 'Rhapsody of the Seas'
					WHEN LOCATE('Serenade of the Seas', title) > 0 THEN 'Serenade of the Seas'
					WHEN LOCATE('Spectrum of the Seas', title) > 0 THEN 'Spectrum of the Seas'
					WHEN LOCATE('Star of the Seas', title) > 0 THEN 'Star of the Seas'
					WHEN LOCATE('Symphony of the Seas', title) > 0 THEN 'Symphony of the Seas'
					WHEN LOCATE('Utopia of the Seas', title) > 0 THEN 'Utopia of the Seas'
					WHEN LOCATE('Vision of the Seas', title) > 0 THEN 'Vision of the Seas'
					WHEN LOCATE('Voyager of the Seas', title) > 0 THEN 'Voyager of the Seas'
					WHEN LOCATE('Wonder of the Seas', title) > 0 THEN 'Wonder of the Seas'
					WHEN LOCATE('Seabourn Encore', title) > 0 THEN 'Seabourn Encore'
					WHEN LOCATE('Seabourn Ovation', title) > 0 THEN 'Seabourn Ovation'
					WHEN LOCATE('Seabourn Pursuit', title) > 0 THEN 'Seabourn Pursuit'
					WHEN LOCATE('Seabourn Quest', title) > 0 THEN 'Seabourn Quest'
					WHEN LOCATE('Seabourn Sojourn', title) > 0 THEN 'Seabourn Sojourn'
					WHEN LOCATE('Seabourn Venture', title) > 0 THEN 'Seabourn Venture'
					WHEN LOCATE('SeaDream II', title) > 0 THEN 'SeaDream II'
					WHEN LOCATE('SeaDream I', title) > 0 THEN 'SeaDream I'
					WHEN LOCATE('Silver Cloud', title) > 0 THEN 'Silver Cloud'
					WHEN LOCATE('Silver Dawn', title) > 0 THEN 'Silver Dawn'
					WHEN LOCATE('Silver Endeavour', title) > 0 THEN 'Silver Endeavour'
					WHEN LOCATE('Silver Moon', title) > 0 THEN 'Silver Moon'
					WHEN LOCATE('Silver Muse', title) > 0 THEN 'Silver Muse'
					WHEN LOCATE('Silver Nova', title) > 0 THEN 'Silver Nova'
					WHEN LOCATE('Silver Origin', title) > 0 THEN 'Silver Origin'
					WHEN LOCATE('Silver Ray', title) > 0 THEN 'Silver Ray'
					WHEN LOCATE('Silver Shadow', title) > 0 THEN 'Silver Shadow'
					WHEN LOCATE('Silver Spirit', title) > 0 THEN 'Silver Spirit'
					WHEN LOCATE('Silver Whisper', title) > 0 THEN 'Silver Whisper'
					WHEN LOCATE('Silver Wind', title) > 0 THEN 'Silver Wind'
					WHEN LOCATE('Royal Clipper', title) > 0 THEN 'Royal Clipper'
					WHEN LOCATE('Star Clipper', title) > 0 THEN 'Star Clipper'
					WHEN LOCATE('Star Flyer', title) > 0 THEN 'Star Flyer'
					WHEN LOCATE('Brilliant Lady', title) > 0 THEN 'Brilliant Lady'
					WHEN LOCATE('Resilient Lady', title) > 0 THEN 'Resilient Lady'
					WHEN LOCATE('Scarlet Lady', title) > 0 THEN 'Scarlet Lady'
					WHEN LOCATE('Valiant Lady', title) > 0 THEN 'Valiant Lady'
					WHEN LOCATE('MS Allura', title) > 0 THEN 'Oceania Allura'
					WHEN LOCATE('MS Marina', title) > 0 THEN 'Oceania Marina'
					WHEN LOCATE('MS Sirena', title) > 0 THEN 'Oceania Sirena'
					WHEN LOCATE('MS Vista', title) > 0 THEN 'Oceania Vista'
					ELSE NULL
				END AS cruiseship_name,
				departure_city,
				departure_continent,
				CASE
					WHEN departure_country = 'VS' THEN 'Verenigde Staten'
					WHEN departure_country = 'AN' AND departure_city = 'Curacao' THEN 'Curaçao'
					WHEN departure_country = 'AN' AND departure_city = 'Philipsburg' THEN 'Sint Maarten'
					WHEN departure_country = 'AN' AND departure_city = 'Willemstad' THEN 'Curaçao'
					ELSE c.dutch_short_name
				END AS departure_country,
				departure_date,
				departure_port,
				REPLACE(description, '&amp;', '&') AS description,
				destination_city,
				CASE
					WHEN destination_city    = 'Baltra (Galapagos)' THEN 'ZA'		-- Error in XML productfeed from cruisereizen.nl
					WHEN destination_city    = 'Chioggia' THEN 'EU'					-- Error in XML productfeed from cruisereizen.nl
					WHEN destination_country = 'AE' THEN 'AZ'
					WHEN destination_country = 'AG' THEN 'NA'
					WHEN destination_country = 'AN' THEN 'NA'
					WHEN destination_country = 'AR' THEN 'ZA'
					WHEN destination_country = 'AT' THEN 'EU'
					WHEN destination_country = 'AU' THEN 'OC'
					WHEN destination_country = 'AQ' THEN 'AQ'			
					WHEN destination_country = 'BB' THEN 'NA'
					WHEN destination_country = 'BE' THEN 'EU'
					WHEN destination_country = 'BR' THEN 'ZA'
					WHEN destination_country = 'BZ' THEN 'NA'
					WHEN destination_country = 'CA' THEN 'NA'
					WHEN destination_country = 'CL' THEN 'ZA'
					WHEN destination_country = 'CN' THEN 'AZ'
					WHEN destination_country = 'CO' THEN 'ZA'
					WHEN destination_country = 'CR' THEN 'NA'
					WHEN destination_country = 'CV' THEN 'AF'
					WHEN destination_country = 'DE' THEN 'EU'
					WHEN destination_country = 'DK' THEN 'EU'
					WHEN destination_country = 'DO' THEN 'NA'
					WHEN destination_country = 'EC' THEN 'ZA'
					WHEN destination_country = 'EG' THEN 'AF'
					WHEN destination_country = 'ES' THEN 'EU'
					WHEN destination_country = 'FI' THEN 'EU'
					WHEN destination_country = 'FJ' THEN 'OC'
					WHEN destination_country = 'FM' THEN 'OC'
					WHEN destination_country = 'FR' THEN 'EU'
					WHEN destination_country = 'GB' THEN 'EU'
					WHEN destination_country = 'GD' THEN 'NA'
					WHEN destination_country = 'GH' THEN 'AF'
					WHEN destination_country = 'GL' THEN 'NA'
					WHEN destination_country = 'GP' THEN 'NA'
					WHEN destination_country = 'GR' THEN 'EU'
					WHEN destination_country = 'HR' THEN 'EU'
					WHEN destination_country = 'ID' THEN 'AZ'
					WHEN destination_country = 'IE' THEN 'EU'
					WHEN destination_country = 'IL' THEN 'AZ'
					WHEN destination_country = 'IN' THEN 'AZ'
					WHEN destination_country = 'IS' THEN 'AZ'
					WHEN destination_country = 'IT' THEN 'EU'
					WHEN destination_country = 'JM' THEN 'NA'
					WHEN destination_country = 'JP' THEN 'AZ'
					WHEN destination_country = 'KI' THEN 'OC'
					WHEN destination_country = 'KN' THEN 'NA'
					WHEN destination_country = 'KR' THEN 'AZ'
					WHEN destination_country = 'LC' THEN 'NA'
					WHEN destination_country = 'MA' THEN 'AF'
					WHEN destination_country = 'MC' THEN 'EU'
					WHEN destination_country = 'MG' THEN 'AF'
					WHEN destination_country = 'MQ' THEN 'NA'
					WHEN destination_country = 'MT' THEN 'EU'
					WHEN destination_country = 'MU' THEN 'AF'
					WHEN destination_country = 'NA' THEN 'AF'
					WHEN destination_country = 'NC' THEN 'OC'
					WHEN destination_country = 'NL' THEN 'EU'
					WHEN destination_country = 'NO' THEN 'EU'
					WHEN destination_country = 'NZ' THEN 'OC'
					WHEN destination_country = 'PA' THEN 'NA'
					WHEN destination_country = 'PE' THEN 'ZA'
					WHEN destination_country = 'PF' THEN 'OC'
					WHEN destination_country = 'PR' THEN 'NA'
					WHEN destination_country = 'PT' THEN 'EU'
					WHEN destination_country = 'PW' THEN 'OC'
					WHEN destination_country = 'QA' THEN 'AZ'
					WHEN destination_country = 'SA' THEN 'AZ'
					WHEN destination_country = 'SC' THEN 'AF'
					WHEN destination_country = 'SE' THEN 'EU'
					WHEN destination_country = 'SG' THEN 'AZ'
					WHEN destination_country = 'SN' THEN 'AF'
					WHEN destination_country = 'SX' THEN 'NA'
					WHEN destination_country = 'TH' THEN 'AZ'
					WHEN destination_country = 'TN' THEN 'AF'
					WHEN destination_country = 'TO' THEN 'OC'
					WHEN destination_country = 'TR' THEN 'EU'
					WHEN destination_country = 'TT' THEN 'ZA'
					WHEN destination_country = 'TW' THEN 'AZ'
					WHEN destination_country = 'TZ' THEN 'AF'
					WHEN destination_country = 'US' THEN 'NA'
					WHEN destination_country = 'UY' THEN 'ZA'
					WHEN destination_country = 'VI' THEN 'NA'
					WHEN destination_country = 'VS' THEN 'NA'
					WHEN destination_country = 'WS' THEN 'OC'
					WHEN destination_country = 'ZA' THEN 'AF'
					WHEN destination_city    = 'Dili' THEN 'AZ'
					WHEN destination_city    = 'Hong Kong' THEN 'AZ'
					WHEN destination_city    = 'Sint Maarten' THEN 'NA'
					ELSE NULL
				END AS destination_continent_code,
				CASE
					WHEN destination_city    = 'Baltra (Galapagos)' THEN 'EC'											-- Error in XML productfeed from cruisereizen.nl
					WHEN destination_city    = 'Bayonne' AND destination_continent = 'Canarische Eilanden' THEN 'ES'	-- Error in XML productfeed from cruisereizen.nl
					WHEN destination_city    = 'Bayonne' AND destination_continent = 'Noord-Amerika' THEN 'US'			-- Error in XML productfeed from cruisereizen.nl
					WHEN destination_city    = 'Chioggia' THEN 'IT'														-- Error in XML productfeed from cruisereizen.nl
					WHEN destination_city    = 'Soufriere' THEN 'VC'													-- Error in XML productfeed from cruisereizen.nl
					WHEN destination_country = 'VS' THEN 'US'
					WHEN destination_country = 'AN' AND destination_city = 'Curacao' THEN 'CW'
					WHEN destination_country = 'AN' AND destination_city = 'Philipsburg' THEN 'SX'
					WHEN destination_city    = 'Dili' THEN 'TL'
					WHEN destination_city    = 'Hong Kong' THEN 'HK'
					WHEN destination_city    = 'Marne La Vallée' THEN 'FR'
					WHEN destination_city    = 'Nice' THEN 'FR'
					WHEN destination_city    = 'NYC' THEN 'US'
					WHEN destination_city    = 'Philadelphia' THEN 'US'
					WHEN destination_city    = 'Pitlochry' THEN 'GB'
					WHEN destination_city    = 'Sint Maarten' THEN 'SX'
					WHEN destination_city    = 'Vieux fort' THEN 'LC'
					ELSE destination_country
				END AS destination_country_code,
				NULL AS destination_country_name,
				destination_port,
				NULL AS destination_region,
				CASE
					WHEN merchant_id = 10764 THEN SUBSTRING_INDEX(title, ' ', 1) -- cruisereizen.nl
					ELSE duration_days
				END AS duration_days,
				CASE
					WHEN merchant_id = 10764 THEN SUBSTRING_INDEX(title, ' ', 1) - 1 -- cruisereizen.nl
					ELSE duration_nights
				END AS duration_nights,
				(DATE_SUB(departure_date, INTERVAL 42 DAY) < CURRENT_DATE()) AS holidaytype_is_lastminutes,
				CASE
					WHEN LOCATE('minicruise', title) > 0 THEN 1
					ELSE 0
				END AS holidaytype_is_minicruise,
				CASE
					WHEN LOCATE('douro', title) > 0 THEN 1
					WHEN LOCATE('donau', title) > 0 THEN 1
					WHEN LOCATE('kerstmarktcruise', title) > 0 THEN 1
					WHEN LOCATE('moezel', title) > 0 THEN 1
					WHEN LOCATE('nijl', title) > 0 THEN 1
					WHEN LOCATE('rhone', title) > 0 THEN 1
					WHEN LOCATE('rhône', title) > 0 THEN 1
					WHEN LOCATE('rijn', title) > 0 THEN 1
					WHEN LOCATE('riviercruise', category) > 0 THEN 1
					WHEN LOCATE('riviercruise', title) > 0 THEN 1
					WHEN LOCATE('saône', title) > 0 THEN 1
					WHEN LOCATE('seine', title) > 0 THEN 1
					WHEN LOCATE('vanuit passau', title) > 0 THEN 1
					WHEN LOCATE('wolga', title) > 0 THEN 1
					WHEN travel_tour_operator = 'OAD' THEN 1
					ELSE 0
				END AS holidaytype_is_rivercruise,
				CASE
					WHEN LOCATE('donau', title) > 0 THEN 1
					WHEN LOCATE('vanuit passau', title) > 0 THEN 1
					ELSE 0
				END AS holidaytype_is_rivercruise_danube,
				CASE
					WHEN LOCATE('douro', title) > 0 THEN 1
					ELSE 0
				END AS holidaytype_is_rivercruise_douro,
				CASE
					WHEN LOCATE('moezel', title) > 0 THEN 1
					ELSE 0
				END AS holidaytype_is_rivercruise_moselle,
				CASE
					WHEN LOCATE('nijl', title) > 0 THEN 1
					ELSE 0
				END AS holidaytype_is_rivercruise_nile,
				CASE
					WHEN LOCATE('rijn', title) > 0 THEN 1
					ELSE 0
				END AS holidaytype_is_rivercruise_rhine,
				CASE
					WHEN LOCATE('rhone', title) > 0 THEN 1
					WHEN LOCATE('rhône', title) > 0 THEN 1
					WHEN LOCATE('saône', title) > 0 THEN 1
					ELSE 0
				END AS holidaytype_is_rivercruise_rhone,
				CASE
					WHEN LOCATE('seine', title) > 0 THEN 1
					ELSE 0
				END AS holidaytype_is_rivercruise_seine,
				CASE
					WHEN LOCATE('wolga', title) > 0 THEN 1
					ELSE 0
				END AS holidaytype_is_rivercruise_volga,
				CASE
					WHEN LOCATE('antarctica', title) > 0 THEN 1
					WHEN LOCATE('blue cruise', title) > 0 THEN 1
					WHEN LOCATE('caribbean', title) > 0 THEN 1
					WHEN LOCATE('caribische', title) > 0 THEN 1
					WHEN LOCATE('Middellandse Zee', destination_region) > 0 THEN 1
					WHEN LOCATE('mediterraanse', title) > 0 THEN 1
					WHEN LOCATE('mediterrane', title) > 0 THEN 1
					WHEN LOCATE('middellandse zee', title) > 0 THEN 1
					WHEN LOCATE('wereldcruise', title) > 0 THEN 1
					WHEN LOCATE('zeecruise', title)	> 0 THEN 1
					ELSE 0
				END AS holidaytype_is_seacruise,
				CASE
					WHEN LOCATE('antarctica', title) > 0 THEN 1
					ELSE 0
				END AS holidaytype_is_seacruise_antarctic,
				0 AS holidaytype_is_seacruise_arctic,
				CASE
					WHEN LOCATE('blue cruise', title) > 0 THEN 1
					ELSE 0
				END AS holidaytype_is_seacruise_bluecruise,
				CASE
					WHEN LOCATE('caribbean', title) > 0 THEN 1
					WHEN LOCATE('caribische', title) > 0 THEN 1
					ELSE 0
				END AS holidaytype_is_seacruise_caribbean,
				CASE
					WHEN LOCATE('hurtigruten', title) > 0 THEN 1
					ELSE 0
				END AS holidaytype_is_seacruise_hurtigruten,
				CASE
					WHEN LOCATE('Middellandse Zee', destination_region) > 0 THEN 1
					WHEN LOCATE('mediterraanse', title) > 0 THEN 1
					WHEN LOCATE('mediterrane', title) > 0 THEN 1
					WHEN LOCATE('middellandse zee', title) > 0 THEN 1
					ELSE 0
				END AS holidaytype_is_seacruise_mediterranean,
				CASE
					WHEN LOCATE('Star Clippers', travel_tour_operator) > 0 THEN 1
					ELSE 0
				END AS holidaytype_is_seacruise_sailing,
				CASE
					WHEN LOCATE('wereldcruise', title) > 0 THEN 1
					ELSE 0
				END AS holidaytype_is_seacruise_world,
				0 AS holidaytype_is_herfstvakantie,
				0 AS holidaytype_is_kerstvakantie,
				0 AS holidaytype_is_meivakantie,
				0 AS holidaytype_is_voorjaarsvakantie,
				0 AS holidaytype_is_zomervakantie,
				image_location AS images,
				link,
				price,
				(SELECT id FROM school_holidays WHERE start_date <= departure_date AND end_date >= departure_date LIMIT 1) AS schoolholiday_id,
				sku,
				(SELECT CONCAT(m.slug, '-', REPLACE(REPLACE(REPLACE(REPLACE(LOWER(dc.title), ' ', '-'), '(', ''), ')', ''), '&', '-'), '-', REPLACE(COALESCE(dc.departure_date, ''), '-', ''), '-', dc.product_id) FROM affiliate_networks_merchants m WHERE m.id = dc.merchant_id)
													AS	slug,
				REPLACE(title, '&amp;', '&') AS title
			FROM
				affiliate_products_extracted_daisycon dc
			LEFT JOIN
				vendor_iso_3166_countrycodes c ON c.alpha_2_code = dc.departure_country
			WHERE
				(merchant_id = 7562 AND category = 'Riviercruises')
			OR
				merchant_id = 10764
			OR
				merchant_id = 17747";
		$this->db->execute($sql);
	}
}