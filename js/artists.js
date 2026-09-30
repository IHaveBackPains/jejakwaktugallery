/**
 * JS: Halaman Daftar Seniman & Kontributor Arsip "Jejak Waktu"
 * Mengelola rendering kartu profil artist, filter kategori, galeri karya,
 * serta fitur Edit Profil & Unggah/Ganti Foto Profil Seniman (Admin).
 */

'use strict';

let cachedArtistsData = [];
let activeArtistCategory = 'all';

// Referensi URL blob preview untuk dibersihkan setelah digunakan
let currentPreviewBlobUrl = null;

/**
 * Escape HTML untuk keamanan XSS
 */
function safeEscape(str) {
    if (typeof escapeHtml === 'function') {
        return escapeHtml(str);
    }
    if (str === null || str === undefined) return '';
    return String(str)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
}

/**
 * Reset cache agar halaman artist langsung fetch ulang dari API
 * Dipanggil oleh app.js atau setelah update data seniman
 */
function clearArtistsCache() {
    cachedArtistsData = [];
}
window.clearArtistsCache = clearArtistsCache;

/**
 * Inisialisasi Halaman Daftar Artist
 */
async function renderArtistsPage() {
    const container = document.getElementById('artists-grid-container');
    const loading = document.getElementById('artists-loading');
    const empty = document.getElementById('artists-empty');

    if (!container) return;

    if (loading) loading.style.display = 'flex';
    if (empty) empty.style.display = 'none';

    try {
        if (!cachedArtistsData || cachedArtistsData.length === 0) {
            const res = await fetch('api/artists.json');
            const json = await res.json();
            if (json.status === 'success') {
                cachedArtistsData = json.data || [];
            } else {
                throw new Error(json.message || 'Gagal memuat data');
            }
        }
        displayArtists(cachedArtistsData);
    } catch (err) {
        console.warn('Gagal memuat API seniman, menggunakan fallback lokal:', err);
        displayArtists(getLocalArtistsFallback());
    } finally {
        if (loading) loading.style.display = 'none';
    }
}
window.renderArtistsPage = renderArtistsPage;

/**
 * Render Kartu Profil Seniman & Galeri Karya Mini
 */
