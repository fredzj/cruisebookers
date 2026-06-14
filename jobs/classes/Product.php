<?php

/**
 * Class Product
 * 
 * This class represents a product from one of the affiliate networks productfeeds.
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

require_once __DIR__ . '/AwinProduct.php';
require_once __DIR__ . '/DaisyconProduct.php';
require_once __DIR__ . '/TradetrackerProduct.php';

class Product {
    protected $db;
    protected $log;
	protected $product;
	protected $productfeed;

	/**
	 * Product constructor.
	 * 
	 * Initializes the Product object, sets up the database connection, and assigns the product data.
	 * 
	 * @param Database $db The database connection object.
	 * @param array $productfeed The productfeed meta data of the affiliate network.
	 * @param object $product The product data.
	 */
    public function __construct(Database $db, array $productfeed, $product) {
        $this->db = $db;
        $this->product = $product;
        $this->productfeed = $productfeed;
        $this->log = new Log();
    }

	/**
	 * Saves the product data to the database.
	 * 
	 * This method processes the product data based on the affiliate network code,
	 * and calls the appropriate method to save the product data to the database.
	 * 
	 * @return void
	 */
    public function save(): void {
		// Empty method to be overridden by child classes.
	}
}