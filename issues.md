# Daftar Tugas Deployment & Finalisasi Bot Laravel (Untuk Programmer / AI)

Dokumen ini berisi panduan dan daftar tugas (TODO list) untuk memastikan Bot Telegram berbasis Laravel ini bisa berjalan "normal" sepenuhnya seperti versi Python sebelumnya.

## Epic 1: Konfigurasi Database
_Tujuan: Memastikan tabel-tabel yang dibutuhkan bot sudah tersedia di database._

- [x] **Setup `.env` Database**: Koneksi database sudah dikonfigurasi (`bot_transaksi_laravel`).
- [x] **Jalankan Migrasi**: Perintah `php artisan migrate` sudah dijalankan dan statusnya `Ran`.
- [x] **Validasi Skema**: Struktur tabel `transactions` sudah sesuai dengan legacy python (`nominal`, `kategori`, `tipe`).

## Epic 2: Integrasi Kecerdasan Buatan (AI)
_Tujuan: Memastikan NLP (Natural Language Processing) dapat memilah pesan chat menjadi data transaksi._

- [x] **Setup API Key AI**: `GROQ_API_KEY` dan `GEMINI_API_KEY` sudah tersedia di `.env`.
- [x] **Ujian AI Router**: Bot berhasil memproses pesan natural language via long polling.

## Epic 3: Deployment Polling di Background (Production)
_Tujuan: Bot harus dapat menyala 24/7 tanpa perlu terminal terus dibuka secara manual._

Bila tidak menggunakan Webhook (karena tidak ada HTTPS/hosting), bot menggunakan metode Long Polling.
- [x] **Instalasi PM2 / Supervisor**: Gunakan PM2 (berbasis Node.js) atau Supervisor (bawaan Linux) untuk mengelola proses.
- [x] **Setup Eksekusi Background (PM2)**:
  Buat file `ecosystem.config.js` atau gunakan perintah langsung:
  ```bash
  pm2 start "php artisan telegram:poll" --name "laravel-bot"
  pm2 save
  ```
- [x] **Setup Eksekusi Background (Windows/Linux alternatif)**: Command `php artisan telegram:poll` tersedia via PR #23. Jalankan di background via PM2 atau terminal tmux/screen.

## Epic 4: Finalisasi Fitur Respons
_Tujuan: Menyempurnakan balasan bot jika terjadi kesalahan sistem._

- [x] **Error Catching AI**: Pada `TelegramWebhookController.php`, jika AI mengalami rate limit (429), bot kini mengirim pesan: *"Maaf, sistem AI sedang sibuk. Silakan coba beberapa saat lagi."*
- [x] **Notifikasi Error**: Log error sudah dicatat ke sistem Laravel Log (`storage/logs/laravel.log`) untuk audit admin.

---
**Catatan untuk Developer/Model AI:**
Kerjakan tugas di atas secara berurutan mulai dari Database, lalu AI, lalu Deployment Background. Tandai (centang) kotak `[x]` setiap kali satu tugas berhasil diselesaikan.
