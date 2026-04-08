# Project Epics & Architecture Draft: AI Transaction Bot

Dokumen ini merangkum *High-Level Overview* (HLO) dan *Epics* (Issues besar) dari arsitektur sistem Bot Telegram untuk pencatatan transaksi menggunakan AI. Pendekatan ini berfokus pada kapabilitas sistem, aliran data, dan fondasi aplikasi dibanding detail implementasi *coding*.

## Epic 1: Telegram Ingestion & Interface Layer
**Objective:** Membangun "Pintu Masuk" yang aman dan responsif untuk interaksi dengan pengguna via Telegram.
- [ ] **Webhook Gateway**: Menyediakan *endpoint* API berkecepatan tinggi pada Laravel untuk menerima dan mem-parsing payload dari Telegram secara sinkron.
- [ ] **Authentication & Security Guard**: Merancang mekanisme otentikasi untuk menentukan apakah bot ini bersifat *Single-Tenant* (hanya Anda yang boleh pakai) atau *Multi-Tenant* (publik). Bot harus mengabaikan payload dari *unauthorized sources*.
- [ ] **Command vs. NLP Router**: Middleware atau mekanisme *Routing* yang bisa memisahkan pesan mana yang merupakan "Native Command" (seperti `/start`, `/help`, `/report`) dan mana yang merupakan "Natural Text" untuk dieksekusi oleh AI.

## Epic 2: AI-Powered NLP Engine (Data Extraction)
**Objective:** Menggunakan LLM (Large Language Model) untuk mengekstraksi instruksi bebas menjadi struktur data baku.
- [ ] **LLM Integration Strategy**: Menghubungkan Laravel dengan Provider AI (seperti Google Gemini / OpenAI) menggunakan HTTP client atau SDK, diposisikan secara terisolasi sebagai satu unit *Service*.
- [ ] **Prompt Engineering & Context Injection**: Mengamankan arsitektur dari *hallucination* dengan mendesain Prompt yang memaksa AI selalu mengembalikan skema JSON statis yang terprediksi (tipe transaksi, besaran uang, dan kategori).
- [ ] **Data Sanitization & Fallback Loop**: Lapisan pertahanan jika output AI rusak (JSON *malformed*). Sistem harus dengan gracefully menolak input dan meminta user mengulangi pesan (*"Format chat tidak dikenali, coba lagi"*).

## Epic 3: Core Transaction Data Domain
**Objective:** Menyimpan dan mengelola siklus hidup data finansial secara presisi dan terstruktur di *Database Layer*.
- [ ] **Financial Schema Architecture**: Merancang arsitektur database untuk transaksi yang tangguh untuk query dan analitik agregat (*summing*, *filtering by range*).
- [ ] **Transaction Business Logic**: Membangun *Service Layer* aplikasi untuk mengeksekusi operasi finansial inti secara aman tanpa meletakkannya langsung di *Controller*.
- [ ] **System Extensibility**: Memastikan tabel dirancang sedemikian rupa agar di masa depan dapat mendukung multicurrency atau tag/kategori kustom secara dinamis per-pengguna.

## Epic 4: Feedback Loop & Insight Reporting
**Objective:** Memberikan *User Experience* (UX) yang canggih dimana bot menjadi asisten pintar rekapitulasi data.
- [ ] **Real-time Acknowledgment**: Respons instan setiap sebuah transaksi berhasil diamankan di database (Contoh: *"✅ Dicatat: Rp 20.000 ke kategori Makanan"*).
- [ ] **On-Demand Analytical Queries**: Kemampuan bot untuk merespons permintaan insight dari user di chat. (Contoh: *"Berapa pengeluaran saya minggu ini?"* -> Diubah menjadi database filter -> Dikirim balik sebagai laporan teks).
- [ ] **(Optional) Scheduled Aggregation**: *Cron Job* harian yang mendorong notifikasi rekap (misal: pengingat sisa budget atau saldo harian) menggunakan *Task Scheduling* bawaan Laravel.

---
**Status Dokumen**: *Draft - Dalam Tahapan Diskusi*  
*Issue tracker ini dirancang agar mudah diekstrak menjadi tiket/issues ke GitHub saat development skala penuh dimulai.*
