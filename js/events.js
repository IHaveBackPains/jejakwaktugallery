/**
 * EVENTS.JS â€” Halaman Jadwal Event Seni "Jejak Waktu"
 * Mengelola: fetch events, render cards, filter, detail modal, booking, admin CRUD
 */

'use strict';

// ====================================================
// GLOBAL STATE
// ====================================================
// GLOBAL STATE & DEFAULT STATIC DATA
// ====================================================
const DEFAULT_EVENTS = [
    {
        id: 1,
        title: 'Pameran Besar Seni Rupa Nusantara 2026',
        theme: 'Jejak Peradaban: Antara Tradisi & Modernitas',
        status: 'ongoing',
        start_date: '2026-09-01',
        end_date: '2026-09-30',
        start_time: '09:00:00',
        end_time: '17:00:00',
        ticket_price: 'Gratis',
        ticket_quota: 500,
        location_text: 'Galeri Nasional Indonesia, Jl. Medan Merdeka Timur No. 14, Jakarta Pusat',
        location_map: '',
        artists: 'Affandi Jr., Yuli Prayitno, Maya Indah Lestari, Rendra Kusuma, Sinta Dewi',
        poster_image: 'https://images.unsplash.com/photo-1541961017774-22349e4a1262?auto=format&fit=crop&w=800&q=85',
        description: 'Sebuah perayaan agung seni rupa Indonesia yang menyatukan karya-karya terbaik seniman muda dan maestro senior dalam satu ruang pameran yang megah. Pameran ini menghadirkan lebih dari 120 karya pilihan mulai dari lukisan cat minyak, seni instalasi, hingga fotografi artistik berlatar sejarah Nusantara.',
        organizer: 'Yayasan Seni Jejak Waktu',
        album_id: null
    },
    {
        id: 2,
        title: 'Pameran Fotografi: Wajah Kota Lama',
        theme: 'Memori Visual Arsitektur Kolonial Nusantara',
        status: 'upcoming',
        start_date: '2026-10-15',
        end_date: '2026-10-25',
        start_time: '10:00:00',
        end_time: '20:00:00',
        ticket_price: 'Rp 25.000',
        ticket_quota: 200,
        location_text: 'Museum Fatahillah, Kota Tua Jakarta',
        location_map: '',
        artists: 'Ardi Nugraha, Lestari Wibowo, Foto Komunitas Kota Lama',
        poster_image: 'https://images.unsplash.com/photo-1578632767115-351597cf2477?auto=format&fit=crop&w=800&q=85',
        description: 'Pameran fotografi yang mengabadikan keindahan arsitektur kolonial dan kehidupan masyarakat di kawasan kota-kota lama bersejarah Indonesia. Lebih dari 80 karya foto analog dan digital ditampilkan berdampingan untuk menjembatani gap generasi dan teknologi dalam dunia fotografi.',
        organizer: 'Komunitas Foto Arsip Nusantara',
        album_id: 'jakarta-tempo-doeloe'
    },
    {
        id: 3,
        title: 'Festival Seni Miniatur & Sketsa Urban 2026',
        theme: 'Kota dalam Goresan: Urban Sketching Indonesia',
        status: 'upcoming',
        start_date: '2026-11-08',
        end_date: '2026-11-10',
        start_time: '08:00:00',
        end_time: '22:00:00',
        ticket_price: 'Rp 35.000',
        ticket_quota: 300,
        location_text: 'Braga Art Walk, Jl. Braga No. 1, Bandung',
        location_map: '',
        artists: 'Urban Sketchers Indonesia, Komunitas Sketsa Bandung, 50+ Seniman Lokal',
        poster_image: 'https://images.unsplash.com/photo-1513364776144-60967b0f800f?auto=format&fit=crop&w=800&q=85',
        description: 'Festival seni sketsa outdoor terbesar di Indonesia yang mengundang seniman dari 30 kota untuk melukis wajah kota Bandung secara langsung di jalanan, taman, dan ruang publik.',
        organizer: 'Urban Sketchers Indonesia & Pemkot Bandung',
        album_id: null
    },
    {
        id: 4,
        title: 'Lelang Karya Maestro & Seniman Muda',
        theme: 'Dari Kanvas ke Koleksi: Seni Untuk Semua',
        status: 'completed',
        start_date: '2026-08-20',
        end_date: '2026-08-20',
        start_time: '18:00:00',
        end_time: '22:00:00',
        ticket_price: 'Gratis (Peserta Lelang Terdaftar)',
        ticket_quota: 150,
        location_text: 'Hotel Raffles Jakarta, Jl. H.R. Rasuna Said Kav. 12, Jakarta Selatan',
        location_map: '',
        artists: 'Affandi Museum Collection, Basuki Abdullah Estate, Dede Eri Supria, Tisna Sanjaya',
        poster_image: 'https://images.unsplash.com/photo-1499343162172-9b73b4abb8a5?auto=format&fit=crop&w=800&q=85',
        description: 'Malam lelang bergengsi yang mempertemukan kolektor seni dengan karya-karya otentik maestro Indonesia dan karya segar seniman-seniman muda berbakat.',
        organizer: 'Jejak Waktu Arts Foundation',
        album_id: null
    }
];

let eventsData = [];           // Semua event dari API atau Local Fallback
let currentFilter = 'all';     // 'all' | 'upcoming' | 'ongoing' | 'completed'
let editingEventId = null;     // Null = mode tambah, number = mode edit
let myBookings = [];           // Booking milik user yang sedang login
let albumsList = [];           // Daftar album dari galeri

const isAdmin = () => window.CURRENT_USER && window.CURRENT_USER.role === 'admin';

// ====================================================
// INIT
// ====================================================
document.addEventListener('DOMContentLoaded', () => {
    initFilters();
    initCalendar();
    loadAllEvents();
    loadMyBookings();
    if (isAdmin) {
        loadAlbumsForForm();
    }
    initFormPosterPreview();
    initEventFormSubmit();

    // Tutup modal saat klik overlay
    const detailModal = document.getElementById('event-detail-modal');
    if (detailModal) {
        detailModal.addEventListener('click', (e) => {
            if (e.target === detailModal) closeEventDetailModal();
        });
    }
    const formModal = document.getElementById('event-form-modal');
    if (formModal) {
        formModal.addEventListener('click', (e) => {
            if (e.target === formModal) closeEventModal();
        });
    }
});

// ====================================================
// FILTER PILLS
// ====================================================
function initFilters() {
    const pillsContainer = document.getElementById('events-filter-pills');
    if (!pillsContainer) return;
    pillsContainer.addEventListener('click', (e) => {
        const pill = e.target.closest('.ev-pill');
        if (!pill) return;
        const filter = pill.getAttribute('data-filter');
        if (filter === currentFilter) return;
        currentFilter = filter;
        document.querySelectorAll('.ev-pill').forEach(p => p.classList.remove('active'));
        pill.classList.add('active');
        applyFilter();
    });
}

function applyFilter() {
    const sections = {
        upcoming: document.getElementById('section-upcoming'),
        ongoing:  document.getElementById('section-ongoing'),
        completed: document.getElementById('section-completed'),
    };
    const showAll = currentFilter === 'all';
    Object.entries(sections).forEach(([key, el]) => {
        const grid = document.getElementById('grid-' + key);
        const hasItems = grid && grid.children.length > 0;
        el.style.display = (showAll || currentFilter === key) && hasItems ? 'block' : 'none';
    });
    checkAllEmpty();
}

