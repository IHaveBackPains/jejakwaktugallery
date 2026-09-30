<?php
/**
 * API POST Add Photo
 * Menerima unggahan foto baru dan menyimpannya secara permanen ke MySQL phpMyAdmin
 * Tangguh terhadap missing fields, format gambar beragam, dan auto-resolver Pinterest
 */

require_once __DIR__ . '/session.php';
require_once __DIR__ . '/db.php';

// Proteksi Otorisasi: Hanya Admin yang dapat menempelkan foto baru
require_admin();

if (!isset($pdo) || !($pdo instanceof PDO)) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Koneksi database tidak tersedia']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Metode harus POST']);
    exit();
}


// 1. Ambil data dengan fallback aman
$album_id = isset($_POST['album_id']) && trim($_POST['album_id']) !== '' ? trim($_POST['album_id']) : '';
$title = isset($_POST['title']) && trim($_POST['title']) !== '' ? trim($_POST['title']) : '';
$artist_1 = isset($_POST['artist_1']) && trim($_POST['artist_1']) !== '' ? trim($_POST['artist_1']) : null;
$artist_2 = isset($_POST['artist_2']) && trim($_POST['artist_2']) !== '' ? trim($_POST['artist_2']) : null;
$year = isset($_POST['year']) ? trim($_POST['year']) : '';
$location = isset($_POST['location']) && trim($_POST['location']) !== '' ? trim($_POST['location']) : 'Indonesia';
$medium = isset($_POST['medium']) && trim($_POST['medium']) !== '' ? trim($_POST['medium']) : null;
$dimensions = isset($_POST['dimensions']) && trim($_POST['dimensions']) !== '' ? trim($_POST['dimensions']) : null;
$copyright = isset($_POST['copyright']) && trim($_POST['copyright']) !== '' ? trim($_POST['copyright']) : null;
$caption = isset($_POST['caption']) ? trim($_POST['caption']) : '';
$note = isset($_POST['note']) ? trim($_POST['note']) : '';
$url_src = isset($_POST['photo_url']) ? trim($_POST['photo_url']) : '';

// Jika title kosong, beri nama default
if (empty($title)) {
    $title = 'Karya Seni / Foto Kenangan ' . ($year ? "Tahun $year" : date('Y'));
}

// Pastikan album_id valid di tabel albums untuk mencegah Foreign Key Error
try {
    $stmtCheck = $pdo->prepare("SELECT COUNT(*) FROM `albums` WHERE `id` = :id");
    $stmtCheck->execute([':id' => $album_id]);
    if ($stmtCheck->fetchColumn() == 0) {
        // Jika album belum ada di DB, ambil ID album pertama yang valid dari DB
        $firstAlbum = $pdo->query("SELECT `id` FROM `albums` ORDER BY `created_at` ASC LIMIT 1")->fetchColumn();
        $album_id = $firstAlbum ?: 'jakarta-tempo-doeloe';
    }
} catch (Exception $e) {
    $album_id = 'jakarta-tempo-doeloe';
}


$image_src = '';

// 2. Cek apakah ada file upload lokal
if (isset($_FILES['photo_file']) && $_FILES['photo_file']['error'] === UPLOAD_ERR_OK) {
    $file = $_FILES['photo_file'];
    $allowed_extensions = ['jpg', 'jpeg', 'png', 'webp', 'gif', 'jfif', 'avif', 'bmp', 'svg', 'heic'];
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

    // Jika extension kosong, coba deteksi dari mime type
    if (empty($ext) && isset($file['type'])) {
        $mimeMap = [
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/webp' => 'webp',
            'image/gif' => 'gif',
            'image/bmp' => 'bmp',
            'image/avif' => 'avif'
        ];
        $ext = isset($mimeMap[$file['type']]) ? $mimeMap[$file['type']] : 'jpg';
    }

    if (!in_array($ext, $allowed_extensions)) {
        $ext = 'jpg'; // Fallback aman
    }

    $upload_dir = __DIR__ . '/../uploads/';
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }

    $filename = 'kenangan_' . time() . '_' . rand(1000, 9999) . '.' . $ext;
    $destination = $upload_dir . $filename;

    if (move_uploaded_file($file['tmp_name'], $destination)) {
        $image_src = 'uploads/' . $filename;
    }
}

