<?php
/**
 * API Endpoints: Galeri & Art Space Kota Surabaya
 * Jejak Waktu - Interactive Art Map
 */

require_once __DIR__ . '/db.php';

// Data bawaan komprehensif 28 galeri & art space Surabaya (Data utama & Fallback jika DB offline)
$DEFAULT_GALLERIES = [
    [
        'slug' => 'orasis-art-space',
        'name' => 'Orasis Art Space',
        'alt_name' => 'Pelangi Nusantara Art Gallery / Orasis Art Gallery',
        'address' => 'Jl. Bukit Golf No. B2-25, CitraLand, Surabaya',
        'category' => 'Galeri Seni',
        'status' => 'Aktif',
        'founder_or_owner' => 'Elizabeth / Liza',
        'established_year' => '2002 / 2005',
        'history' => 'Bermula sebagai Pelangi Nusantara Art Gallery pada tahun 2002, kemudian bertransformasi menjadi Orasis Art Gallery pada tahun 2005. Berlokasi di kawasan CitraLand Surabaya Barat dan menjadi ruang pamer seni kontemporer terkemuka.',
        'focus' => 'Seni rupa kontemporer Indonesia',
        'events' => json_encode([
            'Wacana Pelangi Nusantara',
            'Pelangi Nusantara',
            'The Power of Mind',
            'Celebration: Grand Opening',
            'Biennale Jatim X: Invisible Territories',
            'Kine Kini',
            'Ireland’s Eye',
            'Shipibo–Konibo: Portraits of My Blood',
            'O.H. Supono: Supercut of Life',
            'Urban Pulse',
            'A Man, A Monster & The Sea',
            'Segue 2nd Edition: Lim Keng – Breath of Lines',
            'FINNA Art of The Year 2025'
        ], JSON_UNESCAPED_UNICODE),
        'latitude' => -7.2792000,
        'longitude' => 112.6693000,
        'is_verified_coord' => 1,
        'website' => 'https://orasisartspace.com',
        'instagram' => '@orasisartspace',
        'image' => 'https://images.unsplash.com/photo-1577720643272-265f09367456?auto=format&fit=crop&w=800&q=80',
        'sources' => 'Katalog Pameran Orasis Art Space, Arsip Kuratorial Biennale Jatim X'
    ],
    [
        'slug' => 'hadi-art-platform',
        'name' => 'HaDi Art Platform',
        'alt_name' => 'HadiArtPlatform Contemporary Art Gallery / Hadi Gallery',
        'address' => 'Jl. Darmokali No. 16, Keputran, Tegalsari, Surabaya',
        'category' => 'Galeri Seni Kontemporer',
        'status' => 'Aktif',
        'founder_or_owner' => 'Irawan Hadikusumo',
        'established_year' => '2018 (14 Juli 2018)',
        'history' => 'Berkembang dari Hadi Gallery yang didirikan oleh Irawan Hadikusumo pada 14 Juli 2018. Kini beroperasi di Jl. Darmokali No. 16 Surabaya sebagai galeri seni kontemporer dan platform diskusi serta jejaring seni lintas kota.',
        'focus' => 'Seni rupa kontemporer Indonesia, platform seni, diskusi dan jaringan seni',
        'events' => json_encode([
            'Pameran Seni Kontemporer HaDi Art',
            'Forum Kuratorial Seni Rupa Surabaya',
            'Presentasi Karya & Dialog Seniman Muda'
        ], JSON_UNESCAPED_UNICODE),
        'latitude' => -7.2927865,
        'longitude' => 112.7419552,
        'is_verified_coord' => 1,
        'website' => 'Belum ditemukan',
        'instagram' => 'Belum ditemukan',
        'image' => 'https://images.unsplash.com/photo-1536924940846-227afb31e2a5?auto=format&fit=crop&w=800&q=80',
        'sources' => 'Dokumentasi HaDi Art Platform, Arsip Kuratorial Surabaya'
    ],
    [
        'slug' => 'visma-art-gallery',
        'name' => 'Visma Art Gallery',
        'alt_name' => 'Visma Gallery',
        'address' => 'Jl. Tegalsari No. 35–37, Tegalsari, Surabaya',
        'category' => 'Galeri Seni',
        'status' => 'Aktif',
        'founder_or_owner' => 'Irawan Hadikusumo (Co-founder)',
        'established_year' => '2015',
        'history' => 'Didirikan pada tahun 2015 di kawasan bersejarah Tegalsari Surabaya dengan keterlibatan Irawan Hadikusumo sebagai co-founder. Menjadi galeri seni independen yang aktif menyelenggarakan pameran lukisan, fotografi, workshop, dan kolaborasi kebudayaan internasional.',
        'focus' => 'Seni rupa, lukisan, fotografi, workshop dan kolaborasi kebudayaan',
        'events' => json_encode([
            'Pameran karya Jacques Ferrier bersama Institut Français Indonesia (IFI) Surabaya',
            'Pameran Fotografi & Seni Visual Tegalsari',
            'Workshop Seni & Kolaborasi Lintas Disiplin'
        ], JSON_UNESCAPED_UNICODE),
        'latitude' => -7.2763435,
        'longitude' => 112.7337319,
        'is_verified_coord' => 1,
        'website' => 'Belum ditemukan',
        'instagram' => 'Belum ditemukan',
        'image' => 'https://images.unsplash.com/photo-1518998053901-5348d3961a04?auto=format&fit=crop&w=800&q=80',
        'sources' => 'Institut Français Indonesia (IFI) Surabaya, Arsip Visma Art Gallery'
    ],
    [
        'slug' => 'uycc-art-gallery',
        'name' => 'UYCC Art Gallery',
        'alt_name' => 'Unicorn Young Collector Club',
        'address' => 'The WIN Hotel, Jl. Embong Tanjung No. 46–48 Lantai 2, Genteng, Surabaya',
        'category' => 'Galeri Seni',
        'status' => 'Aktif',
        'founder_or_owner' => 'Aldridge Tjiptarahardja',
        'established_year' => '2023 (Maret 2023)',
        'history' => 'Dibuka resmi pada Maret 2023 di The WIN Hotel lantai 2 Surabaya sebagai pengembangan dari ekosistem kreatif Unicorn Creative Space. Ditujukan sebagai wadah segar bagi apresiasi seni rupa Indonesia dan para kolektor muda.',
        'focus' => 'Seni rupa Indonesia dan ruang bagi kolektor muda',
        'events' => json_encode([
            'Continuity',
            'Picturesque: Mixed Feeling Provoking Art Exhibition',
            'From the River to the Liberty: Experience Art for Human Rights'
        ], JSON_UNESCAPED_UNICODE),
        'latitude' => -7.2648000,
        'longitude' => 112.7384000,
        'is_verified_coord' => 1,
        'website' => 'Belum ditemukan',
        'instagram' => '@uycc.artgallery',
        'image' => 'https://images.unsplash.com/photo-1545989253-02cc26577f88?auto=format&fit=crop&w=800&q=80',
        'sources' => 'Katalog UYCC Art Gallery, Dokumentasi Media Seni Visual'
    ],
    [
        'slug' => 'unicorn-creative-space',
        'name' => 'Unicorn Creative Space',
        'alt_name' => 'Unicorn Extension',
        'address' => 'Kawasan Rungkut & Unicorn Extension, Jl. Dharma Husada Indah Utara No. 41, Mulyorejo, Surabaya',
        'category' => 'Art Space',
        'status' => 'Aktif',
        'founder_or_owner' => 'Aldridge Tjiptarahardja',
        'established_year' => '2020',
        'history' => 'Didirikan pada tahun 2020 oleh Aldridge Tjiptarahardja. Bermula di kawasan Rungkut dan berekspansi ke Dharma Husada Indah Utara No. 41 Surabaya sebagai hub kreatif komunitas seni, workshop, dan pameran berkala.',
        'focus' => 'Seni, creative space, workshop dan kegiatan kreatif',
        'events' => json_encode([
            'Pameran seni rupa',
            'Pameran fotografi',
            'Workshop batik',
            'Workshop ecoprint',
            'Workshop lilin aroma',
            'Workshop suminagashi',
            'Piknik Seni',
            'From the River to the Liberty: Experience Art for Human Rights'
        ], JSON_UNESCAPED_UNICODE),
        'latitude' => -7.2700574,
        'longitude' => 112.7755609,
        'is_verified_coord' => 1,
        'website' => 'Belum ditemukan',
        'instagram' => '@unicorncreativespace',
        'image' => 'https://images.unsplash.com/photo-1501084817091-a4f3d1d19e07?auto=format&fit=crop&w=800&q=80',
        'sources' => 'Dokumentasi Program Unicorn Creative Space'
    ],
    [
        'slug' => 'galeri-prabangkara',
        'name' => 'Galeri Prabangkara',
        'alt_name' => 'Galeri Prabangkara UPT Taman Budaya Jatim',
        'address' => 'Jl. Genteng Kali No. 85, Genteng, Surabaya',
        'category' => 'Galeri Institusi/Pemerintah',
        'status' => 'Aktif',
        'founder_or_owner' => 'UPT Taman Budaya Jawa Timur / Disbudpar Jatim',
        'established_year' => '2015 (10 Februari 2015)',
        'history' => 'Diresmikan pada 10 Februari 2015 di kompleks Taman Budaya Jawa Timur. Dikelola oleh UPT Taman Budaya Dinas Kebudayaan & Pariwisata Provinsi Jawa Timur sebagai ruang pameran seni rupa resmi pemerintah bagi seniman lokal, daerah, dan nasional.',
        'focus' => 'Seni rupa dan pameran kebudayaan',
        'events' => json_encode([
            'Ada dan Tiada',
            'Alaras Terang',
            'Garis Gathuk',
            'Let\'s Imagine the Future Together',
            'Dari Gosari ke Kasongan'
        ], JSON_UNESCAPED_UNICODE),
        'latitude' => -7.2647000,
        'longitude' => 112.7435000,
        'is_verified_coord' => 1,
        'website' => 'https://tamanbudayajatim.com',
        'instagram' => '@tamanbudayajatim',
        'image' => 'https://images.unsplash.com/photo-1579783900882-c0d3dad7b119?auto=format&fit=crop&w=800&q=80',
        'sources' => 'UPT Taman Budaya Dinas Kebudayaan & Pariwisata Prov. Jawa Timur'
    ],
    [
        'slug' => 'vin-autism-gallery',
        'name' => 'Vin Autism Gallery',
        'alt_name' => 'Vincent Prijadi Art Gallery',
        'address' => 'G-Walk Junction TL 6/11, Citraland, Sambikerep, Surabaya',
        'category' => 'Galeri Seni Khusus',
        'status' => 'Aktif',
        'founder_or_owner' => 'Vincent Prijadi Purwono dan keluarga',
        'established_year' => 'Belum ditemukan',
        'history' => 'Didirikan oleh keluarga perupa Vincent Prijadi Purwono di kawasan G-Walk Citraland Surabaya Barat. Galeri ini mewadahi karya seni visual anak berkebutuhan khusus dan memberikan edukasi seni inklusif yang menginspirasi publik.',
        'focus' => 'Lukisan, seni visual dan kegiatan seni untuk anak berkebutuhan khusus',
        'events' => json_encode([
            'Should We Slow Down',
            'Pameran lukisan Vincent di Stasiun Gubeng',
            'Berbagai workshop seni inklusif'
        ], JSON_UNESCAPED_UNICODE),
        'latitude' => -7.2878805,
        'longitude' => 112.6554319,
        'is_verified_coord' => 1,
        'website' => 'Belum ditemukan',
        'instagram' => '@vinautismgallery',
        'image' => 'https://images.unsplash.com/photo-1547826039-bfc35e0f1ea8?auto=format&fit=crop&w=800&q=80',
        'sources' => 'Dokumentasi Vin Autism Gallery, Liputan Media Seni Surabaya'
    ],
    [
        'slug' => 'teh-villa-gallery',
        'name' => 'Teh Villa Gallery',
        'alt_name' => 'Galeri Seni Teh Villa',
        'address' => 'Jl. Raya Rungkut Industri II No. 53, Rungkut, Surabaya',
        'category' => 'Galeri Seni',
        'status' => 'Aktif',
        'founder_or_owner' => 'Teh Villa Indonesia / PT Karya Mas Makmur',
        'established_year' => '1981 (sejarah bisnis) / 2020',
        'history' => 'Berkaitan erat dengan sejarah bisnis keluarga produsen Teh Villa (PT Karya Mas Makmur) yang berdiri di Surabaya sejak 1981. Menghadirkan ruang galeri seni rupa representatif di kawasan Rungkut Industri untuk mengangkat karya seni rupa dan kegiatan kemanusiaan.',
        'focus' => 'Seni lukis, ruang pameran, dan kegiatan seni sosial kemanusiaan',
        'events' => json_encode([
            'Living in Harmony',
            'Finding Balance',
            'Art for Humanity',
            'Unexpected Reunion'
        ], JSON_UNESCAPED_UNICODE),
        'latitude' => -7.3282000,
        'longitude' => 112.7756000,
        'is_verified_coord' => 1,
        'website' => 'https://tehvilla.com',
        'instagram' => '@tehvillagallery',
        'image' => 'https://images.unsplash.com/photo-1579783902614-a3fb3927b675?auto=format&fit=crop&w=800&q=80',
        'sources' => 'Arsip Pameran Teh Villa Gallery, PT Karya Mas Makmur'
    ],
    [
        'slug' => 'samata-house',
        'name' => 'Samata House',
        'alt_name' => 'Samata House Coffee & Art',
        'address' => 'Jl. Lingga No. 6, Gubeng, Surabaya',
        'category' => 'Art Space',
        'status' => 'Aktif',
        'founder_or_owner' => 'Belum ditemukan',
        'established_year' => 'Belum ditemukan',
        'history' => 'Ruang alternatif di kawasan Gubeng yang menggabungkan coffee house, ruang kreatif, dan galeri pameran bagi seniman muda independen. Dikenal aktif mengadakan pameran seni rupa, pasar seni, dan diskusi kebudayaan.',
        'focus' => 'Ruang kreatif, coffee house dan ruang pamer',
        'events' => json_encode([
            'TAKE OVER',
            'Berbagai pameran seniman muda',
            'Jatim Biennale XI – Kelana',
            'Berbagai kegiatan seni dan kreatif'
        ], JSON_UNESCAPED_UNICODE),
        'latitude' => -7.2699509,
        'longitude' => 112.7512636,
        'is_verified_coord' => 1,
        'website' => 'Belum ditemukan',
        'instagram' => '@samatahouse',
        'image' => 'https://images.unsplash.com/photo-1513519245088-0e12902e5a38?auto=format&fit=crop&w=800&q=80',
        'sources' => 'Dokumentasi Samata House, Jatim Biennale XI'
    ],
    [
        'slug' => 'c2o-library-collabtive',
        'name' => 'C2O Library & Collabtive',
        'alt_name' => 'C2O Library',
        'address' => 'Jl. Dr. Cipto No. 22, Dr. Soetomo, Tegalsari, Surabaya',
        'category' => 'Creative Space',
        'status' => 'Aktif',
        'founder_or_owner' => 'PERINTIS',
        'established_year' => '2008',
        'history' => 'Didirikan pada tahun 2008 oleh organisasi nirlaba PERINTIS. C2O adalah perpustakaan independen, ruang kerja kolaboratif, serta pusat pameran budaya, literasi, film, desain, dan kegiatan kreatif komunitas warga.',
        'focus' => 'Seni, literasi, diskusi, film, desain dan kegiatan kreatif',
        'events' => json_encode([
            'Codex Code Art Exhibition',
            'Post a Place',
            'Surabaya AnimNation Festival',
            'Eat Play Laugh',
            'Design It Yourself (DIY) Fair',
            'Berbagai diskusi, workshop dan pemutaran film'
        ], JSON_UNESCAPED_UNICODE),
        'latitude' => -7.2812044,
        'longitude' => 112.7388612,
        'is_verified_coord' => 1,
        'website' => 'https://c2o-library.net',
        'instagram' => '@c2olibrary',
        'image' => 'https://images.unsplash.com/photo-1521587760476-6c12a4b040da?auto=format&fit=crop&w=800&q=80',
        'sources' => 'Kolektif C2O Library, Arsip Festival Seni Surabaya'
    ],
    [
        'slug' => 'rahasia-art-space',
        'name' => 'Rahasia Art Space',
        'alt_name' => 'Rahasia Studio',
        'address' => 'Perum YKP I, Jl. Pandugo Timur I No. 22, Penjaringan Sari, Rungkut, Surabaya',
        'category' => 'Art Space',
        'status' => 'Aktif',
        'founder_or_owner' => 'Pak Thomas',
        'established_year' => 'sekitar 2019',
        'history' => 'Didirikan sekitar tahun 2019 oleh Pak Thomas di perumahan YKP I Rungkut sebagai ruang kreasi seni independen, studio pameran, dan workshop kerajinan bagi komunitas seni lokal.',
        'focus' => 'Art space dan kegiatan kreatif',
        'events' => json_encode([
            'Pameran Seni Komunitas Rungkut',
            'Workshop Kerajinan & Lukis Kreatif',
            'Open Studio & Diskusi Seni'
        ], JSON_UNESCAPED_UNICODE),
        'latitude' => -7.3181470,
        'longitude' => 112.7900883,
        'is_verified_coord' => 1,
        'website' => 'Belum ditemukan',
        'instagram' => 'Belum ditemukan',
        'image' => 'https://images.unsplash.com/photo-1460661419201-fd4cecdf8a8b?auto=format&fit=crop&w=800&q=80',
        'sources' => 'Dokumentasi Komunitas Seni Rungkut'
    ],
    [
        'slug' => 'galeri-merah-putih',
        'name' => 'Galeri Merah Putih',
        'alt_name' => 'Belum ditemukan',
        'address' => 'Kawasan Balai Pemuda / Embong Kaliasin, Genteng, Surabaya',
        'category' => 'Galeri Seni',
        'status' => 'Perlu diverifikasi',
        'founder_or_owner' => 'Belum ditemukan',
        'established_year' => 'Belum ditemukan',
        'history' => 'Galeri seni rupa yang tercatat berada di sekitar kawasan Balai Pemuda / Embong Kaliasin Genteng Surabaya. Sejarah pendirian, pengelola, dan catatan kuratorial pamerannya perlu diverifikasi lebih lanjut.',
        'focus' => 'Seni rupa dan pameran seni lukis',
        'events' => json_encode([
            'Pameran Lukisan Merah Putih',
            'Perlu diverifikasi lebih lanjut'
        ], JSON_UNESCAPED_UNICODE),
        'latitude' => -7.2639219,
        'longitude' => 112.7452930,
        'is_verified_coord' => 0,
        'website' => 'Belum ditemukan',
        'instagram' => 'Belum ditemukan',
        'image' => 'https://images.unsplash.com/photo-1541961017774-22349e4a1262?auto=format&fit=crop&w=800&q=80',
        'sources' => 'Arsip Kesenian Kota Surabaya, Perlu verifikasi lebih lanjut'
    ],
    [
        'slug' => 'galeri-mojopahit',
        'name' => 'Galeri Mojopahit',
        'alt_name' => 'Belum ditemukan',
        'address' => 'Jl. Darmokali No. 61A, Darmo, Wonokromo, Surabaya',
        'category' => 'Galeri Seni',
        'status' => 'Perlu diverifikasi',
        'founder_or_owner' => 'Belum ditemukan',
        'established_year' => 'Belum ditemukan',
        'history' => 'Galeri seni rupa yang beralamat di Jl. Darmokali No. 61A Wonokromo Surabaya. Data historis, pendiri, dan arsip pamerannya masih memerlukan verifikasi lapangan lebih lanjut.',
        'focus' => 'Seni rupa dan karya visual',
        'events' => json_encode([
            'Pameran Seni Lukis Koleksi',
            'Perlu diverifikasi lebih lanjut'
        ], JSON_UNESCAPED_UNICODE),
        'latitude' => -7.2927865,
        'longitude' => 112.7419552,
        'is_verified_coord' => 0,
        'website' => 'Belum ditemukan',
        'instagram' => 'Belum ditemukan',
        'image' => 'https://images.unsplash.com/photo-1582561424760-0321d75e81fa?auto=format&fit=crop&w=800&q=80',
        'sources' => 'Direktori Galeri Seni Surabaya, Perlu verifikasi'
    ],
    [
        'slug' => 'studio-lima-gallery',
        'name' => 'Studio Lima Gallery',
        'alt_name' => 'Belum ditemukan',
        'address' => 'Jl. Medokan Asri Utara VII No. 28, Medokan Ayu, Rungkut, Surabaya',
        'category' => 'Galeri Seni',
        'status' => 'Perlu diverifikasi',
        'founder_or_owner' => 'Belum ditemukan',
        'established_year' => 'Belum ditemukan',
        'history' => 'Studio dan ruang galeri seni rupa mandiri di Medokan Ayu Surabaya Timur. Sejarah pendirian, pengelola, dan kurasi pamerannya memerlukan verifikasi lebih lanjut.',
        'focus' => 'Seni rupa, lukisan, dan studio seni',
        'events' => json_encode([
            'Pameran Studio Seni Lokal',
            'Perlu diverifikasi lebih lanjut'
        ], JSON_UNESCAPED_UNICODE),
        'latitude' => -7.3225397,
        'longitude' => 112.7942222,
        'is_verified_coord' => 0,
        'website' => 'Belum ditemukan',
        'instagram' => 'Belum ditemukan',
        'image' => 'https://images.unsplash.com/photo-1576769267415-9642000aa3cb?auto=format&fit=crop&w=800&q=80',
        'sources' => 'Direktori Komunitas Seni Rungkut, Perlu verifikasi'
    ],
    [
        'slug' => 'wusto-art-gallery',
        'name' => 'Wusto Art Gallery',
        'alt_name' => 'Wusto Galeri Seni & Kaligrafi',
        'address' => 'Royal Plaza, Lantai LG Blok AA2-02, Ketintang, Wonokromo, Surabaya',
        'category' => 'Galeri Seni',
        'status' => 'Aktif',
        'founder_or_owner' => 'Belum ditemukan',
        'established_year' => 'Belum ditemukan',
        'history' => 'Galeri seni dan kerajinan komersial di Royal Plaza Surabaya lantai LG Blok AA2-02, menyajikan karya lukisan lanskap, figuratif, kaligrafi, dan bingkai seni berkualitas.',
        'focus' => 'Lukisan, kaligrafi, dan karya seni dekoratif',
        'events' => json_encode([
            'Bursa Lukisan Royal Plaza',
            'Display Karya Seni Rutin'
        ], JSON_UNESCAPED_UNICODE),
        'latitude' => -7.3091349,
        'longitude' => 112.7342835,
        'is_verified_coord' => 1,
        'website' => 'Belum ditemukan',
        'instagram' => 'Belum ditemukan',
        'image' => 'https://images.unsplash.com/photo-1513364776144-60967b0f800f?auto=format&fit=crop&w=800&q=80',
        'sources' => 'Direktori Tenant Royal Plaza Surabaya'
    ],
    [
        'slug' => 'utama-slamet-art-gallery',
        'name' => 'Utama Slamet Art Gallery',
        'alt_name' => 'Galeri Utama Slamet',
        'address' => 'Jl. Pahlawan No. 54 C-D, Alun-alun Contong, Bubutan, Surabaya',
        'category' => 'Galeri Seni',
        'status' => 'Aktif',
        'founder_or_owner' => 'Belum ditemukan',
        'established_year' => 'Belum ditemukan',
        'history' => 'Galeri seni lukis dan pembuatan bingkai seni klasik di jalur bersejarah Jl. Pahlawan dekat Tugu Pahlawan Surabaya. Menyediakan aneka lukisan klasik dan karya seni seniman lokal.',
        'focus' => 'Seni rupa, lukisan lanskap & figuratif, custom framing',
        'events' => json_encode([
            'Pameran Lukisan Klasik Jawa Timur',
            'Bursa Seni Rupa Surabaya'
        ], JSON_UNESCAPED_UNICODE),
        'latitude' => -7.2452377,
        'longitude' => 112.7385975,
        'is_verified_coord' => 1,
        'website' => 'Belum ditemukan',
        'instagram' => 'Belum ditemukan',
        'image' => 'https://images.unsplash.com/photo-1579783900882-c0d3dad7b119?auto=format&fit=crop&w=800&q=80',
        'sources' => 'Direktori Seni & Budaya Kota Surabaya'
    ],
    [
        'slug' => 'unsur-site',
        'name' => 'Unsur Site',
        'alt_name' => 'Unsur Art Space',
        'address' => 'Jl. Untung Suropati No. 85, DR. Soetomo, Tegalsari, Surabaya',
        'category' => 'Art Space',
        'status' => 'Aktif',
        'founder_or_owner' => 'Belum ditemukan',
        'established_year' => 'Belum ditemukan',
        'history' => 'Ruang seni independen dan proyek seni kolaboratif di Jl. Untung Suropati No. 85 Surabaya. Menjadi arena presentasi ide baru dan diskusi seni rupa visual kontemporer.',
        'focus' => 'Eksperimentasi seni, instalasi, dan ruang seni alternatif',
        'events' => json_encode([
            'Pameran Kolaborasi Visual Unsur',
            'Sesi Presentasi Karya Seniman Muda',
            'Diskusi Seni Kontemporer'
        ], JSON_UNESCAPED_UNICODE),
        'latitude' => -7.2819427,
        'longitude' => 112.7348337,
        'is_verified_coord' => 1,
        'website' => 'Belum ditemukan',
        'instagram' => '@unsur.site',
        'image' => 'https://images.unsplash.com/photo-1499343162172-9b73b4abb8a5?auto=format&fit=crop&w=800&q=80',
        'sources' => 'Dokumentasi Program Seni Unsur Site'
    ],
    [
        'slug' => 'filadelvia',
        'name' => 'Filadelvia',
        'alt_name' => 'Filadelvia Art Gallery',
        'address' => 'Ruko Taman Puspa Raya Blok D No. 09, Sambikerep, Surabaya',
        'category' => 'Galeri Seni',
        'status' => 'Aktif',
        'founder_or_owner' => 'Belum ditemukan',
        'established_year' => 'Belum ditemukan',
        'history' => 'Galeri seni dan workshop lukisan di kawasan ruko Taman Puspa Raya CitraLand Surabaya Barat yang menyediakan karya seni lukis dan bingkai artistik.',
        'focus' => 'Seni lukis, karya seni visual, dan workshop melukis',
        'events' => json_encode([
            'Display Lukisan Kontemporer',
            'Workshop Melukis Santai'
        ], JSON_UNESCAPED_UNICODE),
        'latitude' => -7.2762729,
        'longitude' => 112.6446587,
        'is_verified_coord' => 1,
        'website' => 'Belum ditemukan',
        'instagram' => 'Belum ditemukan',
        'image' => 'https://images.unsplash.com/photo-1578632767115-351597cf2477?auto=format&fit=crop&w=800&q=80',
        'sources' => 'Direktori Usaha Seni Sambikerep Surabaya'
    ],
    [
        'slug' => 'art-center-bg-junction',
        'name' => 'Art Center BG Junction',
        'alt_name' => 'Pusat Seni BG Junction',
        'address' => 'BG Junction Mall, Lantai L2, Jl. Bubutan No. 1–7, Bubutan, Surabaya',
        'category' => 'Ruang Budaya',
        'status' => 'Aktif',
        'founder_or_owner' => 'Manajemen BG Junction Surabaya',
        'established_year' => 'Belum ditemukan',
        'history' => 'Pusat kegiatan seni publik di mal BG Junction Bubutan Surabaya. Sering dipakai untuk perhelatan festival seni budaya, pagelaran kesenian daerah, dan pameran seni rupa warga kota.',
        'focus' => 'Pameran seni publik, festival budaya, dan apresiasi karya masyarakat',
        'events' => json_encode([
            'Festival Seni Budaya Surabaya di BG Junction',
            'Pameran Lukisan & Karya Kerajinan Publik'
        ], JSON_UNESCAPED_UNICODE),
        'latitude' => -7.2549143,
        'longitude' => 112.7334286,
        'is_verified_coord' => 1,
        'website' => 'https://bgjunction.com',
        'instagram' => '@bgjunctionsurabaya',
        'image' => 'https://images.unsplash.com/photo-1508997449629-303059a039c0?auto=format&fit=crop&w=800&q=80',
        'sources' => 'Arsip Acara BG Junction, Disbudporapar Surabaya'
    ],
    [
        'slug' => 'kampoeng-seni-bg-junction',
        'name' => 'Kampoeng Seni',
        'alt_name' => 'Kampoeng Seni BG Junction',
        'address' => 'BG Junction Mall, Lantai L2, Jl. Bubutan No. 1–7, Bubutan, Surabaya',
        'category' => 'Galeri Seni Tradisional',
        'status' => 'Aktif',
        'founder_or_owner' => 'Paguyuban Seniman & Pengrajin Surabaya',
        'established_year' => 'Belum ditemukan',
        'history' => 'Komunitas sentra seniman lukis dan pengrajin tradisional yang berhimpun di lantai L2 mal BG Junction Surabaya. Pengunjung dapat melihat proses seniman melukis sketsa wajah, karikatur, serta karya kriya secara langsung.',
        'focus' => 'Seni rupa tradisional, lukisan potret langsung, ukiran dan kerajinan tangan',
        'events' => json_encode([
            'Bursa Lukisan On The Spot Kampoeng Seni',
            'Demo Melukis Sketsa Wajah & Karikatur',
            'Pameran Kerajinan Tradisional Jatim'
        ], JSON_UNESCAPED_UNICODE),
        'latitude' => -7.2552000,
        'longitude' => 112.7339000,
        'is_verified_coord' => 1,
        'website' => 'Belum ditemukan',
        'instagram' => 'Belum ditemukan',
        'image' => 'https://images.unsplash.com/photo-1513364776144-60967b0f800f?auto=format&fit=crop&w=800&q=80',
        'sources' => 'Komunitas Seniman Kampoeng Seni BG Junction'
    ],
    [
        'slug' => 'sakura-warangan-keris',
        'name' => 'Sakura Warangan Keris',
        'alt_name' => 'Sanggar Tosan Aji Sakura',
        'address' => 'Jl. Pakis Tirtosari XA No. 26, Sawahan, Surabaya',
        'category' => 'Galeri Seni Tradisional',
        'status' => 'Aktif',
        'founder_or_owner' => 'Belum ditemukan',
        'established_year' => 'Belum ditemukan',
        'history' => 'Galeri pusaka dan sanggar konservasi tosan aji tradisional di kawasan Sawahan Surabaya. Mengedukasi masyarakat mengenai filosofi keris Nusantara, proses perawatan warangan, dan pelestarian benda budaya.',
        'focus' => 'Keris, tosan aji, warangan, dan benda seni tradisional Nusantara',
        'events' => json_encode([
            'Edukasi Budaya Tosan Aji & Jamasan Keris',
            'Pameran Koleksi Keris Kuno Nusantara',
            'Workshop Konservasi Benda Pusaka Tradisional'
        ], JSON_UNESCAPED_UNICODE),
        'latitude' => -7.2910403,
        'longitude' => 112.7210246,
        'is_verified_coord' => 1,
        'website' => 'Belum ditemukan',
        'instagram' => 'Belum ditemukan',
        'image' => 'https://images.unsplash.com/photo-1582561424760-0321d75e81fa?auto=format&fit=crop&w=800&q=80',
        'sources' => 'Paguyuban Pecinta Tosan Aji Surabaya'
    ],
    [
        'slug' => 'cai-cai-company',
        'name' => 'Cai-Cai Company',
        'alt_name' => 'Cai-Cai Design & Caricature',
        'address' => 'Kawasan Villa Kalijudan Indah, Kalijudan, Mulyorejo, Surabaya',
        'category' => 'Creative Space',
        'status' => 'Aktif',
        'founder_or_owner' => 'Belum ditemukan',
        'established_year' => 'Belum ditemukan',
        'history' => 'Studio kreasi seni dan desain di kawasan Kalijudan Indah Surabaya Timur, menghadirkan workshop dan pameran karya visual bergenre karikatur dan desain grafis.',
        'focus' => 'Desain, ilustrasi, dan seni karikatur',
        'events' => json_encode([
            'Pameran Karikatur & Seni Ilustrasi',
            'Workshop Menggambar Karikatur Urban'
        ], JSON_UNESCAPED_UNICODE),
        'latitude' => -7.2605867,
        'longitude' => 112.7785473,
        'is_verified_coord' => 1,
        'website' => 'Belum ditemukan',
        'instagram' => 'Belum ditemukan',
        'image' => 'https://images.unsplash.com/photo-1541961017774-22349e4a1262?auto=format&fit=crop&w=800&q=80',
        'sources' => 'Direktori Studio Desain Surabaya'
    ],
    [
        'slug' => 'galeri-dks-balai-pemuda',
        'name' => 'Galeri DKS / Galeri Surabaya',
        'alt_name' => 'Balai Pemuda Art Space / Galeri Dewan Kesenian Surabaya',
        'address' => 'Kawasan Balai Pemuda, Jl. Gubernur Suryo No. 15, Embong Kaliasin, Genteng, Surabaya',
        'category' => 'Galeri Institusi/Pemerintah',
        'status' => 'Aktif',
        'founder_or_owner' => 'Dewan Kesenian Surabaya (DKS) / Pemkot Surabaya',
        'established_year' => '1971 (DKS 10 Oktober 1971) / awal 1980-an (galeri)',
        'history' => 'Berkaitan erat dengan Dewan Kesenian Surabaya (DKS) yang berdiri pada 10 Oktober 1971 dan pengembangan ruang galeri pameran pada awal 1980-an di kompleks cagar budaya Balai Pemuda. Menjadi episentrum bersejarah pergerakan seniman Surabaya dari masa ke masa.',
        'focus' => 'Seni rupa dan kegiatan kebudayaan multidisiplin',
        'events' => json_encode([
            'Pameran Seni Rupa Tahunan DKS',
            'Surabaya Art Week',
            'Pementasan Teater & Tari Tradisional Balai Pemuda',
            'Diskusi Sastra & Pemutaran Film Independen'
        ], JSON_UNESCAPED_UNICODE),
        'latitude' => -7.2645158,
        'longitude' => 112.7455144,
        'is_verified_coord' => 1,
        'website' => 'https://surabaya.go.id',
        'instagram' => '@dewankeseniansurabaya',
        'image' => 'https://images.unsplash.com/photo-1544816155-12df9643f363?auto=format&fit=crop&w=800&q=80',
        'sources' => 'Dewan Kesenian Surabaya, Dinas Kebudayaan & Pariwisata Kota Surabaya'
    ],
    [
        'slug' => 'emmitan-fine-art-gallery',
        'name' => 'Emmitan Fine Art Gallery',
        'alt_name' => 'Emmitan Gallery',
        'address' => 'Jl. Walikota Mustajab, Ketabang, Genteng, Surabaya',
        'category' => 'Galeri Historis',
        'status' => 'Historis/Tidak aktif',
        'founder_or_owner' => 'Belum ditemukan',
        'established_year' => 'Belum ditemukan',
        'history' => 'Galeri seni rupa legendaris yang tercatat pernah aktif beroperasi di Jl. Walikota Mustajab Surabaya. Dikenal dalam sejarah seni rupa Jawa Timur sebagai tempat pameran prestisius karya master seniman nasional dan ruang diskusi kesenian Jawa Timur.',
        'focus' => 'Seni rupa murni, seni lukis maestro, dan diskursus seni',
        'events' => json_encode([
            'Pameran Seniman Nasional & Jawa Timur',
            'Retrospeksi Seni Lukis Modern',
            'Diskusi Kesenian & Temu Kurator'
        ], JSON_UNESCAPED_UNICODE),
        'latitude' => -7.2607032,
        'longitude' => 112.7467381,
        'is_verified_coord' => 1,
        'website' => 'Belum ditemukan',
        'instagram' => 'Belum ditemukan',
        'image' => 'https://images.unsplash.com/photo-1579783900882-c0d3dad7b119?auto=format&fit=crop&w=800&q=80',
        'sources' => 'Arsip Sejarah Seni Rupa Surabaya, Katalog Seni Rupa Indonesia'
    ],
    [
        'slug' => 'gracia-art-gallery',
        'name' => 'Gracia Art Gallery',
        'alt_name' => 'Gracia Gallery',
        'address' => 'Kawasan Bukit Darmo Golf, Pradah Kalikendal, Dukuh Pakis, Surabaya',
        'category' => 'Galeri Historis',
        'status' => 'Historis/Tidak aktif',
        'founder_or_owner' => 'Belum ditemukan',
        'established_year' => 'Belum ditemukan',
        'history' => 'Galeri seni prestisius yang pernah tercatat berlokasi di kawasan Bukit Darmo Golf Surabaya Barat. Menjadi ruang pameran penting bagi karya seni rupa seniman Surabaya serta pelukis dari berbagai kota di luar Jawa Timur.',
        'focus' => 'Seni rupa, pameran lukisan koleksi, dan apresiasi seni',
        'events' => json_encode([
            'Pameran Seniman Surabaya & Luar Kota',
            'Pameran Lukisan Masterpiece Koleksi'
        ], JSON_UNESCAPED_UNICODE),
        'latitude' => -7.2891865,
        'longitude' => 112.6874017,
        'is_verified_coord' => 1,
        'website' => 'Belum ditemukan',
        'instagram' => 'Belum ditemukan',
        'image' => 'https://images.unsplash.com/photo-1578632767115-351597cf2477?auto=format&fit=crop&w=800&q=80',
        'sources' => 'Arsip Galeri Seni Surabaya Barat, Catatan Kuratorial Jawa Timur'
    ],
    [
        'slug' => 'its-cultural-center',
        'name' => 'ITS Cultural Center',
        'alt_name' => 'Pusat Kebudayaan ITS',
        'address' => 'Institut Teknologi Sepuluh Nopember, Keputih, Sukolilo, Surabaya',
        'category' => 'Ruang Budaya',
        'status' => 'Aktif',
        'founder_or_owner' => 'Institut Teknologi Sepuluh Nopember (ITS)',
        'established_year' => 'Belum ditemukan',
        'history' => 'Pusat kegiatan kebudayaan dan ruang apresiasi seni di lingkungan Institut Teknologi Sepuluh Nopember (ITS) Surabaya. Bukan galeri komersial murni, melainkan ruang budaya akademik untuk seni pertunjukan, diskusi dan kegiatan kebudayaan civitas akademika dan publik.',
        'focus' => 'Seni pertunjukan, diskusi dan kegiatan kebudayaan',
        'events' => json_encode([
            'Pagelaran Seni & Budaya Nusantara ITS',
            'Festival Tari & Musik Mahasiswa',
            'Diskusi Kebudayaan & Sains-Seni'
        ], JSON_UNESCAPED_UNICODE),
        'latitude' => -7.2826954,
        'longitude' => 112.7956782,
        'is_verified_coord' => 1,
        'website' => 'https://www.its.ac.id',
        'instagram' => '@itsculturalcenter',
        'image' => 'https://images.unsplash.com/photo-1511671782779-c97d3d27a1d4?auto=format&fit=crop&w=800&q=80',
        'sources' => 'Direktorat Kemahasiswaan & Kebudayaan ITS Surabaya'
    ],
    [
        'slug' => 'galeri-ajbs',
        'name' => 'Galeri AJBS',
        'alt_name' => 'AJBS Art Gallery',
        'address' => 'Jl. Raya Dukuh Kupang No. 110, Sawahan, Surabaya',
        'category' => 'Galeri Seni',
        'status' => 'Aktif',
        'founder_or_owner' => 'Agus Joko Budi Santoso',
        'established_year' => '2010',
        'history' => 'Didirikan pada tahun 2010 oleh kolektor seni Agus Joko Budi Santoso di kompleks AJBS Surabaya. Menjadi galeri seni terpadu yang aktif mewadahi seniman Jawa Timur dan Nusantara untuk menggelar pameran seni rupa bersama dan workshop seni rupa.',
        'focus' => 'Seni rupa Jawa Timur & Nusantara, pameran bersama, dan workshop melukis',
        'events' => json_encode([
            'Pameran Seni Rupa Nusantara 2020',
            'Workshop Melukis Akrilis Bersama 2022',
            'Pameran Kolaborasi Seniman Jawa Timur 2023'
        ], JSON_UNESCAPED_UNICODE),
        'latitude' => -7.2931000,
        'longitude' => 112.7147000,
        'is_verified_coord' => 1,
        'website' => 'Belum ditemukan',
        'instagram' => 'Belum ditemukan',
        'image' => 'https://images.unsplash.com/photo-1460661419201-fd4cecdf8a8b?auto=format&fit=crop&w=800&q=80',
        'sources' => 'Dokumentasi Galeri AJBS Surabaya'
    ],
    [
        'slug' => 'house-of-sampoerna',
        'name' => 'House of Sampoerna',
        'alt_name' => 'The Pavillion House of Sampoerna',
        'address' => 'Taman Sampoerna No. 6, Krembangan Utara, Pabean Cantian, Surabaya',
        'category' => 'Ruang Budaya',
        'status' => 'Historis/Tidak aktif',
        'founder_or_owner' => 'Keluarga Sampoerna (Putera Sampoerna Foundation)',
        'established_year' => '2004 (kompleks berdiri 1862)',
        'history' => 'Kompleks cagar budaya bersejarah peninggalan kolonial Belanda yang dibangun pada 1862. Sejak 2004 dikelola oleh Putera Sampoerna Foundation sebagai museum industri tembakau serta galeri seni rupa berkala "The Pavillion" yang menampilkan pameran seni rupa terkemuka Jawa Timur.',
        'focus' => 'Seni rupa kontemporer di The Pavillion, sejarah industri, arsitektur cagar budaya, dan tur sejarah Heritage Walk',
        'events' => json_encode([
            'Pameran Tetap Sejarah Sampoerna & Surabaya',
            'Art Market Bulanan Taman Sampoerna 2022',
            'Heritage Walk & Photography Exhibition 2023',
            'Pameran "Kota Pahlawan dalam Kanvas" 2024'
        ], JSON_UNESCAPED_UNICODE),
        'latitude' => -7.2309923,
        'longitude' => 112.7341461,
        'is_verified_coord' => 1,
        'website' => 'https://houseofsampoerna.museum',
        'instagram' => '@houseofsampoerna',
        'image' => 'https://images.unsplash.com/photo-1578632767115-351597cf2477?auto=format&fit=crop&w=800&q=80',
        'sources' => 'Putera Sampoerna Foundation, Arsip Cagar Budaya Surabaya'
    ]
];

