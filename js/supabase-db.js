/**
 * supabase-db.js — Jejak Waktu
 * Layer abstraksi database: menggantikan semua fetch ke api/*.json (MySQL/PHP)
 * dengan query langsung ke Supabase menggunakan @supabase/supabase-js v2.
 *
 * Semua fungsi ini mengembalikan format respons yang konsisten:
 *   { status: 'success'|'error', data: any, message: string }
 *
 * File ini digunakan oleh: data.js, events.js, feedback.js,
 *                          gallery-map.js, artists.js, app.js
 */

'use strict';

// =====================================================================
// HELPER: Normalisasi respons Supabase ke format { status, data, message }
// =====================================================================
function sbSuccess(data, message) {
    return { status: 'success', data: data, message: message || 'OK' };
}
function sbError(message, code) {
    return { status: 'error', data: null, message: message || 'Terjadi kesalahan', code: code };
}

// =====================================================================
// HELPER: Dapatkan user yang sedang login (dari session/localStorage)
// =====================================================================
function getCurrentUserId() {
    if (window.CURRENT_USER && window.CURRENT_USER.id) {
        return window.CURRENT_USER.id;
    }
    try {
        const u = JSON.parse(localStorage.getItem('jejak_waktu_user'));
        return u && u.id ? u.id : null;
    } catch(e) { return null; }
}

// =====================================================================
// 1. ALBUMS — pengganti api/albums.json (GET)
// =====================================================================

/**
 * Ambil semua album beserta foto-fotonya dari Supabase.
 * @param {string} [searchQuery] - opsional: kata kunci pencarian
 * @returns {Promise<{status, data, message}>}
 */
async function sbFetchAlbums(searchQuery) {
    const supabase = getSupabaseClient();
    if (!supabase) return sbError('Supabase client tidak tersedia.');

    try {
        let albumQuery = supabase
            .from('albums')
            .select('*')
            .order('created_at', { ascending: false });

        if (searchQuery && searchQuery.trim()) {
            const q = searchQuery.trim();
            albumQuery = albumQuery.or(
                `title.ilike.%${q}%,subtitle.ilike.%${q}%,description.ilike.%${q}%,location.ilike.%${q}%`
            );
        }

        const { data: albums, error: albumErr } = await albumQuery;
        if (albumErr) throw albumErr;
        if (!albums || albums.length === 0) return sbSuccess([]);

        // Ambil semua foto yang berelasi dengan album-album ini
        const albumIds = albums.map(a => a.id);
        const { data: photos, error: photoErr } = await supabase
            .from('photos')
            .select('*')
            .in('album_id', albumIds)
            .order('created_at', { ascending: false });

        if (photoErr) throw photoErr;

        // Gabungkan foto ke dalam masing-masing album (format frontend)
        const result = albums.map(album => ({
            ...album,
            coverImage: album.cover_image,
            coverColor: album.cover_color,
            accentColor: album.accent_color,
            photos: (photos || [])
                .filter(p => p.album_id === album.id)
                .map(p => ({
                    ...p,
                    src: p.image_src,
                    thumb: p.thumb_src
                }))
        }));

        return sbSuccess(result);
    } catch (err) {
        console.error('[Supabase] sbFetchAlbums error:', err);
        return sbError(err.message, err.code);
    }
}

// =====================================================================
// 2. ADD PHOTO — pengganti api/add_photo.json (POST)
// =====================================================================

/**
 * Tambah foto baru ke album.
 * Untuk upload file gambar, file perlu diunggah ke Supabase Storage terlebih dulu.
 * @param {object} photoData
 * @param {File|null} [fileObj] - opsional: file gambar untuk diunggah ke Storage
 * @returns {Promise<{status, data, message}>}
 */
