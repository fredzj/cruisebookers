<?php

/**
 * Class AffiliateProductfeedsImporter
 * 
 * This class is responsible for importing product feeds from various affiliate networks into the database.
 * It supports multiple affiliate networks such as Awin, Daisycon, and TradeTracker, and handles the
 * retrieval, parsing, and storage of product feed data.
 * 
 * FUNCTIONALITY:
 * - Initializes the database connection using a configuration file.
 * - Retrieves product feed metadata from the database.
 * - Imports product feeds for supported affiliate networks (Awin, Daisycon, TradeTracker).
 * - Truncates existing product feed data before importing new data.
 * - Logs errors for unsupported affiliate networks or other issues.
 * 
 * USAGE:
 * - Instantiate the class with the database configuration path and affiliate network code.
 * - Call the `import()` method to process and save the product feeds into the database.
 * 
 * SUPPORTED AFFILIATE NETWORKS:
 * - Awin (Code: `AW`)
 * - Daisycon (Code: `DC`)
 * - TradeTracker (Code: `TT`)
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
require_once __DIR__ . '/Database.php';
require_once __DIR__ . '/Productfeed.php';
require_once __DIR__ . '/Product.php';

class AffiliateProductfeedsImporter {
    private $affiliateNetworkCode;
    private $db;
    private $dbConfigPath;
    private $log;
    private $timeStart;

	/**
	 * AffiliateProductfeedsImporter constructor.
	 * 
	 * Initializes the AffiliateProductfeedsImporter object, sets up the database connection,
	 * and registers the exit handler.
	 * 
	 * @param string $dbConfigPath The path to the database configuration file.
	 * @param string $affiliateNetworkCode The affiliate network code.
	 */
    public function __construct($dbConfigPath, $affiliateNetworkCode) {
        $this->affiliateNetworkCode = $affiliateNetworkCode;
		$this->dbConfigPath  = $dbConfigPath;
        $this->log = new Log();
		$this->connectDatabase();
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
	* Imports affiliate product feeds into the database.
	* 
	* This method retrieves product feed metadata from the database, constructs the URL for each product feed,
	* creates a Productfeed object, and saves the product feed data to the database.
	* 
	* @return void
	*/
	public function import(): void {
        switch ($this->affiliateNetworkCode) {
            case 'AW':
                $this->db->truncate('affiliate_products_extracted_awin');
                foreach ($this->getProductfeedMetaData() as $productfeedMetaData) {
                    $productfeed = new AwinProductfeed($this->db, $productfeedMetaData);
                    $productfeed->save();
                }
                break;
            case 'DC':
                $this->db->truncate('affiliate_products_extracted_daisycon');
                foreach ($this->getProductfeedMetaData() as $productfeedMetaData) {
                    $productfeed = new DaisyconProductfeed($this->db, $productfeedMetaData);
                    $productfeed->save();
                }
                break;
            case 'TT':
                $this->db->truncate('affiliate_products_extracted_tradetracker');
                foreach ($this->getProductfeedMetaData() as $productfeedMetaData) {
                    $productfeed = new TradetrackerProductfeed($this->db, $productfeedMetaData);
                    $productfeed->save();
                }
                break;
            default:
                $this->log->error('- ERROR: unknown affiliate ID');
        }
    }

    /**
     * Retrieves product feed metadata from the database.
     * 
     * @return array The result set containing product feed metadata.
     */
    private function getProductfeedMetaData(): array {
        $sql			=	"
        SELECT			m.affiliate_network_code,
                        m.id												AS	merchant_id,
                        m.domain_name										AS	merchant_domain_name,
                        m.name												AS	merchant_name,
                        NULL												AS	affiliate_network_media_id,
                        f.id												AS	productfeed_id,
                        f.name												AS	productfeed_name,
                        f.url												AS	productfeed_url,
                        COALESCE(f.url_fields, '')							AS	productfeed_url_fields,
                        f.expected_properties,
                        f.expected_variations
        FROM			affiliate_networks_merchants_productfeeds f
        JOIN			affiliate_networks_merchants m						ON	m.id						=	f.merchant_id
        WHERE			m.affiliate_network_code							=	'{$this->affiliateNetworkCode}'
		AND				m.is_blocked										=	0
        AND			(				
                        m.date_blocked										IS	NULL	OR
                        m.date_blocked										>	CURDATE()
                    )			
        AND				f.is_blocked										=	0
        AND				COALESCE(f.is_parks, 0)								=	0
        GROUP BY		m.id, f.id
        ORDER BY		m.domain_name";
        return $this->db->select($sql);
    }
}