function displayArtists(artists) {
    const container = document.getElementById('artists-grid-container');
    const empty = document.getElementById('artists-empty');
    if (!container) return;

    const filtered = artists.filter(artist => {
        if (activeArtistCategory === 'all') return true;
        return artist.category === activeArtistCategory;
    });

    if (filtered.length === 0) {
        container.innerHTML = '';
        if (empty) empty.style.display = 'block';
        return;
    }
    if (empty) empty.style.display = 'none';

    const isAdmin = window.CURRENT_USER && window.CURRENT_USER.role === 'admin';

    container.innerHTML = filtered.map(artist => {
        const artworks = artist.artworks || [];
        const galleryHtml = artworks.length > 0 
            ? `<div class="artist-artworks-row">
                <span class="artist-artworks-label">Sumbangsi Karya dalam Arsip:</span>
                <div class="artist-mini-gallery">
                    ${artworks.map(art => `
                        <div class="artist-thumb-card" title="${safeEscape(art.title)} (${art.year || ''}) â€” ${safeEscape(art.album_title || '')}">
                            <img src="${safeEscape(art.thumb || art.src)}" alt="${safeEscape(art.title)}" loading="lazy" />
                            <span class="artist-thumb-title">${safeEscape(art.title)}</span>
                        </div>
                    `).join('')}
                </div>
               </div>`
            : `<div class="artist-no-artworks"><em>Belum ada foto karya yang diunggah khusus untuk seniman ini.</em></div>`;

        const newBadge = artist.is_dynamic
            ? `<span class="artist-new-badge" title="Artist baru yang ditambahkan melalui form Tempel Foto">âœ¨ Kontributor Baru</span>`
            : '';

        // Tombol Edit & Hapus Profil khusus Admin dengan data-artist-id dan onclick
        const adminBar = isAdmin
            ? `<div class="artist-card-admin-bar">
                <button type="button" class="artist-edit-card-btn" data-artist-id="${safeEscape(artist.id)}" onclick="openArtistEditModal('${safeEscape(artist.id)}', event)" title="Edit Data & Ganti Foto Profil">
                    âœï¸ Edit Seniman
                </button>
                <button type="button" class="artist-delete-card-btn" data-artist-id="${safeEscape(artist.id)}" data-artist-name="${safeEscape(artist.name)}" onclick="openArtistDeleteModal('${safeEscape(artist.id)}', event)" title="Hapus Seniman dari Arsip">
                    ðŸ—‘ï¸ Hapus Seniman
                </button>
               </div>`
            : '';

        // Avatar foto atau placeholder
        const avatarHtml = (artist.avatar && artist.avatar.trim() !== '')
            ? `<img src="${safeEscape(artist.avatar)}" alt="${safeEscape(artist.name)}" class="artist-avatar-img" onerror="this.onerror=null; this.src='https://images.unsplash.com/photo-1544005313-94ddf0286df2?auto=format&fit=crop&w=400&q=80';" />`
            : `<div class="artist-edit-avatar-placeholder" style="width:84px;height:84px;">
                 <span class="artist-placeholder-icon" style="font-size:1.5rem;">ðŸ‘¤</span>
                 <span class="artist-placeholder-text">Tanpa Foto</span>
               </div>`;

        return `
            <div class="artist-card-vintage${artist.is_dynamic ? ' artist-card-dynamic' : ''}" data-artist-id="${safeEscape(artist.id)}">
                ${adminBar}
                <div class="artist-card-header">
                    <div class="artist-avatar-wrapper">
                        ${avatarHtml}
                        <span class="artist-era-badge">${safeEscape(artist.era || 'Klasik')}</span>
                    </div>
                    <div class="artist-main-info">
                        <div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap;">
                            <span class="postage-stamp artist-category-tag">${getCategoryLabel(artist.category)}</span>
                            ${newBadge}
                        </div>
                        <h3 class="artist-name">${safeEscape(artist.name)}</h3>
                        <p class="artist-role-title">ðŸ“œ ${safeEscape(artist.role)}</p>
                        <p class="artist-location">ðŸ“ Dominasi Wilayah Karya: <strong>${safeEscape(artist.location || 'Nusantara')}</strong></p>
                    </div>
                </div>

                <div class="artist-bio-box">
                    <p>${safeEscape(artist.bio)}</p>
                </div>

                ${galleryHtml}
            </div>
        `;
    }).join('');
}
window.displayArtists = displayArtists;

function getCategoryLabel(cat) {
    switch (cat) {
        case 'pelukis': return 'ðŸŽ¨ Pelukis & Maestro';
        case 'fotografer': return 'ðŸ“· Fotografer Dokumen';
        case 'kurator': return 'ðŸ“– Kurator Budaya';
        case 'arsiparis': return 'ðŸ› Arsiparis Sejarah';
        default: return 'ðŸ“œ Kontributor Seni';
    }
}
window.getCategoryLabel = getCategoryLabel;

/**
 * Filter Seniman berdasarkan Kategori
 */
function filterArtistsCategory(cat, btnElem) {
    activeArtistCategory = cat;
    document.querySelectorAll('.artist-cat-pill').forEach(b => b.classList.remove('active'));
    if (btnElem) btnElem.classList.add('active');
    displayArtists(cachedArtistsData);
}
window.filterArtistsCategory = filterArtistsCategory;

// ========================================================
// FITUR: EDIT PROFIL & FOTO SENIMAN (ADMIN)
// ========================================================

/**
 * Buka Modal Edit Seniman & Isi Data
 */
