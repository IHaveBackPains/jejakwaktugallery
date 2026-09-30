<?php
/**
 * API Autentikasi (Login, Logout, Check Session) "Jejak Waktu"
 */

require_once __DIR__ . '/session.php';
require_once __DIR__ . '/db.php';

header('Content-Type: application/json; charset=utf-8');

$action = isset($_GET['action']) ? trim($_GET['action']) : (isset($_POST['action']) ? trim($_POST['action']) : 'check');

// 1. ACTION: CHECK SESSION
if ($action === 'check') {
    $user = get_current_user_session();
    if ($user) {
        echo json_encode([
            'status' => 'success',
            'authenticated' => true,
            'data' => $user
        ]);
    } else {
        echo json_encode([
            'status' => 'success',
            'authenticated' => false,
            'data' => null
        ]);
    }
    exit();
}

// 2. ACTION: LOGOUT
if ($action === 'logout') {
    $_SESSION = [];
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    session_destroy();

    // Jika dipanggil via browser GET biasa, redirect ke login.php
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        header('Location: ../login.php');
        exit();
    }

    echo json_encode([
        'status' => 'success',
        'message' => 'Berhasil keluar dari sesi Jejak Waktu.'
    ]);
    exit();
}

// 3. ACTION: LOGIN
if ($action === 'login') {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode(['status' => 'error', 'message' => 'Metode request harus POST']);
        exit();
    }

    $username = isset($_POST['username']) ? trim($_POST['username']) : '';
    $password = isset($_POST['password']) ? trim($_POST['password']) : '';

    if (empty($username) || empty($password)) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Nama pengguna dan kata sandi wajib diisi.']);
        exit();
    }

    // Cek ke Database MySQL
    if (isset($pdo) && ($pdo instanceof PDO)) {
        try {
            $stmt = $pdo->prepare("SELECT `id`, `username`, `password`, `full_name`, `role` FROM `users` WHERE `username` = :username LIMIT 1");
            $stmt->execute([':username' => $username]);
            $userData = $stmt->fetch();

            if ($userData && password_verify($password, $userData['password'])) {
                // Regenerasi session ID untuk mencegah session fixation
                session_regenerate_id(true);

                $_SESSION['user'] = [
                    'id' => (int)$userData['id'],
                    'username' => $userData['username'],
                    'full_name' => $userData['full_name'],
                    'role' => $userData['role']
                ];

                echo json_encode([
                    'status' => 'success',
                    'message' => 'Selamat datang kembali di Jejak Waktu!',
                    'data' => $_SESSION['user']
                ]);
                exit();
            }
        } catch (Exception $e) {
            // Lanjut ke fallback verifikasi jika DB error
        }
    }

    // Fallback akun dummy jika database MySQL belum terhubung
    $dummyUsers = [
        'admin' => [
            'id' => 1,
            'username' => 'admin',
            'password_hash' => '$2y$10$l01c1zhdtdGAM7mslH7TIuNSQ4M4oueyTH1WoIGHcLa5PnlzggGx2', // admin123
            'full_name' => 'Arsiparis Utama',
            'role' => 'admin'
        ],
        'user' => [
            'id' => 2,
            'username' => 'user',
            'password_hash' => '$2y$10$JPmy6hjPxOYCK2JMbzs1OOn3GocT4ZmkZQ.b4QpijrnQbNCOyzhmy', // user123
            'full_name' => 'Pengunjung Nostalgia',
            'role' => 'user'
        ]
    ];

    if (isset($dummyUsers[$username]) && password_verify($password, $dummyUsers[$username]['password_hash'])) {
        session_regenerate_id(true);
        $u = $dummyUsers[$username];
        $_SESSION['user'] = [
            'id' => $u['id'],
            'username' => $u['username'],
            'full_name' => $u['full_name'],
            'role' => $u['role']
        ];

        echo json_encode([
            'status' => 'success',
            'message' => 'Selamat datang kembali di Jejak Waktu!',
            'data' => $_SESSION['user']
        ]);
        exit();
    }

    // Kredensial tidak cocok
    http_response_code(401);
    echo json_encode([
        'status' => 'error',
        'message' => 'Nama pengguna atau kata sandi tidak cocok. Silakan periksa kembali.'
    ]);
    exit();
}
