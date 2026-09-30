<?php
/**
 * Halaman Utama Terproteksi "Jejak Waktu"
 * Autentikasi & Otorisasi RBAC (Admin & User)
 */

require_once __DIR__ . '/api/session.php';
require_once __DIR__ . '/api/db.php';

// Proteksi Server-Side: Wajib login sebelum konten disajikan
require_auth();

$currentUser = get_current_user_session();

// Tangkap parameter pencarian dari GET
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

// Query data album dan foto dari database MySQL dengan filter WHERE jika search diisi
$albums = [];
$matchedPhotos = [];

if (isset($pdo) && ($pdo instanceof PDO)) {
    try {
        if ($search !== '') {
            $term = '%' . $search . '%';

            // 1. Query tabel albums (title, subtitle, description, era, location)
            $stmtAlbums = $pdo->prepare("SELECT * FROM `albums` 
                    WHERE `title` LIKE ? 
                       OR `subtitle` LIKE ? 
                       OR `description` LIKE ? 
                       OR `era` LIKE ? 
                       OR `location` LIKE ? 
                    ORDER BY `created_at` ASC");
            $stmtAlbums->execute([$term, $term, $term, $term, $term]);
            $rawAlbums = $stmtAlbums->fetchAll();

            // 2. Query tabel photos (title, caption, location, note, artist_1, artist_2, medium, year)
            $stmtPhotos = $pdo->prepare("SELECT p.*, a.title AS album_title, a.era AS album_era, a.decade AS album_decade, a.cover_color, a.cover_image 
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
            $stmtPhotos->execute([$term, $term, $term, $term, $term, $term, $term, $term]);
            $matchedPhotos = $stmtPhotos->fetchAll();
        } else {
            // Default: ambil semua album jika search kosong
            $stmtAlbums = $pdo->query("SELECT * FROM `albums` ORDER BY `created_at` ASC");
            $rawAlbums = $stmtAlbums->fetchAll();
        }

        foreach ($rawAlbums as $alb) {
            $stmtP = $pdo->prepare("SELECT * FROM `photos` WHERE `album_id` = :aid ORDER BY `id` DESC");
            $stmtP->execute([':aid' => $alb['id']]);
            $photos = $stmtP->fetchAll();

            $albums[] = [
                'id' => $alb['id'],
                'title' => $alb['title'],
                'subtitle' => $alb['subtitle'],
                'era' => $alb['era'],
                'decade' => $alb['decade'],
                'category' => $alb['category'],
                'coverImage' => $alb['cover_image'],
                'coverColor' => $alb['cover_color'],
                'accentColor' => $alb['accent_color'],
                'description' => $alb['description'],
                'location' => $alb['location'],
                'curator' => $alb['curator'],
                'photos' => array_map(function($p) {
                    return [
                        'id' => $p['id'],
                        'title' => $p['title'],
                        'artist_1' => $p['artist_1'] ?? null,
                        'artist_2' => $p['artist_2'] ?? null,
                        'year' => $p['year'],
                        'date' => $p['date'],
                        'location' => $p['location'],
                        'medium' => $p['medium'] ?? null,
                        'dimensions' => $p['dimensions'] ?? null,
                        'copyright' => $p['copyright'] ?? null,
                        'caption' => $p['caption'],
                        'src' => $p['image_src'],
                        'thumb' => $p['thumb_src'] ?: $p['image_src'],
                        'tilt' => $p['tilt'],
                        'tape' => $p['tape'],
                        'note' => $p['note']
                    ];
                }, $photos)
            ];
        }
    } catch (PDOException $e) {
        $albums = [];
        $matchedPhotos = [];
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Jejak Waktu — Galeri Album Foto & Kenangan Sejarah</title>
    <meta name="description" content="Jejak Waktu: Mengabadikan Kenangan, Merawat Sejarah. Galeri foto vintage dengan estetika album jadul fisik.">
    <link rel="icon" href="data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 100 100%22><text y=%22.9em%22 font-size=%2290%22>📜</text></svg>">
    
    <!-- Google Fonts Preconnect & Stylesheet -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Caveat:wght@500;700&family=Cormorant+Garamond:ital,wght@0,400;0,600;0,700;1,400&family=Lora:ital,wght@0,400;0,600;1,400&family=Playfair+Display:ital,wght@0,600;0,800;1,400;1,700&family=Special+Elite&display=swap">

    <!-- Stylesheets -->
    <link rel="stylesheet" href="css/vintage.css">
    <link rel="stylesheet" href="css/animations.css">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/events.css">
    <link rel="stylesheet" href="css/feedback.css">

    <!-- Injeksi Session Pengguna & Data Server ke Frontend -->
    <script>
        window.CURRENT_USER = <?= json_encode($currentUser) ?>;
        window.SERVER_ALBUMS = <?= json_encode($albums) ?>;
        window.SEARCH_QUERY = <?= json_encode($search) ?>;
        window.MATCHED_PHOTOS = <?= json_encode($matchedPhotos) ?>;
    </script>
</head>
<body class="vintage-paper-bg">

    <div class="main-album-wrapper">

        <?php require_once __DIR__ . '/includes/navbar.php'; ?>

        <!-- Layout Body: Left Sidebar + Main Binder Content -->
        <div class="album-layout-body">
            <?php require_once __DIR__ . '/includes/sidebar.php'; ?>

            <!-- ====================================================
                 MAIN SKEUOMORPHIC LEATHER BINDER & AGED PAPER
                 ==================================================== -->
            <main class="album-book-outer">
            <!-- Brass Corners -->
            <div class="brass-corner corner-top-left"></div>
            <div class="brass-corner corner-top-right"></div>
            <div class="brass-corner corner-bottom-left"></div>
            <div class="brass-corner corner-bottom-right"></div>
            <div class="album-stitching"></div>

            <!-- Inner Paper Sheet -->
            <div class="album-paper-sheet">
                <!-- Ring Holes on Left Edge -->
                <div class="paper-ring-holes" aria-hidden="true">
                    <div class="ring-hole"></div>
                    <div class="ring-hole"></div>
                    <div class="ring-hole"></div>
                    <div class="ring-hole"></div>
                    <div class="ring-hole"></div>
                </div>

                <?php if ($currentUser['role'] === 'user'): ?>
                <!-- Banner Akses Read-Only untuk Role User -->
                <div class="read-only-banner" id="read-only-banner">
                    <span class="banner-icon">📖</span>
                    <div>
                        <strong>Akses Pembaca (Hanya Baca):</strong> Anda sedang melihat arsip sebagai pengunjung. Anda dapat menjelajahi seluruh koleksi album & memperbesar foto kenangan. Hak modifikasi data (tambah & hapus) hanya dapat diakses oleh akun Kurator (Admin).
                    </div>
                </div>
                <?php endif; ?>

                <!-- ----------------------------------------------------
                     TAB 1: BERANDA (LANDING PAGE)
                     ---------------------------------------------------- -->
                <section id="tab-beranda" class="tab-content active" aria-labelledby="hero-title">
                    <!-- Hero Section -->
                    <div class="hero-vintage-container">
                        <div class="hero-stamp-badge">
                            <span class="postage-stamp">ARSIP DIGITAL NUSANTARA &bull; SEJAK 1950</span>
                        </div>

                        <h1 id="hero-title" class="hero-main-title">Jejak Waktu</h1>
                        <p class="hero-tagline">&mdash; Mengabadikan Kenangan, Merawat Sejarah &mdash;</p>
                        <p class="hero-subtext">
                            "Setiap helai foto usang menyimpan detak masa lalu yang tak pernah pudar oleh sang kala. Buka lembarannya, dengarkan kisahnya."
                        </p>
                        <!-- Form Pencarian Album & Kenangan (GET Method) -->
                        <form action="index.php" method="GET" class="hero-search-wrapper" id="hero-search-form" role="search">
                            <input 
                                type="text" 
                                name="search" 
                                id="hero-search-input" 
                                value="<?= htmlspecialchars($search, ENT_QUOTES, 'UTF-8') ?>" 
                                placeholder="Cari kenangan, album, atau peristiwa..." 
                                class="vintage-input hero-search-input" 
                                autocomplete="off"
                            />
                            <button type="submit" class="hero-search-btn" id="hero-search-btn" title="Cari Album & Kenangan" aria-label="Cari">
                                <svg class="hero-search-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                            </button>
                        </form>
                        <?php if (!empty($search)): ?>
                        <div class="hero-search-status" style="margin-top: -12px; margin-bottom: 22px; font-family: var(--font-typewriter); font-size: 0.88rem; color: var(--color-ink-sepia);">
                            <span>Hasil pencarian global untuk: "<strong><?= htmlspecialchars($search) ?></strong>" &bull; <em><?= count($albums) ?> album</em> &bull; <em><?= count($matchedPhotos) ?> foto kenangan</em></span>
                            <a href="index.php" class="hero-search-reset" style="margin-left: 10px; color: var(--color-stamp-red); text-decoration: underline; font-weight: bold;">&times; Hapus Pencarian</a>
                        </div>
                        <?php endif; ?>

                        <!-- Quick Decade Filter Ribbons -->
                        <div class="era-filter-ribbon">
                            <span style="font-family: var(--font-typewriter); font-size: 0.85rem; color: var(--color-ink-sepia); align-self: center; margin-right: 6px;">Jelajahi Era:</span>
                            <button class="era-chip-btn active" data-decade="all">Semua Era</button>
                            <button class="era-chip-btn" data-decade="1950s">1950-an</button>
                            <button class="era-chip-btn" data-decade="1960s">1960-an</button>
                            <button class="era-chip-btn" data-decade="1970s">1970-an</button>
                            <button class="era-chip-btn" data-decade="1980s">1980-an</button>
                            <button class="era-chip-btn" data-decade="1990s">1990-an</button>
                            <button class="era-chip-btn" data-decade="2000s">2000-an</button>
                            <button class="era-chip-btn" data-decade="2010s">2010-an</button>
                        </div>
                    </div>

                    <?php if (!empty($search)): ?>
                    <!-- ====================================================
                         HASIL PENCARIAN GLOBAL (ALBUM & FOTO)
                         ==================================================== -->
                    <div class="global-search-container" id="global-search-results">

                        <?php if (empty($albums) && empty($matchedPhotos)): ?>
                            <!-- Tidak ada hasil di kedua tabel -->
                            <div class="search-empty-state" style="text-align: center; padding: 50px 20px; background: rgba(255, 255, 255, 0.45); border: 1px dashed #cbb898; border-radius: 6px; margin: 30px 0;">
                                <div style="font-size: 2.8rem; margin-bottom: 12px;">🔍</div>
                                <h3 style="font-family: var(--font-heading); font-size: 1.8rem; color: var(--color-leather-dark); margin-bottom: 8px;">Tidak ada hasil pencarian untuk "<?= htmlspecialchars($search) ?>"</h3>
                                <p style="font-family: var(--font-typewriter); color: var(--color-ink-sepia); font-size: 0.95rem; max-width: 550px; margin: 0 auto 20px auto;">
                                    Tidak ditemukan buku album maupun foto kenangan yang cocok dengan kata kunci tersebut. Coba gunakan kata kunci kota, tahun, peristiwa, atau nama seniman lain.
                                </p>
                                <a href="index.php" class="era-chip-btn" style="display: inline-block; text-decoration: none; padding: 8px 20px; font-weight: bold;">
                                    &larr; Tampilkan Semua Koleksi
                                </a>
                            </div>

                        <?php else: ?>

                            <!-- SEKSI 1: ALBUM YANG DITEMUKAN -->
                            <div class="search-results-section" style="margin-top: 35px; margin-bottom: 45px;">
                                <div class="section-vintage-title">
                                    <div style="display: flex; align-items: baseline; gap: 10px;">
                                        <h2>Album yang Ditemukan</h2>
                                        <span style="font-family: var(--font-typewriter); font-size: 0.9rem; color: var(--color-stamp-red); font-weight: bold;">(<?= count($albums) ?>)</span>
                                    </div>
                                    <div class="title-line"></div>
                                    <span class="postage-stamp" style="font-size: 0.65rem;">ARSIP BUKU</span>
                                </div>

                                <?php if (!empty($albums)): ?>
                                    <div class="albums-grid-layout" id="search-albums-grid">
                                        <?php foreach ($albums as $alb): ?>
                                            <div class="album-card-wrapper" data-album-id="<?= htmlspecialchars($alb['id']) ?>">
                                                <div class="album-book-card" style="background-color: <?= htmlspecialchars($alb['coverColor'] ?: '#3e261a') ?>;">
                                                    <div class="bookmark-ribbon"></div>
                                                    <div class="album-frame-inlay">
                                                        <div class="album-meta-header">
                                                            <span class="album-decade-tag">ERA <?= htmlspecialchars(str_replace('s', '-an', $alb['decade'] ?: 'KLASIK')) ?></span>
                                                            <span class="album-photo-count"><?= count($alb['photos']) ?> Foto</span>
                                                        </div>

                                                        <img 
                                                            src="<?= htmlspecialchars($alb['coverImage']) ?>" 
                                                            alt="<?= htmlspecialchars($alb['title']) ?>" 
                                                            class="album-cover-img" 
                                                            loading="lazy" 
                                                            onerror="this.src='https://images.unsplash.com/photo-1578632767115-351597cf2477?auto=format&fit=crop&w=800&q=80'"
                                                        />

                                                        <div class="album-title-group">
                                                            <h3 class="gold-foil-title album-book-title"><?= htmlspecialchars($alb['title']) ?></h3>
                                                            <p class="album-book-subtitle"><?= htmlspecialchars($alb['subtitle'] ?? '') ?></p>
                                                        </div>

                                                        <div class="album-open-action">
                                                            <span style="font-family: var(--font-typewriter); font-size: 0.75rem; color: #cfb997;">
                                                                <?= htmlspecialchars($alb['location'] ?: 'Indonesia') ?>
                                                            </span>
                                                            <button type="button" class="album-open-btn" aria-label="Buka album <?= htmlspecialchars($alb['title']) ?>">
                                                                Buka Album &rarr;
                                                            </button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php else: ?>
                                    <div style="padding: 20px; background: rgba(255,255,255,0.3); border: 1px dashed #cbba9d; border-radius: 4px; font-family: var(--font-typewriter); color: var(--color-ink-faded); text-align: center;">
                                        <p>Tidak ada buku album yang cocok dengan kata kunci ini.</p>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <!-- SEKSI 2: FOTO YANG DITEMUKAN -->
                            <div class="search-results-section" style="margin-top: 40px; margin-bottom: 30px;">
                                <div class="section-vintage-title">
                                    <div style="display: flex; align-items: baseline; gap: 10px;">
                                        <h2>Foto yang Ditemukan</h2>
                                        <span style="font-family: var(--font-typewriter); font-size: 0.9rem; color: var(--color-stamp-red); font-weight: bold;">(<?= count($matchedPhotos) ?>)</span>
                                    </div>
                                    <div class="title-line"></div>
                                    <span class="postage-stamp" style="font-size: 0.65rem;">FOTO POLAROID</span>
                                </div>

                                <?php if (!empty($matchedPhotos)): ?>
                                    <div class="polaroid-gallery-grid" id="search-photos-grid">
                                        <?php foreach ($matchedPhotos as $pIdx => $p): 
                                            $tilt = ($pIdx % 2 === 0) ? '-2deg' : '2deg';
                                            $tapeClass = ($p['tape'] ?? 'top-right') === 'top-left' ? 'tape-top-left' : (($p['tape'] ?? '') === 'both' ? 'tape-center-top' : 'tape-top-right');
                                            $imgSrc = !empty($p['thumb_src']) ? $p['thumb_src'] : $p['image_src'];
                                        ?>
                                            <div class="polaroid-card polaroid-frame" style="transform: rotate(<?= $tilt ?>); cursor: pointer;" data-album-id="<?= htmlspecialchars($p['album_id']) ?>" data-photo-id="<?= htmlspecialchars($p['id']) ?>" data-index="<?= $pIdx ?>" title="Klik untuk memperbesar foto kenangan ini">
                                                <div class="washi-tape <?= $tapeClass ?>"></div>
                                                
                                                <div class="photo-corner corner-tl"></div>
                                                <div class="photo-corner corner-tr"></div>
                                                <div class="photo-corner corner-bl"></div>
                                                <div class="photo-corner corner-br"></div>

                                                <div class="polaroid-img-box">
                                                    <img 
                                                        src="<?= htmlspecialchars($imgSrc) ?>" 
                                                        alt="<?= htmlspecialchars($p['title']) ?>" 
                                                        loading="lazy" 
                                                        class="scalloped-edge"
                                                        onerror="this.src='https://images.unsplash.com/photo-1529156069898-49953e39b3ac?auto=format&fit=crop&w=800&q=80'"
                                                    />
                                                </div>

                                                <div class="polaroid-caption">
                                                    <h4><?= htmlspecialchars($p['title']) ?></h4>
                                                    <?php if (!empty($p['artist_1'])): ?>
                                                        <p class="polaroid-artist-subline" style="font-size: 0.75rem; color: var(--color-ink-faded); margin-top: 2px;">
                                                            Oleh: <?= htmlspecialchars($p['artist_1'] . (!empty($p['artist_2']) ? ' & ' . $p['artist_2'] : '')) ?>
                                                        </p>
                                                    <?php endif; ?>
                                                    <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 6px; font-family: var(--font-typewriter); font-size: 0.72rem;">
                                                        <span class="year-tag"><?= htmlspecialchars(!empty($p['year']) ? 'Thn ' . $p['year'] : ($p['date'] ?? 'Kenangan')) ?></span>
                                                        <?php if (!empty($p['album_title'])): ?>
                                                            <span style="color: var(--color-ink-sepia); max-width: 140px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;" title="Dari Album: <?= htmlspecialchars($p['album_title']) ?>">
                                                                📖 <?= htmlspecialchars($p['album_title']) ?>
                                                            </span>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php else: ?>
                                    <div style="padding: 20px; background: rgba(255,255,255,0.3); border: 1px dashed #cbba9d; border-radius: 4px; font-family: var(--font-typewriter); color: var(--color-ink-faded); text-align: center;">
                                        <p>Tidak ada foto kenangan yang cocok dengan kata kunci ini.</p>
                                    </div>
                                <?php endif; ?>
                            </div>

                        <?php endif; ?>

                    </div>

                    <?php else: ?>
                    <!-- ====================================================
                         TAMPILAN DEFAULT (SAAT SEARCH KOSONG)
                         ==================================================== -->
                    <!-- 3D Open Double-Page Photo Album Spread -->
                    <div class="hero-open-album">
                        <div class="album-center-fold" aria-hidden="true"></div>
                        <div class="open-spread-grid">
                            <!-- Left Page Spread -->
                            <div class="spread-page">
                                <div class="spread-diary-box">
                                    <h4>Lembaran Terpilih: Jakarta 1970-an</h4>
                                    <p>Potret ketika jalanan ibukota masih dihiasi pepohonan rindang dan kendaraan antik.</p>
                                </div>
                                <div id="hero-spread-left">
                                    <!-- Dynamic Left Polaroid -->
                                </div>
                            </div>

                            <!-- Right Page Spread -->
                            <div class="spread-page">
                                <div class="spread-diary-box">
                                    <h4>Lembaran Terpilih: Sahabat Sekolah '90</h4>
                                    <p>Tawa tulus di bangku kelas sebelum era ponsel pintar dan media digital.</p>
                                </div>
                                <div id="hero-spread-right">
                                    <!-- Dynamic Right Polaroid -->
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Latest Album Snippet Grid -->
                    <div style="margin-top: 50px;">
                        <div class="section-vintage-title">
                            <h2>Buku Album Terbaru</h2>
                            <div class="title-line"></div>
                            <span class="postage-stamp" style="font-size: 0.65rem;">EDISI TERAWAT</span>
                        </div>
                        <div id="home-latest-albums" class="albums-grid-layout">
                            <?php if (!empty($albums)): ?>
                                <?php foreach (array_slice($albums, 0, 4) as $alb): ?>
                                    <div class="album-card-wrapper" data-album-id="<?= htmlspecialchars($alb['id']) ?>">
                                        <div class="album-book-card" style="background-color: <?= htmlspecialchars($alb['coverColor'] ?: '#3e261a') ?>;">
                                            <div class="bookmark-ribbon"></div>
                                            <div class="album-frame-inlay">
                                                <div class="album-meta-header">
                                                    <span class="album-decade-tag">ERA <?= htmlspecialchars(str_replace('s', '-an', $alb['decade'] ?: 'KLASIK')) ?></span>
                                                    <span class="album-photo-count"><?= count($alb['photos']) ?> Foto</span>
                                                </div>

                                                <img 
                                                    src="<?= htmlspecialchars($alb['coverImage']) ?>" 
                                                    alt="<?= htmlspecialchars($alb['title']) ?>" 
                                                    class="album-cover-img" 
                                                    loading="lazy" 
                                                    onerror="this.src='https://images.unsplash.com/photo-1578632767115-351597cf2477?auto=format&fit=crop&w=800&q=80'"
                                                />

                                                <div class="album-title-group">
                                                    <h3 class="gold-foil-title album-book-title"><?= htmlspecialchars($alb['title']) ?></h3>
                                                    <p class="album-book-subtitle"><?= htmlspecialchars($alb['subtitle'] ?? '') ?></p>
                                                </div>

                                                <div class="album-open-action">
                                                    <span style="font-family: var(--font-typewriter); font-size: 0.75rem; color: #cfb997;">
                                                        <?= htmlspecialchars($alb['location'] ?: 'Indonesia') ?>
                                                    </span>
                                                    <button type="button" class="album-open-btn" aria-label="Buka album <?= htmlspecialchars($alb['title']) ?>">
                                                        Buka Album &rarr;
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endif; ?>
                </section>

                <!-- ----------------------------------------------------
                     TAB 2: GALERI KOLEKSI ALBUM
                     ---------------------------------------------------- -->
                <section id="tab-galeri" class="tab-content" aria-labelledby="gallery-title">
                    <div class="section-vintage-title">
                        <div>
                            <h2 id="gallery-title">Rak Arsip Album</h2>
                            <p style="font-family: var(--font-typewriter); font-size: 0.85rem; color: var(--color-ink-faded); margin-top: 4px;">
                                Pilih salah satu buku album untuk membuka foto-foto kenangan di dalamnya
                            </p>
                        </div>
                        <div class="title-line"></div>
                    </div>

                    <!-- Read-Only Banner Notice for User Role -->
                    <div id="read-only-banner" class="read-only-banner" style="display: none;">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="16" x2="12" y2="12"></line><line x1="12" y1="8" x2="12.01" y2="8"></line></svg>
                        <span><strong>Mode Pembaca:</strong> Anda memiliki hak akses baca arsip. Pembuatan album, penempelan, atau penghapusan foto hanya dapat dilakukan oleh akun Admin.</span>
                    </div>

                    <!-- Filter & Search Toolbar -->
                    <div style="display: flex; flex-wrap: wrap; gap: 16px; justify-content: space-between; align-items: center; margin-bottom: 30px; background: #ebdcc4; padding: 14px 18px; border-radius: 4px; border: 1px solid #cbb898;">
                        <!-- Search Box -->
                        <div style="display: flex; align-items: center; gap: 8px; flex: 1; min-width: 240px;">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
                            <input 
                                type="text" 
                                id="album-search-input" 
                                placeholder="Cari album, peristiwa, atau kota..." 
                                class="vintage-input"
                                style="background: #fff; padding: 6px 12px; font-size: 0.9rem;"
                            />
                        </div>

                        <!-- Decade Filters -->
                        <div class="era-filter-ribbon" style="margin: 0;">
                            <button class="era-chip-btn active" data-decade="all">Semua Era</button>
                            <button class="era-chip-btn" data-decade="1950s">1950</button>
                            <button class="era-chip-btn" data-decade="1960s">1960</button>
                            <button class="era-chip-btn" data-decade="1970s">1970</button>
                            <button class="era-chip-btn" data-decade="1980s">1980</button>
                            <button class="era-chip-btn" data-decade="1990s">1990</button>
                            <button class="era-chip-btn" data-decade="2000s">2000</button>
                            <button class="era-chip-btn" data-decade="2010s">2010</button>
                        </div>
                    </div>

                    <!-- All Albums Grid -->
                    <div id="gallery-albums-grid" class="albums-grid-layout">
                        <!-- Dynamic Albums Content -->
                    </div>
                </section>

                <!-- ----------------------------------------------------
                     TAB 3: DETAIL ALBUM (POLAROID GALLERY)
                     ---------------------------------------------------- -->
                <section id="tab-album-detail" class="tab-content" aria-labelledby="detail-album-title">
                    <div class="album-detail-header">
                        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px;">
                            <button id="btn-back-albums" class="back-to-albums-btn">
                                &larr; Kembali ke Rak Album
                            </button>

                            <!-- Delete Album Button (Admin Only) -->
                            <button id="btn-delete-current-album" class="btn-delete-album admin-action-btn" title="Hapus Buku Album Ini Beserta Seluruh Fotonya">
                                🗑️ Hapus Buku Album
                            </button>
                        </div>

                        <div class="detail-title-row">
                            <div>
                                <h2 id="detail-album-title" class="detail-main-title">Judul Album</h2>
                                <p id="detail-album-subtitle" style="font-family: var(--font-body); font-style: italic; font-size: 1.15rem; color: var(--color-ink-sepia); margin-top: 4px;"></p>
                            </div>
                            <span id="detail-album-era" class="detail-era-badge">Era: 1970-1985</span>
                        </div>

                        <p id="detail-album-desc" class="detail-description"></p>

                        <div class="detail-meta-info">
                            <div><strong>Lokasi:</strong> <span id="detail-album-loc">Jakarta</span></div>
                            <div><strong>Kurator:</strong> <span id="detail-album-curator">Arsiparis</span></div>
                            <div><strong>Total:</strong> <span id="detail-album-count">6 Foto</span></div>
                        </div>
                    </div>

                    <!-- Polaroid Photos Grid -->
                    <div id="album-photos-grid" class="polaroid-gallery-grid">
                        <!-- Dynamic Polaroid Cards -->
                    </div>
                </section>

                <!-- ----------------------------------------------------
                     TAB 4: TENTANG KAMI (VINTAGE LETTER / MANUSCRIPT)
                     ---------------------------------------------------- -->
                <section id="tab-tentang" class="tab-content" aria-labelledby="about-title">
                    <div class="vintage-letter-wrapper">
                        <div class="letter-watermark">JEJAK WAKTU</div>

                        <div class="letter-top-row">
                            <div class="letter-sender">
                                <h3 id="about-title">Warkat Pusaka Jejak Waktu</h3>
                                <p>Arsip Pelestarian Memori & Fotografi Bersejarah</p>
                                <p style="margin-top: 2px;">Nomor Dokumen: JW/ARSIP/1950-2026/01</p>
                            </div>
                            <div class="wax-seal" title="Segel Asli Lilin Jejak Waktu">
                                JW
                            </div>
                        </div>

                        <div class="letter-body">
                            <p>
                                <strong>Salam Takzim untuk Sahabat Pemerhati Masa Lalu,</strong>
                            </p>
                            <p>
                                Website <em>"Jejak Waktu"</em> lahir dari kecintaan mendalam terhadap denyut memori yang pernah hidup di sudut-sudut Nusantara. Di tengah laju zaman serba cepat dan format digital yang fana, kami percaya bahwa setiap helai foto cetak jadul memiliki jiwa—tekstur kertasnya yang menguning, aroma kamar gelap, hingga goresan tulisan tangan di balik bingkai polaroid.
                            </p>
                            <p>
                                Visi kami adalah merawat dan mendigitalkan serpihan-serpihan nostalgia agar tidak terkubur oleh debu waktu. Mulai dari riuhnya pasar malam zaman dulu, senyum polos sahabat berseragam putih abu-abu, hingga deru kereta api uap yang membelah pegunungan Jawa.
                            </p>

                            <div class="letter-core-values">
                                <div class="core-val-card">
                                    <h4>1. Merawat Keaslian (Authenticity)</h4>
                                    <p>Menghadirkan kembali nuansa fisik album foto jadul secara skeuomorphic dan penuh penghormatan pada nilai historis.</p>
                                </div>
                                <div class="core-val-card">
                                    <h4>2. Gotong Royong Kenangan</h4>
                                    <p>Memberi ruang bagi setiap kurator arsip untuk menempelkan foto keluarga mereka dan membagikan ceritanya kepada generasi penerus.</p>
                                </div>
                            </div>

                            <p>
                                Semoga setiap lembaran album yang Anda buka di sini dapat membangkitkan kehangatan, mengobati rindu, serta menjadi jembatan kebijaksanaan antara masa lalu, kini, dan esok hari.
                            </p>
                        </div>

                        <div class="letter-footer-sign">
                            <div>
                                <span class="postage-stamp">TERCATAT RESMI &bull; NUSANTARA</span>
                            </div>
                            <div class="signature-box">
                                <div class="handwritten-signature">Jejak Waktu Curator</div>
                                <div class="sign-role">Dewan Kurator & Kolektor Arsip</div>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- ----------------------------------------------------
                     TAB 5: JADWAL EVENT SENI (EVENTS CALENDAR & BOOKING)
                     ---------------------------------------------------- -->
                <section id="tab-events" class="tab-content" aria-labelledby="events-title">
                    <!-- Hero Banner -->
                    <div class="events-hero-banner">
                        <div class="events-hero-overlay"></div>
                        <div class="events-hero-content">
                            <span class="events-hero-tag postage-stamp">PROGRAM PAMERAN & PAGELARAN SENI</span>
                            <h1 id="events-title" class="events-hero-title">Jadwal Event Seni</h1>
                            <p class="events-hero-subtitle">Temukan, daftarkan diri, dan nikmati pameran seni, fotografi, dan budaya pilihan terbaik Jejak Waktu</p>
                            <div class="events-filter-pills" id="events-filter-pills">
                                <button class="ev-pill active" data-filter="all" id="pill-all">Semua</button>
                                <button class="ev-pill" data-filter="upcoming" id="pill-upcoming">🗓 Akan Datang</button>
                                <button class="ev-pill" data-filter="ongoing" id="pill-ongoing">🟢 Sedang Berjalan</button>
                                <button class="ev-pill" data-filter="completed" id="pill-completed">✅ Selesai</button>
                            </div>
                        </div>
                    </div>

                    <!-- Main Content Wrapper -->
                    <div class="events-main-wrapper">
                        <?php if ($currentUser['role'] === 'admin'): ?>
                        <!-- Admin Banner -->
                        <div class="events-admin-bar" id="admin-anchor">
                            <div class="admin-bar-left">
                                <span>👑 Mode Admin Aktif</span>
                                <span class="admin-bar-desc">Anda dapat menambah, mengedit, dan menghapus event dari panel ini</span>
                            </div>
                            <button class="ev-btn-primary" id="btn-add-event" onclick="openEventModal()">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                                Tambah Event Baru
                            </button>
                        </div>
                        <?php endif; ?>

                        <!-- Loading State -->
                        <div id="events-loading" class="events-loading-state">
                            <div class="events-loading-spinner"></div>
                            <p>Memuat jadwal event seni...</p>
                        </div>

                        <!-- Empty State -->
                        <div id="events-empty" class="events-empty-state" style="display:none;">
                            <div class="events-empty-icon">🎭</div>
                            <h3>Belum Ada Event</h3>
                            <p>Belum ada jadwal event yang tersedia untuk filter ini. Pantau terus halaman ini untuk pembaruan terbaru.</p>
                        </div>

                        <!-- ====================================================
                             INTERACTIVE CALENDAR COMPONENT (KALENDER EVENT SENI)
                             ==================================================== -->
                        <section id="events-calendar-section" class="events-calendar-section">
                            <!-- Calendar Panel Header -->
                            <div class="calendar-panel-header">
                                <div class="calendar-title-wrap">
                                    <span class="postage-stamp calendar-stamp">AGENDA BULANAN</span>
                                    <h2 class="calendar-main-title">Kalender Agenda Event Seni</h2>
                                    <p class="calendar-subtitle">Jelajahi jadwal pameran seni & pagelaran budaya berdasarkan tanggal</p>
                                </div>
                                
                                <div class="calendar-controls-wrap">
                                    <div class="calendar-nav-buttons">
                                        <button type="button" class="cal-nav-btn" id="cal-prev-month" title="Bulan Sebelumnya" aria-label="Bulan Sebelumnya">
                                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="15 18 9 12 15 6"></polyline></svg>
                                        </button>
                                        <div class="cal-current-month-display" id="cal-month-year-label">September 2026</div>
                                        <button type="button" class="cal-nav-btn" id="cal-next-month" title="Bulan Berikutnya" aria-label="Bulan Berikutnya">
                                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="9 18 15 12 9 6"></polyline></svg>
                                        </button>
                                        <button type="button" class="cal-today-btn" id="cal-today-btn" title="Kembali ke Hari Ini">Hari Ini</button>
                                    </div>

                                    <div class="calendar-quick-actions">
                                        <button type="button" class="cal-quick-filter-btn ongoing" id="cal-filter-ongoing" title="Lihat event yang sedang berjalan secara instan">
                                            <span class="status-dot ongoing-dot"></span> Sedang Berjalan (<span id="cal-ongoing-count">0</span>)
                                        </button>
                                        <button type="button" class="cal-reset-btn" id="cal-reset-filter-btn" style="display:none;" title="Tampilkan seluruh event kembali">
                                            ✕ Reset Filter
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- Calendar Layout: Grid Matrix + Agenda Panel -->
                            <div class="calendar-layout-container">
                                <!-- Left: Monthly Grid -->
                                <div class="calendar-grid-card">
                                    <div class="calendar-weekdays-row">
                                        <span class="cal-weekday weekend">Min</span>
                                        <span class="cal-weekday">Sen</span>
                                        <span class="cal-weekday">Sel</span>
                                        <span class="cal-weekday">Rab</span>
                                        <span class="cal-weekday">Kam</span>
                                        <span class="cal-weekday">Jum</span>
                                        <span class="cal-weekday weekend">Sab</span>
                                    </div>
                                    
                                    <div class="calendar-days-grid" id="calendar-days-grid">
                                        <!-- Diisi secara dinamis oleh events.js -->
                                    </div>

                                    <div class="calendar-legend-bar">
                                        <div class="cal-legend-item"><span class="legend-indicator dot-ongoing"></span> Sedang Berlangsung</div>
                                        <div class="cal-legend-item"><span class="legend-indicator dot-upcoming"></span> Akan Datang</div>
                                        <div class="cal-legend-item"><span class="legend-indicator dot-completed"></span> Selesai</div>
                                        <div class="cal-legend-item"><span class="legend-indicator ring-today"></span> Hari Ini</div>
                                    </div>
                                </div>

                                <!-- Right: Agenda Detail Panel for Selected Date -->
                                <div class="calendar-agenda-card" id="calendar-agenda-card">
                                    <div class="agenda-card-header">
                                        <div class="agenda-date-badge" id="agenda-date-badge">
                                            <span class="agenda-day-num" id="agenda-day-num">27</span>
                                            <div class="agenda-date-sub">
                                                <span class="agenda-month-year" id="agenda-month-year">September 2026</span>
                                                <span class="agenda-day-name" id="agenda-day-name">Minggu</span>
                                            </div>
                                        </div>
                                        <span class="agenda-event-count" id="agenda-event-count">0 Event</span>
                                    </div>

                                    <div class="agenda-card-content" id="agenda-card-content">
                                        <!-- Diisi secara dinamis oleh events.js -->
                                    </div>
                                </div>
                            </div>

                            <!-- Active Filter Notification Bar -->
                            <div id="calendar-filter-banner" class="calendar-filter-banner" style="display:none;">
                                <div class="filter-banner-text">
                                    <span>📅 Memfilter event pada tanggal: <strong id="filter-banner-date-text">27 September 2026</strong></span>
                                    <span class="filter-banner-count" id="filter-banner-count-badge">1 Event ditemukan</span>
                                </div>
                                <button type="button" class="filter-banner-clear-btn" id="filter-banner-clear-btn" onclick="clearDateFilter()">
                                    ✕ Batalkan Filter & Tampilkan Semua
                                </button>
                            </div>
                        </section>

                        <!-- Upcoming Events Section -->
                        <div id="section-upcoming" class="events-section" style="display:none;">
                            <div class="events-section-header">
                                <div class="events-section-title-wrap">
                                    <span class="events-section-badge upcoming-badge">🗓 AKAN DATANG</span>
                                    <h2 class="events-section-title">Event Yang Akan Datang</h2>
                                </div>
                                <div class="title-line"></div>
                            </div>
                            <div id="grid-upcoming" class="events-grid"></div>
                        </div>

                        <!-- Ongoing Events Section -->
                        <div id="section-ongoing" class="events-section" style="display:none;">
                            <div class="events-section-header">
                                <div class="events-section-title-wrap">
                                    <span class="events-section-badge ongoing-badge">🟢 SEDANG BERLANGSUNG</span>
                                    <h2 class="events-section-title">Event Sedang Berlangsung</h2>
                                </div>
                                <div class="title-line"></div>
                            </div>
                            <div id="grid-ongoing" class="events-grid"></div>
                        </div>

                        <!-- Completed Events Section -->
                        <div id="section-completed" class="events-section" style="display:none;">
                            <div class="events-section-header">
                                <div class="events-section-title-wrap">
                                    <span class="events-section-badge completed-badge">✅ TELAH SELESAI</span>
                                    <h2 class="events-section-title">Event Yang Telah Selesai</h2>
                                </div>
                                <div class="title-line"></div>
                            </div>
                            <div id="grid-completed" class="events-grid"></div>
                        </div>

                        <!-- My Bookings Section -->
                        <div id="section-my-bookings" class="events-section my-bookings-section">
                            <div class="events-section-header">
                                <div class="events-section-title-wrap">
                                    <span class="events-section-badge booking-badge">🎫 TIKET SAYA</span>
                                    <h2 class="events-section-title">Tiket & Booking Saya</h2>
                                </div>
                                <div class="title-line"></div>
                            </div>
                            <div id="my-bookings-list" class="my-bookings-list">
                                <div class="bookings-loading">Memuat data tiket...</div>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- ----------------------------------------------------
                     TAB 6: KONTRIBUTOR & SENIMAN ARSIP (ARTISTS DIRECTORY)
                     ---------------------------------------------------- -->
                <section id="tab-artists" class="tab-content" aria-labelledby="artists-title">
                    <div class="artists-hero-banner">
                        <div class="artists-hero-overlay"></div>
                        <div class="artists-hero-content">
                            <span class="postage-stamp" style="font-size: 0.72rem; margin-bottom: 10px;">DIREKTORI SENIMAN & KONTRIBUTOR</span>
                            <h1 id="artists-title" class="artists-hero-title">Kontributor & Seniman Arsip</h1>
                            <p class="artists-hero-subtitle">Mengenal para maestro pelukis, fotografer dokumenter, dan kurator yang mengabadikan rekam jejak memori Nusantara.</p>
                            
                            <!-- Filter Kategori Seniman -->
                            <div class="artist-category-pills">
                                <button class="era-chip-btn artist-cat-pill active" onclick="filterArtistsCategory('all', this)">Semua Seniman</button>
                                <button class="era-chip-btn artist-cat-pill" onclick="filterArtistsCategory('pelukis', this)">🎨 Pelukis Maestro</button>
                                <button class="era-chip-btn artist-cat-pill" onclick="filterArtistsCategory('fotografer', this)">📷 Fotografer Dokumen</button>
                                <button class="era-chip-btn artist-cat-pill" onclick="filterArtistsCategory('kurator', this)">📖 Kurator Budaya</button>
                                <button class="era-chip-btn artist-cat-pill" onclick="filterArtistsCategory('arsiparis', this)">🏛 Arsiparis Sejarah</button>
                            </div>
                        </div>
                    </div>

                    <!-- Grid Seniman Container -->
                    <div class="artists-main-wrapper">
                        <!-- Loading State -->
                        <div id="artists-loading" class="events-loading-state">
                            <div class="events-loading-spinner"></div>
                            <p>Memuat direktori seniman & kontributor...</p>
                        </div>

                        <!-- Empty State -->
                        <div id="artists-empty" class="events-empty-state" style="display:none;">
                            <div class="events-empty-icon">🎨</div>
                            <h3>Belum Ada Seniman</h3>
                            <p>Belum ada data seniman yang terdaftar untuk kategori ini.</p>
                        </div>

                        <!-- Grid Kontributor -->
                        <div id="artists-grid-container" class="artists-grid-layout">
                            <!-- Diisi dinamis oleh js/artists.js -->
                        </div>
                    </div>
                </section>

                <!-- ----------------------------------------------------
                     TAB 7 (ADMIN ONLY): PANEL KRITIK & SARAN
                     ---------------------------------------------------- -->
                <?php if ($currentUser['role'] === 'admin'): ?>
                <section id="tab-feedback-admin" class="tab-content" aria-labelledby="admin-fb-title">

                    <!-- Header Card -->
                    <div class="admin-fb-header-card">
                        <div class="admin-fb-title-wrap">
                            <span class="postage-stamp" style="font-size:0.68rem;margin-bottom:8px;">ARSIP MASUKAN PENGUNJUNG</span>
                            <h2 id="admin-fb-title">Kritik &amp; Saran Pengunjung</h2>
                            <p id="admin-fb-summary-text">Memuat data masukan...</p>
                        </div>
                        <div style="display:flex;gap:0.5rem;align-items:center;">
                            <button type="button" id="admin-fb-refresh" class="admin-fb-btn admin-fb-btn-view" title="Muat ulang data masukan" style="padding:0.45rem 0.85rem;">
                                🔄 Segarkan
                            </button>
                        </div>
                    </div>

                    <!-- Toolbar: Filter Pills + Search -->
                    <div class="admin-fb-toolbar">
                        <div class="admin-fb-filter-pills" role="group" aria-label="Filter status masukan">
                            <button class="admin-fb-pill is-active" data-status="all">
                                Semua <span class="pill-count" id="fb-count-all">0</span>
                            </button>
                            <button class="admin-fb-pill" data-status="Belum dibaca">
                                🔴 Belum Dibaca <span class="pill-count" id="fb-count-unread">0</span>
                            </button>
                            <button class="admin-fb-pill" data-status="Sudah dibaca">
                                🔵 Sudah Dibaca <span class="pill-count" id="fb-count-read">0</span>
                            </button>
                            <button class="admin-fb-pill" data-status="Ditindaklanjuti">
                                🟢 Ditindaklanjuti <span class="pill-count" id="fb-count-done">0</span>
                            </button>
                        </div>
                        <div class="admin-fb-search-wrap">
                            <input type="search" id="admin-fb-search" class="admin-fb-search-input" placeholder="🔍  Cari nama, email, atau isi masukan..." autocomplete="off" aria-label="Cari masukan">
                        </div>
                    </div>

                    <!-- Loading State -->
                    <div id="admin-fb-loading" class="events-loading-state" style="display:none;">
                        <div class="events-loading-spinner"></div>
                        <p>Memuat arsip masukan pengunjung...</p>
                    </div>

                    <!-- Empty State -->
                    <div id="admin-fb-empty" class="admin-fb-empty" style="display:none;">
                        <div class="empty-icon">📭</div>
                        <p>Belum ada masukan untuk filter ini.</p>
                    </div>

                    <!-- Tabel Masukan (Vintage Ledger) -->
                    <div class="admin-fb-table-container">
                        <table class="admin-fb-table" aria-label="Daftar Masukan Kritik dan Saran">
                            <thead>
                                <tr>
                                    <th>#ID / Tanggal</th>
                                    <th>Status</th>
                                    <th>Jenis</th>
                                    <th>Pengirim</th>
                                    <th>Isi Masukan</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="admin-fb-tbody">
                                <tr>
                                    <td colspan="6" style="text-align:center;padding:2.5rem;color:#78604d;font-family:var(--font-typewriter);">
                                        Pilih tab ini untuk memuat data masukan pengunjung.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                </section>
                <?php endif; ?>

                <!-- ----------------------------------------------------
                     SECTION: KRITIK & SARAN PENGUNJUNG (VISITOR FEEDBACK)
                     ---------------------------------------------------- -->
                <section class="feedback-section" id="section-kritik-saran" aria-label="Kritik dan Saran Pengunjung">
                    <div class="feedback-container">
                        <!-- Header -->
                        <div class="feedback-header">
                            <span class="feedback-tag">📮 KOTAK SURAT MASUKAN</span>
                            <h2 class="feedback-title">Kritik &amp; Saran</h2>
                            <div class="feedback-divider"></div>
                            <p class="feedback-desc">
                                Bantu kami merawat kualitas Jejak Waktu. Apakah ada data galeri yang perlu diperbarui, pengalaman menjelajah yang kurang nyaman, atau informasi yang dirasa tidak akurat? Tuliskan masukan Anda — setiap suara sangat berarti bagi kami.
                            </p>

                            <!-- Alert Sukses (muncul setelah submit berhasil) -->
                            <div id="feedback-alert-success" class="feedback-alert feedback-alert-success" role="status" aria-live="polite" style="display:none;"></div>

                            <!-- Tombol Buka Form -->
                            <button type="button" id="btn-open-feedback" class="vintage-btn-feedback" aria-expanded="false" aria-controls="feedback-form-card">
                                ✍️ Kirim Kritik &amp; Saran
                            </button>
                        </div>

                        <!-- Form Kritik & Saran (tersembunyi awal) -->
                        <div id="feedback-form-card" class="feedback-form-card" style="display:none;">
                            <form id="feedback-form" novalidate autocomplete="off">

                                <!-- Honeypot anti-spam (tersembunyi dari pengguna) -->
                                <div style="display:none;" aria-hidden="true">
                                    <label for="fb-hp">Biarkan kosong</label>
                                    <input type="text" id="fb-hp" name="hp_check" tabindex="-1" autocomplete="off">
                                </div>

                                <!-- Alert Error -->
                                <div id="feedback-alert-error" class="feedback-alert feedback-alert-error" role="alert" style="display:none;"></div>

                                <!-- Grid 2 kolom: Nama & Email -->
                                <div class="feedback-form-grid">
                                    <div class="feedback-form-group">
                                        <label for="fb-nama" class="feedback-label">👤 Nama <span style="font-weight:400;color:#78604d;">(Opsional)</span></label>
                                        <input type="text" id="fb-nama" name="nama" class="feedback-input" placeholder="Nama Anda atau biarkan anonim" maxlength="100" autocomplete="name">
                                    </div>
                                    <div class="feedback-form-group">
                                        <label for="fb-email" class="feedback-label">✉️ Email <span style="font-weight:400;color:#78604d;">(Opsional)</span></label>
                                        <input type="email" id="fb-email" name="email" class="feedback-input" placeholder="Jika ingin mendapat balasan" maxlength="150" autocomplete="email">
                                    </div>
                                </div>

                                <!-- Jenis Masukan -->
                                <div class="feedback-form-group">
                                    <label for="fb-jenis" class="feedback-label">🏷️ Jenis Masukan</label>
                                    <select id="fb-jenis" name="jenis_masukan" class="feedback-select">
                                        <option value="Saran">💡 Saran</option>
                                        <option value="Kritik">📌 Kritik</option>
                                        <option value="Koreksi Data">✏️ Koreksi Data Galeri</option>
                                        <option value="Bug/Masalah Teknis">🐛 Bug / Masalah Teknis</option>
                                        <option value="Pertanyaan">❓ Pertanyaan</option>
                                        <option value="Lainnya">📋 Lainnya</option>
                                    </select>
                                </div>

                                <!-- Isi Masukan -->
                                <div class="feedback-form-group">
                                    <label for="fb-isi" class="feedback-label">📝 Isi Kritik / Saran <span class="required-star">*</span></label>
                                    <textarea id="fb-isi" name="isi" class="feedback-textarea" rows="5" placeholder="Tuliskan masukan, koreksi data, atau pertanyaan Anda secara lengkap. Setiap masukan akan dibaca dan ditindaklanjuti oleh tim kurator Jejak Waktu." maxlength="2000" required></textarea>
                                </div>

                                <!-- Actions -->
                                <div class="feedback-form-actions">
                                    <button type="button" id="feedback-btn-cancel" class="feedback-btn-cancel">✕ Tutup</button>
                                    <button type="submit" id="feedback-btn-submit" class="feedback-btn-submit">✍️ Kirimkan Masukan</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </section>

                <!-- ====================================================
                     FOOTER
                     ==================================================== -->
                <footer class="album-footer">
                    <div>
                        <strong>Jejak Waktu</strong> &copy; 2026. Merawat Kenangan, Mengabadikan Sejarah.
                    </div>
                    <div style="display: flex; gap: 14px; align-items: center;">
                        <span>Format Album Fisik Digital</span>
                        <span>&bull;</span>
                        <span>Hak Akses: <?= $currentUser['role'] === 'admin' ? 'Administrator Penuh' : 'Pembaca Publik' ?></span>
                        <span>&bull;</span>
                        <button type="button" onclick="document.getElementById('section-kritik-saran').scrollIntoView({behavior:'smooth'}); setTimeout(function(){document.getElementById('btn-open-feedback').click();},500);" style="background:none;border:none;cursor:pointer;font-family:var(--font-typewriter);font-size:0.75rem;color:var(--color-ink-sepia);text-decoration:underline;padding:0;">📮 Kritik &amp; Saran</button>
                    </div>
                </footer>
            </div>
        </main>
    </div>

    <!-- ====================================================
         MODAL: TEMPEL KENANGAN BARU (ADD MEMORY FORM - ADMIN)
         ==================================================== -->
    <div id="memory-modal" class="modal-vintage-overlay" aria-hidden="true" role="dialog" aria-modal="true">
        <div class="modal-archive-sheet">
            <button id="modal-close-btn" class="modal-close-icon" title="Tutup Formulir" aria-label="Tutup">&times;</button>
            
            <div class="modal-form-header">
                <span class="postage-stamp" style="font-size: 0.7rem; margin-bottom: 6px;">FORMULIR PENYERAHAN ARSIP</span>
                <h3>Tempel Kenangan Baru</h3>
                <p>Abadikan foto bersejarah atau memori pribadi Anda ke dalam lembaran album Jejak Waktu</p>
            </div>

            <form id="memory-form">
                <div class="vintage-form-group">
                    <label for="form-album-select">Pilih Buku Album / Galeri Tujuan:</label>
                    <select id="form-album-select" class="vintage-select" required>
                        <!-- Dynamic Albums list -->
                    </select>
                </div>

                <!-- 1. Judul Karya -->
                <div class="vintage-form-group">
                    <label for="form-photo-title">1. Judul Karya / Foto Kenangan: <span style="color: var(--color-stamp-red);">*</span></label>
                    <input type="text" id="form-photo-title" class="vintage-input" placeholder="Contoh: Senja di Pelabuhan Sunda Kelapa '79 / Lukisan Sang Penari" required />
                </div>

                <!-- 2 & 3. Nama Seniman / Pencipta 1 & 2 -->
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                    <div class="vintage-form-group">
                        <label for="form-photo-artist-1">2. Nama Seniman / Pencipta 1:</label>
                        <input type="text" id="form-photo-artist-1" class="vintage-input" placeholder="Contoh: Raden Saleh / Affandi" />
                    </div>
                    <div class="vintage-form-group">
                        <label for="form-photo-artist-2">3. Nama Seniman / Pencipta 2 (Opsional):</label>
                        <input type="text" id="form-photo-artist-2" class="vintage-input" placeholder="Kolaborator / Co-creator" />
                    </div>
                </div>

                <!-- 4 & 5. Tahun Pembuatan & Tempat -->
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                    <div class="vintage-form-group">
                        <label for="form-photo-year">4. Tahun Pembuatan:</label>
                        <input type="text" id="form-photo-year" class="vintage-input" placeholder="Misal: 1979 / 1865" />
                    </div>
                    <div class="vintage-form-group">
                        <label for="form-photo-location">5. Tempat / Lokasi:</label>
                        <input type="text" id="form-photo-location" class="vintage-input" placeholder="Misal: Yogyakarta, Jawa Tengah" />
                    </div>
                </div>

                <!-- 6 & 7. Media/Bahan/Teknik & Ukuran/Dimensi -->
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                    <div class="vintage-form-group">
                        <label for="form-photo-medium">6. Media / Bahan / Teknik:</label>
                        <input type="text" id="form-photo-medium" class="vintage-input" placeholder="Contoh: Oil on canvas, Digital print, Fotografi hitam-putih" />
                    </div>
                    <div class="vintage-form-group">
                        <label for="form-photo-dimension">7. Ukuran / Dimensi:</label>
                        <input type="text" id="form-photo-dimension" class="vintage-input" placeholder="Contoh: 100 x 120 cm, 3000 x 2000 px" />
                    </div>
                </div>

                <!-- 8. Hak Cipta -->
                <div class="vintage-form-group">
                    <label for="form-photo-copyright">8. Hak Cipta / Lisensi:</label>
                    <input type="text" id="form-photo-copyright" class="vintage-input" placeholder="Contoh: Hak Cipta © 2026 Seniman / Domain Publik / CC BY-SA 4.0" />
                </div>

                <div class="vintage-form-group">
                    <label for="form-photo-file">Unggah Berkas Karya / Foto (Perangkat):</label>
                    <input type="file" id="form-photo-file" accept="image/*" class="vintage-input" style="padding: 6px;" />
                </div>

                <div class="vintage-form-group">
                    <label for="form-photo-url">Atau Masukkan Tautan Gambar (URL):</label>
                    <input type="url" id="form-photo-url" class="vintage-input" placeholder="https://..." />
                    <small style="font-family: var(--font-typewriter); font-size: 0.75rem; color: var(--color-ink-faded); display: block; margin-top: 4px;">
                        💡 <em>Tips: Jika dari Pinterest atau Google, klik kanan pada gambar lalu pilih "Salin Alamat Gambar" (Copy Image Address).</em>
                    </small>
                </div>

                <!-- Live Image Preview Box -->
                <div id="modal-live-preview" style="display: none; margin-bottom: 16px; text-align: center; background: #ebdcc4; padding: 10px; border: 1px dashed #cbba9d; border-radius: 4px;">
                    <span style="font-family: var(--font-typewriter); font-size: 0.8rem; color: var(--color-ink-sepia); display: block; margin-bottom: 6px;">Pratinjau Foto:</span>
                    <img id="modal-preview-img" src="" alt="Pratinjau" style="max-height: 140px; max-width: 100%; border-radius: 2px; border: 1px solid rgba(0,0,0,0.2); box-shadow: 0 2px 5px rgba(0,0,0,0.2);" />
                </div>

                <div class="vintage-form-group">
                    <label for="form-photo-caption">Kisah & Narasi Karya / Kisah di Balik Foto:</label>
                    <textarea id="form-photo-caption" class="vintage-textarea" rows="3" placeholder="Tuliskan narasi kuratorial, makna filosofis, memori, atau suasana di balik karya seni ini..."></textarea>
                </div>

                <div class="vintage-form-group">
                    <label for="form-photo-note">Catatan Tambahan / Pemilik Koleksi:</label>
                    <input type="text" id="form-photo-note" class="vintage-input" placeholder="Contoh: Koleksi pribadi galeri / Disumbangkan oleh keluarga" />
                </div>

                <button type="submit" class="vintage-btn-submit">
                    &check; Tempelkan ke Dalam Album Galeri
                </button>
            </form>
        </div>
    </div>

    <!-- ====================================================
         MODAL: BUAT ALBUM BARU (ADD ALBUM FORM - ADMIN)
         ==================================================== -->
    <div id="album-modal" class="modal-vintage-overlay" aria-hidden="true" role="dialog" aria-modal="true">
        <div class="modal-archive-sheet">
            <button id="album-modal-close-btn" class="modal-close-icon" title="Tutup Formulir" aria-label="Tutup">&times;</button>
            
            <div class="modal-form-header">
                <span class="postage-stamp" style="font-size: 0.7rem; margin-bottom: 6px;">ARSIP JILID BUKU BARU</span>
                <h3>Buat Buku Album Baru</h3>
                <p>Buka lembaran babak baru untuk merangkum peristiwa, kota, atau nostalgia masa lalu</p>
            </div>

            <form id="album-form">
                <div class="vintage-form-group">
                    <label for="album-form-title">Judul Buku Album:</label>
                    <input type="text" id="album-form-title" class="vintage-input" placeholder="Contoh: Senandung Kota Pahlawan 1980" required />
                </div>

                <div class="vintage-form-group">
                    <label for="album-form-subtitle">Subjudul / Tajuk Singkat:</label>
                    <input type="text" id="album-form-subtitle" class="vintage-input" placeholder="Contoh: Potret Kenangan Surabaya Tempo Doeloe" />
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                    <div class="vintage-form-group">
                        <label for="album-form-decade">Pilihan Dekade:</label>
                        <select id="album-form-decade" class="vintage-select">
                            <option value="1950s">1950-an</option>
                            <option value="1960s">1960-an</option>
                            <option value="1970s">1970-an</option>
                            <option value="1980s" selected>1980-an</option>
                            <option value="1990s">1990-an</option>
                            <option value="2000s">2000-an</option>
                            <option value="2010s">2010-an</option>
                        </select>
                    </div>
                    <div class="vintage-form-group">
                        <label for="album-form-era">Rentang Era / Tahun:</label>
                        <input type="text" id="album-form-era" class="vintage-input" placeholder="Misal: 1980-1988" />
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                    <div class="vintage-form-group">
                        <label for="album-form-category">Kategori Album:</label>
                        <select id="album-form-category" class="vintage-select">
                            <option value="sejarah-kota">Sejarah Kota & Transportasi</option>
                            <option value="keluarga-kehidupan">Keluarga & Kehidupan</option>
                            <option value="sekolah-remaja">Sekolah & Masa Remaja</option>
                            <option value="budaya-tradisi">Seni & Budaya Tradisi</option>
                        </select>
                    </div>
                    <div class="vintage-form-group">
                        <label for="album-form-location">Lokasi / Wilayah:</label>
                        <input type="text" id="album-form-location" class="vintage-input" placeholder="Misal: Surabaya, Jawa Timur" />
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                    <div class="vintage-form-group">
                        <label for="album-form-curator">Kurator / Pemilik Arsip:</label>
                        <input type="text" id="album-form-curator" class="vintage-input" placeholder="Nama Anda / Kolektif" />
                    </div>
                    <div class="vintage-form-group">
                        <label for="album-form-color">Warna Kulit Sampul:</label>
                        <select id="album-form-color" class="vintage-select">
                            <option value="#422a1d">Cokelat Tua Klasik (Sepia Leather)</option>
                            <option value="#2b3a4a">Biru Kelasi Vintage (Navy)</option>
                            <option value="#364935">Hijau Lumut Rimba (Moss Forest)</option>
                            <option value="#5c2d3e">Merah Marun Beludru (Velvet Maroon)</option>
                            <option value="#252525">Hitam Arang Antik (Vintage Charcoal)</option>
                        </select>
                    </div>
                </div>

                <div class="vintage-form-group">
                    <label for="album-form-file">Unggah Foto Sampul dari Perangkat:</label>
                    <input type="file" id="album-form-file" accept="image/*" class="vintage-input" style="padding: 6px;" />
                </div>

                <div class="vintage-form-group">
                    <label for="album-form-url">Atau Masukkan Tautan Gambar Sampul (URL):</label>
                    <input type="url" id="album-form-url" class="vintage-input" placeholder="https://..." />
                </div>

                <!-- Live Album Cover Preview Box -->
                <div id="album-live-preview" style="display: none; margin-bottom: 16px; text-align: center; background: #ebdcc4; padding: 10px; border: 1px dashed #cbba9d; border-radius: 4px;">
                    <span style="font-family: var(--font-typewriter); font-size: 0.8rem; color: var(--color-ink-sepia); display: block; margin-bottom: 6px;">Pratinjau Sampul Album:</span>
                    <img id="album-preview-img" src="" alt="Pratinjau Sampul" style="max-height: 140px; max-width: 100%; border-radius: 2px; border: 1px solid rgba(0,0,0,0.2); box-shadow: 0 2px 5px rgba(0,0,0,0.2);" />
                </div>

                <div class="vintage-form-group">
                    <label for="album-form-desc">Kisah & Deskripsi Buku Album:</label>
                    <textarea id="album-form-desc" class="vintage-textarea" rows="3" placeholder="Ceritakan latar belakang album, kenangan yang dirawat, atau pesan bagi pembuka lembaran ini..."></textarea>
                </div>

                <button type="submit" class="vintage-btn-submit">
                    &check; Buat & Simpan Buku Album
                </button>
            </form>
        </div>
    </div>

    <!-- ===== MODAL: DETAIL EVENT + BOOKING ===== -->
    <div id="event-detail-modal" class="ev-modal-overlay" aria-hidden="true" role="dialog" aria-modal="true">
        <div class="ev-modal-sheet ev-modal-large">
            <button class="ev-modal-close" onclick="closeEventDetailModal()" aria-label="Tutup">&times;</button>
            <div id="event-detail-content">
                <!-- Diisi dinamis oleh JS -->
            </div>
        </div>
    </div>

    <!-- ===== MODAL: FORM EVENT (Admin) ===== -->
    <div id="event-form-modal" class="ev-modal-overlay" aria-hidden="true" role="dialog" aria-modal="true">
        <div class="ev-modal-sheet">
            <button class="ev-modal-close" onclick="closeEventModal()" aria-label="Tutup">&times;</button>

            <div class="modal-form-header">
                <span class="postage-stamp" style="font-size: 0.7rem; margin-bottom:6px;" id="event-form-stamp">FORMULIR EVENT BARU</span>
                <h3 id="event-form-modal-title">Tambah Event Seni Baru</h3>
                <p>Isi detail pameran seni / event budaya yang akan dipublikasikan di halaman jadwal</p>
            </div>

            <form id="event-admin-form" enctype="multipart/form-data">
                <input type="hidden" id="event-edit-id" name="event_edit_id" value="">

                <div class="vintage-form-group">
                    <label for="ev-title">Nama / Judul Event: <span style="color: var(--color-stamp-red);">*</span></label>
                    <input type="text" id="ev-title" name="title" class="vintage-input" placeholder="Contoh: Pameran Besar Seni Rupa Nusantara 2026" required />
                </div>

                <div class="vintage-form-group">
                    <label for="ev-theme">Tema Event:</label>
                    <input type="text" id="ev-theme" name="theme" class="vintage-input" placeholder="Contoh: Jejak Peradaban Nusantara / Harmoni Alam & Budaya" />
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                    <div class="vintage-form-group">
                        <label for="ev-status">Status Event:</label>
                        <select id="ev-status" name="status" class="vintage-select">
                            <option value="upcoming">🗓 Akan Datang</option>
                            <option value="ongoing">🟢 Sedang Berjalan</option>
                            <option value="completed">✅ Selesai</option>
                        </select>
                    </div>
                    <div class="vintage-form-group">
                        <label for="ev-ticket-price">Harga Tiket Masuk:</label>
                        <input type="text" id="ev-ticket-price" name="ticket_price" class="vintage-input" placeholder="Contoh: Gratis / Rp 50.000" value="Gratis" />
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                    <div class="vintage-form-group">
                        <label for="ev-start-date">Tanggal Mulai: <span style="color: var(--color-stamp-red);">*</span></label>
                        <input type="date" id="ev-start-date" name="start_date" class="vintage-input" required />
                    </div>
                    <div class="vintage-form-group">
                        <label for="ev-end-date">Tanggal Selesai:</label>
                        <input type="date" id="ev-end-date" name="end_date" class="vintage-input" />
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                    <div class="vintage-form-group">
                        <label for="ev-start-time">Jam Buka:</label>
                        <input type="time" id="ev-start-time" name="start_time" class="vintage-input" />
                    </div>
                    <div class="vintage-form-group">
                        <label for="ev-end-time">Jam Tutup:</label>
                        <input type="time" id="ev-end-time" name="end_time" class="vintage-input" />
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                    <div class="vintage-form-group">
                        <label for="ev-quota">Kuota Tiket (kosongkan = tak terbatas):</label>
                        <input type="number" id="ev-quota" name="ticket_quota" class="vintage-input" min="1" placeholder="Misal: 200" />
                    </div>
                    <div class="vintage-form-group">
                        <label for="ev-organizer">Penyelenggara:</label>
                        <input type="text" id="ev-organizer" name="organizer" class="vintage-input" placeholder="Jejak Waktu / Nama Galeri" value="Jejak Waktu" />
                    </div>
                </div>

                <div class="vintage-form-group">
                    <label for="ev-location-text">Lokasi & Alamat Venue:</label>
                    <input type="text" id="ev-location-text" name="location_text" class="vintage-input" placeholder="Contoh: Galeri Nasional Indonesia, Jl. Medan Merdeka Timur No. 14, Jakarta" />
                </div>

                <div class="vintage-form-group">
                    <label for="ev-location-map">Embed Link Peta / Google Maps URL (Opsional):</label>
                    <input type="url" id="ev-location-map" name="location_map" class="vintage-input" placeholder="https://maps.google.com/?q=... atau iframe embed src" />
                    <small style="font-family: var(--font-typewriter); font-size: 0.75rem; color: var(--color-ink-faded);">💡 Gunakan link share dari Google Maps atau salin src dari iframe embed</small>
                </div>

                <div class="vintage-form-group">
                    <label for="ev-artists">Daftar Seniman yang Hadir:</label>
                    <textarea id="ev-artists" name="artists" class="vintage-textarea" rows="2" placeholder="Contoh: Affandi Jr., Raden Saleh IV, Yuli Prayitno, Maya Indah&#10;(pisahkan dengan koma atau baris baru)"></textarea>
                </div>

                <div class="vintage-form-group">
                    <label for="ev-description">Deskripsi Lengkap Event:</label>
                    <textarea id="ev-description" name="description" class="vintage-textarea" rows="3" placeholder="Tuliskan gambaran umum, visi misi event, atau pesan kuratorial..."></textarea>
                </div>

                <div class="vintage-form-group">
                    <label for="ev-poster-file">Upload Poster / Cover Album Event:</label>
                    <input type="file" id="ev-poster-file" name="poster_file" accept="image/*" class="vintage-input" style="padding: 6px;" />
                </div>
                <div class="vintage-form-group">
                    <label for="ev-poster-url">Atau URL Poster / Gambar Cover Event:</label>
                    <input type="url" id="ev-poster-url" name="poster_url" class="vintage-input" placeholder="https://..." />
                </div>

                <!-- Live poster preview -->
                <div id="ev-poster-preview" style="display:none; margin-bottom:16px; text-align:center; background:#ebdcc4; padding:10px; border:1px dashed #cbba9d; border-radius:4px;">
                    <span style="font-family:var(--font-typewriter); font-size:0.8rem; color:var(--color-ink-sepia); display:block; margin-bottom:6px;">Pratinjau Poster:</span>
                    <img id="ev-poster-preview-img" src="" alt="Preview Poster" style="max-height:160px; max-width:100%; border-radius:2px; border:1px solid rgba(0,0,0,0.2);" />
                </div>

                <div class="vintage-form-group">
                    <label for="ev-album-id">Kaitkan ke Album Galeri (Opsional):</label>
                    <select id="ev-album-id" name="album_id" class="vintage-select">
                        <option value="">— Tidak dikaitkan ke album —</option>
                        <!-- Diisi JS dari albums API -->
                    </select>
                </div>

                <div style="display:flex; gap:12px; margin-top:8px;">
                    <button type="submit" class="vintage-btn-submit" id="ev-form-submit-btn">
                        ✓ Simpan Event
                    </button>
                    <button type="button" class="ev-btn-secondary" onclick="closeEventModal()">
                        Batal
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- ====================================================
         MODAL: DETAIL MASUKAN KRITIK & SARAN (ADMIN)
         ==================================================== -->
    <div id="feedback-detail-modal" class="feedback-modal-overlay" role="dialog" aria-modal="true" aria-label="Detail Masukan Kritik &amp; Saran" aria-hidden="true">
        <div class="feedback-modal-sheet">
            <button type="button" class="feedback-modal-close" onclick="closeFeedbackDetailModal()" aria-label="Tutup detail masukan">&times;</button>
            <div id="feedback-detail-modal-body">
                <!-- Diisi dinamis oleh feedback.js -->
            </div>
        </div>
    </div>

    <!-- ====================================================
         MODAL: EDIT PROFIL & FOTO ARTIST (ADMIN)
         ==================================================== -->
    <div id="artist-edit-modal" class="modal-vintage-overlay" aria-hidden="true" role="dialog" aria-modal="true" aria-labelledby="artist-edit-modal-title">
        <div class="modal-archive-sheet" style="max-width: 620px;">
            <button id="artist-edit-close-btn" class="modal-close-icon" onclick="closeArtistEditModal()" title="Tutup Formulir" aria-label="Tutup">&times;</button>
            
            <div class="modal-form-header">
                <span class="postage-stamp" style="font-size: 0.7rem; margin-bottom: 6px;">ARSIP BIOGRAFI SENIMAN</span>
                <h3 id="artist-edit-modal-title">Edit Profil Seniman</h3>
                <p>Perbarui informasi identitas, foto profil, dan narasi kontributor arsip sejarah</p>
            </div>

            <form id="artist-edit-form" enctype="multipart/form-data">
                <input type="hidden" id="edit-artist-id" name="artist_id" value="" />
                <input type="hidden" id="edit-artist-current-avatar" name="current_avatar" value="" />

                <!-- Bagian Edit Foto Profil -->
                <div class="artist-edit-photo-panel">
                    <span class="artist-edit-section-title">Foto Profil Seniman:</span>
                    
                    <div class="artist-edit-photo-flex">
                        <!-- Tampilan Avatar Saat Ini / Preview -->
                        <div class="artist-edit-avatar-box">
                            <img id="edit-artist-current-img" src="" alt="Foto Profil Seniman" class="artist-edit-avatar-img" />
                            <div id="edit-artist-placeholder" class="artist-edit-avatar-placeholder" style="display:none;">
                                <span class="artist-placeholder-icon">👤</span>
                                <span class="artist-placeholder-text">Belum Ada Foto</span>
                            </div>
                            <span id="edit-artist-badge-status" class="artist-edit-badge-status">Foto Saat Ini</span>
                        </div>

                        <!-- Opsi Ganti Foto Profil -->
                        <div class="artist-edit-upload-actions">
                            <div class="artist-upload-btn-group">
                                <label for="edit-artist-file-input" class="vintage-btn-upload-photo" title="Pilih foto profil baru dari perangkat Anda">
                                    📷 Ganti Foto Profil
                                </label>
                                <input type="file" id="edit-artist-file-input" name="avatar_file" accept="image/jpeg,image/png,image/webp,image/jpg" style="display:none;" />
                                
                                <button type="button" id="edit-artist-cancel-photo-btn" class="artist-cancel-file-btn" style="display:none;" onclick="cancelArtistPhotoChange()">
                                    ✕ Batalkan Ganti Foto
                                </button>
                            </div>
                            
                            <p class="artist-upload-note">
                                Format: <strong>JPG, JPEG, PNG, WebP</strong>. Maksimal <strong>5 MB</strong>.<br>
                                Foto baru akan menggantikan foto lama saat disimpan.
                            </p>

                            <!-- Kotak Info Pratinjau Foto Baru -->
                            <div id="edit-artist-new-preview-box" class="artist-new-file-info" style="display:none;">
                                <span class="artist-new-file-badge">✨ Foto Baru Siap Diunggah:</span>
                                <span id="edit-artist-file-name" class="artist-new-file-name"></span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Bagian Form Data Seniman -->
                <div class="vintage-form-group">
                    <label for="edit-artist-name">Nama Lengkap Seniman / Pencipta: <span style="color: var(--color-stamp-red);">*</span></label>
                    <input type="text" id="edit-artist-name" name="name" class="vintage-input" placeholder="Contoh: Raden Saleh Syarif Bustaman" required />
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                    <div class="vintage-form-group">
                        <label for="edit-artist-role">Gelar / Peran Seni:</label>
                        <input type="text" id="edit-artist-role" name="role" class="vintage-input" placeholder="Contoh: Pelukis Maestro / Fotografer" />
                    </div>
                    <div class="vintage-form-group">
                        <label for="edit-artist-era">Rentang Era / Tahun:</label>
                        <input type="text" id="edit-artist-era" name="era" class="vintage-input" placeholder="Contoh: 1850-1880 / 1990-an" />
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                    <div class="vintage-form-group">
                        <label for="edit-artist-category">Kategori Utama:</label>
                        <select id="edit-artist-category" name="category" class="vintage-select">
                            <option value="pelukis">🎨 Pelukis Maestro</option>
                            <option value="fotografer">📷 Fotografer Dokumen</option>
                            <option value="kurator">📖 Kurator Budaya</option>
                            <option value="arsiparis">🏛 Arsiparis Sejarah</option>
                        </select>
                    </div>
                    <div class="vintage-form-group">
                        <label for="edit-artist-location">Dominasi Wilayah Karya:</label>
                        <input type="text" id="edit-artist-location" name="location" class="vintage-input" placeholder="Contoh: Semarang & Batavia / Surabaya" />
                    </div>
                </div>

                <div class="vintage-form-group">
                    <label for="edit-artist-bio">Biografi Singkat & Kisah Kontribusi:</label>
                    <textarea id="edit-artist-bio" name="bio" class="vintage-textarea" rows="3" placeholder="Tuliskan biografi singkat atau rekam jejak kontribusi seniman ini..."></textarea>
                </div>

                <div style="display: flex; gap: 12px; margin-top: 16px;">
                    <button type="submit" class="vintage-btn-submit" id="edit-artist-submit-btn">
                        ✓ Simpan Perubahan Profil
                    </button>
                    <button type="button" class="ev-btn-secondary" onclick="closeArtistEditModal()">
                        Batal
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- ====================================================
         MODAL: KONFIRMASI HAPUS SENIMAN (ADMIN)
         ==================================================== -->
    <div id="artist-delete-modal" class="modal-vintage-overlay" aria-hidden="true" role="dialog" aria-modal="true" aria-labelledby="artist-delete-modal-title">
        <div class="modal-archive-sheet" style="max-width: 480px; text-align: center;">
            <button id="artist-delete-close-btn" class="modal-close-icon" onclick="closeArtistDeleteModal()" title="Tutup" aria-label="Tutup">&times;</button>
            
            <div class="modal-form-header" style="text-align: center; border-bottom: none; margin-bottom: 6px;">
                <div style="font-size: 2.2rem; margin-bottom: 6px;">⚠️</div>
                <span class="postage-stamp" style="font-size: 0.68rem; margin-bottom: 6px; background: #9c2727; color: #fff; border-color: #6b1515;">ARSIP SENIMAN</span>
                <h3 id="artist-delete-modal-title" style="color: #6e1c1c; margin-top: 6px;">Konfirmasi Hapus Seniman</h3>
            </div>

            <input type="hidden" id="delete-artist-id" value="" />

            <div style="margin: 14px 0 22px 0; font-family: var(--font-body); font-size: 0.95rem; line-height: 1.6; color: #433022;">
                <p id="artist-delete-confirm-text" style="font-weight: 500; margin-bottom: 8px;">
                    Yakin ingin menghapus seniman ini? Data seniman dan foto profilnya akan ikut dihapus.
                </p>
                <div id="artist-delete-target-name" style="font-weight: bold; color: #8f2d2d; font-family: var(--font-typewriter); font-size: 0.95rem;"></div>
            </div>

            <div style="display: flex; gap: 12px; justify-content: center;">
                <button type="button" class="ev-btn-secondary" id="artist-delete-cancel-btn" onclick="closeArtistDeleteModal()" style="min-width: 95px; padding: 7px 18px;">
                    Batal
                </button>
                <button type="button" class="vintage-btn-danger" id="artist-delete-confirm-btn" onclick="confirmDeleteArtist()" style="min-width: 95px; background: #9c2727; color: #fff; border: 1px solid #6b1515; padding: 7px 18px; border-radius: 4px; font-family: var(--font-typewriter); font-size: 0.85rem; font-weight: bold; cursor: pointer; transition: all 0.2s;">
                    🗑️ Hapus
                </button>
            </div>
        </div>
    </div>


    <!-- Scripts -->
    <script src="js/data.js"></script>
    <script src="js/audio.js"></script>
    <script src="js/lightbox.js"></script>
    <script src="js/events.js"></script>
    <script src="js/artists.js"></script>
    <script src="js/app.js"></script>
    <script src="js/feedback.js"></script>
</body>
</html>
