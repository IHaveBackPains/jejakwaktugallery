/**
 * Vintage Polaroid Lightbox Modal "Jejak Waktu"
 * Menampilkan foto dalam bingkai polaroid besar dengan filter retro real-time dan narasi sejarah
 */

class VintageLightbox {
    constructor() {
        this.currentAlbum = null;
        this.currentIndex = -1;
        this.activeFilter = 'filter-vintage';
        this.modalEl = null;
        this.init();
    }

    init() {
        if (!document.getElementById('vintage-lightbox')) {
            this.createDOM();
        } else {
            this.modalEl = document.getElementById('vintage-lightbox');
        }
        this.bindEvents();
    }

    createDOM() {
        const modalHtml = `
            <div id="vintage-lightbox" class="lightbox-overlay" aria-hidden="true" role="dialog" aria-modal="true">
                <div class="lightbox-backdrop"></div>
                <div class="lightbox-container">
                    <button type="button" class="lightbox-close-btn" title="Tutup Album (Esc)" aria-label="Tutup">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="18" y1="6" x2="6" y2="18"></line>
                            <line x1="6" y1="6" x2="18" y2="18"></line>
                        </svg>
                    </button>

                    <button type="button" class="lightbox-nav-btn prev-btn" title="Foto Sebelumnya (Panah Kiri)" aria-label="Sebelumnya">
                        <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="15 18 9 12 15 6"></polyline>
                        </svg>
                    </button>

                    <button type="button" class="lightbox-nav-btn next-btn" title="Foto Berikutnya (Panah Kanan)" aria-label="Berikutnya">
                        <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="9 18 15 12 9 6"></polyline>
                        </svg>
                    </button>

                    <!-- Main Polaroid Showcase -->
                    <div class="polaroid-lightbox-card">
                        <!-- Washi Tape Decor -->
                        <div class="washi-tape tape-center-top"></div>

                        <!-- Image Section -->
                        <div class="polaroid-photo-wrapper">
                            <div class="photo-inner-shadow"></div>
                            <img id="lb-photo-img" src="" alt="Foto Kenangan" class="filter-vintage" />
                            <div class="photo-timestamp-stamp" id="lb-photo-stamp">ARSIP 1972</div>
                        </div>

                        <!-- Filter Switcher Bar -->
                        <div class="vintage-filters-bar">
                            <span class="filter-label">Filter Lensa:</span>
                            <button type="button" class="filter-btn" data-filter="filter-original">Asli</button>
                            <button type="button" class="filter-btn active" data-filter="filter-vintage">Nostalgia 70an</button>
                            <button type="button" class="filter-btn" data-filter="filter-sepia">Sepia 1920</button>
                            <button type="button" class="filter-btn" data-filter="filter-noir">Hitam Putih 50an</button>
                            <button type="button" class="filter-btn" data-filter="filter-warm">Hangat Senja</button>
                        </div>

                        <!-- Polaroid Caption & Story -->
                        <div class="polaroid-details">
                            <div class="polaroid-handwriting-header">
                                <h3 id="lb-photo-title" class="handwritten-title">Judul Foto</h3>
                                <span id="lb-photo-year" class="typewriter-year">Tahun 1978</span>
                            </div>

                            <!-- Plakat Informasi Karya Seni / Museum Placard -->
                            <div class="artwork-placard" id="lb-art-placard">
                                <div class="placard-artist-row" id="lb-art-artists-container" style="display: none;">
                                    <span class="placard-label">🎨 Seniman / Pencipta:</span>
                                    <span id="lb-art-artists" class="placard-value-artist"></span>
                                </div>

                                <div class="placard-specs-grid">
                                    <div class="placard-spec-item" id="lb-art-medium-container" style="display: none;">
                                        <span class="placard-label">🖌️ Media / Teknik:</span>
                                        <span id="lb-art-medium" class="placard-value"></span>
                                    </div>
                                    <div class="placard-spec-item" id="lb-art-dim-container" style="display: none;">
                                        <span class="placard-label">📐 Ukuran / Dimensi:</span>
                                        <span id="lb-art-dimensions" class="placard-value"></span>
                                    </div>
                                </div>

                                <div class="placard-copyright-row" id="lb-art-copyright-container" style="display: none;">
                                    <span class="placard-label">⚖️ Hak Cipta / Lisensi:</span>
                                    <span id="lb-art-copyright" class="placard-value"></span>
                                </div>
                            </div>

                            <p id="lb-photo-caption" class="photo-story-text">Deskripsi cerita memori di balik foto...</p>

                            <div class="photo-meta-footer">
                                <div class="meta-item">
                                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path><circle cx="12" cy="10" r="3"></circle></svg>
                                    <span id="lb-photo-location">Jakarta Pusat</span>
                                </div>
                                <div class="meta-item">
                                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>
                                    <span id="lb-photo-date">14 Agustus 1972</span>
                                </div>
                                <div class="meta-counter" id="lb-photo-counter">Foto 1 dari 6</div>
                            </div>

                            <!-- Curator / Personal Note -->
                            <div class="curator-hand-note" id="lb-photo-note">
                                <em>Catatan: Diambil dari album keluarga kakek</em>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `;
        document.body.insertAdjacentHTML('beforeend', modalHtml);
        this.modalEl = document.getElementById('vintage-lightbox');
    }