async function sbAddPhoto(photoData, fileObj) {
    const supabase = getSupabaseClient();
    if (!supabase) return sbError('Supabase client tidak tersedia.');

    try {
        let imageSrc = photoData.photo_url || '';
        let thumbSrc = photoData.photo_url || '';

        // Upload file ke Supabase Storage jika ada
        if (fileObj && fileObj instanceof File) {
            const ext = fileObj.name.split('.').pop();
            const fileName = `photos/${photoData.album_id}/${Date.now()}.${ext}`;
            const { data: uploadData, error: uploadErr } = await supabase.storage
                .from('jejak-waktu-assets')
                .upload(fileName, fileObj, { cacheControl: '3600', upsert: false });

            if (uploadErr) throw uploadErr;

            const { data: publicUrlData } = supabase.storage
                .from('jejak-waktu-assets')
                .getPublicUrl(fileName);

            imageSrc = publicUrlData.publicUrl;
            thumbSrc = publicUrlData.publicUrl;
        }

        const insertPayload = {
            album_id:   photoData.album_id,
            title:      photoData.title,
            artist_1:   photoData.artist_1 || null,
            artist_2:   photoData.artist_2 || null,
            year:       photoData.year || null,
            date:       photoData.date || null,
            location:   photoData.location || null,
            medium:     photoData.medium || null,
            dimensions: photoData.dimensions || null,
            copyright:  photoData.copyright || null,
            caption:    photoData.caption || null,
            image_src:  imageSrc,
            thumb_src:  thumbSrc,
            tilt:       photoData.tilt || '-2deg',
            tape:       photoData.tape || 'top-right',
            note:       photoData.note || null
        };

        const { data, error } = await supabase
            .from('photos')
            .insert([insertPayload])
            .select()
            .single();

        if (error) throw error;
        return sbSuccess(data, 'Foto kenangan berhasil ditambahkan ke arsip.');
    } catch (err) {
        console.error('[Supabase] sbAddPhoto error:', err);
        return sbError(err.message, err.code);
    }
}

// =====================================================================
// 3. DELETE PHOTO — pengganti api/delete_photo.json (POST)
// =====================================================================

/**
 * Hapus foto dari database (dan Storage jika ada).
 * @param {string|number} photoId
 * @param {string} albumId
 * @returns {Promise<{status, data, message}>}
 */
async function sbDeletePhoto(photoId, albumId) {
    const supabase = getSupabaseClient();
    if (!supabase) return sbError('Supabase client tidak tersedia.');

    try {
        const { error } = await supabase
            .from('photos')
            .delete()
            .eq('id', photoId)
            .eq('album_id', albumId);

        if (error) throw error;
        return sbSuccess(null, 'Foto kenangan berhasil dihapus dari arsip.');
    } catch (err) {
        console.error('[Supabase] sbDeletePhoto error:', err);
        return sbError(err.message, err.code);
    }
}

// =====================================================================
// 4. ADD ALBUM — pengganti api/add_album.json (POST)
// =====================================================================

/**
 * Buat album baru.
 * @param {object} albumData
 * @param {File|null} [coverFile]
 * @returns {Promise<{status, data, message}>}
 */
