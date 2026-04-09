## Epic 10: Bot Configuration & Health Check

_Tujuan: Konfigurasi token, setup webhook, dan validasi Telegram API._

- [ ] **Environment Variables**: Mengisi `TELEGRAM_BOT_TOKEN`, `GROQ_API_KEY`, dan `GEMINI_API_KEY` di dalam file `.env`.
- [/] **Webhook Setup**: Gunakan `php artisan telegram:manage set-webhook` setelah mengisi .env dan menset APP_URL ke domain publik (ngrok).
- [/] **API Validation**: Jalankan `php artisan telegram:manage info` dan `status` untuk memvalidasi bot.
- [ ] **End-to-End Testing**: Melakukan test chat langsung ke bot Telegram untuk memvalidasi alur dari Telegram -> Webhook Laravel -> AI Engine -> Telegram.
