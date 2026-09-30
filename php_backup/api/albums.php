<?php
/**
 * API GET Albums & Photos
 * Mengambil seluruh data album dan relasi fotonya dari MySQL phpMyAdmin
 */

require_once __DIR__ . '/session.php';
require_once __DIR__ . '/db.php';

// Proteksi Otorisasi: Pengguna harus login untuk membaca arsip
require_auth(true);

if (!isset($pdo) || !($pdo instanceof PDO)) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Koneksi database tidak tersedia']);
    exit();
}

$album_id = isset($_GET['id']) ? trim($_GET['id']) : null;



try {
    if ($album_id) {
        // Ambil 1 album spesifik
        $stmt = $pdo->prepare("SELECT * FROM `albums` WHERE `id` = :id");
        $stmt->execute([':id' => $album_id]);
        $album = $stmt->fetch();

        if (!$album) {
            http_response_code(404);
            echo json_encode(['status' => 'error', 'message' => 'Album tidak ditemukan']);
            exit();
        }

        // Ambil foto-foto album tersebut
        $stmtPhotos = $pdo->prepare("SELECT * FROM `photos` WHERE `album_id` = :id ORDER BY `id` DESC");
        $stmtPhotos->execute([':id' => $album_id]);
        $photos = $stmtPhotos->fetchAll();

        // Format mapping
        $albumFormatted = [
            'id' => $album['id'],
            'title' => $album['title'],
            'subtitle' => $album['subtitle'],
            'era' => $album['era'],
            'decade' => $album['decade'],
            'category' => $album['category'],
            'coverImage' => $album['cover_image'],
            'coverColor' => $album['cover_color'],
            'accentColor' => $album['accent_color'],
            'description' => $album['description'],
            'location' => $album['location'],
            'curator' => $album['curator'],
            'photos' => array_map(function($p) {
                return [
                    'id' => $p['id'],
                    'title' => $p['title'],
                    'artist_1' => $p['artist_1'] ?? null,
                    'artist_2' => $p['artist_2'] ?? null,
                    'year' => $p['year'],
                    'date' => $p['date'],
                    'location' => $p['location'],
                    'medium' => $p['medium'] ?? null,
                    'dimensions' => $p['dimensions'] ?? null,
                    'copyright' => $p['copyright'] ?? null,
                    'caption' => $p['caption'],
                    'src' => $p['image_src'],
                    'thumb' => $p['thumb_src'] ?: $p['image_src'],
                    'tilt' => $p['tilt'],
                    'tape' => $p['tape'],
                    'note' => $p['note']
                ];
            }, $photos)
        ];

        echo json_encode(['status' => 'success', 'data' => $albumFormatted]);
        exit();
    }

    // Ambil album dan foto dengan filter pencarian jika ada
    $search = isset($_GET['search']) ? trim($_GET['search']) : '';
    $matchedPhotos = [];

    if ($search !== '') {
        $term = '%' . $search . '%';
        $stmt = $pdo->prepare("SELECT * FROM `albums` 
                WHERE `title` LIKE ? 
                   OR `subtitle` LIKE ? 
                   OR `description` LIKE ? 
                   OR `era` LIKE ? 
                   OR `location` LIKE ? 
                ORDER BY `created_at` ASC");
        $stmt->execute([$term, $term, $term, $term, $term]);

        $stmtPhotosMatch = $pdo->prepare("SELECT p.*, a.title AS album_title, a.era AS album_era, a.decade AS album_decade 
                FROM `photos` p 
                LEFT JOIN `albums` a ON a.id = p.album_id 
                WHERE p.`title` LIKE ? 
                   OR p.`caption` LIKE ? 
                   OR p.`location` LIKE ? 
                   OR p.`note` LIKE ? 
                   OR p.`artist_1` LIKE ? 
                   OR p.`artist_2` LIKE ? 
                   OR p.`medium` LIKE ? 
                   OR p.`year` LIKE ? 
                ORDER BY p.`id` DESC");
        $stmtPhotosMatch->execute([$term, $term, $term, $term, $term, $term, $term, $term]);
        $matchedPhotos = $stmtPhotosMatch->fetchAll();
    } else {
        $stmt = $pdo->query("SELECT * FROM `albums` ORDER BY `created_at` ASC");
    }
    $albums = $stmt->fetchAll();

    $result = [];
    foreach ($albums as $album) {
        $stmtPhotos = $pdo->prepare("SELECT * FROM `photos` WHERE `album_id` = :id ORDER BY `id` DESC");
        $stmtPhotos->execute([':id' => $album['id']]);
        $photos = $stmtPhotos->fetchAll();

        $result[] = [
            'id' => $album['id'],
            'title' => $album['title'],
            'subtitle' => $album['subtitle'],
            'era' => $album['era'],
            'decade' => $album['decade'],
            'category' => $album['category'],
            'coverImage' => $album['cover_image'],
            'coverColor' => $album['cover_color'],
            'accentColor' => $album['accent_color'],
            'description' => $album['description'],
            'location' => $album['location'],
            'curator' => $album['curator'],
            'photos' => array_map(function($p) {
                return [
                    'id' => $p['id'],
                    'title' => $p['title'],
                    'artist_1' => $p['artist_1'] ?? null,
                    'artist_2' => $p['artist_2'] ?? null,
                    'year' => $p['year'],
                    'date' => $p['date'],
                    'location' => $p['location'],
                    'medium' => $p['medium'] ?? null,
                    'dimensions' => $p['dimensions'] ?? null,
                    'copyright' => $p['copyright'] ?? null,
                    'caption' => $p['caption'],
                    'src' => $p['image_src'],
                    'thumb' => $p['thumb_src'] ?: $p['image_src'],
                    'tilt' => $p['tilt'],
                    'tape' => $p['tape'],
                    'note' => $p['note']
                ];
            }, $photos)
        ];
    }

    echo json_encode([
        'status' => 'success', 
        'data' => $result,
        'matched_photos' => $matchedPhotos
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
