<?php
/**
 * Database Export Script
 * This script exports the chat threading database to a SQL file
 */

// Database configuration
$host = 'localhost';
$username = 'root'; // Change to your database username
$password = ''; // Change to your database password
$database = 'chat_threading_db';
$exportFile = 'chat_threading_export_' . date('Y-m-d_H-i-s') . '.sql';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$database", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "<h2>Exporting Database: $database</h2>";
    
    // Start building the export content
    $export = "-- Chat Threading Database Export\n";
    $export .= "-- Generated on: " . date('Y-m-d H:i:s') . "\n";
    $export .= "-- Database: $database\n\n";
    
    $export .= "SET SQL_MODE = \"NO_AUTO_VALUE_ON_ZERO\";\n";
    $export .= "SET AUTOCOMMIT = 0;\n";
    $export .= "START TRANSACTION;\n";
    $export .= "SET time_zone = \"+00:00\";\n\n";
    
    $export .= "-- Create database\n";
    $export .= "CREATE DATABASE IF NOT EXISTS `$database` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;\n";
    $export .= "USE `$database`;\n\n";
    
    // Get all tables
    $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    
    foreach ($tables as $table) {
        echo "<p>Exporting table: <strong>$table</strong></p>";
        
        // Get table structure
        $createTable = $pdo->query("SHOW CREATE TABLE `$table`")->fetch();
        $export .= "-- Table structure for table `$table`\n";
        $export .= "DROP TABLE IF EXISTS `$table`;\n";
        $export .= $createTable['Create Table'] . ";\n\n";
        
        // Get table data
        $rows = $pdo->query("SELECT * FROM `$table`")->fetchAll(PDO::FETCH_ASSOC);
        
        if (!empty($rows)) {
            $export .= "-- Dumping data for table `$table`\n";
            
            // Get column names
            $columns = array_keys($rows[0]);
            $columnList = '`' . implode('`, `', $columns) . '`';
            
            $export .= "INSERT INTO `$table` ($columnList) VALUES\n";
            
            $values = [];
            foreach ($rows as $row) {
                $rowValues = [];
                foreach ($row as $value) {
                    if ($value === null) {
                        $rowValues[] = 'NULL';
                    } else {
                        $rowValues[] = "'" . addslashes($value) . "'";
                    }
                }
                $values[] = '(' . implode(', ', $rowValues) . ')';
            }
            
            $export .= implode(",\n", $values) . ";\n\n";
        }
    }
    
    $export .= "COMMIT;\n";
    
    // Save to file
    file_put_contents($exportFile, $export);
    
    echo "<h3 style='color: green;'>✓ Export completed successfully!</h3>";
    echo "<p><strong>Export file:</strong> <a href='$exportFile' download>$exportFile</a></p>";
    echo "<p><strong>File size:</strong> " . number_format(filesize($exportFile)) . " bytes</p>";
    
    // Show preview
    echo "<h4>Export Preview (first 1000 characters):</h4>";
    echo "<pre style='background: #f5f5f5; padding: 10px; border: 1px solid #ddd; max-height: 300px; overflow-y: auto;'>";
    echo htmlspecialchars(substr($export, 0, 1000));
    if (strlen($export) > 1000) {
        echo "\n... (truncated)";
    }
    echo "</pre>";
    
} catch (PDOException $e) {
    echo "<h3 style='color: red;'>Export failed!</h3>";
    echo "<p>Error: " . $e->getMessage() . "</p>";
}
?>
