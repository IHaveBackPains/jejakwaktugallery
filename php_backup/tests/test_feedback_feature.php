<?php
/**
 * Test Suite: Fitur Kritik & Saran (Public Submit, Validation, Admin Auth, Status Update, Delete)
 */

$baseUrl = 'http://127.0.0.1/jejak-waktu';
$cookieFile = __DIR__ . '/fb_cookie.txt';
if (file_exists($cookieFile)) @unlink($cookieFile);

echo "=== MEMULAI TEST FITUR KRITIK & SARAN ===\n\n";

$allPassed = true;
function assertFB($desc, $cond) {
    global $allPassed;
    if ($cond) {
        echo "  [PASS] $desc\n";
    } else {
        echo "  [FAIL] $desc\n";
        $allPassed = false;
    }
}

// 1. Test POST Submit Form Publik (Valid Data)
$ch = curl_init("$baseUrl/api/feedbacks.php");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
    'nama' => 'Ahmad Pengunjung',
    'email' => 'ahmad@example.com',
    'jenis_masukan' => 'Koreksi Data Galeri',
    'isi' => 'Data jam buka galeri Visma mohon disesuaikan dengan info terbaru.'
]));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
$res = curl_exec($ch);
$http = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

$postData = json_decode($res, true);
assertFB("Public submit berhasil (HTTP 201)", $http === 201 && isset($postData['status']) && $postData['status'] === 'success');
assertFB("Response mengembalikan ID masukan baru", isset($postData['data']['id']) && $postData['data']['id'] > 0);
$testId = $postData['data']['id'] ?? 0;

// 2. Test Validasi: Isi Kosong (Harus Gagal 422)
$ch = curl_init("$baseUrl/api/feedbacks.php");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
    'nama' => 'Tanpa Pesan',
    'email' => 'test@example.com',
    'jenis_masukan' => 'Kritik',
    'isi' => ''
]));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
$resVal1 = curl_exec($ch);
$httpVal1 = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);
assertFB("Validasi isi kosong ditolak (HTTP 422)", $httpVal1 === 422);

// 3. Test Validasi: Format Email Salah (Harus Gagal 422)
$ch = curl_init("$baseUrl/api/feedbacks.php");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
    'nama' => 'Email Ngawur',
    'email' => 'bukan-email-valid',
    'jenis_masukan' => 'Saran',
    'isi' => 'Tampilan peta interaktifnya sangat bagus.'
]));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
$resVal2 = curl_exec($ch);
$httpVal2 = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);
assertFB("Validasi email tidak valid ditolak (HTTP 422)", $httpVal2 === 422);

// 4. Test Honeypot Anti-Spam
$ch = curl_init("$baseUrl/api/feedbacks.php");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
    'nama' => 'Spam Bot',
    'email' => 'bot@spammer.com',
    'isi' => 'Buy cheap viagra now',
    'website_hp' => 'http://spam-link.com'
]));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
$resBot = curl_exec($ch);
curl_close($ch);
assertFB("Honeypot anti-spam menangkap bot", strpos($resBot, 'success') !== false);

// 5. Test Non-Admin Akses GET (Harus Ditolak 401 / 403)
$ch = curl_init("$baseUrl/api/feedbacks.php");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$resNoAuth = curl_exec($ch);
$httpNoAuth = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);
assertFB("Akses admin GET tanpa login ditolak (HTTP 401)", $httpNoAuth === 401);

// 6. Login Admin
$ch = curl_init("$baseUrl/api/auth.php?action=login");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
    'username' => 'admin',
    'password' => 'admin123'
]));
curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieFile);
curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieFile);
$resLogin = curl_exec($ch);
curl_close($ch);

// 7. Test Admin GET Daftar Masukan
$ch = curl_init("$baseUrl/api/feedbacks.php");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieFile);
$resAdminGet = curl_exec($ch);
$httpAdminGet = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

$adminData = json_decode($resAdminGet, true);
assertFB("Admin berhasil mengambil daftar masukan (HTTP 200)", $httpAdminGet === 200 && isset($adminData['data']));
assertFB("Status default masukan baru adalah 'Belum dibaca'", isset($adminData['data'][0]['status']) && $adminData['data'][0]['status'] === 'Belum dibaca');
assertFB("Hitungan unread count tersedia", isset($adminData['counts']['unread']) && $adminData['counts']['unread'] >= 1);

// 8. Test Admin Update Status -> 'Sudah dibaca'
$ch = curl_init("$baseUrl/api/feedbacks.php?action=update_status");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
    'id' => $testId,
    'status' => 'Sudah dibaca'
]));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieFile);
$resUpdate1 = curl_exec($ch);
$httpUpdate1 = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);
$upData1 = json_decode($resUpdate1, true);
assertFB("Admin berhasil ubah status ke 'Sudah dibaca'", $httpUpdate1 === 200 && ($upData1['new_status'] ?? '') === 'Sudah dibaca');

// 9. Test Admin Update Status -> 'Ditindaklanjuti'
$ch = curl_init("$baseUrl/api/feedbacks.php?action=update_status");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
    'id' => $testId,
    'status' => 'Ditindaklanjuti'
]));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieFile);
$resUpdate2 = curl_exec($ch);
$upData2 = json_decode($resUpdate2, true);
assertFB("Admin berhasil ubah status ke 'Ditindaklanjuti'", ($upData2['new_status'] ?? '') === 'Ditindaklanjuti');

// 10. Test Admin Hapus Masukan
$ch = curl_init("$baseUrl/api/feedbacks.php?action=delete");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(['id' => $testId]));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieFile);
$resDel = curl_exec($ch);
$httpDel = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);
assertFB("Admin berhasil menghapus masukan", $httpDel === 200);

if (file_exists($cookieFile)) @unlink($cookieFile);

echo "\n============================================\n";
if ($allPassed) {
    echo "HASIL: SEMUA PENGUJIAN API FEEDBACK LULUS 100%!\n";
} else {
    echo "HASIL: ADA PENGUJIAN YANG GAGAL!\n";
}
echo "============================================\n";
