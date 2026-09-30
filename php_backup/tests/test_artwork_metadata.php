<?php
/**
 * Automated Test Suite: Artwork Metadata Fields & API Verification
 */

require_once __DIR__ . '/../api/session.php';
require_once __DIR__ . '/../api/db.php';

echo "=== MEMULAI TEST METADATA KARYA SENI ===\n\n";

$testsPassed = 0;
$testsTotal = 0;

function assertTest($description, $condition) {
    global $testsPassed, $testsTotal;
    $testsTotal++;
    if ($condition) {
        echo "✅ PASS: $description\n";
        $testsPassed++;
    } else {
        echo "❌ FAIL: $description\n";
    }
}

// TEST 1: Database Connection
assertTest("Koneksi PDO Database aktif", isset($pdo) && ($pdo instanceof PDO));

// TEST 2: Verify columns in photos table
if (isset($pdo) && ($pdo instanceof PDO)) {
    $stmt = $pdo->query("SHOW COLUMNS FROM `photos`");
    $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);

    $requiredFields = [
        'id', 'album_id', 'title', 'artist_1', 'artist_2',
        'year', 'date', 'location', 'medium', 'dimensions',
        'copyright', 'caption', 'image_src', 'thumb_src',
        'tilt', 'tape', 'note', 'created_at'
    ];

    foreach ($requiredFields as $field) {
        assertTest("Kolom '$field' tersedia pada tabel photos", in_array($field, $columns));
    }

    // TEST 3: Direct Insert with all 8 metadata fields
    $testAlbumId = 'jakarta-tempo-doeloe';
    $testTitle = 'Penangkapan Pangeran Diponegoro';
    $testArtist1 = 'Raden Saleh';
    $testArtist2 = 'Kolektif Sejarah Batavia';
    $testYear = '1857';
    $testDate = 'Tahun 1857';
    $testLocation = 'Batavia (Jakarta)';
    $testMedium = 'Oil on canvas (Cat minyak di atas kanvas)';
    $testDimensions = '112 x 178 cm';
    $testCopyright = 'Domain Publik / Museum Nasional Indonesia';
    $testCaption = 'Karya agung Raden Saleh yang melukiskan peristiwa bersejarah perlawanan Diponegoro.';
    $testImageSrc = 'https://images.unsplash.com/photo-1579783902614-a3fb3927b675?auto=format&fit=crop&w=1200&q=85';
    $testNote = 'Kurasi Pameran Utama';

    $insertSql = "INSERT INTO `photos` (`album_id`, `title`, `artist_1`, `artist_2`, `year`, `date`, `location`, `medium`, `dimensions`, `copyright`, `caption`, `image_src`, `thumb_src`, `tilt`, `tape`, `note`) 
                  VALUES (:album_id, :title, :artist_1, :artist_2, :year, :date, :location, :medium, :dimensions, :copyright, :caption, :image_src, :thumb_src, '-1.5deg', 'top-right', :note)";
    
    $stmtInsert = $pdo->prepare($insertSql);
    $execRes = $stmtInsert->execute([
        ':album_id' => $testAlbumId,
        ':title' => $testTitle,
        ':artist_1' => $testArtist1,
        ':artist_2' => $testArtist2,
        ':year' => $testYear,
        ':date' => $testDate,
        ':location' => $testLocation,
        ':medium' => $testMedium,
        ':dimensions' => $testDimensions,
        ':copyright' => $testCopyright,
        ':caption' => $testCaption,
        ':image_src' => $testImageSrc,
        ':thumb_src' => $testImageSrc,
        ':note' => $testNote
    ]);

    $newPhotoId = $pdo->lastInsertId();
    assertTest("Berhasil insert record foto baru dengan ID: $newPhotoId", $execRes && $newPhotoId > 0);

    // TEST 4: Fetch and assert exact field values
    $stmtFetch = $pdo->prepare("SELECT * FROM `photos` WHERE `id` = :id");
    $stmtFetch->execute([':id' => $newPhotoId]);
    $fetched = $stmtFetch->fetch();

    assertTest("Judul Karya (title) cocok", $fetched['title'] === $testTitle);
    assertTest("Nama Seniman 1 (artist_1) cocok", $fetched['artist_1'] === $testArtist1);
    assertTest("Nama Seniman 2 (artist_2) cocok", $fetched['artist_2'] === $testArtist2);
    assertTest("Tahun Pembuatan (year) cocok", $fetched['year'] === $testYear);
    assertTest("Tempat (location) cocok", $fetched['location'] === $testLocation);
    assertTest("Media / Teknik (medium) cocok", $fetched['medium'] === $testMedium);
    assertTest("Ukuran / Dimensi (dimensions) cocok", $fetched['dimensions'] === $testDimensions);
    assertTest("Hak Cipta (copyright) cocok", $fetched['copyright'] === $testCopyright);
    assertTest("Kisah (caption) cocok", $fetched['caption'] === $testCaption);
    assertTest("Catatan (note) cocok", $fetched['note'] === $testNote);

    // Cleanup test record
    $stmtDelete = $pdo->prepare("DELETE FROM `photos` WHERE `id` = :id");
    $stmtDelete->execute([':id' => $newPhotoId]);
    assertTest("Cleanup data pengujian berhasil", true);
}

echo "\n========================================\n";
echo "HASIL PENGUJIAN: $testsPassed / $testsTotal TEST BERHASIL\n";
echo "========================================\n";

if ($testsPassed === $testsTotal) {
    exit(0);
} else {
    exit(1);
}
