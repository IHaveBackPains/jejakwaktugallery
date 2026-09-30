-- ========================================================
-- DATABASE SCHEMA: JEJAK WAKTU
-- Import file ini di phpMyAdmin (http://localhost/phpmyadmin)
-- ========================================================

CREATE DATABASE IF NOT EXISTS `jejak_waktu_db` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `jejak_waktu_db`;

-- --------------------------------------------------------
-- 1. Tabel: albums (Buku Album Foto)
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `albums` (
    `id` VARCHAR(100) NOT NULL PRIMARY KEY,
    `title` VARCHAR(255) NOT NULL,
    `subtitle` VARCHAR(255) DEFAULT NULL,
    `era` VARCHAR(50) DEFAULT NULL,
    `decade` VARCHAR(20) DEFAULT '1970s',
    `category` VARCHAR(50) DEFAULT 'sejarah-kota',
    `cover_image` TEXT NOT NULL,
    `cover_color` VARCHAR(30) DEFAULT '#422a1d',
    `accent_color` VARCHAR(30) DEFAULT '#b38241',
    `description` TEXT,
    `location` VARCHAR(150) DEFAULT 'Indonesia',
    `curator` VARCHAR(150) DEFAULT 'Arsiparis Jejak Waktu',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- 2. Tabel: photos (Foto Polaroid & Kenangan)
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `photos` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `album_id` VARCHAR(100) NOT NULL,
    `title` VARCHAR(255) NOT NULL,
    `artist_1` VARCHAR(255) DEFAULT NULL,
    `artist_2` VARCHAR(255) DEFAULT NULL,
    `year` VARCHAR(10) DEFAULT NULL,
    `date` VARCHAR(100) DEFAULT NULL,
    `location` VARCHAR(150) DEFAULT NULL,
    `medium` VARCHAR(255) DEFAULT NULL,
    `dimensions` VARCHAR(150) DEFAULT NULL,
    `copyright` VARCHAR(255) DEFAULT NULL,
    `caption` TEXT,
    `image_src` TEXT NOT NULL,
    `thumb_src` TEXT,
    `tilt` VARCHAR(20) DEFAULT '-2deg',
    `tape` VARCHAR(20) DEFAULT 'top-right',
    `note` TEXT,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`album_id`) REFERENCES `albums`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- 3. Data Awal Album (Seeding)
-- --------------------------------------------------------
INSERT INTO `albums` (`id`, `title`, `subtitle`, `era`, `decade`, `category`, `cover_image`, `cover_color`, `accent_color`, `description`, `location`, `curator`) VALUES
('jakarta-tempo-doeloe', 'Jakarta Tempo Doeloe', 'Hiruk-pikuk Ibukota di Era 70 & 80-an', '1970-1985', '1970s', 'sejarah-kota', 'https://images.unsplash.com/photo-1578632767115-351597cf2477?auto=format&fit=crop&w=800&q=80', '#422a1d', '#b38241', 'Menyusuri jalanan MH Thamrin saat masih lengang, trem listrik masa kolonial yang telah berganti mikrolet oranye, serta gedung-gedung bertingkat pertama yang mulai menghiasi cakrawala Batavia berganti Jakarta.', 'DKI Jakarta, Indonesia', 'Arsiparis Bambang S.'),
('kenangan-masa-sekolah', 'Kenangan Masa Sekolah', 'Putih Abu-abu & Sahabat Karib Angkatan 90', '1988-1996', '1990s', 'sekolah-remaja', 'https://images.unsplash.com/photo-1529156069898-49953e39b3ac?auto=format&fit=crop&w=800&q=80', '#2b3a4a', '#d4a373', 'Lembaran memori saat seragam putih abu-abu dicoret tanda tangan kelulusan, saling pinjam kaset mixtape lagu Cassanova, serta berkumpul di kantin sekolah sepulang pelajaran olahraga.', 'SMA Negeri 1 Nusantara', 'Rina Kartika (Alumni 95)'),
('potret-pedesaan-jawa', 'Harmoni Pedesaan Jawa', 'Kedamaian Sawah, Padi, dan Gotong Royong 1960-70an', '1962-1974', '1960s', 'budaya-tradisi', 'https://images.unsplash.com/photo-1500382017468-9049fed747ef?auto=format&fit=crop&w=800&q=80', '#364935', '#cfb97c', 'Potret otentik kehidupan kaum tani di lereng Gunung Merapi dan persawahan Klaten. Kehidupan bersahaja yang diikat oleh falsafah rukun agawe santosa dan hembusan angin sawah yang sejuk.', 'Klaten & Sleman, D.I. Yogyakarta', 'Koleksi Budaya Ki Suryo'),
('pesta-rakyat-pasar-malam', 'Pesta Rakyat & Pasar Malam', 'Layar Tancap, Odong-odong, dan Gulali 80-an', '1982-1989', '1980s', 'keluarga-kehidupan', 'https://images.unsplash.com/photo-1513151233558-d860c5398176?auto=format&fit=crop&w=800&q=80', '#5c2d3e', '#e9a868', 'Kemeriahan lapangan terbuka saat pasar malam tahunan tiba. Bau arum manis, desing komidi putar kayu manual, dan kerlip lampu minyak petromaks yang mengundang tawa seluruh warga sekampung.', 'Lapangan Alun-Alun Utara', 'Paguyuban Warga Nostalgia'),
('transportasi-kereta-uap', 'Loko Uap & Jalur Rel Besi', 'Deru Lokomotif Hitam & Perjalanan Lintas Stasiun 1950-an', '1954-1965', '1950s', 'sejarah-kota', 'https://images.unsplash.com/photo-1474487548417-781cb71495f3?auto=format&fit=crop&w=800&q=80', '#252525', '#c59b27', 'Keagungan lokomotif uap raksasa D52 buatan Jerman dan Krupp yang membelah pegunungan Jawa Barat dan Jawa Tengah. Kepulan asap putih tebal dan derit roda baja di atas bantalan kayu jati.', 'Depo Ambarawa & Jalur Priangan', 'Komunitas Pecinta Sejarah Kereta Api'),
('nostalgia-milenium-2000', 'Nostalgia Milenium 2000-an', 'Era Wartel, Kamera Digital Pertama & Musik Pop Populer', '2000-2008', '2000s', 'keluarga-kehidupan', 'https://images.unsplash.com/photo-1511671782779-c97d3d27a1d4?auto=format&fit=crop&w=800&q=80', '#2b3a4a', '#e9a868', 'Potret peralihan milenium baru saat posko wartel jamur di tiap sudut jalan, kaset CD kompilasi lagu favorit, dan kamera saku digital piksel awal menjadi tren baru.', 'Bandung & Jakarta, Indonesia', 'Generasi Y2K Nusantara'),
('jejak-seni-modern-2010', 'Seni & Budaya Digital 2010-an', 'Pameran Fotografi Modern & Eksplorasi Media Baru', '2010-2018', '2010s', 'budaya-tradisi', 'https://images.unsplash.com/photo-1460661419201-fd4cecdf8a8b?auto=format&fit=crop&w=800&q=80', '#364935', '#d4af37', 'Arsip dokumentasi seni rupa dan galeri pameran seni kontemporer Indonesia pada dekade 2010-an dengan paduan instalasi media baru.', 'Yogyakarta & Jakarta', 'Ruang Seni Nusantara')
ON DUPLICATE KEY UPDATE `title` = VALUES(`title`);

-- --------------------------------------------------------
-- 4. Data Awal Foto Polaroid (Seeding)
-- --------------------------------------------------------
INSERT INTO `photos` (`album_id`, `title`, `artist_1`, `artist_2`, `year`, `date`, `location`, `medium`, `dimensions`, `copyright`, `caption`, `image_src`, `thumb_src`, `tilt`, `tape`, `note`) VALUES
('jakarta-tempo-doeloe', 'Bundaran HI Masa Awal Pembangunan', 'Arsiparis Bambang S.', NULL, '1972', '14 Agustus 1972', 'Bundaran Hotel Indonesia, Jakarta Pusat', 'Fotografi Analog 35mm (Black & White)', '30 x 40 cm', 'Arsip Nasional Republik Indonesia', 'Pemandangan Hotel Indonesia yang megah dengan lalu lintas mobil klasik Fiat dan VW Kodok yang masih sangat tertib.', 'https://images.unsplash.com/photo-1578632767115-351597cf2477?auto=format&fit=crop&w=1200&q=85', 'https://images.unsplash.com/photo-1578632767115-351597cf2477?auto=format&fit=crop&w=600&q=80', '-2deg', 'top-right', 'Diambil oleh alm. Kakek saat bertugas di Bappenas'),
('jakarta-tempo-doeloe', 'Jalan Sabang di Senja Hari', 'Bambang Soetjipto', 'Hendra W.', '1978', '28 Oktober 1978', 'Jl. H. Agus Salim (Sabang), Jakarta', 'Fotografi Berwarna Analog (Kodachrome)', '20 x 30 cm', 'Hak Cipta Koleksi Keluarga Sutrisno', 'Deretan toko kelontong, kedai es kopi, dan lampu neon khas akhir tahun 70-an saat warga pulang kantor.', 'https://images.unsplash.com/photo-1509198397868-475647b2a1e5?auto=format&fit=crop&w=1200&q=85', 'https://images.unsplash.com/photo-1509198397868-475647b2a1e5?auto=format&fit=crop&w=600&q=80', '3deg', 'top-left', 'Tempat favorit beli kaset pita Barat & Pop Indo'),
('jakarta-tempo-doeloe', 'Pasar Senen & Stasiun Kereta Api', 'Kusworo Hartono', NULL, '1981', '03 Mei 1981', 'Pasar Senen, Jakarta Pusat', 'Cetak Gelatin Perak (Silver Gelatin Print)', '40 x 50 cm', 'Arsip Dinas Kebudayaan DKI Jakarta', 'Keramaian calon penumpang kereta jurusan Jawa Tengah memadati pelataran stasiun dengan koper seng dan kardus bertali rafia.', 'https://images.unsplash.com/photo-1526778548025-fa2f459cd5c1?auto=format&fit=crop&w=1200&q=85', 'https://images.unsplash.com/photo-1526778548025-fa2f459cd5c1?auto=format&fit=crop&w=600&q=80', '-1.5deg', 'both', 'Tiket kereta masih ditulis tangan oleh juru karcis'),
('jakarta-tempo-doeloe', 'Antrean Bioskop Megaria', 'Suryono Danu', 'Wartawan Sinema', '1984', '19 Desember 1984', 'Bioskop Metropole / Megaria, Cikini', 'Fotografi Dokumenter 35mm', '24 x 36 cm', 'Koleksi Sinematek Indonesia', 'Muda-mudi berdandan rapi dengan celana cutbrai dan rambut berombak mengantre tiket film layar lebar akhir pekan.', 'https://images.unsplash.com/photo-1489599849927-2ee91cede3ba?auto=format&fit=crop&w=1200&q=85', 'https://images.unsplash.com/photo-1489599849927-2ee91cede3ba?auto=format&fit=crop&w=600&q=80', '2.5deg', 'top-right', 'Film Warkop DKI "Maju Kena Mundur Kena"'),
('kenangan-masa-sekolah', 'Foto Kelas 3 Fisika 2 di Bawah Pohon Beringin', 'Dimas Prasetyo', 'Rina Kartika', '1994', '12 April 1994', 'Halaman Belakang SMAN 1', 'Fotografi Kamera Analog Yashica FX-3', '15 x 21 cm', 'Alumni 95 SMAN 1', 'Pose andalan kami sebelum menempuh EBTANAS. Senyum optimis menyambut masa depan yang belum tentu arahnya.', 'https://images.unsplash.com/photo-1529156069898-49953e39b3ac?auto=format&fit=crop&w=1200&q=85', 'https://images.unsplash.com/photo-1529156069898-49953e39b3ac?auto=format&fit=crop&w=600&q=80', '2deg', 'top-left', 'Foto pakai kamera analog Yashica milik Dimas'),
('kenangan-masa-sekolah', 'Latihan Band Festival Musik Pelajar', 'Agus Kridanto', NULL, '1993', '08 September 1993', 'Studio Rental Nada Indah', 'Dokumentasi Analog Fujicolor Super HR', '18 x 24 cm', 'Studio Nada Indah', 'Membawakan lagu Slank dan Nirvana dengan amplifier bising. Keringat dan tawa memenuhi ruangan studio sempit berkarpet kusam.', 'https://images.unsplash.com/photo-1511671782779-c97d3d27a1d4?auto=format&fit=crop&w=1200&q=85', 'https://images.unsplash.com/photo-1511671782779-c97d3d27a1d4?auto=format&fit=crop&w=600&q=80', '-2.5deg', 'top-right', 'Juara 2 se-Karesidenan!'),
('potret-pedesaan-jawa', 'Musim Panen Raya & Tumbuk Padi di Lesung', 'Ki Suryo Sasongko', 'Mangun Raharjo', '1968', '10 Juli 1968', 'Desa Somokaton, Ngluwar', 'Lukisan Cat Minyak di Atas Kanvas (Oil on Canvas)', '90 x 140 cm', 'Museum Seni & Budaya Nusantara', 'Ibu-ibu desa bergotong-royong menumbuk bulir padi menggunakan alu kayu dengan irama alu yang berpadu seperti alunan musik tradisional.', 'https://images.unsplash.com/photo-1500382017468-9049fed747ef?auto=format&fit=crop&w=1200&q=85', 'https://images.unsplash.com/photo-1500382017468-9049fed747ef?auto=format&fit=crop&w=600&q=80', '-1deg', 'top-right', 'Suara lesung terdengar sampai desa sebelah sejak subuh'),
('pesta-rakyat-pasar-malam', 'Nonton Layar Tancap Beralas Tikar Pandan', 'Teguh Sujarwo', NULL, '1985', '17 Agustus 1985', 'Lapangan Sepakbola Kampung Melayu', 'Seni Grafis Cukil Kayu & Cat Akrilik (Woodcut & Acrylic)', '60 x 80 cm', 'Paguyuban Warga Nostalgia', 'Warga berkumpul menikmati tontonan film laga kolosal gratis di bawah taburan bintang malam peringatan HUT RI ke-40.', 'https://images.unsplash.com/photo-1513151233558-d860c5398176?auto=format&fit=crop&w=1200&q=85', 'https://images.unsplash.com/photo-1513151233558-d860c5398176?auto=format&fit=crop&w=600&q=80', '-2.2deg', 'top-left', 'Tukang kacang rebus keliling selalu laris manis'),
('transportasi-kereta-uap', 'Lokomotif Uap Seri D52 di Stasiun Ambarawa', 'Ir. Soedarsono', 'Kolektif Balai Yasa', '1956', '07 Juli 1956', 'Stasiun Willem I (Ambarawa)', 'Fotografi Medium Format 120mm Monokrom', '50 x 70 cm', 'Pusat Pelestarian Benda Bersejarah PT KAI', 'Petugas masinis dan juru api bersiap memasukkan batu bara ke dalam tungku pembakaran menjelang keberangkatan ke Magelang.', 'https://images.unsplash.com/photo-1474487548417-781cb71495f3?auto=format&fit=crop&w=1200&q=85', 'https://images.unsplash.com/photo-1474487548417-781cb71495f3?auto=format&fit=crop&w=600&q=80', '1.2deg', 'top-left', 'Peluit uapnya berbunyi hingga radius 5 kilometer');

-- --------------------------------------------------------
-- 5. Tabel: users (Pengguna & Hak Akses RBAC)
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `users` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `username` VARCHAR(50) NOT NULL UNIQUE,
    `password` VARCHAR(255) NOT NULL,
    `full_name` VARCHAR(100) NOT NULL,
    `role` ENUM('admin', 'user') NOT NULL DEFAULT 'user',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- 6. Data Akun Default (Admin & User)
-- Password 'admin123' & 'user123' di-hash dengan Bcrypt
-- --------------------------------------------------------
INSERT INTO `users` (`username`, `password`, `full_name`, `role`) VALUES
('admin', '$2y$10$l01c1zhdtdGAM7mslH7TIuNSQ4M4oueyTH1WoIGHcLa5PnlzggGx2', 'Arsiparis Utama', 'admin'),
('user', '$2y$10$JPmy6hjPxOYCK2JMbzs1OOn3GocT4ZmkZQ.b4QpijrnQbNCOyzhmy', 'Pengunjung Nostalgia', 'user')
ON DUPLICATE KEY UPDATE `full_name` = VALUES(`full_name`), `role` = VALUES(`role`);

-- --------------------------------------------------------
-- 7. Tabel: events (Jadwal Event & Pameran Seni)
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `events` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `title` VARCHAR(255) NOT NULL,
    `theme` VARCHAR(255) DEFAULT NULL,
    `status` ENUM('upcoming','ongoing','completed') DEFAULT 'upcoming',
    `start_date` DATE NOT NULL,
    `end_date` DATE DEFAULT NULL,
    `start_time` TIME DEFAULT NULL,
    `end_time` TIME DEFAULT NULL,
    `ticket_price` VARCHAR(100) DEFAULT 'Gratis',
    `ticket_quota` INT DEFAULT NULL,
    `location_text` TEXT DEFAULT NULL,
    `location_map` TEXT DEFAULT NULL,
    `artists` TEXT DEFAULT NULL,
    `poster_image` TEXT DEFAULT NULL,
    `description` TEXT DEFAULT NULL,
    `organizer` VARCHAR(150) DEFAULT 'Jejak Waktu',
    `album_id` VARCHAR(100) DEFAULT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- 8. Tabel: bookings (Pemesanan Tiket Event)
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `bookings` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `event_id` INT NOT NULL,
    `user_id` INT NOT NULL,
    `qty` INT DEFAULT 1,
    `total_price` VARCHAR(100) DEFAULT NULL,
    `status` ENUM('pending','confirmed','cancelled') DEFAULT 'confirmed',
    `booking_code` VARCHAR(50) DEFAULT NULL,
    `notes` TEXT DEFAULT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`event_id`) REFERENCES `events`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- 9. Data Awal Event (Seeding)
