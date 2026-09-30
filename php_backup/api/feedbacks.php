<?php
/**
 * API Feedbacks - Pengelolaan Kritik & Saran Pengunjung
 * 
 * Endpoint:
 * - POST /api/feedbacks.php                      → Kirim Kritik & Saran baru (Publik)
 * - GET  /api/feedbacks.php                      → Ambil daftar masukan (Admin Only)
 * - GET  /api/feedbacks.php?action=count         → Ambil jumlah masukan belum dibaca (Admin Only)
 * - POST /api/feedbacks.php?action=update_status → Update status masukan (Admin Only)
 * - POST /api/feedbacks.php?action=delete        → Hapus masukan (Admin Only)
 */

require_once __DIR__ . '/session.php';
require_once __DIR__ . '/db.php';

header('Content-Type: application/json; charset=utf-8');

if (!isset($pdo) || !($pdo instanceof PDO)) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Koneksi ke database tidak tersedia.'
    ]);
    exit();
}

$method = $_SERVER['REQUEST_METHOD'];
$action = isset($_GET['action']) ? trim($_GET['action']) : '';

// Helper untuk membaca request body (JSON atau Form URL Encoded)
function get_request_data() {
    $input = file_get_contents('php://input');
    $data = [];
    if (!empty($input)) {
        $json = json_decode($input, true);
        if (is_array($json)) {
            $data = $json;
        } else {
            parse_str($input, $data);
        }
    }
    if (!empty($_POST)) {
        $data = array_merge($data, $_POST);
    }
    return $data;
}

// =========================================================================
// 1. PUBLIC: KIRIM KRITIK & SARAN (POST)
// =========================================================================
if ($method === 'POST' && ($action === '' || $action === 'create')) {
    $data = get_request_data();

    // Perlindungan Anti-Spam: Honeypot field (harus kosong)
    if (!empty($data['hp_check']) || !empty($data['website_hp'])) {
        // Bot terdeteksi, berikan respon sukses palsu agar bot tidak mengulangi
        echo json_encode([
            'status' => 'success',
            'message' => 'Terima kasih atas kritik dan saran yang Anda sampaikan.'
        ]);
        exit();
    }

    // Rate Limiting sederhana per session (jeda minimal 4 detik antar submit)
    $now = time();
    if (isset($_SESSION['last_feedback_time']) && ($now - $_SESSION['last_feedback_time']) < 4) {
        http_response_code(429);
        echo json_encode([
            'status' => 'error',
            'message' => 'Mohon tunggu beberapa detik sebelum mengirimkan masukan kembali.'
        ]);
        exit();
    }

    // Ekstraksi & Sanitasi Input
    $nama  = isset($data['nama']) ? trim(strip_tags($data['nama'])) : '';
    $email = isset($data['email']) ? trim(strip_tags($data['email'])) : '';
    $jenis = isset($data['jenis_masukan']) ? trim(strip_tags($data['jenis_masukan'])) : 'Saran';
    $isi   = isset($data['isi']) ? trim(strip_tags($data['isi'])) : '';

    // Validasi field wajib
    if (empty($isi)) {
        http_response_code(422);
        echo json_encode([
            'status' => 'error',
            'message' => 'Kolom isi kritik & saran wajib diisi.'
        ]);
        exit();
    }

    if (mb_strlen($isi) < 5) {
        http_response_code(422);
        echo json_encode([
            'status' => 'error',
            'message' => 'Isi kritik & saran minimal terdiri dari 5 karakter agar informasi jelas.'
        ]);
        exit();
    }

    // Validasi format email jika diisi
    if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        http_response_code(422);
        echo json_encode([
            'status' => 'error',
            'message' => 'Format alamat email tidak valid. Mohon periksa kembali atau kosongkan jika tidak ingin menyertakan email.'
        ]);
        exit();
    }

    // Validasi pilihan jenis masukan
    $validTypes = ['Kritik', 'Saran', 'Koreksi Data Galeri', 'Laporan Kesalahan', 'Lainnya'];
    if (!in_array($jenis, $validTypes)) {
        $jenis = 'Saran';
    }

    // Batasi panjang string
    $namaClean  = !empty($nama) ? mb_substr($nama, 0, 150) : null;
    $emailClean = !empty($email) ? mb_substr($email, 0, 150) : null;

    try {
        $stmt = $pdo->prepare("
            INSERT INTO `feedbacks` (`nama`, `email`, `jenis_masukan`, `isi`, `status`, `created_at`)
            VALUES (:nama, :email, :jenis, :isi, 'Belum dibaca', NOW())
        ");
        $stmt->execute([
            ':nama'  => $namaClean,
            ':email' => $emailClean,
            ':jenis' => $jenis,
            ':isi'   => $isi
        ]);

        $insertId = (int)$pdo->lastInsertId();
        $_SESSION['last_feedback_time'] = $now;

        http_response_code(201);
        echo json_encode([
            'status' => 'success',
            'message' => 'Kritik & saran Anda berhasil dikirim! Terima kasih telah berkontribusi merawat arsip Jejak Waktu.',
            'data' => [
                'id' => $insertId,
                'nama' => $namaClean,
                'jenis_masukan' => $jenis,
                'status' => 'Belum dibaca',
                'created_at' => date('Y-m-d H:i:s')
            ]
        ]);
        exit();

    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode([
            'status' => 'error',
            'message' => 'Terjadi kesalahan sistem saat menyimpan masukan: ' . $e->getMessage()
        ]);
        exit();
    }
}