async function sbAddAlbum(albumData, coverFile) {
    const supabase = getSupabaseClient();
    if (!supabase) return sbError('Supabase client tidak tersedia.');

    try {
        let coverImage = albumData.cover_url || '';

        if (coverFile && coverFile instanceof File) {
            const ext = coverFile.name.split('.').pop();
            const fileName = `covers/${Date.now()}.${ext}`;
            const { data: uploadData, error: uploadErr } = await supabase.storage
                .from('jejak-waktu-assets')
                .upload(fileName, coverFile, { cacheControl: '3600', upsert: false });

            if (uploadErr) throw uploadErr;

            const { data: publicUrlData } = supabase.storage
                .from('jejak-waktu-assets')
                .getPublicUrl(fileName);

            coverImage = publicUrlData.publicUrl;
        }

        const slug = albumData.title
            .toLowerCase()
            .replace(/[^a-z0-9]+/g, '-')
            .replace(/(^-|-$)/g, '');
        const albumId = `${slug}-${Date.now().toString().slice(-4)}`;

        const insertPayload = {
            id:          albumId,
            title:       albumData.title,
            subtitle:    albumData.subtitle || null,
            era:         albumData.era || null,
            decade:      albumData.decade || '1970s',
            category:    albumData.category || 'sejarah-kota',
            cover_image: coverImage,
            cover_color: albumData.cover_color || '#422a1d',
            accent_color: albumData.accent_color || '#b38241',
            description: albumData.description || null,
            location:    albumData.location || 'Indonesia',
            curator:     albumData.curator || 'Koleksi Pribadi'
        };

        const { data, error } = await supabase
            .from('albums')
            .insert([insertPayload])
            .select()
            .single();

        if (error) throw error;
        return sbSuccess(data, 'Buku album baru berhasil dijilid ke dalam koleksi.');
    } catch (err) {
        console.error('[Supabase] sbAddAlbum error:', err);
        return sbError(err.message, err.code);
    }
}

// =====================================================================
// 5. DELETE ALBUM — pengganti api/delete_album.json (POST)
// =====================================================================

/**
 * Hapus album beserta semua fotonya (CASCADE via RLS/FK).
 * @param {string} albumId
 * @returns {Promise<{status, data, message}>}
 */
async function sbDeleteAlbum(albumId) {
    const supabase = getSupabaseClient();
    if (!supabase) return sbError('Supabase client tidak tersedia.');

    try {
        const { error } = await supabase
            .from('albums')
            .delete()
            .eq('id', albumId);

        if (error) throw error;
        return sbSuccess(null, 'Buku album beserta seluruh foto berhasil dihapus dari arsip.');
    } catch (err) {
        console.error('[Supabase] sbDeleteAlbum error:', err);
        return sbError(err.message, err.code);
    }
}

// =====================================================================
// 6. EVENTS — pengganti api/events.json (GET / POST / PUT / DELETE)
// =====================================================================

/**
 * Ambil semua event.
 * @returns {Promise<{status, data, message}>}
 */
async function sbFetchEvents() {
    const supabase = getSupabaseClient();
    if (!supabase) return sbError('Supabase client tidak tersedia.');

    try {
        const { data, error } = await supabase
            .from('events')
            .select('*')
            .order('start_date', { ascending: false });

        if (error) throw error;

        // Hitung booked_count per event
        const eventIds = (data || []).map(e => e.id);
        if (eventIds.length > 0) {
            const { data: bookingCounts } = await supabase
                .from('bookings')
                .select('event_id')
                .in('event_id', eventIds)
                .neq('status', 'cancelled');

            const countMap = {};
            (bookingCounts || []).forEach(b => {
                countMap[b.event_id] = (countMap[b.event_id] || 0) + 1;
            });

            data.forEach(ev => {
                ev.booked_count = countMap[ev.id] || 0;
            });
        }

        return sbSuccess(data || []);
    } catch (err) {
        console.error('[Supabase] sbFetchEvents error:', err);
        return sbError(err.message, err.code);
    }
}

/**
 * Ambil satu event berdasarkan ID.
 * @param {number} eventId
 * @returns {Promise<{status, data, message}>}
 */
async function sbFetchEventById(eventId) {
    const supabase = getSupabaseClient();
    if (!supabase) return sbError('Supabase client tidak tersedia.');

    try {
        const { data, error } = await supabase
            .from('events')
            .select('*')
            .eq('id', eventId)
            .single();

        if (error) throw error;

        // Hitung booked_count
        const { count } = await supabase
            .from('bookings')
            .select('*', { count: 'exact', head: true })
            .eq('event_id', eventId)
            .neq('status', 'cancelled');

        data.booked_count = count || 0;
        return sbSuccess(data);
    } catch (err) {
        console.error('[Supabase] sbFetchEventById error:', err);
        return sbError(err.message, err.code);
    }
}