    bindEvents() {
        if (!this.modalEl) return;

        // Close buttons & Backdrop
        const closeBtn = this.modalEl.querySelector('.lightbox-close-btn');
        if (closeBtn) closeBtn.addEventListener('click', () => this.close());

        const backdrop = this.modalEl.querySelector('.lightbox-backdrop');
        if (backdrop) backdrop.addEventListener('click', () => this.close());

        // Nav buttons
        const prevBtn = this.modalEl.querySelector('.prev-btn');
        if (prevBtn) {
            prevBtn.addEventListener('click', (e) => {
                e.stopPropagation();
                this.prev();
            });
        }

        const nextBtn = this.modalEl.querySelector('.next-btn');
        if (nextBtn) {
            nextBtn.addEventListener('click', (e) => {
                e.stopPropagation();
                this.next();
            });
        }

        // Filter Buttons
        const filterBtns = this.modalEl.querySelectorAll('.filter-btn');
        filterBtns.forEach(btn => {
            btn.addEventListener('click', (e) => {
                filterBtns.forEach(b => b.classList.remove('active'));
                btn.classList.add('active');
                const filterClass = btn.getAttribute('data-filter');
                this.setFilter(filterClass);
            });
        });

        // Keyboard navigation
        window.addEventListener('keydown', (e) => {
            if (!this.isOpen()) return;
            if (e.key === 'Escape') this.close();
            if (e.key === 'ArrowLeft') this.prev();
            if (e.key === 'ArrowRight') this.next();
        });

        // Touch Swipe (Dilindungi agar tidak terpicu saat scroll vertikal)
        let startX = 0;
        let startY = 0;
        const card = this.modalEl.querySelector('.polaroid-lightbox-card');
        if (card) {
            card.addEventListener('touchstart', (e) => {
                startX = e.changedTouches[0].screenX;
                startY = e.changedTouches[0].screenY;
            }, { passive: true });

            card.addEventListener('touchend', (e) => {
                const endX = e.changedTouches[0].screenX;
                const endY = e.changedTouches[0].screenY;
                const diffX = endX - startX;
                const diffY = endY - startY;
                if (Math.abs(diffX) > Math.abs(diffY) && Math.abs(diffX) > 50) {
                    if (diffX > 0) this.prev();
                    else this.next();
                }
            }, { passive: true });
        }
    }

    open(album, photoIndex = 0) {
        if (!album || !album.photos || album.photos.length === 0) return;
        this.currentAlbum = album;
        this.currentIndex = photoIndex;

        // Play vintage shutter sound
        if (typeof vintageSound !== 'undefined' && vintageSound.playShutter) {
            vintageSound.playShutter();
        }

        this.renderPhoto();
        if (this.modalEl) {
            this.modalEl.classList.add('is-open');
            this.modalEl.setAttribute('aria-hidden', 'false');
        }
        document.body.style.overflow = 'hidden';
    }

    close() {
        if (this.modalEl) {
            this.modalEl.classList.remove('is-open');
            this.modalEl.setAttribute('aria-hidden', 'true');
        }
        document.body.style.overflow = '';
    }

    isOpen() {
        return this.modalEl ? this.modalEl.classList.contains('is-open') : false;
    }

    next() {
        if (!this.currentAlbum) return;
        this.currentIndex = (this.currentIndex + 1) % this.currentAlbum.photos.length;
        if (typeof vintageSound !== 'undefined' && vintageSound.playPageFlip) vintageSound.playPageFlip();
        this.renderPhoto();
    }

    prev() {
        if (!this.currentAlbum) return;
        this.currentIndex = (this.currentIndex - 1 + this.currentAlbum.photos.length) % this.currentAlbum.photos.length;
        if (typeof vintageSound !== 'undefined' && vintageSound.playPageFlip) vintageSound.playPageFlip();
        this.renderPhoto();
    }

    setFilter(filterClass) {
        this.activeFilter = filterClass;
        const img = document.getElementById('lb-photo-img');
        if (img) {
            img.className = filterClass;
        }
    }