function checkAllEmpty() {
    const grids = ['upcoming', 'ongoing', 'completed'];
    const allEmpty = grids.every(s => {
        const grid = document.getElementById('grid-' + s);
        const sect = document.getElementById('section-' + s);
        return !grid || grid.children.length === 0 || sect.style.display === 'none';
    });
    document.getElementById('events-empty').style.display = allEmpty ? 'block' : 'none';
}

// ====================================================
// LOAD EVENTS & RENDER
// ====================================================
async function loadAllEvents() {
    showLoading(true);
    try {
        // Gunakan Supabase jika sudah dikonfigurasi
        if (typeof isSupabaseConfigured === 'function' && isSupabaseConfigured()) {
            const result = await sbFetchEvents();
            if (result.status === 'success') {
                eventsData = result.data || [];
                renderAllEvents();
                return;
            }
            throw new Error(result.message);
        }
        throw new Error('Supabase belum dikonfigurasi');
    } catch (err) {
        console.warn('Supabase events tidak tersedia, memuat data event bawaan/lokal:', err);
        const savedCustomEvents = localStorage.getItem('jejak_waktu_custom_events');
        let customList = [];
        if (savedCustomEvents) {
            try { customList = JSON.parse(savedCustomEvents); } catch(e){}
        }
        eventsData = [...customList, ...DEFAULT_EVENTS];
        renderAllEvents();
    }
}

function renderAllEvents() {
    renderEventCards();
    renderCalendar();
}

function renderEventCards() {
    const statuses = ['upcoming', 'ongoing', 'completed'];
    statuses.forEach(st => {
        const grid = document.getElementById('grid-' + st);
        if (!grid) return;
        grid.innerHTML = '';
        let filtered = eventsData.filter(e => e.status === st);
        if (selectedCalendarDate) {
            filtered = filtered.filter(ev => isEventActiveOnDate(ev, selectedCalendarDate));
        }
        filtered.forEach(ev => {
            grid.appendChild(buildEventCard(ev));
        });
    });
    showLoading(false);
    applyFilter();
}

// ====================================================
// KALENDER EVENT SENI INTERAKTIF
// ====================================================
let calViewDate = new Date();       // Bulan dan tahun yang sedang dilihat di kalender
let selectedCalendarDate = null;    // 'YYYY-MM-DD' atau null
const INDO_MONTHS = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
const INDO_DAYS = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];

function isEventActiveOnDate(ev, dateStr) {
    if (!ev || !ev.start_date) return false;
    const start = ev.start_date;
    const end = ev.end_date || ev.start_date;
    return dateStr >= start && dateStr <= end;
}

function getEventsForDate(dateStr) {
    return eventsData.filter(ev => isEventActiveOnDate(ev, dateStr));
}

function initCalendar() {
    const prevBtn = document.getElementById('cal-prev-month');
    const nextBtn = document.getElementById('cal-next-month');
    const todayBtn = document.getElementById('cal-today-btn');
    const ongoingBtn = document.getElementById('cal-filter-ongoing');
    const resetBtn = document.getElementById('cal-reset-filter-btn');
    const bannerClearBtn = document.getElementById('filter-banner-clear-btn');

    if (prevBtn) {
        prevBtn.addEventListener('click', () => {
            calViewDate.setMonth(calViewDate.getMonth() - 1);
            renderCalendar();
        });
    }
    if (nextBtn) {
        nextBtn.addEventListener('click', () => {
            calViewDate.setMonth(calViewDate.getMonth() + 1);
            renderCalendar();
        });
    }
    if (todayBtn) {
        todayBtn.addEventListener('click', () => {
            calViewDate = new Date();
            renderCalendar();
        });
    }
    if (ongoingBtn) {
        ongoingBtn.addEventListener('click', () => {
            filterOngoingEventsInstant();
        });
    }
    if (resetBtn) {
        resetBtn.addEventListener('click', () => {
            clearDateFilter();
        });
    }
    if (bannerClearBtn) {
        bannerClearBtn.addEventListener('click', () => {
            clearDateFilter();
        });
    }
}

function filterOngoingEventsInstant() {
    // Saring dan lihat event yang sedang berjalan secara instan
    const ongoingEvents = eventsData.filter(e => e.status === 'ongoing');
    if (ongoingEvents.length > 0) {
        const firstOngoing = ongoingEvents[0];
        if (firstOngoing.start_date) {
            const parts = firstOngoing.start_date.split('-');
            calViewDate = new Date(parseInt(parts[0]), parseInt(parts[1]) - 1, 1);
        }
    }

    // Set filter pil ke 'ongoing'
    currentFilter = 'ongoing';
    document.querySelectorAll('.ev-pill').forEach(p => {
        if (p.getAttribute('data-filter') === 'ongoing') {
            p.classList.add('active');
        } else {
            p.classList.remove('active');
        }
    });

    // Reset filter tanggal spesifik agar seluruh event ongoing ditampilkan
    selectedCalendarDate = null;
    renderCalendar();
    renderEventCards();

    // Beri penanda visual animasi pulsasi pada cell yang memiliki event ongoing
    const dayCells = document.querySelectorAll('.cal-day-cell.has-ongoing');
    dayCells.forEach(cell => {
        cell.classList.add('cal-ongoing-highlight');
        setTimeout(() => cell.classList.remove('cal-ongoing-highlight'), 3500);
    });

    // Perbarui panel agenda dengan daftar pameran ongoing
    updateAgendaPanelOngoing(ongoingEvents);

    // Scroll halus ke daftar event yang sedang berjalan
    const ongoingSection = document.getElementById('section-ongoing');
    if (ongoingSection) {
        ongoingSection.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }
}

