<?php
/**
 * API: Daftar Seniman & Kontributor Arsip (Artists Directory)
 * Jejak Waktu - Galeri Album Foto & Kenangan Sejarah
 * Mendukung GET (daftar seniman dengan override profil) & POST (edit profil + unggah foto seniman)
 */

header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/session.php';
require_once __DIR__ . '/db.php';

require_auth(true);

$method = $_SERVER['REQUEST_METHOD'];
$override = $_POST['_method'] ?? $_GET['_method'] ?? null;
if ($override === 'DELETE') {
    $method = 'DELETE';
}
$action = $_GET['action'] ?? $_POST['action'] ?? null;

/**
 * Hapus file foto profil seniman secara aman dari storage/server lokal
 * Mencegah penumpukan file sampah dan mencegah path traversal
 */
function delete_artist_avatar_file($avatarPath) {
    if (empty($avatarPath) || !is_string($avatarPath)) return;

    // Jangan sentuh jika URL eksternal (Unsplash, dsb)
    if (preg_match('/^https?:\/\//i', $avatarPath)) {
        return;
    }

    $cleanPath = ltrim($avatarPath, '/\\');
    if (strpos($cleanPath, 'uploads/') === 0) {
        $uploadsDir = realpath(__DIR__ . '/../uploads');
        $fullPath = realpath(__DIR__ . '/../' . $cleanPath);
        if ($fullPath && $uploadsDir && strpos($fullPath, $uploadsDir) === 0 && is_file($fullPath)) {
            @unlink($fullPath);
        }
    }
}

