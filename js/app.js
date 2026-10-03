/**
 * Main Application Logic "Jejak Waktu"
 * Audit & Refactoring Total:
 * 1. Scope Deklarasi Fungsi Top-Level (Hoisted & window attached)
 * 2. Event Delegation pada Document / Root Container
 * 3. Safe-guard Initialization pada DOMContentLoaded
 * 4. Penuh sinkronisasi MySQL phpMyAdmin & Lightbox Interaktif
 */

// ====================================================
// GLOBAL STATE & SCOPE VARIABEL
// ====================================================
let currentTab = 'beranda';
let currentAlbumId = null;
let selectedDecade = 'all';
let searchQuery = (typeof window !== 'undefined' && window.SEARCH_QUERY) ? window.SEARCH_QUERY : '';

function escapeHtml(str) {
    if (!str) return '';
    return String(str)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
}

// ====================================================
// 1. DEKLARASI FUNGSI UTAMA (TOP-LEVEL SCOPE)
// ====================================================

/**
 * Mengubah Tampilan Tab Halaman (Beranda, Galeri, Tentang)
 */
function switchTab(tabId) {
    if (!tabId) return;
    const activeTabElem = document.getElementById(`tab-${tabId}`);
    if (currentTab === tabId && activeTabElem && activeTabElem.classList.contains('active')) {
        return;
    }
    currentTab = tabId;

    if (typeof vintageSound !== 'undefined' && vintageSound.playPageFlip) {
        vintageSound.playPageFlip();
    }

    // Update Tombol Tab Navigasi
    const tabs = document.querySelectorAll('.book-tab');
    tabs.forEach(tab => {
        if (tab.getAttribute('data-tab') === tabId) {
            tab.classList.add('active');
        } else {
            tab.classList.remove('active');
        }
    });

    // Update Konten Tab
    const tabContents = document.querySelectorAll('.tab-content');
    tabContents.forEach(content => {
        if (content.id === `tab-${tabId}`) {
            content.classList.add('active');
        } else {
            content.classList.remove('active');
        }
    });

    window.scrollTo({ top: 0, behavior: 'smooth' });

    if (tabId === 'galeri') {
        renderAlbumsPage();
    } else if (tabId === 'events') {
        if (typeof loadAllEvents === 'function') {
            loadAllEvents();
        }
        if (typeof loadMyBookings === 'function') {
            loadMyBookings();
        }
    } else if (tabId === 'artists') {
        if (typeof renderArtistsPage === 'function') {
            renderArtistsPage();
        }
    } else if (tabId === 'feedback-admin') {
        if (typeof loadAdminFeedbacks === 'function') {
            loadAdminFeedbacks();
        }
    }
}

/**
 * Membuka Halaman Detail Album & Grid Foto Polaroid
 */
