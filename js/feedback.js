/**
 * Feedback (Kritik & Saran) Client Controller "Jejak Waktu"
 * Database: Supabase (menggantikan MySQL/PHP)
 * Menangani:
 * 1. Form publik Kritik & Saran (Validasi, Submit ke Supabase, Pesan Sukses)
 * 2. Panel Admin (Daftar Masukan, Filter, Detail Modal, Update Status, Hapus)
 * 3. Notifikasi counter otomatis untuk masukan yang "Belum dibaca"
 */

(function () {
    'use strict';

    // State Admin
    var currentFilter = 'all';
    var currentSearch = '';
    var activeFeedbackList = [];
    var activeDetailItem = null;

    // Helper escape HTML untuk proteksi XSS
    function esc(str) {
        if (!str && str !== 0) return '';
        var div = document.createElement('div');
        div.textContent = String(str);
        return div.innerHTML;
    }

    // Helper format tanggal Indonesia
    function formatTanggalIndo(dateStr) {
        if (!dateStr) return '-';
        var d = new Date(dateStr.replace(/-/g, '/'));
        if (isNaN(d.getTime())) return dateStr;

        var bulan = [
            'Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun',
            'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'
        ];
        var day = ('0' + d.getDate()).slice(-2);
        var month = bulan[d.getMonth()];
        var year = d.getFullYear();
        var hours = ('0' + d.getHours()).slice(-2);
        var mins = ('0' + d.getMinutes()).slice(-2);

        return day + ' ' + month + ' ' + year + ', ' + hours + ':' + mins;
    }

    // =========================================================================
    // 1. VISITOR: FORM SUBMIT & TOGGLE
    // =========================================================================
    function initVisitorForm() {
        var toggleBtn = document.getElementById('btn-open-feedback');
        var formCard = document.getElementById('feedback-form-card');
        var cancelBtn = document.getElementById('feedback-btn-cancel');
        var form = document.getElementById('feedback-form');
        var alertErr = document.getElementById('feedback-alert-error');
        var alertSucc = document.getElementById('feedback-alert-success');
        var submitBtn = document.getElementById('feedback-btn-submit');

        if (!toggleBtn || !formCard) return;

        // Toggle buka / tutup form
        toggleBtn.addEventListener('click', function () {
            var isHidden = formCard.style.display === 'none' || formCard.style.display === '';
            if (isHidden) {
                formCard.style.display = 'block';
                toggleBtn.setAttribute('aria-expanded', 'true');
                formCard.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
                var firstInput = document.getElementById('fb-nama');
                if (firstInput) firstInput.focus();
            } else {
                formCard.style.display = 'none';
                toggleBtn.setAttribute('aria-expanded', 'false');
            }
        });

        // Tombol Tutup / Batal
        if (cancelBtn) {
            cancelBtn.addEventListener('click', function () {
                formCard.style.display = 'none';
                toggleBtn.setAttribute('aria-expanded', 'false');
                if (alertErr) alertErr.style.display = 'none';
            });
        }

        // Handle Submit Form
        if (form) {
            form.addEventListener('submit', function (e) {
                e.preventDefault();

                if (alertErr) alertErr.style.display = 'none';
                if (alertSucc) alertSucc.style.display = 'none';

                var nama = document.getElementById('fb-nama') ? document.getElementById('fb-nama').value.trim() : '';
                var email = document.getElementById('fb-email') ? document.getElementById('fb-email').value.trim() : '';
                var jenis = document.getElementById('fb-jenis') ? document.getElementById('fb-jenis').value : 'Saran';
                var isi = document.getElementById('fb-isi') ? document.getElementById('fb-isi').value.trim() : '';
                var hp = document.getElementById('fb-hp') ? document.getElementById('fb-hp').value : '';

                // Validasi Client-side
                if (!isi) {
                    showError('Kolom isi kritik & saran wajib diisi.');
                    if (document.getElementById('fb-isi')) document.getElementById('fb-isi').focus();
                    return;
                }

                if (isi.length < 5) {
                    showError('Isi kritik & saran minimal terdiri dari 5 karakter.');
                    if (document.getElementById('fb-isi')) document.getElementById('fb-isi').focus();
                    return;
                }

                if (email) {
                    var emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                    if (!emailRegex.test(email)) {
                        showError('Format alamat email tidak valid. Periksa kembali atau kosongkan.');
                        if (document.getElementById('fb-email')) document.getElementById('fb-email').focus();
                        return;
                    }
                }

                // UI loading state
                if (submitBtn) {
                    submitBtn.disabled = true;
                    submitBtn.innerHTML = '<span class="feedback-spinner"></span> Mengirimkan...';
                }

                var payload = {
                    nama: nama,
                    email: email,
                    jenis_masukan: jenis,
                    isi: isi,
                    hp_check: hp
                };

                var submitPromise;
                if (typeof sbSubmitFeedback === 'function') {
                    submitPromise = sbSubmitFeedback(payload).then(function (res) {
                        return { ok: res.status === 'success', status: res.status === 'success' ? 200 : 400, data: res };
                    });
                } else {
                    submitPromise = fetch('api/feedbacks.json', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify(payload)
                    })
                    .then(function (res) {
                        return res.json().then(function (data) {
                            return { ok: res.ok, status: res.status, data: data };
                        });
                    });
                }

                submitPromise
                .then(function (result) {
                    if (submitBtn) {
                        submitBtn.disabled = false;
                        submitBtn.innerHTML = 'âœï¸ Kirimkan Masukan';
                    }

                    if (!result.ok || result.data.status !== 'success') {
                        var msg = (result.data && result.data.message) ? result.data.message : 'Gagal mengirimkan masukan. Silakan coba kembali.';
                        showError(msg);
                        return;
                    }

                    // Tampilkan pesan sukses
                    if (alertSucc) {
                        alertSucc.innerHTML = '<strong>Terima Kasih!</strong> '
                            + esc(result.data.message || 'Kritik & saran Anda telah berhasil dicatat ke dalam buku arsip masukan.');
                        alertSucc.style.display = 'block';
                    }

                    // Kosongkan form
                    form.reset();

                    // Perbarui counter notifikasi jika pengguna saat ini adalah admin
                    updateAdminUnreadBadge();

                    // Auto hide pesan sukses setelah 8 detik
                    setTimeout(function () {
                        if (alertSucc) alertSucc.style.display = 'none';
                    }, 8000);
                })
                .catch(function (err) {
                    if (submitBtn) {
                        submitBtn.disabled = false;
                        submitBtn.innerHTML = 'âœï¸ Kirimkan Masukan';
                    }
                    console.warn('API feedback offline, menyimpan masukan ke penyimpanan lokal:', err);
                    var newFb = {
                        id: Date.now(),
                        nama: nama,
                        email: email,
                        jenis_masukan: jenis,
                        isi: isi,
                        status: 'Belum dibaca',
                        created_at: new Date().toISOString()
                    };
                    var saved = localStorage.getItem('jejak_waktu_feedbacks');
                    var list = saved ? JSON.parse(saved) : [];
                    list.unshift(newFb);
                    localStorage.setItem('jejak_waktu_feedbacks', JSON.stringify(list));
                    if (alertSucc) {
                        alertSucc.innerHTML = '<strong>Terima Kasih!</strong> '
                            + 'Kritik &amp; saran Anda telah berhasil dicatat ke dalam buku arsip masukan (Tersimpan Lokal).';
                        alertSucc.style.display = 'block';
                    }
                    form.reset();
                    updateAdminUnreadBadge();
                    setTimeout(function () {
                        if (alertSucc) alertSucc.style.display = 'none';
                    }, 8000);
                });
            });
        }

        function showError(msg) {
            if (alertErr) {
                alertErr.textContent = msg;
                alertErr.style.display = 'block';
                alertErr.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
            } else {
                alert(msg);
            }
        }
    }

    // =========================================================================
    // 2. ADMIN: NOTIFIKASI COUNTER "BELUM DIBACA"
    // =========================================================================
    function updateAdminUnreadBadge() {
        var navBadge = document.getElementById('admin-feedback-badge');
        var sideBadge = document.getElementById('sidebar-feedback-badge');
        var navBtn = document.getElementById('btn-admin-feedback');

        // Hanya fetch jika elemen badge admin ada di DOM (artinya user adalah Admin)
        if (!navBadge && !sideBadge && !navBtn) return;

        // Gunakan Supabase jika sudah dikonfigurasi
        var countPromise;
        if (typeof isSupabaseConfigured === 'function' && isSupabaseConfigured()) {
            countPromise = sbFetchFeedbackCount();
        } else {
            // Fallback localStorage
            var saved = localStorage.getItem('jejak_waktu_feedbacks');
            var list = saved ? JSON.parse(saved) : [];
            var unreadLocal = list.filter(function(x) { return x.status === 'Belum dibaca'; }).length;
            countPromise = Promise.resolve({
                status: 'success',
                counts: { total: list.length, unread: unreadLocal, read: 0, followed_up: 0 },
                unread: unreadLocal
            });
        }

        countPromise
        .then(function (json) {
            if (!json || json.status !== 'success' || !json.counts) return;

            var unread = parseInt(json.unread || 0, 10);

            // Update badge di navbar
            if (navBadge) {
                navBadge.textContent = unread;
                if (unread > 0) {
                    navBadge.style.display = 'inline-block';
                    navBadge.classList.remove('is-zero');
                } else {
                    navBadge.style.display = 'none';
                    navBadge.classList.add('is-zero');
                }
            }

            // Update title tombol navbar
            if (navBtn) {
                navBtn.title = unread > 0 ? 'Kritik & Saran (' + unread + ' belum dibaca)' : 'Kritik & Saran Pengunjung';
            }

            // Update badge di tab sidebar
            if (sideBadge) {
                sideBadge.textContent = unread;
                if (unread > 0) {
                    sideBadge.style.display = 'inline-block';
                    sideBadge.classList.remove('is-zero');
                } else {
                    sideBadge.style.display = 'none';
                    sideBadge.classList.add('is-zero');
                }
            }

            // Update counter angka di tab panel admin jika sedang terbuka
            updateTabCountsUI(json.counts);
        })
        .catch(function () {
            var saved = localStorage.getItem('jejak_waktu_feedbacks');
            var list = saved ? JSON.parse(saved) : [];
            var unread = list.filter(function(x) { return x.status === 'Belum dibaca'; }).length;
            if (navBadge) {
                navBadge.textContent = unread;
                navBadge.style.display = unread > 0 ? 'inline-block' : 'none';
            }
            if (sideBadge) {
                sideBadge.textContent = unread;
                sideBadge.style.display = unread > 0 ? 'inline-block' : 'none';
            }
        });
    }

    function updateTabCountsUI(counts) {
        if (!counts) return;
        var elAll = document.getElementById('fb-count-all');
        var elUnread = document.getElementById('fb-count-unread');
        var elRead = document.getElementById('fb-count-read');
        var elDone = document.getElementById('fb-count-done');
        var elSub = document.getElementById('admin-fb-summary-text');

        if (elAll) elAll.textContent = counts.total || 0;
        if (elUnread) elUnread.textContent = counts.unread || 0;
        if (elRead) elRead.textContent = counts.read || 0;
        if (elDone) elDone.textContent = counts.followed_up || 0;

        if (elSub) {
            var unreadNum = counts.unread || 0;
            elSub.innerHTML = 'Total <strong>' + (counts.total || 0) + '</strong> masukan terdata Â· '
                + '<span style="color:' + (unreadNum > 0 ? '#a8382b;font-weight:700;' : 'inherit;') + '">'
                + unreadNum + ' belum dibaca</span>';
        }
    }

    // =========================================================================
    // 3. ADMIN: LOAD & RENDER TAB KRITIK & SARAN
    // =========================================================================
    window.loadAdminFeedbacks = function () {
        var tbody = document.getElementById('admin-fb-tbody');
        var emptyEl = document.getElementById('admin-fb-empty');
        var loadingEl = document.getElementById('admin-fb-loading');

        if (!tbody) return;

        if (loadingEl) loadingEl.style.display = 'block';
        if (emptyEl) emptyEl.style.display = 'none';

        var url = currentFilter;
        var searchQ = currentSearch;

        // Gunakan Supabase jika sudah dikonfigurasi
        var fetchPromise;
        if (typeof isSupabaseConfigured === 'function' && isSupabaseConfigured()) {
            fetchPromise = sbFetchFeedbacks(url, searchQ);
        } else {
            // Fallback localStorage
            fetchPromise = Promise.reject(new Error('Supabase belum dikonfigurasi'));
        }

        fetchPromise
        .then(function (json) {
            if (loadingEl) loadingEl.style.display = 'none';

            if (!json || json.status !== 'success') {
                throw new Error(json.message || 'Gagal memuat masukan');
            }

            activeFeedbackList = json.data || [];
            updateTabCountsUI(json.counts);
            renderFeedbackTable(activeFeedbackList);
        })
        .catch(function (err) {
            console.warn('Supabase feedback tidak tersedia, membaca data masukan lokal:', err);
            if (loadingEl) loadingEl.style.display = 'none';
            var saved = localStorage.getItem('jejak_waktu_feedbacks');
            var list = saved ? JSON.parse(saved) : [];
            if (currentFilter !== 'all') {
                list = list.filter(function(x) { return x.status === currentFilter; });
            }
            activeFeedbackList = list;
            var counts = {
                all: list.length,
                unread: list.filter(function(x) { return x.status === 'Belum dibaca'; }).length,
                read: list.filter(function(x) { return x.status === 'Sudah dibaca'; }).length,
                done: list.filter(function(x) { return x.status === 'Ditindaklanjuti'; }).length
            };
            updateTabCountsUI(counts);
            renderFeedbackTable(activeFeedbackList);
        });
    };

    function renderFeedbackTable(items) {
        var tbody = document.getElementById('admin-fb-tbody');
        var emptyEl = document.getElementById('admin-fb-empty');

        if (!tbody) return;

        if (!items || items.length === 0) {
            tbody.innerHTML = '';
            if (emptyEl) emptyEl.style.display = 'block';
            return;
        }

        if (emptyEl) emptyEl.style.display = 'none';

        var rowsHtml = items.map(function (item, idx) {
            var isUnread = item.status === 'Belum dibaca';
            var statusClass = isUnread ? 'fb-status-unread' : (item.status === 'Ditindaklanjuti' ? 'fb-status-done' : 'fb-status-read');
            var statusIcon = isUnread ? 'ðŸ”´ ' : (item.status === 'Ditindaklanjuti' ? 'ðŸŸ¢ ' : 'ðŸ”µ ');

            // Truncate isi untuk tampilan tabel
            var isiExcerpt = item.isi;
            if (isiExcerpt.length > 95) {
                isiExcerpt = isiExcerpt.substring(0, 95) + '...';
            }

            var pengirim = esc(item.nama || 'Anonim');
            if (item.email) {
                pengirim += '<br><a href="mailto:' + esc(item.email) + '" style="font-size:0.75rem;color:#304f69;text-decoration:none;">âœ‰ï¸ ' + esc(item.email) + '</a>';
            } else {
                pengirim += '<br><span style="font-size:0.75rem;color:#998372;font-style:italic;">(Tanpa Email)</span>';
            }

            return '<tr class="' + (isUnread ? 'is-unread' : '') + '" data-id="' + item.id + '">'
                + '<td style="font-family:var(--font-typewriter);font-size:0.75rem;white-space:nowrap;">'
                + '#' + item.id + '<br><span style="color:#78604d;font-size:0.72rem;">' + formatTanggalIndo(item.created_at) + '</span>'
                + '</td>'
                + '<td>'
                + '<span class="fb-status-badge ' + statusClass + '">' + statusIcon + esc(item.status) + '</span>'
                + '</td>'
                + '<td>'
                + '<span class="fb-type-badge">' + esc(item.jenis_masukan) + '</span>'
                + '</td>'
                + '<td>' + pengirim + '</td>'
                + '<td style="max-width:320px;line-height:1.45;">'
                + '<span title="' + esc(item.isi) + '">' + esc(isiExcerpt) + '</span>'
                + '</td>'
                + '<td>'
                + '<div class="admin-fb-actions">'
                + '<button type="button" class="admin-fb-btn admin-fb-btn-view" data-action="view" data-id="' + item.id + '" title="Lihat detail lengkap masukan">ðŸ‘ï¸ Detail</button>'
                + (item.status !== 'Sudah dibaca' ? '<button type="button" class="admin-fb-btn admin-fb-btn-status" data-action="set-read" data-id="' + item.id + '" title="Tandai Sudah Dibaca">âœ“ Dibaca</button>' : '')
                + (item.status !== 'Ditindaklanjuti' ? '<button type="button" class="admin-fb-btn admin-fb-btn-status" data-action="set-done" data-id="' + item.id + '" title="Tandai Ditindaklanjuti">âš¡ Tindak</button>' : '')
                + '<button type="button" class="admin-fb-btn admin-fb-btn-delete" data-action="delete" data-id="' + item.id + '" title="Hapus masukan dari arsip">ðŸ—‘ï¸</button>'
                + '</div>'
                + '</td>'
                + '</tr>';
        }).join('');

        tbody.innerHTML = rowsHtml;

        // Attach action handlers
        attachTableActionHandlers(tbody);
    }

    function attachTableActionHandlers(tbody) {
        tbody.querySelectorAll('button[data-action]').forEach(function (btn) {
            btn.addEventListener('click', function (e) {
                e.stopPropagation();
                var action = btn.getAttribute('data-action');
                var id = parseInt(btn.getAttribute('data-id'), 10);
                var item = activeFeedbackList.find(function (x) { return parseInt(x.id, 10) === id; });

                if (action === 'view' && item) {
                    openFeedbackDetailModal(item);
                } else if (action === 'set-read') {
                    updateFeedbackStatus(id, 'Sudah dibaca');
                } else if (action === 'set-done') {
                    updateFeedbackStatus(id, 'Ditindaklanjuti');
                } else if (action === 'delete') {
                    deleteFeedbackItem(id);
                }
            });
        });
    }

    // =========================================================================
    // 4. ADMIN: DETAIL MODAL & STATUS UPDATER
    // =========================================================================
    function openFeedbackDetailModal(item) {
        activeDetailItem = item;
        var modal = document.getElementById('feedback-detail-modal');
        var content = document.getElementById('feedback-detail-modal-body');

        if (!modal || !content) return;

        var statusClass = item.status === 'Belum dibaca' ? 'fb-status-unread' : (item.status === 'Ditindaklanjuti' ? 'fb-status-done' : 'fb-status-read');
        var emailHtml = item.email ? '<a href="mailto:' + esc(item.email) + '" style="color:#304f69;text-decoration:underline;">' + esc(item.email) + '</a>' : '<span style="color:#78604d;font-style:italic;">Tidak disertakan</span>';

        content.innerHTML = '<div style="margin-bottom:0.75rem;">'
            + '<span class="postage-stamp" style="font-size:0.68rem;">DETAIL MASUKAN #' + item.id + '</span>'
            + '<h3 style="font-family:var(--font-heading);font-size:1.35rem;color:#2b231d;margin:0.4rem 0 0.2rem;">Masukan Pengunjung</h3>'
            + '</div>'
            + '<div class="fb-detail-meta-grid">'
            + '<div class="fb-detail-meta-cell"><span class="fb-detail-meta-label">ðŸ‘¤ Nama Pengirim</span><span class="fb-detail-meta-val">' + esc(item.nama || 'Anonim') + '</span></div>'
            + '<div class="fb-detail-meta-cell"><span class="fb-detail-meta-label">âœ‰ï¸ Alamat Email</span><span class="fb-detail-meta-val">' + emailHtml + '</span></div>'
            + '<div class="fb-detail-meta-cell"><span class="fb-detail-meta-label">ðŸ·ï¸ Jenis Masukan</span><span class="fb-detail-meta-val"><span class="fb-type-badge">' + esc(item.jenis_masukan) + '</span></span></div>'
            + '<div class="fb-detail-meta-cell"><span class="fb-detail-meta-label">ðŸ“… Waktu Kirim</span><span class="fb-detail-meta-val">' + formatTanggalIndo(item.created_at) + '</span></div>'
            + '<div class="fb-detail-meta-cell" style="grid-column:1/-1;"><span class="fb-detail-meta-label">ðŸ“Œ Status Saat Ini</span><span class="fb-detail-meta-val"><span class="fb-status-badge ' + statusClass + '" id="detail-modal-status-badge">' + esc(item.status) + '</span></span></div>'
            + '</div>'
            + '<div><span class="fb-detail-meta-label" style="display:block;margin-bottom:4px;">ðŸ“ Isi Kritik / Saran:</span><div class="fb-detail-body">' + esc(item.isi) + '</div></div>'
            + '<div class="fb-detail-status-changer">'
            + '<div><span style="font-family:var(--font-typewriter);font-size:0.75rem;font-weight:700;color:#4d2f1d;display:block;margin-bottom:4px;">Ubah Status Masukan:</span>'
            + '<div class="fb-status-buttons-row">'
            + '<button type="button" class="admin-fb-btn admin-fb-btn-status" id="btn-set-unread">ðŸ”´ Belum Dibaca</button>'
            + '<button type="button" class="admin-fb-btn admin-fb-btn-status" id="btn-set-read">ðŸ”µ Sudah Dibaca</button>'
            + '<button type="button" class="admin-fb-btn admin-fb-btn-status" id="btn-set-done">ðŸŸ¢ Ditindaklanjuti</button>'
            + '</div>'
            + '</div>'
            + '<button type="button" class="admin-fb-btn admin-fb-btn-delete" id="btn-modal-delete" style="padding:0.45rem 0.85rem;">ðŸ—‘ï¸ Hapus Masukan</button>'
            + '</div>';

        modal.classList.add('is-open');

        // Jika status masih belum dibaca saat dibuka, otomatis tawarkan / beri indikasi
        var bUnread = document.getElementById('btn-set-unread');
        var bRead   = document.getElementById('btn-set-read');
        var bDone   = document.getElementById('btn-set-done');
        var bDel    = document.getElementById('btn-modal-delete');

        if (bUnread) bUnread.addEventListener('click', function () { updateFeedbackStatus(item.id, 'Belum dibaca', true); });
        if (bRead) bRead.addEventListener('click', function () { updateFeedbackStatus(item.id, 'Sudah dibaca', true); });
        if (bDone) bDone.addEventListener('click', function () { updateFeedbackStatus(item.id, 'Ditindaklanjuti', true); });
        if (bDel) bDel.addEventListener('click', function () { deleteFeedbackItem(item.id, true); });
    }

    window.closeFeedbackDetailModal = function () {
        var modal = document.getElementById('feedback-detail-modal');
        if (modal) modal.classList.remove('is-open');
        activeDetailItem = null;
    };

    function updateFeedbackStatus(id, newStatus, fromModal) {
        var updatePromise;
        if (typeof isSupabaseConfigured === 'function' && isSupabaseConfigured()) {
            updatePromise = sbUpdateFeedbackStatus(id, newStatus);
        } else {
            updatePromise = Promise.resolve({ status: 'success' });
        }

        updatePromise
        .then(function (json) {
            if (json.status !== 'success') {
                alert(json.message || 'Gagal mengubah status');
                return;
            }

            // Perbarui data lokal
            var found = activeFeedbackList.find(function (x) { return parseInt(x.id, 10) === id; });
            if (found) found.status = newStatus;

            // Re-render table & counts
            renderFeedbackTable(activeFeedbackList);
            updateAdminUnreadBadge();

            // Jika dipanggil dari dalam modal detail, perbarui tampilan modal
            if (fromModal && activeDetailItem && activeDetailItem.id === id) {
                activeDetailItem.status = newStatus;
                var badgeEl = document.getElementById('detail-modal-status-badge');
                if (badgeEl) {
                    badgeEl.textContent = newStatus;
                    badgeEl.className = 'fb-status-badge ' + (newStatus === 'Belum dibaca' ? 'fb-status-unread' : (newStatus === 'Ditindaklanjuti' ? 'fb-status-done' : 'fb-status-read'));
                }
            }
        })
        .catch(function (err) {
            alert('Gagal menghubungi Supabase untuk mengubah status.');
        });
    }

    function deleteFeedbackItem(id, fromModal) {
        if (!confirm('Apakah Anda yakin ingin menghapus masukan #' + id + ' ini dari arsip? Tindakan ini tidak dapat dibatalkan.')) {
            return;
        }

        var deletePromise;
        if (typeof isSupabaseConfigured === 'function' && isSupabaseConfigured()) {
            deletePromise = sbDeleteFeedback(id);
        } else {
            // Fallback: hapus dari localStorage
            deletePromise = Promise.resolve({ status: 'success' });
        }

        deletePromise
        .then(function (json) {
            if (json.status !== 'success') {
                alert(json.message || 'Gagal menghapus masukan');
                return;
            }

            // Hapus dari list lokal
            activeFeedbackList = activeFeedbackList.filter(function (x) { return parseInt(x.id, 10) !== id; });
            renderFeedbackTable(activeFeedbackList);
            updateAdminUnreadBadge();

            if (fromModal) {
                window.closeFeedbackDetailModal();
            }
        })
        .catch(function () {
            alert('Gagal menghubungi Supabase untuk menghapus masukan.');
        });
    }

    // =========================================================================
    // 5. INISIALISASI EVENT LISTENERS ADMIN FILTER
    // =========================================================================
    function initAdminToolbar() {
        var pills = document.querySelectorAll('.admin-fb-pill[data-status]');
        pills.forEach(function (pill) {
            pill.addEventListener('click', function () {
                pills.forEach(function (p) { p.classList.remove('is-active'); });
                pill.classList.add('is-active');
                currentFilter = pill.getAttribute('data-status') || 'all';
                window.loadAdminFeedbacks();
            });
        });

        var searchInput = document.getElementById('admin-fb-search');
        if (searchInput) {
            var searchTimeout = null;
            searchInput.addEventListener('input', function () {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(function () {
                    currentSearch = searchInput.value.trim();
                    window.loadAdminFeedbacks();
                }, 300);
            });
        }

        var refreshBtn = document.getElementById('admin-fb-refresh');
        if (refreshBtn) {
            refreshBtn.addEventListener('click', function () {
                window.loadAdminFeedbacks();
            });
        }
    }

    // =========================================================================
    // 6. DOM READY
    // =========================================================================
    document.addEventListener('DOMContentLoaded', function () {
        initVisitorForm();
        updateAdminUnreadBadge();
        initAdminToolbar();

        // Modal close button outside click
        var modal = document.getElementById('feedback-detail-modal');
        if (modal) {
            modal.addEventListener('click', function (e) {
                if (e.target === modal) {
                    window.closeFeedbackDetailModal();
                }
            });
        }
    });

})();

