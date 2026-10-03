/**
 * Data Koleksi Album & Foto Vintage "Jejak Waktu"
 * Sinkronisasi dengan Supabase Database & Fallback LocalStorage
 */

const DEFAULT_ALBUMS = [
    {
        id: 'jakarta-tempo-doeloe',
        title: 'Jakarta Tempo Doeloe',
        subtitle: 'Hiruk-pikuk Ibukota di Era 70 & 80-an',
        era: '1970-1985',
        decade: '1970s',
        category: 'sejarah-kota',
        coverImage: 'https://images.unsplash.com/photo-1578632767115-351597cf2477?auto=format&fit=crop&w=800&q=80',
        coverColor: '#422a1d',
        accentColor: '#b38241',
        description: 'Menyusuri jalanan MH Thamrin saat masih lengang, trem listrik masa kolonial yang telah berganti mikrolet oranye, serta gedung-gedung bertingkat pertama yang mulai menghiasi cakrawala Batavia berganti Jakarta.',
        location: 'DKI Jakarta, Indonesia',
        curator: 'Arsiparis Bambang S.',
        photos: [
            {
                id: 'jkt-1',
                title: 'Bundaran HI Masa Awal Pembangunan',
                artist_1: 'Arsiparis Bambang S.',
                artist_2: null,
                year: '1972',
                date: '14 Agustus 1972',
                location: 'Bundaran Hotel Indonesia, Jakarta Pusat',
                medium: 'Fotografi Analog 35mm (Black & White)',
                dimensions: '30 x 40 cm',
                copyright: 'Arsip Nasional Republik Indonesia',
                caption: 'Pemandangan Hotel Indonesia yang megah dengan lalu lintas mobil klasik Fiat dan VW Kodok yang masih sangat tertib.',
                src: 'https://images.unsplash.com/photo-1578632767115-351597cf2477?auto=format&fit=crop&w=1200&q=85',
                thumb: 'https://images.unsplash.com/photo-1578632767115-351597cf2477?auto=format&fit=crop&w=600&q=80',
                tilt: '-2deg',
                tape: 'top-right',
                note: 'Diambil oleh alm. Kakek saat bertugas di Bappenas'
            },
            {
                id: 'jkt-2',
                title: 'Jalan Sabang di Senja Hari',
                artist_1: 'Bambang Soetjipto',
                artist_2: 'Hendra W.',
                year: '1978',
                date: '28 Oktober 1978',
                location: 'Jl. H. Agus Salim (Sabang), Jakarta',
                medium: 'Fotografi Berwarna Analog (Kodachrome)',
                dimensions: '20 x 30 cm',
                copyright: 'Hak Cipta Koleksi Keluarga Sutrisno',
                caption: 'Deretan toko kelontong, kedai es kopi, dan lampu neon khas akhir tahun 70-an saat warga pulang kantor.',
                src: 'https://images.unsplash.com/photo-1509198397868-475647b2a1e5?auto=format&fit=crop&w=1200&q=85',
                thumb: 'https://images.unsplash.com/photo-1509198397868-475647b2a1e5?auto=format&fit=crop&w=600&q=80',
                tilt: '3deg',
                tape: 'top-left',
                note: 'Tempat favorit beli kaset pita Barat & Pop Indo'
            },
            {
                id: 'jkt-3',
                title: 'Pasar Senen & Stasiun Kereta Api',
                artist_1: 'Kusworo Hartono',
                artist_2: null,
                year: '1981',
                date: '03 Mei 1981',
                location: 'Pasar Senen, Jakarta Pusat',
                medium: 'Cetak Gelatin Perak (Silver Gelatin Print)',
                dimensions: '40 x 50 cm',
                copyright: 'Arsip Dinas Kebudayaan DKI Jakarta',
                caption: 'Keramaian calon penumpang kereta jurusan Jawa Tengah memadati pelataran stasiun dengan koper seng dan kardus bertali rafia.',
                src: 'https://images.unsplash.com/photo-1526778548025-fa2f459cd5c1?auto=format&fit=crop&w=1200&q=85',
                thumb: 'https://images.unsplash.com/photo-1526778548025-fa2f459cd5c1?auto=format&fit=crop&w=600&q=80',
                tilt: '-1.5deg',
                tape: 'both',
                note: 'Tiket kereta masih ditulis tangan oleh juru karcis'
            },
            {
                id: 'jkt-4',
                title: 'Antrean Bioskop Megaria',
                artist_1: 'Suryono Danu',
                artist_2: 'Wartawan Sinema',
                year: '1984',
                date: '19 Desember 1984',
                location: 'Bioskop Metropole / Megaria, Cikini',
                medium: 'Fotografi Dokumenter 35mm',
                dimensions: '24 x 36 cm',
                copyright: 'Koleksi Sinematek Indonesia',
                caption: 'Muda-mudi berdandan rapi dengan celana cutbrai dan rambut berombak mengantre tiket film layar lebar akhir pekan.',
                src: 'https://images.unsplash.com/photo-1489599849927-2ee91cede3ba?auto=format&fit=crop&w=1200&q=85',
                thumb: 'https://images.unsplash.com/photo-1489599849927-2ee91cede3ba?auto=format&fit=crop&w=600&q=80',
                tilt: '2.5deg',
                tape: 'top-right',
                note: 'Film Warkop DKI "Maju Kena Mundur Kena"'
            }
        ]
    },
    {
        id: 'kenangan-masa-sekolah',
        title: 'Kenangan Masa Sekolah',
        subtitle: 'Putih Abu-abu & Sahabat Karib Angkatan 90',
        era: '1988-1996',
        decade: '1990s',
        category: 'sekolah-remaja',
        coverImage: 'https://images.unsplash.com/photo-1529156069898-49953e39b3ac?auto=format&fit=crop&w=800&q=80',
        coverColor: '#2b3a4a',
        accentColor: '#d4a373',
        description: 'Lembaran memori saat seragam putih abu-abu dicoret tanda tangan kelulusan, saling pinjam kaset mixtape lagu Cassanova, serta berkumpul di kantin sekolah sepulang pelajaran olahraga.',
        location: 'SMA Negeri 1 Nusantara',
        curator: 'Rina Kartika (Alumni 95)',
        photos: [
            {
                id: 'sch-1',
                title: 'Foto Kelas 3 Fisika 2 di Bawah Pohon Beringin',
                artist_1: 'Dimas Prasetyo',
                artist_2: 'Rina Kartika',
                year: '1994',
                date: '12 April 1994',
                location: 'Halaman Belakang SMAN 1',
                medium: 'Fotografi Kamera Analog Yashica FX-3',
                dimensions: '15 x 21 cm',
                copyright: 'Alumni 95 SMAN 1',
                caption: 'Pose andalan kami sebelum menempuh EBTANAS. Senyum optimis menyambut masa depan yang belum tentu arahnya.',
                src: 'https://images.unsplash.com/photo-1529156069898-49953e39b3ac?auto=format&fit=crop&w=1200&q=85',
                thumb: 'https://images.unsplash.com/photo-1529156069898-49953e39b3ac?auto=format&fit=crop&w=600&q=80',
                tilt: '2deg',
                tape: 'top-left',
                note: 'Foto pakai kamera analog Yashica milik Dimas'
            },
            {
                id: 'sch-2',
                title: 'Latihan Band Festival Musik Pelajar',
                artist_1: 'Agus Kridanto',
                artist_2: null,
                year: '1993',
                date: '08 September 1993',
                location: 'Studio Rental Nada Indah',
                medium: 'Dokumentasi Analog Fujicolor Super HR',
                dimensions: '18 x 24 cm',
                copyright: 'Studio Nada Indah',
                caption: 'Membawakan lagu Slank dan Nirvana dengan amplifier bising. Keringat dan tawa memenuhi ruangan studio sempit berkarpet kusam.',
                src: 'https://images.unsplash.com/photo-1511671782779-c97d3d27a1d4?auto=format&fit=crop&w=1200&q=85',
                thumb: 'https://images.unsplash.com/photo-1511671782779-c97d3d27a1d4?auto=format&fit=crop&w=600&q=80',
                tilt: '-2.5deg',
                tape: 'top-right',
                note: 'Juara 2 se-Karesidenan!'
            }
        ]
    },
    {
        id: 'potret-pedesaan-jawa',
        title: 'Harmoni Pedesaan Jawa',
        subtitle: 'Kedamaian Sawah, Padi, dan Gotong Royong 1960-70an',
        era: '1962-1974',
        decade: '1960s',
        category: 'budaya-tradisi',
        coverImage: 'https://images.unsplash.com/photo-1500382017468-9049fed747ef?auto=format&fit=crop&w=800&q=80',
        coverColor: '#364935',
        accentColor: '#cfb97c',
        description: 'Potret otentik kehidupan kaum tani di lereng Gunung Merapi dan persawahan Klaten. Kehidupan bersahaja yang diikat oleh falsafah rukun agawe santosa dan hembusan angin sawah yang sejuk.',
        location: 'Klaten & Sleman, D.I. Yogyakarta',
        curator: 'Koleksi Budaya Ki Suryo',
        photos: [
            {
                id: 'desa-1',
                title: 'Musim Panen Raya & Tumbuk Padi di Lesung',
                artist_1: 'Ki Suryo Sasongko',
                artist_2: 'Mangun Raharjo',
                year: '1968',
                date: '10 Juli 1968',
                location: 'Desa Somokaton, Ngluwar',
                medium: 'Lukisan Cat Minyak di Atas Kanvas (Oil on Canvas)',
                dimensions: '90 x 140 cm',
                copyright: 'Museum Seni & Budaya Nusantara',
                caption: 'Ibu-ibu desa bergotong-royong menumbuk bulir padi menggunakan alu kayu dengan irama alu yang berpadu seperti alunan musik tradisional.',
                src: 'https://images.unsplash.com/photo-1500382017468-9049fed747ef?auto=format&fit=crop&w=1200&q=85',
                thumb: 'https://images.unsplash.com/photo-1500382017468-9049fed747ef?auto=format&fit=crop&w=600&q=80',
                tilt: '-1deg',
                tape: 'top-right',
                note: 'Suara lesung terdengar sampai desa sebelah sejak subuh'
            }
        ]
    },
    {
        id: 'pesta-rakyat-pasar-malam',
        title: 'Pesta Rakyat & Pasar Malam',
        subtitle: 'Layar Tancap, Odong-odong, dan Gulali 80-an',
        era: '1982-1989',
        decade: '1980s',
        category: 'keluarga-kehidupan',
        coverImage: 'https://images.unsplash.com/photo-1513151233558-d860c5398176?auto=format&fit=crop&w=800&q=80',
        coverColor: '#5c2d3e',
        accentColor: '#e9a868',
        description: 'Kemeriahan lapangan terbuka saat pasar malam tahunan tiba. Bau arum manis, desing komidi putar kayu manual, dan kerlip lampu minyak petromaks yang mengundang tawa seluruh warga sekampung.',
        location: 'Lapangan Alun-Alun Utara',
        curator: 'Paguyuban Warga Nostalgia',
        photos: [
            {
                id: 'pesta-1',
                title: 'Nonton Layar Tancap Beralas Tikar Pandan',
                artist_1: 'Teguh Sujarwo',
                artist_2: null,
                year: '1985',
                date: '17 Agustus 1985',
                location: 'Lapangan Sepakbola Kampung Melayu',
                medium: 'Seni Grafis Cukil Kayu & Cat Akrilik (Woodcut & Acrylic)',
                dimensions: '60 x 80 cm',
                copyright: 'Paguyuban Warga Nostalgia',
                caption: 'Warga berkumpul menikmati tontonan film laga kolosal gratis di bawah taburan bintang malam peringatan HUT RI ke-40.',
                src: 'https://images.unsplash.com/photo-1513151233558-d860c5398176?auto=format&fit=crop&w=1200&q=85',
                thumb: 'https://images.unsplash.com/photo-1513151233558-d860c5398176?auto=format&fit=crop&w=600&q=80',
                tilt: '-2.2deg',
                tape: 'top-left',
                note: 'Tukang kacang rebus keliling selalu laris manis'
            }
        ]
    },
    {
        id: 'transportasi-kereta-uap',
        title: 'Loko Uap & Jalur Rel Besi',
        subtitle: 'Deru Lokomotif Hitam & Perjalanan Lintas Stasiun 1950-an',
        era: '1954-1965',
        decade: '1950s',
        category: 'sejarah-kota',
        coverImage: 'https://images.unsplash.com/photo-1474487548417-781cb71495f3?auto=format&fit=crop&w=800&q=80',
        coverColor: '#252525',
        accentColor: '#c59b27',
        description: 'Keagungan lokomotif uap raksasa D52 buatan Jerman dan Krupp yang membelah pegunungan Jawa Barat dan Jawa Tengah. Kepulan asap putih tebal dan derit roda baja di atas bantalan kayu jati.',
        location: 'Depo Ambarawa & Jalur Priangan',
        curator: 'Komunitas Pecinta Sejarah Kereta Api',
        photos: [
            {
                id: 'train-1',
                title: 'Lokomotif Uap Seri D52 di Stasiun Ambarawa',
                artist_1: 'Ir. Soedarsono',
                artist_2: 'Kolektif Balai Yasa',
                year: '1956',
                date: '07 Juli 1956',
                location: 'Stasiun Willem I (Ambarawa)',
                medium: 'Fotografi Medium Format 120mm Monokrom',
                dimensions: '50 x 70 cm',
                copyright: 'Pusat Pelestarian Benda Bersejarah PT KAI',
                caption: 'Petugas masinis dan juru api bersiap memasukkan batu bara ke dalam tungku pembakaran menjelang keberangkatan ke Magelang.',
                src: 'https://images.unsplash.com/photo-1474487548417-781cb71495f3?auto=format&fit=crop&w=1200&q=85',
                thumb: 'https://images.unsplash.com/photo-1474487548417-781cb71495f3?auto=format&fit=crop&w=600&q=80',
                tilt: '1.2deg',
                tape: 'top-left',
                note: 'Peluit uapnya berbunyi hingga radius 5 kilometer'
            }
        ]
    },
    {
        id: 'nostalgia-milenium-2000',
        title: 'Nostalgia Milenium 2000-an',
        subtitle: 'Era Wartel, Kamera Digital Pertama & Musik Pop Populer',
        era: '2000-2008',
        decade: '2000s',
        category: 'keluarga-kehidupan',
        coverImage: 'https://images.unsplash.com/photo-1511671782779-c97d3d27a1d4?auto=format&fit=crop&w=800&q=80',
        coverColor: '#2b3a4a',
        accentColor: '#e9a868',
        description: 'Potret peralihan milenium baru saat posko wartel jamur di tiap sudut jalan, kaset CD kompilasi lagu favorit, dan kamera saku digital piksel awal menjadi tren baru.',
        location: 'Bandung & Jakarta, Indonesia',
        curator: 'Generasi Y2K Nusantara',
        photos: [
            {
                id: 'y2k-1',
                title: 'Kamera Saku & DiskCD Musik Y2K',
                artist_1: 'Andra Permana',
                artist_2: null,
                year: '2004',
                date: '12 November 2004',
                location: 'Bandung, Jawa Barat',
                medium: 'Fotografi Digital Saku 3 Megapiksel',
                dimensions: '20 x 25 cm',
                copyright: 'Koleksi Komunitas Y2K',
                caption: 'Peralihan dari rol film ke kamera saku digital di mana setiap momen bisa langsung ditinjau di layar LCD kecil.',
                src: 'https://images.unsplash.com/photo-1511671782779-c97d3d27a1d4?auto=format&fit=crop&w=1200&q=85',
                thumb: 'https://images.unsplash.com/photo-1511671782779-c97d3d27a1d4?auto=format&fit=crop&w=600&q=80',
                tilt: '-1.8deg',
                tape: 'top-left',
                note: 'Memori card kapasitas 128MB sudah terasa sangat besar saat itu'
            }
        ]
    },
    {
        id: 'jejak-seni-modern-2010',
        title: 'Seni & Budaya Digital 2010-an',
        subtitle: 'Pameran Fotografi Modern & Eksplorasi Media Baru',
        era: '2010-2018',
        decade: '2010s',
        category: 'budaya-tradisi',
        coverImage: 'https://images.unsplash.com/photo-1460661419201-fd4cecdf8a8b?auto=format&fit=crop&w=800&q=80',
        coverColor: '#364935',
        accentColor: '#d4af37',
        description: 'Arsip dokumentasi seni rupa dan galeri pameran seni kontemporer Indonesia pada dekade 2010-an dengan paduan instalasi media baru.',
        location: 'Yogyakarta & Jakarta',
        curator: 'Ruang Seni Nusantara',
        photos: [
            {
                id: 'mod-1',
                title: 'Instalasi Seni Kontemporer 2015',
                artist_1: 'Maya Indah',
                artist_2: 'Kolektif Ruang Rupa',
                year: '2015',
                date: '20 Agustus 2015',
                location: 'Jogja National Museum (JNM)',
                medium: 'Digital Fine Art Print',
                dimensions: '60 x 90 cm',
                copyright: 'Hak Cipta Seniman & Galeri',
                caption: 'Ruang pameran kontemporer yang menggabungkan pencahayaan instalasi lampu vintage dan karya kanvas modern.',
                src: 'https://images.unsplash.com/photo-1460661419201-fd4cecdf8a8b?auto=format&fit=crop&w=1200&q=85',
                thumb: 'https://images.unsplash.com/photo-1460661419201-fd4cecdf8a8b?auto=format&fit=crop&w=600&q=80',
                tilt: '2deg',
                tape: 'top-right',
                note: 'Dokumentasi gelaran Biennale Jogja'
            }
        ]
    }
];

