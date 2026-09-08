<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$pdo = DB::connection()->getPdo();
$tablesStmt = $pdo->query("SELECT name FROM sqlite_master WHERE type='table' AND name NOT LIKE 'sqlite_%' ORDER BY name");
$tables = $tablesStmt->fetchAll(PDO::FETCH_COLUMN);

$sql = "-- ========================================================\n";
$sql .= "-- Hospital Management System (HMS) - Full Database Dump\n";
$sql .= "-- Apex Horizon International Medical Center\n";
$sql .= "-- Compatible with MySQL / MariaDB / XAMPP phpMyAdmin\n";
$sql .= "-- Generated: " . date('Y-m-d H:i:s') . "\n";
$sql .= "-- ========================================================\n\n";
$sql .= "CREATE DATABASE IF NOT EXISTS `hospital` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;\n";
$sql .= "USE `hospital`;\n\n";
$sql .= "SET FOREIGN_KEY_CHECKS = 0;\n\n";

foreach ($tables as $table) {
    $sql .= "-- --------------------------------------------------------\n";
    $sql .= "-- Table structure for table `{$table}`\n";
    $sql .= "-- --------------------------------------------------------\n";
    $sql .= "DROP TABLE IF EXISTS `{$table}`;\n";

    // Get SQLite create SQL
    $createStmt = $pdo->query("SELECT sql FROM sqlite_master WHERE type='table' AND name='{$table}'");
    $createSql = $createStmt->fetchColumn();

    // Convert SQLite CREATE TABLE to MySQL CREATE TABLE
    $mysqlCreate = $createSql;
    // Fix quotes
    $mysqlCreate = preg_replace('/"([^"]+)"/i', '`$1`', $mysqlCreate);
    // Remove check constraints like check (`status` in ('a', 'b'))
    $mysqlCreate = preg_replace('/check\s*\([^)]*in\s*\([^)]*\)\)/i', '', $mysqlCreate);
    $mysqlCreate = preg_replace('/check\s*\([^)]+\)/i', '', $mysqlCreate);
    // Fix auto increments
    $mysqlCreate = preg_replace('/`id`\s+integer\s+primary\s+key\s+autoincrement\s+not\s+null/i', '`id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY', $mysqlCreate);
    $mysqlCreate = preg_replace('/`id`\s+BIGINT\s+primary\s+key\s+autoincrement\s+not\s+null/i', '`id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY', $mysqlCreate);
    $mysqlCreate = preg_replace('/integer\s+primary\s+key\s+autoincrement\s+not\s+null/i', 'BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY', $mysqlCreate);
    $mysqlCreate = preg_replace('/primary\s+key\s+autoincrement\s+not\s+null/i', 'NOT NULL AUTO_INCREMENT PRIMARY KEY', $mysqlCreate);
    $mysqlCreate = preg_replace('/varchar\b/i', 'VARCHAR(255)', $mysqlCreate);
    $mysqlCreate = preg_replace('/datetime\b/i', 'DATETIME', $mysqlCreate);
    $mysqlCreate = preg_replace('/float\b/i', 'DOUBLE', $mysqlCreate);
    $mysqlCreate = preg_replace('/boolean\b/i', 'TINYINT(1)', $mysqlCreate);
    $mysqlCreate = preg_replace('/integer\b/i', 'BIGINT', $mysqlCreate);
    
    // Add ENGINE and CHARSET
    $mysqlCreate = rtrim($mysqlCreate, ';') . " ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;\n\n";
    $sql .= $mysqlCreate;

    // Get Rows
    $rowsStmt = $pdo->query("SELECT * FROM `{$table}`");
    $rows = $rowsStmt->fetchAll(PDO::FETCH_ASSOC);

    if (count($rows) > 0) {
        $sql .= "-- Dumping data for table `{$table}`\n";
        $columns = array_keys($rows[0]);
        $colList = implode('`, `', $columns);

        $sql .= "INSERT INTO `{$table}` (`{$colList}`) VALUES\n";
        $rowValues = [];
        foreach ($rows as $row) {
            $vals = [];
            foreach ($row as $val) {
                if ($val === null) {
                    $vals[] = "NULL";
                } elseif (is_numeric($val) && !is_string($val)) {
                    $vals[] = $val;
                } else {
                    $vals[] = $pdo->quote((string)$val);
                }
            }
            $rowValues[] = "(" . implode(', ', $vals) . ")";
        }
        $sql .= implode(",\n", $rowValues) . ";\n\n";
    }
}

$sql .= "SET FOREIGN_KEY_CHECKS = 1;\n";

file_put_contents(__DIR__ . '/hospital.sql', $sql);
echo "Exported full MySQL database to database/hospital.sql (" . strlen($sql) . " bytes)\n";
