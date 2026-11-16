# PesanKita — Simple Messaging Application (CPK 2, 3, 4)

Proyek aplikasi pengirim pesan sederhana untuk Praktikum Pemrograman Berbasis Platform (PBP) yang dibangun dengan **arsitektur Pure Native** (HTML, CSS murni, JavaScript murni, dan PHP murni) serta pemisahan tegas antara `frontend/` dan `backend/`.

## Struktur Proyek

```text
├── frontend/
│   ├── index.html          # Halaman autentikasi & dashboard kontak
│   ├── css/style.css       # Pure CSS tanpa framework
│   └── js/
│       ├── api.js          # Fetch wrapper (JSON + Bearer token)
│       └── app.js          # Logika UI autentikasi & CRUD kontak
├── backend/
│   ├── config/
│   │   ├── config.php      # Konfigurasi DB & secret token
│   │   └── database.php    # Koneksi PDO MariaDB/MySQL
│   ├── helpers/
│   │   ├── response.php    # CORS, parser JSON body, & response JSON
│   │   ├── auth.php        # Token HMAC-SHA256 native & middleware auth
│   │   └── contact_validator.php
│   ├── database/
│   │   └── schema.sql      # Skema tabel users & contacts
│   └── api/
│       ├── auth/           # register.php, login.php, me.php, delete.php
│       └── contacts/       # index.php, create.php, update.php, delete.php
└── docs/
    ├── work-log.md         # Catatan durasi pengerjaan harian
    └── ai-usage.md         # Deklarasi penggunaan Generative AI
```

## Cara Menjalankan Lokal

1. **Siapkan Database (MariaDB / MySQL)**:
   - Jalankan MariaDB/MySQL (misalnya melalui Laragon atau XAMPP).
   - Impor file `backend/database/schema.sql` ke database server Anda.
   - Jika username/password database bukan `root` tanpa password, salin atau buat file `backend/config/config.local.php`:
     ```php
     <?php
     define('DB_HOST', '127.0.0.1');
     define('DB_PORT', '3306');
     define('DB_NAME', 'cpk_messaging');
     define('DB_USER', 'root');
     define('DB_PASS', '');
     ```

2. **Jalankan Backend PHP**:
   Dari folder root proyek:
   ```bash
   php -S localhost:8000 -t backend
   ```
   *(Atau arahkan virtual host Laragon/XAMPP ke folder `backend` dan sesuaikan `pesankita_api_base` di `localStorage` jika URL berbeda dari `http://localhost:8000/api`.)*

3. **Jalankan Frontend**:
   Dari folder root proyek (di terminal terpisah):
   ```bash
   php -S localhost:5500 -t frontend
   ```
   Lalu buka `http://localhost:5500` di Chrome atau Firefox.

## Daftar Endpoint API (Kriteria 2)

| Method | Endpoint | Deskripsi | Auth |
| --- | --- | --- | --- |
| `POST` | `/api/auth/register.php` | Registrasi akun baru | Tidak |
| `POST` | `/api/auth/login.php` | Login dan mendapatkan token | Tidak |
| `GET` | `/api/auth/me.php` | Ambil profil pengguna aktif | Bearer |
| `DELETE` | `/api/auth/delete.php` | Hapus akun beserta kontaknya | Bearer |
| `GET` | `/api/contacts/index.php` | Ambil daftar kontak (opsional `?q=`) | Bearer |
| `POST` | `/api/contacts/create.php` | Tambah kontak baru | Bearer |
| `PUT` | `/api/contacts/update.php` | Perbarui kontak milik user | Bearer |
| `DELETE` | `/api/contacts/delete.php` | Hapus kontak milik user | Bearer |
