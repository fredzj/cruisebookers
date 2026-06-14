<?php

declare(strict_types=1);

// Database configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'u10919p130675_cruise');
define('DB_USER', 'u10919p130675_cruise');
define('DB_PASS', 'RoseDeWittBukater');
define('DB_CHARSET', 'utf8mb4');

// API endpoint
define('API_URL', 'https://opendata.rijksoverheid.nl/v1/infotypes/schoolholidays?output=json');

function createConnection(): PDO
{
    $dsn = sprintf('mysql:host=%s;dbname=%s;charset=%s', DB_HOST, DB_NAME, DB_CHARSET);
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];

    return new PDO($dsn, DB_USER, DB_PASS, $options);
}

function createTable(PDO $pdo): void
{
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS school_holidays (
            id              INT UNSIGNED     NOT NULL AUTO_INCREMENT,
            school_year     VARCHAR(9)       NOT NULL COMMENT '2024-2025',
            vacation_type   VARCHAR(50)      NOT NULL COMMENT 'Herfstvakantie, Kerstvakantie, etc.',
            compulsory_dates TINYINT(1)      NOT NULL DEFAULT 0,
            region          VARCHAR(30)      NOT NULL COMMENT 'noord, midden, zuid, heel Nederland',
            start_date      DATE             NOT NULL,
            end_date        DATE             NOT NULL,
            PRIMARY KEY (id),
            INDEX idx_school_year  (school_year),
            INDEX idx_vacation_type (vacation_type),
            INDEX idx_region       (region),
            INDEX idx_start_date   (start_date)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci
    ");
}

function fetchHolidays(): array
{
    $context = stream_context_create([
        'http' => [
            'timeout' => 15,
            'header'  => 'Accept: application/json',
        ],
    ]);

    $json = file_get_contents(API_URL, false, $context);
    if ($json === false) {
        throw new RuntimeException('Failed to fetch data from API: ' . API_URL);
    }

    $data = json_decode($json, true, 512, JSON_THROW_ON_ERROR);

    $rows = [];
    foreach ($data as $yearEntry) {
        foreach ($yearEntry['content'] as $content) {
            $schoolYear = $content['schoolyear'];
            foreach ($content['vacations'] as $vacation) {
                $compulsory = $vacation['compulsorydates'] === 'true' ? 1 : 0;
                foreach ($vacation['regions'] as $regionEntry) {
                    $rows[] = [
                        'school_year'      => trim($schoolYear),
                        'vacation_type'    => trim($vacation['type']),
                        'compulsory_dates' => $compulsory,
                        'region'           => $regionEntry['region'],
                        'start_date'       => substr($regionEntry['startdate'], 0, 10),
                        'end_date'         => substr($regionEntry['enddate'], 0, 10),
                    ];
                }
            }
        }
    }

    return $rows;
}

function importHolidays(PDO $pdo, array $rows): int
{
    $pdo->exec('TRUNCATE TABLE school_holidays');

    $stmt = $pdo->prepare("
        INSERT INTO school_holidays
            (school_year, vacation_type, compulsory_dates, region, start_date, end_date)
        VALUES
            (:school_year, :vacation_type, :compulsory_dates, :region, :start_date, :end_date)
    ");

    $pdo->beginTransaction();
    try {
        foreach ($rows as $row) {
            $stmt->execute($row);
        }
        $pdo->commit();
    } catch (Throwable $e) {
        $pdo->rollBack();
        throw $e;
    }

    return count($rows);
}

// ── Main ──────────────────────────────────────────────────────────────────────

try {
    echo "Fetching school holidays from rijksoverheid.nl...\n";
    $rows = fetchHolidays();
    echo sprintf("  Fetched %d records.\n", count($rows));

    echo "Connecting to database...\n";
    $pdo = createConnection();

    echo "Creating table if not exists...\n";
    createTable($pdo);

    echo "Importing records...\n";
    $imported = importHolidays($pdo, $rows);
    echo sprintf("  Done. %d rows imported into school_holidays.\n", $imported);

} catch (Throwable $e) {
    fwrite(STDERR, 'Error: ' . $e->getMessage() . "\n");
    exit(1);
}
