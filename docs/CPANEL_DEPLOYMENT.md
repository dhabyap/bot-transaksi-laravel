# Panduan Deployment di cPanel (Shared Hosting)

Bot ini dirancang untuk berjalan optimal di shared hosting cPanel dengan Laravel 10.

## 1. Persiapan File
1. Kompres seluruh folder proyek (kecuali `node_modules`, `vendor`, dan `.git`) menjadi `.zip`.
2. Upload ke cPanel (disarankan di luar folder `public_html` untuk keamanan).
3. Ekstrak file zip tersebut.

## 2. Pengaturan Public Folder
Buatlah symbolic link atau arahkan domain/subdomain Anda ke folder `public/` di dalam proyek Laravel.
Jika menggunakan `public_html`, Anda bisa memindahkan isi folder `public/` ke `public_html` dan menyesuaikan file `index.php`.

## 3. Konfigurasi .env
Sesuaikan variabel berikut di file `.env` hosting:
```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://domain-anda.com

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=nama_db_cpanel
DB_USERNAME=user_db_cpanel
DB_PASSWORD=password_db_cpanel

TELEGRAM_BOT_TOKEN=token_anda
TELEGRAM_AUTHORIZED_IDS=123456,789012
AI_DRIVER_PRIORITY=groq,gemini
GROQ_API_KEY=key_anda
GEMINI_API_KEY=key_anda
```

## 4. Instalasi Dependensi
Jalankan perintah berikut via SSH di folder proyek:
```bash
composer install --no-dev --optimize-autoloader
php artisan key:generate
php artisan migrate --force
php artisan optimize
```

## 5. Registrasi Webhook Telegram
Setelah domain aktif, daftarkan URL webhook Anda ke Telegram:
`https://api.telegram.org/bot<TOKEN>/setWebhook?url=https://domain-anda.com/api/webhook/telegram`

## 6. Troubleshoot
- **Permission Error**: Pastikan folder `storage` dan `bootstrap/cache` memiliki izin tulis (chmod 775 atau 755).
- **PHP Version**: Gunakan PHP versi 8.1 atau lebih tinggi sesuai persyaratan Laravel 10.
