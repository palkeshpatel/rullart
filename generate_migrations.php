<?php

/**
 * Script to generate Laravel migrations from SQL CREATE TABLE statements
 * Usage: php generate_migrations.php
 */

$docFile = __DIR__ . '/doc.MD';
$migrationsDir = __DIR__ . '/database/migrations';

if (!file_exists($docFile)) {
    die("Error: doc.MD file not found!\n");
}

$content = file_get_contents($docFile);

// Extract all CREATE TABLE statements
preg_match_all('/CREATE TABLE `([^`]+)`\s*\((.*?)\)\s*ENGINE=/s', $content, $matches, PREG_SET_ORDER);

if (empty($matches)) {
    die("No CREATE TABLE statements found!\n");
}

echo "Found " . count($matches) . " tables to migrate\n";

$timestamp = date('Y_m_d_His');
$counter = 0;

foreach ($matches as $match) {
    $tableName = $match[1];
    $tableDef = $match[2];

    // Skip tables that already have migrations
    $existingMigrations = glob($migrationsDir . '/*_create_' . $tableName . '_table.php');
    if (!empty($existingMigrations)) {
        echo "Skipping $tableName - migration already exists\n";
        continue;
    }

    $counter++;
    $migrationName = $timestamp . sprintf('%04d', $counter) . '_create_' . $tableName . '_table.php';
    $migrationPath = $migrationsDir . '/' . $migrationName;

    // Parse columns from table definition
    $columns = parseTableDefinition($tableDef, $tableName);

    // Generate migration file
    $migrationContent = generateMigration($tableName, $columns);

    file_put_contents($migrationPath, $migrationContent);
    echo "Created migration for table: $tableName\n";
}

echo "\nDone! Created $counter migration files.\n";

function parseTableDefinition($def, $tableName)
{
    $columns = [];
    $lines = explode("\n", $def);

    foreach ($lines as $line) {
        $line = trim($line);
        if (empty($line) || strpos($line, 'PRIMARY KEY') !== false || strpos($line, 'KEY') !== false || strpos($line, 'UNIQUE') !== false) {
            continue;
        }

        // Remove trailing comma
        $line = rtrim($line, ',');

        if (preg_match('/^`([^`]+)`\s+(.+)$/', $line, $colMatch)) {
            $colName = $colMatch[1];
            $colDef = trim($colMatch[2]);
            $columns[] = parseColumn($colName, $colDef);
        }
    }

    return $columns;
}

function parseColumn($name, $def)
{
    $column = ['name' => $name, 'type' => 'string', 'nullable' => false, 'default' => null];

    // Check for nullable
    if (stripos($def, 'DEFAULT NULL') !== false || stripos($def, 'NULL') !== false) {
        $column['nullable'] = true;
    }

    // Parse data types
    if (preg_match('/^(tinyint|smallint|mediumint|int|bigint)/i', $def, $typeMatch)) {
        $intType = strtolower($typeMatch[1]);
        if (stripos($def, 'UNSIGNED') !== false) {
            $column['unsigned'] = true;
        }

        if ($intType === 'bigint' && stripos($def, 'UNSIGNED') !== false) {
            $column['type'] = 'unsignedBigInteger';
        } elseif ($intType === 'int' && stripos($def, 'UNSIGNED') !== false) {
            $column['type'] = 'unsignedInteger';
        } elseif ($intType === 'bigint') {
            $column['type'] = 'bigInteger';
        } else {
            $column['type'] = 'integer';
        }

        // Check for auto increment (usually on primary key)
        if (stripos($def, 'AUTO_INCREMENT') !== false || stripos($def, 'NOT NULL') !== false && $name === 'id') {
            // Will be handled as primary key
        }
    } elseif (preg_match('/^varchar\((\d+)\)/i', $def, $varcharMatch)) {
        $column['type'] = 'string';
        $column['length'] = (int)$varcharMatch[1];
    } elseif (preg_match('/^char\((\d+)\)/i', $def, $charMatch)) {
        $column['type'] = 'char';
        $column['length'] = (int)$charMatch[1];
    } elseif (preg_match('/^(tinytext|text|mediumtext|longtext)/i', $def, $textMatch)) {
        $column['type'] = 'text';
        if (strtolower($textMatch[1]) === 'longtext') {
            $column['type'] = 'longText';
        }
    } elseif (preg_match('/^(decimal|numeric)\((\d+),(\d+)\)/i', $def, $decimalMatch)) {
        $column['type'] = 'decimal';
        $column['precision'] = (int)$decimalMatch[2];
        $column['scale'] = (int)$decimalMatch[3];
    } elseif (preg_match('/^(float|double)/i', $def)) {
        $column['type'] = 'float';
    } elseif (preg_match('/^date/i', $def)) {
        $column['type'] = 'date';
    } elseif (preg_match('/^(datetime|timestamp)/i', $def)) {
        $column['type'] = 'dateTime';
        if (stripos($def, 'DEFAULT current_timestamp') !== false) {
            $column['default'] = 'useCurrent';
        }
    } elseif (preg_match('/^time/i', $def)) {
        $column['type'] = 'time';
    } elseif (preg_match('/^tinyint\(1\)/i', $def) || (preg_match('/^bit\(1\)/i', $def))) {
        $column['type'] = 'boolean';
    }

    // Parse default values
    if (preg_match("/DEFAULT\s+([^,\s]+)/i", $def, $defaultMatch)) {
        $defaultVal = trim($defaultMatch[1], "'\"");
        if ($defaultVal !== 'NULL' && $defaultVal !== 'current_timestamp()') {
            if (is_numeric($defaultVal)) {
                $column['default'] = (float)$defaultVal == (int)$defaultVal ? (int)$defaultVal : (float)$defaultVal;
            } elseif ($defaultVal === '0' || $defaultVal === '1') {
                $column['default'] = (int)$defaultVal;
            } else {
                $column['default'] = $defaultVal;
            }
        }
    }

    return $column;
}

