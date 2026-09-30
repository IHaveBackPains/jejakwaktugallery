<?php
require_once __DIR__ . '/../api/db.php';

try {
    $sql = "CREATE TABLE IF NOT EXISTS `artist_profiles` (
        `id` VARCHAR(100) NOT NULL PRIMARY KEY,
        `name` VARCHAR(255) DEFAULT NULL,
        `role` VARCHAR(255) DEFAULT NULL,
        `era` VARCHAR(100) DEFAULT NULL,
        `category` VARCHAR(50) DEFAULT NULL,
        `avatar` TEXT DEFAULT NULL,
        `bio` TEXT DEFAULT NULL,
        `location` VARCHAR(150) DEFAULT NULL,
        `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    $pdo->exec($sql);
    echo "SUCCESS: Tabel artist_profiles berhasil dibuat atau sudah ada.\n";
    
    $stmt = $pdo->query("SHOW COLUMNS FROM `artist_profiles`");
    $cols = $stmt->fetchAll(PDO::FETCH_COLUMN);
    echo "Columns: " . implode(", ", $cols) . "\n";
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