// ========================================================
// 1. DELETE / POST (action=delete): HAPUS SENIMAN (ADMIN ONLY)
// ========================================================
if ($method === 'DELETE' || ($method === 'POST' && $action === 'delete')) {
    require_admin();

    if (!isset($pdo) || !($pdo instanceof PDO)) {
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => 'Koneksi database tidak tersedia']);
        exit();
    }

    $input_id = $_POST['artist_id'] ?? $_POST['id'] ?? $_GET['artist_id'] ?? $_GET['id'] ?? null;
    $input_avatar = $_POST['current_avatar'] ?? $_POST['avatar'] ?? null;

    if (empty($input_id)) {
        $rawJson = @file_get_contents('php://input');
        if (!empty($rawJson)) {
            $parsed = @json_decode($rawJson, true);
            if (is_array($parsed)) {
                $input_id = $parsed['artist_id'] ?? $parsed['id'] ?? null;
                $input_avatar = $parsed['current_avatar'] ?? $parsed['avatar'] ?? null;
            }
        }
    }

    $artist_id = trim((string)$input_id);
    if ($artist_id === '') {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'ID seniman wajib disertakan']);
        exit();
    }

    try {
        // 1. Ambil data seniman dari artist_profiles jika ada
        $stmtCheck = $pdo->prepare("SELECT * FROM `artist_profiles` WHERE `id` = :id");
        $stmtCheck->execute([':id' => $artist_id]);
        $existing = $stmtCheck->fetch(PDO::FETCH_ASSOC);

        $artistName = $existing['name'] ?? null;
        $avatarPath = $existing['avatar'] ?? $input_avatar;

        $featuredArtistsList = [
            'raden-saleh' => 'Raden Saleh Syarif Bustaman',
            'bambang-soetjipto' => 'Arsiparis Bambang S. / Bambang Soetjipto',
            'rina-kartika' => 'Rina Kartika',
            'ki-suryo' => 'Ki Suryo Sasongko',
            'soedarsono' => 'Ir. Soedarsono',
            'andra-permana' => 'Andra Permana',
            'maya-indah' => 'Maya Indah'
        ];

        if (empty($artistName) && isset($featuredArtistsList[$artist_id])) {
            $artistName = $featuredArtistsList[$artist_id];
        }

        // 2. Hapus file foto profil dari server/storage (jika tersimpan di uploads/ lokal)
        if (!empty($avatarPath)) {
            delete_artist_avatar_file($avatarPath);
        }
        if (!empty($existing['avatar']) && $existing['avatar'] !== $avatarPath) {
            delete_artist_avatar_file($existing['avatar']);
        }

        // 3. Cek ketersediaan kolom is_deleted pada artist_profiles
        $artistCols = $pdo->query("SHOW COLUMNS FROM `artist_profiles`")->fetchAll(PDO::FETCH_COLUMN);
        $hasIsDeleted = in_array('is_deleted', $artistCols);

        // Jika ID termasuk dalam daftar seniman bawaan (featured), simpan status is_deleted = 1 agar tidak muncul kembali
        $isFeaturedId = isset($featuredArtistsList[$artist_id]);

        if ($hasIsDeleted && $isFeaturedId) {
            if ($existing) {
                $stmtDel = $pdo->prepare("UPDATE `artist_profiles` SET `is_deleted` = 1, `avatar` = NULL WHERE `id` = :id");
                $stmtDel->execute([':id' => $artist_id]);
            } else {
                $stmtDel = $pdo->prepare("INSERT INTO `artist_profiles` (`id`, `name`, `is_deleted`) VALUES (:id, :name, 1) ON DUPLICATE KEY UPDATE `is_deleted` = 1, `avatar` = NULL");
                $stmtDel->execute([':id' => $artist_id, ':name' => $artistName]);
            }
        } else {
            // Hapus data seniman dari database
            $stmtDel = $pdo->prepare("DELETE FROM `artist_profiles` WHERE `id` = :id");
            $stmtDel->execute([':id' => $artist_id]);
        }

        // 4. Bersihkan referensi nama seniman dari tabel photos agar seniman dinamis tidak ter-generate kembali
        $namesToClear = array_unique(array_filter([$artist_id, $artistName]));
        if (strpos($artist_id, 'dynamic-') === 0) {
            $dynClean = trim(str_replace('-', ' ', substr($artist_id, 8)));
            if (!empty($dynClean)) {
                $namesToClear[] = $dynClean;
            }
        }

        foreach ($namesToClear as $target) {
            $stmtP1 = $pdo->prepare("UPDATE `photos` SET `artist_1` = NULL WHERE LOWER(TRIM(`artist_1`)) = LOWER(TRIM(:t))");
            $stmtP1->execute([':t' => $target]);

            $stmtP2 = $pdo->prepare("UPDATE `photos` SET `artist_2` = NULL WHERE LOWER(TRIM(`artist_2`)) = LOWER(TRIM(:t))");
            $stmtP2->execute([':t' => $target]);
        }

        echo json_encode([
            'status'  => 'success',
            'message' => 'Seniman dan foto profilnya berhasil dihapus.',
            'data'    => [
                'id' => $artist_id
            ]
        ]);
        exit();

    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode([
            'status'  => 'error',
            'message' => 'Gagal menghapus seniman: ' . $e->getMessage()
        ]);
        exit();
    }
}