function generateMigration($tableName, $columns)
{
    $className = 'Create' . str_replace('_', '', ucwords($tableName, '_')) . 'Table';

    $upMethod = "    public function up(): void\n    {\n        Schema::create('$tableName', function (Blueprint \$table) {\n";

    $hasPrimaryKey = false;
    foreach ($columns as $col) {
        $line = "            ";

        // Check if this is the primary key (usually 'id' or table name + 'id')
        $isPrimaryKey = ($col['name'] === 'id' ||
            $col['name'] === $tableName . 'id' ||
            preg_match('/^(\w+)id$/', $col['name'], $pkMatch) && $pkMatch[1] . 'id' === $col['name']);

        if ($isPrimaryKey && !$hasPrimaryKey) {
            $hasPrimaryKey = true;
            if ($col['type'] === 'unsignedBigInteger' || $col['type'] === 'bigInteger') {
                $line .= "\$table->unsignedBigInteger('{$col['name']}')->primary();";
            } elseif ($col['type'] === 'unsignedInteger' || $col['type'] === 'integer') {
                $line .= "\$table->integer('{$col['name']}')->primary();";
            } else {
                $line .= generateColumnLine($col);
            }
        } else {
            $line .= generateColumnLine($col);
        }

        $upMethod .= $line . "\n";
    }

    $upMethod .= "        });\n    }";

    return <<<PHP
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
{$upMethod}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('$tableName');
    }
};
PHP;
}

function generateColumnLine($col)
{
    $line = "\$table->";

    // Handle special types
    if ($col['type'] === 'string' && isset($col['length'])) {
        $line .= "string('{$col['name']}', {$col['length']})";
    } elseif ($col['type'] === 'decimal' && isset($col['precision']) && isset($col['scale'])) {
        $line .= "decimal('{$col['name']}', {$col['precision']}, {$col['scale']})";
    } elseif ($col['type'] === 'char' && isset($col['length'])) {
        $line .= "char('{$col['name']}', {$col['length']})";
    } else {
        $line .= "{$col['type']}('{$col['name']}')";
    }

    // Add unsigned if needed
    if (isset($col['unsigned']) && $col['unsigned']) {
        $line .= "->unsigned()";
    }

    // Add nullable
    if ($col['nullable']) {
        $line .= "->nullable()";
    }

    // Add default
    if ($col['default'] !== null) {
        if ($col['default'] === 'useCurrent') {
            $line .= "->useCurrent()";
        } else {
            $defaultVal = is_string($col['default']) ? "'{$col['default']}'" : $col['default'];
            $line .= "->default({$defaultVal})";
        }
    }

    $line .= ";";

    return $line;
}