function renderCalendar() {
    const label = document.getElementById('cal-month-year-label');
    const grid = document.getElementById('calendar-days-grid');
    const ongoingCountEl = document.getElementById('cal-ongoing-count');
    const resetBtn = document.getElementById('cal-reset-filter-btn');

    if (!grid) return;

    const year = calViewDate.getFullYear();
    const month = calViewDate.getMonth();

    if (label) {
        label.textContent = `${INDO_MONTHS[month]} ${year}`;
    }

    // Update counter event ongoing di tombol quick filter
    const ongoingCount = eventsData.filter(e => e.status === 'ongoing').length;
    if (ongoingCountEl) {
        ongoingCountEl.textContent = ongoingCount;
    }

    // Tombol reset filter visibilitas
    if (resetBtn) {
        resetBtn.style.display = selectedCalendarDate ? 'inline-flex' : 'none';
    }

    // Perhitungan hari kalender
    const firstDayIndex = new Date(year, month, 1).getDay(); // 0 = Minggu
    const daysInMonth = new Date(year, month + 1, 0).getDate();
    const daysInPrevMonth = new Date(year, month, 0).getDate();

    const today = new Date();
    const todayStr = `${today.getFullYear()}-${String(today.getMonth() + 1).padStart(2, '0')}-${String(today.getDate()).padStart(2, '0')}`;

    grid.innerHTML = '';

    // 1. Hari-hari bulan sebelumnya (padding)
    for (let i = firstDayIndex - 1; i >= 0; i--) {
        const cell = document.createElement('div');
        cell.className = 'cal-day-cell other-month';
        cell.innerHTML = `<span class="cal-day-num">${daysInPrevMonth - i}</span>`;
        grid.appendChild(cell);
    }

    // 2. Hari-hari bulan aktif
    for (let d = 1; d <= daysInMonth; d++) {
        const dateStr = `${year}-${String(month + 1).padStart(2, '0')}-${String(d).padStart(2, '0')}`;
        const dayEvents = getEventsForDate(dateStr);
        const dayOfWeek = (firstDayIndex + d - 1) % 7;
        const isWeekend = (dayOfWeek === 0 || dayOfWeek === 6);
        const isToday = (dateStr === todayStr);
        const isSelected = (dateStr === selectedCalendarDate);

        const hasOngoing = dayEvents.some(e => e.status === 'ongoing');
        const hasUpcoming = dayEvents.some(e => e.status === 'upcoming');
        const hasCompleted = dayEvents.some(e => e.status === 'completed');

        const cell = document.createElement('div');
        let classes = ['cal-day-cell'];
        if (isWeekend) classes.push('weekend');
        if (isToday) classes.push('is-today');
        if (isSelected) classes.push('is-selected');

        if (dayEvents.length > 0) {
            classes.push('has-events');
            if (hasOngoing) classes.push('has-ongoing');
            if (hasUpcoming) classes.push('has-upcoming');
            if (hasCompleted) classes.push('has-completed');
        }

        cell.className = classes.join(' ');
        cell.setAttribute('data-date', dateStr);

        let tooltipTitles = dayEvents.map(e => e.title).join(' â€¢ ');
        if (tooltipTitles) {
            cell.setAttribute('title', `${d} ${INDO_MONTHS[month]}: ${tooltipTitles}`);
        }

        let indicatorsHtml = '';
        if (dayEvents.length > 0) {
            indicatorsHtml = `
                <div class="cal-day-indicators">
                    ${hasOngoing ? '<span class="cal-dot dot-ongoing" title="Sedang Berlangsung"></span>' : ''}
                    ${hasUpcoming ? '<span class="cal-dot dot-upcoming" title="Akan Datang"></span>' : ''}
                    ${hasCompleted ? '<span class="cal-dot dot-completed" title="Telah Selesai"></span>' : ''}
                    ${dayEvents.length > 1 ? `<span class="cal-event-badge">${dayEvents.length}</span>` : ''}
                </div>
            `;
        }

        cell.innerHTML = `
            <span class="cal-day-num">${d}</span>
            ${indicatorsHtml}
        `;

        cell.addEventListener('click', () => {
            handleCalendarDayClick(dateStr);
        });

        grid.appendChild(cell);
    }

    // 3. Hari-hari bulan berikutnya untuk melengkapi baris
    const totalRendered = firstDayIndex + daysInMonth;
    const remaining = (7 - (totalRendered % 7)) % 7;
    for (let j = 1; j <= remaining; j++) {
        const cell = document.createElement('div');
        cell.className = 'cal-day-cell other-month';
        cell.innerHTML = `<span class="cal-day-num">${j}</span>`;
        grid.appendChild(cell);
    }

    // Update panel agenda samping & filter banner
    if (selectedCalendarDate) {
        updateAgendaPanel(selectedCalendarDate);
        updateFilterBanner(selectedCalendarDate);
    } else {
        updateAgendaPanelOverview();
        updateFilterBanner(null);
    }
}

function handleCalendarDayClick(dateStr) {
    if (selectedCalendarDate === dateStr) {
        // Klik kedua kali pada tanggal yang sama: batalkan filter
        clearDateFilter();
        return;
    }

    selectedCalendarDate = dateStr;

    // Update active highlight di grid kalender
    document.querySelectorAll('.cal-day-cell').forEach(c => {
        if (c.getAttribute('data-date') === dateStr) {
            c.classList.add('is-selected');
        } else {
            c.classList.remove('is-selected');
        }
    });

    const resetBtn = document.getElementById('cal-reset-filter-btn');
    if (resetBtn) resetBtn.style.display = 'inline-flex';

    updateAgendaPanel(dateStr);
    updateFilterBanner(dateStr);
    renderEventCards();
}

function clearDateFilter() {
    selectedCalendarDate = null;

    document.querySelectorAll('.cal-day-cell').forEach(c => c.classList.remove('is-selected'));

    const resetBtn = document.getElementById('cal-reset-filter-btn');
    if (resetBtn) resetBtn.style.display = 'none';

    updateFilterBanner(null);
    updateAgendaPanelOverview();
    renderEventCards();
}

function updateFilterBanner(dateStr) {
    const banner = document.getElementById('calendar-filter-banner');
    const textEl = document.getElementById('filter-banner-date-text');
    const countBadge = document.getElementById('filter-banner-count-badge');
    if (!banner) return;

    if (!dateStr) {
        banner.style.display = 'none';
        return;
    }

    banner.style.display = 'flex';
    const dayEvents = getEventsForDate(dateStr);
    if (textEl) {
        textEl.textContent = formatIndoDateString(dateStr);
    }
    if (countBadge) {
        countBadge.textContent = `${dayEvents.length} Event ditemukan`;
    }
}

function formatIndoDateString(dateStr) {
    if (!dateStr) return '';
    const parts = dateStr.split('-');
    if (parts.length < 3) return dateStr;
    const d = parseInt(parts[2]);
    const m = parseInt(parts[1]) - 1;
    const y = parts[0];
    return `${d} ${INDO_MONTHS[m]} ${y}`;
}

