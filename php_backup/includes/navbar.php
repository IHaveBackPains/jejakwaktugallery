<?php
/**
 * Shared Component: Top Header Navbar (User Profile & Admin Controls)
 * Jejak Waktu — Galeri Album Foto & Kenangan Sejarah
 */
?>
<!-- ====================================================
     TOP HEADER NAVBAR (BRAND & USER ADMIN CONTROLS)
     ==================================================== -->
<header class="album-topnav" aria-label="Navigasi Header Admin">
    <div class="topnav-left">
        <!-- Logo / Brand Title -->
        <a href="index.php" class="topnav-brand" title="Jejak Waktu — Galeri Album Foto & Kenangan Sejarah">
            <span class="topnav-brand-icon">📜</span>
            <div class="topnav-brand-text">
                <span class="topnav-brand-title">Jejak Waktu</span>
                <span class="topnav-brand-sub">ARSIP DIGITAL NUSANTARA</span>
            </div>
        </a>
    </div>

    <div class="topnav-right">
        <!-- User Profile & Role Badge -->
        <div class="user-profile-badge <?= ($currentUser['role'] ?? 'user') === 'admin' ? 'badge-admin' : 'badge-user' ?>" title="Akun yang sedang aktif: <?= htmlspecialchars($currentUser['username'] ?? '') ?>">
            <span class="user-role-icon"><?= ($currentUser['role'] ?? 'user') === 'admin' ? '👑' : '📖' ?></span>
            <div class="user-meta-names">
                <span class="user-fullname" style="color: #ffffff; font-weight: 700; font-size: 0.82rem; text-shadow: 0 1px 2px rgba(0,0,0,0.5);"><?= htmlspecialchars($currentUser['full_name'] ?? 'Pengunjung') ?></span>
                <span class="user-role-tag" style="color: #f0ddb0; font-size: 0.72rem; opacity: 0.95;"><?= ($currentUser['role'] ?? 'user') === 'admin' ? 'Kurator (Admin)' : 'Pembaca (Hanya Baca)' ?></span>
            </div>
        </div>

        <!-- Vinyl Sound Toggle -->
        <button id="vinyl-toggle-btn" class="vinyl-switch-btn" title="Aktifkan/Matikan Suara Kresek Piringan Hitam Vinyl">
            <span class="vinyl-icon">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor">
                    <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="1.5" fill="none"/>
                    <circle cx="12" cy="12" r="4" stroke="currentColor" stroke-width="1.5" fill="none"/>
                    <circle cx="12" cy="12" r="1.5" fill="currentColor"/>
                </svg>
            </span>
            <span class="vinyl-text">Kresek Vinyl</span>
        </button>

        <?php if (($currentUser['role'] ?? 'user') === 'admin'): ?>
        <!-- Create Album Button (Admin Only) -->
        <button id="btn-add-album" class="btn-add-album admin-action-btn" title="Buat Buku Album Kenangan Baru">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"></path>
                <path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"></path>
                <line x1="12" y1="8" x2="12" y2="14"></line>
                <line x1="9" y1="11" x2="15" y2="11"></line>
            </svg>
            <span>Buat Album</span>
        </button>

        <!-- Add Memory Button (Admin Only) -->
        <button id="btn-add-memory" class="btn-add-memory admin-action-btn" title="Tempelkan Foto Kenangan Baru ke Album">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <line x1="12" y1="5" x2="12" y2="19"></line>
                <line x1="5" y1="12" x2="19" y2="12"></line>
            </svg>
            <span>Tempel Foto</span>
        </button>
        <!-- Admin Feedback Badge Button (Admin Only) -->
        <button id="btn-admin-feedback" class="btn-admin-feedback" type="button" title="Kritik &amp; Saran Pengunjung — Panel Masukan Pengunjung">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
            </svg>
            <span>Masukan</span>
            <span id="admin-feedback-badge" class="feedback-nav-badge is-zero" style="display:none;">0</span>
        </button>
        <?php endif; ?>

        <!-- Logout Button -->
        <button id="btn-logout" class="btn-logout" title="Keluar dari Akun">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
                <polyline points="16 17 21 12 16 7"></polyline>
                <line x1="21" y1="12" x2="9" y2="12"></line>
            </svg>
            <span>Keluar</span>
        </button>
    </div>
</header>