// ========================================================
// 2. POST: EDIT PROFIL SENIMAN (FOTO & INFORMASI) - ADMIN ONLY
// ========================================================
if ($method === 'POST') {
    require_admin();

    if (!isset($pdo) || !($pdo instanceof PDO)) {
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => 'Koneksi database tidak tersedia']);
        exit();
    }

    $artist_id = isset($_POST['artist_id']) ? trim($_POST['artist_id']) : '';
    if (empty($artist_id)) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'ID seniman wajib disertakan']);
        exit();
    }

    $name     = isset($_POST['name']) && trim($_POST['name']) !== '' ? trim($_POST['name']) : null;
    $role     = isset($_POST['role']) && trim($_POST['role']) !== '' ? trim($_POST['role']) : null;
    $era      = isset($_POST['era']) && trim($_POST['era']) !== '' ? trim($_POST['era']) : null;
    $category = isset($_POST['category']) && trim($_POST['category']) !== '' ? trim($_POST['category']) : null;
    $bio      = isset($_POST['bio']) && trim($_POST['bio']) !== '' ? trim($_POST['bio']) : null;
    $location = isset($_POST['location']) && trim($_POST['location']) !== '' ? trim($_POST['location']) : null;

    $current_avatar = isset($_POST['current_avatar']) ? trim($_POST['current_avatar']) : '';

    $new_avatar_path = null;

    // Cek apakah ada file foto profil baru yang diunggah
    if (isset($_FILES['avatar_file']) && $_FILES['avatar_file']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['avatar_file'];

        // 1. Validasi batas ukuran file (maksimal 5MB)
        $max_bytes = 5 * 1024 * 1024;
        if ($file['size'] > $max_bytes) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Ukuran file foto profil terlalu besar (maksimal 5MB)']);
            exit();
        }

        // 2. Validasi ekstensi yang diizinkan (JPG, JPEG, PNG, WebP)
        $allowed_exts = ['jpg', 'jpeg', 'png', 'webp'];
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

        if (!in_array($ext, $allowed_exts)) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Format file tidak didukung. Harap gunakan format JPG, JPEG, PNG, atau WebP']);
            exit();
        }

        // 3. Validasi tipe gambar MIME nyata
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        $allowed_mimes = ['image/jpeg', 'image/png', 'image/webp'];
        if (!in_array($mime, $allowed_mimes)) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'File yang diunggah bukan format gambar yang valid']);
            exit();
        }

        // 4. Pastikan folder uploads tersedia
        $upload_dir = __DIR__ . '/../uploads/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }

        // 5. Nama file aman & unik
        $safe_slug = preg_replace('/[^a-z0-9_-]/', '', strtolower($artist_id));
        if (empty($safe_slug)) $safe_slug = 'artist';
        $filename = 'artist_avatar_' . $safe_slug . '_' . time() . '_' . rand(1000, 9999) . '.' . $ext;
        $destination = $upload_dir . $filename;

        if (move_uploaded_file($file['tmp_name'], $destination)) {
            $new_avatar_path = 'uploads/' . $filename;

            // 6. Hapus foto lama JIKA tersimpan di uploads lokal (tidak menghapus jika URL eksternal / Unsplash)
            try {
                $stmtOld = $pdo->prepare("SELECT `avatar` FROM `artist_profiles` WHERE `id` = :id");
                $stmtOld->execute([':id' => $artist_id]);
                $oldAvatar = $stmtOld->fetchColumn();

                if (empty($oldAvatar) && !empty($current_avatar)) {
                    $oldAvatar = $current_avatar;
                }

                if ($oldAvatar && strpos($oldAvatar, 'uploads/') === 0) {
                    $oldFullPath = __DIR__ . '/../' . $oldAvatar;
                    if (file_exists($oldFullPath) && is_file($oldFullPath) && realpath($oldFullPath) !== realpath($destination)) {
                        @unlink($oldFullPath);
                    }
                }
            } catch (Exception $ex) {
                // Abaikan jika pembersihan file lama gagal
            }
        } else {
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => 'Gagal memindahkan file foto profil yang diunggah']);
            exit();
        }
    }

    // Simpan ke database: Pastikan tidak membuat artist baru jika record sudah ada
    try {
        // Cek record eksisting di artist_profiles
        $stmtCheck = $pdo->prepare("SELECT * FROM `artist_profiles` WHERE `id` = :id");
        $stmtCheck->execute([':id' => $artist_id]);
        $existing = $stmtCheck->fetch(PDO::FETCH_ASSOC);

        $final_avatar = null;
        if ($new_avatar_path !== null) {
            $final_avatar = $new_avatar_path;
        } elseif ($existing && !empty($existing['avatar'])) {
            $final_avatar = $existing['avatar'];
        } elseif (!empty($current_avatar)) {
            $final_avatar = $current_avatar;
        }

        if ($existing) {
            $sqlUpdate = "UPDATE `artist_profiles` SET
                            `name` = COALESCE(:name, `name`),
                            `role` = COALESCE(:role, `role`),
                            `era` = COALESCE(:era, `era`),
                            `category` = COALESCE(:category, `category`),
                            `avatar` = COALESCE(:avatar, `avatar`),
                            `bio` = COALESCE(:bio, `bio`),
                            `location` = COALESCE(:location, `location`),
                            `is_deleted` = 0,
                            `updated_at` = CURRENT_TIMESTAMP
                          WHERE `id` = :id";
            $stmtUp = $pdo->prepare($sqlUpdate);
            $stmtUp->execute([
                ':name'     => $name,
                ':role'     => $role,
                ':era'      => $era,
                ':category' => $category,
                ':avatar'   => $final_avatar,
                ':bio'      => $bio,
                ':location' => $location,
                ':id'       => $artist_id
            ]);
        } else {
            $sqlInsert = "INSERT INTO `artist_profiles` (`id`, `name`, `role`, `era`, `category`, `avatar`, `bio`, `location`, `is_deleted`)
                          VALUES (:id, :name, :role, :era, :category, :avatar, :bio, :location, 0)";
            $stmtIn = $pdo->prepare($sqlInsert);
            $stmtIn->execute([
                ':id'       => $artist_id,
                ':name'     => $name,
                ':role'     => $role,
                ':era'      => $era,
                ':category' => $category,
                ':avatar'   => $final_avatar,
                ':bio'      => $bio,
                ':location' => $location
            ]);
        }

        echo json_encode([
            'status'  => 'success',
            'message' => 'Profil dan foto seniman berhasil diperbarui',
            'data'    => [
                'id'         => $artist_id,
                'avatar'     => $final_avatar,
                'avatar_new' => $new_avatar_path !== null
            ]
        ]);
        exit();

    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => 'Gagal menyimpan profil seniman: ' . $e->getMessage()]);
        exit();
    }
}