    renderPhoto() {
        const photo = this.currentAlbum.photos[this.currentIndex];
        if (!photo) return;

        const img = document.getElementById('lb-photo-img');
        const title = document.getElementById('lb-photo-title');
        const year = document.getElementById('lb-photo-year');
        const caption = document.getElementById('lb-photo-caption');
        const loc = document.getElementById('lb-photo-location');
        const date = document.getElementById('lb-photo-date');
        const counter = document.getElementById('lb-photo-counter');
        const note = document.getElementById('lb-photo-note');
        const stamp = document.getElementById('lb-photo-stamp');

        if (img) {
            img.style.opacity = '0';
            const targetSrc = photo.src || photo.thumb || 'https://images.unsplash.com/photo-1529156069898-49953e39b3ac?auto=format&fit=crop&w=1200&q=85';
            
            const showImg = () => {
                img.style.opacity = '1';
            };

            img.onload = showImg;
            img.onerror = () => {
                img.src = 'https://images.unsplash.com/photo-1529156069898-49953e39b3ac?auto=format&fit=crop&w=1200&q=85';
                showImg();
            };

            setTimeout(() => {
                img.src = targetSrc;
                img.alt = photo.title || 'Foto Kenangan';
                img.className = this.activeFilter;
                // Jika gambar sudah selesai dicache browser, tampilkan segera
                if (img.complete && img.naturalHeight !== 0) {
                    showImg();
                }
            }, 60);
        }

        if (title) title.textContent = photo.title;
        if (year) year.textContent = photo.year ? `Tahun ${photo.year}` : 'Arsip Klasik';
        if (caption) caption.textContent = photo.caption || 'Foto kenangan bersejarah tersimpan abadi dalam album Jejak Waktu.';
        if (loc) loc.textContent = photo.location || 'Indonesia';
        if (date) date.textContent = photo.date || (photo.year ? `Tahun ${photo.year}` : 'Tanggal tidak tercatat');
        if (counter) counter.textContent = `Foto ${this.currentIndex + 1} dari ${this.currentAlbum.photos.length}`;
        if (stamp) stamp.textContent = `ARSIP ${photo.year || 'KLASIK'}`;

        // Plakat Metadata Seni
        const artistsContainer = document.getElementById('lb-art-artists-container');
        const artistsEl = document.getElementById('lb-art-artists');
        const mediumContainer = document.getElementById('lb-art-medium-container');
        const mediumEl = document.getElementById('lb-art-medium');
        const dimContainer = document.getElementById('lb-art-dim-container');
        const dimEl = document.getElementById('lb-art-dimensions');
        const copyrightContainer = document.getElementById('lb-art-copyright-container');
        const copyrightEl = document.getElementById('lb-art-copyright');
        const placardEl = document.getElementById('lb-art-placard');

        let hasAnyArtworkMeta = false;

        // Seniman 1 & 2
        let artistNames = [];
        if (photo.artist_1 && photo.artist_1.trim() !== '') artistNames.push(photo.artist_1.trim());
        if (photo.artist_2 && photo.artist_2.trim() !== '') artistNames.push(photo.artist_2.trim());

        if (artistsContainer && artistsEl) {
            if (artistNames.length > 0) {
                artistsEl.textContent = artistNames.join(' & ');
                artistsContainer.style.display = 'flex';
                hasAnyArtworkMeta = true;
            } else {
                artistsContainer.style.display = 'none';
            }
        }

        // Media / Bahan / Teknik
        if (mediumContainer && mediumEl) {
            if (photo.medium && photo.medium.trim() !== '') {
                mediumEl.textContent = photo.medium.trim();
                mediumContainer.style.display = 'flex';
                hasAnyArtworkMeta = true;
            } else {
                mediumContainer.style.display = 'none';
            }
        }

        // Ukuran / Dimensi
        if (dimContainer && dimEl) {
            if (photo.dimensions && photo.dimensions.trim() !== '') {
                dimEl.textContent = photo.dimensions.trim();
                dimContainer.style.display = 'flex';
                hasAnyArtworkMeta = true;
            } else {
                dimContainer.style.display = 'none';
            }
        }

        // Hak Cipta
        if (copyrightContainer && copyrightEl) {
            if (photo.copyright && photo.copyright.trim() !== '') {
                copyrightEl.textContent = photo.copyright.trim();
                copyrightContainer.style.display = 'flex';
                hasAnyArtworkMeta = true;
            } else {
                copyrightContainer.style.display = 'none';
            }
        }

        if (placardEl) {
            placardEl.style.display = hasAnyArtworkMeta ? 'block' : 'none';
        }

        if (note) {
            if (photo.note) {
                note.style.display = 'block';
                note.innerHTML = `<em>Catatan: ${photo.note}</em>`;
            } else {
                note.style.display = 'none';
            }
        }
    }
}

// Global instance setup
let vintageLightbox;
document.addEventListener('DOMContentLoaded', () => {
    vintageLightbox = new VintageLightbox();
    window.vintageLightbox = vintageLightbox;
});