function openArtistEditModal(artistId, event) {
    if (event) {
        event.preventDefault();
        event.stopPropagation();
    }

    if (!artistId) {
        console.warn('openArtistEditModal: artistId kosong');
        return;
    }

    // 1. Cari data seniman di cache
    let artist = cachedArtistsData.find(a => String(a.id) === String(artistId));

    // 2. Fallback jika data belum di cache: ambil langsung dari DOM kartu seniman
    if (!artist) {
        const card = document.querySelector(`.artist-card-vintage[data-artist-id="${artistId}"]`);
        if (card) {
            const nameEl = card.querySelector('.artist-name');
            const roleEl = card.querySelector('.artist-role-title');
            const locEl = card.querySelector('.artist-location strong');
            const bioEl = card.querySelector('.artist-bio-box p');
            const imgEl = card.querySelector('.artist-avatar-img');
            const eraEl = card.querySelector('.artist-era-badge');

            artist = {
                id: artistId,
                name: nameEl ? nameEl.textContent.trim() : artistId,
                role: roleEl ? roleEl.textContent.replace(/^ðŸ“œ\s*/, '').trim() : '',
                location: locEl ? locEl.textContent.trim() : 'Nusantara',
                bio: bioEl ? bioEl.textContent.trim() : '',
                avatar: imgEl ? imgEl.src : '',
                era: eraEl ? eraEl.textContent.trim() : 'Klasik',
                category: 'pelukis'
            };
        }
    }

    // 3. Fallback kedua: cari di daftar fallback lokal
    if (!artist) {
        const fallbackList = getLocalArtistsFallback();
        artist = fallbackList.find(a => String(a.id) === String(artistId));
    }

    if (!artist) {
        alert('Data seniman tidak ditemukan. Silakan segarkan halaman.');
        return;
    }

    const modal = document.getElementById('artist-edit-modal');
    if (!modal) {
        console.error('Modal #artist-edit-modal tidak ditemukan di halaman');
        return;
    }

    // Reset status & preview foto sebelumnya
    cancelArtistPhotoChange();

    // Isi nilai form
    const idInput = document.getElementById('edit-artist-id');
    const curAvatarInput = document.getElementById('edit-artist-current-avatar');
    const nameInput = document.getElementById('edit-artist-name');
    const roleInput = document.getElementById('edit-artist-role');
    const eraInput = document.getElementById('edit-artist-era');
    const catInput = document.getElementById('edit-artist-category');
    const locInput = document.getElementById('edit-artist-location');
    const bioInput = document.getElementById('edit-artist-bio');

    if (idInput) idInput.value = artist.id;
    if (curAvatarInput) curAvatarInput.value = artist.avatar || '';
    if (nameInput) nameInput.value = artist.name || '';
    if (roleInput) roleInput.value = artist.role || '';
    if (eraInput) eraInput.value = artist.era || '';
    if (catInput) catInput.value = artist.category || 'pelukis';
    if (locInput) locInput.value = artist.location || '';
    if (bioInput) bioInput.value = artist.bio || '';

    // Tampilkan foto profil saat ini atau placeholder
    const currentImg = document.getElementById('edit-artist-current-img');
    const placeholder = document.getElementById('edit-artist-placeholder');
    const badgeStatus = document.getElementById('edit-artist-badge-status');

    if (badgeStatus) {
        badgeStatus.textContent = 'Foto Saat Ini';
        badgeStatus.classList.remove('is-new-photo');
    }

    if (artist.avatar && artist.avatar.trim() !== '') {
        if (currentImg) {
            currentImg.src = artist.avatar;
            currentImg.style.display = 'block';
        }
        if (placeholder) placeholder.style.display = 'none';
    } else {
        if (currentImg) {
            currentImg.src = '';
            currentImg.style.display = 'none';
        }
        if (placeholder) placeholder.style.display = 'flex';
    }

    // Buka modal secara eksplisit dengan class dan inline styles
    modal.classList.add('is-open');
    modal.classList.add('active');
    modal.setAttribute('aria-hidden', 'false');
    modal.style.display = 'flex';
    modal.style.opacity = '1';
    modal.style.pointerEvents = 'auto';
    modal.style.visibility = 'visible';
    modal.style.zIndex = '10000';
    document.body.style.overflow = 'hidden';
}
window.openArtistEditModal = openArtistEditModal;

/**
 * Tutup Modal Edit Seniman
 */