// =========================================================================
// 2. ADMIN ONLY ENDPOINTS (GET, UPDATE STATUS, DELETE)
// =========================================================================
$currentUser = require_admin();

// Helper untuk menghitung statistik status
function get_feedback_counts($pdo) {
    $counts = [
        'total' => 0,
        'unread' => 0,
        'read' => 0,
        'followed_up' => 0
    ];
    try {
        $stmt = $pdo->query("
            SELECT `status`, COUNT(*) as cnt 
            FROM `feedbacks` 
            GROUP BY `status`
        ");
        while ($row = $stmt->fetch()) {
            $c = (int)$row['cnt'];
            $counts['total'] += $c;
            if ($row['status'] === 'Belum dibaca') {
                $counts['unread'] = $c;
            } elseif ($row['status'] === 'Sudah dibaca') {
                $counts['read'] = $c;
            } elseif ($row['status'] === 'Ditindaklanjuti') {
                $counts['followed_up'] = $c;
            }
        }
    } catch (Exception $ex) {}
    return $counts;
}

// Endpoint: Ambil hanya jumlah belum dibaca / counts
if ($method === 'GET' && $action === 'count') {
    $counts = get_feedback_counts($pdo);
    echo json_encode([
        'status' => 'success',
        'counts' => $counts,
        'unread' => $counts['unread']
    ]);
    exit();
}

// Endpoint: Update Status (POST ?action=update_status)
if ($method === 'POST' && $action === 'update_status') {
    $data = get_request_data();
    $id = isset($data['id']) ? (int)$data['id'] : 0;
    $newStatus = isset($data['status']) ? trim($data['status']) : '';

    $allowedStatuses = ['Belum dibaca', 'Sudah dibaca', 'Ditindaklanjuti'];
    if ($id <= 0 || !in_array($newStatus, $allowedStatuses)) {
        http_response_code(422);
        echo json_encode([
            'status' => 'error',
            'message' => 'ID masukan atau pilihan status tidak valid.'
        ]);
        exit();
    }

    try {
        $stmt = $pdo->prepare("UPDATE `feedbacks` SET `status` = :st WHERE `id` = :id");
        $stmt->execute([':st' => $newStatus, ':id' => $id]);

        $counts = get_feedback_counts($pdo);

        echo json_encode([
            'status' => 'success',
            'message' => "Status masukan #$id berhasil diubah menjadi '$newStatus'.",
            'id' => $id,
            'new_status' => $newStatus,
            'counts' => $counts
        ]);
        exit();
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode([
            'status' => 'error',
            'message' => 'Gagal memperbarui status: ' . $e->getMessage()
        ]);
        exit();
    }
}

// Endpoint: Hapus Masukan (POST ?action=delete atau method DELETE)
if (($method === 'POST' && $action === 'delete') || $method === 'DELETE') {
    $data = get_request_data();
    $id = isset($data['id']) ? (int)$data['id'] : (isset($_GET['id']) ? (int)$_GET['id'] : 0);

    if ($id <= 0) {
        http_response_code(422);
        echo json_encode([
            'status' => 'error',
            'message' => 'ID masukan yang akan dihapus tidak valid.'
        ]);
        exit();
    }

    try {
        $stmt = $pdo->prepare("DELETE FROM `feedbacks` WHERE `id` = :id");
        $stmt->execute([':id' => $id]);

        $counts = get_feedback_counts($pdo);

        echo json_encode([
            'status' => 'success',
            'message' => "Masukan #$id berhasil dihapus dari arsip.",
            'id' => $id,
            'counts' => $counts
        ]);
        exit();
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode([
            'status' => 'error',
            'message' => 'Gagal menghapus masukan: ' . $e->getMessage()
        ]);
        exit();
    }
}

// Endpoint: GET Daftar Masukan (Admin)
if ($method === 'GET') {
    try {
        $statusFilter = isset($_GET['status']) ? trim($_GET['status']) : 'all';
        $jenisFilter  = isset($_GET['jenis']) ? trim($_GET['jenis']) : 'all';
        $search       = isset($_GET['q']) ? trim($_GET['q']) : '';

        $sql = "SELECT * FROM `feedbacks` WHERE 1=1";
        $params = [];

        if ($statusFilter !== 'all' && in_array($statusFilter, ['Belum dibaca', 'Sudah dibaca', 'Ditindaklanjuti'])) {
            $sql .= " AND `status` = :st";
            $params[':st'] = $statusFilter;
        }

        if ($jenisFilter !== 'all') {
            $sql .= " AND `jenis_masukan` = :jn";
            $params[':jn'] = $jenisFilter;
        }

        if ($search !== '') {
            $sql .= " AND (`nama` LIKE :q OR `email` LIKE :q OR `isi` LIKE :q)";
            $params[':q'] = '%' . $search . '%';
        }

        $sql .= " ORDER BY `created_at` DESC";

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $feedbacks = $stmt->fetchAll();

        $counts = get_feedback_counts($pdo);

        echo json_encode([
            'status' => 'success',
            'counts' => $counts,
            'count' => count($feedbacks),
            'data' => $feedbacks
        ]);
        exit();

    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode([
            'status' => 'error',
            'message' => 'Gagal mengambil data kritik & saran: ' . $e->getMessage()
        ]);
        exit();
    }
}

// Fallback untuk method tidak didukung
http_response_code(405);
echo json_encode([
    'status' => 'error',
    'message' => 'Metode HTTP tidak didukung.'
]);
