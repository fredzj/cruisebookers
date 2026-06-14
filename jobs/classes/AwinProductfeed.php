<?php

/**
 * Class AwinProductfeed
 * 
 * This class represents a productfeed from the Awin affiliate network.
 * It inherits from the Productfeed class and adds any Awin-specific functionality.
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

class AwinProductfeed extends Productfeed {

	/**
	 * AwinProductfeed constructor.
	 * 
	 * Initializes the Productfeed object, sets up the database connection, fetches the product feed data,
	 * unzips it if necessary, creates a backup, validates the XML, and truncates the database table.
	 * 
	 * @param Database $db The database connection object.
	 * @param array $productfeed The metadata of the product feed.
	 */
    public function __construct(Database $db, array $productfeed) {
        parent::__construct($db, $productfeed);
    }

	/**
	 * Saves the Awin product feed data to the database.
	 * 
	 * This method iterates through each product in the Awin product feed, creates a Product object for each product,
	 * and saves it to the database. It also logs the number of products saved.
	 * 
	 * @param SimpleXMLElement $xml The XML data of the product feed.
	 * @return void
	 */
    public function save(): void {
        $xml = simplexml_load_string($this->data);
        foreach ($xml->datafeed as $datafeed) {
            foreach ($datafeed->prod as $product) {
                $product = new AwinProduct($this->db, $this->productfeed, $product);
                $product->save();
            }
            $this->log->info('- ' . count($datafeed->prod) . ' products saved to database');
        }
    }
}