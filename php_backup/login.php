<?php
/**
 * Halaman Login Vintage "Jejak Waktu"
 * Autentikasi & Otorisasi RBAC (Admin & User)
 */

require_once __DIR__ . '/api/session.php';

// Jika pengguna sudah login, langsung alihkan ke halaman utama
$currentUser = get_current_user_session();
if ($currentUser) {
    header('Location: index.php');
    exit();
}

$errorMessage = '';
if (isset($_GET['error'])) {
    if ($_GET['error'] === 'unauthenticated') {
        $errorMessage = 'Akses Terbatas: Anda wajib masuk (login) terlebih dahulu untuk membuka lembaran arsip.';
    } elseif ($_GET['error'] === 'forbidden') {
        $errorMessage = 'Akses Ditolak: Hak akses Anda tidak mencukupi untuk membuka halaman tersebut.';
    } else {
        $errorMessage = htmlspecialchars($_GET['error']);
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Masuk Arsip — Jejak Waktu (Galeri Kenangan)</title>
    <meta name="description" content="Masuk ke portal digital Jejak Waktu untuk merawat kenangan dan menjelajahi sejarah.">
    <link rel="icon" href="data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 100 100%22><text y=%22.9em%22 font-size=%2290%22>📜</text></svg>">

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Caveat:wght@500;700&family=Cormorant+Garamond:ital,wght@0,400;0,600;0,700;1,400&family=Lora:ital,wght@0,400;0,600;1,400&family=Playfair+Display:ital,wght@0,600;0,800;1,400;1,700&family=Special+Elite&display=swap">

    <!-- Stylesheets -->
    <link rel="stylesheet" href="css/vintage.css">
    <link rel="stylesheet" href="css/animations.css">
    <link rel="stylesheet" href="css/style.css">

    <style>
        .login-wrapper {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px 16px;
        }

        .login-book-binder {
            background: #42291d;
            background-image: linear-gradient(135deg, #4d2f21 0%, #2f1b12 100%);
            border-radius: 12px;
            padding: 20px;
            max-width: 480px;
            width: 100%;
            position: relative;
            box-shadow: 0 25px 60px rgba(0, 0, 0, 0.8), 0 0 0 1px rgba(255, 255, 255, 0.1);
        }

        .login-paper-sheet {
            background-color: var(--color-paper-light);
            background-image: 
                radial-gradient(ellipse at center, rgba(255, 255, 255, 0.6) 0%, rgba(235, 220, 196, 0.4) 100%),
                repeating-linear-gradient(0deg, transparent, transparent 24px, rgba(180, 150, 110, 0.06) 25px);
            border: 1px solid #c9b798;
            border-radius: 6px;
            padding: 36px 32px;
            position: relative;
            box-shadow: inset 0 0 40px rgba(180, 140, 90, 0.15);
        }

        .login-header {
            text-align: center;
            border-bottom: 2px dashed #cbba9d;
            padding-bottom: 20px;
            margin-bottom: 24px;
            position: relative;
        }

        .login-wax-seal {
            margin: 0 auto 12px auto;
        }

        .login-title {
            font-family: var(--font-heading);
            font-size: 2.3rem;
            color: var(--color-leather-dark);
            margin-bottom: 4px;
            line-height: 1.15;
        }

        .login-sub {
            font-family: var(--font-typewriter);
            font-size: 0.85rem;
            color: var(--color-ink-sepia);
            letter-spacing: 1px;
        }

        .login-alert-box {
            background: #fdf1ed;
            border: 1px solid #e09f95;
            border-left: 4px solid var(--color-stamp-red);
            padding: 10px 14px;
            border-radius: 4px;
            font-family: var(--font-typewriter);
            font-size: 0.82rem;
            color: #8b1e12;
            margin-bottom: 20px;
            display: none;
            align-items: center;
            gap: 8px;
            animation: fadeInAged 0.3s ease-out;
        }
        .login-alert-box.is-active {
            display: flex;
        }

        .quick-login-section {
            margin-top: 24px;
            padding-top: 20px;
            border-top: 1px dashed #cbba9d;
            text-align: center;
        }

        .quick-login-title {
            font-family: var(--font-typewriter);
            font-size: 0.8rem;
            color: var(--color-ink-faded);
            margin-bottom: 12px;
            text-transform: uppercase;
            letter-spacing: 1.5px;
        }

        .quick-btn-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
        }

        .quick-role-btn {
            background: #ebdcc4;
            border: 1px solid #cbb898;
            color: var(--color-ink-dark);
            padding: 9px 8px;
            border-radius: 4px;
            font-family: var(--font-typewriter);
            font-size: 0.78rem;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.2s ease;
            box-shadow: 0 2px 4px rgba(0,0,0,0.08);
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 3px;
        }

        .quick-role-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.18);
        }

        .quick-btn-admin:hover {
            background: #2b1d16;
            color: var(--color-gold-bright);
            border-color: var(--color-gold-foil);
        }

        .quick-btn-user:hover {
            background: #304f69;
            color: #fff;
            border-color: #203649;
        }

        .quick-role-btn small {
            font-size: 0.7rem;
            font-weight: normal;
            opacity: 0.85;
        }
    </style>
