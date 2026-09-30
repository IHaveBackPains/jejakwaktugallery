<?php
/**
 * Test Suite: Fitur Edit Foto Profil & Profil Artist (Admin)
 * Menguji skenario:
 * 1. Edit artist tanpa mengganti foto -> foto tetap sama.
 * 2. Edit artist dengan foto baru -> foto berhasil terganti.
 * 3. Artist tanpa foto -> bisa menambahkan foto.
 * 4. Refresh / GET ulang -> foto baru tetap tampil.
 * 5. Foto artist lain tidak ikut berubah.
 * 6. File lama lokal di uploads/ terhapus aman saat diganti foto baru.
 * 7. Validasi file (format & ukuran) bekerja dengan benar.
 */

$baseUrl = 'http://127.0.0.1/jejak-waktu';
$cookieFile = __DIR__ . '/test_artist_cookie.txt';
if (file_exists($cookieFile)) @unlink($cookieFile);

require_once __DIR__ . '/../api/db.php';

echo "=== MEMULAI TEST END-TO-END FITUR EDIT FOTO PROFIL ARTIST ===\n\n";

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
// 0. Login Admin
// ----------------------------------------------------
$ch = curl_init("$baseUrl/api/auth.php?action=login");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
    'username' => 'admin',
    'password' => 'admin123'
]));
curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieFile);
curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieFile);
$res = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

$authData = json_decode($res, true);
assertTest("Login Admin berhasil", $httpCode === 200 && isset($authData['status']) && $authData['status'] === 'success');

// Bersihkan data tes terdahulu di DB jika ada
$testArtistId = 'raden-saleh';
$otherArtistId = 'bambang-soetjipto';
$dynArtistId = 'dynamic-seniman-baru';
$pdo->prepare("DELETE FROM `artist_profiles` WHERE `id` IN (?, ?, ?)")->execute([$testArtistId, $otherArtistId, $dynArtistId]);

// ----------------------------------------------------
// 1. GET Awal: Cek data seniman
// ----------------------------------------------------
$ch = curl_init("$baseUrl/api/artists.php");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieFile);
$res = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

$getData = json_decode($res, true);
assertTest("GET api/artists.php berhasil (HTTP 200)", $httpCode === 200 && isset($getData['status']) && $getData['status'] === 'success');

$initialRaden = null;
$initialBambang = null;
foreach ($getData['data'] as $a) {
    if ($a['id'] === $testArtistId) $initialRaden = $a;
    if ($a['id'] === $otherArtistId) $initialBambang = $a;
}

assertTest("Seniman 'raden-saleh' ada di respons awal", $initialRaden !== null);
assertTest("Seniman 'bambang-soetjipto' ada di respons awal", $initialBambang !== null);

$initialRadenAvatar = $initialRaden['avatar'];
$initialBambangAvatar = $initialBambang['avatar'];

// ----------------------------------------------------
// TEST 1: Edit artist TANPA mengganti foto → foto tetap sama
// ----------------------------------------------------
$postFieldsNoPhoto = [
    'artist_id' => $testArtistId,
    'name' => 'Raden Saleh Syarif Bustaman (Nama Terupdate)',
    'role' => 'Pelukis Maestro Nusantara Era Kolonial',
    'current_avatar' => $initialRadenAvatar
];

$ch = curl_init("$baseUrl/api/artists.php");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $postFieldsNoPhoto);
curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieFile);
$res = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

$postRes1 = json_decode($res, true);
assertTest("1. POST edit artist tanpa upload foto berhasil", $httpCode === 200 && isset($postRes1['status']) && $postRes1['status'] === 'success');
assertTest("1. Foto yang dikembalikan tetap foto awal", $postRes1['data']['avatar'] === $initialRadenAvatar);

