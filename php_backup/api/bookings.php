<?php
/**
 * API Bookings - Booking Tiket Event
 * GET  /api/bookings.php?event_id=N  → Cek status booking user ini untuk event tsb
 * GET  /api/bookings.php?my=1        → Semua booking milik user yang login
 * GET  /api/bookings.php?all=1       → Semua booking (Admin only)
 * POST /api/bookings.php             → Buat booking baru
 * POST /api/bookings.php?_method=DELETE&id=N → Batalkan booking (user sendiri atau Admin)
 */

require_once __DIR__ . '/session.php';
require_once __DIR__ . '/db.php';

require_auth(true);

if (!isset($pdo) || !($pdo instanceof PDO)) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Koneksi database tidak tersedia']);
    exit();
}

$method   = $_SERVER['REQUEST_METHOD'];
$override = isset($_GET['_method']) ? strtoupper($_GET['_method']) : '';
if ($override === 'DELETE') $method = 'DELETE';

$currentUser = get_current_user_session();
$userId      = (int)$currentUser['id'];
$isAdmin     = $currentUser['role'] === 'admin';

// ---- GET ----
if ($method === 'GET') {
    try {
        // Admin: ambil semua booking
        if ($isAdmin && isset($_GET['all'])) {
            $stmt = $pdo->query("
                SELECT b.*, u.username, u.full_name, e.title AS event_title, e.start_date
                FROM `bookings` b
                JOIN `users` u ON b.user_id = u.id
                JOIN `events` e ON b.event_id = e.id
                ORDER BY b.created_at DESC
            ");
            $bookings = $stmt->fetchAll();
            echo json_encode(['status' => 'success', 'data' => $bookings]);
            exit();
        }

        // My bookings
        if (isset($_GET['my'])) {
            $stmt = $pdo->prepare("
                SELECT b.*, e.title AS event_title, e.start_date, e.status AS event_status, e.poster_image, e.location_text, e.ticket_price
                FROM `bookings` b
                JOIN `events` e ON b.event_id = e.id
                WHERE b.user_id = :uid
                ORDER BY b.created_at DESC
            ");
            $stmt->execute([':uid' => $userId]);
            $bookings = $stmt->fetchAll();
            echo json_encode(['status' => 'success', 'data' => $bookings]);
            exit();
        }

        // Cek booking tertentu untuk event
        if (isset($_GET['event_id'])) {
            $eid = (int)$_GET['event_id'];
            $stmt = $pdo->prepare("SELECT * FROM `bookings` WHERE `event_id` = :eid AND `user_id` = :uid AND `status` != 'cancelled'");
            $stmt->execute([':eid' => $eid, ':uid' => $userId]);
            $booking = $stmt->fetch();
            echo json_encode(['status' => 'success', 'data' => $booking ?: null]);
            exit();
        }

        echo json_encode(['status' => 'error', 'message' => 'Parameter tidak valid']);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
    exit();
}

// ---- POST: Buat Booking ----
if ($method === 'POST') {
    $event_id = isset($_POST['event_id']) ? (int)$_POST['event_id'] : 0;
    $qty      = isset($_POST['qty']) && (int)$_POST['qty'] > 0 ? (int)$_POST['qty'] : 1;
    $notes    = isset($_POST['notes']) ? trim($_POST['notes']) : '';

    if ($event_id <= 0) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'ID event tidak valid']);
        exit();
    }

    try {
        // Cek event exist
        $stmtE = $pdo->prepare("SELECT * FROM `events` WHERE `id` = :id");
        $stmtE->execute([':id' => $event_id]);
        $event = $stmtE->fetch();
        if (!$event) {
            http_response_code(404);
            echo json_encode(['status' => 'error', 'message' => 'Event tidak ditemukan']);
            exit();
        }

        // Cek event sudah selesai
        if ($event['status'] === 'completed') {
            echo json_encode(['status' => 'error', 'message' => 'Event ini telah selesai, pendaftaran ditutup']);
            exit();
        }

        // Cek duplikat booking
        $stmtDup = $pdo->prepare("SELECT id FROM `bookings` WHERE `event_id` = :eid AND `user_id` = :uid AND `status` != 'cancelled'");
        $stmtDup->execute([':eid' => $event_id, ':uid' => $userId]);
        if ($stmtDup->fetch()) {
            echo json_encode(['status' => 'error', 'message' => 'Anda sudah terdaftar untuk event ini']);
            exit();
        }

        // Cek kuota
        if ($event['ticket_quota'] !== null) {
            $stmtBooked = $pdo->prepare("SELECT COALESCE(SUM(qty),0) FROM `bookings` WHERE `event_id` = :eid AND `status` != 'cancelled'");
            $stmtBooked->execute([':eid' => $event_id]);
            $totalBooked = (int)$stmtBooked->fetchColumn();
            if ($totalBooked + $qty > (int)$event['ticket_quota']) {
                echo json_encode(['status' => 'error', 'message' => 'Kuota tiket penuh atau tidak mencukupi']);
                exit();
            }
        }

        // Hitung harga
        $priceStr = $event['ticket_price'];
        $isGratis = stripos($priceStr, 'gratis') !== false || trim($priceStr) === '0' || trim($priceStr) === '';
        $totalPrice = $isGratis ? 'Gratis' : $priceStr . ($qty > 1 ? " × {$qty} tiket" : '');

        // Generate kode booking
        $bookingCode = 'JW-' . strtoupper(substr(md5($event_id . $userId . time()), 0, 8));

        $stmt = $pdo->prepare("
            INSERT INTO `bookings` (`event_id`,`user_id`,`qty`,`total_price`,`status`,`booking_code`,`notes`)
            VALUES (:eid,:uid,:qty,:total_price,'confirmed',:code,:notes)
        ");
        $stmt->execute([
            ':eid'         => $event_id,
            ':uid'         => $userId,
            ':qty'         => $qty,
            ':total_price' => $totalPrice,
            ':code'        => $bookingCode,
            ':notes'       => $notes ?: null,
        ]);

        echo json_encode([
            'status'       => 'success',
            'message'      => "Tiket berhasil dipesan! Kode booking: $bookingCode",
            'data'         => [
                'id'           => (int)$pdo->lastInsertId(),
                'booking_code' => $bookingCode,
                'total_price'  => $totalPrice,
                'qty'          => $qty,
                'event_title'  => $event['title'],
            ]
        ]);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
    }
    exit();
}

// ---- DELETE: Batalkan Booking ----
if ($method === 'DELETE') {
    $booking_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
    if ($booking_id <= 0) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'ID booking tidak valid']);
        exit();
    }
    try {
        // Hanya pemilik booking atau admin yang boleh batalkan
        $stmtB = $pdo->prepare("SELECT * FROM `bookings` WHERE `id` = :id");
        $stmtB->execute([':id' => $booking_id]);
        $booking = $stmtB->fetch();
        if (!$booking) {
            http_response_code(404);
            echo json_encode(['status' => 'error', 'message' => 'Booking tidak ditemukan']);
            exit();
        }
        if (!$isAdmin && (int)$booking['user_id'] !== $userId) {
            http_response_code(403);
            echo json_encode(['status' => 'error', 'message' => 'Anda tidak berhak membatalkan booking ini']);
            exit();
        }

        $stmt = $pdo->prepare("UPDATE `bookings` SET `status` = 'cancelled' WHERE `id` = :id");
        $stmt->execute([':id' => $booking_id]);
        echo json_encode(['status' => 'success', 'message' => 'Booking berhasil dibatalkan']);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
    }
    exit();
}

http_response_code(405);
echo json_encode(['status' => 'error', 'message' => 'Metode tidak diizinkan']);
