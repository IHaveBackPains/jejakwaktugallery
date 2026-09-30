<?php
/**
 * Test Suite: Validasi Peta Interaktif & Database Galeri Surabaya
 */

$baseUrl = 'http://localhost/jejak-waktu';
$allPassed = true;

function assertTest($condition, $message) {
    global $allPassed;
    if ($condition) {
        echo "  [PASS] $message\n";
    } else {
        echo "  [FAIL] $message\n";
        $allPassed = false;
    }
}

echo "=== 1. TEST API /api/galleries.php ===\n";
$apiUrl = "$baseUrl/api/galleries.php";
$jsonStr = @file_get_contents($apiUrl);

assertTest(!empty($jsonStr), "API merespon data dari $apiUrl");
$data = json_decode($jsonStr, true);
assertTest($data && isset($data['status']) && $data['status'] === 'success', "Status respon API adalah 'success'");
assertTest(isset($data['count']) && $data['count'] >= 28, "Jumlah total galeri terdata adalah " . ($data['count'] ?? 0) . " (minimal 28)");

echo "\n=== 2. VALIDASI DATA SETIAP LOKASI (FIELD LENGKAP & KOORDINAT SURABAYA) ===\n";
$requiredFields = [
    'name', 'address', 'category', 'status', 'founder_or_owner',
    'established_year', 'history', 'focus', 'events', 'latitude',
    'longitude', 'website', 'instagram', 'image', 'sources'
];

$validSurabaya = true;
$missingFields = [];
$categoriesFound = [];
$statusesFound = [];

foreach ($data['data'] as $idx => $g) {
    foreach ($requiredFields as $f) {
        if (!array_key_exists($f, $g)) {
            $missingFields[] = "Galeri #{$g['name']} kekurangan field '$f'";
        }
    }

    // Cek batas koordinat Kota Surabaya (-7.40 s/d -7.18 S, 112.55 s/d 112.88 E)
    $lat = $g['latitude'];
    $lng = $g['longitude'];
    if ($lat !== null && $lng !== null) {
        if ($lat < -7.42 || $lat > -7.15 || $lng < 112.55 || $lng > 112.90) {
            $validSurabaya = false;
            echo "    [WARN] Koordinat di luar Surabaya: {$g['name']} ($lat, $lng)\n";
        }
    }

    $categoriesFound[$g['category']] = ($categoriesFound[$g['category']] ?? 0) + 1;
    $statusesFound[$g['status']] = ($statusesFound[$g['status']] ?? 0) + 1;
}

assertTest(empty($missingFields), "Semua lokasi memiliki 15 field wajib lengkap");
assertTest($validSurabaya, "Semua koordinat berada di dalam wilayah Kota Surabaya");

echo "\n=== 3. VALIDASI FILTER KATEGORI & STATUS ===\n";
$expectedCats = [
    'Galeri Seni', 'Galeri Seni Kontemporer', 'Galeri Institusi/Pemerintah',
    'Art Space', 'Creative Space', 'Ruang Budaya',
    'Galeri Seni Tradisional', 'Galeri Seni Khusus', 'Galeri Historis'
];

foreach ($expectedCats as $c) {
    assertTest(isset($categoriesFound[$c]) && $categoriesFound[$c] > 0, "Kategori '$c' memiliki data ({$categoriesFound[$c]} lokasi)");
}

$expectedStatuses = ['Aktif', 'Historis/Tidak aktif', 'Perlu diverifikasi'];
foreach ($expectedStatuses as $s) {
    assertTest(isset($statusesFound[$s]) && $statusesFound[$s] > 0, "Status '$s' memiliki data ({$statusesFound[$s]} lokasi)");
}

echo "\n=== 4. TEST FILTER & SEARCH VIA API QUERY PARAMS ===\n";
$searchRes = json_decode(@file_get_contents("$apiUrl?q=Orasis"), true);
assertTest($searchRes && $searchRes['count'] >= 1 && $searchRes['data'][0]['name'] === 'Orasis Art Space', "Pencarian '?q=Orasis' menemukan Orasis Art Space");

$catRes = json_decode(@file_get_contents("$apiUrl?category=" . urlencode('Art Space')), true);
assertTest($catRes && $catRes['count'] >= 3, "Filter '?category=Art Space' menghasilkan " . ($catRes['count'] ?? 0) . " lokasi");

$statusRes = json_decode(@file_get_contents("$apiUrl?status=" . urlencode('Historis/Tidak aktif')), true);
assertTest($statusRes && $statusRes['count'] >= 3, "Filter '?status=Historis/Tidak aktif' menghasilkan " . ($statusRes['count'] ?? 0) . " lokasi");

echo "\n=== 5. VALIDASI INTEGRASI UI & KOMPONEN MAP DI INDEX.PHP ===\n";
$indexHtml = @file_get_contents("$baseUrl/index.php");
assertTest(!empty($indexHtml), "index.php berhasil dimuat");
assertTest(strpos($indexHtml, 'id="galleryMapWidget"') !== false, "Sidebar widget '#galleryMapWidget' tersedia");
assertTest(strpos($indexHtml, 'id="galleryMapModal"') !== false, "Modal fullscreen '#galleryMapModal' tersedia");
assertTest(strpos($indexHtml, 'id="gmapSearchInput"') !== false, "Search bar '#gmapSearchInput' tersedia");
assertTest(strpos($indexHtml, 'id="gmapCategoryFilter"') !== false, "Dropdown kategori '#gmapCategoryFilter' tersedia");
assertTest(strpos($indexHtml, 'id="gmapStatusFilter"') !== false, "Dropdown status '#gmapStatusFilter' tersedia");
assertTest(strpos($indexHtml, 'id="gmapCategoryPillsBar"') !== false, "Category pills quick-bar '#gmapCategoryPillsBar' tersedia");
assertTest(strpos($indexHtml, 'id="gmapListDrawer"') !== false, "Daftar tempat drawer '#gmapListDrawer' tersedia");
assertTest(strpos($indexHtml, 'id="gmapLegendDrawer"') !== false, "Legenda kategori drawer '#gmapLegendDrawer' tersedia");
assertTest(strpos($indexHtml, 'id="galleryInfoPanelModal"') !== false, "Detail panel modal '#galleryInfoPanelModal' tersedia");
assertTest(strpos($indexHtml, 'js/leaflet.markercluster.js') !== false, "Script 'leaflet.markercluster.js' dimuat");
assertTest(strpos($indexHtml, 'js/gallery-map.js') !== false, "Script 'gallery-map.js' dimuat");
assertTest(strpos($indexHtml, 'css/gallery-map.css') !== false, "Stylesheet 'gallery-map.css' dimuat");

echo "\n============================================\n";
if ($allPassed) {
    echo "HASIL: SEMUA PENGUJIAN BERHASIL LULUS 100%!\n";
} else {
    echo "HASIL: ADA PENGUJIAN YANG GAGAL!\n";
}
echo "============================================\n";