-- --------------------------------------------------------
INSERT INTO `events` (`title`,`theme`,`status`,`start_date`,`end_date`,`start_time`,`end_time`,`ticket_price`,`ticket_quota`,`location_text`,`artists`,`poster_image`,`description`,`organizer`) VALUES
(
    'Pameran Besar Seni Rupa Nusantara 2026',
    'Jejak Peradaban: Antara Tradisi & Modernitas',
    'ongoing',
    '2026-09-01',
    '2026-09-30',
    '09:00:00',
    '17:00:00',
    'Gratis',
    500,
    'Galeri Nasional Indonesia, Jl. Medan Merdeka Timur No. 14, Jakarta Pusat',
    'Affandi Jr., Yuli Prayitno, Maya Indah Lestari, Rendra Kusuma, Sinta Dewi',
    'https://images.unsplash.com/photo-1541961017774-22349e4a1262?auto=format&fit=crop&w=800&q=85',
    'Sebuah perayaan agung seni rupa Indonesia yang menyatukan karya-karya terbaik seniman muda dan maestro senior dalam satu ruang pameran yang megah. Pameran ini menghadirkan lebih dari 120 karya pilihan mulai dari lukisan cat minyak, seni instalasi, hingga fotografi artistik berlatar sejarah Nusantara.',
    'Yayasan Seni Jejak Waktu'
),
(
    'Pameran Fotografi: Wajah Kota Lama',
    'Memori Visual Arsitektur Kolonial Nusantara',
    'upcoming',
    '2026-10-15',
    '2026-10-25',
    '10:00:00',
    '20:00:00',
    'Rp 25.000',
    200,
    'Museum Fatahillah, Kota Tua Jakarta',
    'Ardi Nugraha, Lestari Wibowo, Foto Komunitas Kota Lama',
    'https://images.unsplash.com/photo-1578632767115-351597cf2477?auto=format&fit=crop&w=800&q=85',
    'Pameran fotografi yang mengabadikan keindahan arsitektur kolonial dan kehidupan masyarakat di kawasan kota-kota lama bersejarah Indonesia. Lebih dari 80 karya foto analog dan digital ditampilkan berdampingan untuk menjembatani gap generasi dan teknologi dalam dunia fotografi.',
    'Komunitas Foto Arsip Nusantara'
),
(
    'Festival Seni Miniatur & Sketsa Urban 2026',
    'Kota dalam Goresan: Urban Sketching Indonesia',
    'upcoming',
    '2026-11-08',
    '2026-11-10',
    '08:00:00',
    '22:00:00',
    'Rp 35.000',
    300,
    'Braga Art Walk, Jl. Braga No. 1, Bandung',
    'Urban Sketchers Indonesia, Komunitas Sketsa Bandung, 50+ Seniman Lokal',
    'https://images.unsplash.com/photo-1513364776144-60967b0f800f?auto=format&fit=crop&w=800&q=85',
    'Festival seni sketsa outdoor terbesar di Indonesia yang mengundang seniman dari 30 kota untuk melukis wajah kota Bandung secara langsung di jalanan, taman, dan ruang publik. Pengunjung dapat menyaksikan proses pembuatan karya secara real-time dan berinteraksi langsung dengan para seniman.',
    'Urban Sketchers Indonesia & Pemkot Bandung'
),
(
    'Lelang Karya Maestro & Seniman Muda',
    'Dari Kanvas ke Koleksi: Seni Untuk Semua',
    'completed',
    '2026-08-20',
    '2026-08-20',
    '18:00:00',
    '22:00:00',
    'Gratis (Peserta Lelang Terdaftar)',
    150,
    'Hotel Raffles Jakarta, Jl. H.R. Rasuna Said Kav. 12, Jakarta Selatan',
    'Affandi Museum Collection, Basuki Abdullah Estate, Dede Eri Supria, Tisna Sanjaya',
    'https://images.unsplash.com/photo-1499343162172-9b73b4abb8a5?auto=format&fit=crop&w=800&q=85',
    'Malam lelang bergengsi yang mempertemukan kolektor seni dengan karya-karya otentik maestro Indonesia dan karya segar seniman-seniman muda berbakat. Event ini juga merupakan upaya pendanaan beasiswa bagi seniman muda dari keluarga tidak mampu.',
    'Jejak Waktu Arts Foundation'
)
ON DUPLICATE KEY UPDATE `title` = VALUES(`title`);


