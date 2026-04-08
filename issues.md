# AI Transaction Bot - Legacy Migration & Expansion Roadmap

Dokumen ini melacak integrasi Laravel dengan skema database legacy Python dan pengembangan fitur lanjutan.

## Epic 6: Legacy Database Alignment (Priority: High)
*Tujuan: Mengubah skema Laravel agar serasi dengan database Python yang sudah ada.*

- [ ] **Schema Renaming**: Mengubah kolom `transactions` (telegram_user_id -> user_id, amount -> nominal, type -> tipe, category -> kategori, description -> item).
- [ ] **Data Type Sync**: Menyesuaikan tipe `nominal` menjadi `DOUBLE`.
- [ ] **Model Refactoring**: Update Eloquent Models agar sinkron dengan penamaan kolom baru.
- [ ] **Verification**: Memastikan logic AI extraction tetap bekerja dengan mapping kolom baru.

## Epic 7: User Tracking & Chat Logging
*Tujuan: Memonitor aktivitas pengguna dan menyimpan jejak percakapan.*

- [ ] **User Table Implementation**: Membuat tabel `bot_users` sesuai legacy (`first_seen`, `last_active`, `message_count`).
- [ ] **Chat Logs Table**: Membuat tabel `chat_logs` untuk menyimpan data mentah pesan teks dari Telegram.
- [ ] **Activity Middleware**: Otomatis memperbarui `last_active` dan menambah `message_count` setiap kali pengguna berinteraksi.

## Epic 8: Inventory Management System
*Tujuan: Implementasi fitur manajemen stok barang.*

- [ ] **Inventory Schema**: Membuat tabel `inventory` (`nama_barang`, `kuantitas`, `status`).
- [ ] **AI Inventory Extraction**: Update NLP Engine agar bisa mendeteksi perintah inventaris (misal: "Stok beras bertambah 5kg" atau "Barang masuk: Kopi 10 box").
- [ ] **Inventory Insight**: Fitur laporan untuk melihat daftar stok saat ini via perintah bot.

## Epic 9: System Hardening & Cleanup
*Tujuan: Optimasi final dan migrasi server.*

- [ ] **Database Migration Plan**: Dokumentasi langkah-langkah migrasi data dari database SQL Python lama ke Laravel.
- [ ] **Environment Audit**: Memastikan semua API Key dan konfigurasi cPanel siap untuk tahap produksi.