function updateAgendaPanel(dateStr) {
    const dayNumEl = document.getElementById('agenda-day-num');
    const monthYearEl = document.getElementById('agenda-month-year');
    const dayNameEl = document.getElementById('agenda-day-name');
    const countEl = document.getElementById('agenda-event-count');
    const contentEl = document.getElementById('agenda-card-content');

    if (!contentEl) return;

    const parts = dateStr.split('-');
    const y = parseInt(parts[0]);
    const m = parseInt(parts[1]) - 1;
    const d = parseInt(parts[2]);
    const dObj = new Date(y, m, d);

    if (dayNumEl) dayNumEl.textContent = d;
    if (monthYearEl) monthYearEl.textContent = `${INDO_MONTHS[m]} ${y}`;
    if (dayNameEl) dayNameEl.textContent = INDO_DAYS[dObj.getDay()];

    const dayEvents = getEventsForDate(dateStr);
    if (countEl) countEl.textContent = `${dayEvents.length} Event`;

    if (dayEvents.length === 0) {
        contentEl.innerHTML = `
            <div class="agenda-empty">
                <span class="agenda-empty-icon">ðŸ“­</span>
                <div>Tidak ada jadwal event seni pada tanggal ini.</div>
                <div style="margin-top:8px; font-size:0.75rem; color:#9e7a45;">
                    Silakan klik tanggal yang memiliki titik warna penanda di kalender atau <a href="javascript:void(0)" onclick="clearDateFilter()" style="color:#b38241; text-decoration:underline;">tampilkan semua event</a>.
                </div>
            </div>
        `;
        return;
    }

    contentEl.innerHTML = dayEvents.map(ev => {
        const statusLabel = { upcoming: 'ðŸ—“ Akan Datang', ongoing: 'ðŸŸ¢ Berlangsung', completed: 'âœ… Selesai' };
        const timeStr = ev.start_time ? formatTime(ev.start_time) + (ev.end_time ? ' â€“ ' + formatTime(ev.end_time) : '') : 'Sepanjang Hari';
        const dateRangeStr = formatDateRange(ev.start_date, ev.end_date);

        return `
            <div class="agenda-item status-${ev.status}">
                <div class="agenda-item-header">
                    <div class="agenda-item-title" onclick="openEventDetail(${ev.id})" title="Klik untuk lihat detail">${escHtml(ev.title)}</div>
                    <span class="event-card-status status-${ev.status}" style="font-size:0.65rem; padding:2px 6px;">
                        ${statusLabel[ev.status] || ev.status}
                    </span>
                </div>
                ${ev.theme ? `<div class="agenda-item-theme">${escHtml(ev.theme)}</div>` : ''}
                <div class="agenda-item-meta">
                    <div>ðŸ“… ${escHtml(dateRangeStr)}</div>
                    <div>ðŸ• ${escHtml(timeStr)}</div>
                    ${ev.location_text ? `<div>ðŸ“ ${escHtml(ev.location_text)}</div>` : ''}
                    <div>ðŸŽŸ ${ev.ticket_price === 'Gratis' || !ev.ticket_price ? 'Gratis' : escHtml(ev.ticket_price)}</div>
                </div>
                <div class="agenda-item-actions">
                    <button type="button" class="agenda-item-btn" onclick="openEventDetail(${ev.id})">
                        ðŸ” Lihat Detail &amp; Tiket
                    </button>
                </div>
            </div>
        `;
    }).join('');
}

function updateAgendaPanelOverview() {
    const dayNumEl = document.getElementById('agenda-day-num');
    const monthYearEl = document.getElementById('agenda-month-year');
    const dayNameEl = document.getElementById('agenda-day-name');
    const countEl = document.getElementById('agenda-event-count');
    const contentEl = document.getElementById('agenda-card-content');

    if (!contentEl) return;

    const today = new Date();
    if (dayNumEl) dayNumEl.textContent = today.getDate();
    if (monthYearEl) monthYearEl.textContent = `${INDO_MONTHS[today.getMonth()]} ${today.getFullYear()}`;
    if (dayNameEl) dayNameEl.textContent = INDO_DAYS[today.getDay()];
    if (countEl) countEl.textContent = `${eventsData.length} Total`;

    const ongoingEvents = eventsData.filter(e => e.status === 'ongoing');
    const upcomingEvents = eventsData.filter(e => e.status === 'upcoming');

    let html = `
        <div style="background: rgba(235,220,196,0.35); border: 1px dashed #cbba9d; border-radius: 4px; padding: 10px 12px; margin-bottom: 8px;">
            <div style="font-family: var(--font-typewriter); font-size: 0.76rem; color: #523826; font-weight: 700; margin-bottom: 4px;">
                ðŸ’¡ Petunjuk Interaksi
            </div>
            <div style="font-family: var(--font-typewriter); font-size: 0.72rem; color: #78604d; line-height: 1.4;">
                Klik salah satu tanggal bertanda di kalender untuk memfilter dan menampilkan daftar event pada hari tersebut.
            </div>
        </div>
    `;

    if (ongoingEvents.length > 0) {
        html += `
            <div style="font-family: var(--font-typewriter); font-size: 0.74rem; font-weight: 700; color: #0f5132; margin-top: 4px; text-transform: uppercase;">
                ðŸŸ¢ Sedang Berlangsung Sekarang:
            </div>
        `;
        ongoingEvents.forEach(ev => {
            html += `
                <div class="agenda-item status-ongoing">
                    <div class="agenda-item-header">
                        <div class="agenda-item-title" onclick="openEventDetail(${ev.id})">${escHtml(ev.title)}</div>
                        <span class="event-card-status status-ongoing" style="font-size:0.62rem; padding:2px 5px;">Sedang Jalan</span>
                    </div>
                    <div class="agenda-item-meta">
                        <div>ðŸ“… ${escHtml(formatDateRange(ev.start_date, ev.end_date))}</div>
                        ${ev.location_text ? `<div>ðŸ“ ${escHtml(ev.location_text)}</div>` : ''}
                    </div>
                    <button type="button" class="agenda-item-btn" onclick="openEventDetail(${ev.id})">
                        ðŸ” Lihat Detail Event
                    </button>
                </div>
            `;
        });
    } else if (upcomingEvents.length > 0) {
        html += `
            <div style="font-family: var(--font-typewriter); font-size: 0.74rem; font-weight: 700; color: #b38241; margin-top: 4px; text-transform: uppercase;">
                ðŸ—“ Segera Hadir:
            </div>
        `;
        upcomingEvents.slice(0, 2).forEach(ev => {
            html += `
                <div class="agenda-item status-upcoming">
                    <div class="agenda-item-header">
                        <div class="agenda-item-title" onclick="openEventDetail(${ev.id})">${escHtml(ev.title)}</div>
                    </div>
                    <div class="agenda-item-meta">
                        <div>ðŸ“… ${escHtml(formatDateRange(ev.start_date, ev.end_date))}</div>
                    </div>
                    <button type="button" class="agenda-item-btn" onclick="openEventDetail(${ev.id})">
                        ðŸ” Lihat Detail Event
                    </button>
                </div>
            `;
        });
    }

    contentEl.innerHTML = html;
}

function updateAgendaPanelOngoing(ongoingEvents) {
    const dayNumEl = document.getElementById('agenda-day-num');
    const monthYearEl = document.getElementById('agenda-month-year');
    const dayNameEl = document.getElementById('agenda-day-name');
    const countEl = document.getElementById('agenda-event-count');
    const contentEl = document.getElementById('agenda-card-content');

    if (!contentEl) return;

    if (dayNumEl) dayNumEl.textContent = 'ðŸŸ¢';
    if (monthYearEl) monthYearEl.textContent = 'Event Berjalan';
    if (dayNameEl) dayNameEl.textContent = 'Semua Berlangsung';
    if (countEl) countEl.textContent = `${ongoingEvents.length} Event`;

    if (ongoingEvents.length === 0) {
        contentEl.innerHTML = `
            <div class="agenda-empty">
                <span class="agenda-empty-icon">ðŸƒ</span>
                <div>Saat ini tidak ada event yang berstatus sedang berlangsung.</div>
            </div>
        `;
        return;
    }

    contentEl.innerHTML = ongoingEvents.map(ev => `
        <div class="agenda-item status-ongoing">
            <div class="agenda-item-header">
                <div class="agenda-item-title" onclick="openEventDetail(${ev.id})">${escHtml(ev.title)}</div>
                <span class="event-card-status status-ongoing" style="font-size:0.65rem; padding:2px 6px;">ðŸŸ¢ Berlangsung</span>
            </div>
            ${ev.theme ? `<div class="agenda-item-theme">${escHtml(ev.theme)}</div>` : ''}
            <div class="agenda-item-meta">
                <div>ðŸ“… ${escHtml(formatDateRange(ev.start_date, ev.end_date))}</div>
                ${ev.location_text ? `<div>ðŸ“ ${escHtml(ev.location_text)}</div>` : ''}
            </div>
            <button type="button" class="agenda-item-btn" onclick="openEventDetail(${ev.id})">
                ðŸ” Lihat Detail &amp; Tiket
            </button>
        </div>
    `).join('');
}