// Memory state cache
let loadedAlbumsCache = null;
let isConnectedToSupabase = false;

/**
 * Async function: load albums dari Supabase dengan fallback ke LocalStorage/DEFAULT_ALBUMS.
 * Menggantikan fetch('api/albums.json') MySQL sebelumnya.
 */
async function fetchAlbumsFromDB() {
    // Coba ambil data dari Supabase jika sudah dikonfigurasi
    if (typeof isSupabaseConfigured === 'function' && isSupabaseConfigured()) {
        try {
            const searchQuery = (typeof window !== 'undefined' && window.SEARCH_QUERY)
                ? window.SEARCH_QUERY
                : '';

            const result = await sbFetchAlbums(searchQuery);

            if (result.status === 'success' && Array.isArray(result.data)) {
                // Jika mode pencarian, terima array kosong (0 hasil valid)
                if (searchQuery || result.data.length > 0) {
                    loadedAlbumsCache = result.data;
                    isConnectedToSupabase = true;
                    return result.data;
                }
            }
        } catch (e) {
            console.warn('[Supabase] fetchAlbumsFromDB gagal, beralih ke mode offline:', e);
        }
    } else {
        console.info('[Supabase] Belum dikonfigurasi — gunakan data lokal/DEFAULT_ALBUMS.');
    }

    isConnectedToSupabase = false;
    return getAlbumsData();
}