/**
 * Buat atau update event (admin only).
 * @param {object} eventData
 * @param {string|null} editId - null untuk insert, ID untuk update
 * @returns {Promise<{status, data, message}>}
 */
async function sbSaveEvent(eventData, editId) {
    const supabase = getSupabaseClient();
    if (!supabase) return sbError('Supabase client tidak tersedia.');

    try {
        const payload = {
            title:          eventData.get ? eventData.get('title') : eventData.title,
            theme:          eventData.get ? eventData.get('theme') : eventData.theme,
            status:         eventData.get ? eventData.get('status') : eventData.status,
            start_date:     eventData.get ? eventData.get('start_date') : eventData.start_date,
            end_date:       eventData.get ? eventData.get('end_date') : eventData.end_date,
            start_time:     eventData.get ? (eventData.get('start_time') || null) : (eventData.start_time || null),
            end_time:       eventData.get ? (eventData.get('end_time') || null) : (eventData.end_time || null),
            ticket_price:   eventData.get ? eventData.get('ticket_price') : eventData.ticket_price,
            ticket_quota:   eventData.get ? (parseInt(eventData.get('ticket_quota')) || null) : eventData.ticket_quota,
            location_text:  eventData.get ? eventData.get('location_text') : eventData.location_text,
            location_map:   eventData.get ? eventData.get('location_map') : eventData.location_map,
            artists:        eventData.get ? eventData.get('artists') : eventData.artists,
            poster_image:   eventData.get ? eventData.get('poster_image') : eventData.poster_image,
            description:    eventData.get ? eventData.get('description') : eventData.description,
            organizer:      eventData.get ? eventData.get('organizer') : eventData.organizer,
            album_id:       (eventData.get ? eventData.get('album_id') : eventData.album_id) || null
        };

        let result;
        if (editId) {
            result = await supabase
                .from('events')
                .update(payload)
                .eq('id', editId)
                .select()
                .single();
        } else {
            result = await supabase
                .from('events')
                .insert([payload])
                .select()
                .single();
        }

        if (result.error) throw result.error;
        const msg = editId ? 'Event berhasil diperbarui.' : 'Event baru berhasil ditambahkan ke jadwal.';
        return sbSuccess(result.data, msg);
    } catch (err) {
        console.error('[Supabase] sbSaveEvent error:', err);
        return sbError(err.message, err.code);
    }
}

/**
 * Hapus event berdasarkan ID.
 * @param {number} eventId
 * @returns {Promise<{status, data, message}>}
 */
async function sbDeleteEvent(eventId) {
    const supabase = getSupabaseClient();
    if (!supabase) return sbError('Supabase client tidak tersedia.');

    try {
        const { error } = await supabase
            .from('events')
            .delete()
            .eq('id', eventId);

        if (error) throw error;
        return sbSuccess(null, 'Event berhasil dihapus.');
    } catch (err) {
        console.error('[Supabase] sbDeleteEvent error:', err);
        return sbError(err.message, err.code);
    }
}

// =====================================================================
// 7. BOOKINGS — pengganti api/bookings.json (GET / POST / DELETE)
// =====================================================================

/**
 * Ambil semua booking milik user yang sedang login.
 * @returns {Promise<{status, data, message}>}
 */
async function sbFetchMyBookings() {
    const supabase = getSupabaseClient();
    if (!supabase) return sbError('Supabase client tidak tersedia.');

    const userId = getCurrentUserId();

    try {
        let query = supabase
            .from('bookings')
            .select(`
                *,
                events (
                    id, title, theme, status, start_date, end_date,
                    start_time, end_time, poster_image, location_text, ticket_price
                )
            `)
            .order('created_at', { ascending: false });

        if (userId) {
            query = query.eq('user_id', userId);
        }

        const { data, error } = await query;
        if (error) throw error;

        // Flatten: pindahkan field events ke level booking
        const bookings = (data || []).map(b => ({
            ...b,
            event_title:   b.events ? b.events.title : '',
            theme:         b.events ? b.events.theme : '',
            status_event:  b.events ? b.events.status : '',
            start_date:    b.events ? b.events.start_date : '',
            end_date:      b.events ? b.events.end_date : '',
            start_time:    b.events ? b.events.start_time : '',
            end_time:      b.events ? b.events.end_time : '',
            poster_image:  b.events ? b.events.poster_image : '',
            location_text: b.events ? b.events.location_text : '',
            ticket_price:  b.events ? b.events.ticket_price : '',
            events:        undefined
        }));

        return sbSuccess(bookings);
    } catch (err) {
        console.error('[Supabase] sbFetchMyBookings error:', err);
        return sbError(err.message, err.code);
    }
}