// Global functions for inline HTML handlers
window.clearDateFilter = clearDateFilter;
window.handleCalendarDayClick = handleCalendarDayClick;
window.filterOngoingEventsInstant = filterOngoingEventsInstant;

function buildEventCard(ev) {
    const card = document.createElement('div');
    card.className = 'event-card';
    card.setAttribute('data-event-id', ev.id);

    const statusLabel = { upcoming: 'Akan Datang', ongoing: 'Sedang Berlangsung', completed: 'Telah Selesai' };
    const statusClass = { upcoming: 'status-upcoming', ongoing: 'status-ongoing', completed: 'status-completed' };

    const dateStr = formatDateRange(ev.start_date, ev.end_date);
    const timeStr = ev.start_time ? formatTime(ev.start_time) + (ev.end_time ? ' â€“ ' + formatTime(ev.end_time) : '') : '';
    const isBooked = myBookings.some(b => b.event_id == ev.id && b.status !== 'cancelled');
    const quotaFull = ev.ticket_quota && ev.booked_count >= ev.ticket_quota;

    card.innerHTML = `
        ${ev.poster_image
            ? `<img class="event-card-poster" src="${escHtml(ev.poster_image)}" alt="${escHtml(ev.title)}" loading="lazy" onerror="this.parentNode.innerHTML='<div class=\\'event-card-poster-placeholder\\'>ðŸŽ¨</div>'" />`
            : `<div class="event-card-poster-placeholder">ðŸŽ¨</div>`
        }
        <div class="event-card-body">
            <span class="event-card-status ${statusClass[ev.status] || 'status-upcoming'}">${statusLabel[ev.status] || ev.status}</span>
            <div class="event-card-title">${escHtml(ev.title)}</div>
            ${ev.theme ? `<div class="event-card-theme">${escHtml(ev.theme)}</div>` : ''}
            <div class="event-card-meta">
                <div class="event-card-meta-row"><span class="event-card-meta-icon">ðŸ“…</span>${escHtml(dateStr)}</div>
                ${timeStr ? `<div class="event-card-meta-row"><span class="event-card-meta-icon">ðŸ•</span>${escHtml(timeStr)}</div>` : ''}
                ${ev.location_text ? `<div class="event-card-meta-row"><span class="event-card-meta-icon">ðŸ“</span>${escHtml(ev.location_text)}</div>` : ''}
                ${ev.ticket_quota ? `<div class="event-card-meta-row"><span class="event-card-meta-icon">ðŸŽ«</span>${ev.booked_count}/${ev.ticket_quota} pendaftar</div>` : ''}
            </div>
            <div class="event-card-footer">
                <span class="event-card-price">${ev.ticket_price === 'Gratis' || !ev.ticket_price ? 'ðŸŽŸ Gratis' : 'ðŸ’° ' + escHtml(ev.ticket_price)}</span>
                <button class="event-card-btn ${isBooked ? 'booked-btn' : ''}"
                    onclick="openEventDetail(${ev.id})"
                    ${ev.status === 'completed' ? '' : ''}>
                    ${isBooked ? 'âœ… Sudah Terdaftar' : (ev.status === 'completed' ? 'Lihat Detail' : (quotaFull ? 'Kuota Penuh' : 'Detail & Daftar'))}
                </button>
            </div>
        </div>
        ${isAdmin ? `
        <div class="event-card-admin-actions">
            <button class="ev-btn-secondary" style="font-size:0.72rem; padding:5px 10px;" onclick="editEvent(${ev.id}, event)">âœ Edit</button>
            <button class="ev-btn-danger" onclick="deleteEvent(${ev.id}, event)">ðŸ—‘ Hapus</button>
        </div>` : ''}
    `;
    return card;
}