function closeArtistEditModal() {
    const modal = document.getElementById('artist-edit-modal');
    if (!modal) return;

    cancelArtistPhotoChange();
    modal.classList.remove('is-open');
    modal.classList.remove('active');
    modal.setAttribute('aria-hidden', 'true');
    modal.style.display = 'none';
    modal.style.opacity = '0';
    modal.style.pointerEvents = 'none';
    document.body.style.overflow = '';
}
window.closeArtistEditModal = closeArtistEditModal;

/**
 * Buka Modal Konfirmasi Hapus Seniman & Isi Data
 */
function openArtistDeleteModal(artistId, event) {
    if (event) {
        event.preventDefault();
        event.stopPropagation();
    }

    if (!artistId) {
        console.warn('openArtistDeleteModal: artistId kosong');
        return;
    }

    // 1. Cari data seniman di cache
    let artist = cachedArtistsData.find(a => String(a.id) === String(artistId));

    // 2. Fallback jika data belum di cache: ambil langsung dari DOM kartu seniman
    if (!artist) {
        const card = document.querySelector(`.artist-card-vintage[data-artist-id="${artistId}"]`);
        if (card) {
            const nameEl = card.querySelector('.artist-name');
            artist = {
                id: artistId,
                name: nameEl ? nameEl.textContent.trim() : artistId
            };
        }
    }

    // 3. Fallback kedua: cari di daftar fallback lokal
    if (!artist) {
        const fallbackList = getLocalArtistsFallback();
        artist = fallbackList.find(a => String(a.id) === String(artistId));
    }

    const artistName = artist ? (artist.name || artistId) : artistId;

    const modal = document.getElementById('artist-delete-modal');
    if (!modal) {
        console.error('Modal #artist-delete-modal tidak ditemukan di halaman');
        if (confirm(`Yakin ingin menghapus seniman ini? Data seniman dan foto profilnya akan ikut dihapus.\n\nSeniman: ${artistName}`)) {
            confirmDeleteArtistDirect(artistId);
        }
        return;
    }

    const idInput = document.getElementById('delete-artist-id');
    const nameDisplay = document.getElementById('artist-delete-target-name');
    const confirmBtn = document.getElementById('artist-delete-confirm-btn');
    const cancelBtn = document.getElementById('artist-delete-cancel-btn');

    if (idInput) idInput.value = artistId;
    if (nameDisplay) {
        nameDisplay.textContent = `Seniman: ${artistName}`;
    }
    if (confirmBtn) {
        confirmBtn.disabled = false;
        confirmBtn.innerHTML = 'ðŸ—‘ï¸ Hapus';
    }
    if (cancelBtn) {
        cancelBtn.disabled = false;
    }

    // Buka modal secara eksplisit
    modal.classList.add('is-open');
    modal.classList.add('active');
    modal.setAttribute('aria-hidden', 'false');
    modal.style.display = 'flex';
    modal.style.opacity = '1';
    modal.style.pointerEvents = 'auto';
    modal.style.visibility = 'visible';
    modal.style.zIndex = '10001';
    document.body.style.overflow = 'hidden';
}
window.openArtistDeleteModal = openArtistDeleteModal;

/**
 * Tutup Modal Konfirmasi Hapus Seniman
 */
function closeArtistDeleteModal() {
    const modal = document.getElementById('artist-delete-modal');
    if (!modal) return;

    modal.classList.remove('is-open');
    modal.classList.remove('active');
    modal.setAttribute('aria-hidden', 'true');
    modal.style.display = 'none';
    modal.style.opacity = '0';
    modal.style.pointerEvents = 'none';
    document.body.style.overflow = '';
}
window.closeArtistDeleteModal = closeArtistDeleteModal;

/**
 * Eksekusi Penghapusan Seniman ke Backend (Admin Only)
 */