// Cek via GET ulang
$ch = curl_init("$baseUrl/api/artists.php");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieFile);
$res = curl_exec($ch);
curl_close($ch);
$checkData1 = json_decode($res, true);
$checkRaden1 = null;
foreach ($checkData1['data'] as $a) {
    if ($a['id'] === $testArtistId) $checkRaden1 = $a;
}
assertTest("1. GET: Foto Raden Saleh tetap sama setelah edit", $checkRaden1['avatar'] === $initialRadenAvatar);
assertTest("1. GET: Nama Raden Saleh berhasil diperbarui", strpos($checkRaden1['name'], 'Nama Terupdate') !== false);

// ----------------------------------------------------
// TEST 2: Edit artist DENGAN foto baru → foto berhasil terganti
// ----------------------------------------------------
// Siapkan file gambar PNG valid (1x1 transparent PNG)
$pngData = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==');
$tempFile1 = tempnam(sys_get_temp_dir(), 'avatar1_') . '.png';
file_put_contents($tempFile1, $pngData);

$postFieldsNewPhoto = [
    'artist_id' => $testArtistId,
    'current_avatar' => $initialRadenAvatar,
    'avatar_file' => new CURLFile($tempFile1, 'image/png', 'raden_foto_baru.png')
];

$ch = curl_init("$baseUrl/api/artists.php");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $postFieldsNewPhoto);
curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieFile);
$res = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);
@unlink($tempFile1);

$postRes2 = json_decode($res, true);
assertTest("2. POST edit artist dengan foto baru berhasil (HTTP 200)", $httpCode === 200 && isset($postRes2['status']) && $postRes2['status'] === 'success');
$firstUploadedAvatar = $postRes2['data']['avatar'] ?? '';
assertTest("2. Foto baru tersimpan di uploads/artist_avatar_raden-saleh_...", strpos($firstUploadedAvatar, 'uploads/artist_avatar_raden-saleh_') === 0);
assertTest("2. File foto fisik baru benar-benar tersimpan di disk", file_exists(__DIR__ . '/../' . $firstUploadedAvatar));

// ----------------------------------------------------
// TEST 3: Artist tanpa foto → bisa menambahkan foto
// ----------------------------------------------------
// Siapkan file WebP valid
$webpData = base64_decode('UklGRhoAAABXRUJQVlA4TA0AAAAvAAAAEAcQERGIiP4HAA==');
$tempFileDyn = tempnam(sys_get_temp_dir(), 'avatar_dyn_') . '.webp';
file_put_contents($tempFileDyn, $webpData);

$postFieldsDyn = [
    'artist_id' => $dynArtistId,
    'name' => 'Seniman Baru Nusantara',
    'role' => 'Fotografer Muda',
    'current_avatar' => '', // Tanpa foto sebelumnya
    'avatar_file' => new CURLFile($tempFileDyn, 'image/webp', 'seniman_baru.webp')
];

$ch = curl_init("$baseUrl/api/artists.php");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $postFieldsDyn);
curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieFile);
$res = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);
@unlink($tempFileDyn);

$postResDyn = json_decode($res, true);
assertTest("3. Artist tanpa foto berhasil ditambahkan foto baru", $httpCode === 200 && isset($postResDyn['status']) && $postResDyn['status'] === 'success');
$dynUploadedAvatar = $postResDyn['data']['avatar'] ?? '';
assertTest("3. File foto artist baru tersimpan di disk", file_exists(__DIR__ . '/../' . $dynUploadedAvatar));

// ----------------------------------------------------
// TEST 4 & 5: Refresh halaman / GET ulang dari API
// 4. Foto baru tetap tampil setelah refresh
// 5. Foto artist lain tidak ikut berubah
// ----------------------------------------------------
$ch = curl_init("$baseUrl/api/artists.php");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieFile);
$res = curl_exec($ch);
curl_close($ch);

$refreshedData = json_decode($res, true);
$refreshedRaden = null;
$refreshedBambang = null;
foreach ($refreshedData['data'] as $a) {
    if ($a['id'] === $testArtistId) $refreshedRaden = $a;
    if ($a['id'] === $otherArtistId) $refreshedBambang = $a;
}