// ====================================================
// EVENT DETAIL MODAL + BOOKING
// ====================================================
async function openEventDetail(eventId) {
    const ev = eventsData.find(e => e.id == eventId);
    if (!ev) return;

    const modal = document.getElementById('event-detail-modal');
    const content = document.getElementById('event-detail-content');
    modal.classList.add('open');
    modal.setAttribute('aria-hidden', 'false');
    document.body.style.overflow = 'hidden';

    content.innerHTML = '<div style="text-align:center; padding:40px; font-family:var(--font-typewriter, \'Special Elite\', monospace); color:#9e7a45;">Memuat detail event...</div>';

    // Fetch fresh data (termasuk booked_count terbaru) dari Supabase
    let freshEv = ev;
    try {
        if (typeof isSupabaseConfigured === 'function' && isSupabaseConfigured()) {
            const result = await sbFetchEventById(eventId);
            if (result.status === 'success') freshEv = result.data;
        }
    } catch (_) {}

    // Cek apakah user sudah booking event ini
    const myBookingForThis = myBookings.find(b => b.event_id == eventId && b.status !== 'cancelled');

    const statusLabel = { upcoming: 'ðŸ—“ Akan Datang', ongoing: 'ðŸŸ¢ Sedang Berlangsung', completed: 'âœ… Telah Selesai' };
    const statusClass = { upcoming: 'status-upcoming', ongoing: 'status-ongoing', completed: 'status-completed' };
    const dateStr = formatDateRange(freshEv.start_date, freshEv.end_date);
    const timeStr = freshEv.start_time ? formatTime(freshEv.start_time) + (freshEv.end_time ? ' â€“ ' + formatTime(freshEv.end_time) : '') : 'Sepanjang Hari';

    const quotaPct = freshEv.ticket_quota ? Math.min(100, Math.round((freshEv.booked_count / freshEv.ticket_quota) * 100)) : 0;
    const quotaFull = freshEv.ticket_quota && freshEv.booked_count >= freshEv.ticket_quota;

    let bookingSection = '';
    if (freshEv.status === 'completed') {
        bookingSection = `<div style="background:#e9ecef; border-radius:5px; padding:14px 16px; font-family:var(--font-typewriter,'Special Elite',monospace); font-size:0.85rem; color:#6c757d; text-align:center;">
            Event ini telah selesai. Pendaftaran ditutup.
        </div>`;
    } else if (myBookingForThis) {
        bookingSection = `<div class="event-booking-section">
            <h4 class="event-booking-title">ðŸŽ« Status Tiket Anda</h4>
            <div class="booking-confirmed-badge">
                <span style="font-size:1.5rem;">âœ…</span>
                <div>
                    <div>Anda telah terdaftar pada event ini!</div>
                    <div class="booking-code-highlight">${escHtml(myBookingForThis.booking_code || 'N/A')}</div>
                    <div style="margin-top:4px; font-size:0.75rem; color:#0f5132;">Jumlah tiket: ${myBookingForThis.qty} | Total: ${escHtml(myBookingForThis.total_price || 'Gratis')}</div>
                </div>
            </div>
            <button class="ev-btn-danger" style="margin-top:12px; width:100%; justify-content:center;"
                onclick="cancelBooking(${myBookingForThis.id})">
                âœ• Batalkan Pendaftaran
            </button>
        </div>`;
    } else if (quotaFull) {
        bookingSection = `<div class="event-booking-section">
            <h4 class="event-booking-title">ðŸŽ« Pendaftaran</h4>
            <div style="text-align:center; padding:16px; font-family:var(--font-typewriter,'Special Elite',monospace); font-size:0.85rem; color:#856404; background:#fff3cd; border-radius:4px;">
                Kuota pendaftar telah penuh (${freshEv.booked_count}/${freshEv.ticket_quota} pendaftar)
            </div>
        </div>`;
    } else {
        const priceIsGratis = !freshEv.ticket_price || freshEv.ticket_price.toLowerCase() === 'gratis';
        bookingSection = `<div class="event-booking-section">
            <h4 class="event-booking-title">ðŸŽ« Daftarkan Diri Anda</h4>
            ${freshEv.ticket_quota ? `
            <div class="event-quota-bar-wrap">
                <div class="event-quota-text">Kuota: ${freshEv.booked_count} / ${freshEv.ticket_quota} telah mendaftar (${quotaPct}%)</div>
                <div class="event-quota-bar"><div class="event-quota-fill" style="width:${quotaPct}%"></div></div>
            </div>` : ''}
            ${!priceIsGratis ? `
            <div class="booking-qty-row">
                <span class="booking-qty-label">Jumlah Tiket:</span>
                <input type="number" class="booking-qty-input" id="booking-qty-input" min="1" max="${freshEv.ticket_quota ? freshEv.ticket_quota - freshEv.booked_count : 10}" value="1" />
                <span class="booking-qty-label">@ ${escHtml(freshEv.ticket_price)}</span>
            </div>` : ''}
            <button class="ev-btn-primary" style="width:100%; justify-content:center; padding:11px;" id="btn-book-event" onclick="submitBooking(${freshEv.id})">
                ${priceIsGratis ? 'ðŸŽŸ Daftar Gratis Sekarang' : 'ðŸ’³ Pesan Tiket Sekarang'}
            </button>
            <p style="font-family:var(--font-typewriter,'Special Elite',monospace); font-size:0.72rem; color:#9e7a45; text-align:center; margin-top:8px; margin-bottom:0;">
                Tiket akan dikonfirmasi secara otomatis dan kode booking dikirim ke akun Anda.
            </p>
        </div>`;
    }

    // Map embed atau link
    let mapSection = '';
    if (freshEv.location_map) {
        const mapVal = freshEv.location_map;
        const isIframeSrc = mapVal.startsWith('http') && (mapVal.includes('maps.google') || mapVal.includes('google.com/maps') || mapVal.includes('openstreetmap'));
        mapSection = `<div style="margin-bottom:20px;">
            <div style="font-family:var(--font-typewriter,'Special Elite',monospace); font-size:0.7rem; text-transform:uppercase; letter-spacing:0.06em; color:#9e7a45; margin-bottom:8px;">ðŸ—º Peta Lokasi</div>
            <iframe src="${escHtml(mapVal)}" class="event-map-frame" frameborder="0" allowfullscreen loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
        </div>`;
    } else if (freshEv.location_text) {
        const mapsUrl = 'https://maps.google.com/?q=' + encodeURIComponent(freshEv.location_text);
        mapSection = `<div style="margin-bottom:20px;">
            <a href="${mapsUrl}" target="_blank" rel="noopener" class="ev-btn-secondary" style="font-size:0.78rem;">ðŸ—º Buka di Google Maps</a>
        </div>`;
    }

    content.innerHTML = `
        ${freshEv.poster_image
            ? `<img src="${escHtml(freshEv.poster_image)}" alt="${escHtml(freshEv.title)}" class="event-detail-poster" onerror="this.style.display='none'" />`
            : `<div class="event-card-poster-placeholder" style="height:200px; margin-bottom:20px; border-radius:4px; border:1px solid #d4c4a0;">ðŸŽ¨</div>`
        }
        <div class="event-detail-header">
            <span class="event-card-status ${statusClass[freshEv.status]} event-detail-status-badge">${statusLabel[freshEv.status] || freshEv.status}</span>
            <h2 class="event-detail-title">${escHtml(freshEv.title)}</h2>
            ${freshEv.theme ? `<p class="event-detail-theme">${escHtml(freshEv.theme)}</p>` : ''}
        </div>

        <div class="event-detail-meta-grid">
            <div class="detail-meta-item">
                <span class="detail-meta-label">ðŸ“… Tanggal</span>
                <span class="detail-meta-value">${escHtml(dateStr)}</span>
            </div>
            <div class="detail-meta-item">
                <span class="detail-meta-label">ðŸ• Waktu</span>
                <span class="detail-meta-value">${escHtml(timeStr)}</span>
            </div>
            <div class="detail-meta-item">
                <span class="detail-meta-label">ðŸ“ Lokasi</span>
                <span class="detail-meta-value">${freshEv.location_text ? escHtml(freshEv.location_text) : 'â€”'}</span>
            </div>
            <div class="detail-meta-item">
                <span class="detail-meta-label">ðŸ’° Tiket</span>
                <span class="detail-meta-value">${freshEv.ticket_price ? escHtml(freshEv.ticket_price) : 'Gratis'}</span>
            </div>
            <div class="detail-meta-item">
                <span class="detail-meta-label">ðŸ› Penyelenggara</span>
                <span class="detail-meta-value">${freshEv.organizer ? escHtml(freshEv.organizer) : 'Jejak Waktu'}</span>
            </div>
            ${freshEv.ticket_quota ? `
            <div class="detail-meta-item">
                <span class="detail-meta-label">ðŸŽ« Pendaftar</span>
                <span class="detail-meta-value">${freshEv.booked_count} / ${freshEv.ticket_quota}</span>
            </div>` : ''}
        </div>

        ${freshEv.description ? `<p class="event-detail-description">${escHtml(freshEv.description).replace(/\n/g,'<br>')}</p>` : ''}

        ${freshEv.artists ? `
        <div class="event-detail-artists">
            <div class="event-detail-artists-label">ðŸŽ¨ Seniman / Peserta Pameran</div>
            <div class="event-detail-artists-list">${escHtml(freshEv.artists).replace(/\n/g, '<br>')}</div>
        </div>` : ''}

        ${mapSection}
        ${bookingSection}
    `;
}

function closeEventDetailModal() {
    const modal = document.getElementById('event-detail-modal');
    modal.classList.remove('open');
    modal.setAttribute('aria-hidden', 'true');
    document.body.style.overflow = '';
}