// Synchronous helper: menggabungkan DEFAULT_ALBUMS, custom_albums, dan extra photos offline
function getAlbumsData() {
    if (loadedAlbumsCache !== null) {
        return loadedAlbumsCache;
    }

    if (typeof window !== 'undefined' && Array.isArray(window.SERVER_ALBUMS) && (window.SEARCH_QUERY || window.SERVER_ALBUMS.length > 0)) {
        loadedAlbumsCache = window.SERVER_ALBUMS;
        return loadedAlbumsCache;
    }

    let baseAlbums = JSON.parse(JSON.stringify(DEFAULT_ALBUMS));

    // Gabungkan album kustom dari localStorage jika ada
    const customAlbums = localStorage.getItem('jejak_waktu_custom_albums');
    if (customAlbums) {
        try {
            const parsed = JSON.parse(customAlbums);
            if (Array.isArray(parsed)) {
                baseAlbums = [...baseAlbums, ...parsed];
            }
        } catch (e) {
            console.warn('Gagal membaca custom albums dari localStorage', e);
        }
    }

    // Gabungkan foto offline ekstra ke masing-masing album
    return baseAlbums.map(album => {
        const extraPhotosKey = `jejak_waktu_extra_photos_${album.id}`;
        const extraPhotos = localStorage.getItem(extraPhotosKey);
        if (extraPhotos) {
            try {
                const parsedExtras = JSON.parse(extraPhotos);
                if (Array.isArray(parsedExtras)) {
                    album.photos = [...parsedExtras, ...(album.photos || [])];
                }
            } catch (e) { }
        }
        return album;
    });
}

