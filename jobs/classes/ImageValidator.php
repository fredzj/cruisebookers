<?php

/**
 * Class ImageValidator
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

class ImageValidator {
    private $db;

    /**
     * ImageValidator constructor.
     * 
     * @param Database $db The database connection object.
     */
    public function __construct(Database $db) {
        $this->db = $db;
    }

    public function getImages(string &$productImages, array $properties, array $variations): void {

        $array = [];
	
        $this->getProductLevelImages(			$productImages,					$array);
        $this->getPropertyLevelImages(			$properties, 'imageurllarge',	$array);
        $this->getPropertyLevelImages(			$properties, 'imageurlsmall',	$array);
        $this->getPropertyLevelImages(			$properties, 'image',			$array);
        $this->getPropertyLevelNumberedImages(	$properties, 'imageurl',		$array);
        $this->getPropertyLevelNumberedImages(	$properties, 'productimage',	$array);
        
        if (count($array) > 0) {
            $array	=	array_unique($array);
            $productImages	=	implode('|', $array);
        } else {
            $productImages	=	null;
        }
        
        return;
    }

    private function getProductLevelConcatenatedImages(&$images_array) {

        foreach ($images_array as $name => $value) {
            
            if (!is_array($value)) {
                
                if (substr_count($value, 'https')									>	1		&&							// Only for amerikaplus.nl...
                    mb_strpos($value, 'https://anpeiorzlo.cloudimg.io/v7/https')	=== false	) {							// ...but not for vakanties.nl
                    
                    $value			=	str_replace('https', '|https', $value);
                    $images_array	=	explode('|', substr($value, 1));
                }
            }
            
            break;
        }
        
        return;
    }
    
    private function getProductLevelImages($images, &$array) {
    
        if (!empty($images)) {
            
            $images_array	=	json_decode($images, true);
            
            $this->getProductLevelConcatenatedImages($images_array);
        
            foreach ($images_array as $name => $values) {
                
                if (is_array($values)) {
                    
                    foreach ($values as $key => $value) {
                    
                        $value		=	str_replace('150x150-optimized', '812x477-optimized', $value);	// Only for skichalets.nl
                        $array[]	=	addslashes($value);
                    }
                    
                } else {
                    
                    $values		=	str_replace('150x150-optimized', '812x477-optimized', $values);		// Only for skichalets.nl
//                    if (mb_strpos($values, 'urn:uuid') === false || mb_strpos($values, 'francecomfort') !== false) {										// Only for terspegelt.nl
                        $array[]	=	$values;
//                    }
                }
            }
        }
        
        return;
    }
    
    private function getPropertyLevelImages($properties, $node, &$array) {
    
        if (array_key_exists($node, $properties)) {
            $array[]	=	str_replace(';', '|', $properties[$node]);
        }
        
        return;
    }
    
    private function getPropertyLevelNumberedImages($properties, $node, &$array) {
    
        for ($i = 0; $i < 13; $i++) {
            if (array_key_exists("$node$i", $properties)) {
                $array[]	=	$properties["$node$i"];
            } else {
                break;
            }
        }
        
        return;
    }
}