// ====================================================
// BOOKING SUBMIT
// ====================================================
async function submitBooking(eventId) {
    const btn = document.getElementById('btn-book-event');
    if (btn) { btn.disabled = true; btn.textContent = 'Memproses...'; }

    const qtyInput = document.getElementById('booking-qty-input');
    const qty = qtyInput ? parseInt(qtyInput.value) || 1 : 1;

    const fd = new FormData();
    fd.append('event_id', eventId);
    fd.append('qty', qty);

    try {
        let json;
        if (typeof sbCreateBooking === 'function') {
            json = await sbCreateBooking(eventId, qty);
        } else {
            const resp = await fetch('api/bookings.json', { method: 'POST', body: fd });
            json = await resp.json();
        }
        if (json.status === 'success') {
            showToast('âœ… ' + json.message, 'success');
            await loadMyBookings();
            closeEventDetailModal();
            loadAllEvents();
        } else {
            showToast('âš  ' + json.message, 'error');
            if (btn) { btn.disabled = false; btn.textContent = 'ðŸŽŸ Coba Lagi'; }
        }
    } catch (err) {
        console.warn('API bookings offline, menyimpan booking secara lokal:', err);
        const ev = eventsData.find(x => x.id == eventId);
        const localBooking = {
            id: Date.now(),
            event_id: eventId,
            event_title: ev ? ev.title : 'Event Seni',
            theme: ev ? ev.theme : '',
            status: ev ? ev.status : 'upcoming',
            start_date: ev ? ev.start_date : '',
            end_date: ev ? ev.end_date : '',
            start_time: ev ? ev.start_time : '',
            end_time: ev ? ev.end_time : '',
            location_text: ev ? ev.location_text : '',
            poster_image: ev ? ev.poster_image : '',
            ticket_price: ev ? ev.ticket_price : 'Gratis',
            qty: qty,
            booking_code: 'JW-' + Math.floor(100000 + Math.random() * 900000),
            created_at: new Date().toISOString()
        };
        const savedBookings = localStorage.getItem('jejak_waktu_my_bookings');
        let bList = savedBookings ? JSON.parse(savedBookings) : [];
        bList.unshift(localBooking);
        localStorage.setItem('jejak_waktu_my_bookings', JSON.stringify(bList));
        showToast('âœ… Tiket berhasil didaftarkan (Tersimpan Lokal)!', 'success');
        await loadMyBookings();
        closeEventDetailModal();
    }
}

async function cancelBooking(bookingId) {
    if (!confirm('Yakin ingin membatalkan pendaftaran event ini?')) return;
    try {
        const resp = await fetch(`api/bookings.json?_method=DELETE&id=${bookingId}`, { method: 'POST' });
        const json = await resp.json();
        if (json.status === 'success') {
            showToast('âœ… Pendaftaran berhasil dibatalkan', 'success');
            await loadMyBookings();
            closeEventDetailModal();
            loadAllEvents();
        } else {
            showToast('âš  ' + json.message, 'error');
        }
    } catch (err) {
        showToast('Gagal: ' + err.message, 'error');
    }
}

// ====================================================
// MY BOOKINGS
// ====================================================
async function loadMyBookings() {
    try {
        if (typeof isSupabaseConfigured === 'function' && isSupabaseConfigured()) {
            const result = await sbFetchMyBookings();
            myBookings = result.status === 'success' ? result.data : [];
            renderMyBookings();
            return;
        }
        throw new Error('Supabase belum dikonfigurasi');
    } catch (_) {
        const savedBookings = localStorage.getItem('jejak_waktu_my_bookings');
        myBookings = savedBookings ? JSON.parse(savedBookings) : [];
        renderMyBookings();
    }
}

function renderMyBookings() {
    const container = document.getElementById('my-bookings-list');
    if (!container) return;

    if (!myBookings || myBookings.length === 0) {
        container.innerHTML = `<div class="bookings-empty">
            ðŸŽ­ Anda belum memiliki tiket booking event apapun.<br>
            <span style="font-size:0.8rem;">Jelajahi event di atas dan daftarkan diri Anda!</span>
        </div>`;
        return;
    }

    const statusLabel = { upcoming: 'Akan Datang', ongoing: 'Sedang Berlangsung', completed: 'Telah Selesai' };

    container.innerHTML = myBookings.map(b => `
        <div class="booking-item-card">
            ${b.poster_image
                ? `<img class="booking-item-poster" src="${escHtml(b.poster_image)}" alt="${escHtml(b.event_title || '')}" onerror="this.style.display='none'" />`
                : `<div class="booking-item-poster-placeholder">ðŸŽ¨</div>`
            }
            <div class="booking-item-info">
                <div class="booking-item-event-title">${escHtml(b.event_title || 'Event Seni')}</div>
                <div class="booking-item-meta">
                    <span>📅 ${b.start_date ? formatDateDisplay(b.start_date) : '—'}</span>
                    <span>📍 ${escHtml(b.location_text || '—')}</span>
                    <span>🎟️ ${escHtml(b.total_price \vert{}\vert{} 'Gratis')}</span>${b.qty > 1 ? `<span>${b.qty} tiket</span>` : ''}
                    <span>Status event: ${statusLabel[b.event_status] || b.event_status || '—'}</span>
                </div>
                <div>
                    <span class="booking-item-code">Kode: ${escHtml(b.booking_code || '—')}</span>
                    <span style="font-family:var(--font-typewriter,'Special Elite',monospace); font-size:0.72rem; color:${b.status === 'confirmed' ? '#0f5132' : (b.status === 'cancelled' ? '#842029' : '#856404')}; margin-left:8px;">
                        ${b.status === 'confirmed' ? '✅ Terkonfirmasi' : (b.status === 'cancelled' ? '✖ Dibatalkan' : '⏳ ' + escHtml(b.status || 'Menunggu'))}
                    </span>
                </div>
            </div>
            <div class="booking-item-actions">
                ${b.status !== 'cancelled' && b.event_status !== 'completed' ? `
                <button class="ev-btn-danger" style="font-size:0.72rem;" onclick="cancelBooking(${b.id})">âœ• Batal</button>
                ` : ''}
                <button class="ev-btn-secondary" style="font-size:0.72rem;" onclick="openEventDetail(${b.event_id})">Lihat</button>
            </div>
        </div>
    `).join('');
}

// ====================================================
// ADMIN: EVENT FORM
// ====================================================
function openEventModal(eventData = null) {
    if (!isAdmin) return;
    const modal = document.getElementById('event-form-modal');
    const form = document.getElementById('event-admin-form');
    const title = document.getElementById('event-form-modal-title');
    const stamp = document.getElementById('event-form-stamp');
    const submitBtn = document.getElementById('ev-form-submit-btn');

    form.reset();
    document.getElementById('ev-poster-preview').style.display = 'none';
    document.getElementById('event-edit-id').value = '';
    editingEventId = null;

    if (eventData) {
        // Mode Edit
        editingEventId = eventData.id;
        title.textContent = 'Edit Event Seni';
        stamp.textContent = 'EDIT DATA EVENT';
        submitBtn.textContent = 'âœ“ Simpan Perubahan';
        document.getElementById('event-edit-id').value = eventData.id;

        document.getElementById('ev-title').value = eventData.title || '';
        document.getElementById('ev-theme').value = eventData.theme || '';
        document.getElementById('ev-status').value = eventData.status || 'upcoming';
        document.getElementById('ev-start-date').value = eventData.start_date || '';
        document.getElementById('ev-end-date').value = eventData.end_date || '';
        document.getElementById('ev-start-time').value = eventData.start_time || '';
        document.getElementById('ev-end-time').value = eventData.end_time || '';
        document.getElementById('ev-ticket-price').value = eventData.ticket_price || 'Gratis';
        document.getElementById('ev-quota').value = eventData.ticket_quota || '';
        document.getElementById('ev-location-text').value = eventData.location_text || '';
        document.getElementById('ev-location-map').value = eventData.location_map || '';
        document.getElementById('ev-artists').value = eventData.artists || '';
        document.getElementById('ev-description').value = eventData.description || '';
        document.getElementById('ev-organizer').value = eventData.organizer || 'Jejak Waktu';
        document.getElementById('ev-album-id').value = eventData.album_id || '';
        document.getElementById('ev-poster-url').value = eventData.poster_image || '';

        if (eventData.poster_image) {
            const preview = document.getElementById('ev-poster-preview');
            const img = document.getElementById('ev-poster-preview-img');
            img.src = eventData.poster_image;
            preview.style.display = 'block';
        }
    } else {
        title.textContent = 'Tambah Event Seni Baru';
        stamp.textContent = 'FORMULIR EVENT BARU';
        submitBtn.textContent = 'âœ“ Simpan Event';
    }

    modal.classList.add('open');
    modal.setAttribute('aria-hidden', 'false');
    document.body.style.overflow = 'hidden';
}