// Get single album by ID (with deduplication)
function getAlbumById(id) {
    if (!id) return null;
    const allAlbums = getAlbumsData();
    const album = allAlbums.find(a => {
        const currentId = a.id || a.album_id;
        return String(currentId).trim() === String(id).trim();
    });
    if (!album) return null;
    return JSON.parse(JSON.stringify(album));
}

// Save local fallback untuk foto baru
function addPhotoToAlbumLocal(albumId, photoData) {
    const extraPhotosKey = `jejak_waktu_extra_photos_${albumId}`;
    const existingExtras = localStorage.getItem(extraPhotosKey);
    let extrasList = existingExtras ? JSON.parse(existingExtras) : [];
    extrasList.unshift(photoData);
    try {
        localStorage.setItem(extraPhotosKey, JSON.stringify(extrasList));
    } catch (e) {
        console.warn('LocalStorage full, skipped local cache save');
    }
    return true;
}

// Save local fallback untuk album baru
function addAlbumLocal(albumData) {
    const customAlbumsKey = 'jejak_waktu_custom_albums';
    const existingAlbums = localStorage.getItem(customAlbumsKey);
    let albumsList = existingAlbums ? JSON.parse(existingAlbums) : [];
    albumsList.unshift(albumData);
    try {
        localStorage.setItem(customAlbumsKey, JSON.stringify(albumsList));
    } catch (e) {
        console.warn('LocalStorage full, skipped custom album save');
    }
    return true;
}


