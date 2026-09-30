<?php
/**
 * API DELETE Album
 * Menghapus album beserta foto-fotonya dari database dan storage (Khusus Role Admin)
 */

require_once __DIR__ . '/session.php';
require_once __DIR__ . '/db.php';

// Proteksi Otorisasi: Hanya Admin yang diizinkan menghapus album
require_admin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST' && $_SERVER['REQUEST_METHOD'] !== 'DELETE') {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Metode harus POST atau DELETE']);
    exit();
}

$album_id = isset($_POST['album_id']) ? trim($_POST['album_id']) : (isset($_GET['id']) ? trim($_GET['id']) : '');

if (empty($album_id)) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'ID album tidak valid.']);
    exit();
}

try {
    // 1. Hapus file fisik foto dalam album jika ada di uploads/
    $stmtPhotos = $pdo->prepare("SELECT `image_src` FROM `photos` WHERE `album_id` = :id");
    $stmtPhotos->execute([':id' => $album_id]);
    $photos = $stmtPhotos->fetchAll();

    foreach ($photos as $p) {
        if (!empty($p['image_src']) && strpos($p['image_src'], 'uploads/') === 0) {
            $filePath = __DIR__ . '/../' . $p['image_src'];
            if (file_exists($filePath)) {
                @unlink($filePath);
            }
        }
    }

    // 2. Hapus file fisik sampul album jika ada di uploads/
    $stmtCover = $pdo->prepare("SELECT `cover_image` FROM `albums` WHERE `id` = :id LIMIT 1");
    $stmtCover->execute([':id' => $album_id]);
    $cover = $stmtCover->fetch();
    if ($cover && !empty($cover['cover_image']) && strpos($cover['cover_image'], 'uploads/') === 0) {
        $coverPath = __DIR__ . '/../' . $cover['cover_image'];
        if (file_exists($coverPath)) {
            @unlink($coverPath);
        }
    }

    // 3. Hapus album dari database (cascade akan menghapus data photos)
    $stmtDelete = $pdo->prepare("DELETE FROM `albums` WHERE `id` = :id");
    $stmtDelete->execute([':id' => $album_id]);

    echo json_encode([
        'status' => 'success',
        'message' => "Buku album '$album_id' berhasil dihapus dari arsip database."
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
}
