<?php
/**
 * Test Suite: Fitur Hapus Seniman (Delete Artist) - Admin Only
 * Menguji:
 * 1. Otorisasi Backend:
 *    - Unauthenticated (Guest) -> 401 Unauthorized
 *    - User biasa (Role 'user') -> 403 Forbidden
 *    - Admin -> 200 Success
 * 2. Validasi input:
 *    - Request tanpa ID seniman -> 400 Bad Request
 * 3. Hapus Seniman & Foto Profil dari Server:
 *    - Pembuatan seniman uji dengan foto profil lokal di uploads/
 *    - Eksekusi DELETE / POST ?action=delete oleh Admin
 *    - Foto profil fisik terhapus dari disk storage
 *    - Data seniman terhapus dari database / tidak muncul di GET api/artists.php
 *    - Seniman lain tetap utuh
 * 4. Mendukung method DELETE dan POST ?action=delete
 */

$baseUrl = 'http://127.0.0.1/jejak-waktu';
$cookieFileAdmin = __DIR__ . '/test_admin_cookie.txt';
$cookieFileUser = __DIR__ . '/test_user_cookie.txt';

if (file_exists($cookieFileAdmin)) @unlink($cookieFileAdmin);
if (file_exists($cookieFileUser)) @unlink($cookieFileUser);

require_once __DIR__ . '/../api/db.php';

echo "=== MEMULAI TEST END-TO-END FITUR HAPUS SENIMAN ===\n\n";

$testsPassed = 0;
$testsTotal = 0;

function assertTest($desc, $cond) {
    global $testsPassed, $testsTotal;
    $testsTotal++;
    if ($cond) {
        echo "  [PASS] $desc\n";
        $testsPassed++;
    } else {
        echo "  [FAIL] $desc\n";
    }
}

// ----------------------------------------------------
// 0. Login Admin & Login User
// ----------------------------------------------------
$ch = curl_init("$baseUrl/api/auth.php?action=login");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
    'username' => 'admin',
    'password' => 'admin123'
]));
curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieFileAdmin);
curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieFileAdmin);
$res = curl_exec($ch);
curl_close($ch);
$adminAuth = json_decode($res, true);
assertTest("0. Login Admin berhasil", isset($adminAuth['status']) && $adminAuth['status'] === 'success');

$ch = curl_init("$baseUrl/api/auth.php?action=login");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
    'username' => 'user',
    'password' => 'user123'
]));
curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieFileUser);
curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieFileUser);
$res = curl_exec($ch);
curl_close($ch);
$userAuth = json_decode($res, true);
assertTest("0. Login User biasa berhasil", isset($userAuth['status']) && $userAuth['status'] === 'success');

// ----------------------------------------------------
// 1. Uji Keamanan Backend (RBAC)
// ----------------------------------------------------
// A. Guest (tanpa cookie) -> 401
$ch = curl_init("$baseUrl/api/artists.php?action=delete");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query(['artist_id' => 'raden-saleh']));
$res = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);
assertTest("1. Guest ditolak dengan HTTP 401", $httpCode === 401);

// B. User biasa (bukan admin) -> 403 Forbidden
$ch = curl_init("$baseUrl/api/artists.php?action=delete");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieFileUser);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query(['artist_id' => 'raden-saleh']));
$res = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);
assertTest("1. User non-admin ditolak dengan HTTP 403 Forbidden", $httpCode === 403);

// C. Admin tanpa artist_id -> 400 Bad Request
$ch = curl_init("$baseUrl/api/artists.php?action=delete");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieFileAdmin);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query(['artist_id' => '']));
$res = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);
assertTest("1. Permintaan hapus tanpa ID ditolak dengan HTTP 400", $httpCode === 400);

// ----------------------------------------------------
// 2. Buat Seniman Uji Lengkap dengan File Foto di uploads/
// ----------------------------------------------------
$testId = 'seniman-uji-' . time();
$uploadDir = __DIR__ . '/../uploads/';
if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
$testAvatarFilename = 'artist_avatar_test_' . time() . '.png';
$testAvatarPath = $uploadDir . $testAvatarFilename;
$png1px = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z8BQDwAEhQGAhKmMIQAAAABJRU5ErkJggg==');
file_put_contents($testAvatarPath, $png1px);