/**
 * Buat booking baru.
 * @param {number} eventId
 * @param {number} qty
 * @returns {Promise<{status, data, message}>}
 */
async function sbCreateBooking(eventId, qty) {
    const supabase = getSupabaseClient();
    if (!supabase) return sbError('Supabase client tidak tersedia.');

    const userId = getCurrentUserId();

    try {
        // Cek kuota terlebih dulu
        const eventRes = await sbFetchEventById(eventId);
        if (eventRes.status !== 'success') throw new Error(eventRes.message);
        const ev = eventRes.data;

        if (ev.ticket_quota && ev.booked_count >= ev.ticket_quota) {
            return sbError('Maaf, kuota pendaftar event ini telah penuh.');
        }

        // Cek apakah sudah booking sebelumnya
        if (userId) {
            const { data: existingBooking } = await supabase
                .from('bookings')
                .select('id')
                .eq('event_id', eventId)
                .eq('user_id', userId)
                .neq('status', 'cancelled')
                .single();

            if (existingBooking) {
                return sbError('Anda sudah mendaftar untuk event ini.');
            }
        }

        const priceIsGratis = !ev.ticket_price || ev.ticket_price.toLowerCase() === 'gratis';
        const bookingCode = 'JW-' + Math.floor(100000 + Math.random() * 900000);

        const payload = {
            event_id:     eventId,
            user_id:      userId || 1,
            qty:          qty || 1,
            total_price:  priceIsGratis ? 'Gratis' : ev.ticket_price,
            status:       'confirmed',
            booking_code: bookingCode,
            notes:        null
        };

        const { data, error } = await supabase
            .from('bookings')
            .insert([payload])
            .select()
            .single();

        if (error) throw error;
        return sbSuccess(data, `Pendaftaran berhasil! Kode tiket: ${bookingCode}`);
    } catch (err) {
        console.error('[Supabase] sbCreateBooking error:', err);
        return sbError(err.message, err.code);
    }
}

/**
 * Batalkan booking.
 * @param {number} bookingId
 * @returns {Promise<{status, data, message}>}
 */
async function sbCancelBooking(bookingId) {
    const supabase = getSupabaseClient();
    if (!supabase) return sbError('Supabase client tidak tersedia.');

    try {
        const { error } = await supabase
            .from('bookings')
            .update({ status: 'cancelled' })
            .eq('id', bookingId);

        if (error) throw error;
        return sbSuccess(null, 'Pendaftaran berhasil dibatalkan.');
    } catch (err) {
        console.error('[Supabase] sbCancelBooking error:', err);
        return sbError(err.message, err.code);
    }
}

// =====================================================================
// 8. GALLERIES — pengganti api/galleries.json (GET)
// =====================================================================

/**
 * Ambil semua galeri dari Supabase.
 * @returns {Promise<{status, data, message}>}
 */
async function sbFetchGalleries() {
    const supabase = getSupabaseClient();
    if (!supabase) return sbError('Supabase client tidak tersedia.');

    try {
        const { data, error } = await supabase
            .from('galleries')
            .select('*')
            .order('name', { ascending: true });

        if (error) throw error;

        // Parse JSON string kolom 'events' jika diperlukan
        const normalized = (data || []).map(g => ({
            ...g,
            events: typeof g.events === 'string' ? (() => {
                try { return JSON.parse(g.events); } catch(e) { return []; }
            })() : (g.events || [])
        }));

        return sbSuccess(normalized);
    } catch (err) {
        console.error('[Supabase] sbFetchGalleries error:', err);
        return sbError(err.message, err.code);
    }
}