assertTest("4. Refresh: Foto baru Raden Saleh tetap tampil secara persisten", $refreshedRaden['avatar'] === $firstUploadedAvatar);
assertTest("5. Foto artist lain (Bambang Soetjipto) TIDAK ikut berubah", $refreshedBambang['avatar'] === $initialBambangAvatar);

// ----------------------------------------------------
// TEST 6: Penggantian foto kedua menghapus file foto lama di uploads/ secara aman
// ----------------------------------------------------
// Siapkan file JPEG valid
$jpegData = base64_decode('/9j/4AAQSkZJRgABAQEASABIAAD/2wBDAP//////////////////////////////////////////////////////////////////////////////////////wgALCAABAAEBAREA/8QAFBABAAAAAAAAAAAAAAAAAAAAAP/aAAgBAQABPxA=');
$tempFile2 = tempnam(sys_get_temp_dir(), 'avatar2_') . '.jpg';
file_put_contents($tempFile2, $jpegData);

$postFieldsReplacePhoto = [
    'artist_id' => $testArtistId,
    'current_avatar' => $firstUploadedAvatar,
    'avatar_file' => new CURLFile($tempFile2, 'image/jpeg', 'raden_foto_v2.jpg')
];

$ch = curl_init("$baseUrl/api/artists.php");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $postFieldsReplacePhoto);
curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieFile);
$res = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);
@unlink($tempFile2);

$postRes3 = json_decode($res, true);
$secondUploadedAvatar = $postRes3['data']['avatar'] ?? '';
assertTest("6. Upload foto pengganti kedua berhasil", $httpCode === 200 && isset($postRes3['status']) && $postRes3['status'] === 'success');
assertTest("6. File foto lama ($firstUploadedAvatar) otomatis terhapus dari server", !file_exists(__DIR__ . '/../' . $firstUploadedAvatar));
assertTest("6. File foto baru ($secondUploadedAvatar) tersimpan di server", file_exists(__DIR__ . '/../' . $secondUploadedAvatar));

// ----------------------------------------------------
// TEST 7: Validasi format bukan gambar (.txt) ditolak
// ----------------------------------------------------
$fakeDoc = tempnam(sys_get_temp_dir(), 'fake_') . '.txt';
file_put_contents($fakeDoc, 'Bukan gambar');

$postFieldsFake = [
    'artist_id' => $testArtistId,
    'current_avatar' => $secondUploadedAvatar,
    'avatar_file' => new CURLFile($fakeDoc, 'text/plain', 'bukan_foto.txt')
];

$ch = curl_init("$baseUrl/api/artists.php");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $postFieldsFake);
curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieFile);
$res = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);
@unlink($fakeDoc);

$postResFake = json_decode($res, true);
assertTest("7. Validasi: Unggah format bukan gambar (.txt) ditolak (HTTP 400)", $httpCode === 400 && isset($postResFake['status']) && $postResFake['status'] === 'error');

// ----------------------------------------------------
// Cleanup Data Tes
// ----------------------------------------------------
if (file_exists(__DIR__ . '/../' . $secondUploadedAvatar)) @unlink(__DIR__ . '/../' . $secondUploadedAvatar);
if (file_exists(__DIR__ . '/../' . $dynUploadedAvatar)) @unlink(__DIR__ . '/../' . $dynUploadedAvatar);
$pdo->prepare("DELETE FROM `artist_profiles` WHERE `id` IN (?, ?, ?)")->execute([$testArtistId, $otherArtistId, $dynArtistId]);
if (file_exists($cookieFile)) @unlink($cookieFile);

echo "\n=== HASIL TEST: $testsPassed / $testsTotal LULUS ===\n";
if ($testsPassed === $testsTotal) {
    echo "🎉 SEMUA TEST BERHASIL LULUS 100%!\n";
} else {
    echo "⚠️ ADA TEST YANG GAGAL!\n";
}