async function confirmDeleteArtist() {
    const idInput = document.getElementById('delete-artist-id');
    const artistId = idInput ? idInput.value.trim() : '';

    if (!artistId) {
        alert('ID seniman tidak valid.');
        closeArtistDeleteModal();
        return;
    }

    const confirmBtn = document.getElementById('artist-delete-confirm-btn');
    const cancelBtn = document.getElementById('artist-delete-cancel-btn');
    const originalBtnText = confirmBtn ? confirmBtn.innerHTML : 'ðŸ—‘ï¸ Hapus';

    if (confirmBtn) {
        confirmBtn.disabled = true;
        confirmBtn.innerHTML = 'â³ Menghapus...';
    }
    if (cancelBtn) {
        cancelBtn.disabled = true;
    }

    try {
        const formData = new URLSearchParams();
        formData.append('artist_id', artistId);
        formData.append('action', 'delete');
        formData.append('_method', 'DELETE');

        const res = await fetch('api/artists.json?action=delete', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                'Accept': 'application/json'
            },
            body: formData
        });

        const json = await res.json();

        if (json.status === 'success') {
            // Tutup modal konfirmasi
            closeArtistDeleteModal();

            // Tampilkan notifikasi toast vintage
            showArtistToast(json.message || 'Seniman berhasil dihapus.');

            // Bersihkan cache seniman
            clearArtistsCache();

            // Refresh tampilan daftar seniman secara asinkron tanpa reload halaman
            await renderArtistsPage();
        } else {
            // Tampilkan pesan error dan JANGAN menghilangkan data dari tampilan
            alert('Gagal menghapus: ' + (json.message || 'Terjadi kesalahan sistem'));
            if (confirmBtn) {
                confirmBtn.disabled = false;
                confirmBtn.innerHTML = originalBtnText;
            }
            if (cancelBtn) {
                cancelBtn.disabled = false;
            }
        }
    } catch (err) {
        console.error('Error saat menghapus data seniman:', err);
        alert('Terjadi kesalahan koneksi saat menghapus data seniman.');
        if (confirmBtn) {
            confirmBtn.disabled = false;
            confirmBtn.innerHTML = originalBtnText;
        }
        if (cancelBtn) {
            cancelBtn.disabled = false;
        }
    }
}
window.confirmDeleteArtist = confirmDeleteArtist;

/**
 * Batalkan pilihan foto baru dan kembalikan ke foto profil saat ini
 */
function cancelArtistPhotoChange() {
    const fileInput = document.getElementById('edit-artist-file-input');
    if (fileInput) fileInput.value = '';

    if (currentPreviewBlobUrl) {
        URL.revokeObjectURL(currentPreviewBlobUrl);
        currentPreviewBlobUrl = null;
    }

    const currentAvatar = document.getElementById('edit-artist-current-avatar')
        ? document.getElementById('edit-artist-current-avatar').value
        : '';
    const currentImg = document.getElementById('edit-artist-current-img');
    const placeholder = document.getElementById('edit-artist-placeholder');
    const badgeStatus = document.getElementById('edit-artist-badge-status');
    const cancelBtn = document.getElementById('edit-artist-cancel-photo-btn');
    const previewBox = document.getElementById('edit-artist-new-preview-box');

    if (cancelBtn) cancelBtn.style.display = 'none';
    if (previewBox) previewBox.style.display = 'none';

    if (badgeStatus) {
        badgeStatus.textContent = 'Foto Saat Ini';
        badgeStatus.classList.remove('is-new-photo');
    }

    if (currentAvatar && currentAvatar.trim() !== '') {
        if (currentImg) {
            currentImg.src = currentAvatar;
            currentImg.style.display = 'block';
        }
        if (placeholder) placeholder.style.display = 'none';
    } else {
        if (currentImg) {
            currentImg.src = '';
            currentImg.style.display = 'none';
        }
        if (placeholder) placeholder.style.display = 'flex';
    }
}
window.cancelArtistPhotoChange = cancelArtistPhotoChange;

/**
 * Setup Event Listeners untuk Formulir Edit Artist & Upload Foto serta Modal Hapus
 */