// =====================================================================
// 9. FEEDBACKS — pengganti api/feedbacks.json (GET / POST)
// =====================================================================

/**
 * Submit feedback baru dari pengunjung.
 * @param {object} feedbackData - { nama, email, jenis_masukan, isi, hp_check }
 * @returns {Promise<{status, data, message}>}
 */
async function sbSubmitFeedback(feedbackData) {
    const supabase = getSupabaseClient();
    if (!supabase) return sbError('Supabase client tidak tersedia.');

    // Anti-spam honeypot
    if (feedbackData.hp_check && feedbackData.hp_check.trim() !== '') {
        return sbError('Pengiriman terdeteksi sebagai spam.');
    }

    if (!feedbackData.isi || feedbackData.isi.trim().length < 10) {
        return sbError('Isi masukan terlalu singkat (minimal 10 karakter).');
    }

    try {
        const { data, error } = await supabase
            .from('feedbacks')
            .insert([{
                nama:          feedbackData.nama || null,
                email:         feedbackData.email || null,
                jenis_masukan: feedbackData.jenis_masukan || 'Saran',
                isi:           feedbackData.isi,
                status:        'Belum dibaca'
            }])
            .select()
            .single();

        if (error) throw error;
        return sbSuccess(data, 'Terima kasih! Kritik & saran Anda telah berhasil dicatat ke dalam buku arsip masukan.');
    } catch (err) {
        console.error('[Supabase] sbSubmitFeedback error:', err);
        return sbError(err.message, err.code);
    }
}

/**
 * Ambil daftar feedback (admin only).
 * @param {string} [statusFilter] - 'all', 'Belum dibaca', 'Sudah dibaca', 'Ditindaklanjuti'
 * @param {string} [searchQuery]
 * @returns {Promise<{status, data, counts, unread, message}>}
 */
async function sbFetchFeedbacks(statusFilter, searchQuery) {
    const supabase = getSupabaseClient();
    if (!supabase) return sbError('Supabase client tidak tersedia.');

    try {
        let query = supabase
            .from('feedbacks')
            .select('*')
            .order('created_at', { ascending: false });

        if (statusFilter && statusFilter !== 'all') {
            query = query.eq('status', statusFilter);
        }

        if (searchQuery && searchQuery.trim()) {
            const q = searchQuery.trim();
            query = query.or(`nama.ilike.%${q}%,isi.ilike.%${q}%,email.ilike.%${q}%`);
        }

        const { data, error } = await query;
        if (error) throw error;

        const list = data || [];

        // Hitung counts untuk tab filter
        const { data: allFb } = await supabase
            .from('feedbacks')
            .select('status');

        const allList = allFb || [];
        const counts = {
            total:       allList.length,
            unread:      allList.filter(x => x.status === 'Belum dibaca').length,
            read:        allList.filter(x => x.status === 'Sudah dibaca').length,
            followed_up: allList.filter(x => x.status === 'Ditindaklanjuti').length
        };

        return {
            status:  'success',
            data:    list,
            counts:  counts,
            unread:  counts.unread,
            message: 'OK'
        };
    } catch (err) {
        console.error('[Supabase] sbFetchFeedbacks error:', err);
        return sbError(err.message, err.code);
    }
}

/**
 * Ambil jumlah feedback yang belum dibaca (untuk badge notifikasi).
 * @returns {Promise<{status, counts, unread, message}>}
 */
