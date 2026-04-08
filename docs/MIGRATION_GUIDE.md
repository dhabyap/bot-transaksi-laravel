# Legacy Python to Laravel Migration Guide

Dokumen ini menjelaskan cara memindahkan data dari database Bot Python Anda ke sistem Laravel yang baru.

## 1. Pemetaan Struktur Tabel

| Tabel Legacy (Python) | Tabel Baru (Laravel) | Keterangan |
|---|---|---|
| `transactions` | `transactions` | Nama tabel tetap, kolom disesuaikan. |
| `users` | `bot_users` | Nama tabel diubah untuk menghindari konflik dengan Laravel Auth. |
| `chat_logs` | `chat_logs` | Struktur identik. |
| `inventory` | `inventory` | (Opsional) Jika fitur inventaris diaktifkan. |

## 2. Pemetaan Kolom Transaksi

Lakukan SQL `INSERT INTO ... SELECT` untuk memigrasi data transaksi:

```sql
INSERT INTO laravel_db.transactions 
(user_id, tipe, nominal, kategori, item, timestamp, created_at, updated_at)
SELECT 
user_id, tipe, nominal, kategori, item, timestamp, timestamp, timestamp
FROM python_db.transactions;
```

## 3. Pemetaan Kolom Pengguna (Users)

```sql
INSERT INTO laravel_db.bot_users 
(user_id, first_name, last_name, username, language_code, first_seen, last_active, message_count, created_at, updated_at)
SELECT 
user_id, first_name, last_name, username, language_code, first_seen, last_active, message_count, first_seen, last_active
FROM python_db.users;
```

## 4. Tips Pasca Migrasi
1. **Reset Auto-Increment**: Pastikan untuk melakukan reset auto-increment pada tabel `transactions` jika ID dimulai dari awal lagi.
2. **Validasi Tipe Data**: Pastikan kolom `nominal` di database target sudah bertipe `DOUBLE` atau `DECIMAL(15,2)`.
3. **Backup**: Selalu lakukan backup database lama sebelum menjalankan perintah migrasi ini.
