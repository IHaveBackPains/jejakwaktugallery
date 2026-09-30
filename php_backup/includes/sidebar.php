<?php
/**
 * Shared Component: Vertical Retro Tab Sidebar Navigation
 * Jejak Waktu — Galeri Album Foto & Kenangan Sejarah
 */
$activePage = $activeTab ?? 'beranda';
?>

<!-- ============================================================
     Leaflet.js — dimuat sekali di sidebar, akan di-guard duplikat
     ============================================================ -->
<?php if (!defined('LEAFLET_LOADED')): define('LEAFLET_LOADED', true); ?>
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
<link rel="stylesheet" href="css/MarkerCluster.css">
<link rel="stylesheet" href="css/MarkerCluster.Default.css">
<link rel="stylesheet" href="css/gallery-map.css">
<?php endif; ?>

<!-- ====================================================
     VERTICAL SKEUOMORPHIC BOOKMARK / INDEX CARD TABS SIDEBAR
     ==================================================== -->
<aside class="album-sidebar-nav" aria-label="Navigasi Sidebar Tab">
    <div class="sidebar-tabs-list">
        <a href="#beranda" class="book-tab vertical-tab <?= $activePage === 'beranda' ? 'active' : '' ?>" data-tab="beranda" id="tab-nav-beranda" style="text-decoration:none;">
            <span class="tab-tag-num">01</span>
            <span>Beranda Album</span>
        </a>
        <a href="#galeri" class="book-tab vertical-tab <?= $activePage === 'galeri' ? 'active' : '' ?>" data-tab="galeri" id="tab-nav-galeri" style="text-decoration:none;">
            <span class="tab-tag-num">02</span>
            <span>Koleksi Album</span>
        </a>
        <a href="#tentang" class="book-tab vertical-tab <?= $activePage === 'tentang' ? 'active' : '' ?>" data-tab="tentang" id="tab-nav-tentang" style="text-decoration:none;">
            <span class="tab-tag-num">03</span>
            <span>Tentang Kami</span>
        </a>
        <a href="#events" class="book-tab vertical-tab <?= $activePage === 'events' ? 'active' : '' ?>" data-tab="events" id="tab-nav-events" style="text-decoration:none;">
            <span class="tab-tag-num">04</span>
            <span>Jadwal Event</span>
        </a>
        <a href="#artists" class="book-tab vertical-tab <?= $activePage === 'artists' ? 'active' : '' ?>" data-tab="artists" id="tab-nav-artists" style="text-decoration:none;">
            <span class="tab-tag-num">05</span>
            <span>Daftar Artist</span>
        </a>
        <?php if (($currentUser['role'] ?? 'user') === 'admin'): ?>
        <a href="#feedback-admin" class="book-tab vertical-tab <?= $activePage === 'feedback-admin' ? 'active' : '' ?>" data-tab="feedback-admin" id="tab-nav-feedback-admin" style="text-decoration:none;" title="Panel Kritik &amp; Saran Pengunjung">
            <span class="tab-tag-num">06</span>
            <span>Masukan</span>
            <span id="sidebar-feedback-badge" class="tab-badge-counter is-zero" style="display:none;">0</span>
        </a>
        <?php endif; ?>
    </div>

    <!-- ====================================================
         WIDGET: PETA GALERI SENI SURABAYA
         ==================================================== -->
    <div class="gallery-map-widget" id="galleryMapWidget">

        <!-- ▸ Area klik untuk membuka modal (header + peta) -->
        <div class="gallery-map-trigger" id="galleryMapTrigger"
             role="button" tabindex="0"
             aria-label="Buka peta galeri dalam layar penuh"
             title="Klik untuk membuka peta dalam layar penuh">

            <!-- Header -->
            <div class="gallery-map-header">
                <span class="gallery-map-header-icon" aria-hidden="true">🖼️</span>
                <div>
                    <h2 class="gallery-map-title">Galeri Seni Surabaya</h2>
                    <p class="gallery-map-subtitle" id="galleryMapSidebarSubtitle">Peta Interaktif · 28 Lokasi</p>
                </div>
                <span class="gallery-map-counter" id="galleryMapSidebarCounter" aria-label="28 galeri terdata">28</span>
            </div>

            <!-- Peta Leaflet (sidebar – mini preview) -->
            <div id="galleryMapCanvas" class="gallery-map-canvas"
                 role="application"
                 aria-label="Peta galeri seni di Surabaya, klik untuk tampilan penuh"></div>

            <!-- Hint expand -->
            <div class="gallery-map-expand-hint" aria-hidden="true">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <polyline points="15 3 21 3 21 9"/>
                    <polyline points="9 21 3 21 3 15"/>
                    <line x1="21" y1="3" x2="14" y2="10"/>
                    <line x1="3" y1="21" x2="10" y2="14"/>
                </svg>
                Klik untuk tampilan penuh &amp; detail
            </div>

        </div><!-- /.gallery-map-trigger -->

        <!-- Hint marker (di luar trigger) -->
        <div class="gallery-map-hint" aria-hidden="true">
            Klik peta untuk membuka eksplorasi galeri
        </div>

        <!-- Panel detail galeri mini (sidebar) -->
        <div id="galleryInfoPanel" class="gallery-info-panel" aria-live="polite">
            <div id="galleryInfoInner" class="gallery-info-inner">
                <!-- Diisi oleh gallery-map.js -->
            </div>
        </div>

    </div><!-- /.gallery-map-widget -->

    <!-- ====================================================
         MODAL FULLSCREEN — Peta Galeri Seni & Art Space Surabaya
         ==================================================== -->
    <div id="galleryMapModal" class="gmap-modal-overlay" role="dialog"
         aria-modal="true" aria-label="Peta Galeri Seni Surabaya — Tampilan Penuh"
         aria-hidden="true">

        <div class="gmap-modal-box">

            <!-- ─── Header Modal ─── -->
            <div class="gmap-modal-header">
                <div class="gmap-modal-header-left">
                    <span class="gmap-modal-icon" aria-hidden="true">🏛️</span>
                    <div>
                        <h2 class="gmap-modal-title">Peta Galeri Seni &amp; Art Space Kota Surabaya</h2>
                        <p class="gmap-modal-subtitle" id="gmapModalSubtitle">Peta Interaktif · 28 Ruang Seni &amp; Budaya · Klik marker untuk detail</p>
                    </div>
                </div>

                <div class="gmap-modal-header-actions">
                    <button type="button" class="gmap-header-btn" id="gmapToggleLegendBtn" title="Lihat Legenda Kategori">
                        <span class="gmap-btn-icon">🎨</span>
                        <span class="gmap-btn-text">Legenda</span>
                    </button>
                    <button type="button" class="gmap-header-btn" id="gmapToggleListBtn" title="Lihat Daftar Galeri">
                        <span class="gmap-btn-icon">📋</span>
                        <span class="gmap-btn-text">Daftar Tempat</span>
                        <span class="gmap-count-badge" id="gmapHeaderCount">28</span>
                    </button>
                    <button class="gmap-modal-close" id="galleryMapModalClose"
                            aria-label="Tutup peta" title="Tutup (Esc)">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round">
                            <line x1="18" y1="6" x2="6" y2="18"/>
                            <line x1="6" y1="6" x2="18" y2="18"/>
                        </svg>
                    </button>
                </div>
            </div>

            <!-- ─── Toolbar: Pencarian & Filter ─── -->
            <div class="gmap-toolbar">
                <!-- Search Bar -->
                <div class="gmap-search-wrap">
                    <span class="gmap-search-icon" aria-hidden="true">🔍</span>
                    <input type="text" id="gmapSearchInput" class="gmap-search-input"
                           placeholder="Cari nama galeri, founder, fokus seni, atau alamat..."
                           autocomplete="off" aria-label="Cari galeri seni">
                    <button type="button" id="gmapSearchClear" class="gmap-search-clear" title="Bersihkan pencarian" aria-label="Bersihkan pencarian">✕</button>
                </div>

                <!-- Dropdown Filters -->
                <div class="gmap-filters-wrap">
                    <!-- Status Filter -->
                    <div class="gmap-select-wrapper">
                        <label for="gmapStatusFilter" class="gmap-filter-label">Status:</label>
                        <select id="gmapStatusFilter" class="gmap-select" aria-label="Filter status">
                            <option value="Semua">Semua Status</option>
                            <option value="Aktif">● Aktif</option>
                            <option value="Historis/Tidak aktif">● Historis / Tidak aktif</option>
                            <option value="Perlu diverifikasi">● Perlu diverifikasi</option>
                        </select>
                    </div>

                    <!-- Category Filter Dropdown -->
                    <div class="gmap-select-wrapper">
                        <label for="gmapCategoryFilter" class="gmap-filter-label">Kategori:</label>
                        <select id="gmapCategoryFilter" class="gmap-select" aria-label="Filter kategori">
                            <option value="Semua">Semua Kategori</option>
                            <option value="Galeri Seni">Galeri Seni</option>
                            <option value="Galeri Seni Kontemporer">Galeri Seni Kontemporer</option>
                            <option value="Galeri Institusi/Pemerintah">Galeri Institusi/Pemerintah</option>
                            <option value="Art Space">Art Space</option>
                            <option value="Creative Space">Creative Space</option>
                            <option value="Ruang Budaya">Ruang Budaya</option>
                            <option value="Galeri Seni Tradisional">Galeri Seni Tradisional</option>
                            <option value="Galeri Seni Khusus">Galeri Seni Khusus</option>
                            <option value="Galeri Historis">Galeri Historis</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- ─── Horizontal Scrollable Category Filter Pills ─── -->
            <div class="gmap-category-pills-bar" id="gmapCategoryPillsBar" role="tablist" aria-label="Filter cepat kategori">
                <button type="button" class="gmap-pill is-active" data-category="Semua" role="tab" aria-selected="true">Semua</button>
                <button type="button" class="gmap-pill" data-category="Galeri Seni" role="tab">🖼️ Galeri Seni</button>
                <button type="button" class="gmap-pill" data-category="Galeri Seni Kontemporer" role="tab">🎨 Galeri Seni Kontemporer</button>
                <button type="button" class="gmap-pill" data-category="Galeri Institusi/Pemerintah" role="tab">🏛️ Galeri Institusi/Pemerintah</button>
                <button type="button" class="gmap-pill" data-category="Art Space" role="tab">📐 Art Space</button>
                <button type="button" class="gmap-pill" data-category="Creative Space" role="tab">💡 Creative Space</button>
                <button type="button" class="gmap-pill" data-category="Ruang Budaya" role="tab">🎭 Ruang Budaya</button>
                <button type="button" class="gmap-pill" data-category="Galeri Seni Tradisional" role="tab">🗡️ Galeri Seni Tradisional</button>
                <button type="button" class="gmap-pill" data-category="Galeri Seni Khusus" role="tab">🌟 Galeri Seni Khusus</button>
                <button type="button" class="gmap-pill" data-category="Galeri Historis" role="tab">⏳ Galeri Historis</button>
            </div>

            <!-- ─── Modal Main Stage: Map + Side Drawers + Bottom Detail Panel ─── -->
            <div class="gmap-modal-main">

                <!-- Drawer: Daftar Galeri (Quick Finder) -->
                <div class="gmap-drawer gmap-drawer-list" id="gmapListDrawer" aria-hidden="true">
                    <div class="gmap-drawer-header">
                        <div class="gmap-drawer-title-wrap">
                            <span class="gmap-drawer-icon">📋</span>
                            <h3 class="gmap-drawer-title">Daftar Tempat (<span id="gmapDrawerCount">28</span>)</h3>
                        </div>
                        <button type="button" class="gmap-drawer-close" id="gmapCloseListBtn" aria-label="Tutup daftar">✕</button>
                    </div>
                    <div class="gmap-list-scroll" id="gmapListItems">
                        <!-- Diisi secara dinamis oleh JS -->
                    </div>
                </div>

                <!-- Drawer: Legenda Kategori & Simbol -->
                <div class="gmap-drawer gmap-drawer-legend" id="gmapLegendDrawer" aria-hidden="true">
                    <div class="gmap-drawer-header">
                        <div class="gmap-drawer-title-wrap">
                            <span class="gmap-drawer-icon">🎨</span>
                            <h3 class="gmap-drawer-title">Legenda Kategori &amp; Status</h3>
                        </div>
                        <button type="button" class="gmap-drawer-close" id="gmapCloseLegendBtn" aria-label="Tutup legenda">✕</button>
                    </div>
                    <div class="gmap-legend-scroll" id="gmapLegendContent">
                        <!-- Diisi secara dinamis oleh JS -->
                    </div>
                </div>

                <!-- Leaflet Full Canvas -->
                <div class="gmap-modal-map-wrap">
                    <div id="galleryMapModalCanvas" class="gmap-modal-canvas"
                         role="application"
                         aria-label="Peta interaktif galeri seni Surabaya ukuran penuh"></div>
                </div>

                <!-- Panel Detail Galeri Lengkap (Expandable Bottom Sheet) -->
                <div id="galleryInfoPanelModal" class="gmap-modal-info-panel" aria-live="polite">
                    <div id="galleryInfoInnerModal" class="gmap-modal-info-inner">
                        <!-- Diisi secara dinamis oleh gallery-map.js saat marker diklik -->
                    </div>
                </div>

            </div><!-- /.gmap-modal-main -->

            <!-- ─── Footer Dekoratif Modal ─── -->
            <div class="gmap-modal-footer">
                <div class="gmap-modal-footer-left">
                    <span class="gmap-modal-footer-badge" id="gmapFooterBadge">28 Lokasi Terdata</span>
                    <span class="gmap-modal-footer-summary" id="gmapFooterSummary">22 Aktif · 3 Historis · 3 Perlu Verifikasi</span>
                </div>
                <span class="gmap-modal-footer-credit">OpenStreetMap Geodata · Jejak Waktu Surabaya &copy; 2026</span>
            </div>

        </div><!-- /.gmap-modal-box -->

    </div><!-- /#galleryMapModal -->

</aside>

<!-- ============================================================
     Scripts — dimuat di akhir agar tidak blocking render
     ============================================================ -->
<?php if (!defined('LEAFLET_JS_LOADED')): define('LEAFLET_JS_LOADED', true); ?>
<!-- Leaflet & MarkerCluster JS -->
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script src="js/leaflet.markercluster.js"></script>
<script src="js/gallery-map.js"></script>
<?php endif; ?>
