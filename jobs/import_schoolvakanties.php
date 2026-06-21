<?php

declare(strict_types=1);

// Load .env file if present (CLI usage)
(static function (): void {
    $envFile = __DIR__ . '/.env';
    if (!file_exists($envFile)) {
        return;
    }
    foreach (file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        if (str_starts_with(trim($line), '#')) {
            continue;
        }
        if (!str_contains($line, '=')) {
            continue;
        }
        [$key, $value] = explode('=', $line, 2);
        $key   = trim($key);
        $value = trim($value, " \t\n\r\0\x0B\"'");
        if ($key !== '' && getenv($key) === false) {
            putenv("$key=$value");
        }
    }
})();

define('DB_HOST',    getenv('DB_HOST')    ?: 'localhost');
define('DB_NAME',    getenv('DB_NAME')    ?: '');
define('DB_USER',    getenv('DB_USER')    ?: '');
define('DB_PASS',    getenv('DB_PASS')    ?: '');
define('DB_CHARSET', 'utf8mb4');

// API endpoint
define('API_URL', 'https://opendata.rijksoverheid.nl/v1/infotypes/schoolholidays?output=json');

function logLine(string $message): void
{
    echo sprintf('[%s] %s', date('H:i:s'), $message);
}

function createConnection(): PDO
{
    if (DB_NAME === '' || DB_USER === '') {
        throw new RuntimeException('Database credentials not configured. Copy .env.example to .env and fill in the values.');
    }

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
    logLine("Fetching school holidays from rijksoverheid.nl...\n");
    $rows = fetchHolidays();
    logLine(sprintf("  Fetched %d records.\n", count($rows)));

    logLine("Connecting to database...\n");
    $pdo = createConnection();

    logLine("Creating table if not exists...\n");
    createTable($pdo);

    logLine("Importing records...\n");
    $imported = importHolidays($pdo, $rows);
    logLine(sprintf("  Done. %d rows imported into school_holidays.\n", $imported));

} catch (Throwable $e) {
    fwrite(STDERR, sprintf('[%s] Error: %s' . "\n", date('H:i:s'), $e->getMessage()));
    exit(1);
}