-- --------------------------------------------------------
-- 10. Tabel: galleries (Galeri & Art Space Surabaya)
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `galleries` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `slug` VARCHAR(100) NOT NULL UNIQUE,
    `name` VARCHAR(255) NOT NULL,
    `alt_name` VARCHAR(255) DEFAULT NULL,
    `address` TEXT NOT NULL,
    `category` VARCHAR(100) NOT NULL,
    `status` VARCHAR(50) NOT NULL DEFAULT 'Aktif',
    `founder_or_owner` VARCHAR(255) DEFAULT 'Belum ditemukan',
    `established_year` VARCHAR(50) DEFAULT 'Belum ditemukan',
    `history` TEXT DEFAULT NULL,
    `focus` TEXT DEFAULT NULL,
    `events` TEXT DEFAULT NULL,
    `latitude` DECIMAL(10, 7) DEFAULT NULL,
    `longitude` DECIMAL(10, 7) DEFAULT NULL,
    `is_verified_coord` TINYINT(1) DEFAULT 1,
    `website` VARCHAR(255) DEFAULT 'Belum ditemukan',
    `instagram` VARCHAR(255) DEFAULT 'Belum ditemukan',
    `image` TEXT DEFAULT NULL,
    `sources` TEXT DEFAULT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `galleries` (`slug`, `name`, `alt_name`, `address`, `category`, `status`, `founder_or_owner`, `established_year`, `history`, `focus`, `events`, `latitude`, `longitude`, `is_verified_coord`, `website`, `instagram`, `image`, `sources`) VALUES 
