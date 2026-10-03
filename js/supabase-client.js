/**
 * supabase-client.js — Jejak Waktu
 * Konfigurasi dan inisialisasi Supabase client (browser-compatible, no build step).
 *
 * ===== CARA KONFIGURASI =====
 * Karena proyek ini adalah static HTML (bukan Next.js/Node.js), nilai env
 * diinjeksi langsung di sini. Ganti dua nilai di bawah ini dengan credentials
 * Supabase project Anda (Settings → API → Project URL & anon/public key).
 *
 * Nilai ini aman untuk diletakkan di frontend karena:
 *  1. Row Level Security (RLS) Supabase melindungi data di sisi server.
 *  2. Anon key hanya memiliki hak akses yang diizinkan oleh policy RLS.
 *
 * Jika ke depan proyek di-port ke Next.js/Vite, ganti kedua konstanta ini:
 *   const SUPABASE_URL  = process.env.NEXT_PUBLIC_SUPABASE_URL;
 *   const SUPABASE_KEY  = process.env.NEXT_PUBLIC_SUPABASE_ANON_KEY;
 */

'use strict';

// ======================================================
// KONFIGURASI SUPABASE — Menggunakan process.env atau window
// ======================================================
const SUPABASE_URL = (typeof process !== 'undefined' && process.env && process.env.NEXT_PUBLIC_SUPABASE_URL)
    || (typeof window !== 'undefined' && window.NEXT_PUBLIC_SUPABASE_URL)
    || 'https://YOUR_PROJECT_ID.supabase.co';

const SUPABASE_KEY = (typeof process !== 'undefined' && process.env && process.env.NEXT_PUBLIC_SUPABASE_ANON_KEY)
    || (typeof window !== 'undefined' && window.NEXT_PUBLIC_SUPABASE_ANON_KEY)
    || 'YOUR_SUPABASE_ANON_KEY';

// ======================================================
// INISIALISASI CLIENT (menggunakan Supabase JS v2 via CDN)
// ======================================================
let _supabaseClient = null;

/**
 * Mengembalikan instance Supabase client (singleton).
 * Akan diinisialisasi saat pertama kali dipanggil.
 */
function getSupabaseClient() {
    if (_supabaseClient) return _supabaseClient;

    const sbLib = (typeof window !== 'undefined') ? (window.__supabase || window.supabase) : null;
    if (!sbLib) {
        console.error(
            '[Supabase] Library belum dimuat. Pastikan CDN script ada sebelum supabase-client.js.'
        );
        return null;
    }

    const { createClient } = sbLib;
    _supabaseClient = createClient(SUPABASE_URL, SUPABASE_KEY, {
        auth: {
            persistSession: true,
            autoRefreshToken: true,
            detectSessionInUrl: true,
            storageKey: 'jejak_waktu_supabase_session'
        },
        global: {
            headers: { 'x-client-info': 'jejak-waktu/1.0' }
        }
    });

    console.info('[Supabase] Client berhasil diinisialisasi ->', SUPABASE_URL);
    return _supabaseClient;
}

/**
 * Cek apakah Supabase URL sudah dikonfigurasi (bukan placeholder).
 */
function isSupabaseConfigured() {
    return (
        SUPABASE_URL !== 'https://YOUR_PROJECT_ID.supabase.co' &&
        SUPABASE_KEY !== 'YOUR_SUPABASE_ANON_KEY' &&
        SUPABASE_URL.startsWith('https://') &&
        SUPABASE_KEY.length > 20
    );
}

// Expose ke global scope agar bisa digunakan di semua script
window.getSupabaseClient    = getSupabaseClient;
window.isSupabaseConfigured = isSupabaseConfigured;
