# Laravel API Starter Template

Template ini adalah titik awal yang solid untuk membangun backend API menggunakan Laravel. Dilengkapi dengan fitur-fitur esensial yang sering dibutuhkan dalam pengembangan aplikasi modern, memungkinkan Anda untuk fokus pada logika bisnis inti.

## ✨ Fitur Utama

- **Otentikasi API**: Menggunakan **Laravel Sanctum** untuk otentikasi berbasis token yang aman dan stateless.
  - Endpoint untuk Register, Login, Logout, Lupa Password, dan Reset Password.
  - Proteksi Rate Limiting pada proses login.
  - Proteksi **Google reCAPTCHA v2** pada endpoint login.
- **Manajemen Pengguna & Peran**: Sistem peran (role) sederhana (`user` dan `admin`) dengan middleware untuk proteksi rute.
- **Integrasi Pembayaran**: Terintegrasi dengan **Midtrans** untuk memproses pembayaran.
  - Pembuatan transaksi dan mendapatkan `snap_token`.
  - Penanganan notifikasi pembayaran melalui *Webhook*.
  - Pengecekan status transaksi secara *real-time* melalui metode *Polling*.
- **Contoh CRUD**: Endpoint CRUD lengkap untuk resource `Product` sebagai contoh implementasi.
- **Struktur Proyek**: Organisasi file yang rapi dan mudah diperluas.

## 🚀 Memulai Proyek

### Prasyarat
- PHP >= 8.2
- Composer
- Database (MySQL, PostgreSQL, dll.)
- Akun Midtrans (untuk fitur pembayaran)
- Kunci Google reCAPTCHA (untuk proteksi login)

### Langkah Instalasi

1.  **Clone repository ini:**
    ```bash
    git clone https://github.com/username/repository-anda.git
    cd repository-anda
    ```

2.  **Install dependensi PHP:**
    ```bash
    composer install
    ```

3.  **Buat file `.env`:**
    Salin file `.env.example` menjadi `.env`.
    ```bash
    cp .env.example .env
    ```

4.  **Generate application key:**
    ```bash
    php artisan key:generate
    ```

5.  **Konfigurasi file `.env`:**
    Buka file `.env` dan sesuaikan variabel berikut:
    - **Database:**
      ```env
      DB_CONNECTION=mysql
      DB_HOST=127.0.0.1
      DB_PORT=3306
      DB_DATABASE=nama_database_anda
      DB_USERNAME=user_database_anda
      DB_PASSWORD=password_anda
      ```
    - **Midtrans:**
      Dapatkan dari dashboard Midtrans Anda.
      ```env
      MIDTRANS_SERVER_KEY=SB-Mid-server-xxxxxxxxxxxxxxxxxxxx
      MIDTRANS_IS_PRODUCTION=false
      ```
    - **Google reCAPTCHA:**
      Dapatkan dari konsol Google reCAPTCHA.
      ```env
      RECAPTCHA_SITE_KEY=kunci_site_anda
      RECAPTCHA_SECRET_KEY=kunci_rahasia_anda
      ```

6.  **Jalankan migrasi database:**
    Perintah ini akan membuat tabel-tabel yang dibutuhkan seperti `users`, `products`, dll.
    ```bash
    php artisan migrate
    ```

7.  **Jalankan server development:**
    ```bash
    php artisan serve
    ```
    Aplikasi Anda sekarang berjalan di `http://127.0.0.1:8000`.

## 📖 Dokumentasi API

Berikut adalah daftar endpoint API yang tersedia.

### Otentikasi

*   `POST /api/register`
    - Mendaftarkan pengguna baru.
    - **Body**: `name`, `email`, `password`, `password_confirmation`.
*   `POST /api/login`
    - Login pengguna dan mendapatkan token.
    - **Body**: `email`, `password`, `g-recaptcha-response`.
*   `POST /api/logout`
    - Logout pengguna dan menghapus token saat ini.
    - **Memerlukan**: Header `Authorization: Bearer <token>`.
*   `POST /api/forgot-password`
    - Mengirim link reset password ke email pengguna.
    - **Body**: `email`.
*   `POST /api/reset-password`
    - Mereset password pengguna dengan token yang valid.
    - **Body**: `token`, `email`, `password`, `password_confirmation`.

### Pengguna

*   `GET /api/user`
    - Mendapatkan data pengguna yang sedang login.
    - **Memerlukan**: Header `Authorization: Bearer <token>`.

### Pembayaran (Midtrans)

*   `POST /api/payment/create-transaction`
    - Membuat transaksi baru dan mendapatkan `snap_token` dari Midtrans.
    - **Memerlukan**: Header `Authorization: Bearer <token>`.
    - **Body**: `order_id` (unik), `amount`.
*   `GET /api/payment/status/{order_id}`
    - Memeriksa status transaksi secara real-time (polling).
    - **Memerlukan**: Header `Authorization: Bearer <token>`.
*   `POST /api/payment/notification`
    - Endpoint **Webhook** untuk menerima notifikasi status dari Midtrans. Endpoint ini tidak memerlukan otentikasi dan harus diatur di dashboard Midtrans Anda.

### Produk (Contoh CRUD)

*   `GET /api/products`
    - Menampilkan daftar produk dengan paginasi.
*   `POST /api/products`
    - Membuat produk baru.
    - **Body**: `name`, `description` (opsional), `price`.
*   `GET /api/products/{product}`
    - Menampilkan detail satu produk.
*   `PUT/PATCH /api/products/{product}`
    - Memperbarui data produk.
*   `DELETE /api/products/{product}`
    - Menghapus produk.

> **Catatan**: Endpoint produk di atas belum diproteksi. Untuk membatasi akses (misalnya hanya untuk admin), pindahkan rute-rute tersebut ke dalam grup middleware `role:admin` di `routes/api.php`.

### Rute Admin

*   `GET /api/admin/dashboard`
    - Contoh endpoint yang hanya bisa diakses oleh pengguna dengan peran `admin`.
    - **Memerlukan**: Header `Authorization: Bearer <token>` dari user admin.

