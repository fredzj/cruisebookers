<?php
/**
 * SCRIPT: extractProductfeeds.php
 * 
 * This script extracts product feeds from the affiliate networks and imports them into the database.
 * It initializes the required classes, sets up the environment, and handles exceptions during the import process.
 * 
 * USAGE:
 * Run this script from the command line or a scheduled task to automate the extraction and import of product feeds.
 * 
 * FUNCTIONALITY:
 * - Sets up the default timezone, encoding, and locale.
 * - Registers an exit handler to log script execution time and handle shutdown events.
 * - Initializes the `AffiliateProductfeedsImporter` class to handle the import process.
 * - Logs any exceptions that occur during execution.
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

try {
    require_once __DIR__ . '/classes/AffiliateProductfeedsImporter.php';
    require_once __DIR__ . '/classes/ExitHandler.php';
    require_once __DIR__ . '/classes/Log.php';
    
    // Set defaults
    date_default_timezone_set(	'Europe/Amsterdam');
    mb_internal_encoding(		'UTF-8');
    setlocale(LC_ALL,			'nl_NL.utf8');
    register_shutdown_function([new ExitHandler(microtime(true)), 'handleExit']);
    
    $dbConfigPath = substr(__DIR__, 0, mb_strrpos(__DIR__, '/')) . '/config/db.ini';
    $log = new Log();
    
    //$importer = new AffiliateProductfeedsImporter($dbConfigPath, 'AW');
    //$importer->import();
    $importer = new AffiliateProductfeedsImporter($dbConfigPath, 'DC');
    $importer->import();
    $importer = new AffiliateProductfeedsImporter($dbConfigPath, 'TT');
    $importer->import();
} catch (PDOException $e) {
    $log->error('Caught PDOException: ' . $e->getMessage());
} catch (Exception $e) {
    $log->error('Caught Exception: ' . $e->getMessage());
} finally {
	// The exit handler will be called automatically at the end of the script
}