<?php
/**
 * Test Suite: Global Dual-Table Search Feature Verification (Albums & Photos)
 */

require_once __DIR__ . '/../api/session.php';
require_once __DIR__ . '/../api/db.php';

echo "=== MEMULAI TEST FITUR PENCARIAN GLOBAL (ALBUM & FOTO) ===\n\n";

$testsPassed = 0;
$testsTotal = 0;

function assertSearch($desc, $condition) {
    global $testsPassed, $testsTotal;
    $testsTotal++;
    if ($condition) {
        echo "✅ PASS: $desc\n";
        $testsPassed++;
    } else {
        echo "❌ FAIL: $desc\n";
    }
}

// 1. Test Koneksi Database
assertSearch("Koneksi Database PDO tersedia", isset($pdo) && ($pdo instanceof PDO));

// 2. Test SQL Query Pencarian di Backend PHP (Dual-Table: albums & photos)
// Skenario A: Pencarian kosong (harus mengambil semua album)
$stmtAll = $pdo->query("SELECT * FROM `albums` ORDER BY `created_at` ASC");
$allAlbums = $stmtAll->fetchAll();
assertSearch("Query database tanpa filter berhasil (total: " . count($allAlbums) . " album)", count($allAlbums) > 0);

// Skenario B: Pencarian spesifik keyword 'Jakarta' (ada di album dan foto)
$kwJakarta = 'Jakarta';
$termJkt = '%' . $kwJakarta . '%';
$stmtJktAlb = $pdo->prepare("SELECT * FROM `albums` 
        WHERE `title` LIKE ? 
           OR `subtitle` LIKE ? 
           OR `description` LIKE ? 
           OR `era` LIKE ? 
           OR `location` LIKE ? 
        ORDER BY `created_at` ASC");
$stmtJktAlb->execute([$termJkt, $termJkt, $termJkt, $termJkt, $termJkt]);
$jktAlbums = $stmtJktAlb->fetchAll();
assertSearch("Query albums dengan filter 'Jakarta' berhasil (ditemukan: " . count($jktAlbums) . ")", count($jktAlbums) > 0);

$stmtJktPhotos = $pdo->prepare("SELECT p.*, a.title AS album_title 
        FROM `photos` p 
        LEFT JOIN `albums` a ON a.id = p.album_id 
        WHERE p.`title` LIKE ? 
           OR p.`caption` LIKE ? 
           OR p.`location` LIKE ? 
           OR p.`note` LIKE ? 
           OR p.`artist_1` LIKE ? 
           OR p.`artist_2` LIKE ? 
           OR p.`medium` LIKE ? 
           OR p.`year` LIKE ? 
        ORDER BY p.`id` DESC");
$stmtJktPhotos->execute([$termJkt, $termJkt, $termJkt, $termJkt, $termJkt, $termJkt, $termJkt, $termJkt]);
$jktPhotos = $stmtJktPhotos->fetchAll();
assertSearch("Query photos dengan filter 'Jakarta' berhasil (ditemukan: " . count($jktPhotos) . " foto)", count($jktPhotos) > 0);

// Skenario C: Pencarian spesifik ke atribut foto saja (misal keyword 'Bundaran HI')
$kwPhotoOnly = 'Bundaran HI';
$termPhotoOnly = '%' . $kwPhotoOnly . '%';
$stmtPOnly = $pdo->prepare("SELECT p.*, a.title AS album_title 
        FROM `photos` p 
        LEFT JOIN `albums` a ON a.id = p.album_id 
        WHERE p.`title` LIKE ? 
           OR p.`caption` LIKE ? 
           OR p.`location` LIKE ? 
           OR p.`note` LIKE ? 
           OR p.`artist_1` LIKE ? 
           OR p.`artist_2` LIKE ? 
           OR p.`medium` LIKE ? 
           OR p.`year` LIKE ? 
        ORDER BY p.`id` DESC");
$stmtPOnly->execute([$termPhotoOnly, $termPhotoOnly, $termPhotoOnly, $termPhotoOnly, $termPhotoOnly, $termPhotoOnly, $termPhotoOnly, $termPhotoOnly]);
$photoOnlyResults = $stmtPOnly->fetchAll();
assertSearch("Query photos dengan keyword spesifik foto 'Bundaran HI' berhasil (ditemukan: " . count($photoOnlyResults) . " foto)", count($photoOnlyResults) > 0);

// Skenario D: Pencarian keyword yang tidak ada di kedua tabel
$kwFake = 'KataKunciYangPastiTidakAda999xyz';
$termFake = '%' . $kwFake . '%';
$stmtFakeA = $pdo->prepare("SELECT * FROM `albums` WHERE `title` LIKE ? OR `description` LIKE ?");
$stmtFakeA->execute([$termFake, $termFake]);
$fakeA = $stmtFakeA->fetchAll();

$stmtFakeP = $pdo->prepare("SELECT * FROM `photos` WHERE `title` LIKE ? OR `caption` LIKE ?");
$stmtFakeP->execute([$termFake, $termFake]);
$fakeP = $stmtFakeP->fetchAll();
assertSearch("Query keyword palsu aman dan menghasilkan 0 album & 0 foto", count($fakeA) === 0 && count($fakeP) === 0);

// 3. Test HTTP GET via cURL ke index.php
$baseUrl = 'http://127.0.0.1/jejak-waktu';
$cookieFile = __DIR__ . '/test_search_cookie.txt';
if (file_exists($cookieFile)) @unlink($cookieFile);

// Login dulu sebagai user untuk mendapatkan session cookie
$ch = curl_init("$baseUrl/api/auth.php?action=login");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
    'username' => 'admin',
    'password' => 'admin123'
]));
curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieFile);
curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieFile);
$loginRes = curl_exec($ch);
curl_close($ch);