function initArtistEditEvents() {
    const fileInput = document.getElementById('edit-artist-file-input');
    const form = document.getElementById('artist-edit-form');
    const modal = document.getElementById('artist-edit-modal');
    const deleteModal = document.getElementById('artist-delete-modal');

    // Tutup saat mengklik backdrop luar modal edit
    if (modal) {
        modal.addEventListener('click', function(e) {
            if (e.target === modal) {
                closeArtistEditModal();
            }
        });
    }

    // Tutup saat mengklik backdrop luar modal hapus
    if (deleteModal) {
        deleteModal.addEventListener('click', function(e) {
            if (e.target === deleteModal) {
                closeArtistDeleteModal();
            }
        });
    }

    // Tutup modal dengan tombol Escape
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            const m = document.getElementById('artist-edit-modal');
            if (m && (m.classList.contains('is-open') || m.classList.contains('active') || m.style.display === 'flex')) {
                closeArtistEditModal();
            }
            const dm = document.getElementById('artist-delete-modal');
            if (dm && (dm.classList.contains('is-open') || dm.classList.contains('active') || dm.style.display === 'flex')) {
                closeArtistDeleteModal();
            }
        }
    });

    // Event Delegation: Menjamin klik pada tombol .artist-edit-card-btn & .artist-delete-card-btn selalu tertangkap
    document.addEventListener('click', function(e) {
        const editBtn = e.target.closest('.artist-edit-card-btn');
        if (editBtn) {
            e.preventDefault();
            e.stopPropagation();
            const artistId = editBtn.getAttribute('data-artist-id');
            if (artistId) {
                openArtistEditModal(artistId, e);
            }
            return;
        }

        const delBtn = e.target.closest('.artist-delete-card-btn');
        if (delBtn) {
            e.preventDefault();
            e.stopPropagation();
            const artistId = delBtn.getAttribute('data-artist-id');
            if (artistId) {
                openArtistDeleteModal(artistId, e);
            }
            return;
        }
    });

    // 1. Live Preview saat Admin memilih foto baru
    if (fileInput) {
        fileInput.addEventListener('change', function(e) {
            const file = this.files && this.files[0];
            if (!file) return;

            // Validasi Ukuran (Maks 5MB)
            const maxBytes = 5 * 1024 * 1024;
            if (file.size > maxBytes) {
                alert('Ukuran file foto profil terlalu besar. Maksimal 5 MB.');
                cancelArtistPhotoChange();
                return;
            }

            // Validasi Format Gambar
            const allowedTypes = ['image/jpeg', 'image/png', 'image/webp'];
            if (!allowedTypes.includes(file.type)) {
                alert('Format file tidak didukung. Harap pilih gambar dengan format JPG, JPEG, PNG, atau WebP.');
                cancelArtistPhotoChange();
                return;
            }

            // Tampilkan Live Preview Foto Baru
            if (currentPreviewBlobUrl) {
                URL.revokeObjectURL(currentPreviewBlobUrl);
            }
            currentPreviewBlobUrl = URL.createObjectURL(file);

            const currentImg = document.getElementById('edit-artist-current-img');
            const placeholder = document.getElementById('edit-artist-placeholder');
            const badgeStatus = document.getElementById('edit-artist-badge-status');
            const cancelBtn = document.getElementById('edit-artist-cancel-photo-btn');
            const previewBox = document.getElementById('edit-artist-new-preview-box');
            const fileNameEl = document.getElementById('edit-artist-file-name');

            if (currentImg) {
                currentImg.src = currentPreviewBlobUrl;
                currentImg.style.display = 'block';
            }
            if (placeholder) placeholder.style.display = 'none';

            if (badgeStatus) {
                badgeStatus.textContent = 'âœ¨ Pratinjau Foto Baru';
                badgeStatus.classList.add('is-new-photo');
            }

            if (cancelBtn) cancelBtn.style.display = 'inline-block';
            if (previewBox) previewBox.style.display = 'flex';
            if (fileNameEl) {
                const sizeMb = (file.size / (1024 * 1024)).toFixed(2);
                fileNameEl.textContent = `${file.name} (${sizeMb} MB)`;
            }
        });
    }

    // 2. Submit form edit artist (AJAX multipart/form-data)
    if (form) {
        form.addEventListener('submit', async function(e) {
            e.preventDefault();

            const submitBtn = document.getElementById('edit-artist-submit-btn');
            const originalBtnText = submitBtn ? submitBtn.innerHTML : 'âœ“ Simpan Perubahan Profil';

            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.innerHTML = 'â³ Menyimpan Perubahan...';
            }

            try {
                const formData = new FormData(form);
                const res = await fetch('api/artists.json', {
                    method: 'POST',
                    body: formData
                });

                const json = await res.json();

                if (json.status === 'success') {
                    // Tampilkan notifikasi berhasil
                    showArtistToast(json.message || 'Profil seniman berhasil diperbarui!');

                    // Reset cache agar data terupdate diambil kembali
                    clearArtistsCache();

                    // Tutup modal
                    closeArtistEditModal();

                    // Render ulang kartu seniman
                    await renderArtistsPage();
                } else {
                    alert('Gagal menyimpan: ' + (json.message || 'Terjadi kesalahan sistem'));
                }
            } catch (err) {
                console.error('Error saat menyimpan profil seniman:', err);
                alert('Terjadi kesalahan koneksi saat menyimpan profil seniman.');
            } finally {
                if (submitBtn) {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalBtnText;
                }
            }
        });
    }
}