// 3. Jika tidak ada file upload, cek apakah ada URL gambar
if (empty($image_src) && !empty($url_src)) {
    // Auto-resolver Pinterest
    if (stripos($url_src, 'pinterest.com/pin/') !== false || stripos($url_src, 'pin.it/') !== false) {
        $opts = [
            'http' => [
                'method' => 'GET',
                'header' => "User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36\r\n",
                'timeout' => 5
            ]
        ];
        $ctx = stream_context_create($opts);
        $html = @file_get_contents($url_src, false, $ctx);
        if ($html) {
            if (preg_match('/<meta[^>]+property=["\']og:image["\'][^>]+content=["\']([^"\']+)["\']/i', $html, $m)) {
                $url_src = $m[1];
            } elseif (preg_match('/https:\/\/i\.pinimg\.com\/originals\/[a-zA-Z0-9_\/]+\.(jpg|jpeg|png|webp)/i', $html, $m)) {
                $url_src = $m[0];
            } elseif (preg_match('/https:\/\/i\.pinimg\.com\/[0-9x]+\/[a-zA-Z0-9_\/]+\.(jpg|jpeg|png|webp)/i', $html, $m)) {
                $url_src = $m[0];
            }
        }
    }
    $image_src = $url_src;
}

// Fallback gambar vintage jika kosong
if (empty($image_src)) {
    $image_src = 'https://images.unsplash.com/photo-1529156069898-49953e39b3ac?auto=format&fit=crop&w=1200&q=85';
}

// Properti Polaroid Estetik
$randomTilts = ['-2.5deg', '-1.5deg', '1.8deg', '2.5deg', '-3deg', '2deg'];
$randomTapes = ['top-left', 'top-right', 'both'];
$tilt = $randomTilts[array_rand($randomTilts)];
$tape = $randomTapes[array_rand($randomTapes)];
$date_formatted = $year ? "Tahun $year" : "Kenangan Masa Lalu";

try {
    $sql = "INSERT INTO `photos` (`album_id`, `title`, `artist_1`, `artist_2`, `year`, `date`, `location`, `medium`, `dimensions`, `copyright`, `caption`, `image_src`, `thumb_src`, `tilt`, `tape`, `note`) 
            VALUES (:album_id, :title, :artist_1, :artist_2, :year, :date, :location, :medium, :dimensions, :copyright, :caption, :image_src, :thumb_src, :tilt, :tape, :note)";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':album_id' => $album_id,
        ':title' => $title,
        ':artist_1' => $artist_1,
        ':artist_2' => $artist_2,
        ':year' => $year,
        ':date' => $date_formatted,
        ':location' => $location,
        ':medium' => $medium,
        ':dimensions' => $dimensions,
        ':copyright' => $copyright,
        ':caption' => $caption ?: 'Foto karya kenangan yang baru saja ditempel di arsip Jejak Waktu.',
        ':image_src' => $image_src,
        ':thumb_src' => $image_src,
        ':tilt' => $tilt,
        ':tape' => $tape,
        ':note' => $note ?: 'Disumbangkan oleh kurator/pengunjung'
    ]);

    $newId = $pdo->lastInsertId();

    echo json_encode([
        'status' => 'success',
        'message' => 'Foto kenangan / karya seni berhasil disimpan ke database phpMyAdmin!',
        'data' => [
            'id' => (int)$newId,
            'album_id' => $album_id,
            'title' => $title,
            'artist_1' => $artist_1,
            'artist_2' => $artist_2,
            'year' => $year,
            'date' => $date_formatted,
            'location' => $location,
            'medium' => $medium,
            'dimensions' => $dimensions,
            'copyright' => $copyright,
            'caption' => $caption,
            'src' => $image_src,
            'thumb' => $image_src,
            'tilt' => $tilt,
            'tape' => $tape,
            'note' => $note
        ]
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
}