// Helper fungsi untuk decode events jika berupa JSON string
function formatGalleryItem($row) {
    if (isset($row['events']) && is_string($row['events'])) {
        $decoded = json_decode($row['events'], true);
        if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
            $row['events'] = $decoded;
        } else {
            $row['events'] = array_filter(array_map('trim', explode("\n", $row['events'])));
        }
    }
    if (!isset($row['events']) || !is_array($row['events'])) {
        $row['events'] = [];
    }
    if (isset($row['latitude'])) $row['latitude'] = (float)$row['latitude'];
    if (isset($row['longitude'])) $row['longitude'] = (float)$row['longitude'];
    if (isset($row['is_verified_coord'])) $row['is_verified_coord'] = (int)$row['is_verified_coord'];
    return $row;
}

// Inisialisasi Tabel dan Sinkronisasi Data jika terhubung ke MySQL
if (isset($pdo) && $pdo !== null) {
    try {
        $tableExists = $pdo->query("SHOW TABLES LIKE 'galleries'")->rowCount();
        if ($tableExists === 0) {
            $pdo->exec("CREATE TABLE IF NOT EXISTS `galleries` (
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
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
        } else {
            // Migrasi: Tambah kolom is_verified_coord jika belum ada (backward compat)
            $hasCol = $pdo->query("SHOW COLUMNS FROM `galleries` LIKE 'is_verified_coord'")->rowCount();
            if ($hasCol === 0) {
                $pdo->exec("ALTER TABLE `galleries` ADD COLUMN `is_verified_coord` TINYINT(1) DEFAULT 1 AFTER `longitude`");
                $pdo->exec("UPDATE `galleries` SET `is_verified_coord` = 0 WHERE `status` = 'Perlu diverifikasi'");
            }
            // Migrasi: Perbaiki kategori non-standard 'Ruang Seni' -> 'Ruang Budaya'
            $pdo->exec("UPDATE `galleries` SET `category` = 'Ruang Budaya' WHERE `category` = 'Ruang Seni' AND `slug` != 'kampoeng-seni-bg-junction'");
            // Kampoeng Seni seharusnya 'Galeri Seni Tradisional'
            $pdo->exec("UPDATE `galleries` SET `category` = 'Galeri Seni Tradisional' WHERE `slug` = 'kampoeng-seni-bg-junction' AND `category` NOT IN ('Galeri Seni Tradisional')");
        }

        // Cek jumlah data yang tersimpan
        $count = (int)$pdo->query("SELECT COUNT(*) FROM `galleries`")->fetchColumn();
        if ($count < count($DEFAULT_GALLERIES)) {
            $stmt = $pdo->prepare("INSERT INTO `galleries` 
                (`slug`, `name`, `alt_name`, `address`, `category`, `status`, `founder_or_owner`, `established_year`, `history`, `focus`, `events`, `latitude`, `longitude`, `is_verified_coord`, `website`, `instagram`, `image`, `sources`) 
                VALUES 
                (:slug, :name, :alt_name, :address, :category, :status, :founder_or_owner, :established_year, :history, :focus, :events, :latitude, :longitude, :is_verified_coord, :website, :instagram, :image, :sources)
                ON DUPLICATE KEY UPDATE 
                `name` = VALUES(`name`),
                `alt_name` = VALUES(`alt_name`),
                `address` = VALUES(`address`),
                `category` = VALUES(`category`),
                `status` = VALUES(`status`),
                `founder_or_owner` = VALUES(`founder_or_owner`),
                `established_year` = VALUES(`established_year`),
                `history` = VALUES(`history`),
                `focus` = VALUES(`focus`),
                `events` = VALUES(`events`),
                `latitude` = VALUES(`latitude`),
                `longitude` = VALUES(`longitude`),
                `is_verified_coord` = VALUES(`is_verified_coord`),
                `website` = VALUES(`website`),
                `instagram` = VALUES(`instagram`),
                `image` = VALUES(`image`),
                `sources` = VALUES(`sources`)");

            foreach ($DEFAULT_GALLERIES as $g) {
                $stmt->execute([
                    ':slug' => $g['slug'],
                    ':name' => $g['name'],
                    ':alt_name' => $g['alt_name'],
                    ':address' => $g['address'],
                    ':category' => $g['category'],
                    ':status' => $g['status'],
                    ':founder_or_owner' => $g['founder_or_owner'],
                    ':established_year' => $g['established_year'],
                    ':history' => $g['history'],
                    ':focus' => $g['focus'],
                    ':events' => $g['events'],
                    ':latitude' => $g['latitude'],
                    ':longitude' => $g['longitude'],
                    ':is_verified_coord' => $g['is_verified_coord'],
                    ':website' => $g['website'],
                    ':instagram' => $g['instagram'],
                    ':image' => $g['image'],
                    ':sources' => $g['sources']
                ]);
            }
        }
    } catch (Exception $e) {
        // Abaikan error DB agar fallback tetap menyajikan data
    }
}

// Proses Query Request
$catFilter = isset($_GET['category']) ? trim($_GET['category']) : '';
$statusFilter = isset($_GET['status']) ? trim($_GET['status']) : '';
$searchQuery = isset($_GET['q']) ? trim($_GET['q']) : '';
$slugFilter = isset($_GET['slug']) ? trim($_GET['slug']) : '';
$idFilter = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$galleries = [];

if (isset($pdo) && $pdo !== null) {
    try {
        $sql = "SELECT * FROM `galleries` WHERE 1=1";
        $params = [];

        if (!empty($slugFilter)) {
            $sql .= " AND `slug` = :slug";
            $params[':slug'] = $slugFilter;
        } elseif ($idFilter > 0) {
            $sql .= " AND `id` = :id";
            $params[':id'] = $idFilter;
        }

        if (!empty($catFilter) && $catFilter !== 'Semua') {
            $sql .= " AND `category` = :cat";
            $params[':cat'] = $catFilter;
        }

        if (!empty($statusFilter) && $statusFilter !== 'Semua') {
            $sql .= " AND `status` = :status";
            $params[':status'] = $statusFilter;
        }

        if (!empty($searchQuery)) {
            $sql .= " AND (`name` LIKE :q OR `alt_name` LIKE :q OR `address` LIKE :q OR `founder_or_owner` LIKE :q OR `focus` LIKE :q)";
            $params[':q'] = "%$searchQuery%";
        }

        $sql .= " ORDER BY `name` ASC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $raw = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($raw as $item) {
            $galleries[] = formatGalleryItem($item);
        }
    } catch (Exception $e) {
        $galleries = [];
    }
}

// Jika database kosong atau offline, gunakan data fallback dengan pemfilteran in-memory
if (empty($galleries) && empty($slugFilter) && $idFilter === 0) {
    foreach ($DEFAULT_GALLERIES as $item) {
        $match = true;
        if (!empty($catFilter) && $catFilter !== 'Semua' && $item['category'] !== $catFilter) {
            $match = false;
        }
        if (!empty($statusFilter) && $statusFilter !== 'Semua' && $item['status'] !== $statusFilter) {
            $match = false;
        }
        if (!empty($searchQuery)) {
            $haystack = strtolower($item['name'] . ' ' . $item['alt_name'] . ' ' . $item['address'] . ' ' . $item['founder_or_owner'] . ' ' . $item['focus']);
            if (strpos($haystack, strtolower($searchQuery)) === false) {
                $match = false;
            }
        }
        if ($match) {
            $galleries[] = formatGalleryItem($item);
        }
    }
}

// Header respon JSON
header('Content-Type: application/json; charset=utf-8');
echo json_encode([
    'status' => 'success',
    'count' => count($galleries),
    'categories' => [
        'Semua',
        'Galeri Seni',
        'Galeri Seni Kontemporer',
        'Galeri Institusi/Pemerintah',
        'Art Space',
        'Creative Space',
        'Ruang Budaya',
        'Galeri Seni Tradisional',
        'Galeri Seni Khusus',
        'Galeri Historis'
    ],
    'statuses' => [
        'Semua',
        'Aktif',
        'Historis/Tidak aktif',
        'Perlu diverifikasi'
    ],
    'data' => $galleries
], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
