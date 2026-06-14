<?php
/**
 * Class TravelAdviceImporter
 * 
 * This class is responsible for downloading travel advice data from a specified URL,
 * processing the XML data, and inserting it into the appropriate database tables.
 * It ensures that the database is updated with the latest travel advice information.
 *
 * @package rijksoverheid-opendata
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
class TravelAdviceImporter {
    private $db;
	private $dbConfigPath;
    private $inputUrl;
    private $log;
    private $outputColumns;
    private $outputValues;
    private $outputDataLines = 0;
    private $timeStart;

    /**
     * TravelAdviceImporter constructor.
     * 
     * @param Database $db The database connection object.
     * @param string $url The URL to fetch XML data from.
     */
    public function __construct($dbConfigPath, $inputUrl) {
		$this->dbConfigPath = $dbConfigPath;
        $this->inputUrl = $inputUrl;
        $this->log = new Log();
        $this->initializeOutputColumns();
        $this->registerExitHandler();
		$this->connectDatabase();
    }

    /**
     * Initializes the output columns for the database tables.
     */
    private function initializeOutputColumns() {
        $this->outputColumns = [
            'traveladvice'               => ['id', 'type', 'canonical', 'dataurl', 'title', 'introduction', 'location', 'modificationdate', 'modifications', 'authorities', 'creators', 'lastmodified', 'issued', 'available', 'license', 'rightsholders', 'language'],
            'traveladvice_contentblocks' => ['id', 'paragraph', 'paragraphtitle', 'sequence'],
            'traveladvice_files'         => ['id', 'fileurl', 'mimetype', 'filesize', 'filename', 'filetitle', 'filedescription', 'filemodifieddate', 'maptype']
        ];
    }

    /**
     * Register the exit handler.
     *
     * @return void
     */
    private function registerExitHandler() {
        $this->timeStart = microtime(true);
        register_shutdown_function([new ExitHandler($this->timeStart), 'handleExit']);
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
	private function connectDatabase() {
		if (($dbConfig = parse_ini_file($this->dbConfigPath, FALSE, INI_SCANNER_TYPED)) === FALSE) {
			throw new Exception("Parsing file " . $this->dbConfigPath	. " FAILED");
		}
		$this->db = new Database($dbConfig);
	}

    /**
     * Download travel advice and save to the database.
     *
     * @return void
     */
    public function import() {
        define("OPENDATA_OFFSET", 0);
        define("OPENDATA_ROWS", 200);

        $this->truncateTables();

        for ($offset = OPENDATA_OFFSET; $offset < 400; $offset += OPENDATA_ROWS) {
            $nextUrl = str_replace(['{{OFFSET}}', '{{ROWS}}'], [$offset, OPENDATA_ROWS], $this->inputUrl);

            $this->log->info('Reading XML Feed ' . $nextUrl);
            if (($file_contents = file_get_contents($nextUrl)) !== false) {
                $this->process_bulk_countries($file_contents);
            }
        }

        $this->log->info('- ' . $this->outputDataLines . ' rows processed');
    }

    /**
     * Save all bulk-level travel advice content to the database.
     *
     * @param string $file_contents Contents of the bulk-level XML file.
     *
     * @return void
     */
    private function process_bulk_countries($file_contents) {
        $documents = simplexml_load_string($file_contents);

        foreach ($documents->document as $document) {
            $this->log->info('- Reading XML Feed ' . $document->dataurl);
            if (($file_contents = file_get_contents($document->dataurl)) !== false) {
                $this->process_one_country($file_contents);
            }
        }
    }

    /**
     * Save all country-level travel advice content to the database.
     *
     * @param string $file_contents Contents of the country-level XML file.
     *
     * @return void
     */
    private function process_one_country($file_contents) {
        sleep(1);

        $document = simplexml_load_string($file_contents);

        $this->log->info('-- Reading XML Feed ' . $document->travelAdvice);
        if (($contents = @file_get_contents($document->travelAdvice)) !== false) {
            $reisadvies = simplexml_load_string($contents);

            # Collect data for travel advice
            $this->outputValues['traveladvice'] = [
                $reisadvies->id,
                $reisadvies->type,
                $reisadvies->canonical,
                $reisadvies->dataurl,
                addslashes($reisadvies->title),
                addslashes(preg_replace('/\s+/', ' ', $reisadvies->introduction)),
                addslashes($reisadvies->location),
                addslashes($reisadvies->modificationdate),
                addslashes($reisadvies->modifications),
                $reisadvies->authorities->department,
                $reisadvies->creators->department,
                $reisadvies->lastmodified,
                $reisadvies->issued,
                $reisadvies->available,
                $reisadvies->license,
                $reisadvies->rightsholders->department,
                $reisadvies->language
            ];
            $this->db->insert('vendor_rijksoverheid_nl_traveladvice', $this->outputColumns['traveladvice'], $this->outputValues['traveladvice']);
            $this->outputDataLines++;

            # Collect data for travel advice content blocks
            $sequence = 0;
            foreach ($reisadvies->content->category->contentblock as $contentblock) {
                $this->outputValues['traveladvice_contentblocks'] = [
                    $reisadvies->id,
                    addslashes(preg_replace('/\s+/', ' ', $contentblock->paragraph)),
                    addslashes($contentblock->paragraphtitle),
                    $sequence++
                ];
                $this->db->insert('vendor_rijksoverheid_nl_traveladvice_contentblocks', $this->outputColumns['traveladvice_contentblocks'], $this->outputValues['traveladvice_contentblocks']);
            }

            # Collect data for travel advice files
            foreach ($reisadvies->files->file as $file) {
                $this->outputValues['traveladvice_files'] = [
                    $reisadvies->id,
                    addslashes($file->fileurl),
                    addslashes($file->mimetype),
                    addslashes($file->filesize),
                    addslashes($file->filename),
                    addslashes($file->filetitle),
                    addslashes($file->filedescription),
                    addslashes($file->filemodifieddate),
                    addslashes($file->maptype)
                ];
                $this->db->insert('vendor_rijksoverheid_nl_traveladvice_files', $this->outputColumns['traveladvice_files'], $this->outputValues['traveladvice_files']);

                # Download images
                //$this->download_map($file->filename, $file->fileurl);
            }
        } else {
            $this->log->error('--- file_get_contents FAILED');
        }
    }

    /**
     * Create images in two sizes and three formats for the travel advice map and save these locally.
     *
     * @param string $filename The filename of the image.
     * @param string $fileurl  The URL of the image.
     *
     * @return void
     */
    private function download_map($filename, $fileurl) {
        $image = @imagecreatefrompng($fileurl);

        if (!$image) {
            $this->log->error("imagecreatefrompng(): '$fileurl' is not a valid PNG file");
        } else {
            $path = substr(__DIR__, 0, mb_strrpos(__DIR__, '/'));
            $path = substr($path, 0, mb_strrpos($path, '/')) . '/public_html/assets/img/';

            $image_large = imagescale($image, 468);

            imagepng($image_large, $path . str_replace('.png', '.png', $filename));
            imagejpeg($image_large, $path . str_replace('.png', '.jpg', $filename));
            imagewebp($image_large, $path . str_replace('.png', '.webp', $filename));

            $image_small = imagecreatetruecolor(306, 210);
            list($width, $height) = getimagesize($path . $filename);
            $height = 620 * ($width / 903);
            imagecopyresampled($image_small, $image_large, 0, 0, 0, 0, 306, 210, $width, $height);

            imagepng($image_small, $path . str_replace('.png', '_small.png', $filename));
            imagejpeg($image_small, $path . str_replace('.png', '_small.jpg', $filename));
            imagewebp($image_small, $path . str_replace('.png', '_small.webp', $filename));

            imagedestroy($image);
            imagedestroy($image_large);
            imagedestroy($image_small);
        }
    }

    /**
     * Truncates the relevant database tables.
     */
    private function truncateTables() {
        foreach ($this->outputColumns as $table => $columns) {
			$this->db->truncate('vendor_rijksoverheid_nl_' . $table);
        }
    }
}