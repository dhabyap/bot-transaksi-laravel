# Bot Transaksi Laravel

Sistem ini adalah sebuah aplikasi web yang dibangun menggunakan **Laravel** (versi 10/11) dan bertindak sebagai Bot/Sistem Pencatatan Transaksi otomatis.

---

## 🚀 Cara Penggunaan (Sebagai Pengguna / *End-User*)
Karena sistem ini umumnya dihubungkan dengan bot (seperti Telegram), berikut adalah gambaran umum cara pengguna memahami dan memakai sistem ini:
1. **Akses Bot**: Pengguna berinteraksi dengan sistem ini melalui chat bot.
2. **Perintah / Menu Utama**: Pengguna bisa menggunakan *slash commands* (contoh: `/start`, `/help`) untuk memulai.
3. **Pencatatan Transaksi**: Pengguna dapat mengirim pesan berupa detail transaksi yang kemudian direkam secara otomatis ke dalam database sistem ini.
4. **Dashboard Panel**: Jika ada akses ke Web (melalui browser), pengguna bisa melihat rekapitulasi, dashboard, dan laporan transaksi yang lebih lengkap.
*(Catatan: Detail perintah ini bisa disesuaikan nanti seiring fiturnya ditambahkan ke dalam sistem).*

---

## 🛠 Persiapan & Instalasi (Sebagai Developer)

Bagi seorang developer yang ingin berkontribusi, memodifikasi, atau menjalankan proyek ini di *local machine*, ikuti langkah-langkah berikut:

### 1. Requirements (Persyaratan Sistem)
- PHP >= 8.1
- Composer
- Node.js & NPM (jika mengelola aset frontend)
- Database server (MySQL / PostgreSQL / SQLite)

### 2. Langkah-Langkah Instalasi

1. **Clone Repository (Atau gunakan yang sudah ada)**
   ```bash
   git clone https://github.com/dhabyap/bot-transaksi-laravel.git
   cd bot-transaksi-laravel
   ```

2. **Install Dependencies PHP Engine & NodeModules**
   ```bash
   composer install
   npm install
   ```

3. **Duplikasi File Environment**
   Gandakan file `.env.example` dan ubah namanya menjadi `.env`:
   ```bash
   cp .env.example .env
   ```
   Kemudian, buka file `.env` di text editor dan sesuaikan konfigurasinya:
   - Pengaturan Database (`DB_CONNECTION`, `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`).
   - Token & Webhook URL Bot Telegram (Misal disiapkan untuk bot: `TELEGRAM_BOT_TOKEN`, dsb).

4. **Generate Application Key**
   Buat *encryption key* untuk *security* Laravel:
   ```bash
   php artisan key:generate
   ```

5. **Jalankan Migrasi Database**
   Buat struktur tabel ke dalam database Anda:
   ```bash
   php artisan migrate
   ```

6. **Jalankan Aplikasi Web Serve**
   Jalankan server bawaan Laravel:
   ```bash
   php artisan serve
   ```
   *Opsional: Jika ada aset *frontend*, jalankan vite / laravel mix di terminal terpisah:*
   ```bash
   npm run dev
   ```

7. **Konfigurasi Webhook (Local Testing)**
   Untuk menguji fungsionalitas Bot (contoh Bot Telegram), Anda perlu menghubungkan Webhook ke aplikasi lokal. Anda bisa memanfaatkan *tunnelling* seperti **Ngrok** atau **Cloudflare Tunnels**.
   ```bash
   ngrok http 8000
   ```
   Lalu daftarkan URL Ngrok tersebut ke Webhook Bot Anda.

---

## 📚 Struktur & Alur Kerja Singkat (Untuk Developer)
- **`routes/web.php` & `routes/api.php`**: Di bawah ini tempat mendaftarkan *Route Webhook* dan juga route panel halaman Admin.
- **`app/Http/Controllers`**: Berisi *Logic Flow* dari balasan chat bot serta manipulasi data dari database.
- **`app/Models`**: Representasi interaksi ke Database Tabel Transaksi, User / Pelanggan Anda.

Silakan update README ini seiring dengan berjalannya proses modifikasi dan penambahan fitur yang dilakukan pada project secara berkesinambungan!