$avatarRelPath = 'uploads/' . $testAvatarFilename;

// Simpan seniman uji ke database
$stmt = $pdo->prepare("INSERT INTO `artist_profiles` (`id`, `name`, `role`, `era`, `category`, `avatar`, `bio`, `location`, `is_deleted`) 
                       VALUES (:id, 'Seniman Uji Hapus', 'Pelukis Eksperimental', '2020-an', 'pelukis', :avatar, 'Bio Seniman Uji', 'Yogyakarta', 0)");
$stmt->execute([
    ':id' => $testId,
    ':avatar' => $avatarRelPath
]);

// Buat juga foto terkait di tabel photos untuk menguji pembersihan artist_1
$validAlbumId = $pdo->query("SELECT `id` FROM `albums` LIMIT 1")->fetchColumn();
if (!$validAlbumId) {
    $pdo->exec("INSERT INTO `albums` (`title`, `description`, `era`, `cover_src`) VALUES ('Album Uji', 'Deskripsi Uji', '1950', 'uploads/cover.jpg')");
    $validAlbumId = $pdo->lastInsertId();
}

$stmtPhoto = $pdo->prepare("INSERT INTO `photos` (`album_id`, `title`, `artist_1`, `year`, `image_src`) VALUES (:album_id, 'Karya Seniman Uji', 'Seniman Uji Hapus', '2023', 'uploads/test_photo.jpg')");
$stmtPhoto->execute([':album_id' => $validAlbumId]);
$photoId = $pdo->lastInsertId();

assertTest("2. File foto fisik seniman uji berhasil dibuat di uploads/", file_exists($testAvatarPath));

// Verifikasi seniman uji muncul di GET
$ch = curl_init("$baseUrl/api/artists.php");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieFileAdmin);
$res = curl_exec($ch);
curl_close($ch);
$list = json_decode($res, true)['data'] ?? [];
$foundTestArtist = false;
foreach ($list as $art) {
    if ($art['id'] === $testId) {
        $foundTestArtist = true;
        break;
    }
}
assertTest("2. Seniman uji terdaftar di GET api/artists.php", $foundTestArtist);

// ----------------------------------------------------
// 3. Eksekusi Hapus Seniman oleh Admin (POST ?action=delete)
// ----------------------------------------------------
$ch = curl_init("$baseUrl/api/artists.php?action=delete");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieFileAdmin);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
    'artist_id' => $testId,
    'current_avatar' => $avatarRelPath
]));
$res = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

$delData = json_decode($res, true);
assertTest("3. Eksekusi hapus berhasil (HTTP 200)", $httpCode === 200 && isset($delData['status']) && $delData['status'] === 'success');

// ----------------------------------------------------
// 4. Verifikasi File Foto Profil Terhapus dari Server/Storage
// ----------------------------------------------------
assertTest("4. File foto profil seniman terhapus dari server (tidak jadi sampah)", !file_exists($testAvatarPath));

// ----------------------------------------------------
// 5. Verifikasi Seniman Uji Hilang dari GET api/artists.php
// ----------------------------------------------------
$ch = curl_init("$baseUrl/api/artists.php");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieFileAdmin);
$res = curl_exec($ch);
curl_close($ch);
$listAfter = json_decode($res, true)['data'] ?? [];
$foundAfter = false;
$foundRadenSaleh = false;
foreach ($listAfter as $art) {
    if ($art['id'] === $testId) $foundAfter = true;
    if ($art['id'] === 'raden-saleh') $foundRadenSaleh = true;
}
assertTest("5. Seniman uji TIDAK LAGI muncul di daftar artist", !$foundAfter);
assertTest("5. Seniman lain (Raden Saleh) tetap aman dan utuh", $foundRadenSaleh);