('orasis-art-space', 'Orasis Art Space', 'Pelangi Nusantara Art Gallery / Orasis Art Gallery', 'Jl. Bukit Golf No. B2-25, CitraLand, Surabaya', 'Galeri Seni', 'Aktif', 'Elizabeth / Liza', '2002 / 2005', 'Bermula sebagai Pelangi Nusantara Art Gallery pada tahun 2002, kemudian bertransformasi menjadi Orasis Art Gallery pada tahun 2005. Berlokasi di kawasan CitraLand Surabaya Barat dan menjadi ruang pamer seni kontemporer terkemuka.', 'Seni rupa kontemporer Indonesia', '[\"Wacana Pelangi Nusantara\",\"Pelangi Nusantara\",\"The Power of Mind\",\"Celebration: Grand Opening\",\"Biennale Jatim X: Invisible Territories\",\"Kine Kini\",\"Ireland’s Eye\",\"Shipibo–Konibo: Portraits of My Blood\",\"O.H. Supono: Supercut of Life\",\"Urban Pulse\",\"A Man, A Monster & The Sea\",\"Segue 2nd Edition: Lim Keng – Breath of Lines\",\"FINNA Art of The Year 2025\"]', -7.2792, 112.6693, 1, 'https://orasisartspace.com', '@orasisartspace', 'https://images.unsplash.com/photo-1577720643272-265f09367456?auto=format&fit=crop&w=800&q=80', 'Katalog Pameran Orasis Art Space, Arsip Kuratorial Biennale Jatim X'),
('hadi-art-platform', 'HaDi Art Platform', 'HadiArtPlatform Contemporary Art Gallery / Hadi Gallery', 'Jl. Darmokali No. 16, Keputran, Tegalsari, Surabaya', 'Galeri Seni Kontemporer', 'Aktif', 'Irawan Hadikusumo', '2018 (14 Juli 2018)', 'Berkembang dari Hadi Gallery yang didirikan oleh Irawan Hadikusumo pada 14 Juli 2018. Kini beroperasi di Jl. Darmokali No. 16 Surabaya sebagai galeri seni kontemporer dan platform diskusi serta jejaring seni lintas kota.', 'Seni rupa kontemporer Indonesia, platform seni, diskusi dan jaringan seni', '[\"Pameran Seni Kontemporer HaDi Art\",\"Forum Kuratorial Seni Rupa Surabaya\",\"Presentasi Karya & Dialog Seniman Muda\"]', -7.2927865, 112.7419552, 1, 'Belum ditemukan', 'Belum ditemukan', 'https://images.unsplash.com/photo-1536924940846-227afb31e2a5?auto=format&fit=crop&w=800&q=80', 'Dokumentasi HaDi Art Platform, Arsip Kuratorial Surabaya'),
('visma-art-gallery', 'Visma Art Gallery', 'Visma Gallery', 'Jl. Tegalsari No. 35–37, Tegalsari, Surabaya', 'Galeri Seni', 'Aktif', 'Irawan Hadikusumo (Co-founder)', '2015', 'Didirikan pada tahun 2015 di kawasan bersejarah Tegalsari Surabaya dengan keterlibatan Irawan Hadikusumo sebagai co-founder. Menjadi galeri seni independen yang aktif menyelenggarakan pameran lukisan, fotografi, workshop, dan kolaborasi kebudayaan internasional.', 'Seni rupa, lukisan, fotografi, workshop dan kolaborasi kebudayaan', '[\"Pameran karya Jacques Ferrier bersama Institut Français Indonesia (IFI) Surabaya\",\"Pameran Fotografi & Seni Visual Tegalsari\",\"Workshop Seni & Kolaborasi Lintas Disiplin\"]', -7.2763435, 112.7337319, 1, 'Belum ditemukan', 'Belum ditemukan', 'https://images.unsplash.com/photo-1518998053901-5348d3961a04?auto=format&fit=crop&w=800&q=80', 'Institut Français Indonesia (IFI) Surabaya, Arsip Visma Art Gallery'),
('uycc-art-gallery', 'UYCC Art Gallery', 'Unicorn Young Collector Club', 'The WIN Hotel, Jl. Embong Tanjung No. 46–48 Lantai 2, Genteng, Surabaya', 'Galeri Seni', 'Aktif', 'Aldridge Tjiptarahardja', '2023 (Maret 2023)', 'Dibuka resmi pada Maret 2023 di The WIN Hotel lantai 2 Surabaya sebagai pengembangan dari ekosistem kreatif Unicorn Creative Space. Ditujukan sebagai wadah segar bagi apresiasi seni rupa Indonesia dan para kolektor muda.', 'Seni rupa Indonesia dan ruang bagi kolektor muda', '[\"Continuity\",\"Picturesque: Mixed Feeling Provoking Art Exhibition\",\"From the River to the Liberty: Experience Art for Human Rights\"]', -7.2648, 112.7384, 1, 'Belum ditemukan', '@uycc.artgallery', 'https://images.unsplash.com/photo-1545989253-02cc26577f88?auto=format&fit=crop&w=800&q=80', 'Katalog UYCC Art Gallery, Dokumentasi Media Seni Visual'),
('unicorn-creative-space', 'Unicorn Creative Space', 'Unicorn Extension', 'Kawasan Rungkut & Unicorn Extension, Jl. Dharma Husada Indah Utara No. 41, Mulyorejo, Surabaya', 'Art Space', 'Aktif', 'Aldridge Tjiptarahardja', '2020', 'Didirikan pada tahun 2020 oleh Aldridge Tjiptarahardja. Bermula di kawasan Rungkut dan berekspansi ke Dharma Husada Indah Utara No. 41 Surabaya sebagai hub kreatif komunitas seni, workshop, dan pameran berkala.', 'Seni, creative space, workshop dan kegiatan kreatif', '[\"Pameran seni rupa\",\"Pameran fotografi\",\"Workshop batik\",\"Workshop ecoprint\",\"Workshop lilin aroma\",\"Workshop suminagashi\",\"Piknik Seni\",\"From the River to the Liberty: Experience Art for Human Rights\"]', -7.2700574, 112.7755609, 1, 'Belum ditemukan', '@unicorncreativespace', 'https://images.unsplash.com/photo-1501084817091-a4f3d1d19e07?auto=format&fit=crop&w=800&q=80', 'Dokumentasi Program Unicorn Creative Space'),
('galeri-prabangkara', 'Galeri Prabangkara', 'Galeri Prabangkara UPT Taman Budaya Jatim', 'Jl. Genteng Kali No. 85, Genteng, Surabaya', 'Galeri Institusi/Pemerintah', 'Aktif', 'UPT Taman Budaya Jawa Timur / Disbudpar Jatim', '2015 (10 Februari 2015)', 'Diresmikan pada 10 Februari 2015 di kompleks Taman Budaya Jawa Timur. Dikelola oleh UPT Taman Budaya Dinas Kebudayaan & Pariwisata Provinsi Jawa Timur sebagai ruang pameran seni rupa resmi pemerintah bagi seniman lokal, daerah, dan nasional.', 'Seni rupa dan pameran kebudayaan', '[\"Ada dan Tiada\",\"Alaras Terang\",\"Garis Gathuk\",\"Let\'s Imagine the Future Together\",\"Dari Gosari ke Kasongan\"]', -7.2647, 112.7435, 1, 'https://tamanbudayajatim.com', '@tamanbudayajatim', 'https://images.unsplash.com/photo-1579783900882-c0d3dad7b119?auto=format&fit=crop&w=800&q=80', 'UPT Taman Budaya Dinas Kebudayaan & Pariwisata Prov. Jawa Timur'),
('vin-autism-gallery', 'Vin Autism Gallery', 'Vincent Prijadi Art Gallery', 'G-Walk Junction TL 6/11, Citraland, Sambikerep, Surabaya', 'Galeri Seni Khusus', 'Aktif', 'Vincent Prijadi Purwono dan keluarga', 'Belum ditemukan', 'Didirikan oleh keluarga perupa Vincent Prijadi Purwono di kawasan G-Walk Citraland Surabaya Barat. Galeri ini mewadahi karya seni visual anak berkebutuhan khusus dan memberikan edukasi seni inklusif yang menginspirasi publik.', 'Lukisan, seni visual dan kegiatan seni untuk anak berkebutuhan khusus', '[\"Should We Slow Down\",\"Pameran lukisan Vincent di Stasiun Gubeng\",\"Berbagai workshop seni inklusif\"]', -7.2878805, 112.6554319, 1, 'Belum ditemukan', '@vinautismgallery', 'https://images.unsplash.com/photo-1547826039-bfc35e0f1ea8?auto=format&fit=crop&w=800&q=80', 'Dokumentasi Vin Autism Gallery, Liputan Media Seni Surabaya'),
('teh-villa-gallery', 'Teh Villa Gallery', 'Galeri Seni Teh Villa', 'Jl. Raya Rungkut Industri II No. 53, Rungkut, Surabaya', 'Galeri Seni', 'Aktif', 'Teh Villa Indonesia / PT Karya Mas Makmur', '1981 (sejarah bisnis) / 2020', 'Berkaitan erat dengan sejarah bisnis keluarga produsen Teh Villa (PT Karya Mas Makmur) yang berdiri di Surabaya sejak 1981. Menghadirkan ruang galeri seni rupa representatif di kawasan Rungkut Industri untuk mengangkat karya seni rupa dan kegiatan kemanusiaan.', 'Seni lukis, ruang pameran, dan kegiatan seni sosial kemanusiaan', '[\"Living in Harmony\",\"Finding Balance\",\"Art for Humanity\",\"Unexpected Reunion\"]', -7.3282, 112.7756, 1, 'https://tehvilla.com', '@tehvillagallery', 'https://images.unsplash.com/photo-1579783902614-a3fb3927b675?auto=format&fit=crop&w=800&q=80', 'Arsip Pameran Teh Villa Gallery, PT Karya Mas Makmur'),
('samata-house', 'Samata House', 'Samata House Coffee & Art', 'Jl. Lingga No. 6, Gubeng, Surabaya', 'Art Space', 'Aktif', 'Belum ditemukan', 'Belum ditemukan', 'Ruang alternatif di kawasan Gubeng yang menggabungkan coffee house, ruang kreatif, dan galeri pameran bagi seniman muda independen. Dikenal aktif mengadakan pameran seni rupa, pasar seni, dan diskusi kebudayaan.', 'Ruang kreatif, coffee house dan ruang pamer', '[\"TAKE OVER\",\"Berbagai pameran seniman muda\",\"Jatim Biennale XI – Kelana\",\"Berbagai kegiatan seni dan kreatif\"]', -7.2699509, 112.7512636, 1, 'Belum ditemukan', '@samatahouse', 'https://images.unsplash.com/photo-1513519245088-0e12902e5a38?auto=format&fit=crop&w=800&q=80', 'Dokumentasi Samata House, Jatim Biennale XI'),
('c2o-library-collabtive', 'C2O Library & Collabtive', 'C2O Library', 'Jl. Dr. Cipto No. 22, Dr. Soetomo, Tegalsari, Surabaya', 'Creative Space', 'Aktif', 'PERINTIS', '2008', 'Didirikan pada tahun 2008 oleh organisasi nirlaba PERINTIS. C2O adalah perpustakaan independen, ruang kerja kolaboratif, serta pusat pameran budaya, literasi, film, desain, dan kegiatan kreatif komunitas warga.', 'Seni, literasi, diskusi, film, desain dan kegiatan kreatif', '[\"Codex Code Art Exhibition\",\"Post a Place\",\"Surabaya AnimNation Festival\",\"Eat Play Laugh\",\"Design It Yourself (DIY) Fair\",\"Berbagai diskusi, workshop dan pemutaran film\"]', -7.2812044, 112.7388612, 1, 'https://c2o-library.net', '@c2olibrary', 'https://images.unsplash.com/photo-1521587760476-6c12a4b040da?auto=format&fit=crop&w=800&q=80', 'Kolektif C2O Library, Arsip Festival Seni Surabaya'),
('rahasia-art-space', 'Rahasia Art Space', 'Rahasia Studio', 'Perum YKP I, Jl. Pandugo Timur I No. 22, Penjaringan Sari, Rungkut, Surabaya', 'Art Space', 'Aktif', 'Pak Thomas', 'sekitar 2019', 'Didirikan sekitar tahun 2019 oleh Pak Thomas di perumahan YKP I Rungkut sebagai ruang kreasi seni independen, studio pameran, dan workshop kerajinan bagi komunitas seni lokal.', 'Art space dan kegiatan kreatif', '[\"Pameran Seni Komunitas Rungkut\",\"Workshop Kerajinan & Lukis Kreatif\",\"Open Studio & Diskusi Seni\"]', -7.318147, 112.7900883, 1, 'Belum ditemukan', 'Belum ditemukan', 'https://images.unsplash.com/photo-1460661419201-fd4cecdf8a8b?auto=format&fit=crop&w=800&q=80', 'Dokumentasi Komunitas Seni Rungkut'),
('galeri-merah-putih', 'Galeri Merah Putih', 'Belum ditemukan', 'Kawasan Balai Pemuda / Embong Kaliasin, Genteng, Surabaya', 'Galeri Seni', 'Perlu diverifikasi', 'Belum ditemukan', 'Belum ditemukan', 'Galeri seni rupa yang tercatat berada di sekitar kawasan Balai Pemuda / Embong Kaliasin Genteng Surabaya. Sejarah pendirian, pengelola, dan catatan kuratorial pamerannya perlu diverifikasi lebih lanjut.', 'Seni rupa dan pameran seni lukis', '[\"Pameran Lukisan Merah Putih\",\"Perlu diverifikasi lebih lanjut\"]', -7.2639219, 112.745293, 0, 'Belum ditemukan', 'Belum ditemukan', 'https://images.unsplash.com/photo-1541961017774-22349e4a1262?auto=format&fit=crop&w=800&q=80', 'Arsip Kesenian Kota Surabaya, Perlu verifikasi lebih lanjut'),
('galeri-mojopahit', 'Galeri Mojopahit', 'Belum ditemukan', 'Jl. Darmokali No. 61A, Darmo, Wonokromo, Surabaya', 'Galeri Seni', 'Perlu diverifikasi', 'Belum ditemukan', 'Belum ditemukan', 'Galeri seni rupa yang beralamat di Jl. Darmokali No. 61A Wonokromo Surabaya. Data historis, pendiri, dan arsip pamerannya masih memerlukan verifikasi lapangan lebih lanjut.', 'Seni rupa dan karya visual', '[\"Pameran Seni Lukis Koleksi\",\"Perlu diverifikasi lebih lanjut\"]', -7.2927865, 112.7419552, 0, 'Belum ditemukan', 'Belum ditemukan', 'https://images.unsplash.com/photo-1582561424760-0321d75e81fa?auto=format&fit=crop&w=800&q=80', 'Direktori Galeri Seni Surabaya, Perlu verifikasi'),
('studio-lima-gallery', 'Studio Lima Gallery', 'Belum ditemukan', 'Jl. Medokan Asri Utara VII No. 28, Medokan Ayu, Rungkut, Surabaya', 'Galeri Seni', 'Perlu diverifikasi', 'Belum ditemukan', 'Belum ditemukan', 'Studio dan ruang galeri seni rupa mandiri di Medokan Ayu Surabaya Timur. Sejarah pendirian, pengelola, dan kurasi pamerannya memerlukan verifikasi lebih lanjut.', 'Seni rupa, lukisan, dan studio seni', '[\"Pameran Studio Seni Lokal\",\"Perlu diverifikasi lebih lanjut\"]', -7.3225397, 112.7942222, 0, 'Belum ditemukan', 'Belum ditemukan', 'https://images.unsplash.com/photo-1576769267415-9642000aa3cb?auto=format&fit=crop&w=800&q=80', 'Direktori Komunitas Seni Rungkut, Perlu verifikasi'),
('wusto-art-gallery', 'Wusto Art Gallery', 'Wusto Galeri Seni & Kaligrafi', 'Royal Plaza, Lantai LG Blok AA2-02, Ketintang, Wonokromo, Surabaya', 'Galeri Seni', 'Aktif', 'Belum ditemukan', 'Belum ditemukan', 'Galeri seni dan kerajinan komersial di Royal Plaza Surabaya lantai LG Blok AA2-02, menyajikan karya lukisan lanskap, figuratif, kaligrafi, dan bingkai seni berkualitas.', 'Lukisan, kaligrafi, dan karya seni dekoratif', '[\"Bursa Lukisan Royal Plaza\",\"Display Karya Seni Rutin\"]', -7.3091349, 112.7342835, 1, 'Belum ditemukan', 'Belum ditemukan', 'https://images.unsplash.com/photo-1513364776144-60967b0f800f?auto=format&fit=crop&w=800&q=80', 'Direktori Tenant Royal Plaza Surabaya'),
('utama-slamet-art-gallery', 'Utama Slamet Art Gallery', 'Galeri Utama Slamet', 'Jl. Pahlawan No. 54 C-D, Alun-alun Contong, Bubutan, Surabaya', 'Galeri Seni', 'Aktif', 'Belum ditemukan', 'Belum ditemukan', 'Galeri seni lukis dan pembuatan bingkai seni klasik di jalur bersejarah Jl. Pahlawan dekat Tugu Pahlawan Surabaya. Menyediakan aneka lukisan klasik dan karya seni seniman lokal.', 'Seni rupa, lukisan lanskap & figuratif, custom framing', '[\"Pameran Lukisan Klasik Jawa Timur\",\"Bursa Seni Rupa Surabaya\"]', -7.2452377, 112.7385975, 1, 'Belum ditemukan', 'Belum ditemukan', 'https://images.unsplash.com/photo-1579783900882-c0d3dad7b119?auto=format&fit=crop&w=800&q=80', 'Direktori Seni & Budaya Kota Surabaya'),
('unsur-site', 'Unsur Site', 'Unsur Art Space', 'Jl. Untung Suropati No. 85, DR. Soetomo, Tegalsari, Surabaya', 'Art Space', 'Aktif', 'Belum ditemukan', 'Belum ditemukan', 'Ruang seni independen dan proyek seni kolaboratif di Jl. Untung Suropati No. 85 Surabaya. Menjadi arena presentasi ide baru dan diskusi seni rupa visual kontemporer.', 'Eksperimentasi seni, instalasi, dan ruang seni alternatif', '[\"Pameran Kolaborasi Visual Unsur\",\"Sesi Presentasi Karya Seniman Muda\",\"Diskusi Seni Kontemporer\"]', -7.2819427, 112.7348337, 1, 'Belum ditemukan', '@unsur.site', 'https://images.unsplash.com/photo-1499343162172-9b73b4abb8a5?auto=format&fit=crop&w=800&q=80', 'Dokumentasi Program Seni Unsur Site'),
('filadelvia', 'Filadelvia', 'Filadelvia Art Gallery', 'Ruko Taman Puspa Raya Blok D No. 09, Sambikerep, Surabaya', 'Galeri Seni', 'Aktif', 'Belum ditemukan', 'Belum ditemukan', 'Galeri seni dan workshop lukisan di kawasan ruko Taman Puspa Raya CitraLand Surabaya Barat yang menyediakan karya seni lukis dan bingkai artistik.', 'Seni lukis, karya seni visual, dan workshop melukis', '[\"Display Lukisan Kontemporer\",\"Workshop Melukis Santai\"]', -7.2762729, 112.6446587, 1, 'Belum ditemukan', 'Belum ditemukan', 'https://images.unsplash.com/photo-1578632767115-351597cf2477?auto=format&fit=crop&w=800&q=80', 'Direktori Usaha Seni Sambikerep Surabaya'),
('art-center-bg-junction', 'Art Center BG Junction', 'Pusat Seni BG Junction', 'BG Junction Mall, Lantai L2, Jl. Bubutan No. 1–7, Bubutan, Surabaya', 'Ruang Budaya', 'Aktif', 'Manajemen BG Junction Surabaya', 'Belum ditemukan', 'Pusat kegiatan seni publik di mal BG Junction Bubutan Surabaya. Sering dipakai untuk perhelatan festival seni budaya, pagelaran kesenian daerah, dan pameran seni rupa warga kota.', 'Pameran seni publik, festival budaya, dan apresiasi karya masyarakat', '[\"Festival Seni Budaya Surabaya di BG Junction\",\"Pameran Lukisan & Karya Kerajinan Publik\"]', -7.2549143, 112.7334286, 1, 'https://bgjunction.com', '@bgjunctionsurabaya', 'https://images.unsplash.com/photo-1508997449629-303059a039c0?auto=format&fit=crop&w=800&q=80', 'Arsip Acara BG Junction, Disbudporapar Surabaya'),
('kampoeng-seni-bg-junction', 'Kampoeng Seni', 'Kampoeng Seni BG Junction', 'BG Junction Mall, Lantai L2, Jl. Bubutan No. 1–7, Bubutan, Surabaya', 'Galeri Seni Tradisional', 'Aktif', 'Paguyuban Seniman & Pengrajin Surabaya', 'Belum ditemukan', 'Komunitas sentra seniman lukis dan pengrajin tradisional yang berhimpun di lantai L2 mal BG Junction Surabaya. Pengunjung dapat melihat proses seniman melukis sketsa wajah, karikatur, serta karya kriya secara langsung.', 'Seni rupa tradisional, lukisan potret langsung, ukiran dan kerajinan tangan', '[\"Bursa Lukisan On The Spot Kampoeng Seni\",\"Demo Melukis Sketsa Wajah & Karikatur\",\"Pameran Kerajinan Tradisional Jatim\"]', -7.2552, 112.7339, 1, 'Belum ditemukan', 'Belum ditemukan', 'https://images.unsplash.com/photo-1513364776144-60967b0f800f?auto=format&fit=crop&w=800&q=80', 'Komunitas Seniman Kampoeng Seni BG Junction'),
('sakura-warangan-keris', 'Sakura Warangan Keris', 'Sanggar Tosan Aji Sakura', 'Jl. Pakis Tirtosari XA No. 26, Sawahan, Surabaya', 'Galeri Seni Tradisional', 'Aktif', 'Belum ditemukan', 'Belum ditemukan', 'Galeri pusaka dan sanggar konservasi tosan aji tradisional di kawasan Sawahan Surabaya. Mengedukasi masyarakat mengenai filosofi keris Nusantara, proses perawatan warangan, dan pelestarian benda budaya.', 'Keris, tosan aji, warangan, dan benda seni tradisional Nusantara', '[\"Edukasi Budaya Tosan Aji & Jamasan Keris\",\"Pameran Koleksi Keris Kuno Nusantara\",\"Workshop Konservasi Benda Pusaka Tradisional\"]', -7.2910403, 112.7210246, 1, 'Belum ditemukan', 'Belum ditemukan', 'https://images.unsplash.com/photo-1582561424760-0321d75e81fa?auto=format&fit=crop&w=800&q=80', 'Paguyuban Pecinta Tosan Aji Surabaya'),
('cai-cai-company', 'Cai-Cai Company', 'Cai-Cai Design & Caricature', 'Kawasan Villa Kalijudan Indah, Kalijudan, Mulyorejo, Surabaya', 'Creative Space', 'Aktif', 'Belum ditemukan', 'Belum ditemukan', 'Studio kreasi seni dan desain di kawasan Kalijudan Indah Surabaya Timur, menghadirkan workshop dan pameran karya visual bergenre karikatur dan desain grafis.', 'Desain, ilustrasi, dan seni karikatur', '[\"Pameran Karikatur & Seni Ilustrasi\",\"Workshop Menggambar Karikatur Urban\"]', -7.2605867, 112.7785473, 1, 'Belum ditemukan', 'Belum ditemukan', 'https://images.unsplash.com/photo-1541961017774-22349e4a1262?auto=format&fit=crop&w=800&q=80', 'Direktori Studio Desain Surabaya'),
('galeri-dks-balai-pemuda', 'Galeri DKS / Galeri Surabaya', 'Balai Pemuda Art Space / Galeri Dewan Kesenian Surabaya', 'Kawasan Balai Pemuda, Jl. Gubernur Suryo No. 15, Embong Kaliasin, Genteng, Surabaya', 'Galeri Institusi/Pemerintah', 'Aktif', 'Dewan Kesenian Surabaya (DKS) / Pemkot Surabaya', '1971 (DKS 10 Oktober 1971) / awal 1980-an (galeri)', 'Berkaitan erat dengan Dewan Kesenian Surabaya (DKS) yang berdiri pada 10 Oktober 1971 dan pengembangan ruang galeri pameran pada awal 1980-an di kompleks cagar budaya Balai Pemuda. Menjadi episentrum bersejarah pergerakan seniman Surabaya dari masa ke masa.', 'Seni rupa dan kegiatan kebudayaan multidisiplin', '[\"Pameran Seni Rupa Tahunan DKS\",\"Surabaya Art Week\",\"Pementasan Teater & Tari Tradisional Balai Pemuda\",\"Diskusi Sastra & Pemutaran Film Independen\"]', -7.2645158, 112.7455144, 1, 'https://surabaya.go.id', '@dewankeseniansurabaya', 'https://images.unsplash.com/photo-1544816155-12df9643f363?auto=format&fit=crop&w=800&q=80', 'Dewan Kesenian Surabaya, Dinas Kebudayaan & Pariwisata Kota Surabaya'),
('emmitan-fine-art-gallery', 'Emmitan Fine Art Gallery', 'Emmitan Gallery', 'Jl. Walikota Mustajab, Ketabang, Genteng, Surabaya', 'Galeri Historis', 'Historis/Tidak aktif', 'Belum ditemukan', 'Belum ditemukan', 'Galeri seni rupa legendaris yang tercatat pernah aktif beroperasi di Jl. Walikota Mustajab Surabaya. Dikenal dalam sejarah seni rupa Jawa Timur sebagai tempat pameran prestisius karya master seniman nasional dan ruang diskusi kesenian Jawa Timur.', 'Seni rupa murni, seni lukis maestro, dan diskursus seni', '[\"Pameran Seniman Nasional & Jawa Timur\",\"Retrospeksi Seni Lukis Modern\",\"Diskusi Kesenian & Temu Kurator\"]', -7.2607032, 112.7467381, 1, 'Belum ditemukan', 'Belum ditemukan', 'https://images.unsplash.com/photo-1579783900882-c0d3dad7b119?auto=format&fit=crop&w=800&q=80', 'Arsip Sejarah Seni Rupa Surabaya, Katalog Seni Rupa Indonesia'),
('gracia-art-gallery', 'Gracia Art Gallery', 'Gracia Gallery', 'Kawasan Bukit Darmo Golf, Pradah Kalikendal, Dukuh Pakis, Surabaya', 'Galeri Historis', 'Historis/Tidak aktif', 'Belum ditemukan', 'Belum ditemukan', 'Galeri seni prestisius yang pernah tercatat berlokasi di kawasan Bukit Darmo Golf Surabaya Barat. Menjadi ruang pameran penting bagi karya seni rupa seniman Surabaya serta pelukis dari berbagai kota di luar Jawa Timur.', 'Seni rupa, pameran lukisan koleksi, dan apresiasi seni', '[\"Pameran Seniman Surabaya & Luar Kota\",\"Pameran Lukisan Masterpiece Koleksi\"]', -7.2891865, 112.6874017, 1, 'Belum ditemukan', 'Belum ditemukan', 'https://images.unsplash.com/photo-1578632767115-351597cf2477?auto=format&fit=crop&w=800&q=80', 'Arsip Galeri Seni Surabaya Barat, Catatan Kuratorial Jawa Timur'),
('its-cultural-center', 'ITS Cultural Center', 'Pusat Kebudayaan ITS', 'Institut Teknologi Sepuluh Nopember, Keputih, Sukolilo, Surabaya', 'Ruang Budaya', 'Aktif', 'Institut Teknologi Sepuluh Nopember (ITS)', 'Belum ditemukan', 'Pusat kegiatan kebudayaan dan ruang apresiasi seni di lingkungan Institut Teknologi Sepuluh Nopember (ITS) Surabaya. Bukan galeri komersial murni, melainkan ruang budaya akademik untuk seni pertunjukan, diskusi dan kegiatan kebudayaan civitas akademika dan publik.', 'Seni pertunjukan, diskusi dan kegiatan kebudayaan', '[\"Pagelaran Seni & Budaya Nusantara ITS\",\"Festival Tari & Musik Mahasiswa\",\"Diskusi Kebudayaan & Sains-Seni\"]', -7.2826954, 112.7956782, 1, 'https://www.its.ac.id', '@itsculturalcenter', 'https://images.unsplash.com/photo-1511671782779-c97d3d27a1d4?auto=format&fit=crop&w=800&q=80', 'Direktorat Kemahasiswaan & Kebudayaan ITS Surabaya'),
('galeri-ajbs', 'Galeri AJBS', 'AJBS Art Gallery', 'Jl. Raya Dukuh Kupang No. 110, Sawahan, Surabaya', 'Galeri Seni', 'Aktif', 'Agus Joko Budi Santoso', '2010', 'Didirikan pada tahun 2010 oleh kolektor seni Agus Joko Budi Santoso di kompleks AJBS Surabaya. Menjadi galeri seni terpadu yang aktif mewadahi seniman Jawa Timur dan Nusantara untuk menggelar pameran seni rupa bersama dan workshop seni rupa.', 'Seni rupa Jawa Timur & Nusantara, pameran bersama, dan workshop melukis', '[\"Pameran Seni Rupa Nusantara 2020\",\"Workshop Melukis Akrilis Bersama 2022\",\"Pameran Kolaborasi Seniman Jawa Timur 2023\"]', -7.2931, 112.7147, 1, 'Belum ditemukan', 'Belum ditemukan', 'https://images.unsplash.com/photo-1460661419201-fd4cecdf8a8b?auto=format&fit=crop&w=800&q=80', 'Dokumentasi Galeri AJBS Surabaya'),
('house-of-sampoerna', 'House of Sampoerna', 'The Pavillion House of Sampoerna', 'Taman Sampoerna No. 6, Krembangan Utara, Pabean Cantian, Surabaya', 'Ruang Budaya', 'Historis/Tidak aktif', 'Keluarga Sampoerna (Putera Sampoerna Foundation)', '2004 (kompleks berdiri 1862)', 'Kompleks cagar budaya bersejarah peninggalan kolonial Belanda yang dibangun pada 1862. Sejak 2004 dikelola oleh Putera Sampoerna Foundation sebagai museum industri tembakau serta galeri seni rupa berkala \"The Pavillion\" yang menampilkan pameran seni rupa terkemuka Jawa Timur.', 'Seni rupa kontemporer di The Pavillion, sejarah industri, arsitektur cagar budaya, dan tur sejarah Heritage Walk', '[\"Pameran Tetap Sejarah Sampoerna & Surabaya\",\"Art Market Bulanan Taman Sampoerna 2022\",\"Heritage Walk & Photography Exhibition 2023\",\"Pameran \\\"Kota Pahlawan dalam Kanvas\\\" 2024\"]', -7.2309923, 112.7341461, 1, 'https://houseofsampoerna.museum', '@houseofsampoerna', 'https://images.unsplash.com/photo-1578632767115-351597cf2477?auto=format&fit=crop&w=800&q=80', 'Putera Sampoerna Foundation, Arsip Cagar Budaya Surabaya')
ON DUPLICATE KEY UPDATE `name` = VALUES(`name`);

