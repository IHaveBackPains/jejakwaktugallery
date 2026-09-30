<?php
/**
 * Halaman Jadwal Event Seni — Jejak Waktu
 * Mengarahkan navigasi langsung ke Shared Layout Retro Navbar di index.php#events
 */

require_once __DIR__ . '/api/session.php';
require_auth();

// Redirect ke SPA layout utama dengan tab events aktif secara otomatis
header('Location: index.php#events');
exit;
