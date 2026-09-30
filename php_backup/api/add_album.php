<?php
/**
 * API POST Add Album
 * Menerima data pembuatan buku album baru dan menyimpannya secara permanen ke MySQL phpMyAdmin
 */

require_once __DIR__ . '/session.php';
require_once __DIR__ . '/db.php';

// Proteksi Otorisasi: Hanya Admin yang dapat membuat album baru
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



$title = isset($_POST['title']) && trim($_POST['title']) !== '' ? trim($_POST['title']) : '';
$subtitle = isset($_POST['subtitle']) ? trim($_POST['subtitle']) : '';
$era = isset($_POST['era']) && trim($_POST['era']) !== '' ? trim($_POST['era']) : '1980-an';
$decade = isset($_POST['decade']) && trim($_POST['decade']) !== '' ? trim($_POST['decade']) : '1980s';
$category = isset($_POST['category']) && trim($_POST['category']) !== '' ? trim($_POST['category']) : 'keluarga-kehidupan';
$location = isset($_POST['location']) && trim($_POST['location']) !== '' ? trim($_POST['location']) : 'Indonesia';
$curator = isset($_POST['curator']) && trim($_POST['curator']) !== '' ? trim($_POST['curator']) : 'Koleksi Pribadi';
$description = isset($_POST['description']) ? trim($_POST['description']) : '';
$cover_color = isset($_POST['cover_color']) && trim($_POST['cover_color']) !== '' ? trim($_POST['cover_color']) : '#422a1d';
$url_src = isset($_POST['cover_url']) ? trim($_POST['cover_url']) : '';

if (empty($title)) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Judul album wajib diisi']);
    exit();
}

// Buat ID slug unik dari judul album
$slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $title)));
$slug = trim($slug, '-');
if (empty($slug)) {
    $slug = 'album';
}
$album_id = $slug . '-' . substr(time(), -5);

$cover_image = '';

// 1. Cek file upload gambar sampul
if (isset($_FILES['cover_file']) && $_FILES['cover_file']['error'] === UPLOAD_ERR_OK) {
    $file = $_FILES['cover_file'];
    $allowed_extensions = ['jpg', 'jpeg', 'png', 'webp', 'gif', 'jfif', 'avif', 'bmp'];
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

    if (!in_array($ext, $allowed_extensions)) {
        $ext = 'jpg';
    }

    $upload_dir = __DIR__ . '/../uploads/';
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }

    $filename = 'sampul_' . time() . '_' . rand(1000, 9999) . '.' . $ext;
    $destination = $upload_dir . $filename;

    if (move_uploaded_file($file['tmp_name'], $destination)) {
        $cover_image = 'uploads/' . $filename;
    }
}

// 2. Jika tidak ada file, gunakan URL sampul
if (empty($cover_image) && !empty($url_src)) {
    // Auto-resolver Pinterest untuk sampul
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
            }
        }
    }
    $cover_image = $url_src;
}

// Fallback gambar sampul vintage default
if (empty($cover_image)) {
    $cover_image = 'https://images.unsplash.com/photo-1578632767115-351597cf2477?auto=format&fit=crop&w=800&q=80';
}

$accent_color = '#c99e46';

try {
    $sql = "INSERT INTO `albums` (`id`, `title`, `subtitle`, `era`, `decade`, `category`, `cover_image`, `cover_color`, `accent_color`, `description`, `location`, `curator`) 
            VALUES (:id, :title, :subtitle, :era, :decade, :category, :cover_image, :cover_color, :accent_color, :description, :location, :curator)";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':id' => $album_id,
        ':title' => $title,
        ':subtitle' => $subtitle ?: 'Koleksi Foto & Memori Bersejarah',
        ':era' => $era,
        ':decade' => $decade,
        ':category' => $category,
        ':cover_image' => $cover_image,
        ':cover_color' => $cover_color,
        ':accent_color' => $accent_color,
        ':description' => $description ?: 'Lembaran kenangan dan cerita yang tersimpan dalam album Jejak Waktu.',
        ':location' => $location,
        ':curator' => $curator
    ]);

    echo json_encode([
        'status' => 'success',
        'message' => "Buku album '$title' berhasil dibuat dan disimpan ke database!",
        'data' => [
            'id' => $album_id,
            'title' => $title,
            'subtitle' => $subtitle,
            'era' => $era,
            'decade' => $decade,
            'category' => $category,
            'coverImage' => $cover_image,
            'coverColor' => $cover_color,
            'accentColor' => $accent_color,
            'description' => $description,
            'location' => $location,
            'curator' => $curator,
            'photos' => []
        ]
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
}