function closeEventModal() {
    const modal = document.getElementById('event-form-modal');
    modal.classList.remove('open');
    modal.setAttribute('aria-hidden', 'true');
    document.body.style.overflow = '';
    editingEventId = null;
}

function initEventFormSubmit() {
    const form = document.getElementById('event-admin-form');
    if (!form) return;
    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        if (!isAdmin) return;

        const submitBtn = document.getElementById('ev-form-submit-btn');
        submitBtn.disabled = true;
        submitBtn.textContent = 'Menyimpan...';

        const fd = new FormData(form);
        // Hapus event-edit-id dari fd (hanya untuk internal JS state)
        const editId = fd.get('event_edit_id');
        fd.delete('event_edit_id');

        try {
            let url = 'api/events.json';
            if (editId) url += `?_method=PUT&id=${editId}`;

            const resp = await fetch(url, { method: 'POST', body: fd });
            const json = await resp.json();

            if (json.status === 'success') {
                showToast('âœ… ' + json.message, 'success');
                closeEventModal();
                // Reaktif: Jika event baru memiliki tanggal mulai, sinkronkan kalender ke tanggal tersebut secara instan
                const newStartDate = fd.get('start_date');
                if (newStartDate) {
                    const parts = newStartDate.split('-');
                    if (parts.length >= 2) {
                        calViewDate = new Date(parseInt(parts[0]), parseInt(parts[1]) - 1, 1);
                        selectedCalendarDate = newStartDate;
                    }
                }
                await loadAllEvents();
            } else {
                showToast('âš  ' + json.message, 'error');
            }
        } catch (err) {
            showToast('Kesalahan jaringan: ' + err.message, 'error');
        } finally {
            submitBtn.disabled = false;
            submitBtn.textContent = editId ? 'âœ“ Simpan Perubahan' : 'âœ“ Simpan Event';
        }
    });
}

async function editEvent(eventId, e) {
    if (e) { e.stopPropagation(); }
    const ev = eventsData.find(x => x.id == eventId);
    if (!ev) {
        try {
            if (typeof isSupabaseConfigured === 'function' && isSupabaseConfigured()) {
                const result = await sbFetchEventById(eventId);
                if (result.status === 'success') openEventModal(result.data);
            }
        } catch (err) { showToast('Gagal memuat data event', 'error'); }
        return;
    }
    openEventModal(ev);
}

async function deleteEvent(eventId, e) {
    if (e) { e.stopPropagation(); }
    const ev = eventsData.find(x => x.id == eventId);
    if (!confirm(`Hapus event "${ev ? ev.title : 'ini'}"? Semua booking terkait juga akan dihapus. Tindakan ini tidak bisa dibatalkan.`)) return;

    try {
        const resp = await fetch(`api/events.json?_method=DELETE&id=${eventId}`, { method: 'POST' });
        const json = await resp.json();
        if (json.status === 'success') {
            showToast('âœ… Event berhasil dihapus', 'success');
            await loadAllEvents();
            await loadMyBookings();
        } else {
            showToast('âš  ' + json.message, 'error');
        }
    } catch (err) {
        showToast('Gagal: ' + err.message, 'error');
    }
}

// ====================================================
// LOAD ALBUMS FOR FORM DROPDOWN
// ====================================================
async function loadAlbumsForForm() {
    try {
        if (typeof isSupabaseConfigured === 'function' && isSupabaseConfigured()) {
            const result = await sbFetchAlbums();
            if (result.status !== 'success') return;
            albumsList = result.data || [];
        } else {
            // Fallback: gunakan data yang sudah ada di cache/DEFAULT_ALBUMS
            if (typeof getAlbumsData === 'function') {
                albumsList = getAlbumsData();
            }
        }
        const select = document.getElementById('ev-album-id');
        if (!select) return;
        albumsList.forEach(alb => {
            const opt = document.createElement('option');
            opt.value = alb.id;
            opt.textContent = alb.title;
            select.appendChild(opt);
        });
    } catch (_) {}
}

// ====================================================
// POSTER PREVIEW IN FORM
// ====================================================
function initFormPosterPreview() {
    const urlInput = document.getElementById('ev-poster-url');
    const fileInput = document.getElementById('ev-poster-file');
    const preview = document.getElementById('ev-poster-preview');
    const img = document.getElementById('ev-poster-preview-img');
    if (!urlInput || !fileInput) return;

    urlInput.addEventListener('input', () => {
        const val = urlInput.value.trim();
        if (val) {
            img.src = val;
            preview.style.display = 'block';
        } else {
            preview.style.display = 'none';
        }
    });

    fileInput.addEventListener('change', () => {
        const file = fileInput.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = (ev) => {
                img.src = ev.target.result;
                preview.style.display = 'block';
            };
            reader.readAsDataURL(file);
        }
    });
}

// ====================================================
// TOAST NOTIFICATION
// ====================================================
function showToast(message, type = 'info') {
    const existing = document.querySelector('.ev-toast');
    if (existing) existing.remove();

    const toast = document.createElement('div');
    toast.className = `ev-toast ev-toast-${type}`;
    toast.textContent = message;
    document.body.appendChild(toast);

    requestAnimationFrame(() => {
        requestAnimationFrame(() => { toast.classList.add('show'); });
    });

    setTimeout(() => {
        toast.classList.remove('show');
        setTimeout(() => toast.remove(), 400);
    }, 3800);
}

// ====================================================
// LOADING STATE
// ====================================================
function showLoading(show) {
    document.getElementById('events-loading').style.display = show ? 'block' : 'none';
}

// ====================================================
// HELPER FUNCTIONS
// ====================================================
function escHtml(str) {
    if (!str) return '';
    return String(str)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
}

function formatDateRange(start, end) {
    if (!start) return 'â€”';
    const s = formatDateDisplay(start);
    if (!end || end === start) return s;
    const e = formatDateDisplay(end);
    return `${s} â€“ ${e}`;
}

function formatDateDisplay(dateStr) {
    if (!dateStr) return 'â€”';
    const months = ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'];
    const parts = dateStr.split('-');
    if (parts.length < 3) return dateStr;
    const d = parseInt(parts[2]);
    const m = parseInt(parts[1]) - 1;
    const y = parts[0];
    return `${d} ${months[m]} ${y}`;
}

function formatTime(timeStr) {
    if (!timeStr) return '';
    const parts = timeStr.split(':');
    return `${parts[0]}.${parts[1]} WIB`;
}
