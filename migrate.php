<?php

$pdo = require __DIR__ . '/src/database.php';

$migrationDir = __DIR__ . '/migrations';
if (!is_dir($migrationDir)) {
    die("Migration directory not found: $migrationDir\n");
}

$migrationFiles = glob($migrationDir . '/*.sql');

if (empty($migrationFiles)) {
    die("No migration files found in $migrationDir\n");
}

foreach ($migrationFiles as $file) {
    $sql = file_get_contents($file);
    echo "Running migration: $file\n";

    try {
        $pdo->exec($sql);
        echo "\e[32m✓ Migration executed successfully.\e[0m\n";
    } catch (PDOException $e) {
        echo "\e[31m✗ Error running migration $file: " . $e->getMessage() . "\e[0m\n";
    }
}
