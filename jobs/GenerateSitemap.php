<?php
/**
 * GenerateSitemap
 *
 * This class connects to the database via PDO, queries for static and dynamic URLs,
 * and generates a sitemap.xml file using plain PHP.
 *
 * Save this file (for example as GenerateSitemap.php) and run it from the command line:
 *     php GenerateSitemap.php
 */

class GenerateSitemap
{
    /**
     * @var PDO
     */
    protected $pdo;

    /**
     * @var array
     */
    protected $urls = [];

    /**
     * Constructor.
     *
     * @param PDO $pdo A PDO connection to the database.
     */
    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * Add a URL to the sitemap.
     *
     * @param string $loc
     * @param string $priority
     * @param string $changefreq
     */
    protected function addUrl($loc, $priority = '0.5', $changefreq = 'monthly')
    {
        $this->urls[] = [
            'loc' => $loc,
            'priority' => $priority,
            'changefreq' => $changefreq,
            'lastmod' => date('Y-m-d'),
        ];
    }

    /**
     * Create the main sitemap.
     */
    public function createMainSitemap(): void
    {
        // Add static URLs.
        $this->addUrl('/', '1.0', 'daily');
        $this->addUrl('/cruisemaatschappijen', '0.8', 'weekly');
        $this->addUrl('/partners', '0.8', 'monthly');
        $this->addUrl('/reisadviezen', '0.8', 'monthly');

        // Add dynamic URLs for cruiselines.
        $cruiselineStmt = $this->pdo->query("SELECT slug FROM affiliate_cruiselines WHERE is_blocked = 0");
        while ($row = $cruiselineStmt->fetch(PDO::FETCH_ASSOC)) {
            $slug = $row['slug'];
            $this->addUrl("/cruisemaatschappijen/{$slug}", '0.7', 'weekly');
        }

        // Add dynamic URLs for merchants.
        $merchantStmt = $this->pdo->query("SELECT slug FROM affiliate_networks_merchants WHERE is_blocked = 0");
        while ($row = $merchantStmt->fetch(PDO::FETCH_ASSOC)) {
            $slug = $row['slug'];
            $this->addUrl("/partners/{$slug}", '0.7', 'weekly');
        }

        // Add dynamic URLs for traveladvices.
        $travelAdviceStmt = $this->pdo->query("SELECT id FROM vendor_rijksoverheid_nl_traveladvice");
        while ($row = $travelAdviceStmt->fetch(PDO::FETCH_ASSOC)) {
            $id = $row['id'];
            $this->addUrl("/reisadviezen/{$id}", '0.7', 'weekly');
        }

        // Add cruiseship detail pages where paragraph_destinations exists.
        $stmt = $this->pdo->prepare("
            SELECT acs.*, acl.slug AS cruiseline_slug 
            FROM affiliate_cruiseships acs
            JOIN affiliate_cruiselines acl ON acs.cruiseline_id = acl.id
            WHERE acs.is_blocked = 0 
              AND acs.paragraph_destinations IS NOT NULL
              AND acs.paragraph_destinations <> ''
        ");
        $stmt->execute();

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            if (!empty($row['cruiseline_slug']) && !empty($row['slug'])) {
                $url = "/cruisemaatschappijen/{$row['cruiseline_slug']}/{$row['slug']}";
                $this->addUrl($url, '0.6', 'weekly');
            }
        }

        // Write the sitemap to a file in the public directory.
        $this->writeSitemap(__DIR__ . '/../laravel/public/sitemap.xml');

        echo "Main sitemap generated successfully!" . PHP_EOL;
    }

    /**
     * Create the Daisycon sitemap.
     */
    public function createDaisyconSitemap(): void
    {
        // Add product detail pages.
        $stmt = $this->pdo->prepare("
            SELECT p.slug 
            FROM affiliate_products_loaded_searchpage p
            JOIN affiliate_networks_merchants m ON m.id = p.merchant_id
            WHERE m.is_blocked = 0 
              AND m.affiliate_network_code = 'DC'
        ");
        $stmt->execute();

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            if (!empty($row['slug'])) {
                $url = "/product/{$row['slug']}";
                $this->addUrl($url, '0.6', 'weekly');
            }
        }

        // Write the sitemap to a file in the public directory.
        $this->writeSitemap(__DIR__ . '/../laravel/public/daisycon_sitemap.xml');

        echo "Daisycon sitemap generated successfully!" . PHP_EOL;
    }

    /**
     * Create the TradeTracker sitemap.
     */
    public function createTradetrackerSitemap(): void
    {
        // Add product detail pages.
        $stmt = $this->pdo->prepare("
            SELECT p.slug 
            FROM affiliate_products_loaded_searchpage p
            JOIN affiliate_networks_merchants m ON m.id = p.merchant_id
            WHERE m.is_blocked = 0 
              AND m.affiliate_network_code = 'TT'
        ");
        $stmt->execute();

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            if (!empty($row['slug'])) {
                $url = "/product/{$row['slug']}";
                $this->addUrl($url, '0.6', 'weekly');
            }
        }

        // Write the sitemap to a file in the public directory.
        $this->writeSitemap(__DIR__ . '/../laravel/public/tradetracker_sitemap.xml');

        echo "TradeTracker sitemap generated successfully!" . PHP_EOL;
    }

    /**
     * Write the sitemap.xml file using plain PHP.
     *
     * @param string $filePath
     */
    protected function writeSitemap($filePath)
    {
        $xml = new DOMDocument('1.0', 'UTF-8');
        $xml->formatOutput = true;

        $urlset = $xml->createElement('urlset');
        $urlset->setAttribute('xmlns', 'http://www.sitemaps.org/schemas/sitemap/0.9');

        foreach ($this->urls as $url) {
            $urlElement = $xml->createElement('url');

            $loc = $xml->createElement('loc', htmlspecialchars($this->getFullUrl($url['loc'])));
            $urlElement->appendChild($loc);

            $lastmod = $xml->createElement('lastmod', $url['lastmod']);
            $urlElement->appendChild($lastmod);

            $changefreq = $xml->createElement('changefreq', $url['changefreq']);
            $urlElement->appendChild($changefreq);

            $priority = $xml->createElement('priority', $url['priority']);
            $urlElement->appendChild($priority);

            $urlset->appendChild($urlElement);
        }

        $xml->appendChild($urlset);
        $xml->save($filePath);
    }

    /**
     * Get the full URL for the sitemap entry.
     * Adjust the base URL as needed.
     *
     * @param string $path
     * @return string
     */
    protected function getFullUrl($path)
    {
        $base = 'https://www.cruisebookers.nl'; // Change to your domain
        return rtrim($base, '/') . $path;
    }
}

// --- Example Usage --- //
try {
    // Replace with your actual database connection details.
    $dsn = "mysql:host=localhost;dbname=u10919p130675_cruise;charset=utf8mb4";
    $username = 'u10919p130675_cruise';
    $password = 'RoseDeWittBukater';

    $pdo = new PDO($dsn, $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $generator = new GenerateSitemap($pdo);
    $generator->createMainSitemap();
    $generator->createDaisyconSitemap();
    $generator->createTradeTrackerSitemap();
} catch (PDOException $e) {
    echo "Database connection failed: " . $e->getMessage() . PHP_EOL;
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . PHP_EOL;
}