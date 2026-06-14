<?php

/**
 * Class DaisyconProduct
 * 
 * This class represents a product from the Daisycon affiliate network product feed.
 * It inherits from the Product class and adds any Daisycon-specific functionality.
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

class DaisyconProduct extends Product {

	private $outputColumns = ['merchant_id', 'productfeed_id', 'product_id', 'daisycon_unique_id'];

    /**
     * DaisyconProduct constructor.
     * 
     * Initializes the DaisyconProduct object, sets up the database connection, and assigns the product data.
     * 
     * @param Database $db The database connection object.
     * @param array $productfeed The productfeed meta data of the affiliate network.
     * @param object $product The product data.
     */
    public function __construct(Database $db, array $productfeed, $product) {
        parent::__construct($db, $productfeed, $product);
    }

	/**
	 * Saves the Daisycon product data to the database.
	 * 
	 * This method processes the Daisycon product data, sets the values,
	 * and inserts the data into the `affiliate_products_extracted_daisycon` table in the database.
	 * 
	 * @return void
	 */
    public function save(): void {
        try {
			$outputColumns  = $this->outputColumns;

			$outputValues   = [];
			$outputValues[]	= $this->productfeed['merchant_id'];
			$outputValues[]	= $this->productfeed['productfeed_id'];
			$outputValues[]	= addslashes($this->product->product_info->sku);
			$outputValues[]	= $this->product->update_info->daisycon_unique_id;
			$images = $this->importProductImages($this->product);
			if (array_key_exists('location', $images)) {
				$outputColumns[] = 'image_location';
				$outputValues[]	= addslashes(implode('|', $images['location']));
				$outputColumns[] = 'image_size';
				$outputValues[]	=	implode('|', $images['size']);
				$outputColumns[] = 'image_tag';
				$outputValues[]	=	implode('|', $images['tag']);
				$outputColumns[] = 'image_type';
				$outputValues[]	=	implode('|', $images['type']);
			}
			
			if (empty($this->product->product_info->sku)) {
				$this->log->error('Empty sku for ' . $this->product->update_info->daisycon_unique_id . '-' . $this->product->product_info->accommodation_name . '-' . $this->product->product_info->title);
			}
			foreach ($this->product->product_info->children() as $key => $value) {
				switch ($key) {
					case 'accommodation_address':
					case 'accommodation_lowest_date':
					case 'accommodation_lowest_price':
					case 'accommodation_name':
					case 'accommodation_type':
					case 'airportcode_departure':
					case 'airportcode_destination_return':
					case 'airport_departure':
					case 'category':
					case 'category_path':
					case 'currency':
					case 'daisycon_unique_id':
					case 'departure_city':
					case 'departure_continent':
					case 'departure_country':
					case 'departure_date':
					case 'departure_date_return':
					case 'departure_port':
					case 'description':
					case 'destination_city':
					case 'destination_city_link':
					case 'destination_continent':
					case 'destination_country':
					case 'destination_country_link':
					//case 'destination_language':
					case 'destination_port':
					case 'destination_region':
					case 'duration_days':
					case 'duration_nights':
					case 'max_nr_people':
					case 'price':
					case 'price_old':
					case 'priority':
					case 'sku':
					case 'star_rating':
					case 'title':
					case 'travel_tour_operator':
					case 'travel_transportation_type':
					case 'travel_trip_type':
					case 'trip_holiday_type':
					case 'trip_lastminute':
						$outputColumns[] = $key;
						$outputValues[]	= addslashes($value);
						break;
					case 'link':
						$outputColumns[] = $key;
						$outputValues[]	= str_replace($this->productfeed['affiliate_network_media_id'], '<<MEDIA_ID>>', addslashes($value));
						default:
				}
			}
			$this->db->insert('affiliate_products_extracted_daisycon', $outputColumns, $outputValues);
		} catch (Exception $e) {
			$this->log->error('Failed to save product: ' . $e->getMessage());
		}
	}

	/**
	 * Imports images for a product.
	 * 
	 * This method processes the images of a product and returns an array of image attributes.
	 * 
	 * @param object $product The product data.
	 * @return array The array of image attributes.
	 */
    private function importProductImages($product): array {
        $images = [];
		foreach ($product->product_info->images->image as $image) {
			$images['size'][]		= $image->size;
			$images['tag'][]		= $image->tag;
			$images['type'][]		= $image->type;
			$images['location'][]	= $image->location;
		}
//		if (empty($images)) {
//			$this->log->info($product->update_info->daisycon_unique_id . ' has no images');
//		}
        return $images;
    }
}