// ----------------------------------------------------
// 6. Verifikasi Referensi di tabel photos Terhapus/Bersih
// ----------------------------------------------------
$stmtCheckPhoto = $pdo->prepare("SELECT `artist_1` FROM `photos` WHERE `id` = :id");
$stmtCheckPhoto->execute([':id' => $photoId]);
$photoArtist1 = $stmtCheckPhoto->fetchColumn();
assertTest("6. Referensi artist_1 di tabel photos telah dibersihkan (NULL/kosong)", empty($photoArtist1));

// Bersihkan baris foto uji
$pdo->prepare("DELETE FROM `photos` WHERE `id` = :id")->execute([':id' => $photoId]);

// ----------------------------------------------------
// 7. Uji Method HTTP DELETE Murni
// ----------------------------------------------------
$testId2 = 'seniman-delete-http-' . time();
$testAvatarFilename2 = 'artist_avatar_test2_' . time() . '.png';
$testAvatarPath2 = $uploadDir . $testAvatarFilename2;
file_put_contents($testAvatarPath2, $png1px);
$avatarRelPath2 = 'uploads/' . $testAvatarFilename2;

$pdo->prepare("INSERT INTO `artist_profiles` (`id`, `name`, `role`, `era`, `category`, `avatar`, `bio`, `location`, `is_deleted`) 
               VALUES (:id, 'Seniman HTTP Delete', 'Fotografer', '2020-an', 'fotografer', :avatar, 'Bio', 'Jakarta', 0)")
    ->execute([':id' => $testId2, ':avatar' => $avatarRelPath2]);

$ch = curl_init("$baseUrl/api/artists.php");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieFileAdmin);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(['artist_id' => $testId2]));
$res = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

$delHttpData = json_decode($res, true);
assertTest("7. HTTP DELETE murni berhasil (HTTP 200)", $httpCode === 200 && isset($delHttpData['status']) && $delHttpData['status'] === 'success');
assertTest("7. File foto seniman kedua terhapus dari server", !file_exists($testAvatarPath2));

// ----------------------------------------------------
// 8. Uji Hapus Featured Artist (Soft Delete via is_deleted)
// ----------------------------------------------------
// Pastikan raden-saleh bisa dihapus dan tidak muncul, lalu dipulihkan untuk test selanjutnya
$ch = curl_init("$baseUrl/api/artists.php?action=delete");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieFileAdmin);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query(['artist_id' => 'raden-saleh']));
$res = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);
assertTest("8. Hapus featured artist 'raden-saleh' berhasil (HTTP 200)", $httpCode === 200);

$ch = curl_init("$baseUrl/api/artists.php");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieFileAdmin);
$res = curl_exec($ch);
curl_close($ch);
$listFeaturedDel = json_decode($res, true)['data'] ?? [];
$foundRadenAfterDel = false;
foreach ($listFeaturedDel as $art) {
    if ($art['id'] === 'raden-saleh') $foundRadenAfterDel = true;
}
assertTest("8. 'raden-saleh' tidak muncul di GET setelah dihapus", !$foundRadenAfterDel);

// Pulihkan raden-saleh kembali agar sistem tetap utuh
$pdo->prepare("DELETE FROM `artist_profiles` WHERE `id` = 'raden-saleh'")->execute();

$ch = curl_init("$baseUrl/api/artists.php");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieFileAdmin);
$res = curl_exec($ch);
curl_close($ch);
$listRestored = json_decode($res, true)['data'] ?? [];
$foundRadenRestored = false;
foreach ($listRestored as $art) {
    if ($art['id'] === 'raden-saleh') $foundRadenRestored = true;
}
assertTest("8. 'raden-saleh' berhasil dipulihkan", $foundRadenRestored);

// Bersihkan file cookie tes
if (file_exists($cookieFileAdmin)) @unlink($cookieFileAdmin);
if (file_exists($cookieFileUser)) @unlink($cookieFileUser);

echo "\n=== HASIL TEST: $testsPassed / $testsTotal LULUS ===\n";
if ($testsPassed === $testsTotal) {
    echo "🎉 SEMUA TEST FITUR HAPUS SENIMAN BERHASIL LULUS 100%!\n";
} else {
    echo "⚠️ ADA TEST YANG GAGAL!\n";
}
