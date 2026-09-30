/**
 * gallery-map.js  v4
 * Peta Interaktif Galeri Seni, Art Space & Ruang Budaya Kota Surabaya
 * Jejak Waktu â€” Koleksi Arsip & Seni Budaya Kota Surabaya
 */

window.addEventListener('load', function () {
  'use strict';

  // =========================================================================
  // 1. DEFINISI KATEGORI, SIMBOL, DAN WARNA TEMA
  // =========================================================================
  var CATEGORY_CONFIG = {
    'Galeri Seni': {
      color: '#9e2a2b',
      bgColor: '#fbe8e6',
      icon: 'ðŸ–¼ï¸',
      svgIcon: 'M4 4h16v12H4z M9 9l3 3 5-5 M4 20h16',
      desc: 'Galeri pameran seni rupa murni, seni lukis, dan bursa seni'
    },
    'Galeri Seni Kontemporer': {
      color: '#5e35b1',
      bgColor: '#ede7f6',
      icon: 'ðŸŽ¨',
      svgIcon: 'M12 2a10 10 0 100 20 10 10 0 000-20zm-2 5a2 2 0 110 4 2 2 0 010-4zm-3 5a2 2 0 110 4 2 2 0 010-4zm8 3a2 2 0 110 4 2 2 0 010-4z',
      desc: 'Platform kuratorial seni rupa kontemporer & eksplorasi visual'
    },
    'Galeri Institusi/Pemerintah': {
      color: '#1b4965',
      bgColor: '#e3f2fd',
      icon: 'ðŸ›ï¸',
      svgIcon: 'M2 10h20v2H2zm2 2h2v7H4zm6 0h2v7h-2zm6 0h2v7h-2zm-6-9l10 5H2l10-5z M2 20h20v2H2z',
      desc: 'Galeri dinas kebudayaan, dewan kesenian & instansi resmi'
    },
    'Art Space': {
      color: '#0077b6',
      bgColor: '#e0f7fa',
      icon: 'ðŸ“',
      svgIcon: 'M3 3v18h18V3H3zm16 16H5V5h14v14z',
      desc: 'Ruang seni independen, studio eksperimentasi & komunitas'
    },
    'Creative Space': {
      color: '#c77700',
      bgColor: '#fff8e1',
      icon: 'ðŸ’¡',
      svgIcon: 'M12 2C8.13 2 5 5.13 5 9c0 2.38 1.19 4.47 3 5.74V17c0 .55.45 1 1 1h6c.55 0 1-.45 1-1v-2.26c1.81-1.27 3-3.36 3-5.74 0-3.87-3.13-7-7-7z',
      desc: 'Ruang kerja kreatif, perpustakaan independen & workshop desain'
    },
    'Ruang Budaya': {
      color: '#2d6a4f',
      bgColor: '#e8f5e9',
      icon: 'ðŸŽ­',
      svgIcon: 'M12 3c-4.97 0-9 4.03-9 9 0 2.12.74 4.07 1.97 5.61L12 21l7.03-3.39C20.26 16.07 21 14.12 21 12c0-4.97-4.03-9-9-9z',
      desc: 'Pusat kebudayaan, seni pertunjukan & cagar budaya bersejarah'
    },
    'Galeri Seni Tradisional': {
      color: '#8c431b',
      bgColor: '#fbe9e7',
      icon: 'ðŸ—¡ï¸',
      svgIcon: 'M12 2l3 7h-6l3-7zm0 9l-4 9h8l-4-9z',
      desc: 'Galeri tosan aji, keris, kriya ukir & kesenian rakyat Nusantara'
    },
    'Galeri Seni Khusus': {
      color: '#b5179e',
      bgColor: '#fce4ec',
      icon: 'ðŸŒŸ',
      svgIcon: 'M12 17.27L18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z',
      desc: 'Galeri seni inklusif & ekspresi karya difabel/kebutuhan khusus'
    },
    'Galeri Historis': {
      color: '#4a4e69',
      bgColor: '#f0eff4',
      icon: 'â³',
      svgIcon: 'M6 2v6h.01L6 8.01 10 12l-4 4 .01.01H6V22h12v-5.99h-.01L18 16l-4-4 4-3.99-.01-.01H18V2H6z',
      desc: 'Galeri dengan nilai sejarah penting seni rupa Surabaya (Non-aktif)'
    }
  };

  // =========================================================================
  // 2. DATA UTAMA / FALLBACK 28 LOKASI SURABAYA
  // =========================================================================
  var DEFAULT_SURABAYA_GALLERIES = [
    {
      id: 1, slug: 'orasis-art-space', name: 'Orasis Art Space',
      alt_name: 'Pelangi Nusantara Art Gallery / Orasis Art Gallery',
      address: 'Jl. Bukit Golf No. B2-25, CitraLand, Surabaya',
      category: 'Galeri Seni', status: 'Aktif',
      founder_or_owner: 'Elizabeth / Liza', established_year: '2002 / 2005',
      history: 'Bermula sebagai Pelangi Nusantara Art Gallery pada tahun 2002, kemudian bertransformasi menjadi Orasis Art Gallery pada tahun 2005. Berlokasi di kawasan CitraLand Surabaya Barat dan menjadi ruang pamer seni kontemporer terkemuka.',
      focus: 'Seni rupa kontemporer Indonesia',
      events: ['Wacana Pelangi Nusantara', 'Pelangi Nusantara', 'The Power of Mind', 'Celebration: Grand Opening', 'Biennale Jatim X: Invisible Territories', 'Kine Kini', 'Irelandâ€™s Eye', 'Shipiboâ€“Konibo: Portraits of My Blood', 'O.H. Supono: Supercut of Life', 'Urban Pulse', 'A Man, A Monster & The Sea', 'Segue 2nd Edition: Lim Keng â€“ Breath of Lines', 'FINNA Art of The Year 2025'],
      latitude: -7.2792000, longitude: 112.6693000, is_verified_coord: 1,
      website: 'https://orasisartspace.com', instagram: '@orasisartspace',
      image: 'https://images.unsplash.com/photo-1577720643272-265f09367456?auto=format&fit=crop&w=800&q=80',
      sources: 'Katalog Pameran Orasis Art Space, Arsip Kuratorial Biennale Jatim X'
    },
    {
      id: 2, slug: 'hadi-art-platform', name: 'HaDi Art Platform',
      alt_name: 'HadiArtPlatform Contemporary Art Gallery / Hadi Gallery',
      address: 'Jl. Darmokali No. 16, Keputran, Tegalsari, Surabaya',
      category: 'Galeri Seni Kontemporer', status: 'Aktif',
      founder_or_owner: 'Irawan Hadikusumo', established_year: '2018 (14 Juli 2018)',
      history: 'Berkembang dari Hadi Gallery yang didirikan oleh Irawan Hadikusumo pada 14 Juli 2018. Kini beroperasi di Jl. Darmokali No. 16 Surabaya sebagai galeri seni kontemporer dan platform diskusi serta jejaring seni lintas kota.',
      focus: 'Seni rupa kontemporer Indonesia, platform seni, diskusi dan jaringan seni',
      events: ['Pameran Seni Kontemporer HaDi Art', 'Forum Kuratorial Seni Rupa Surabaya', 'Presentasi Karya & Dialog Seniman Muda'],
      latitude: -7.2927865, longitude: 112.7419552, is_verified_coord: 1,
      website: 'Belum ditemukan', instagram: 'Belum ditemukan',
      image: 'https://images.unsplash.com/photo-1536924940846-227afb31e2a5?auto=format&fit=crop&w=800&q=80',
      sources: 'Dokumentasi HaDi Art Platform, Arsip Kuratorial Surabaya'
    },
    {
      id: 3, slug: 'visma-art-gallery', name: 'Visma Art Gallery',
      alt_name: 'Visma Gallery',
      address: 'Jl. Tegalsari No. 35â€“37, Tegalsari, Surabaya',
      category: 'Galeri Seni', status: 'Aktif',
      founder_or_owner: 'Irawan Hadikusumo (Co-founder)', established_year: '2015',
      history: 'Didirikan pada tahun 2015 di kawasan bersejarah Tegalsari Surabaya dengan keterlibatan Irawan Hadikusumo sebagai co-founder. Menjadi galeri seni independen yang aktif menyelenggarakan pameran lukisan, fotografi, workshop, dan kolaborasi kebudayaan internasional.',
      focus: 'Seni rupa, lukisan, fotografi, workshop dan kolaborasi kebudayaan',
      events: ['Pameran karya Jacques Ferrier bersama Institut FranÃ§ais Indonesia (IFI) Surabaya', 'Pameran Fotografi & Seni Visual Tegalsari', 'Workshop Seni & Kolaborasi Lintas Disiplin'],
      latitude: -7.2763435, longitude: 112.7337319, is_verified_coord: 1,
      website: 'Belum ditemukan', instagram: 'Belum ditemukan',
      image: 'https://images.unsplash.com/photo-1518998053901-5348d3961a04?auto=format&fit=crop&w=800&q=80',
      sources: 'Institut FranÃ§ais Indonesia (IFI) Surabaya, Arsip Visma Art Gallery'
    },
    {
      id: 4, slug: 'uycc-art-gallery', name: 'UYCC Art Gallery',
      alt_name: 'Unicorn Young Collector Club',
      address: 'The WIN Hotel, Jl. Embong Tanjung No. 46â€“48 Lantai 2, Genteng, Surabaya',
      category: 'Galeri Seni', status: 'Aktif',
      founder_or_owner: 'Aldridge Tjiptarahardja', established_year: '2023 (Maret 2023)',
      history: 'Dibuka resmi pada Maret 2023 di The WIN Hotel lantai 2 Surabaya sebagai pengembangan dari ekosistem kreatif Unicorn Creative Space. Ditujukan sebagai wadah segar bagi apresiasi seni rupa Indonesia dan para kolektor muda.',
      focus: 'Seni rupa Indonesia dan ruang bagi kolektor muda',
      events: ['Continuity', 'Picturesque: Mixed Feeling Provoking Art Exhibition', 'From the River to the Liberty: Experience Art for Human Rights'],
      latitude: -7.2648000, longitude: 112.7384000, is_verified_coord: 1,
      website: 'Belum ditemukan', instagram: '@uycc.artgallery',
      image: 'https://images.unsplash.com/photo-1545989253-02cc26577f88?auto=format&fit=crop&w=800&q=80',
      sources: 'Katalog UYCC Art Gallery, Dokumentasi Media Seni Visual'
    },
    {
      id: 5, slug: 'unicorn-creative-space', name: 'Unicorn Creative Space',
      alt_name: 'Unicorn Extension',
      address: 'Kawasan Rungkut & Unicorn Extension, Jl. Dharma Husada Indah Utara No. 41, Mulyorejo, Surabaya',
      category: 'Art Space', status: 'Aktif',
      founder_or_owner: 'Aldridge Tjiptarahardja', established_year: '2020',
      history: 'Didirikan pada tahun 2020 oleh Aldridge Tjiptarahardja. Bermula di kawasan Rungkut dan berekspansi ke Dharma Husada Indah Utara No. 41 Surabaya sebagai hub kreatif komunitas seni, workshop, dan pameran berkala.',
      focus: 'Seni, creative space, workshop dan kegiatan kreatif',
      events: ['Pameran seni rupa', 'Pameran fotografi', 'Workshop batik', 'Workshop ecoprint', 'Workshop lilin aroma', 'Workshop suminagashi', 'Piknik Seni', 'From the River to the Liberty: Experience Art for Human Rights'],
      latitude: -7.2700574, longitude: 112.7755609, is_verified_coord: 1,
      website: 'Belum ditemukan', instagram: '@unicorncreativespace',
      image: 'https://images.unsplash.com/photo-1501084817091-a4f3d1d19e07?auto=format&fit=crop&w=800&q=80',
      sources: 'Dokumentasi Program Unicorn Creative Space'
    },
    {
      id: 6, slug: 'galeri-prabangkara', name: 'Galeri Prabangkara',
      alt_name: 'Galeri Prabangkara UPT Taman Budaya Jatim',
      address: 'Jl. Genteng Kali No. 85, Genteng, Surabaya',
      category: 'Galeri Institusi/Pemerintah', status: 'Aktif',
      founder_or_owner: 'UPT Taman Budaya Jawa Timur / Disbudpar Jatim', established_year: '2015 (10 Februari 2015)',
      history: 'Diresmikan pada 10 Februari 2015 di kompleks Taman Budaya Jawa Timur. Dikelola oleh UPT Taman Budaya Dinas Kebudayaan & Pariwisata Provinsi Jawa Timur sebagai ruang pameran seni rupa resmi pemerintah bagi seniman lokal, daerah, dan nasional.',
      focus: 'Seni rupa dan pameran kebudayaan',
      events: ['Ada dan Tiada', 'Alaras Terang', 'Garis Gathuk', 'Let\'s Imagine the Future Together', 'Dari Gosari ke Kasongan'],
      latitude: -7.2647000, longitude: 112.7435000, is_verified_coord: 1,
      website: 'https://tamanbudayajatim.com', instagram: '@tamanbudayajatim',
      image: 'https://images.unsplash.com/photo-1579783900882-c0d3dad7b119?auto=format&fit=crop&w=800&q=80',
      sources: 'UPT Taman Budaya Dinas Kebudayaan & Pariwisata Prov. Jawa Timur'
    },
    {
      id: 7, slug: 'vin-autism-gallery', name: 'Vin Autism Gallery',
      alt_name: 'Vincent Prijadi Art Gallery',
      address: 'G-Walk Junction TL 6/11, Citraland, Sambikerep, Surabaya',
      category: 'Galeri Seni Khusus', status: 'Aktif',
      founder_or_owner: 'Vincent Prijadi Purwono dan keluarga', established_year: 'Belum ditemukan',
      history: 'Didirikan oleh keluarga perupa Vincent Prijadi Purwono di kawasan G-Walk Citraland Surabaya Barat. Galeri ini mewadahi karya seni visual anak berkebutuhan khusus dan memberikan edukasi seni inklusif yang menginspirasi publik.',
      focus: 'Lukisan, seni visual dan kegiatan seni untuk anak berkebutuhan khusus',
      events: ['Should We Slow Down', 'Pameran lukisan Vincent di Stasiun Gubeng', 'Berbagai workshop seni inklusif'],
      latitude: -7.2878805, longitude: 112.6554319, is_verified_coord: 1,
      website: 'Belum ditemukan', instagram: '@vinautismgallery',
      image: 'https://images.unsplash.com/photo-1547826039-bfc35e0f1ea8?auto=format&fit=crop&w=800&q=80',
      sources: 'Dokumentasi Vin Autism Gallery, Liputan Media Seni Surabaya'
    },
    {
      id: 8, slug: 'teh-villa-gallery', name: 'Teh Villa Gallery',
      alt_name: 'Galeri Seni Teh Villa',
      address: 'Jl. Raya Rungkut Industri II No. 53, Rungkut, Surabaya',
      category: 'Galeri Seni', status: 'Aktif',
      founder_or_owner: 'Teh Villa Indonesia / PT Karya Mas Makmur', established_year: '1981 (sejarah bisnis) / 2020',
      history: 'Berkaitan erat dengan sejarah bisnis keluarga produsen Teh Villa (PT Karya Mas Makmur) yang berdiri di Surabaya sejak 1981. Menghadirkan ruang galeri seni rupa representatif di kawasan Rungkut Industri untuk mengangkat karya seni rupa dan kegiatan kemanusiaan.',
      focus: 'Seni lukis, ruang pameran, dan kegiatan seni sosial kemanusiaan',
      events: ['Living in Harmony', 'Finding Balance', 'Art for Humanity', 'Unexpected Reunion'],
      latitude: -7.3282000, longitude: 112.7756000, is_verified_coord: 1,
      website: 'https://tehvilla.com', instagram: '@tehvillagallery',
      image: 'https://images.unsplash.com/photo-1579783902614-a3fb3927b675?auto=format&fit=crop&w=800&q=80',
      sources: 'Arsip Pameran Teh Villa Gallery, PT Karya Mas Makmur'
    },
    {
      id: 9, slug: 'samata-house', name: 'Samata House',
      alt_name: 'Samata House Coffee & Art',
      address: 'Jl. Lingga No. 6, Gubeng, Surabaya',
      category: 'Art Space', status: 'Aktif',
      founder_or_owner: 'Belum ditemukan', established_year: 'Belum ditemukan',
      history: 'Ruang alternatif di kawasan Gubeng yang menggabungkan coffee house, ruang kreatif, dan galeri pameran bagi seniman muda independen. Dikenal aktif mengadakan pameran seni rupa, pasar seni, dan diskusi kebudayaan.',
      focus: 'Ruang kreatif, coffee house dan ruang pamer',
      events: ['TAKE OVER', 'Berbagai pameran seniman muda', 'Jatim Biennale XI â€“ Kelana', 'Berbagai kegiatan seni dan kreatif'],
      latitude: -7.2699509, longitude: 112.7512636, is_verified_coord: 1,
      website: 'Belum ditemukan', instagram: '@samatahouse',
      image: 'https://images.unsplash.com/photo-1513519245088-0e12902e5a38?auto=format&fit=crop&w=800&q=80',
      sources: 'Dokumentasi Samata House, Jatim Biennale XI'
    },
    {
      id: 10, slug: 'c2o-library-collabtive', name: 'C2O Library & Collabtive',
      alt_name: 'C2O Library',
      address: 'Jl. Dr. Cipto No. 22, Dr. Soetomo, Tegalsari, Surabaya',
      category: 'Creative Space', status: 'Aktif',
      founder_or_owner: 'PERINTIS', established_year: '2008',
      history: 'Didirikan pada tahun 2008 oleh organisasi nirlaba PERINTIS. C2O adalah perpustakaan independen, ruang kerja kolaboratif, serta pusat pameran budaya, literasi, film, desain, dan kegiatan kreatif komunitas warga.',
      focus: 'Seni, literasi, diskusi, film, desain dan kegiatan kreatif',
      events: ['Codex Code Art Exhibition', 'Post a Place', 'Surabaya AnimNation Festival', 'Eat Play Laugh', 'Design It Yourself (DIY) Fair', 'Berbagai diskusi, workshop dan pemutaran film'],
      latitude: -7.2812044, longitude: 112.7388612, is_verified_coord: 1,
      website: 'https://c2o-library.net', instagram: '@c2olibrary',
      image: 'https://images.unsplash.com/photo-1521587760476-6c12a4b040da?auto=format&fit=crop&w=800&q=80',
      sources: 'Kolektif C2O Library, Arsip Festival Seni Surabaya'
    },
    {
      id: 11, slug: 'rahasia-art-space', name: 'Rahasia Art Space',
      alt_name: 'Rahasia Studio',
      address: 'Perum YKP I, Jl. Pandugo Timur I No. 22, Penjaringan Sari, Rungkut, Surabaya',
      category: 'Art Space', status: 'Aktif',
      founder_or_owner: 'Pak Thomas', established_year: 'sekitar 2019',
      history: 'Didirikan sekitar tahun 2019 oleh Pak Thomas di perumahan YKP I Rungkut sebagai ruang kreasi seni independen, studio pameran, dan workshop kerajinan bagi komunitas seni lokal.',
      focus: 'Art space dan kegiatan kreatif',
      events: ['Pameran Seni Komunitas Rungkut', 'Workshop Kerajinan & Lukis Kreatif', 'Open Studio & Diskusi Seni'],
      latitude: -7.3181470, longitude: 112.7900883, is_verified_coord: 1,
      website: 'Belum ditemukan', instagram: 'Belum ditemukan',
      image: 'https://images.unsplash.com/photo-1460661419201-fd4cecdf8a8b?auto=format&fit=crop&w=800&q=80',
      sources: 'Dokumentasi Komunitas Seni Rungkut'
    },
    {
      id: 12, slug: 'galeri-merah-putih', name: 'Galeri Merah Putih',
      alt_name: 'Belum ditemukan',
      address: 'Kawasan Balai Pemuda / Embong Kaliasin, Genteng, Surabaya',
      category: 'Galeri Seni', status: 'Perlu diverifikasi',
      founder_or_owner: 'Belum ditemukan', established_year: 'Belum ditemukan',
      history: 'Galeri seni rupa yang tercatat berada di sekitar kawasan Balai Pemuda / Embong Kaliasin Genteng Surabaya. Sejarah pendirian, pengelola, dan catatan kuratorial pamerannya perlu diverifikasi lebih lanjut.',
      focus: 'Seni rupa dan pameran seni lukis',
      events: ['Pameran Lukisan Merah Putih', 'Perlu diverifikasi lebih lanjut'],
      latitude: -7.2639219, longitude: 112.7452930, is_verified_coord: 0,
      website: 'Belum ditemukan', instagram: 'Belum ditemukan',
      image: 'https://images.unsplash.com/photo-1541961017774-22349e4a1262?auto=format&fit=crop&w=800&q=80',
      sources: 'Arsip Kesenian Kota Surabaya, Perlu verifikasi lebih lanjut'
    },
    {
      id: 13, slug: 'galeri-mojopahit', name: 'Galeri Mojopahit',
      alt_name: 'Belum ditemukan',
      address: 'Jl. Darmokali No. 61A, Darmo, Wonokromo, Surabaya',
      category: 'Galeri Seni', status: 'Perlu diverifikasi',
      founder_or_owner: 'Belum ditemukan', established_year: 'Belum ditemukan',
      history: 'Galeri seni rupa yang beralamat di Jl. Darmokali No. 61A Wonokromo Surabaya. Data historis, pendiri, dan arsip pamerannya masih memerlukan verifikasi lapangan lebih lanjut.',
      focus: 'Seni rupa dan karya visual',
      events: ['Pameran Seni Lukis Koleksi', 'Perlu diverifikasi lebih lanjut'],
      latitude: -7.2927865, longitude: 112.7419552, is_verified_coord: 0,
      website: 'Belum ditemukan', instagram: 'Belum ditemukan',
      image: 'https://images.unsplash.com/photo-1582561424760-0321d75e81fa?auto=format&fit=crop&w=800&q=80',
      sources: 'Direktori Galeri Seni Surabaya, Perlu verifikasi'
    },
    {
      id: 14, slug: 'studio-lima-gallery', name: 'Studio Lima Gallery',
      alt_name: 'Belum ditemukan',
      address: 'Jl. Medokan Asri Utara VII No. 28, Medokan Ayu, Rungkut, Surabaya',
      category: 'Galeri Seni', status: 'Perlu diverifikasi',
      founder_or_owner: 'Belum ditemukan', established_year: 'Belum ditemukan',
      history: 'Studio dan ruang galeri seni rupa mandiri di Medokan Ayu Surabaya Timur. Sejarah pendirian, pengelola, dan kurasi pamerannya memerlukan verifikasi lebih lanjut.',
      focus: 'Seni rupa, lukisan, dan studio seni',
      events: ['Pameran Studio Seni Lokal', 'Perlu diverifikasi lebih lanjut'],
      latitude: -7.3225397, longitude: 112.7942222, is_verified_coord: 0,
      website: 'Belum ditemukan', instagram: 'Belum ditemukan',
      image: 'https://images.unsplash.com/photo-1576769267415-9642000aa3cb?auto=format&fit=crop&w=800&q=80',
      sources: 'Direktori Komunitas Seni Rungkut, Perlu verifikasi'
    },
    {
      id: 15, slug: 'wusto-art-gallery', name: 'Wusto Art Gallery',
      alt_name: 'Wusto Galeri Seni & Kaligrafi',
      address: 'Royal Plaza, Lantai LG Blok AA2-02, Ketintang, Wonokromo, Surabaya',
      category: 'Galeri Seni', status: 'Aktif',
      founder_or_owner: 'Belum ditemukan', established_year: 'Belum ditemukan',
      history: 'Galeri seni dan kerajinan komersial di Royal Plaza Surabaya lantai LG Blok AA2-02, menyajikan karya lukisan lanskap, figuratif, kaligrafi, dan bingkai seni berkualitas.',
      focus: 'Lukisan, kaligrafi, dan karya seni dekoratif',
      events: ['Bursa Lukisan Royal Plaza', 'Display Karya Seni Rutin'],
      latitude: -7.3091349, longitude: 112.7342835, is_verified_coord: 1,
      website: 'Belum ditemukan', instagram: 'Belum ditemukan',
      image: 'https://images.unsplash.com/photo-1513364776144-60967b0f800f?auto=format&fit=crop&w=800&q=80',
      sources: 'Direktori Tenant Royal Plaza Surabaya'
    },
    {
      id: 16, slug: 'utama-slamet-art-gallery', name: 'Utama Slamet Art Gallery',
      alt_name: 'Galeri Utama Slamet',
      address: 'Jl. Pahlawan No. 54 C-D, Alun-alun Contong, Bubutan, Surabaya',
      category: 'Galeri Seni', status: 'Aktif',
      founder_or_owner: 'Belum ditemukan', established_year: 'Belum ditemukan',
      history: 'Galeri seni lukis dan pembuatan bingkai seni klasik di jalur bersejarah Jl. Pahlawan dekat Tugu Pahlawan Surabaya. Menyediakan aneka lukisan klasik dan karya seni seniman lokal.',
      focus: 'Seni rupa, lukisan lanskap & figuratif, custom framing',
      events: ['Pameran Lukisan Klasik Jawa Timur', 'Bursa Seni Rupa Surabaya'],
      latitude: -7.2452377, longitude: 112.7385975, is_verified_coord: 1,
      website: 'Belum ditemukan', instagram: 'Belum ditemukan',
      image: 'https://images.unsplash.com/photo-1579783900882-c0d3dad7b119?auto=format&fit=crop&w=800&q=80',
      sources: 'Direktori Seni & Budaya Kota Surabaya'
    },
    {
      id: 17, slug: 'unsur-site', name: 'Unsur Site',
      alt_name: 'Unsur Art Space',
      address: 'Jl. Untung Suropati No. 85, DR. Soetomo, Tegalsari, Surabaya',
      category: 'Art Space', status: 'Aktif',
      founder_or_owner: 'Belum ditemukan', established_year: 'Belum ditemukan',
      history: 'Ruang seni independen dan proyek seni kolaboratif di Jl. Untung Suropati No. 85 Surabaya. Menjadi arena presentasi ide baru dan diskusi seni rupa visual kontemporer.',
      focus: 'Eksperimentasi seni, instalasi, dan ruang seni alternatif',
      events: ['Pameran Kolaborasi Visual Unsur', 'Sesi Presentasi Karya Seniman Muda', 'Diskusi Seni Kontemporer'],
      latitude: -7.2819427, longitude: 112.7348337, is_verified_coord: 1,
      website: 'Belum ditemukan', instagram: '@unsur.site',
      image: 'https://images.unsplash.com/photo-1499343162172-9b73b4abb8a5?auto=format&fit=crop&w=800&q=80',
      sources: 'Dokumentasi Program Seni Unsur Site'
    },
    {
      id: 18, slug: 'filadelvia', name: 'Filadelvia',
      alt_name: 'Filadelvia Art Gallery',
      address: 'Ruko Taman Puspa Raya Blok D No. 09, Sambikerep, Surabaya',
      category: 'Galeri Seni', status: 'Aktif',
      founder_or_owner: 'Belum ditemukan', established_year: 'Belum ditemukan',
      history: 'Galeri seni dan workshop lukisan di kawasan ruko Taman Puspa Raya CitraLand Surabaya Barat yang menyediakan karya seni lukis dan bingkai artistik.',
      focus: 'Seni lukis, karya seni visual, dan workshop melukis',
      events: ['Display Lukisan Kontemporer', 'Workshop Melukis Santai'],
      latitude: -7.2762729, longitude: 112.6446587, is_verified_coord: 1,
      website: 'Belum ditemukan', instagram: 'Belum ditemukan',
      image: 'https://images.unsplash.com/photo-1578632767115-351597cf2477?auto=format&fit=crop&w=800&q=80',
      sources: 'Direktori Usaha Seni Sambikerep Surabaya'
    },
    {
      id: 19, slug: 'art-center-bg-junction', name: 'Art Center BG Junction',
      alt_name: 'Pusat Seni BG Junction',
      address: 'BG Junction Mall, Lantai L2, Jl. Bubutan No. 1â€“7, Bubutan, Surabaya',
      category: 'Ruang Budaya', status: 'Aktif',
      founder_or_owner: 'Manajemen BG Junction Surabaya', established_year: 'Belum ditemukan',
      history: 'Pusat kegiatan seni publik di mal BG Junction Bubutan Surabaya. Sering dipakai untuk perhelatan festival seni budaya, pagelaran kesenian daerah, dan pameran seni rupa warga kota.',
      focus: 'Pameran seni publik, festival budaya, dan apresiasi karya masyarakat',
      events: ['Festival Seni Budaya Surabaya di BG Junction', 'Pameran Lukisan & Karya Kerajinan Publik'],
      latitude: -7.2549143, longitude: 112.7334286, is_verified_coord: 1,
      website: 'https://bgjunction.com', instagram: '@bgjunctionsurabaya',
      image: 'https://images.unsplash.com/photo-1508997449629-303059a039c0?auto=format&fit=crop&w=800&q=80',
      sources: 'Arsip Acara BG Junction, Disbudporapar Surabaya'
    },
    {
      id: 20, slug: 'kampoeng-seni-bg-junction', name: 'Kampoeng Seni',
      alt_name: 'Kampoeng Seni BG Junction',
      address: 'BG Junction Mall, Lantai L2, Jl. Bubutan No. 1â€“7, Bubutan, Surabaya',
      category: 'Galeri Seni Tradisional', status: 'Aktif',
      founder_or_owner: 'Paguyuban Seniman & Pengrajin Surabaya', established_year: 'Belum ditemukan',
      history: 'Komunitas sentra seniman lukis dan pengrajin tradisional yang berhimpun di lantai L2 mal BG Junction Surabaya. Pengunjung dapat melihat proses seniman melukis sketsa wajah, karikatur, serta karya kriya secara langsung.',
      focus: 'Seni rupa tradisional, lukisan potret langsung, ukiran dan kerajinan tangan',
      events: ['Bursa Lukisan On The Spot Kampoeng Seni', 'Demo Melukis Sketsa Wajah & Karikatur', 'Pameran Kerajinan Tradisional Jatim'],
      latitude: -7.2552000, longitude: 112.7339000, is_verified_coord: 1,
      website: 'Belum ditemukan', instagram: 'Belum ditemukan',
      image: 'https://images.unsplash.com/photo-1513364776144-60967b0f800f?auto=format&fit=crop&w=800&q=80',
      sources: 'Komunitas Seniman Kampoeng Seni BG Junction'
    },
    {
      id: 21, slug: 'sakura-warangan-keris', name: 'Sakura Warangan Keris',
      alt_name: 'Sanggar Tosan Aji Sakura',
      address: 'Jl. Pakis Tirtosari XA No. 26, Sawahan, Surabaya',
      category: 'Galeri Seni Tradisional', status: 'Aktif',
      founder_or_owner: 'Belum ditemukan', established_year: 'Belum ditemukan',
      history: 'Galeri pusaka dan sanggar konservasi tosan aji tradisional di kawasan Sawahan Surabaya. Mengedukasi masyarakat mengenai filosofi keris Nusantara, proses perawatan warangan, dan pelestarian benda budaya.',
      focus: 'Keris, tosan aji, warangan, dan benda seni tradisional Nusantara',
      events: ['Edukasi Budaya Tosan Aji & Jamasan Keris', 'Pameran Koleksi Keris Kuno Nusantara', 'Workshop Konservasi Benda Pusaka Tradisional'],
      latitude: -7.2910403, longitude: 112.7210246, is_verified_coord: 1,
      website: 'Belum ditemukan', instagram: 'Belum ditemukan',
      image: 'https://images.unsplash.com/photo-1582561424760-0321d75e81fa?auto=format&fit=crop&w=800&q=80',
      sources: 'Paguyuban Pecinta Tosan Aji Surabaya'
    },
    {
      id: 22, slug: 'cai-cai-company', name: 'Cai-Cai Company',
      alt_name: 'Cai-Cai Design & Caricature',
      address: 'Kawasan Villa Kalijudan Indah, Kalijudan, Mulyorejo, Surabaya',
      category: 'Creative Space', status: 'Aktif',
      founder_or_owner: 'Belum ditemukan', established_year: 'Belum ditemukan',
      history: 'Studio kreasi seni dan desain di kawasan Kalijudan Indah Surabaya Timur, menghadirkan workshop dan pameran karya visual bergenre karikatur dan desain grafis.',
      focus: 'Desain, ilustrasi, dan seni karikatur',
      events: ['Pameran Karikatur & Seni Ilustrasi', 'Workshop Menggambar Karikatur Urban'],
      latitude: -7.2605867, longitude: 112.7785473, is_verified_coord: 1,
      website: 'Belum ditemukan', instagram: 'Belum ditemukan',
      image: 'https://images.unsplash.com/photo-1541961017774-22349e4a1262?auto=format&fit=crop&w=800&q=80',
      sources: 'Direktori Studio Desain Surabaya'
    },
    {
      id: 23, slug: 'galeri-dks-balai-pemuda', name: 'Galeri DKS / Galeri Surabaya',
      alt_name: 'Balai Pemuda Art Space / Galeri Dewan Kesenian Surabaya',
      address: 'Kawasan Balai Pemuda, Jl. Gubernur Suryo No. 15, Embong Kaliasin, Genteng, Surabaya',
      category: 'Galeri Institusi/Pemerintah', status: 'Aktif',
      founder_or_owner: 'Dewan Kesenian Surabaya (DKS) / Pemkot Surabaya', established_year: '1971 (DKS 10 Oktober 1971) / awal 1980-an (galeri)',
      history: 'Berkaitan erat dengan Dewan Kesenian Surabaya (DKS) yang berdiri pada 10 Oktober 1971 dan pengembangan ruang galeri pameran pada awal 1980-an di kompleks cagar budaya Balai Pemuda. Menjadi episentrum bersejarah pergerakan seniman Surabaya dari masa ke masa.',
      focus: 'Seni rupa dan kegiatan kebudayaan multidisiplin',
      events: ['Pameran Seni Rupa Tahunan DKS', 'Surabaya Art Week', 'Pementasan Teater & Tari Tradisional Balai Pemuda', 'Diskusi Sastra & Pemutaran Film Independen'],
      latitude: -7.2645158, longitude: 112.7455144, is_verified_coord: 1,
      website: 'https://surabaya.go.id', instagram: '@dewankeseniansurabaya',
      image: 'https://images.unsplash.com/photo-1544816155-12df9643f363?auto=format&fit=crop&w=800&q=80',
      sources: 'Dewan Kesenian Surabaya, Dinas Kebudayaan & Pariwisata Kota Surabaya'
    },
    {
      id: 24, slug: 'emmitan-fine-art-gallery', name: 'Emmitan Fine Art Gallery',
      alt_name: 'Emmitan Gallery',
      address: 'Jl. Walikota Mustajab, Ketabang, Genteng, Surabaya',
      category: 'Galeri Historis', status: 'Historis/Tidak aktif',
      founder_or_owner: 'Belum ditemukan', established_year: 'Belum ditemukan',
      history: 'Galeri seni rupa legendaris yang tercatat pernah aktif beroperasi di Jl. Walikota Mustajab Surabaya. Dikenal dalam sejarah seni rupa Jawa Timur sebagai tempat pameran prestisius karya master seniman nasional dan ruang diskusi kesenian Jawa Timur.',
      focus: 'Seni rupa murni, seni lukis maestro, dan diskursus seni',
      events: ['Pameran Seniman Nasional & Jawa Timur', 'Retrospeksi Seni Lukis Modern', 'Diskusi Kesenian & Temu Kurator'],
      latitude: -7.2607032, longitude: 112.7467381, is_verified_coord: 1,
      website: 'Belum ditemukan', instagram: 'Belum ditemukan',
      image: 'https://images.unsplash.com/photo-1579783900882-c0d3dad7b119?auto=format&fit=crop&w=800&q=80',
      sources: 'Arsip Sejarah Seni Rupa Surabaya, Katalog Seni Rupa Indonesia'
    },
    {
      id: 25, slug: 'gracia-art-gallery', name: 'Gracia Art Gallery',
      alt_name: 'Gracia Gallery',
      address: 'Kawasan Bukit Darmo Golf, Pradah Kalikendal, Dukuh Pakis, Surabaya',
      category: 'Galeri Historis', status: 'Historis/Tidak aktif',
      founder_or_owner: 'Belum ditemukan', established_year: 'Belum ditemukan',
      history: 'Galeri seni prestisius yang pernah tercatat berlokasi di kawasan Bukit Darmo Golf Surabaya Barat. Menjadi ruang pameran penting bagi karya seni rupa seniman Surabaya serta pelukis dari berbagai kota di luar Jawa Timur.',
      focus: 'Seni rupa, pameran lukisan koleksi, dan apresiasi seni',
      events: ['Pameran Seniman Surabaya & Luar Kota', 'Pameran Lukisan Masterpiece Koleksi'],
      latitude: -7.2891865, longitude: 112.6874017, is_verified_coord: 1,
      website: 'Belum ditemukan', instagram: 'Belum ditemukan',
      image: 'https://images.unsplash.com/photo-1578632767115-351597cf2477?auto=format&fit=crop&w=800&q=80',
      sources: 'Arsip Galeri Seni Surabaya Barat, Catatan Kuratorial Jawa Timur'
    },
    {
      id: 26, slug: 'its-cultural-center', name: 'ITS Cultural Center',
      alt_name: 'Pusat Kebudayaan ITS',
      address: 'Institut Teknologi Sepuluh Nopember, Keputih, Sukolilo, Surabaya',
      category: 'Ruang Budaya', status: 'Aktif',
      founder_or_owner: 'Institut Teknologi Sepuluh Nopember (ITS)', established_year: 'Belum ditemukan',
      history: 'Pusat kegiatan kebudayaan dan ruang apresiasi seni di lingkungan Institut Teknologi Sepuluh Nopember (ITS) Surabaya. Bukan galeri komersial murni, melainkan ruang budaya akademik untuk seni pertunjukan, diskusi dan kegiatan kebudayaan civitas akademika dan publik.',
      focus: 'Seni pertunjukan, diskusi dan kegiatan kebudayaan',
      events: ['Pagelaran Seni & Budaya Nusantara ITS', 'Festival Tari & Musik Mahasiswa', 'Diskusi Kebudayaan & Sains-Seni'],
      latitude: -7.2826954, longitude: 112.7956782, is_verified_coord: 1,
      website: 'https://www.its.ac.id', instagram: '@itsculturalcenter',
      image: 'https://images.unsplash.com/photo-1511671782779-c97d3d27a1d4?auto=format&fit=crop&w=800&q=80',
      sources: 'Direktorat Kemahasiswaan & Kebudayaan ITS Surabaya'
    },
    {
      id: 27, slug: 'galeri-ajbs', name: 'Galeri AJBS',
      alt_name: 'AJBS Art Gallery',
      address: 'Jl. Raya Dukuh Kupang No. 110, Sawahan, Surabaya',
      category: 'Galeri Seni', status: 'Aktif',
      founder_or_owner: 'Agus Joko Budi Santoso', established_year: '2010',
      history: 'Didirikan pada tahun 2010 oleh kolektor seni Agus Joko Budi Santoso di kompleks AJBS Surabaya. Menjadi galeri seni terpadu yang aktif mewadahi seniman Jawa Timur dan Nusantara untuk menggelar pameran seni rupa bersama dan workshop seni rupa.',
      focus: 'Seni rupa Jawa Timur & Nusantara, pameran bersama, dan workshop melukis',
      events: ['Pameran Seni Rupa Nusantara 2020', 'Workshop Melukis Akrilis Bersama 2022', 'Pameran Kolaborasi Seniman Jawa Timur 2023'],
      latitude: -7.2931000, longitude: 112.7147000, is_verified_coord: 1,
      website: 'Belum ditemukan', instagram: 'Belum ditemukan',
      image: 'https://images.unsplash.com/photo-1460661419201-fd4cecdf8a8b?auto=format&fit=crop&w=800&q=80',
      sources: 'Dokumentasi Galeri AJBS Surabaya'
    },
    {
      id: 28, slug: 'house-of-sampoerna', name: 'House of Sampoerna',
      alt_name: 'The Pavillion House of Sampoerna',
      address: 'Taman Sampoerna No. 6, Krembangan Utara, Pabean Cantian, Surabaya',
      category: 'Ruang Budaya', status: 'Aktif',
      founder_or_owner: 'Keluarga Sampoerna (Putera Sampoerna Foundation)', established_year: '2004 (kompleks berdiri 1862)',
      history: 'Kompleks cagar budaya bersejarah peninggalan kolonial Belanda yang dibangun pada 1862. Sejak 2004 dikelola oleh Putera Sampoerna Foundation sebagai museum industri tembakau serta galeri seni rupa berkala "The Pavillion" yang menampilkan pameran seni rupa terkemuka Jawa Timur.',
      focus: 'Seni rupa kontemporer di The Pavillion, sejarah industri, arsitektur cagar budaya, dan tur sejarah Heritage Walk',
      events: ['Pameran Tetap Sejarah Sampoerna & Surabaya', 'Art Market Bulanan Taman Sampoerna 2022', 'Heritage Walk & Photography Exhibition 2023', 'Pameran "Kota Pahlawan dalam Kanvas" 2024'],
      latitude: -7.2309923, longitude: 112.7341461, is_verified_coord: 1,
      website: 'https://houseofsampoerna.museum', instagram: '@houseofsampoerna',
      image: 'https://images.unsplash.com/photo-1578632767115-351597cf2477?auto=format&fit=crop&w=800&q=80',
      sources: 'Putera Sampoerna Foundation, Arsip Cagar Budaya Surabaya'
    }
  ];

  var allGalleries = DEFAULT_SURABAYA_GALLERIES.slice();

  // Helper escape HTML
  function esc(str) {
    if (str === null || str === undefined) return '';
    return String(str)
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;')
      .replace(/'/g, '&#39;');
  }

  // Helper Google Maps URL
  function getGoogleMapsUrl(g) {
    if (g.latitude && g.longitude) {
      return 'https://www.google.com/maps/search/?api=1&query=' + g.latitude + ',' + g.longitude;
    }
    return 'https://www.google.com/maps/search/?api=1&query=' + encodeURIComponent(g.name + ' ' + g.address);
  }

  // Generator custom SVG Marker Icon
  function makeMarkerIcon(g, isActive) {
    var catConf = CATEGORY_CONFIG[g.category] || { color: '#a8382b', icon: 'ðŸ–¼ï¸' };
    var pinColor = catConf.color;
    var iconSym = catConf.icon;
    var goldColor = '#c99e46';
    var scale = isActive ? 1.25 : 1.0;
    var w = Math.round(34 * scale);
    var h = Math.round(44 * scale);
    var anchorX = Math.round(17 * scale);
    var anchorY = h;

    var strokeColor = isActive ? '#ffe082' : goldColor;
    var strokeWidth = isActive ? '2.5' : '1.8';

    var svg = '<svg xmlns="http://www.w3.org/2000/svg" width="' + w + '" height="' + h + '" viewBox="0 0 34 44">'
      + '<filter id="ds_' + (isActive ? 'a' : 'n') + '">'
      + '<feDropShadow dx="0" dy="2" stdDeviation="2" flood-opacity="' + (isActive ? '0.6' : '0.35') + '"/>'
      + '</filter>'
      + '<g filter="url(#ds_' + (isActive ? 'a' : 'n') + ')">'
      + '<path d="M17 1C8.72 1 2 7.72 2 16c0 11.2 15 27 15 27S32 27.2 32 16C32 7.72 25.28 1 17 1z" fill="' + pinColor + '" stroke="' + strokeColor + '" stroke-width="' + strokeWidth + '"/>'
      + '<circle cx="17" cy="16" r="9" fill="#ffffff" opacity="0.95"/>'
      + '<text x="17" y="20.5" text-anchor="middle" font-size="10" font-family="Apple Color Emoji, Segoe UI Emoji, sans-serif">' + iconSym + '</text>'
      + '</g></svg>';

    return L.divIcon({
      html: svg,
      className: 'gallery-custom-marker' + (isActive ? ' is-active' : ''),
      iconSize: [w, h],
      iconAnchor: [anchorX, anchorY],
      popupAnchor: [0, -anchorY - 2]
    });
  }

  // =========================================================================
  // 3. PETA MINI SIDEBAR (Preview statis & interaktif ringan)
  // =========================================================================
  var miniMapEl = document.getElementById('galleryMapCanvas');
  var miniMap = null;
  var miniMarkers = [];

  function initMiniMap() {
    if (!miniMapEl || typeof L === 'undefined') return;
    miniMap = L.map('galleryMapCanvas', {
      center: [-7.2655, 112.7420],
      zoom: 11,
      scrollWheelZoom: false, tap: false, zoomControl: false,
      dragging: false, doubleClickZoom: false, boxZoom: false, keyboard: false, touchZoom: false
    });

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
      attribution: '&copy; OpenStreetMap',
      maxZoom: 18
    }).addTo(miniMap);

    renderMiniMapMarkers();

    setTimeout(function () { if (miniMap) miniMap.invalidateSize(); }, 300);
    if ('IntersectionObserver' in window) {
      new IntersectionObserver(function (entries) {
        entries.forEach(function (e) { if (e.isIntersecting && miniMap) miniMap.invalidateSize(); });
      }, { threshold: 0.05 }).observe(miniMapEl);
    }
  }

  function renderMiniMapMarkers() {
    if (!miniMap) return;
    miniMarkers.forEach(function (m) { miniMap.removeLayer(m); });
    miniMarkers = [];

    allGalleries.forEach(function (g) {
      if (g.latitude && g.longitude) {
        var marker = L.marker([g.latitude, g.longitude], {
          icon: makeMarkerIcon(g, false),
          title: g.name,
          interactive: false
        }).addTo(miniMap);
        miniMarkers.push(marker);
      }
    });
  }

  // =========================================================================
  // 4. MODAL FULLSCREEN & STATE MANAJEMEN
  // =========================================================================
  var modal        = document.getElementById('galleryMapModal');
  var modalClose   = document.getElementById('galleryMapModalClose');
  var trigger      = document.getElementById('galleryMapTrigger');
  var modalCanvas  = document.getElementById('galleryMapModalCanvas');
  var modalPanel   = document.getElementById('galleryInfoPanelModal');
  var modalInner   = document.getElementById('galleryInfoInnerModal');

  var searchInput  = document.getElementById('gmapSearchInput');
  var searchClear  = document.getElementById('gmapSearchClear');
  var statusFilter = document.getElementById('gmapStatusFilter');
  var catFilterSel = document.getElementById('gmapCategoryFilter');
  var catPillsBar  = document.getElementById('gmapCategoryPillsBar');

  var listDrawer   = document.getElementById('gmapListDrawer');
  var toggleListBtn= document.getElementById('gmapToggleListBtn');
  var closeListBtn = document.getElementById('gmapCloseListBtn');
  var listItemsEl  = document.getElementById('gmapListItems');
  var drawerCount  = document.getElementById('gmapDrawerCount');
  var headerCount  = document.getElementById('gmapHeaderCount');

  var legendDrawer = document.getElementById('gmapLegendDrawer');
  var toggleLegBtn = document.getElementById('gmapToggleLegendBtn');
  var closeLegBtn  = document.getElementById('gmapCloseLegendBtn');
  var legendContent= document.getElementById('gmapLegendContent');

  var modalMap = null;
  var modalInitialized = false;
  var activeMarkerModal = null;
  var clusterGroup = null;
  var markerMap = {}; // mapping slug -> L.marker

  var currentFilter = {
    search: '',
    category: 'Semua',
    status: 'Semua'
  };

  function initModalMap() {
    if (modalInitialized || !modalCanvas || typeof L === 'undefined') return;
    modalInitialized = true;

    modalMap = L.map('galleryMapModalCanvas', {
      center: [-7.2655, 112.7420],
      zoom: 12,
      scrollWheelZoom: true,
      tap: true,
      zoomControl: true
    });

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
      attribution: '&copy; <a href="https://openstreetmap.org/copyright" target="_blank">OpenStreetMap</a> Â· Jejak Waktu Surabaya',
      maxZoom: 19
    }).addTo(modalMap);

    // Inisialisasi Marker Clustering jika didukung
    if (typeof L.markerClusterGroup === 'function') {
      clusterGroup = L.markerClusterGroup({
        showCoverageOnHover: false,
        zoomToBoundsOnClick: true,
        spiderfyOnMaxZoom: true,
        maxClusterRadius: 40,
        iconCreateFunction: function (cluster) {
          var count = cluster.getChildCount();
          var size = count < 5 ? 36 : (count < 10 ? 42 : 48);
          return L.divIcon({
            html: '<div class="marker-cluster-vintage" style="width:' + size + 'px;height:' + size + 'px;"><span>' + count + '</span></div>',
            className: 'custom-cluster-wrapper',
            iconSize: [size, size]
          });
        }
      });
      modalMap.addLayer(clusterGroup);
    } else {
      clusterGroup = L.layerGroup().addTo(modalMap);
    }

    renderLegend();
    applyFilters();
  }

  // =========================================================================
  // 5. FILTERING, PENCARIAN & UPDATE TAMPILAN
  // =========================================================================
  function getFilteredGalleries() {
    return allGalleries.filter(function (g) {
      // Category filter
      if (currentFilter.category !== 'Semua' && g.category !== currentFilter.category) {
        return false;
      }
      // Status filter
      if (currentFilter.status !== 'Semua' && g.status !== currentFilter.status) {
        return false;
      }
      // Search query
      if (currentFilter.search) {
        var q = currentFilter.search.toLowerCase();
        var matchName = (g.name || '').toLowerCase().indexOf(q) !== -1;
        var matchAlt = (g.alt_name || '').toLowerCase().indexOf(q) !== -1;
        var matchAddr = (g.address || '').toLowerCase().indexOf(q) !== -1;
        var matchFounder = (g.founder_or_owner || '').toLowerCase().indexOf(q) !== -1;
        var matchFocus = (g.focus || '').toLowerCase().indexOf(q) !== -1;
        var matchCat = (g.category || '').toLowerCase().indexOf(q) !== -1;
        if (!matchName && !matchAlt && !matchAddr && !matchFounder && !matchFocus && !matchCat) {
          return false;
        }
      }
      return true;
    });
  }

  function applyFilters() {
    var filtered = getFilteredGalleries();

    // Update markers on Map
    if (clusterGroup) {
      clusterGroup.clearLayers();
      markerMap = {};

      filtered.forEach(function (g) {
        if (g.latitude && g.longitude) {
          var marker = L.marker([g.latitude, g.longitude], {
            icon: makeMarkerIcon(g, false),
            title: g.name
          });

          // Popup ringkas saat marker di-hover / di-klik
          var catConf = CATEGORY_CONFIG[g.category] || { color: '#a8382b', icon: 'ðŸ–¼ï¸' };
          var popupContent = '<div style="min-width:180px;font-family:serif;">'
            + '<div style="display:flex;align-items:center;gap:4px;font-size:0.7rem;font-weight:bold;color:' + catConf.color + ';margin-bottom:3px;">'
            + catConf.icon + ' ' + esc(g.category)
            + '</div>'
            + '<strong style="font-size:0.92rem;color:#2b231d;">' + esc(g.name) + '</strong>'
            + '<p style="margin:3px 0 0;font-size:0.74rem;color:#666;">' + esc(g.address) + '</p>'
            + '<div style="margin-top:6px;font-size:0.68rem;color:#a8382b;font-weight:bold;">Klik untuk melihat informasi lengkap &rarr;</div>'
            + '</div>';
          marker.bindPopup(popupContent, { offset: [0, -38] });

          marker.on('click', function () {
            selectGallery(g, marker);
          });

          clusterGroup.addLayer(marker);
          markerMap[g.slug] = marker;
        }
      });
    }

    // Update List Drawer
    renderListDrawer(filtered);

    // Update Counter Badges
    var count = filtered.length;
    if (drawerCount) drawerCount.textContent = count;
    if (headerCount) headerCount.textContent = count;

    var footerBadge = document.getElementById('gmapFooterBadge');
    if (footerBadge) footerBadge.textContent = count + ' Lokasi Ditampilkan';

    var statusSummary = document.getElementById('gmapFooterSummary');
    if (statusSummary) {
      var aktifCount = filtered.filter(function(x){ return x.status === 'Aktif'; }).length;
      var histCount = filtered.filter(function(x){ return x.status === 'Historis/Tidak aktif'; }).length;
      var verifCount = filtered.filter(function(x){ return x.status === 'Perlu diverifikasi'; }).length;
      statusSummary.textContent = aktifCount + ' Aktif Â· ' + histCount + ' Historis Â· ' + verifCount + ' Perlu Verifikasi';
    }
  }

  // Pilih dan fokuskan ke suatu galeri
  function selectGallery(g, marker) {
    if (activeMarkerModal && activeMarkerModal !== marker) {
      // kembalikan ikon marker sebelumnya
      var prevG = activeMarkerModal._galleryData;
      if (prevG) activeMarkerModal.setIcon(makeMarkerIcon(prevG, false));
    }

    if (marker) {
      marker._galleryData = g;
      marker.setIcon(makeMarkerIcon(g, true));
      activeMarkerModal = marker;
    }

    renderModalInfoPanel(g);

    if (g.latitude && g.longitude && modalMap) {
      modalMap.panTo([g.latitude, g.longitude], { animate: true, duration: 0.6 });
    }

    // Highlight item di list drawer
    if (listItemsEl) {
      var allItems = listItemsEl.querySelectorAll('.gmap-list-item');
      allItems.forEach(function (el) {
        if (el.getAttribute('data-slug') === g.slug) {
          el.classList.add('is-selected');
          el.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        } else {
          el.classList.remove('is-selected');
        }
      });
    }
  }

  // Render List Drawer
  function renderListDrawer(items) {
    if (!listItemsEl) return;
    if (items.length === 0) {
      listItemsEl.innerHTML = '<div style="padding:1.5rem;text-align:center;color:#78604d;font-family:serif;">'
        + '<p style="font-size:1.5rem;margin:0 0 0.5rem;">ðŸ”</p>'
        + '<p style="font-size:0.85rem;margin:0;">Tidak ada galeri atau ruang seni yang cocok dengan filter saat ini.</p>'
        + '<button type="button" id="gmapResetFilterBtn" style="margin-top:0.75rem;padding:0.35rem 0.8rem;background:#4d2f1d;color:#c99e46;border:none;border-radius:4px;cursor:pointer;font-size:0.75rem;font-family:monospace;">Reset Filter</button>'
        + '</div>';
      var rBtn = document.getElementById('gmapResetFilterBtn');
      if (rBtn) {
        rBtn.addEventListener('click', function () {
          currentFilter = { search: '', category: 'Semua', status: 'Semua' };
          if (searchInput) searchInput.value = '';
          if (searchClear) searchClear.style.display = 'none';
          if (catFilterSel) catFilterSel.value = 'Semua';
          if (statusFilter) statusFilter.value = 'Semua';
          updateActivePill('Semua');
          applyFilters();
        });
      }
      return;
    }

    var html = items.map(function (g) {
      var catConf = CATEGORY_CONFIG[g.category] || { color: '#a8382b', icon: 'ðŸ–¼ï¸', bgColor: '#fbe8e6' };
      var statusClass = g.status === 'Aktif' ? 'status-aktif' : (g.status === 'Historis/Tidak aktif' ? 'status-historis' : 'status-verifikasi');
      var verifBadge = (!g.is_verified_coord || g.status === 'Perlu diverifikasi')
        ? '<span class="gmap-badge-status status-verifikasi" title="Data/koordinat perlu diverifikasi lebih lanjut">âš ï¸ Verifikasi</span>'
        : '';

      return '<div class="gmap-list-item" data-slug="' + esc(g.slug) + '" role="button" tabindex="0">'
        + '<div class="gmap-list-item-header">'
        + '<h4 class="gmap-list-item-title">' + esc(g.name) + '</h4>'
        + '<span class="gmap-badge-status ' + statusClass + '">' + esc(g.status) + '</span>'
        + '</div>'
        + '<p class="gmap-list-item-addr">ðŸ“ ' + esc(g.address) + '</p>'
        + '<div class="gmap-list-item-tags">'
        + '<span class="gmap-badge-category" style="background:' + catConf.bgColor + ';color:' + catConf.color + ';">'
        + catConf.icon + ' ' + esc(g.category)
        + '</span>'
        + verifBadge
        + '</div>'
        + '</div>';
    }).join('');

    listItemsEl.innerHTML = html;

    // Attach click events to list items
    var domItems = listItemsEl.querySelectorAll('.gmap-list-item');
    domItems.forEach(function (el) {
      var slug = el.getAttribute('data-slug');
      var itemData = allGalleries.find(function (x) { return x.slug === slug; });
      if (itemData) {
        el.addEventListener('click', function () {
          var targetMarker = markerMap[slug];
          selectGallery(itemData, targetMarker);
          // Tutup drawer di layar mobile untuk memberi ruang pada peta & panel
          if (window.innerWidth < 768 && listDrawer) {
            listDrawer.classList.remove('is-open');
            if (toggleListBtn) toggleListBtn.classList.remove('is-active');
          }
        });
      }
    });
  }

  // Render Legenda Drawer
  function renderLegend() {
    if (!legendContent) return;
    var cats = Object.keys(CATEGORY_CONFIG);

    var html = cats.map(function (catName) {
      var cfg = CATEGORY_CONFIG[catName];
      var count = allGalleries.filter(function (x) { return x.category === catName; }).length;

      return '<div class="gmap-legend-item" data-category="' + esc(catName) + '" role="button" tabindex="0" title="Klik untuk memfilter kategori ini">'
        + '<div class="gmap-legend-left">'
        + '<div class="gmap-legend-icon-badge" style="background:' + cfg.color + ';">' + cfg.icon + '</div>'
        + '<div>'
        + '<div class="gmap-legend-name">' + esc(catName) + '</div>'
        + '<div style="font-size:0.65rem;color:#78604d;margin-top:2px;">' + esc(cfg.desc) + '</div>'
        + '</div>'
        + '</div>'
        + '<span class="gmap-legend-count">' + count + '</span>'
        + '</div>';
    }).join('');

    legendContent.innerHTML = html;

    var legendItems = legendContent.querySelectorAll('.gmap-legend-item');
    legendItems.forEach(function (el) {
      var cat = el.getAttribute('data-category');
      el.addEventListener('click', function () {
        currentFilter.category = cat;
        if (catFilterSel) catFilterSel.value = cat;
        updateActivePill(cat);
        applyFilters();
        if (legendDrawer) {
          legendDrawer.classList.remove('is-open');
          if (toggleLegBtn) toggleLegBtn.classList.remove('is-active');
        }
      });
    });
  }

  // Render Rich Detail Info Panel Modal
  function renderModalInfoPanel(g) {
    if (!modalPanel || !modalInner) return;

    var catConf = CATEGORY_CONFIG[g.category] || { color: '#a8382b', icon: 'ðŸ–¼ï¸', bgColor: '#fbe8e6' };
    var statusClass = g.status === 'Aktif' ? 'status-aktif' : (g.status === 'Historis/Tidak aktif' ? 'status-historis' : 'status-verifikasi');
    var gMapsUrl = getGoogleMapsUrl(g);

    // List of events
    var eventsHtml = '<p style="font-size:0.75rem;color:#78604d;font-style:italic;margin:0.2rem 0;">Belum ada arsip pameran yang terdokumentasi.</p>';
    if (Array.isArray(g.events) && g.events.length > 0) {
      eventsHtml = '<ul class="gmap-events-list">'
        + g.events.map(function (ev) { return '<li>' + esc(ev) + '</li>'; }).join('')
        + '</ul>';
    }

    // Web & Instagram buttons
    var webBtn = '';
    if (g.website && g.website !== 'Belum ditemukan') {
      webBtn = '<a class="gmap-link-btn gmap-btn-web" href="' + esc(g.website) + '" target="_blank" rel="noopener noreferrer">ðŸŒ Website Resmi</a>';
    }

    var igBtn = '';
    if (g.instagram && g.instagram !== 'Belum ditemukan') {
      var igHandle = g.instagram.replace('@', '');
      igBtn = '<a class="gmap-link-btn gmap-btn-web" href="https://instagram.com/' + esc(igHandle) + '" target="_blank" rel="noopener noreferrer">ðŸ“· Instagram (' + esc(g.instagram) + ')</a>';
    }

    var coordNotice = '';
    if (!g.is_verified_coord || g.status === 'Perlu diverifikasi') {
      coordNotice = '<div style="background:#fff8e1;border:1px solid #ffe082;border-radius:4px;padding:0.4rem 0.6rem;font-size:0.72rem;color:#b26a00;margin-top:0.35rem;">'
        + 'âš ï¸ <strong>Catatan:</strong> Informasi kuratorial / koordinat titik ini masih dalam proses verifikasi lapangan Kota Surabaya.'
        + '</div>';
    }

    modalInner.innerHTML = '<button class="gmap-detail-close-btn" id="gmapDetailCloseBtn" aria-label="Tutup panel detail" title="Tutup detail">&#10005;</button>'
      + '<div class="gmap-detail-card">'
      + '<div class="gmap-detail-banner-wrap">'
      + '<img class="gmap-detail-banner" src="' + esc(g.image || 'https://images.unsplash.com/photo-1579783900882-c0d3dad7b119?auto=format&fit=crop&w=800&q=80') + '" alt="' + esc(g.name) + '">'
      + '<span class="gmap-detail-banner-badge" style="background:' + catConf.color + ';color:#fff;">' + catConf.icon + ' ' + esc(g.category) + '</span>'
      + '</div>'
      + '<div class="gmap-detail-content">'
      + '<div class="gmap-detail-header-row">'
      + '<div>'
      + '<h3 class="gmap-detail-title">' + esc(g.name) + '</h3>'
      + (g.alt_name && g.alt_name !== 'Belum ditemukan' ? '<div class="gmap-detail-alt-title">Nama Lain / Perkembangan: ' + esc(g.alt_name) + '</div>' : '')
      + '<div class="gmap-detail-tags-row">'
      + '<span class="gmap-badge-status ' + statusClass + '">' + esc(g.status) + '</span>'
      + '<span class="gmap-badge-category" style="background:' + catConf.bgColor + ';color:' + catConf.color + ';">' + catConf.icon + ' ' + esc(g.category) + '</span>'
      + '</div>'
      + '</div>'
      + '</div>'
      + '<div class="gmap-detail-grid">'
      + '<div class="gmap-meta-cell"><span class="gmap-meta-label">ðŸ“ Alamat</span><span class="gmap-meta-value">' + esc(g.address) + '</span></div>'
      + '<div class="gmap-meta-cell"><span class="gmap-meta-label">ðŸ‘¤ Founder / Pengelola</span><span class="gmap-meta-value">' + esc(g.founder_or_owner || 'Belum ditemukan') + '</span></div>'
      + '<div class="gmap-meta-cell"><span class="gmap-meta-label">ðŸ“… Tahun Berdiri / Buka</span><span class="gmap-meta-value">' + esc(g.established_year || 'Belum ditemukan') + '</span></div>'
      + '<div class="gmap-meta-cell"><span class="gmap-meta-label">ðŸŽ¯ Fokus Seni</span><span class="gmap-meta-value">' + esc(g.focus || 'Belum ditemukan') + '</span></div>'
      + '</div>'
      + coordNotice
      + (g.history ? '<div><h4 class="gmap-detail-section-title">Sejarah &amp; Latar Belakang</h4><p class="gmap-detail-text">' + esc(g.history) + '</p></div>' : '')
      + '<div><h4 class="gmap-detail-section-title">Event &amp; Pameran Terdokumentasi</h4>' + eventsHtml + '</div>'
      + (g.sources ? '<div style="font-size:0.7rem;color:#78604d;margin-top:0.2rem;"><strong>Sumber Data:</strong> ' + esc(g.sources) + '</div>' : '')
      + '<div class="gmap-links-row">'
      + '<a class="gmap-link-btn gmap-btn-maps" href="' + gMapsUrl + '" target="_blank" rel="noopener noreferrer">'
      + '<svg style="width:14px;height:14px;fill:currentColor;" viewBox="0 0 24 24"><path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5A2.5 2.5 0 1 1 12 6.5a2.5 2.5 0 0 1 0 5z"/></svg>'
      + ' Buka di Google Maps'
      + '</a>'
      + webBtn
      + igBtn
      + '</div>'
      + '</div>'
      + '</div>';

    modalPanel.classList.add('is-open');
    modalPanel.scrollTop = 0;

    var cBtn = document.getElementById('gmapDetailCloseBtn');
    if (cBtn) cBtn.addEventListener('click', closeModalInfoPanel);
  }

  function closeModalInfoPanel() {
    if (modalPanel) {
      modalPanel.classList.remove('is-open');
      modalPanel.scrollTop = 0;
    }
    if (activeMarkerModal) {
      var prevG = activeMarkerModal._galleryData;
      if (prevG) activeMarkerModal.setIcon(makeMarkerIcon(prevG, false));
      activeMarkerModal = null;
    }
  }

  // Update visual state tombol kategori pills
  function updateActivePill(category) {
    if (!catPillsBar) return;
    var pills = catPillsBar.querySelectorAll('.gmap-pill');
    pills.forEach(function (pill) {
      if (pill.getAttribute('data-category') === category) {
        pill.classList.add('is-active');
        pill.setAttribute('aria-selected', 'true');
        pill.scrollIntoView({ behavior: 'smooth', block: 'nearest', inline: 'center' });
      } else {
        pill.classList.remove('is-active');
        pill.setAttribute('aria-selected', 'false');
      }
    });
  }

  // =========================================================================
  // 6. EVENT LISTENERS FILTER, SEARCH, DRAWER & MODAL
  // =========================================================================

  // Search Input
  if (searchInput) {
    searchInput.addEventListener('input', function (e) {
      var val = e.target.value.trim();
      currentFilter.search = val;
      if (searchClear) searchClear.style.display = val ? 'block' : 'none';
      applyFilters();
    });
  }

  if (searchClear) {
    searchClear.addEventListener('click', function () {
      if (searchInput) {
        searchInput.value = '';
        currentFilter.search = '';
        searchClear.style.display = 'none';
        applyFilters();
        searchInput.focus();
      }
    });
  }

  // Status Filter Select
  if (statusFilter) {
    statusFilter.addEventListener('change', function (e) {
      currentFilter.status = e.target.value;
      applyFilters();
    });
  }

  // Category Filter Select
  if (catFilterSel) {
    catFilterSel.addEventListener('change', function (e) {
      var cat = e.target.value;
      currentFilter.category = cat;
      updateActivePill(cat);
      applyFilters();
    });
  }

  // Category Pills Buttons
  if (catPillsBar) {
    catPillsBar.addEventListener('click', function (e) {
      var pill = e.target.closest('.gmap-pill');
      if (!pill) return;
      var cat = pill.getAttribute('data-category');
      currentFilter.category = cat;
      if (catFilterSel) catFilterSel.value = cat;
      updateActivePill(cat);
      applyFilters();
    });
  }

  // Toggle Drawers
  if (toggleListBtn && listDrawer) {
    toggleListBtn.addEventListener('click', function () {
      var isOpen = listDrawer.classList.toggle('is-open');
      toggleListBtn.classList.toggle('is-active', isOpen);
      if (isOpen && legendDrawer) {
        legendDrawer.classList.remove('is-open');
        if (toggleLegBtn) toggleLegBtn.classList.remove('is-active');
      }
    });
  }

  if (closeListBtn && listDrawer) {
    closeListBtn.addEventListener('click', function () {
      listDrawer.classList.remove('is-open');
      if (toggleListBtn) toggleListBtn.classList.remove('is-active');
    });
  }

  if (toggleLegBtn && legendDrawer) {
    toggleLegBtn.addEventListener('click', function () {
      var isOpen = legendDrawer.classList.toggle('is-open');
      toggleLegBtn.classList.toggle('is-active', isOpen);
      if (isOpen && listDrawer) {
        listDrawer.classList.remove('is-open');
        if (toggleListBtn) toggleListBtn.classList.remove('is-active');
      }
    });
  }

  if (closeLegBtn && legendDrawer) {
    closeLegBtn.addEventListener('click', function () {
      legendDrawer.classList.remove('is-open');
      if (toggleLegBtn) toggleLegBtn.classList.remove('is-active');
    });
  }

  // Open & Close Modal
  function openModal() {
    if (!modal) return;
    modal.classList.add('is-open');
    modal.setAttribute('aria-hidden', 'false');
    document.body.classList.add('gmap-modal-open');
    initModalMap();
    setTimeout(function () {
      if (modalMap) {
        modalMap.invalidateSize();
        // Pusatkan ke area Surabaya
        modalMap.setView([-7.2655, 112.7420], 12);
      }
    }, 400);
    setTimeout(function () { if (modalClose) modalClose.focus(); }, 350);
  }

  function closeModal() {
    if (!modal) return;
    modal.classList.remove('is-open');
    modal.setAttribute('aria-hidden', 'true');
    document.body.classList.remove('gmap-modal-open');
    closeModalInfoPanel();
    if (listDrawer) listDrawer.classList.remove('is-open');
    if (legendDrawer) legendDrawer.classList.remove('is-open');
    if (toggleListBtn) toggleListBtn.classList.remove('is-active');
    if (toggleLegBtn) toggleLegBtn.classList.remove('is-active');
    setTimeout(function () { if (trigger) trigger.focus(); }, 100);
  }

  if (trigger) {
    trigger.addEventListener('click', openModal);
    trigger.addEventListener('keydown', function (e) {
      if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); openModal(); }
    });
  }

  if (modalClose) modalClose.addEventListener('click', closeModal);
  if (modal) {
    modal.addEventListener('click', function (e) {
      if (e.target === modal) closeModal();
    });
  }

  document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape' && modal && modal.classList.contains('is-open')) {
      if (modalPanel && modalPanel.classList.contains('is-open')) {
        closeModalInfoPanel();
      } else if (listDrawer && listDrawer.classList.contains('is-open')) {
        listDrawer.classList.remove('is-open');
        if (toggleListBtn) toggleListBtn.classList.remove('is-active');
      } else if (legendDrawer && legendDrawer.classList.contains('is-open')) {
        legendDrawer.classList.remove('is-open');
        if (toggleLegBtn) toggleLegBtn.classList.remove('is-active');
      } else {
        closeModal();
      }
    }
  });

  // =========================================================================
  // 7. SINKRONISASI DATA DARI API /api/galleries.json
  // =========================================================================
  function loadGalleriesFromApi() {
    fetch('api/galleries.json')
      .then(function (res) {
        if (!res.ok) throw new Error('HTTP ' + res.status);
        return res.json();
      })
      .then(function (json) {
        if (json && json.status === 'success' && Array.isArray(json.data) && json.data.length > 0) {
          allGalleries = json.data;
          renderMiniMapMarkers();
          if (modalInitialized) {
            renderLegend();
            applyFilters();
          }
          var sub = document.getElementById('galleryMapSidebarSubtitle');
          if (sub) sub.textContent = 'Peta Interaktif Â· ' + allGalleries.length + ' Lokasi';
          var cnt = document.getElementById('galleryMapSidebarCounter');
          if (cnt) cnt.textContent = allGalleries.length;
        }
      })
      .catch(function (err) {
        console.info('[gallery-map] Menggunakan data bawaan Surabaya (fallback):', err.message);
      });
  }

  // Jalankan inisialisasi awal
  initMiniMap();
  loadGalleriesFromApi();

}); /* end window.load */