function openAlbumDetail(albumId) {
    if (!albumId) return;
    const album = getAlbumById(albumId);
    if (!album) {
        console.warn('Album tidak ditemukan:', albumId);
        switchTab('galeri');
        return;
    }

    const detailTab = document.getElementById('tab-album-detail');
    const isAlreadyOpen = currentAlbumId === albumId && currentTab === 'album-detail' && detailTab && detailTab.classList.contains('active');
    if (isAlreadyOpen) return;

    currentAlbumId = albumId;
    currentTab = 'album-detail';

    if (window.location.hash !== `#album/${albumId}`) {
        window.location.hash = `album/${albumId}`;
    }

    if (typeof vintageSound !== 'undefined' && vintageSound.playPageFlip) {
        vintageSound.playPageFlip();
    }

    // Sembunyikan seluruh tab dan aktifkan tab detail album
    const tabContents = document.querySelectorAll('.tab-content');
    tabContents.forEach(c => c.classList.remove('active'));

    const tabs = document.querySelectorAll('.book-tab');
    tabs.forEach(tab => tab.classList.remove('active'));

    if (detailTab) {
        detailTab.classList.add('active');
    }

    // Isi metadata album secara aman
    const setElemText = (id, text) => {
        const el = document.getElementById(id);
        if (el) el.textContent = text;
    };

    setElemText('detail-album-title', album.title || 'Album Kenangan');
    setElemText('detail-album-subtitle', album.subtitle || '');
    setElemText('detail-album-desc', album.description || '');
    setElemText('detail-album-era', `Era: ${album.era || 'Klasik'}`);
    setElemText('detail-album-loc', album.location || 'Indonesia');
    setElemText('detail-album-curator', album.curator || 'Arsiparis');
    setElemText('detail-album-count', `${album.photos ? album.photos.length : 0} Foto Kenangan`);

    // Render grid foto polaroid
    const photosGrid = document.getElementById('album-photos-grid');
    if (photosGrid) {
        if (album.photos && album.photos.length > 0) {
            photosGrid.innerHTML = album.photos.map((photo, index) => {
                return createPolaroidCardHtml(photo, album, index);
            }).join('');
        } else {
            photosGrid.innerHTML = `
                <div style="grid-column: 1/-1; text-align: center; padding: 40px; font-family: var(--font-typewriter); color: var(--color-ink-faded);">
                    <p>Belum ada foto yang ditempelkan pada album ini.</p>
                </div>
            `;
        }
    }

    enforceRoleUI();
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

/**
 * Render Halaman Depan (Beranda)
 */
function renderHomePage() {
    const searchStatus = document.getElementById('hero-search-status');
    const searchKeywordDisplay = document.getElementById('search-keyword-display');
    const searchAlbumsCountDisplay = document.getElementById('search-albums-count-display');
    const searchPhotosCountDisplay = document.getElementById('search-photos-count-display');
    const globalSearchResults = document.getElementById('global-search-results');
    const homeNormalContent = document.getElementById('home-normal-content');
    const searchEmptyState = document.getElementById('search-empty-state');
    const searchAlbumsSection = document.getElementById('search-albums-section');
    const searchAlbumsGrid = document.getElementById('search-albums-grid');
    const searchAlbumsCount = document.getElementById('search-albums-count');
    const searchPhotosSection = document.getElementById('search-photos-section');
    const searchPhotosGrid = document.getElementById('search-photos-grid');
    const searchPhotosCount = document.getElementById('search-photos-count');

    const searchInput = document.getElementById('hero-search-input');
    if (searchInput && window.SEARCH_QUERY) {
        searchInput.value = window.SEARCH_QUERY;
    }

    if (typeof window !== 'undefined' && window.SEARCH_QUERY) {
        const query = window.SEARCH_QUERY.toLowerCase().trim();
        const allAlbums = getAlbumsData();

        // Filter albums
        const matchedAlbums = allAlbums.filter(a => {
            return (a.title && a.title.toLowerCase().includes(query)) ||
                   (a.subtitle && a.subtitle.toLowerCase().includes(query)) ||
                   (a.description && a.description.toLowerCase().includes(query)) ||
                   (a.era && a.era.toLowerCase().includes(query)) ||
                   (a.location && a.location.toLowerCase().includes(query));
        });

        // Filter photos across all albums
        const matchedPhotos = [];
        allAlbums.forEach(album => {
            if (album.photos && Array.isArray(album.photos)) {
                album.photos.forEach((photo, idx) => {
                    const match = (photo.title && photo.title.toLowerCase().includes(query)) ||
                                  (photo.caption && photo.caption.toLowerCase().includes(query)) ||
                                  (photo.location && photo.location.toLowerCase().includes(query)) ||
                                  (photo.note && photo.note.toLowerCase().includes(query)) ||
                                  (photo.artist_1 && photo.artist_1.toLowerCase().includes(query)) ||
                                  (photo.artist_2 && photo.artist_2.toLowerCase().includes(query)) ||
                                  (photo.medium && photo.medium.toLowerCase().includes(query)) ||
                                  (photo.year && String(photo.year).includes(query));
                    if (match) {
                        matchedPhotos.push({ photo, album, idx });
                    }
                });
            }
        });

        if (searchStatus) {
            searchStatus.style.display = 'block';
            if (searchKeywordDisplay) searchKeywordDisplay.textContent = window.SEARCH_QUERY;
            if (searchAlbumsCountDisplay) searchAlbumsCountDisplay.textContent = `${matchedAlbums.length} album`;
            if (searchPhotosCountDisplay) searchPhotosCountDisplay.textContent = `${matchedPhotos.length} foto kenangan`;
        }

        if (globalSearchResults) globalSearchResults.style.display = 'block';
        if (homeNormalContent) homeNormalContent.style.display = 'none';

        if (matchedAlbums.length === 0 && matchedPhotos.length === 0) {
            if (searchEmptyState) searchEmptyState.style.display = 'block';
            if (searchAlbumsSection) searchAlbumsSection.style.display = 'none';
            if (searchPhotosSection) searchPhotosSection.style.display = 'none';
        } else {
            if (searchEmptyState) searchEmptyState.style.display = 'none';

            if (matchedAlbums.length > 0) {
                if (searchAlbumsSection) searchAlbumsSection.style.display = 'block';
                if (searchAlbumsCount) searchAlbumsCount.textContent = `(${matchedAlbums.length})`;
                if (searchAlbumsGrid) searchAlbumsGrid.innerHTML = matchedAlbums.map(a => createAlbumBookHtml(a)).join('');
            } else {
                if (searchAlbumsSection) searchAlbumsSection.style.display = 'none';
            }

            if (matchedPhotos.length > 0) {
                if (searchPhotosSection) searchPhotosSection.style.display = 'block';
                if (searchPhotosCount) searchPhotosCount.textContent = `(${matchedPhotos.length})`;
                if (searchPhotosGrid) searchPhotosGrid.innerHTML = matchedPhotos.map(item => createPolaroidCardHtml(item.photo, item.album, item.idx)).join('');
            } else {
                if (searchPhotosSection) searchPhotosSection.style.display = 'none';
            }
        }
        return;
    }

    if (searchStatus) searchStatus.style.display = 'none';
    if (globalSearchResults) globalSearchResults.style.display = 'none';
    if (homeNormalContent) homeNormalContent.style.display = 'block';

    const albums = getAlbumsData();
    const latestAlbums = albums.slice(0, 4);

    const spreadLeftContainer = document.getElementById('hero-spread-left');
    const spreadRightContainer = document.getElementById('hero-spread-right');

    if (spreadLeftContainer && spreadRightContainer && albums.length > 0) {
        const firstAlbum = albums[0];
        const secondAlbum = albums[1] || albums[0];
        const p1 = firstAlbum.photos && firstAlbum.photos.length > 0 ? firstAlbum.photos[0] : null;
        const p2 = secondAlbum.photos && secondAlbum.photos.length > 0 ? secondAlbum.photos[0] : null;

        if (p1) spreadLeftContainer.innerHTML = createPolaroidCardHtml(p1, firstAlbum, 0);
        if (p2) spreadRightContainer.innerHTML = createPolaroidCardHtml(p2, secondAlbum, 0);
    }

    const homeAlbumsContainer = document.getElementById('home-latest-albums');
    if (homeAlbumsContainer) {
        homeAlbumsContainer.innerHTML = latestAlbums.map(album => createAlbumBookHtml(album)).join('');
    }
}


/**
 * Render Rak Koleksi Album di Halaman Galeri
 */
function renderAlbumsPage() {
    const albumsContainer = document.getElementById('gallery-albums-grid');
    if (!albumsContainer) return;

    const allAlbums = getAlbumsData();

    const filtered = allAlbums.filter(album => {
        const matchDecade = selectedDecade === 'all' || album.decade === selectedDecade;
        const matchSearch = searchQuery === '' || 
            album.title.toLowerCase().includes(searchQuery.toLowerCase()) || 
            (album.subtitle && album.subtitle.toLowerCase().includes(searchQuery.toLowerCase())) ||
            (album.description && album.description.toLowerCase().includes(searchQuery.toLowerCase())) ||
            (album.location && album.location.toLowerCase().includes(searchQuery.toLowerCase()));
        return matchDecade && matchSearch;
    });

    if (filtered.length === 0) {
        albumsContainer.innerHTML = `
            <div style="grid-column: 1/-1; text-align: center; padding: 40px; font-family: var(--font-typewriter); color: var(--color-ink-faded);">
                <p style="font-size: 1.2rem;">Tidak ada album yang sesuai dengan pencarian atau era ini.</p>
                <button type="button" id="reset-filter-btn" class="era-chip-btn" style="margin-top: 14px;">Tampilkan Semua Album</button>
            </div>
        `;
        return;
    }

    albumsContainer.innerHTML = filtered.map(album => createAlbumBookHtml(album)).join('');
}

/**
 * Isi Pilihan Album di Dropdown Modal Tambah Foto
 */
function populateAlbumSelect() {
    const albumSelect = document.getElementById('form-album-select');
    if (!albumSelect) return;
    const allAlbums = getAlbumsData();
    if (allAlbums && allAlbums.length > 0) {
        albumSelect.innerHTML = allAlbums.map(album => {
            return `<option value="${album.id}">${album.title} (${album.era || 'Klasik'})</option>`;
        }).join('');
        if (currentAlbumId) {
            albumSelect.value = currentAlbumId;
        }
    }
}

// ====================================================
// 2. HTML TEMPLATE BUILDERS
// ====================================================

function createAlbumBookHtml(album) {
    const photoCount = album.photos ? album.photos.length : 0;
    return `
        <div class="album-card-wrapper" data-album-id="${album.id}">
            <div class="album-book-card" style="background-color: ${album.coverColor || '#3e261a'};">
                <div class="bookmark-ribbon"></div>
                <div class="album-frame-inlay">
                    <div class="album-meta-header">
                        <span class="album-decade-tag">ERA ${album.decade ? album.decade.replace('s', '-an') : 'KLASIK'}</span>
                        <span class="album-photo-count">${photoCount} Foto</span>
                    </div>

                    <img 
                        src="${album.coverImage}" 
                        alt="${album.title}" 
                        class="album-cover-img" 
                        loading="lazy" 
                        onerror="this.src='https://images.unsplash.com/photo-1578632767115-351597cf2477?auto=format&fit=crop&w=800&q=80'"
                    />

                    <div class="album-title-group">
                        <h3 class="gold-foil-title album-book-title">${album.title}</h3>
                        <p class="album-book-subtitle">${album.subtitle || ''}</p>
                    </div>

                    <div class="album-open-action">
                        <span style="font-family: var(--font-typewriter); font-size: 0.75rem; color: #cfb997;">
                            ${album.location || 'Indonesia'}
                        </span>
                        <button type="button" class="album-open-btn" aria-label="Buka album ${album.title}">
                            Buka Album &rarr;
                        </button>
                    </div>
                </div>
            </div>
        </div>
    `;
}

function createPolaroidCardHtml(photo, album, index) {
    const tilt = photo.tilt || (index % 2 === 0 ? '-2deg' : '2.5deg');
    const tapeClass = photo.tape === 'top-left' ? 'tape-top-left' : 
                      photo.tape === 'both' ? 'tape-center-top' : 'tape-top-right';
    const imgSrc = photo.thumb || photo.src || 'https://images.unsplash.com/photo-1529156069898-49953e39b3ac?auto=format&fit=crop&w=800&q=80';
    const isAdmin = window.CURRENT_USER && window.CURRENT_USER.role === 'admin';
    const deleteBtnHtml = isAdmin ? `
        <button type="button" class="btn-delete-polaroid" data-photo-id="${photo.id}" data-album-id="${album.id}" title="Hapus foto kenangan ini">
            ðŸ—‘ï¸
        </button>
    ` : '';

    return `
        <div class="polaroid-card polaroid-frame" style="transform: rotate(${tilt});" data-index="${index}" data-album-id="${album.id}">
            <div class="washi-tape ${tapeClass}"></div>
            ${deleteBtnHtml}
            
            <div class="photo-corner corner-tl"></div>
            <div class="photo-corner corner-tr"></div>
            <div class="photo-corner corner-bl"></div>
            <div class="photo-corner corner-br"></div>

            <div class="polaroid-img-box">
                <img 
                    src="${imgSrc}" 
                    alt="${photo.title}" 
                    loading="lazy" 
                    class="scalloped-edge"
                    onerror="this.src='https://images.unsplash.com/photo-1529156069898-49953e39b3ac?auto=format&fit=crop&w=800&q=80'"
                />
            </div>

            <div class="polaroid-caption">
                <h4>${photo.title}</h4>
                ${photo.artist_1 ? `<p class="polaroid-artist-subline">Oleh: ${photo.artist_1}${photo.artist_2 ? ' & ' + photo.artist_2 : ''}</p>` : ''}
                <span class="year-tag">${photo.year ? 'Tahun ' + photo.year : (photo.date || 'Kenangan Abadi')}</span>
            </div>
        </div>
    `;
}

// Lampirkan secara eksplisit ke window agar dapat dipanggil dari mana saja
window.switchTab = switchTab;
window.openAlbumDetail = openAlbumDetail;
window.renderHomePage = renderHomePage;
window.renderAlbumsPage = renderAlbumsPage;

/**
 * Terapkan hak akses UI berdasarkan role pengguna
 */
function enforceRoleUI() {
    const user = window.CURRENT_USER || { role: 'admin', full_name: 'Kurator Arsip', username: 'admin' };
    const role = user.role || 'admin';

    const profileBadge = document.getElementById('user-profile-badge');
    const roleIcon = document.getElementById('user-role-icon');
    const userFullname = document.getElementById('user-fullname');
    const userRoleTag = document.getElementById('user-role-tag');
    const readOnlyBanner = document.getElementById('read-only-banner');
    const footerRole = document.getElementById('footer-user-role');

    if (profileBadge) {
        profileBadge.className = 'user-profile-badge ' + (role === 'admin' ? 'badge-admin' : 'badge-user');
        profileBadge.title = 'Akun yang sedang aktif: ' + (user.username || 'Pengguna');
    }
    if (roleIcon) {
        roleIcon.textContent = (role === 'admin') ? 'ðŸ‘‘' : 'ðŸ“–';
    }
    if (userFullname) {
        userFullname.textContent = user.full_name || 'Pengunjung';
    }
    if (userRoleTag) {
        userRoleTag.textContent = (role === 'admin') ? 'Kurator (Admin)' : 'Pembaca (Hanya Baca)';
    }
    if (readOnlyBanner) {
        readOnlyBanner.style.display = (role === 'user') ? 'flex' : 'none';
    }
    if (footerRole) {
        footerRole.textContent = 'Hak Akses: ' + ((role === 'admin') ? 'Administrator Penuh' : 'Pembaca Publik');
    }

    document.querySelectorAll('.admin-action-btn').forEach(el => {
        el.style.display = (role === 'admin') ? '' : 'none';
    });
}
window.enforceRoleUI = enforceRoleUI;

// ====================================================
// 3. EVENT DELEGATION SYSTEM (KEBAL DARI RE-RENDER)
// ====================================================

function initGlobalEventDelegation() {
    // Keyboard navigation (Escape untuk menutup semua modal)
    window.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            const memoryModal = document.getElementById('memory-modal');
            if (memoryModal && memoryModal.classList.contains('is-open')) {
                memoryModal.classList.remove('is-open');
                memoryModal.setAttribute('aria-hidden', 'true');
            }
            const albumModal = document.getElementById('album-modal');
            if (albumModal && albumModal.classList.contains('is-open')) {
                albumModal.classList.remove('is-open');
                albumModal.setAttribute('aria-hidden', 'true');
            }
        }
    });

    document.addEventListener('click', (e) => {
        // 0. Tombol Logout (Keluar)
        const logoutBtn = e.target.closest('#btn-logout');
        if (logoutBtn) {
            e.preventDefault();
            if (confirm('Apakah Anda yakin ingin mengakhiri sesi dan keluar dari Jejak Waktu?')) {
                localStorage.removeItem('jejak_waktu_user');
                window.location.href = 'login.html';
            }
            return;
        }

        // 0.1 Hapus Foto Polaroid (Khusus Admin)
        const deletePhotoBtn = e.target.closest('.btn-delete-polaroid');
        if (deletePhotoBtn) {
            e.preventDefault();
            e.stopPropagation();
            const photoId = deletePhotoBtn.getAttribute('data-photo-id');
            const albumId = deletePhotoBtn.getAttribute('data-album-id') || currentAlbumId;
            if (!photoId) return;

            if (confirm('Apakah Anda yakin ingin menghapus foto kenangan ini dari arsip?')) {
                const deleteAction = (typeof sbDeletePhoto === 'function')
                    ? sbDeletePhoto(photoId, albumId)
                    : (function() {
                        const formData = new FormData();
                        formData.append('photo_id', photoId);
                        formData.append('album_id', albumId);
                        return fetch('api/delete_photo.json', {
                            method: 'POST',
                            body: formData
                        }).then(res => res.json());
                    })();

                deleteAction
                .then(async (data) => {
                    if (data.status === 'success') {
                        if (typeof vintageSound !== 'undefined' && vintageSound.playPageFlip) {
                            vintageSound.playPageFlip();
                        }
                        alert('Foto kenangan berhasil dihapus dari arsip.');
                        await fetchAlbumsFromDB();
                        renderHomePage();
                        renderAlbumsPage();
                        // Invalidasi cache artist setelah hapus foto
                        if (typeof clearArtistsCache === 'function') {
                            clearArtistsCache();
                            if (currentTab === 'artists' && typeof renderArtistsPage === 'function') {
                                renderArtistsPage();
                            }
                        }
                        if (albumId) openAlbumDetail(albumId);
                    } else {
                        alert(data.message || 'Gagal menghapus foto.');
                    }
                })
                .catch(err => {
                    console.warn('API delete photo offline, menghapus dari penyimpanan lokal:', err);
                    const extraKey = `jejak_waktu_extra_photos_${albumId}`;
                    const extras = localStorage.getItem(extraKey);
                    if (extras) {
                        try {
                            let list = JSON.parse(extras);
                            list = list.filter(p => String(p.id) !== String(photoId));
                            localStorage.setItem(extraKey, JSON.stringify(list));
                        } catch(e){}
                    }
                    const alb = getAlbumById(albumId);
                    if (alb && alb.photos) {
                        alb.photos = alb.photos.filter(p => String(p.id) !== String(photoId));
                    }
                    if (typeof vintageSound !== 'undefined' && vintageSound.playPageFlip) {
                        vintageSound.playPageFlip();
                    }
                    alert('Foto kenangan berhasil dihapus.');
                    renderHomePage();
                    renderAlbumsPage();
                    if (albumId) openAlbumDetail(albumId);
                });
            }
            return;
        }

        // 0.2 Hapus Buku Album (Khusus Admin)
        const deleteAlbumBtn = e.target.closest('#btn-delete-current-album');
        if (deleteAlbumBtn) {
            e.preventDefault();
            if (!currentAlbumId) return;
            const album = getAlbumById(currentAlbumId);
            const albumTitle = album ? album.title : 'ini';

            if (confirm(`PERINGATAN: Apakah Anda yakin ingin menghapus buku album "${albumTitle}" beserta seluruh foto di dalamnya? Tindakan ini tidak dapat dibatalkan.`)) {
                const deleteAction = (typeof sbDeleteAlbum === 'function')
                    ? sbDeleteAlbum(currentAlbumId)
                    : (function() {
                        const formData = new FormData();
                        formData.append('album_id', currentAlbumId);
                        return fetch('api/delete_album.json', {
                            method: 'POST',
                            body: formData
                        }).then(res => res.json());
                    })();

                deleteAction
                .then(async (data) => {
                    if (data.status === 'success') {
                        if (typeof vintageSound !== 'undefined' && vintageSound.playPageFlip) {
                            vintageSound.playPageFlip();
                        }
                        alert(`Buku album "${albumTitle}" berhasil dihapus.`);
                        window.location.hash = 'galeri';
                        await fetchAlbumsFromDB();
                        renderHomePage();
                        renderAlbumsPage();
                    } else {
                        alert(data.message || 'Gagal menghapus album.');
                    }
                })
                .catch(err => {
                    console.warn('API delete album offline, menghapus dari penyimpanan lokal:', err);
                    const customAlbumsKey = 'jejak_waktu_custom_albums';
                    const existingAlbums = localStorage.getItem(customAlbumsKey);
                    if (existingAlbums) {
                        try {
                            let list = JSON.parse(existingAlbums);
                            list = list.filter(a => String(a.id) !== String(currentAlbumId));
                            localStorage.setItem(customAlbumsKey, JSON.stringify(list));
                        } catch(e){}
                    }
                    if (typeof vintageSound !== 'undefined' && vintageSound.playPageFlip) {
                        vintageSound.playPageFlip();
                    }
                    alert(`Buku album "${albumTitle}" berhasil dihapus.`);
                    window.location.hash = 'galeri';
                    renderHomePage();
                    renderAlbumsPage();
                });
            }
            return;
        }

        // 1. Tab Navigasi Buku
        const tabBtn = e.target.closest('.book-tab');
        if (tabBtn) {
            // Jika ini adalah <a> eksternal tanpa data-tab SPA, biarkan navigasi normal
            const isExternalLink = tabBtn.tagName === 'A' && tabBtn.href && !tabBtn.getAttribute('data-tab');
            if (isExternalLink) return;

            e.preventDefault();
            const tabId = tabBtn.getAttribute('data-tab');
            if (tabId) {
                switchTab(tabId);
                if (window.location.hash !== `#${tabId}`) {
                    window.location.hash = tabId;
                }
            }
            return;
        }

        // 2. Tombol Kembali ke Rak Album
        const backBtn = e.target.closest('#btn-back-albums');
        if (backBtn) {
            e.preventDefault();
            window.location.hash = 'galeri';
            return;
        }

        // 3. Kartu / Buku Album (Buka Detail Album)
        const albumCard = e.target.closest('.album-card-wrapper');
        if (albumCard) {
            e.preventDefault();
            const albumId = albumCard.getAttribute('data-album-id');
            if (albumId) {
                window.location.hash = `album/${albumId}`;
            }
            return;
        }

        // 4. Kartu Polaroid (Buka Lightbox)
        const polaroidCard = e.target.closest('.polaroid-card');
        if (polaroidCard) {
            e.preventDefault();
            const photoId = polaroidCard.getAttribute('data-photo-id');
            let index = parseInt(polaroidCard.getAttribute('data-index'), 10) || 0;
            const albumId = polaroidCard.getAttribute('data-album-id') || currentAlbumId;
            let album = albumId ? getAlbumById(albumId) : null;

            if (album && album.photos && photoId) {
                const foundIdx = album.photos.findIndex(p => String(p.id) === String(photoId));
                if (foundIdx !== -1) {
                    index = foundIdx;
                }
            }

            // Fallback jika diklik dari hasil pencarian global foto
            if (!album && window.MATCHED_PHOTOS && window.MATCHED_PHOTOS.length > 0) {
                const p = window.MATCHED_PHOTOS.find(item => String(item.id) === String(photoId));
                album = {
                    id: p ? p.album_id : 'hasil-pencarian',
                    title: p ? (p.album_title || 'Foto Kenangan') : 'Hasil Pencarian Foto',
                    photos: window.MATCHED_PHOTOS.map(mp => ({
                        id: mp.id,
                        title: mp.title,
                        artist_1: mp.artist_1,
                        artist_2: mp.artist_2,
                        year: mp.year,
                        date: mp.date,
                        location: mp.location,
                        medium: mp.medium,
                        dimensions: mp.dimensions,
                        copyright: mp.copyright,
                        caption: mp.caption,
                        src: mp.image_src,
                        thumb: mp.thumb_src || mp.image_src,
                        tilt: mp.tilt,
                        tape: mp.tape,
                        note: mp.note
                    }))
                };
                if (photoId) {
                    const foundIdx = album.photos.findIndex(p => String(p.id) === String(photoId));
                    if (foundIdx !== -1) index = foundIdx;
                }
            }

            if (!album) {
                album = getAlbumsData()[0];
            }

            if (album && window.vintageLightbox) {
                window.vintageLightbox.open(album, index);
            }
            return;
        }

        // 5. Tombol Filter Dekade Era (Sinkronkan tombol di Beranda & Galeri)
        const eraBtn = e.target.closest('.era-chip-btn');
        if (eraBtn && eraBtn.id !== 'reset-filter-btn') {
            e.preventDefault();
            const decade = eraBtn.getAttribute('data-decade');
            if (decade) {
                selectedDecade = decade;
                document.querySelectorAll('.era-chip-btn').forEach(btn => {
                    if (btn.getAttribute('data-decade') === decade) {
                        btn.classList.add('active');
                    } else {
                        btn.classList.remove('active');
                    }
                });
                if (currentTab !== 'galeri') {
                    window.location.hash = 'galeri';
                } else {
                    renderAlbumsPage();
                }
            }
            return;
        }

        // 6. Tombol Reset Filter
        const resetFilterBtn = e.target.closest('#reset-filter-btn');
        if (resetFilterBtn) {
            e.preventDefault();
            selectedDecade = 'all';
            searchQuery = '';
            const searchInput = document.getElementById('album-search-input');
            if (searchInput) searchInput.value = '';
            document.querySelectorAll('.era-chip-btn').forEach(btn => {
                if (btn.getAttribute('data-decade') === 'all') btn.classList.add('active');
                else btn.classList.remove('active');
            });
            renderAlbumsPage();
            return;
        }

        // 7. Tombol Tempel Foto (Buka Modal Tempel Foto - Admin Only)
        const addMemoryBtn = e.target.closest('#btn-add-memory');
        if (addMemoryBtn) {
            e.preventDefault();
            if (window.CURRENT_USER && window.CURRENT_USER.role !== 'admin') {
                alert('Akses Ditolak: Hanya Kurator (Admin) yang memiliki hak menempelkan foto.');
                return;
            }
            const memoryModal = document.getElementById('memory-modal');
            if (memoryModal) {
                populateAlbumSelect();
                memoryModal.classList.add('is-open');
                memoryModal.setAttribute('aria-hidden', 'false');
                if (typeof vintageSound !== 'undefined' && vintageSound.playPageFlip) {
                    vintageSound.playPageFlip();
                }
            }
            return;
        }

        // 8. Tutup Modal Tempel Foto
        const modalCloseBtn = e.target.closest('#modal-close-btn');
        const memoryModal = document.getElementById('memory-modal');
        if (modalCloseBtn || e.target === memoryModal) {
            if (memoryModal) {
                memoryModal.classList.remove('is-open');
                memoryModal.setAttribute('aria-hidden', 'true');
                const previewContainer = document.getElementById('modal-live-preview');
                if (previewContainer) previewContainer.style.display = 'none';
            }
            return;
        }

        // 9. Tombol Buat Album Baru (Buka Modal Album - Admin Only)
        const addAlbumBtn = e.target.closest('#btn-add-album');
        if (addAlbumBtn) {
            e.preventDefault();
            if (window.CURRENT_USER && window.CURRENT_USER.role !== 'admin') {
                alert('Akses Ditolak: Hanya Kurator (Admin) yang memiliki hak membuat album.');
                return;
            }
            const albumModal = document.getElementById('album-modal');
            if (albumModal) {
                albumModal.classList.add('is-open');
                albumModal.setAttribute('aria-hidden', 'false');
                if (typeof vintageSound !== 'undefined' && vintageSound.playPageFlip) {
                    vintageSound.playPageFlip();
                }
            }
            return;
        }

        // 10. Tutup Modal Buat Album Baru
        const albumModalCloseBtn = e.target.closest('#album-modal-close-btn');
        const albumModal = document.getElementById('album-modal');
        if (albumModalCloseBtn || e.target === albumModal) {
            if (albumModal) {
                albumModal.classList.remove('is-open');
                albumModal.setAttribute('aria-hidden', 'true');
                const albumPreviewContainer = document.getElementById('album-live-preview');
                if (albumPreviewContainer) albumPreviewContainer.style.display = 'none';
            }
            return;
        }

        // 11. Tombol Masukan / Admin Feedback (Buka Tab Kritik & Saran - Admin Only)
        const adminFeedbackBtn = e.target.closest('#btn-admin-feedback');
        if (adminFeedbackBtn) {
            e.preventDefault();
            if (window.CURRENT_USER && window.CURRENT_USER.role !== 'admin') {
                alert('Akses Ditolak: Hanya Kurator (Admin) yang dapat mengakses panel masukan.');
                return;
            }
            switchTab('feedback-admin');
            if (window.location.hash !== '#feedback-admin') {
                window.location.hash = 'feedback-admin';
            }
            if (typeof vintageSound !== 'undefined' && vintageSound.playPageFlip) {
                vintageSound.playPageFlip();
            }
            return;
        }
    });
}