// Test GET index.php tanpa query string (Default view intact)
$ch = curl_init("$baseUrl/index.php");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieFile);
$htmlIndex = curl_exec($ch);
curl_close($ch);

assertSearch("Form pencarian dengan method GET ada di index.php", strpos($htmlIndex, '<form action="index.php" method="GET"') !== false);
assertSearch("Input name='search' ada di index.php", strpos($htmlIndex, 'name="search"') !== false);
assertSearch("Tombol submit type='submit' ada di dalam form", strpos($htmlIndex, 'type="submit"') !== false);
assertSearch("Default view intact: 3D Open Album Spread ada saat search kosong", strpos($htmlIndex, 'hero-open-album') !== false);
assertSearch("Default view intact: Seksi 'Buku Album Terbaru' ada saat search kosong", strpos($htmlIndex, 'Buku Album Terbaru') !== false);

// Test GET index.php?search=Jakarta (Dual section results)
$ch = curl_init("$baseUrl/index.php?search=" . urlencode("Jakarta"));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieFile);
$htmlSearch = curl_exec($ch);
curl_close($ch);

assertSearch("Halaman index.php?search=Jakarta mengembalikan status 200", !empty($htmlSearch));
assertSearch("Nilai pencarian tetap terisi di input value='Jakarta'", strpos($htmlSearch, 'value="Jakarta"') !== false);
assertSearch("Seksi 1 'Album yang Ditemukan' ditampilkan", strpos($htmlSearch, 'Album yang Ditemukan') !== false);
assertSearch("Seksi 2 'Foto yang Ditemukan' ditampilkan", strpos($htmlSearch, 'Foto yang Ditemukan') !== false);
assertSearch("Grid foto polaroid hasil pencarian ditampilkan", strpos($htmlSearch, 'search-photos-grid') !== false);
assertSearch("Tautan Hapus Pencarian disediakan", strpos($htmlSearch, 'Hapus Pencarian') !== false);
assertSearch("window.MATCHED_PHOTOS diinjeksi ke frontend", strpos($htmlSearch, 'window.MATCHED_PHOTOS') !== false);

// Test GET index.php?search=Bundaran+HI (Foto match)
$ch = curl_init("$baseUrl/index.php?search=" . urlencode("Bundaran HI"));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieFile);
$htmlPhotoSearch = curl_exec($ch);
curl_close($ch);
assertSearch("Hasil pencarian khusus foto menampilkan kartu polaroid Bundaran HI", strpos($htmlPhotoSearch, 'Bundaran HI') !== false);

// Test GET index.php?search=KataKunciPalsu (Empty state message)
$ch = curl_init("$baseUrl/index.php?search=" . urlencode($kwFake));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieFile);
$htmlEmpty = curl_exec($ch);
curl_close($ch);

assertSearch("Pesan ramah 'Tidak ada hasil pencarian untuk...' muncul saat 0 hasil di kedua tabel", strpos($htmlEmpty, 'Tidak ada hasil pencarian untuk') !== false);
assertSearch("Tombol kembali 'Tampilkan Semua Koleksi' tersedia di halaman kosong", strpos($htmlEmpty, 'Tampilkan Semua Koleksi') !== false);

// 4. Test API GET api/albums.php?search=Jakarta
$ch = curl_init("$baseUrl/api/albums.php?search=" . urlencode("Jakarta"));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieFile);
$apiRes = curl_exec($ch);
curl_close($ch);

$apiData = json_decode($apiRes, true);
assertSearch("API api/albums.php?search=Jakarta sukses merespon", isset($apiData['status']) && $apiData['status'] === 'success');
assertSearch("API mengembalikan data album terfilter", isset($apiData['data']) && is_array($apiData['data']));
assertSearch("API mengembalikan matched_photos terfilter", isset($apiData['matched_photos']) && is_array($apiData['matched_photos']) && count($apiData['matched_photos']) > 0);

if (file_exists($cookieFile)) @unlink($cookieFile);

echo "\n============================================\n";
echo "HASIL AKHIR: $testsPassed / $testsTotal TEST BERHASIL!\n";
echo "============================================\n";

exit($testsPassed === $testsTotal ? 0 : 1);