-- --------------------------------------------------------
-- 5. Tabel: feedbacks (Kritik & Saran Pengunjung)
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `feedbacks` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `nama` VARCHAR(150) DEFAULT NULL,
    `email` VARCHAR(150) DEFAULT NULL,
    `jenis_masukan` ENUM('Kritik', 'Saran', 'Koreksi Data Galeri', 'Laporan Kesalahan', 'Lainnya') NOT NULL DEFAULT 'Saran',
    `isi` TEXT NOT NULL,
    `status` ENUM('Belum dibaca', 'Sudah dibaca', 'Ditindaklanjuti') NOT NULL DEFAULT 'Belum dibaca',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- 6. Tabel: artist_profiles (Profil Kustom & Foto Profil Seniman)
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `artist_profiles` (
    `id` VARCHAR(100) NOT NULL PRIMARY KEY,
    `name` VARCHAR(255) DEFAULT NULL,
    `role` VARCHAR(255) DEFAULT NULL,
    `era` VARCHAR(100) DEFAULT NULL,
    `category` VARCHAR(50) DEFAULT NULL,
    `avatar` TEXT DEFAULT NULL,
    `bio` TEXT DEFAULT NULL,
    `location` VARCHAR(150) DEFAULT NULL,
    `is_deleted` TINYINT(1) NOT NULL DEFAULT 0,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