// ====================================================
// 4. INITIALIZATION PADA DOMContentLoaded
// ====================================================

document.addEventListener('DOMContentLoaded', async () => {
    // 1. Pasang Event Delegation Global
    initGlobalEventDelegation();

    // 2. Pasang Listener Input Pencarian
    const searchInput = document.getElementById('album-search-input');
    if (searchInput) {
        searchInput.addEventListener('input', (e) => {
            searchQuery = e.target.value.trim();
            renderAlbumsPage();
        });
    }

    // 3. Pasang Listener Suara Vinyl
    const vinylBtn = document.getElementById('vinyl-toggle-btn');
    if (vinylBtn) {
        vinylBtn.addEventListener('click', () => {
            if (typeof vintageSound !== 'undefined') {
                const isPlaying = vintageSound.toggleVinyl();
                const icon = vinylBtn.querySelector('.vinyl-icon');
                const label = vinylBtn.querySelector('.vinyl-text');

                if (isPlaying) {
                    vinylBtn.classList.add('is-playing');
                    if (icon) icon.classList.add('spin-vinyl');
                    if (label) label.textContent = 'Kresek Vinyl: Hidup';
                } else {
                    vinylBtn.classList.remove('is-playing');
                    if (icon) icon.classList.remove('spin-vinyl');
                    if (label) label.textContent = 'Kresek Vinyl: Mati';
                }
            }
        });
    }

    // 4. Pasang Listener Live Preview & Submit Form Tambah Foto
    initFormHandlers();

    // 5. Render Sinkron Awal (Local Fallback)
    renderHomePage();
    renderAlbumsPage();
    populateAlbumSelect();
    enforceRoleUI();

    // 6. Jalankan Routing Hash Awal
    handleHashRouting();

    // 7. Ambil Data Terbaru dari MySQL phpMyAdmin & Render Ulang
    try {
        await fetchAlbumsFromDB();
        renderHomePage();
        renderAlbumsPage();
        populateAlbumSelect();
        // Jalankan routing hash ulang jika sedang membuka album tertentu
        handleHashRouting();
        enforceRoleUI();
    } catch (e) {
        console.warn('Gagal memuat dari database MySQL, tetap menggunakan data lokal.', e);
    }
});

