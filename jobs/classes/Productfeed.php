<?php

/**
 * Class Productfeed
 * 
 * This class represents a productfeed from one of the affiliate networks.
 * 
 * @package affiliate-productfeeds
 * @version 1.0.0
 * @since 2025
 * @license MIT
 * 
 * COPYRIGHT: 2025 Fred Onis - All rights reserved.
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

require_once __DIR__ . '/AwinProductfeed.php';
require_once __DIR__ . '/DaisyconProductfeed.php';
require_once __DIR__ . '/TradetrackerProductfeed.php';

class Productfeed {
    protected $data;
    protected $db;
    protected $log;
    protected $productfeed;

	/**
	 * Productfeed constructor.
	 * 
	 * Initializes the Productfeed object, sets up the database connection, fetches the product feed data,
	 * unzips it if necessary, creates a backup, validates the XML, and truncates the database table.
	 * 
	 * @param Database $db The database connection object.
	 * @param array $productfeed The metadata of the product feed.
	 */
    public function __construct(Database $db, array $productfeed) {
        $this->db = $db;
        $this->productfeed = $productfeed;
        $this->log = new Log();
        $this->fetch();
        $this->unzip();
//        $this->backup();
        $this->validate();
    }

	/**
	 * Fetches the product feed data from the specified URL.
	 * 
	 * This method reads the product feed data from the URL and stores it in the `$data` property.
	 * 
	 * @return void
	 */
    private function fetch(): void {
		try {
        	$this->log->info('Reading productfeed ' . $this->productfeed['productfeed_url'] . ' (' . $this->productfeed['affiliate_network_code'] . '-' . $this->productfeed['productfeed_name'] . ')');
        	$this->data = @file_get_contents($this->productfeed['productfeed_url'] . $this->productfeed['productfeed_url_fields']);
		} catch (Exception $e) {
			$this->log->error('Error reading productfeed');
		}
	}

	/**
	 * Unzips the product feed data if it is compressed.
	 * 
	 * This method checks if the URL contains 'gzip' and, if so, decompresses the data using `gzdecode`.
	 * 
	 * @return void
	 */
    private function unzip(): void {
        if (mb_stripos($this->productfeed['productfeed_url'], 'gzip') !== false) {
            $len = round(mb_strlen($this->data) / 1024);
            $this->data = gzdecode($this->data);
            $this->log->info('- Productfeed unzipped (from ' . $len . ' MB to ' . round(mb_strlen($this->data) / 1024) . ' MB)');
        }
    }

	/**
	 * Creates a backup of the product feed data.
	 * 
	 * This method compresses the product feed data and saves it to a local file.
	 * 
	 * @return void
	 */
    private function backup(): void {
        $path =	substr(__DIR__, 0, mb_strrpos(__DIR__, '/'));
        $path =	substr($path,   0, mb_strrpos($path,   '/'));
        $localFilename = $path . '/resources/extracted/' . urlencode($this->productfeed['productfeed_name']) . '-' . date('Ymd') . '.xml.gz';
        $gzdata	= gzencode($this->data, 9);
        file_put_contents($localFilename, $gzdata);
        $this->log->info('- Productfeed backed up to ' . $localFilename);
    }

	/**
	 * Validates the XML structure of the product feed data.
	 * 
	 * This method loads the product feed data into a DOMDocument and validates it.
	 * It logs whether the XML document is valid or not.
	 * 
	 * @return void
	 */
    private function validate(): void {
		if (!empty($this->data)) {
			$dom = new DOMDocument;
            $dom->loadXML($this->data);
            if (@$dom->validate()) {
                $this->log->info('- Productfeed validated OK');
            }
        }
    }

	/**
	 * Saves the product feed data to the database.
	 * 
	 * This method parses the product feed data as XML, iterates through each product,
	 * creates a Product object for each product, and saves it to the database.
	 * 
	 * @return void
	 */
    public function save(): void {
		// Empty method to be overridden by child classes.
    }
}