async function sbFetchFeedbackCount() {
    const supabase = getSupabaseClient();
    if (!supabase) return sbError('Supabase client tidak tersedia.');

    try {
        const { data, error } = await supabase
            .from('feedbacks')
            .select('status');

        if (error) throw error;

        const list = data || [];
        const counts = {
            total:       list.length,
            unread:      list.filter(x => x.status === 'Belum dibaca').length,
            read:        list.filter(x => x.status === 'Sudah dibaca').length,
            followed_up: list.filter(x => x.status === 'Ditindaklanjuti').length
        };

        return {
            status:  'success',
            counts:  counts,
            unread:  counts.unread,
            message: 'OK'
        };
    } catch (err) {
        console.error('[Supabase] sbFetchFeedbackCount error:', err);
        return sbError(err.message, err.code);
    }
}

/**
 * Update status feedback (admin only).
 * @param {number} id
 * @param {string} newStatus - 'Belum dibaca'|'Sudah dibaca'|'Ditindaklanjuti'
 * @returns {Promise<{status, data, message}>}
 */
async function sbUpdateFeedbackStatus(id, newStatus) {
    const supabase = getSupabaseClient();
    if (!supabase) return sbError('Supabase client tidak tersedia.');

    try {
        const { data, error } = await supabase
            .from('feedbacks')
            .update({ status: newStatus })
            .eq('id', id)
            .select()
            .single();

        if (error) throw error;
        return sbSuccess(data, 'Status masukan berhasil diperbarui.');
    } catch (err) {
        console.error('[Supabase] sbUpdateFeedbackStatus error:', err);
        return sbError(err.message, err.code);
    }
}

/**
 * Hapus feedback (admin only).
 * @param {number} id
 * @returns {Promise<{status, data, message}>}
 */
async function sbDeleteFeedback(id) {
    const supabase = getSupabaseClient();
    if (!supabase) return sbError('Supabase client tidak tersedia.');

    try {
        const { error } = await supabase
            .from('feedbacks')
            .delete()
            .eq('id', id);

        if (error) throw error;
        return sbSuccess(null, 'Masukan berhasil dihapus dari arsip.');
    } catch (err) {
        console.error('[Supabase] sbDeleteFeedback error:', err);
        return sbError(err.message, err.code);
    }
}

// =====================================================================
// 10. ARTISTS — pengganti api/artists.json (GET / POST / DELETE)
// =====================================================================

/**
 * Ambil semua profil seniman beserta karya-karyanya.
 * @returns {Promise<{status, data, message}>}
 */
async function sbFetchArtists() {
    const supabase = getSupabaseClient();
    if (!supabase) return sbError('Supabase client tidak tersedia.');

    try {
        // Ambil profil kustom seniman
        const { data: profiles, error: profileErr } = await supabase
            .from('artist_profiles')
            .select('*')
            .eq('is_deleted', false)
            .order('name', { ascending: true });

        if (profileErr) throw profileErr;

        // Ambil foto untuk karya mini gallery per seniman
        const { data: photos } = await supabase
            .from('photos')
            .select('id, title, image_src, thumb_src, album_id, artist_1, artist_2, year')
            .order('created_at', { ascending: false });

        const photoList = photos || [];

        // Bangun data seniman dari gabungan profil + karya
        const artists = (profiles || []).map(profile => {
            const artworks = photoList.filter(p =>
                (p.artist_1 && p.artist_1.toLowerCase().includes(profile.name.toLowerCase())) ||
                (p.artist_2 && p.artist_2.toLowerCase().includes(profile.name.toLowerCase()))
            ).slice(0, 6);

            return {
                id:       profile.id,
                name:     profile.name,
                role:     profile.role,
                era:      profile.era,
                category: profile.category,
                avatar:   profile.avatar,
                bio:      profile.bio,
                location: profile.location,
                artworks: artworks.map(p => ({
                    id:    p.id,
                    title: p.title,
                    src:   p.image_src,
                    thumb: p.thumb_src,
                    year:  p.year
                }))
            };
        });

        return sbSuccess(artists);
    } catch (err) {
        console.error('[Supabase] sbFetchArtists error:', err);
        return sbError(err.message, err.code);
    }
}