// ========================================================
// 2. GET: AMBIL DAFTAR SENIMAN & KONTRIBUTOR
// ========================================================
try {
    $featuredArtists = [
        ['id'=>'raden-saleh','name'=>'Raden Saleh Syarif Bustaman','role'=>'Pelukis Maestro & Pioneer Seni Rupa Modern Nusantara','era'=>'1850-1880','category'=>'pelukis','avatar'=>'https://images.unsplash.com/photo-1544005313-94ddf0286df2?auto=format&fit=crop&w=400&q=80','bio'=>'Pelopor seni rupa modern Indonesia berkebangsaan Jawa yang memadukan romantisisme Eropa dengan panorama dan satwa liar Nusantara. Karya-karyanya diakui sebagai warisan budaya bernilai tinggi.','location'=>'Semarang & Batavia','photos_keyword'=>'Raden Saleh'],
        ['id'=>'bambang-soetjipto','name'=>'Arsiparis Bambang S. / Bambang Soetjipto','role'=>'Fotografer Dokumenter Lanskap Kota Batavia & Jakarta','era'=>'1970-1985','category'=>'fotografer','avatar'=>'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?auto=format&fit=crop&w=400&q=80','bio'=>'Kurator senior yang mendedikasikan hidupnya merekam dinamika pembangunan Ibukota Jakarta dari era trem listrik hingga munculnya gedung pencakar langit pertama di Thamrin.','location'=>'DKI Jakarta','photos_keyword'=>'Bambang'],
        ['id'=>'rina-kartika','name'=>'Rina Kartika','role'=>'Fotografer Komunitas & Kenangan Remaja 90-an','era'=>'1990-an','category'=>'fotografer','avatar'=>'https://images.unsplash.com/photo-1534528741775-53994a69daeb?auto=format&fit=crop&w=400&q=80','bio'=>'Alumni SMA Negeri 1 Nusantara angkatan 1995 yang rajin mengabadikan momen keseharian sahabat dan warna-warni kehidupan remaja era 90-an.','location'=>'Bandung & Jakarta','photos_keyword'=>'Rina'],
        ['id'=>'ki-suryo','name'=>'Ki Suryo Sasongko','role'=>'Dokumentator Budaya & Pedesaan Tradisional Jawa','era'=>'1960-1975','category'=>'kurator','avatar'=>'https://images.unsplash.com/photo-1500648767791-00dcc994a43e?auto=format&fit=crop&w=400&q=80','bio'=>'Pemerhati budaya Jawa yang merekam kehangatan tradisi gotong royong, kehidupan persawahan lereng Merapi, dan kearifan lokal masyarakat perdesaan.','location'=>'Yogyakarta & Klaten','photos_keyword'=>'Ki Suryo'],
        ['id'=>'soedarsono','name'=>'Ir. Soedarsono','role'=>'Arsiparis Sejarah Transportasi & Lokomotif Uap','era'=>'1950-1965','category'=>'arsiparis','avatar'=>'https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?auto=format&fit=crop&w=400&q=80','bio'=>'Insinyur rel kereta api generasi awal yang mendokumentasikan kejayaan lokomotif uap raksasa Seri D52 dan rute-rute pegunungan bersejarah di Priangan.','location'=>'Ambarawa & Bandung','photos_keyword'=>'Soedarsono'],
        ['id'=>'andra-permana','name'=>'Andra Permana','role'=>'Fotografer Komunitas Y2K & Era Milenium 2000-an','era'=>'2000-an','category'=>'fotografer','avatar'=>'https://images.unsplash.com/photo-1519085360753-af0119f7cbe7?auto=format&fit=crop&w=400&q=80','bio'=>'Penggiat fotografi digital generasi awal yang mengabadikan tren kultur pop Y2K, menjamurnya posko wartel, dan kaset CD musik awal dekade 2000-an.','location'=>'Bandung','photos_keyword'=>'Andra'],
        ['id'=>'maya-indah','name'=>'Maya Indah','role'=>'Seniman Instalasi Kontemporer & Media Baru 2010-an','era'=>'2010-an','category'=>'pelukis','avatar'=>'https://images.unsplash.com/photo-1573496359142-b8d87734a5a2?auto=format&fit=crop&w=400&q=80','bio'=>'Seniman kontemporer yang aktif merespons sejarah melalui perpaduan instalasi pencahayaan lampu vintage dan fotografi digital fine art.','location'=>'Yogyakarta','photos_keyword'=>'Maya Indah']
    ];

    $featuredKeywords = array_map(function($fa) {
        return mb_strtolower(trim($fa['photos_keyword']));
    }, $featuredArtists);

    // Ambil semua custom profile overrides dari tabel artist_profiles
    $customProfiles = [];
    if (isset($pdo) && ($pdo instanceof PDO)) {
        try {
            $stmtProf = $pdo->query("SELECT * FROM `artist_profiles`");
            if ($stmtProf) {
                while ($row = $stmtProf->fetch(PDO::FETCH_ASSOC)) {
                    $customProfiles[$row['id']] = $row;
                }
            }
        } catch (Exception $e) {
            // Tabel mungkin belum dibuat, biarkan kosong
        }
    }

    $artistList = [];

    if (isset($pdo) && ($pdo instanceof PDO)) {
        // LANGKAH 1: Populasi karya untuk seniman featured
        foreach ($featuredArtists as $art) {
            $artId = $art['id'];

            // Lewati jika seniman featured ini telah ditandai dihapus
            if (isset($customProfiles[$artId]) && !empty($customProfiles[$artId]['is_deleted'])) {
                continue;
            }

            // Terapkan override dari artist_profiles jika ada
            if (isset($customProfiles[$artId])) {
                $cp = $customProfiles[$artId];
                if (!empty($cp['name']))     $art['name']     = $cp['name'];
                if (!empty($cp['role']))     $art['role']     = $cp['role'];
                if (!empty($cp['era']))      $art['era']      = $cp['era'];
                if (!empty($cp['category'])) $art['category'] = $cp['category'];
                if (!empty($cp['bio']))      $art['bio']      = $cp['bio'];
                if (!empty($cp['location'])) $art['location'] = $cp['location'];
                if (!empty($cp['avatar']))   $art['avatar']   = $cp['avatar'];
            }

            $term = '%' . $art['photos_keyword'] . '%';
            $stmt = $pdo->prepare('SELECT p.*, a.title AS album_title FROM `photos` p LEFT JOIN `albums` a ON a.id = p.album_id WHERE p.`artist_1` LIKE ? OR p.`artist_2` LIKE ? ORDER BY p.id DESC');
            $stmt->execute([$term, $term]);
            $artworks = $stmt->fetchAll();
            $art['artworks'] = array_map(function($p) {
                return [
                    'id'          => $p['id'],
                    'title'       => $p['title'],
                    'year'        => $p['year'],
                    'location'    => $p['location'],
                    'medium'      => $p['medium'] ?? 'Foto Kenangan',
                    'src'         => $p['image_src'],
                    'thumb'       => $p['thumb_src'] ?: $p['image_src'],
                    'album_title' => $p['album_title'] ?? 'Galeri Kenangan'
                ];
            }, $artworks);
            $art['artworks_count'] = count($art['artworks']);
            $artistList[] = $art;
        }

        // LANGKAH 2: Deteksi artist BARU secara dinamis dari tabel photos
        $stmtDyn = $pdo->query(
            "SELECT DISTINCT `artist_1` AS n FROM `photos` WHERE `artist_1` IS NOT NULL AND TRIM(`artist_1`) != ''
             UNION
             SELECT DISTINCT `artist_2` FROM `photos` WHERE `artist_2` IS NOT NULL AND TRIM(`artist_2`) != ''"
        );
        $dynamicNames = $stmtDyn ? $stmtDyn->fetchAll(PDO::FETCH_COLUMN) : [];

        $defaultAvatars = [
            'https://images.unsplash.com/photo-1438761681033-6461ffad8d80?auto=format&fit=crop&w=400&q=80',
            'https://images.unsplash.com/photo-1521119989659-a83eee488004?auto=format&fit=crop&w=400&q=80',
            'https://images.unsplash.com/photo-1517841905240-472988babdf9?auto=format&fit=crop&w=400&q=80',
            'https://images.unsplash.com/photo-1499952127939-9bbf5af6c51c?auto=format&fit=crop&w=400&q=80',
            'https://images.unsplash.com/photo-1527980965255-d3b416303d12?auto=format&fit=crop&w=400&q=80',
        ];

        $ai = 0;
        foreach ($dynamicNames as $dynName) {
            $dynName  = trim($dynName);
            if ($dynName === '') continue;
            $dynLower = mb_strtolower($dynName);

            $isFeatured = false;
            foreach ($featuredKeywords as $fk) {
                if ($fk !== '' && (strpos($dynLower, $fk) !== false || strpos($fk, $dynLower) !== false)) {
                    $isFeatured = true;
                    break;
                }
            }
            if ($isFeatured) continue;

            $dynId = 'dynamic-' . preg_replace('/[^a-z0-9]+/', '-', $dynLower);

            // Lewati jika seniman dinamis ini telah ditandai dihapus
            if (isset($customProfiles[$dynId]) && !empty($customProfiles[$dynId]['is_deleted'])) {
                continue;
            }

            $term    = '%' . $dynName . '%';
            $stmtArt = $pdo->prepare('SELECT p.*, a.title AS album_title FROM `photos` p LEFT JOIN `albums` a ON a.id = p.album_id WHERE p.`artist_1` LIKE ? OR p.`artist_2` LIKE ? ORDER BY p.id DESC');
            $stmtArt->execute([$term, $term]);
            $dynArtworks = $stmtArt->fetchAll();

            $fp = !empty($dynArtworks) ? $dynArtworks[0] : null;
            $dynEra      = ($fp && !empty($fp['year']))     ? $fp['year']     : 'Kontemporer';
            $dynLocation = ($fp && !empty($fp['location'])) ? $fp['location'] : 'Nusantara';

            $dynCategory = 'fotografer';
            if ($fp) {
                $isA1 = !empty($fp['artist_1']) && mb_strtolower(trim($fp['artist_1'])) === $dynLower;
                if ($isA1 && !empty($fp['artist_1_category'])) $dynCategory = $fp['artist_1_category'];
                elseif (!empty($fp['artist_2_category']))       $dynCategory = $fp['artist_2_category'];
            }

            $dynBio = 'Kontributor karya seni dan foto kenangan untuk arsip Jejak Waktu. Setiap karya yang diunggah memberi warna baru pada lembaran sejarah visual Nusantara.';
            if ($fp) {
                $isA1 = !empty($fp['artist_1']) && mb_strtolower(trim($fp['artist_1'])) === $dynLower;
                if ($isA1 && !empty($fp['artist_1_bio'])) $dynBio = $fp['artist_1_bio'];
                elseif (!empty($fp['artist_2_bio']))       $dynBio = $fp['artist_2_bio'];
            }

            $dynAvatar = $defaultAvatars[$ai % count($defaultAvatars)];

            // Terapkan override dari artist_profiles jika ada
            if (isset($customProfiles[$dynId])) {
                $cp = $customProfiles[$dynId];
                if (!empty($cp['name']))     $dynName     = $cp['name'];
                if (!empty($cp['role']))     $dynRole     = $cp['role'];
                if (!empty($cp['era']))      $dynEra      = $cp['era'];
                if (!empty($cp['category'])) $dynCategory = $cp['category'];
                if (!empty($cp['bio']))      $dynBio      = $cp['bio'];
                if (!empty($cp['location'])) $dynLocation = $cp['location'];
                if (!empty($cp['avatar']))   $dynAvatar   = $cp['avatar'];
            }

            $artistList[] = [
                'id'             => $dynId,
                'name'           => $dynName,
                'role'           => $dynRole ?? 'Kontributor Karya Seni / Fotografer',
                'era'            => $dynEra,
                'category'       => $dynCategory,
                'avatar'         => $dynAvatar,
                'bio'            => $dynBio,
                'location'       => $dynLocation,
                'photos_keyword' => $dynName,
                'is_dynamic'     => true,
                'artworks'       => array_map(function($p) {
                    return [
                        'id'          => $p['id'],
                        'title'       => $p['title'],
                        'year'        => $p['year'],
                        'location'    => $p['location'],
                        'medium'      => $p['medium'] ?? 'Foto Kenangan',
                        'src'         => $p['image_src'],
                        'thumb'       => $p['thumb_src'] ?: $p['image_src'],
                        'album_title' => $p['album_title'] ?? 'Galeri Kenangan'
                    ];
                }, $dynArtworks),
                'artworks_count' => count($dynArtworks)
            ];
            $ai++;
        }

        // LANGKAH 3: Sertakan profil seniman lain dari tabel artist_profiles yang belum masuk
        $includedIds = array_column($artistList, 'id');
        foreach ($customProfiles as $profId => $cp) {
            if (in_array($profId, $includedIds) || !empty($cp['is_deleted'])) {
                continue;
            }
            $artistList[] = [
                'id'             => $profId,
                'name'           => !empty($cp['name']) ? $cp['name'] : $profId,
                'role'           => !empty($cp['role']) ? $cp['role'] : 'Kontributor Arsip Seni',
                'era'            => !empty($cp['era']) ? $cp['era'] : 'Kontemporer',
                'category'       => !empty($cp['category']) ? $cp['category'] : 'pelukis',
                'avatar'         => !empty($cp['avatar']) ? $cp['avatar'] : 'https://images.unsplash.com/photo-1544005313-94ddf0286df2?auto=format&fit=crop&w=400&q=80',
                'bio'            => !empty($cp['bio']) ? $cp['bio'] : '',
                'location'       => !empty($cp['location']) ? $cp['location'] : 'Nusantara',
                'photos_keyword' => !empty($cp['name']) ? $cp['name'] : $profId,
                'artworks'       => [],
                'artworks_count' => 0
            ];
        }
    } else {
        $artistList = $featuredArtists;
    }

    echo json_encode(['status' => 'success', 'data' => $artistList]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Gagal mengambil data seniman: ' . $e->getMessage()]);
}

