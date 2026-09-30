<?php
/**
 * API DELETE Photo
 * Menghapus foto kenangan dari database dan storage (Khusus Role Admin)
 */

require_once __DIR__ . '/session.php';
require_once __DIR__ . '/db.php';

// Proteksi Otorisasi: Hanya Admin yang diizinkan menghapus foto
require_admin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST' && $_SERVER['REQUEST_METHOD'] !== 'DELETE') {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Metode harus POST atau DELETE']);
    exit();
}

$photo_id = isset($_POST['photo_id']) ? (int)$_POST['photo_id'] : (isset($_GET['id']) ? (int)$_GET['id'] : 0);

if ($photo_id <= 0) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'ID foto tidak valid.']);
    exit();
}

try {
    // Ambil data path foto untuk menghapus file fisik jika ada di uploads/
    $stmtSelect = $pdo->prepare("SELECT `image_src` FROM `photos` WHERE `id` = :id LIMIT 1");
    $stmtSelect->execute([':id' => $photo_id]);
    $photo = $stmtSelect->fetch();

    if ($photo && !empty($photo['image_src'])) {
        if (strpos($photo['image_src'], 'uploads/') === 0) {
            $filePath = __DIR__ . '/../' . $photo['image_src'];
            if (file_exists($filePath)) {
                @unlink($filePath);
            }
        }
    }

    $stmtDelete = $pdo->prepare("DELETE FROM `photos` WHERE `id` = :id");
    $stmtDelete->execute([':id' => $photo_id]);

    echo json_encode([
        'status' => 'success',
        'message' => 'Foto kenangan berhasil dihapus dari arsip database.'
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
}
