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
5. [Setup Webhook Telegram](#-setup-webhook-telegram).
6. [Cek Status Bot](#-cek-status-bot).

## 🔗 Setup Webhook Telegram

Bot ini menggunakan metode **Webhook** untuk menerima pesan. Anda harus mendaftarkan URL aplikasi Anda ke Telegram agar Bot bisa merespon.

### Cara 1: Menggunakan Artisan (Direkomendasikan)
Setelah mengisi `TELEGRAM_BOT_TOKEN` di `.env`, jalankan perintah berikut di terminal:
```bash
# Otomatis menggunakan APP_URL dari .env
php artisan telegram:manage set-webhook
```

### Cara 2: Manual via Browser
Buka URL berikut di browser Anda (ganti `<TOKEN>` dan `<DOMAIN>`):
`https://api.telegram.org/bot<TOKEN>/setWebhook?url=https://<DOMAIN>/api/webhook/telegram`

---

## 🚦 Cek Status Bot

Untuk memastikan Bot dan semua API (Groq/Gemini/Database) berjalan dengan baik, Anda bisa mengeceknya melalui:

1. **Terminal**:
   ```bash
   php artisan telegram:manage info
   php artisan telegram:manage status
   ```

2. **Browser (API Endpoint)**:
   Buka `https://domain-anda.com/api/status` untuk melihat laporan kesehatan sistem dalam format JSON.

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