/**
 * Notifikasi Toast Vintage Sederhana
 */
function showArtistToast(msg) {
    let toast = document.getElementById('artist-vintage-toast');
    if (!toast) {
        toast = document.createElement('div');
        toast.id = 'artist-vintage-toast';
        toast.style.cssText = `
            position: fixed;
            bottom: 24px;
            right: 24px;
            background: #2b1d16;
            color: #f7e6c4;
            border: 2px solid #b38241;
            padding: 12px 20px;
            border-radius: 6px;
            font-family: var(--font-typewriter);
            font-size: 0.85rem;
            box-shadow: 0 6px 20px rgba(0,0,0,0.35);
            z-index: 99999;
            display: flex;
            align-items: center;
            gap: 10px;
            transition: opacity 0.3s ease, transform 0.3s ease;
        `;
        document.body.appendChild(toast);
    }
    toast.innerHTML = `<span>âœ¨</span><span>${safeEscape(msg)}</span>`;
    toast.style.opacity = '1';
    toast.style.transform = 'translateY(0)';

    setTimeout(() => {
        toast.style.opacity = '0';
        toast.style.transform = 'translateY(10px)';
    }, 3500);
}

// Inisialisasi event saat DOM selesai dimuat
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initArtistEditEvents);
} else {
    initArtistEditEvents();
}

/**
 * Fallback Lokal jika API Offline
 */
function getLocalArtistsFallback() {
    return [
        {
            id: 'raden-saleh',
            name: 'Raden Saleh Syarif Bustaman',
            role: 'Pelukis Maestro & Pioneer Seni Rupa Modern Nusantara',
            era: '1850-1880',
            category: 'pelukis',
            avatar: 'https://images.unsplash.com/photo-1544005313-94ddf0286df2?auto=format&fit=crop&w=400&q=80',
            bio: 'Pelopor seni rupa modern Indonesia berkebangsaan Jawa yang memadukan romantisisme Eropa dengan panorama dan satwa liar Nusantara.',
            location: 'Semarang & Batavia',
            artworks: []
        },
        {
            id: 'bambang-soetjipto',
            name: 'Arsiparis Bambang S. / Bambang Soetjipto',
            role: 'Fotografer Dokumenter Lanskap Kota Batavia & Jakarta',
            era: '1970-1985',
            category: 'fotografer',
            avatar: 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?auto=format&fit=crop&w=400&q=80',
            bio: 'Kurator senior yang mendedikasikan hidupnya merekam dinamika pembangunan Ibukota Jakarta dari era trem listrik hingga munculnya gedung pencakar langit.',
            location: 'DKI Jakarta',
            artworks: []
        }
    ];
}