</head>
<body class="vintage-paper-bg login-wrapper">

    <div class="login-book-binder">
        <!-- Brass Corners -->
        <div class="brass-corner corner-top-left"></div>
        <div class="brass-corner corner-top-right"></div>
        <div class="brass-corner corner-bottom-left"></div>
        <div class="brass-corner corner-bottom-right"></div>
        <div class="album-stitching"></div>

        <div class="login-paper-sheet">
            <!-- Header Stamp & Seal -->
            <div class="login-header">
                <div class="wax-seal login-wax-seal" title="Segel Lilin Jejak Waktu">
                    JW
                </div>
                <span class="postage-stamp" style="font-size: 0.65rem; margin-bottom: 6px;">GERBANG OTORISASI ARSIP</span>
                <h1 class="login-title">Jejak Waktu</h1>
                <p class="login-sub">&mdash; Buka Kunci Akses Memori Sejarah &mdash;</p>
            </div>

            <!-- Error Banner -->
            <div id="login-alert" class="login-alert-box <?= !empty($errorMessage) ? 'is-active' : '' ?>">
                <span>⚠️</span>
                <span id="login-alert-text"><?= $errorMessage ?></span>
            </div>

            <!-- Login Form -->
            <form id="login-form">
                <div class="vintage-form-group">
                    <label for="login-username">Nama Pengguna (Username):</label>
                    <input 
                        type="text" 
                        id="login-username" 
                        name="username" 
                        class="vintage-input" 
                        placeholder="Masukkan nama pengguna..." 
                        required 
                        autocomplete="username"
                    />
                </div>

                <div class="vintage-form-group">
                    <label for="login-password">Kata Sandi (Password):</label>
                    <input 
                        type="password" 
                        id="login-password" 
                        name="password" 
                        class="vintage-input" 
                        placeholder="Masukkan kata sandi..." 
                        required 
                        autocomplete="current-password"
                    />
                </div>

                <button type="submit" id="btn-login-submit" class="vintage-btn-submit" style="margin-top: 14px;">
                    🔓 Masuk ke Dalam Lembaran Arsip
                </button>
            </form>

            <!-- Quick One-Click Demo Login -->
            <div class="quick-login-section">
                <div class="quick-login-title">Akses Cepat Pengujian (Demo):</div>
                <div class="quick-btn-grid">
                    <button type="button" id="btn-quick-admin" class="quick-role-btn quick-btn-admin" title="Masuk dengan hak akses penuh (Admin)">
                        <span>👑 Akun Admin</span>
                        <small>admin / admin123</small>
                    </button>
                    <button type="button" id="btn-quick-user" class="quick-role-btn quick-btn-user" title="Masuk dengan hak akses hanya-baca (User)">
                        <span>📖 Akun User</span>
                        <small>user / user123 (Read-Only)</small>
                    </button>
                </div>
            </div>

            <div style="margin-top: 20px; text-align: center; font-family: var(--font-typewriter); font-size: 0.75rem; color: var(--color-ink-faded);">
                Jejak Waktu &copy; 2026. Merawat Kenangan, Mengabadikan Sejarah.
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="js/audio.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const loginForm = document.getElementById('login-form');
            const usernameInput = document.getElementById('login-username');
            const passwordInput = document.getElementById('login-password');
            const submitBtn = document.getElementById('btn-login-submit');
            const alertBox = document.getElementById('login-alert');
            const alertText = document.getElementById('login-alert-text');

            const btnAdmin = document.getElementById('btn-quick-admin');
            const btnUser = document.getElementById('btn-quick-user');

            // Quick demo buttons
            if (btnAdmin) {
                btnAdmin.addEventListener('click', () => {
                    usernameInput.value = 'admin';
                    passwordInput.value = 'admin123';
                    if (typeof vintageSound !== 'undefined' && vintageSound.playPageFlip) vintageSound.playPageFlip();
                    loginForm.dispatchEvent(new Event('submit'));
                });
            }

            if (btnUser) {
                btnUser.addEventListener('click', () => {
                    usernameInput.value = 'user';
                    passwordInput.value = 'user123';
                    if (typeof vintageSound !== 'undefined' && vintageSound.playPageFlip) vintageSound.playPageFlip();
                    loginForm.dispatchEvent(new Event('submit'));
                });
            }

            // Form Submit
            loginForm.addEventListener('submit', async (e) => {
                e.preventDefault();

                const username = usernameInput.value.trim();
                const password = passwordInput.value.trim();

                if (!username || !password) return;

                submitBtn.disabled = true;
                submitBtn.innerHTML = '⏳ Membuka Segel Arsip...';
                alertBox.classList.remove('is-active');

                const formData = new FormData();
                formData.append('action', 'login');
                formData.append('username', username);
                formData.append('password', password);

                try {
                    const response = await fetch('api/auth.php', {
                        method: 'POST',
                        body: formData
                    });

                    const result = await response.json();

                    if (response.ok && result.status === 'success') {
                        if (typeof vintageSound !== 'undefined' && vintageSound.playShutter) {
                            vintageSound.playShutter();
                        }
                        submitBtn.innerHTML = '✨ Berhasil! Membuka Lembaran...';
                        setTimeout(() => {
                            window.location.href = 'index.php';
                        }, 400);
                        return;
                    } else {
                        alertText.textContent = result.message || 'Nama pengguna atau kata sandi tidak valid.';
                        alertBox.classList.add('is-active');
                    }
                } catch (err) {
                    alertText.textContent = 'Koneksi ke server gagal. Pastikan Apache/PHP aktif.';
                    alertBox.classList.add('is-active');
                }

                submitBtn.disabled = false;
                submitBtn.innerHTML = '🔓 Masuk ke Dalam Lembaran Arsip';
            });
        });
    </script>
</body>
</html>
