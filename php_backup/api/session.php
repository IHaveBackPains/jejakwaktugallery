<?php
/**
 * Modul Manajemen Sesi & Otorisasi RBAC "Jejak Waktu"
 */

if (session_status() === PHP_SESSION_NONE) {
    // Pengaturan cookie sesi aman
    ini_set('session.cookie_httponly', 1);
    ini_set('session.use_only_cookies', 1);
    ini_set('session.cookie_samesite', 'Lax');
    session_start();
}

/**
 * Mengambil data pengguna dari sesi aktif
 * @return array|null
 */
function get_current_user_session() {
    if (isset($_SESSION['user']) && is_array($_SESSION['user'])) {
        return $_SESSION['user'];
    }
    return null;
}

/**
 * Memastikan pengguna sudah terautentikasi (login)
 * Jika belum, alihkan ke login.php atau kirim 401 JSON
 */
function require_auth($isApi = false) {
    $user = get_current_user_session();
    if (!$user) {
        if ($isApi || (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false)) {
            http_response_code(401);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode([
                'status' => 'error',
                'message' => 'Sesi belum terautentikasi. Silakan login terlebih dahulu.',
                'code' => 'UNAUTHENTICATED'
            ]);
            exit();
        } else {
            header('Location: login.php');
            exit();
        }
    }
    return $user;
}

/**
 * Memastikan pengguna memiliki role Admin
 * Jika bukan admin, tolak dengan 403 Forbidden
 */
function require_admin() {
    $user = require_auth(true);
    if ($user['role'] !== 'admin') {
        http_response_code(403);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'status' => 'error',
            'message' => 'Akses ditolak: Hanya akun dengan role Admin yang diizinkan memodifikasi arsip.',
            'code' => 'FORBIDDEN'
        ]);
        exit();
    }
    return $user;
}
