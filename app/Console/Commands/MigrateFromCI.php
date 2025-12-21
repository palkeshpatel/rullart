<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use PDO;
use PDOException;

class MigrateFromCI extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'migrate:from-ci
                            {--source= : CI Source database name}
                            {--target= : Laravel Target database name}
                            {--host=127.0.0.1 : Database host}
                            {--port=3306 : Database port}
                            {--username=root : Database username}
                            {--password= : Database password}
                            {--force : Force migration even if target tables have data}
                            {--check-only : Only check table/column matching without migrating}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Migrate data from CodeIgniter database to Laravel database with validation';

    /**
     * Available CI databases and their corresponding Laravel databases
     * Note: Ports 8000/8001 are Laravel server ports, not MySQL ports
     * MySQL uses port 3306 for all connections
     */
    protected $databaseMapping = [
        '1' => [
            'ci_db' => 'rullart_rullart',
            'laravel_db' => 'rullart_kuwaitbeta_laravel',
            'laravel_port' => 8000,  // Laravel server port, not MySQL
            'description' => 'Kuwait Database (Laravel runs on port 8000)'
        ],
        '2' => [
            'ci_db' => 'rullart_rullart_qatarbeta',
            'laravel_db' => 'rullart_qatarbeta_laravel',
            'laravel_port' => 8001,  // Laravel server port, not MySQL
            'description' => 'Qatar Database (Laravel runs on port 8001)'
        ],
    ];

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('=== CodeIgniter to Laravel Database Migration Tool ===');
        $this->newLine();

        // If no options provided, show interactive menu
        if (!$this->option('source') && !$this->option('target')) {
            $this->showDatabaseSelection();
            return;
        }

        // Get source and target databases
        $sourceDb = $this->option('source');
        $targetDb = $this->option('target');
        $host = $this->option('host');
        $port = $this->option('port');
        $username = $this->option('username');
        $password = $this->option('password');
        $force = $this->option('force');
        $checkOnly = $this->option('check-only');

        if (!$sourceDb || !$targetDb) {
            $this->error('Both --source and --target options are required, or use interactive mode.');
            return 1;
        }

        return $this->performMigration($sourceDb, $targetDb, $host, $port, $username, $password, $force, $checkOnly);
    }

    /**
     * Show database selection menu
     */
    protected function showDatabaseSelection()
    {
        $this->info('Available Database Migrations:');
        $this->newLine();

        foreach ($this->databaseMapping as $key => $config) {
            $this->line("  [{$key}] {$config['description']}");
            $this->line("      CI DB: {$config['ci_db']}");
            $this->line("      Laravel DB: {$config['laravel_db']}");
            $this->line("      Laravel Server Port: {$config['laravel_port']} (MySQL uses port 3306)");
            $this->newLine();
        }

        $choice = $this->choice('Select database to migrate', array_keys($this->databaseMapping));
        $config = $this->databaseMapping[$choice];

        $this->newLine();
        $this->info("Selected: {$config['description']}");
        $this->line("Source: {$config['ci_db']}");
        $this->line("Target: {$config['laravel_db']}");

        if (!$this->confirm('Continue with this selection?', true)) {
            $this->warn('Migration cancelled.');
            return;
        }

        $force = $this->option('force') ?: $this->confirm('Force migration (overwrite existing data)?', false);
        $checkOnly = $this->option('check-only') ?: $this->confirm('Check table/column matching only?', false);

        $this->performMigration(
            $config['ci_db'],
            $config['laravel_db'],
            $this->option('host'),
            $this->option('port'),
            $this->option('username'),
            $this->option('password'),
            $force,
            $checkOnly
        );
    }

    /**
     * Perform the migration
     */
    protected function performMigration($sourceDb, $targetDb, $host, $port, $username, $password, $force, $checkOnly)
    {
        $this->newLine();
        $this->info("Starting migration process...");
        $this->line("Source Database: {$sourceDb}");
        $this->line("Target Database: {$targetDb}");
        $this->newLine();

        try {
            // Connect to CI database
            $ciConnection = $this->connectToDatabase($host, $port, $sourceDb, $username, $password);
            if ($ciConnection === null) {
                return 1;
            }

            // Get all tables from CI database
            $ciTables = $this->getTablesFromDatabase($ciConnection, $sourceDb);
            if (empty($ciTables)) {
                $this->error("No tables found in source database: {$sourceDb}");
                return 1;
            }

            $this->info("Found " . count($ciTables) . " tables in CI database");
            $this->newLine();

            // Get Laravel tables
            $laravelTables = $this->getLaravelTables();

            // Check table and column matching
            $matchingResults = $this->checkTableMatching($ciConnection, $ciTables, $laravelTables);

            // Display matching results
            $this->displayMatchingResults($matchingResults);

            if ($checkOnly) {
                $this->info("Check-only mode: No data migration performed.");
                return 0;
            }

            // Confirm before proceeding
            if (!$force && !$this->confirm('Proceed with data migration?', true)) {
                $this->warn('Migration cancelled.');
                return 0;
            }

            // Perform data migration
            return $this->migrateData($ciConnection, $matchingResults, $force);
        } catch (\Exception $e) {
            $this->error("Fatal error: " . $e->getMessage());
            $this->error($e->getTraceAsString());
            return 1;
        }
    }

    /**
     * Connect to database
     */
    protected function connectToDatabase($host, $port, $database, $username, $password)
    {
        try {
            $this->info("Connecting to database: {$database}...");
            $connection = new PDO(
                "mysql:host={$host};port={$port};dbname={$database};charset=utf8mb4",
                $username,
                $password,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci"
                ]
            );
            $this->info("✓ Connected successfully");
            return $connection;
        } catch (PDOException $e) {
            $this->error("✗ Connection failed: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Get tables from database
     */
    protected function getTablesFromDatabase($connection, $database)
    {
        try {
            $stmt = $connection->query("SHOW TABLES");
            $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);

            // Filter out migrations table
            return array_filter($tables, function ($table) {
                return $table !== 'migrations';
            });
        } catch (PDOException $e) {
            $this->error("Error fetching tables: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get Laravel tables
     */
    protected function getLaravelTables()
    {
        $tables = [];
        try {
            $databaseName = DB::getDatabaseName();
            $allTables = DB::select("SHOW TABLES");

            if (empty($allTables)) {
                return $tables;
            }

            // Get the column name (varies by MySQL version)
            $firstTable = (array)$allTables[0];
            $key = array_keys($firstTable)[0];

            foreach ($allTables as $table) {
                $tableArray = (array)$table;
                $tables[] = $tableArray[$key];
            }
        } catch (\Exception $e) {
            $this->error("Error fetching Laravel tables: " . $e->getMessage());
        }

        return $tables;
    }

    /**
     * Check table and column matching
     */
    protected function checkTableMatching($ciConnection, $ciTables, $laravelTables)
    {
        $results = [];

        $this->info("Checking table and column matching...");
        $this->newLine();

        $progressBar = $this->output->createProgressBar(count($ciTables));
        $progressBar->start();

        foreach ($ciTables as $table) {
            $progressBar->advance();

            $result = [
                'table' => $table,
                'exists_in_laravel' => in_array($table, $laravelTables),
                'ci_columns' => [],
                'laravel_columns' => [],
                'matching_columns' => [],
                'missing_in_laravel' => [],
                'extra_in_laravel' => [],
            ];

            // Get CI table columns
            try {
                $stmt = $ciConnection->query("SHOW COLUMNS FROM `{$table}`");
                $result['ci_columns'] = $stmt->fetchAll(PDO::FETCH_COLUMN);
            } catch (PDOException $e) {
                $result['error'] = $e->getMessage();
                $results[] = $result;
                continue;
            }

            // Get Laravel table columns if table exists
            if ($result['exists_in_laravel']) {
                try {
                    $laravelColumns = DB::select("SHOW COLUMNS FROM `{$table}`");
                    $result['laravel_columns'] = array_column($laravelColumns, 'Field');

                    // Find matching and missing columns
                    $result['matching_columns'] = array_intersect($result['ci_columns'], $result['laravel_columns']);
                    $result['missing_in_laravel'] = array_diff($result['ci_columns'], $result['laravel_columns']);
                    $result['extra_in_laravel'] = array_diff($result['laravel_columns'], $result['ci_columns']);
                } catch (\Exception $e) {
                    $result['laravel_error'] = $e->getMessage();
                }
            }

            $results[] = $result;
        }

        $progressBar->finish();
        $this->newLine(2);

        return $results;
    }

    /**
     * Display matching results
     */
    protected function displayMatchingResults($results)
    {
        $this->info("=== Table Matching Results ===");
        $this->newLine();

        $totalTables = count($results);
        $existingTables = 0;
        $fullyMatchingTables = 0;

        // Prepare table data
        $tableData = [];

        foreach ($results as $result) {
            $exists = $result['exists_in_laravel'] ? '✓' : '✗';
            $ciColCount = count($result['ci_columns']);
            $laravelColCount = count($result['laravel_columns']);
            $matchingCount = count($result['matching_columns']);
            $missingCount = count($result['missing_in_laravel']);

            $status = 'OK';
            if (!$result['exists_in_laravel']) {
                $status = 'MISSING TABLE';
            } elseif ($missingCount > 0) {
                $status = 'MISSING COLUMNS';
            } elseif ($matchingCount === $ciColCount && $matchingCount === $laravelColCount) {
                $status = 'PERFECT MATCH';
                $fullyMatchingTables++;
            } else {
                $status = 'PARTIAL MATCH';
            }

            if ($result['exists_in_laravel']) {
                $existingTables++;
            }

            $missingColumns = $missingCount > 0
                ? implode(', ', array_slice($result['missing_in_laravel'], 0, 3)) . ($missingCount > 3 ? '...' : '')
                : '-';

            $tableData[] = [
                $result['table'],
                $exists,
                (string)$ciColCount,
                (string)$laravelColCount,
                (string)$matchingCount,
                $missingColumns,
                $status,
            ];
        }

        // Display table using Laravel's table method
        $this->table(
            ['Table', 'Exists', 'CI Columns', 'Laravel Columns', 'Matching', 'Missing', 'Status'],
            $tableData
        );

        // Collect missing tables
        $missingTables = [];
        foreach ($results as $result) {
            if (!$result['exists_in_laravel']) {
                $missingTables[] = $result['table'];
            }
        }

        $this->newLine();
        $this->info("Summary:");
        $this->line("  Total CI Tables: {$totalTables}");
        $this->line("  Tables in Laravel: {$existingTables}");
        $this->line("  Perfect Matches: {$fullyMatchingTables}");
        $missingCount = $totalTables - $existingTables;
        $this->line("  Missing Tables: {$missingCount}");

        if (!empty($missingTables)) {
            $this->newLine();
            $this->warn("Missing Table Names:");
            foreach ($missingTables as $missingTable) {
                $this->line("    - {$missingTable}");
            }
        }

        $this->newLine();
    }

    /**
     * Migrate data from CI to Laravel
     */
    protected function migrateData($ciConnection, $matchingResults, $force)
    {
        $this->info("=== Starting Data Migration ===");
        $this->newLine();

        $totalMigrated = 0;
        $totalSkipped = 0;
        $totalErrors = 0;

        $migratableTables = array_filter($matchingResults, function ($result) {
            return $result['exists_in_laravel'] && empty($result['error']) && empty($result['laravel_error']);
        });

        $this->info("Tables to migrate: " . count($migratableTables));
        $this->newLine();

        foreach ($migratableTables as $result) {
            $table = $result['table'];
            $this->line("Processing table: <comment>{$table}</comment>");

            try {
                // Get count from CI
                $ciCount = $ciConnection->query("SELECT COUNT(*) FROM `{$table}`")->fetchColumn();
                $this->line("  CI records: {$ciCount}");

                if ($ciCount == 0) {
                    $this->warn("  ⚠ No data to copy. Skipping...");
                    $totalSkipped++;
                    $this->newLine();
                    continue;
                }

                // Check Laravel count
                $laravelCount = DB::table($table)->count();
                $this->line("  Laravel records: {$laravelCount}");

                if ($laravelCount > 0 && !$force) {
                    if (!$this->confirm("  Table already has data. Overwrite? (y/n)", false)) {
                        $this->warn("  Skipping...");
                        $totalSkipped++;
                        $this->newLine();
                        continue;
                    }
                    // Clear existing data
                    DB::table($table)->truncate();
                }

                // Fetch and insert data
                $migrated = $this->copyTableData($ciConnection, $table, $result['matching_columns']);
                $totalMigrated += $migrated;
                $this->info("  ✓ Migrated {$migrated} records");
            } catch (\Exception $e) {
                $this->error("  ✗ Error: " . $e->getMessage());
                $totalErrors++;
            }

            $this->newLine();
        }

        $this->newLine();
        $this->info("=== Migration Summary ===");
        $this->line("  Migrated: {$totalMigrated} records");
        $this->line("  Skipped: {$totalSkipped} tables");
        $this->line("  Errors: {$totalErrors} tables");

        return $totalErrors > 0 ? 1 : 0;
    }

    /**
     * Copy table data
     */
    protected function copyTableData($ciConnection, $table, $columns)
    {
        $batchSize = 100;
        $inserted = 0;

        try {
            // Only select matching columns
            $columnList = '`' . implode('`, `', $columns) . '`';
            $ciData = $ciConnection->query("SELECT {$columnList} FROM `{$table}`")->fetchAll(PDO::FETCH_ASSOC);

            if (empty($ciData)) {
                return 0;
            }

            DB::beginTransaction();

            foreach (array_chunk($ciData, $batchSize) as $batch) {
                foreach ($batch as $row) {
                    // Handle invalid datetime values
                    foreach ($row as $key => $value) {
                        if (is_string($value) && (
                            $value === '0000-00-00 00:00:00' ||
                            $value === '0000-00-00' ||
                            preg_match('/^0000-\d{2}-\d{2}/', $value)
                        )) {
                            $row[$key] = null;
                        }
                    }

                    try {
                        DB::table($table)->insert($row);
                        $inserted++;
                    } catch (\Exception $e) {
                        // Skip duplicate entries
                        if (strpos($e->getMessage(), 'Duplicate entry') === false) {
                            throw $e;
                        }
                    }
                }
            }

            DB::commit();
            return $inserted;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
