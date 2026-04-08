# AI Finance Telegram Bot (Laravel Edition)

Bot Telegram berbasis AI untuk pencatatan transaksi keuangan otomatis dan pelaporan analitik, selaras dengan skema database legacy Python.

## ✨ Fitur Utama
- **AI-Powered Natural Language Processing**: Catat transaksi hanya dengan kata-kata (misal: "Beli kopi 15rb").
- **Dynamic AI Drivers**: Menggunakan Groq (Llama 3) dan Google Gemini dengan sistem *auto-fallback*.
- **Temporal Awareness**: Memahami konteks waktu (misal: "Kemarin gajian 5jt").
- **Analytical Reporting**: Laporan harian, mingguan, dan bulanan dengan ringkasan pemasukan, pengeluaran, dan saldo bersih.
- **Legacy Database Alignment**: Skema database yang sepenuhnya kompatibel dengan versi Python sebelumnya (`user_id`, `nominal`, `tipe`, `kategori`, `item`).
- **Activity & Chat Logging**: Pemantauan statistik pengguna dan riwayat pesan secara otomatis.

## 🚀 Persyaratan Sistem
- PHP 8.1+
- MySQL/MariaDB
- Composer

## 🛠️ Instalasi Cepat
1. Clone repositori ini.
2. Jalankan `composer install`.
3. Copy `.env.example` ke `.env` dan isi API Key Anda.
4. Jalankan migrasi: `php artisan migrate`.
5. Daftarkan webhook Telegram Anda.

## 📂 Struktur Dokumentasi
- [Panduan Migrasi dari Python](docs/MIGRATION_GUIDE.md)
- [Panduan Deployment cPanel](docs/CPANEL_DEPLOYMENT.md)

## 🤖 Perintah Bot Dasar
- `/start`: Memulai interaksi dan mendaftarkan profil Anda.
- `/help`: Menampilkan panduan penggunaan.
- `/rekap`: Menampilkan laporan keuangan hari ini secara instan.
- Chat bebas: Untuk mencatat transaksi atau menanyakan laporan (misal: "Berapa saldo saya bulan ini?").

---
*Dikembangkan dengan Laravel 10.*
