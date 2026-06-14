<?php

/**
 * Class TradetrackerProductfeed
 * 
 * This class represents a productfeed from the Tradetracker affiliate network.
 * It inherits from the Productfeed class and adds any Tradetracker-specific functionality.
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

class TradetrackerProductfeed extends Productfeed {

	/**
	 * TradetrackerProductfeed constructor.
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
	 * Saves the Tradetracker product feed data to the database.
	 * 
	 * This method iterates through each product in the Tradetracker product feed, creates a Product object for each product,
	 * and saves it to the database. It also logs the number of products saved.
	 * 
	 * @param SimpleXMLElement $xml The XML data of the product feed.
	 * @return void
	 */
    public function save(): void {
		try {
       		$xml = simplexml_load_string($this->data);
			if (isset($xml->product)) {
				//$this->log->info('Processing ' . $this->productfeed['productfeed_name'] . ' product feed...');
			} else {
				$this->log->info('No products found in product feed.');
				return;
			}
			$this->extract_properties($xml->product[0]);
			$this->extract_variations($xml->product[0]);
			foreach ($xml->product as $productData) {
            	$product = new TradetrackerProduct($this->db, $this->productfeed, $productData);
            	$product->save();
        	}
        	$this->log->info('- ' . count($xml->product) . ' products saved to database');
		} catch (Exception $e) {
			$this->log->error('Caught Exception: ' . $e->getMessage());
		}
    }
	
	/**
	 * Extracts properties from the product data.
	 * 
	 * This method collects the properties from the product data, checks them against the expected properties,
	 * and updates the database with the extracted properties if necessary.
	 * 
	 * @param SimpleXMLElement $product The product data.
	 * @return void
	 */
	private function extract_properties($product): void {
		
		if (isset($product->properties)) {
			
			# Collect extracted properties
			$extracted_properties			=	[];
			foreach ($product->properties->property as $property) {
				$extracted_properties[]		=	(string) $property->attributes()->name;
			}
			asort($extracted_properties);
			
			if (isset($this->productfeed['expected_properties'])) {
				
				# Check extracted & expected properties
				$this->check_presence($this->productfeed['expected_properties'], $extracted_properties);
				
			} else {
		
				if (count($extracted_properties) > 0) {
				
					# Save extracted properties as expected properties
					$table						=	'affiliate_networks_merchants_productfeeds';
					$id							=	$this->productfeed['productfeed_id'];
					$assignment					=	"expected_properties='" . implode(',', $extracted_properties) . "'";
					$this->db->update($table, $id, $assignment);
				}
			}
		}
	}
	
	/**
	 * Extracts variations from the product data.
	 * 
	 * This method collects the variations from the product data, checks them against the expected variations,
	 * and updates the database with the extracted variations if necessary.
	 * 
	 * @param SimpleXMLElement $product The product data.
	 * @return void
	 */
	private function extract_variations($product): void {
			
		if (isset($product->variations)) {
			
			# Collect extracted variations
			$extracted_variations			=	[];
			foreach ($product->variations->variation as $variation) {
				foreach ($variation->property as $property) {
					$extracted_variations[]	=	(string) $property->attributes()->name;
				}
				break;
			}
			asort($extracted_variations);
			
			if (isset($this->productfeed['expected_variations'])) {
				
				# Check extracted & expected variations
				$this->check_presence($this->productfeed['expected_variations'], $extracted_variations);
				
			} else {
				
				if (count($extracted_variations) > 0) {
					
					# Save extracted variations as expected variations
					$table						=	'affiliate_networks_merchants_productfeeds';
					$id							=	$this->productfeed['productfeed_id'];
					$assignment					=	"expected_variations='" . implode(',', $extracted_variations) . "'";
					$this->db->update($table, $id, $assignment);
				}
			}
		}
	}

	/**
	 * Checks the presence of expected properties or variations.
	 * 
	 * This method checks if all expected properties or variations are present in the product data,
	 * and logs warnings if any expected properties or variations are missing or if there are unexpected ones.
	 * 
	 * @param string $expected_properties The expected properties or variations as a comma-separated string.
	 * @param array $present_properties The present properties or variations as an array.
	 * @return void
	 */
	private function check_presence($expected_properties, $present_properties): void {

		#
		$expected_properties	=	explode(',', $expected_properties);
		
		# Check if all expected properties are present
		foreach ($expected_properties as $expected_property) {
			if (!in_array($expected_property, $present_properties)) {
				$this->log->info('WARNING: Expected property "' . $expected_property . '" is not present.');
			}
		}
		
		# Check if all present properties are expected
		foreach ($present_properties as $present_property) {
			if (!in_array($present_property, $expected_properties)) {
				$this->log->info('WARNING: Present property "' . $present_property . '" is not expected.');
			}
		}
	}
}