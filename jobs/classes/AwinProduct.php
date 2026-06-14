<?php

/**
 * Class AwinProduct
 * 
 * This class represents a product from the Awin affiliate network product feed.
 * It inherits from the Product class and adds any Awin-specific functionality.
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

class AwinProduct extends Product {

	private $outputColumns = ['merchant_id', 'productfeed_id', 'product_id', 'affiliate_unique_id', 'prod_lang', 'brandname', 'price_curr', 'price_buynow', 'price_rrp', 'price_saving', 'price_savings_percent', 'price_basepricetext', 'text_name', 'text_desc', 'text_product_short_description', 'text_promo', 'text_spec', 'uri_aw_track', 'uri_alternate_image', 'uri_alternate_image_two', 'uri_alternate_image_three', 'uri_aw_image', 'uri_aw_thumb', 'uri_large_image', 'uri_m_image', 'uri_m_link', 'uri_m_thumb', 'vertical_id', 'vertical_name', 'vertical_arrival_code', 'vertical_cancellation_policy', 'vertical_destination_region', 'vertical_duration', 'vertical_starting_from_price', 'vertical_travel_rating', 'vertical_travel_type','prod_avgrating', 'prod_custom1', 'prod_custom3', 'prod_rating', 'prod_valfrom', 'prod_valto'];

    /**
     * AwinProduct constructor.
     * 
     * Initializes the AwinProduct object, sets up the database connection, and assigns the product data.
     * 
     * @param Database $db The database connection object.
     * @param array $productfeed The productfeed meta data of the affiliate network.
     * @param object $product The product data.
     */
    public function __construct(Database $db, array $productfeed, $product) {
        parent::__construct($db, $productfeed, $product);
    }

    /**
     * Saves the Awin product data to the database.
     * 
     * This method processes the Awin product data, corrects numeric formats, sets the values,
     * and inserts the data into the `affiliate_products_extracted_awin` table in the database.
     * 
     * @return void
     */
    public function save(): void {
        try {
			$outputValues	= [];
			$outputValues[]	= $this->productfeed['merchant_id'];
			$outputValues[]	= $this->productfeed['productfeed_id'];
			$outputValues[]	= addslashes($this->product->pId);
			$outputValues[]	= $this->product['id'];
			$outputValues[]	= $this->product['lang'];
			$outputValues[]	= addslashes($this->product->brand->brandName);
			$outputValues[]	= addslashes($this->product->price['curr']);
			$outputValues[]	= addslashes($this->product->price->buynow);
			$outputValues[]	= $this->correctFormat($this->product->price->rrp);
			$outputValues[]	= $this->correctFormat($this->product->price->saving);
			$outputValues[]	= $this->correctFormat($this->product->price->savingsPercent);
			$outputValues[]	= addslashes($this->product->price->basePriceText);
			$outputValues[]	= addslashes($this->product->text->name);
			$outputValues[]	= addslashes($this->product->text->desc);
			$outputValues[]	= addslashes($this->product->text->productShortDescription);
			$outputValues[]	= addslashes($this->product->text->promo);
			$outputValues[]	= addslashes($this->product->text->spec);
			$outputValues[]	= addslashes($this->product->uri->awTrack);
			$outputValues[]	= addslashes($this->product->uri->alternateImage);
			$outputValues[]	= addslashes($this->product->uri->alternateImageTwo);
			$outputValues[]	= addslashes($this->product->uri->alternateImageThree);
			$outputValues[]	= addslashes($this->product->uri->awImage);
			$outputValues[]	= addslashes($this->product->uri->awThumb);
			$outputValues[]	= addslashes($this->product->uri->largeImage);
			$outputValues[]	= addslashes($this->product->uri->mImage);
			$outputValues[]	= addslashes($this->product->uri->mLink);
			$outputValues[]	= addslashes($this->product->uri->mThumb);
			$outputValues[]	= addslashes($this->product->vertical['id']);
			$outputValues[]	= addslashes($this->product->vertical['name']);
			$outputValues[]	= addslashes($this->product->vertical->arrivalCode);
			$outputValues[]	= addslashes($this->product->vertical->cancellationPolicy);
			$outputValues[]	= addslashes($this->product->vertical->destinationRegion);
			$outputValues[]	= addslashes($this->product->vertical->duration);
			$outputValues[]	= $this->correctFormat($this->product->vertical->startingFromPrice);
			$outputValues[]	= $this->correctFormat($this->product->vertical->travelRating);
			$outputValues[]	= addslashes($this->product->vertical->travelType);
			$outputValues[]	= $this->correctFormat($this->product->avgRating);
			$outputValues[]	= addslashes($this->product->custom1);
			$outputValues[]	= addslashes($this->product->custom3);
			$outputValues[]	= $this->correctFormat($this->product->rating);
			$outputValues[]	= addslashes($this->product->valFrom);
			$outputValues[]	= addslashes($this->product->valTo);
			$this->db->insert('affiliate_products_extracted_awin', $this->outputColumns, $outputValues);
		} catch (Exception $e) {
			$this->log->error('Failed to save product: ' . $e->getMessage());
		}
	}

	/**
	 * Corrects the format of a numeric value.
	 * 
	 * This method replaces commas with dots and removes any dots in the value to ensure
	 * the numeric format is consistent and suitable for database storage.
	 * 
	 * @param string $value The value to be corrected.
	 * @return string The corrected value.
	 */
    private function correctFormat(string $value): string {
        return str_replace(',', '.', str_replace('.', '', $value));
    }
}