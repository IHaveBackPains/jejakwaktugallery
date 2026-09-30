<?php
/**
 * API Events - CRUD Lengkap
 * GET    /api/events.php          → Daftar semua event
 * GET    /api/events.php?id=N     → Detail satu event
 * POST   /api/events.php          → Buat event baru (Admin)
 * POST   /api/events.php?_method=PUT&id=N  → Edit event (Admin)
 * POST   /api/events.php?_method=DELETE&id=N → Hapus event (Admin)
 */

require_once __DIR__ . '/session.php';
require_once __DIR__ . '/db.php';

// Semua operasi butuh autentikasi login
require_auth(true);

if (!isset($pdo) || !($pdo instanceof PDO)) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Koneksi database tidak tersedia']);
    exit();
}

// Auto-migrate: Buat tabel events & bookings jika belum ada
try {
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `events` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `title` VARCHAR(255) NOT NULL,
            `theme` VARCHAR(255) DEFAULT NULL,
            `status` ENUM('upcoming','ongoing','completed') DEFAULT 'upcoming',
            `start_date` DATE NOT NULL,
            `end_date` DATE DEFAULT NULL,
            `start_time` TIME DEFAULT NULL,
            `end_time` TIME DEFAULT NULL,
            `ticket_price` VARCHAR(100) DEFAULT 'Gratis',
            `ticket_quota` INT DEFAULT NULL,
            `location_text` TEXT DEFAULT NULL,
            `location_map` TEXT DEFAULT NULL,
            `artists` TEXT DEFAULT NULL,
            `poster_image` TEXT DEFAULT NULL,
            `description` TEXT DEFAULT NULL,
            `organizer` VARCHAR(150) DEFAULT 'Jejak Waktu',
            `album_id` VARCHAR(100) DEFAULT NULL,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ");

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `bookings` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `event_id` INT NOT NULL,
            `user_id` INT NOT NULL,
            `qty` INT DEFAULT 1,
            `total_price` VARCHAR(100) DEFAULT NULL,
            `status` ENUM('pending','confirmed','cancelled') DEFAULT 'confirmed',
            `booking_code` VARCHAR(50) DEFAULT NULL,
            `notes` TEXT DEFAULT NULL,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (`event_id`) REFERENCES `events`(`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ");
} catch (PDOException $e) {
    // Tabel sudah ada, lanjutkan
}

$method = $_SERVER['REQUEST_METHOD'];
$override = isset($_GET['_method']) ? strtoupper($_GET['_method']) : '';
if ($override === 'PUT') $method = 'PUT';
if ($override === 'DELETE') $method = 'DELETE';

$event_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// ---- Fungsi Format Event ----
function formatEvent(array $e): array {
    return [
        'id'            => (int)$e['id'],
        'title'         => $e['title'],
        'theme'         => $e['theme'],
        'status'        => $e['status'],
        'start_date'    => $e['start_date'],
        'end_date'      => $e['end_date'],
        'start_time'    => $e['start_time'],
        'end_time'      => $e['end_time'],
        'ticket_price'  => $e['ticket_price'],
        'ticket_quota'  => $e['ticket_quota'] !== null ? (int)$e['ticket_quota'] : null,
        'location_text' => $e['location_text'],
        'location_map'  => $e['location_map'],
        'artists'       => $e['artists'],
        'poster_image'  => $e['poster_image'],
        'description'   => $e['description'],
        'organizer'     => $e['organizer'],
        'album_id'      => $e['album_id'],
        'created_at'    => $e['created_at'],
    ];
}

// ---- GET: Ambil event ----
if ($method === 'GET') {
    try {
        if ($event_id > 0) {
            $stmt = $pdo->prepare("SELECT * FROM `events` WHERE `id` = :id");
            $stmt->execute([':id' => $event_id]);
            $event = $stmt->fetch();
            if (!$event) {
                http_response_code(404);
                echo json_encode(['status' => 'error', 'message' => 'Event tidak ditemukan']);
                exit();
            }
            // Hitung total booking
            $stmtB = $pdo->prepare("SELECT COALESCE(SUM(qty),0) FROM `bookings` WHERE `event_id` = :eid AND `status` != 'cancelled'");
            $stmtB->execute([':eid' => $event_id]);
            $booked = (int)$stmtB->fetchColumn();
            $data = formatEvent($event);
            $data['booked_count'] = $booked;
            echo json_encode(['status' => 'success', 'data' => $data]);
        } else {
            // Filter opsional berdasarkan status
            $statusFilter = isset($_GET['status']) ? trim($_GET['status']) : '';
            $validStatuses = ['upcoming', 'ongoing', 'completed'];
            if ($statusFilter && in_array($statusFilter, $validStatuses)) {
                $stmt = $pdo->prepare("SELECT * FROM `events` WHERE `status` = :s ORDER BY `start_date` ASC");
                $stmt->execute([':s' => $statusFilter]);
            } else {
                $stmt = $pdo->query("SELECT * FROM `events` ORDER BY `start_date` ASC");
            }
            $events = $stmt->fetchAll();
            $result = [];
            foreach ($events as $ev) {
                $stmtB = $pdo->prepare("SELECT COALESCE(SUM(qty),0) FROM `bookings` WHERE `event_id` = :eid AND `status` != 'cancelled'");
                $stmtB->execute([':eid' => $ev['id']]);
                $booked = (int)$stmtB->fetchColumn();
                $item = formatEvent($ev);
                $item['booked_count'] = $booked;
                $result[] = $item;
            }
            echo json_encode(['status' => 'success', 'data' => $result]);
        }
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
    exit();
}

// ---- POST: Buat event baru ----
if ($method === 'POST') {
    require_admin();

    $title         = isset($_POST['title'])         ? trim($_POST['title'])         : '';
    $theme         = isset($_POST['theme'])         ? trim($_POST['theme'])         : null;
    $status        = isset($_POST['status'])        ? trim($_POST['status'])        : 'upcoming';
    $start_date    = isset($_POST['start_date'])    ? trim($_POST['start_date'])    : '';
    $end_date      = isset($_POST['end_date'])      ? trim($_POST['end_date'])      : null;
    $start_time    = isset($_POST['start_time'])    ? trim($_POST['start_time'])    : null;
    $end_time      = isset($_POST['end_time'])      ? trim($_POST['end_time'])      : null;
    $ticket_price  = isset($_POST['ticket_price'])  ? trim($_POST['ticket_price'])  : 'Gratis';
    $ticket_quota  = isset($_POST['ticket_quota']) && trim($_POST['ticket_quota']) !== '' ? (int)$_POST['ticket_quota'] : null;
    $location_text = isset($_POST['location_text']) ? trim($_POST['location_text']) : null;
    $location_map  = isset($_POST['location_map'])  ? trim($_POST['location_map'])  : null;
    $artists       = isset($_POST['artists'])       ? trim($_POST['artists'])       : null;
    $description   = isset($_POST['description'])   ? trim($_POST['description'])   : null;
    $organizer     = isset($_POST['organizer'])     ? trim($_POST['organizer'])     : 'Jejak Waktu';
    $album_id      = isset($_POST['album_id'])      ? trim($_POST['album_id'])      : null;
    $poster_image  = '';

    if (empty($title) || empty($start_date)) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Judul event dan tanggal mulai wajib diisi']);
        exit();
    }

    $valid_statuses = ['upcoming', 'ongoing', 'completed'];
    if (!in_array($status, $valid_statuses)) $status = 'upcoming';

    // Handle poster upload
    if (isset($_FILES['poster_file']) && $_FILES['poster_file']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['poster_file'];
        $allowed = ['jpg','jpeg','png','webp','gif','jfif','avif','bmp'];
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, $allowed)) $ext = 'jpg';
        $upload_dir = __DIR__ . '/../uploads/';
        if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);
        $filename = 'event_poster_' . time() . '_' . rand(1000, 9999) . '.' . $ext;
        if (move_uploaded_file($file['tmp_name'], $upload_dir . $filename)) {
            $poster_image = 'uploads/' . $filename;
        }
    }
    if (empty($poster_image) && isset($_POST['poster_url']) && trim($_POST['poster_url']) !== '') {
        $poster_image = trim($_POST['poster_url']);
    }

    // Auto-sync status berdasarkan tanggal
    if (!empty($start_date)) {
        $today = date('Y-m-d');
        $endCheck = $end_date ?: $start_date;
        if ($today < $start_date) {
            $status = 'upcoming';
        } elseif ($today >= $start_date && $today <= $endCheck) {
            $status = 'ongoing';
        } else {
            $status = 'completed';
        }
        // Override jika admin set manual dan masuk akal
        $manualStatus = isset($_POST['status']) ? trim($_POST['status']) : '';
        if (in_array($manualStatus, ['upcoming','ongoing','completed'])) {
            $status = $manualStatus;
        }
    }

    try {
        $sql = "INSERT INTO `events` (`title`,`theme`,`status`,`start_date`,`end_date`,`start_time`,`end_time`,`ticket_price`,`ticket_quota`,`location_text`,`location_map`,`artists`,`poster_image`,`description`,`organizer`,`album_id`)
                VALUES (:title,:theme,:status,:start_date,:end_date,:start_time,:end_time,:ticket_price,:ticket_quota,:location_text,:location_map,:artists,:poster_image,:description,:organizer,:album_id)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':title'         => $title,
            ':theme'         => $theme,
            ':status'        => $status,
            ':start_date'    => $start_date,
            ':end_date'      => $end_date ?: null,
            ':start_time'    => $start_time ?: null,
            ':end_time'      => $end_time ?: null,
            ':ticket_price'  => $ticket_price ?: 'Gratis',
            ':ticket_quota'  => $ticket_quota,
            ':location_text' => $location_text,
            ':location_map'  => $location_map,
            ':artists'       => $artists,
            ':poster_image'  => $poster_image,
            ':description'   => $description,
            ':organizer'     => $organizer,
            ':album_id'      => $album_id ?: null,
        ]);
        $newId = $pdo->lastInsertId();
        echo json_encode([
            'status'  => 'success',
            'message' => "Event \"$title\" berhasil dibuat!",
            'data'    => ['id' => (int)$newId, 'title' => $title, 'status' => $status, 'poster_image' => $poster_image]
        ]);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
    }
    exit();
}

// ---- PUT: Edit event ----
if ($method === 'PUT') {
    require_admin();
    if ($event_id <= 0) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'ID event wajib disertakan']);
        exit();
    }

    $post = $_POST;

    $fields = [];
    $params = [':id' => $event_id];

    $allowed_fields = [
        'title','theme','status','start_date','end_date','start_time','end_time',
        'ticket_price','ticket_quota','location_text','location_map','artists',
        'description','organizer','album_id'
    ];
    foreach ($allowed_fields as $f) {
        if (isset($post[$f])) {
            $fields[] = "`$f` = :$f";
            $params[":$f"] = $post[$f] !== '' ? trim($post[$f]) : null;
        }
    }

    // Handle poster upload on edit
    if (isset($_FILES['poster_file']) && $_FILES['poster_file']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['poster_file'];
        $allowed = ['jpg','jpeg','png','webp','gif','jfif','avif','bmp'];
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, $allowed)) $ext = 'jpg';
        $upload_dir = __DIR__ . '/../uploads/';
        if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);
        $filename = 'event_poster_' . time() . '_' . rand(1000, 9999) . '.' . $ext;
        if (move_uploaded_file($file['tmp_name'], $upload_dir . $filename)) {
            $fields[] = "`poster_image` = :poster_image";
            $params[':poster_image'] = 'uploads/' . $filename;
        }
    } elseif (isset($post['poster_url']) && trim($post['poster_url']) !== '') {
        $fields[] = "`poster_image` = :poster_image";
        $params[':poster_image'] = trim($post['poster_url']);
    }

    if (empty($fields)) {
        echo json_encode(['status' => 'success', 'message' => 'Tidak ada perubahan yang dikirim']);
        exit();
    }

    try {
        $sql = "UPDATE `events` SET " . implode(', ', $fields) . " WHERE `id` = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        echo json_encode(['status' => 'success', 'message' => 'Event berhasil diperbarui']);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
    }
    exit();
}

// ---- DELETE: Hapus event ----
if ($method === 'DELETE') {
    require_admin();
    if ($event_id <= 0) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'ID event wajib disertakan']);
        exit();
    }
    try {
        $stmt = $pdo->prepare("DELETE FROM `events` WHERE `id` = :id");
        $stmt->execute([':id' => $event_id]);
        echo json_encode(['status' => 'success', 'message' => 'Event berhasil dihapus']);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
    }
    exit();
}

http_response_code(405);
echo json_encode(['status' => 'error', 'message' => 'Metode tidak diizinkan']);
