<?php
/**
 * Koneksi Database MySQL / phpMyAdmin "Jejak Waktu"
 * Konfigurasi standar XAMPP (host: localhost, user: root, password: '')
 */

$isApiRequest = isset($_SERVER['REQUEST_URI']) && strpos($_SERVER['REQUEST_URI'], '/api/') !== false;

if (isset($_SERVER['REQUEST_METHOD']) && $isApiRequest) {
    header('Content-Type: application/json; charset=utf-8');
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type');

    if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
        http_response_code(200);
        exit();
    }
}

$host = '127.0.0.1';
$user = 'root';
$pass = '';
$db_name = 'jejak_waktu_db';

$pdo = null;

try {
    // Hubungkan ke MySQL server dengan timeout 2 detik agar tidak membekukan aplikasi
    $pdo = new PDO("mysql:host=$host;charset=utf8mb4", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
        PDO::ATTR_TIMEOUT => 2,
        PDO::MYSQL_ATTR_MULTI_STATEMENTS => true
    ]);

    // Buat database jika belum ada
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `$db_name` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    $pdo->exec("USE `$db_name`");

    // Pastikan tabel sudah dibuat dari database.sql jika belum ada
    $tableCheck = $pdo->query("SHOW TABLES LIKE 'albums'")->rowCount();
    if ($tableCheck === 0) {
        $sqlFile = __DIR__ . '/../database.sql';
        if (file_exists($sqlFile)) {
            $sql = file_get_contents($sqlFile);
            $pdo->exec($sql);
        }
    } else {
        // Auto-migration: Pastikan kolom metadata baru sudah ada di tabel photos
        $photoCols = $pdo->query("SHOW COLUMNS FROM `photos`")->fetchAll(PDO::FETCH_COLUMN);
        $newCols = [
            'artist_1' => "ALTER TABLE `photos` ADD COLUMN `artist_1` VARCHAR(255) DEFAULT NULL AFTER `title`",
            'artist_2' => "ALTER TABLE `photos` ADD COLUMN `artist_2` VARCHAR(255) DEFAULT NULL AFTER `artist_1`",
            'medium' => "ALTER TABLE `photos` ADD COLUMN `medium` VARCHAR(255) DEFAULT NULL AFTER `location`",
            'dimensions' => "ALTER TABLE `photos` ADD COLUMN `dimensions` VARCHAR(150) DEFAULT NULL AFTER `medium`",
            'copyright' => "ALTER TABLE `photos` ADD COLUMN `copyright` VARCHAR(255) DEFAULT NULL AFTER `dimensions`"
        ];
        foreach ($newCols as $colName => $alterSql) {
            if (!in_array($colName, $photoCols)) {
                try {
                    $pdo->exec($alterSql);
                } catch (Exception $ex) {
                    // Abaikan jika sudah ada
                }
            }
        }
    }

    // Pastikan tabel users sudah ada dan terisi akun default (Admin & User)
    $usersTableCheck = $pdo->query("SHOW TABLES LIKE 'users'")->rowCount();
    if ($usersTableCheck === 0) {
        $pdo->exec("CREATE TABLE IF NOT EXISTS `users` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `username` VARCHAR(50) NOT NULL UNIQUE,
            `password` VARCHAR(255) NOT NULL,
            `full_name` VARCHAR(100) NOT NULL,
            `role` ENUM('admin', 'user') NOT NULL DEFAULT 'user',
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

        $adminHash = '$2y$10$l01c1zhdtdGAM7mslH7TIuNSQ4M4oueyTH1WoIGHcLa5PnlzggGx2';
        $userHash = '$2y$10$JPmy6hjPxOYCK2JMbzs1OOn3GocT4ZmkZQ.b4QpijrnQbNCOyzhmy';

        $stmtUser = $pdo->prepare("INSERT INTO `users` (`username`, `password`, `full_name`, `role`) VALUES 
            (:adminUser, :adminPass, 'Arsiparis Utama', 'admin'),
            (:normalUser, :normalPass, 'Pengunjung Nostalgia', 'user')
            ON DUPLICATE KEY UPDATE `full_name` = VALUES(`full_name`), `role` = VALUES(`role`)");
        $stmtUser->execute([
            ':adminUser' => 'admin',
            ':adminPass' => $adminHash,
            ':normalUser' => 'user',
            ':normalPass' => $userHash
        ]);
    }

    // Pastikan tabel galleries sudah ada
    $galleriesTableCheck = $pdo->query("SHOW TABLES LIKE 'galleries'")->rowCount();
    if ($galleriesTableCheck === 0) {
        $galleriesScript = __DIR__ . '/galleries.php';
        if (file_exists($galleriesScript)) {
            // Include sekali untuk inisialisasi tabel & data
            require_once $galleriesScript;
        }
    }

    // Pastikan tabel feedbacks (Kritik & Saran) sudah ada
    $feedbacksTableCheck = $pdo->query("SHOW TABLES LIKE 'feedbacks'")->rowCount();
    if ($feedbacksTableCheck === 0) {
        $pdo->exec("CREATE TABLE IF NOT EXISTS `feedbacks` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `nama` VARCHAR(150) DEFAULT NULL,
            `email` VARCHAR(150) DEFAULT NULL,
            `jenis_masukan` ENUM('Kritik', 'Saran', 'Koreksi Data Galeri', 'Laporan Kesalahan', 'Lainnya') NOT NULL DEFAULT 'Saran',
            `isi` TEXT NOT NULL,
            `status` ENUM('Belum dibaca', 'Sudah dibaca', 'Ditindaklanjuti') NOT NULL DEFAULT 'Belum dibaca',
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    }

    // Pastikan tabel artist_profiles sudah ada dan memiliki kolom is_deleted
    $profilesTableCheck = $pdo->query("SHOW TABLES LIKE 'artist_profiles'")->rowCount();
    if ($profilesTableCheck === 0) {
        $pdo->exec("CREATE TABLE IF NOT EXISTS `artist_profiles` (
            `id` VARCHAR(100) NOT NULL PRIMARY KEY,
            `name` VARCHAR(255) DEFAULT NULL,
            `role` VARCHAR(255) DEFAULT NULL,
            `era` VARCHAR(100) DEFAULT NULL,
            `category` VARCHAR(50) DEFAULT NULL,
            `avatar` TEXT DEFAULT NULL,
            `bio` TEXT DEFAULT NULL,
            `location` VARCHAR(150) DEFAULT NULL,
            `is_deleted` TINYINT(1) NOT NULL DEFAULT 0,
            `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    } else {
        $artistCols = $pdo->query("SHOW COLUMNS FROM `artist_profiles`")->fetchAll(PDO::FETCH_COLUMN);
        if (!in_array('is_deleted', $artistCols)) {
            try {
                $pdo->exec("ALTER TABLE `artist_profiles` ADD COLUMN `is_deleted` TINYINT(1) NOT NULL DEFAULT 0 AFTER `location`");
            } catch (Exception $ex) {}
        }
    }

} catch (PDOException $e) {
    if (isset($_SERVER['REQUEST_METHOD']) && $isApiRequest) {
        http_response_code(500);
        echo json_encode([
            'status' => 'error',
            'message' => 'Gagal terhubung ke MySQL / phpMyAdmin: ' . $e->getMessage()
        ]);
        exit();
    } elseif (!isset($_SERVER['REQUEST_METHOD'])) {
        // Mode CLI: laporkan error dan keluar secara terkontrol
        fwrite(STDERR, json_encode([
            'status' => 'error',
            'message' => 'Koneksi MySQL gagal: ' . $e->getMessage()
        ]) . PHP_EOL);
        exit(1);
    } else {
        // Fallback untuk web page
        $pdo = null;
    }
}

