## Epic 11: Webhook Route Enhancement
_Tujuan: Memperbaiki user experience saat mengakses route webhook via browser._

- [ ] **Handle GET Method on Webhook**: Tambahkan fallback route `GET /webhook/telegram` untuk menampilkan pesan "Telegram Webhook is Active" agar tidak muncul error 405 (Method Not Supported) saat tidak sengaja diakses via browser.
