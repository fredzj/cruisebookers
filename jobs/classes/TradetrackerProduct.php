<?php

/**
 * Class TradetrackerProduct
 * 
 * This class represents a product from the Tradetracker affiliate network product feed.
 * It inherits from the Product class and adds any Tradetracker-specific functionality.
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

class TradetrackerProduct extends Product {

	private $outputColumns = ['merchant_id', 'productfeed_id', 'product_id', 'campaign_id', 'name', 'currency', 'price', 'url', 'images', 'description', 'categories', 'properties', 'variations'];

    /**
     * TradetrackerProduct constructor.
     * 
     * Initializes the TradetrackerProduct object, sets up the database connection, and assigns the product data.
     * 
     * @param Database $db The database connection object.
     * @param array $productfeed The productfeed meta data of the affiliate network.
     * @param object $product The product data.
     */
    public function __construct(Database $db, array $productfeed, $product) {
        parent::__construct($db, $productfeed, $product);
    }

	/**
	 * Saves the TradeTracker product data to the database.
	 * 
	 * This method processes the TradeTracker product data, sets the values,
	 * and inserts the data into the `affiliate_products_extracted_tradetracker` table in the database.
	 * 
	 * @return void
	 */
    public function save(): void {
		try {
			$outputValues	= [];
			$outputValues[]	= $this->productfeed['merchant_id'];
			$outputValues[]	= $this->productfeed['productfeed_id'];
			$outputValues[]	= $this->product['ID'];
			$outputValues[]	= $this->product->campaignID;
			$outputValues[]	= addslashes(str_replace('&apos;', "'", $this->product->name));
			$outputValues[]	= $this->product->price['currency'];
			$outputValues[]	= $this->product->price;
			$outputValues[]	= str_replace($this->productfeed['affiliate_network_media_id'], '<<MEDIA_ID>>', addslashes($this->product->URL));
			$outputValues[]	= addslashes(json_encode($this->product->images));
			$outputValues[]	= addslashes($this->product->description);
			$outputValues[]	= addslashes(json_encode($this->product->categories));
			$outputValues[]	= addslashes(json_encode($this->product->properties));
			$outputValues[]	= addslashes(json_encode($this->product->variations));
			$this->db->insert('affiliate_products_extracted_tradetracker', $this->outputColumns, $outputValues);
		} catch (Exception $e) {
			$this->log->error('Failed to save product: ' . $e->getMessage());
		}
	}
}