/**
 * Simpan/update profil seniman.
 * @param {FormData} formData
 * @returns {Promise<{status, data, message}>}
 */
async function sbSaveArtist(formData) {
    const supabase = getSupabaseClient();
    if (!supabase) return sbError('Supabase client tidak tersedia.');

    try {
        const artistId = formData.get('artist_id');
        let avatarUrl  = formData.get('current_avatar') || null;

        // Upload foto profil baru jika ada file
        const photoFile = formData.get('artist_photo');
        if (photoFile && photoFile instanceof File && photoFile.size > 0) {
            const ext = photoFile.name.split('.').pop();
            const fileName = `avatars/${artistId || Date.now()}.${ext}`;
            const { data: uploadData, error: uploadErr } = await supabase.storage
                .from('jejak-waktu-assets')
                .upload(fileName, photoFile, { cacheControl: '3600', upsert: true });

            if (uploadErr) throw uploadErr;

            const { data: publicUrlData } = supabase.storage
                .from('jejak-waktu-assets')
                .getPublicUrl(fileName);

            avatarUrl = publicUrlData.publicUrl;
        }

        const payload = {
            name:     formData.get('artist_name') || '',
            role:     formData.get('artist_role') || null,
            era:      formData.get('artist_era') || null,
            category: formData.get('artist_category') || null,
            bio:      formData.get('artist_bio') || null,
            location: formData.get('artist_location') || null,
            avatar:   avatarUrl
        };

        let result;
        if (artistId) {
            result = await supabase
                .from('artist_profiles')
                .update(payload)
                .eq('id', artistId)
                .select()
                .single();
        } else {
            const newId = payload.name
                .toLowerCase()
                .replace(/[^a-z0-9]+/g, '-')
                .replace(/(^-|-$)/g, '')
                + '-' + Date.now().toString().slice(-4);

            result = await supabase
                .from('artist_profiles')
                .insert([{ id: newId, ...payload }])
                .select()
                .single();
        }

        if (result.error) throw result.error;
        const msg = artistId
            ? 'Profil seniman berhasil diperbarui.'
            : 'Profil seniman baru berhasil ditambahkan.';
        return sbSuccess(result.data, msg);
    } catch (err) {
        console.error('[Supabase] sbSaveArtist error:', err);
        return sbError(err.message, err.code);
    }
}

/**
 * Hapus seniman berdasarkan ID (soft-delete).
 * @param {string} artistId
 * @returns {Promise<{status, data, message}>}
 */
async function sbDeleteArtist(artistId) {
    const supabase = getSupabaseClient();
    if (!supabase) return sbError('Supabase client tidak tersedia.');

    try {
        // Soft-delete: set is_deleted = true
        const { error } = await supabase
            .from('artist_profiles')
            .update({ is_deleted: true })
            .eq('id', artistId);

        if (error) throw error;
        return sbSuccess(null, 'Data seniman berhasil dihapus dari direktori.');
    } catch (err) {
        console.error('[Supabase] sbDeleteArtist error:', err);
        return sbError(err.message, err.code);
    }
}

// Expose semua fungsi ke global scope
Object.assign(window, {
    // Albums
    sbFetchAlbums,
    sbAddPhoto,
    sbDeletePhoto,
    sbAddAlbum,
    sbDeleteAlbum,
    // Events
    sbFetchEvents,
    sbFetchEventById,
    sbSaveEvent,
    sbDeleteEvent,
    // Bookings
    sbFetchMyBookings,
    sbCreateBooking,
    sbCancelBooking,
    // Galleries
    sbFetchGalleries,
    // Feedbacks
    sbSubmitFeedback,
    sbFetchFeedbacks,
    sbFetchFeedbackCount,
    sbUpdateFeedbackStatus,
    sbDeleteFeedback,
    // Artists
    sbFetchArtists,
    sbSaveArtist,
    sbDeleteArtist
});
