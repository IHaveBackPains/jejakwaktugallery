<?php
/**
 * End-to-End Simulation Test: Auth, Upload with 8 Metadata fields, Fetch, and HTML Verification
 */

$baseUrl = 'http://127.0.0.1/jejak-waktu';
$cookieFile = __DIR__ . '/test_cookie.txt';
if (file_exists($cookieFile)) @unlink($cookieFile);

echo "=== MEMULAI TEST END-TO-END SIMULASI LENGKAP ===\n\n";

$testsPassed = 0;
$testsTotal = 0;

function assertE2E($desc, $cond) {
    global $testsPassed, $testsTotal;
    $testsTotal++;
    if ($cond) {
        echo "✅ PASS: $desc\n";
        $testsPassed++;
    } else {
        echo "❌ FAIL: $desc\n";
    }
}

// 1. Test Login Admin
$ch = curl_init("$baseUrl/api/auth.php?action=login");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
    'username' => 'admin',
    'password' => 'admin123'
]));
curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieFile);
curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieFile);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

$authResult = json_decode($response, true);
assertE2E("Login Admin berhasil (HTTP 200)", $httpCode === 200 && isset($authResult['status']) && $authResult['status'] === 'success');
assertE2E("Role sesi aktif adalah 'admin'", isset($authResult['data']['role']) && $authResult['data']['role'] === 'admin');

// 2. Test Add Photo with all 8 metadata fields
$testData = [
    'album_id' => 'jakarta-tempo-doeloe',
    'title' => 'Lukisan Senja di Selat Sunda',
    'artist_1' => 'Raden Saleh Syarif Bustaman',
    'artist_2' => 'Kolektif Seni Batavia',
    'year' => '1865',
    'location' => 'Banten & Selat Sunda',
    'medium' => 'Oil on canvas (Cat Minyak di Atas Kanvas)',
    'dimensions' => '120 x 180 cm',
    'copyright' => 'Domain Publik / Arsip Nasional RI',
    'caption' => 'Lukisan agung yang merefleksikan keindahan dan kedahsyatan laut Nusantara.',
    'note' => 'Arsip Kurasi Utama 2026',
    'photo_url' => 'https://images.unsplash.com/photo-1579783902614-a3fb3927b675?auto=format&fit=crop&w=1200&q=85'
];

$ch = curl_init("$baseUrl/api/add_photo.php");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $testData);
curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieFile);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

$uploadResult = json_decode($response, true);
assertE2E("Upload / Tempel Foto API Berhasil (HTTP 200)", $httpCode === 200 && isset($uploadResult['status']) && $uploadResult['status'] === 'success');
$savedData = $uploadResult['data'] ?? [];
assertE2E("Field 'title' (1. Judul Karya) tersimpan", ($savedData['title'] ?? '') === $testData['title']);
assertE2E("Field 'artist_1' (2. Seniman 1) tersimpan", ($savedData['artist_1'] ?? '') === $testData['artist_1']);
assertE2E("Field 'artist_2' (3. Seniman 2) tersimpan", ($savedData['artist_2'] ?? '') === $testData['artist_2']);
assertE2E("Field 'year' (4. Tahun Pembuatan) tersimpan", ($savedData['year'] ?? '') === $testData['year']);
assertE2E("Field 'location' (5. Tempat) tersimpan", ($savedData['location'] ?? '') === $testData['location']);
assertE2E("Field 'medium' (6. Media/Teknik) tersimpan", ($savedData['medium'] ?? '') === $testData['medium']);
assertE2E("Field 'dimensions' (7. Ukuran/Dimensi) tersimpan", ($savedData['dimensions'] ?? '') === $testData['dimensions']);
assertE2E("Field 'copyright' (8. Hak Cipta) tersimpan", ($savedData['copyright'] ?? '') === $testData['copyright']);

$createdPhotoId = $savedData['id'] ?? 0;

// 3. Test Fetch Albums API and verify serialization of metadata
$ch = curl_init("$baseUrl/api/albums.php?id=jakarta-tempo-doeloe");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieFile);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

$albumResult = json_decode($response, true);
assertE2E("Fetch Album API Berhasil (HTTP 200)", $httpCode === 200 && isset($albumResult['data']['photos']));

$photosList = $albumResult['data']['photos'] ?? [];
$foundPhoto = null;
foreach ($photosList as $p) {
    if ($p['id'] == $createdPhotoId) {
        $foundPhoto = $p;
        break;
    }
}

assertE2E("Foto yang baru diunggah ditemukan di list album", $foundPhoto !== null);
if ($foundPhoto) {
    assertE2E("API Albums mengembalikan artist_1 dengan benar", $foundPhoto['artist_1'] === $testData['artist_1']);
    assertE2E("API Albums mengembalikan artist_2 dengan benar", $foundPhoto['artist_2'] === $testData['artist_2']);
    assertE2E("API Albums mengembalikan medium dengan benar", $foundPhoto['medium'] === $testData['medium']);
    assertE2E("API Albums mengembalikan dimensions dengan benar", $foundPhoto['dimensions'] === $testData['dimensions']);
    assertE2E("API Albums mengembalikan copyright dengan benar", $foundPhoto['copyright'] === $testData['copyright']);
}

// 4. Test Form HTML Elements in index.php
$indexHtml = file_get_contents(__DIR__ . '/../index.php');
assertE2E("Input ID 'form-photo-title' ada di index.php", strpos($indexHtml, 'id="form-photo-title"') !== false);
assertE2E("Input ID 'form-photo-artist-1' ada di index.php", strpos($indexHtml, 'id="form-photo-artist-1"') !== false);
assertE2E("Input ID 'form-photo-artist-2' ada di index.php", strpos($indexHtml, 'id="form-photo-artist-2"') !== false);
assertE2E("Input ID 'form-photo-year' ada di index.php", strpos($indexHtml, 'id="form-photo-year"') !== false);
assertE2E("Input ID 'form-photo-location' ada di index.php", strpos($indexHtml, 'id="form-photo-location"') !== false);
assertE2E("Input ID 'form-photo-medium' ada di index.php", strpos($indexHtml, 'id="form-photo-medium"') !== false);
assertE2E("Input ID 'form-photo-dimension' ada di index.php", strpos($indexHtml, 'id="form-photo-dimension"') !== false);
assertE2E("Input ID 'form-photo-copyright' ada di index.php", strpos($indexHtml, 'id="form-photo-copyright"') !== false);

// 5. Cleanup Test Record
if ($createdPhotoId > 0) {
    $ch = curl_init("$baseUrl/api/delete_photo.php");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, ['photo_id' => $createdPhotoId]);
    curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieFile);
    $response = curl_exec($ch);
    curl_close($ch);
    assertE2E("Pembersihan (cleanup) foto pengujian berhasil", true);
}

if (file_exists($cookieFile)) @unlink($cookieFile);

echo "\n========================================\n";
echo "HASIL PENGUJIAN E2E: $testsPassed / $testsTotal TEST BERHASIL\n";
echo "========================================\n";

if ($testsPassed === $testsTotal) {
    exit(0);
} else {
    exit(1);
}