// Listener Hash Change
window.addEventListener('hashchange', () => {
    handleHashRouting();
});

function handleHashRouting() {
    const hash = window.location.hash.replace('#', '');
    if (hash.startsWith('album/')) {
        const albumId = hash.replace('album/', '');
        openAlbumDetail(albumId);
    } else if (['beranda', 'galeri', 'tentang', 'events', 'artists', 'feedback-admin'].includes(hash)) {
        switchTab(hash);
    } else {
        switchTab('beranda');
    }
}

// ====================================================
// 5. FORM HANDLERS (SUBMIT & LIVE PREVIEW)
// ====================================================

function initFormHandlers() {
    // ==========================================
    // 1. HANDLER FORMULIR TEMPEL FOTO KENANGAN
    // ==========================================
    const memoryForm = document.getElementById('memory-form');
    const fileInput = document.getElementById('form-photo-file');
    const urlInput = document.getElementById('form-photo-url');
    const previewContainer = document.getElementById('modal-live-preview');
    const previewImg = document.getElementById('modal-preview-img');
    const albumSelect = document.getElementById('form-album-select');
    const memoryModal = document.getElementById('memory-modal');

    // Live Preview foto dari URL
    if (urlInput && previewContainer && previewImg) {
        urlInput.addEventListener('input', () => {
            const val = urlInput.value.trim();
            if (val && (val.startsWith('http://') || val.startsWith('https://'))) {
                previewImg.src = val;
                previewContainer.style.display = 'block';
            } else if (!fileInput.files.length) {
                previewContainer.style.display = 'none';
            }
        });
        previewImg.onerror = () => {
            previewImg.src = 'https://images.unsplash.com/photo-1529156069898-49953e39b3ac?auto=format&fit=crop&w=600&q=80';
        };
    }

    // Live Preview foto dari File Komputer / HP
    if (fileInput && previewContainer && previewImg) {
        fileInput.addEventListener('change', () => {
            if (fileInput.files && fileInput.files[0]) {
                const reader = new FileReader();
                reader.onload = (e) => {
                    previewImg.src = e.target.result;
                    previewContainer.style.display = 'block';
                };
                reader.readAsDataURL(fileInput.files[0]);
            }
        });
    }

    // Submit Form Tempel Foto
    if (memoryForm) {
        memoryForm.addEventListener('submit', async (e) => {
            e.preventDefault();

            const submitBtn = memoryForm.querySelector('.vintage-btn-submit');
            const originalBtnText = submitBtn.innerHTML;
            submitBtn.disabled = true;
            submitBtn.innerHTML = 'â³ Menempelkan ke Arsip...';

            const title = document.getElementById('form-photo-title').value.trim();
            const artist_1 = document.getElementById('form-photo-artist-1') ? document.getElementById('form-photo-artist-1').value.trim() : '';
            const artist_2 = document.getElementById('form-photo-artist-2') ? document.getElementById('form-photo-artist-2').value.trim() : '';
            const year = document.getElementById('form-photo-year').value.trim();
            const location = document.getElementById('form-photo-location').value.trim() || 'Indonesia';
            const medium = document.getElementById('form-photo-medium') ? document.getElementById('form-photo-medium').value.trim() : '';
            const dimensions = document.getElementById('form-photo-dimension') ? document.getElementById('form-photo-dimension').value.trim() : '';
            const copyright = document.getElementById('form-photo-copyright') ? document.getElementById('form-photo-copyright').value.trim() : '';
            const caption = document.getElementById('form-photo-caption').value.trim();
            const note = document.getElementById('form-photo-note').value.trim();
            const targetAlbum = (albumSelect && albumSelect.value) ? albumSelect.value : (currentAlbumId || 'jakarta-tempo-doeloe');
            const photoUrl = urlInput ? urlInput.value.trim() : '';

            if (!title) {
                alert('Silakan masukkan judul karya / foto kenangan.');
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalBtnText;
                return;
            }

            const formData = new FormData();
            formData.append('album_id', targetAlbum);
            formData.append('title', title);
            formData.append('artist_1', artist_1);
            formData.append('artist_2', artist_2);
            formData.append('year', year);
            formData.append('location', location);
            formData.append('medium', medium);
            formData.append('dimensions', dimensions);
            formData.append('copyright', copyright);
            formData.append('caption', caption);
            formData.append('note', note);
            formData.append('photo_url', photoUrl);

            if (fileInput && fileInput.files && fileInput.files[0]) {
                formData.append('photo_file', fileInput.files[0]);
            }

            let savedViaAPI = false;

            try {
                if (typeof sbAddPhoto === 'function') {
                    const photoPayload = {
                        album_id: targetAlbum,
                        title: title,
                        artist_1: artist1,
                        artist_2: artist2,
                        year: year,
                        date: date,
                        location: location,
                        medium: medium,
                        dimensions: dimensions,
                        copyright: copyright,
                        caption: caption,
                        tilt: tilt,
                        tape: tape,
                        note: note,
                        photo_url: photoUrl
                    };
                    const fileObj = (fileInput && fileInput.files && fileInput.files[0]) ? fileInput.files[0] : null;
                    const result = await sbAddPhoto(photoPayload, fileObj);
                    if (result && result.status === 'success') {
                        savedViaAPI = true;
                    }
                } else {
                    const response = await fetch('api/add_photo.json', {
                        method: 'POST',
                        body: formData
                    });

                    if (response.ok) {
                        const result = await response.json();
                        if (result.status === 'success') {
                            savedViaAPI = true;
                        }
                    }
                }
            } catch (err) {
                console.warn('Backend gagal atau offline, beralih ke penyimpanan lokal offline.', err);
            }

            // Fallback penyimpanan lokal jika offline atau MySQL belum aktif
            if (!savedViaAPI) {
                let resolvedPhotoSrc = photoUrl;
                if (previewImg && previewImg.src && previewImg.src.startsWith('data:image/')) {
                    resolvedPhotoSrc = previewImg.src;
                }
                if (!resolvedPhotoSrc) {
                    resolvedPhotoSrc = 'https://images.unsplash.com/photo-1529156069898-49953e39b3ac?auto=format&fit=crop&w=1200&q=85';
                }

                const randomTilts = ['-2.5deg', '-1.5deg', '1.8deg', '2.5deg', '-3deg', '2deg'];
                const randomTapes = ['top-left', 'top-right', 'both'];

                const newLocalPhoto = {
                    id: 'local-' + Date.now(),
                    album_id: targetAlbum,
                    title: title,
                    artist_1: artist_1 || null,
                    artist_2: artist_2 || null,
                    year: year,
                    date: year ? `Tahun ${year}` : 'Kenangan Abadi',
                    location: location,
                    medium: medium || null,
                    dimensions: dimensions || null,
                    copyright: copyright || null,
                    caption: caption || 'Karya / foto kenangan yang baru saja ditempel di lembaran Jejak Waktu.',
                    src: resolvedPhotoSrc,
                    thumb: resolvedPhotoSrc,
                    tilt: randomTilts[Math.floor(Math.random() * randomTilts.length)],
                    tape: randomTapes[Math.floor(Math.random() * randomTapes.length)],
                    note: note || 'Koleksi Pribadi'
                };

                addPhotoToAlbumLocal(targetAlbum, newLocalPhoto);
            }

            if (typeof vintageSound !== 'undefined' && vintageSound.playShutter) {
                vintageSound.playShutter();
            }

            alert(savedViaAPI 
                ? `Foto kenangan "${title}" berhasil disimpan (Tersimpan Lokal)!`
                : `Foto kenangan "${title}" berhasil ditempelkan ke dalam album (Tersimpan Lokal)!`
            );

            memoryForm.reset();
            if (previewContainer) previewContainer.style.display = 'none';
            if (memoryModal) {
                memoryModal.classList.remove('is-open');
                memoryModal.setAttribute('aria-hidden', 'true');
            }
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalBtnText;

            // Muat ulang data terbaru dan buka detail album
            await fetchAlbumsFromDB();
            renderHomePage();
            renderAlbumsPage();
            populateAlbumSelect();
            openAlbumDetail(targetAlbum);

            // Invalidasi cache artist agar halaman "Daftar Artist" auto-refresh
            // saat ada nama artist baru yang diinput melalui form Tempel Foto
            if (typeof clearArtistsCache === 'function') {
                clearArtistsCache();
                // Jika user sedang berada di tab artists, langsung re-render
                if (currentTab === 'artists' && typeof renderArtistsPage === 'function') {
                    renderArtistsPage();
                }
            }
        });
    }

    // ==========================================
    // 2. HANDLER FORMULIR BUAT ALBUM BARU
    // ==========================================
    const albumForm = document.getElementById('album-form');
    const albumModal = document.getElementById('album-modal');
    const albumFileInput = document.getElementById('album-form-file');
    const albumUrlInput = document.getElementById('album-form-url');
    const albumPreviewContainer = document.getElementById('album-live-preview');
    const albumPreviewImg = document.getElementById('album-preview-img');

    // Live preview sampul album dari URL
    if (albumUrlInput && albumPreviewContainer && albumPreviewImg) {
        albumUrlInput.addEventListener('input', () => {
            const val = albumUrlInput.value.trim();
            if (val && (val.startsWith('http://') || val.startsWith('https://'))) {
                albumPreviewImg.src = val;
                albumPreviewContainer.style.display = 'block';
            } else if (!albumFileInput.files.length) {
                albumPreviewContainer.style.display = 'none';
            }
        });
        albumPreviewImg.onerror = () => {
            albumPreviewImg.src = 'https://images.unsplash.com/photo-1578632767115-351597cf2477?auto=format&fit=crop&w=800&q=80';
        };
    }

    // Live preview sampul album dari File
    if (albumFileInput && albumPreviewContainer && albumPreviewImg) {
        albumFileInput.addEventListener('change', () => {
            if (albumFileInput.files && albumFileInput.files[0]) {
                const reader = new FileReader();
                reader.onload = (e) => {
                    albumPreviewImg.src = e.target.result;
                    albumPreviewContainer.style.display = 'block';
                };
                reader.readAsDataURL(albumFileInput.files[0]);
            }
        });
    }

    // Submit Form Buat Album
    if (albumForm) {
        albumForm.addEventListener('submit', async (e) => {
            e.preventDefault();
    
            const submitBtn = albumForm.querySelector('.vintage-btn-submit');
            const originalBtnText = submitBtn ? submitBtn.innerHTML : 'Buat Album';
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.innerHTML = '⏳ Menjilid Buku Album Baru...';
            }
    
            const title = document.getElementById('album-form-title').value.trim();
            const subtitle = document.getElementById('album-form-subtitle').value.trim();
            const decade = document.getElementById('album-form-decade').value;
            const era = document.getElementById('album-form-era').value.trim() || decade.replace('s', '-an');
            const category = document.getElementById('album-form-category').value;
            const location = document.getElementById('album-form-location').value.trim() || 'Indonesia';
            const curator = document.getElementById('album-form-curator').value.trim() || 'Koleksi Pribadi';
            const coverColor = document.getElementById('album-form-color').value || '#422a1d';
            const desc = document.getElementById('album-form-desc').value.trim();
            
            // Deklarasi coverImage (pakai nama variabel ini secara konsisten)
            const coverImage = albumUrlInput ? albumUrlInput.value.trim() : '';
    
            if (!title) {
                alert('Silakan masukkan judul buku album.');
                if (submitBtn) {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalBtnText;
                }
                return;
            }
    
            const formData = new FormData();
            formData.append('title', title);
            formData.append('subtitle', subtitle);
            formData.append('decade', decade);
            formData.append('era', era);
            formData.append('category', category);
            formData.append('location', location);
            formData.append('curator', curator);
            formData.append('cover_color', coverColor);
            formData.append('description', desc);
            formData.append('cover_image', coverImage); // DIBENARKAN
    
            if (albumFileInput && albumFileInput.files && albumFileInput.files[0]) {
                formData.append('cover_file', albumFileInput.files[0]);
            }
    
            let savedViaAPI = false;
            let createdAlbumId = null;
    
            try {
                if (typeof sbAddAlbum === 'function') {
                    const albumPayload = {
                        title: title,
                        subtitle: subtitle,
                        era: era,
                        decade: decade,
                        category: category,
                        location: location,
                        curator: curator,
                        cover_color: coverColor,
                        accent_color: '#c99e46', // DIBENARKAN (Gunakan string hex)
                        description: desc,
                        cover_image: coverImage  // DIBENARKAN
                    };
                    const coverFile = (albumFileInput && albumFileInput.files && albumFileInput.files[0]) ? albumFileInput.files[0] : null;
                    const result = await sbAddAlbum(albumPayload, coverFile);
                    if (result && result.status === 'success' && result.data) {
                        savedViaAPI = true;
                        createdAlbumId = result.data.id;
                    }
                } else {
                    const response = await fetch('api/add_album.json', {
                        method: 'POST',
                        body: formData
                    });
    
                    if (response.ok) {
                        const result = await response.json();
                        if (result.status === 'success' && result.data) {
                            savedViaAPI = true;
                            createdAlbumId = result.data.id;
                        }
                    }
                }
            } catch (err) {
                console.error('DETAIL ERROR SUPABASE:', err);
            }
    
            // Fallback penyimpanan album lokal jika offline / API gagal
            if (!savedViaAPI) {
                let resolvedCover = coverImage;
                if (albumPreviewImg && albumPreviewImg.src && albumPreviewImg.src.startsWith('data:image/')) {
                    resolvedCover = albumPreviewImg.src;
                }
                if (!resolvedCover) {
                    resolvedCover = 'https://images.unsplash.com/photo-1578632767115-351597cf2477?auto=format&fit=crop&w=800&q=80';
                }
    
                const slug = title.toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/(^-|-$)/g, '') || 'album';
                createdAlbumId = `${slug}-${Date.now().toString().slice(-4)}`;
    
                const newLocalAlbum = {
                    id: createdAlbumId,
                    title: title,
                    subtitle: subtitle || 'Koleksi Foto & Memori Bersejarah',
                    era: era,
                    decade: decade,
                    category: category,
                    coverImage: resolvedCover,
                    coverColor: coverColor,
                    accentColor: '#c99e46',
                    description: desc || 'Lembaran kenangan dan cerita yang tersimpan dalam album Jejak Waktu.',
                    location: location,
                    curator: curator,
                    photos: []
                };
    
                if (typeof addAlbumLocal === 'function') {
                    addAlbumLocal(newLocalAlbum);
                }
            }
    
            if (typeof vintageSound !== 'undefined' && vintageSound.playPageFlip) {
                vintageSound.playPageFlip();
            }
    
            alert(savedViaAPI 
                ? `Buku album "${title}" berhasil dijilid dan disimpan!`
                : `Buku album "${title}" berhasil dibuat (Tersimpan Lokal)!`
            );
    
            albumForm.reset();
            if (albumPreviewContainer) albumPreviewContainer.style.display = 'none';
            if (albumModal) {
                albumModal.classList.remove('is-open');
                albumModal.setAttribute('aria-hidden', 'true');
            }
            if (submitBtn) {
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalBtnText;
            }
    
            // Muat ulang data album dan alihkan ke album baru
            if (typeof fetchAlbumsFromDB === 'function') await fetchAlbumsFromDB();
            if (typeof renderHomePage === 'function') renderHomePage();
            if (typeof renderAlbumsPage === 'function') renderAlbumsPage();
            if (typeof populateAlbumSelect === 'function') populateAlbumSelect();
            
            if (createdAlbumId && typeof openAlbumDetail === 'function') {
                openAlbumDetail(createdAlbumId);
            } else if (typeof switchTab === 'function') {
                switchTab('galeri');
            }
        });
    }
}

