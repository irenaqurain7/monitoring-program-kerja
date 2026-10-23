<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

## Monitoring Program Kerja

Fondasi tahap ini menggunakan Laravel 10, PHP 8.1, MySQL, Blade, dan Bootstrap 5 CDN. Tidak diperlukan Node.js untuk menjalankan antarmuka.

### Persiapan database lokal

1. Jalankan MySQL dari Laragon.
2. Di phpMyAdmin, buat database baru bernama `monitoring_program_kerja_dev` dengan collation `utf8mb4_unicode_ci`. Database ini terpisah dari database lain yang sudah ada.
3. Pastikan `.env` menunjuk ke database baru tersebut (`DB_DATABASE=monitoring_program_kerja_dev`) dan sesuaikan host, nama pengguna, serta kata sandi lokal bila perlu. Jangan menaruh kredensial di kode atau membagikan berkas `.env`.
4. Dari direktori proyek, jalankan:

   ```powershell
   php artisan config:clear
   php artisan migrate --seed
   ```

   Perintah ini hanya menerapkan migration yang belum berjalan dan mengisi enam kampus beserta enam unit awal pada masing-masing kampus. Jangan gunakan `migrate:fresh` atau `db:wipe`.

### Membuat Admin Sistem pertama

Jalankan `php artisan app:create-system-admin`, lalu masukkan nama, email, nama pengguna, dan kata sandi minimal 6 karakter pada prompt tersembunyi. Kata sandi diminta dua kali, di-hash oleh Laravel, dan tidak menjadi argumen perintah atau nilai tetap di kode. Perintah hanya membuat Admin Sistem pertama dan menolak membuat akun kedua. Tidak ada pendaftaran publik.

### Login dengan nama pengguna dan kampus

Form login meminta nama pengguna, kata sandi, dan kampus. Daftar kampus dimuat dari database. Kepala Kampus harus memilih kampus penugasannya; Kepala Unit dan Anggota Unit harus memilih kampus yang terkait dengan unit penugasannya. Admin Sistem memilih opsi **Admin Sistem (tanpa akses kampus)** dan tetap tidak dapat mengakses data organisasi.

Migration `2026_10_22_000004_add_username_to_users_table` menambahkan username unik tanpa menghapus akun. Untuk akun lama, username dibuat dari bagian email sebelum `@` (dinormalisasi ke huruf kecil; jika nama tersebut sudah dipakai, ditambahkan akhiran angka). Setelah `php artisan migrate --seed`, periksa username awal pada tabel `users`; Anda dapat menggunakannya untuk masuk. Migration tidak mengubah email maupun kata sandi.

### Manajemen pengguna

Route `manajemen-pengguna` hanya tersedia bagi Admin Sistem, Kepala Kampus, dan Kepala Unit. Admin Sistem mengelola akun; Kepala Kampus dibatasi ke Kepala Unit/Anggota Unit pada kampus penugasannya; Kepala Unit dibatasi ke Anggota Unit pada unitnya. Anggota Unit tidak mendapat akses ke manajemen pengguna. Akun sendiri boleh memperbarui profil dan kata sandi, tetapi tidak boleh mengubah peran atau penempatan sendiri.

Migration `2026_10_22_000005_add_is_active_to_users_table` menambahkan status aktif dengan nilai awal aktif untuk akun yang sudah ada. Akun nonaktif tidak dapat login; sesi yang sudah ada dikeluarkan saat mengakses route terlindungi berikutnya. Admin Sistem aktif terakhir tidak dapat dinonaktifkan atau diturunkan perannya.

Setelah memastikan database aktif adalah database pengembangan dan membuat backup, terapkan migration yang belum berjalan dengan `php artisan migrate`. Migration ini tidak menghapus atau membuat ulang tabel maupun akun.

### Menjalankan aplikasi dan pengujian

```powershell
php artisan serve
```

Untuk pengujian otomatis, konfigurasi PHPUnit menggunakan SQLite dalam memori agar database MySQL pengembangan tidak disentuh. Bila ekstensi SQLite PHP belum aktif pada Laragon, aktifkan `extension=pdo_sqlite` pada `php.ini`, lalu jalankan:

```powershell
php artisan test
```

Sebagai alternatif untuk mengaktifkan ekstensi hanya pada proses pengujian saat ini:

```powershell
php -d extension=php_pdo_sqlite.dll vendor\bin\phpunit
```

Untuk lingkungan produksi, gunakan `APP_ENV=production` dan `APP_DEBUG=false`.

### Fondasi yang tersedia

- Login dan logout berbasis session Laravel; tidak ada pendaftaran publik.
- Peran: Admin Sistem, Kepala Kampus, Kepala Unit, dan Anggota Unit.
- Akses kampus/unit diperiksa oleh policy dan middleware di server.
- Dashboard awal sesuai peran; modul program kerja dan pelaporan belum dibuat.

## About Laravel

Laravel is a web application framework with expressive, elegant syntax. We believe development must be an enjoyable and creative experience to be truly fulfilling. Laravel takes the pain out of development by easing common tasks used in many web projects, such as:

- [Simple, fast routing engine](https://laravel.com/docs/routing).
- [Powerful dependency injection container](https://laravel.com/docs/container).
- Multiple back-ends for [session](https://laravel.com/docs/session) and [cache](https://laravel.com/docs/cache) storage.
- Expressive, intuitive [database ORM](https://laravel.com/docs/eloquent).
- Database agnostic [schema migrations](https://laravel.com/docs/migrations).
- [Robust background job processing](https://laravel.com/docs/queues).
- [Real-time event broadcasting](https://laravel.com/docs/broadcasting).

Laravel is accessible, powerful, and provides tools required for large, robust applications.

## Learning Laravel

Laravel has the most extensive and thorough [documentation](https://laravel.com/docs) and video tutorial library of all modern web application frameworks, making it a breeze to get started with the framework.

You may also try the [Laravel Bootcamp](https://bootcamp.laravel.com), where you will be guided through building a modern Laravel application from scratch.

If you don't feel like reading, [Laracasts](https://laracasts.com) can help. Laracasts contains thousands of video tutorials on a range of topics including Laravel, modern PHP, unit testing, and JavaScript. Boost your skills by digging into our comprehensive video library.

## Laravel Sponsors

We would like to extend our thanks to the following sponsors for funding Laravel development. If you are interested in becoming a sponsor, please visit the [Laravel Partners program](https://partners.laravel.com).

### Premium Partners

- **[Vehikl](https://vehikl.com/)**
- **[Tighten Co.](https://tighten.co)**
- **[WebReinvent](https://webreinvent.com/)**
- **[Kirschbaum Development Group](https://kirschbaumdevelopment.com)**
- **[64 Robots](https://64robots.com)**
- **[Curotec](https://www.curotec.com/services/technologies/laravel/)**
- **[Cyber-Duck](https://cyber-duck.co.uk)**
- **[DevSquad](https://devsquad.com/hire-laravel-developers)**
- **[Jump24](https://jump24.co.uk)**
- **[Redberry](https://redberry.international/laravel/)**
- **[Active Logic](https://activelogic.com)**
- **[byte5](https://byte5.de)**
- **[OP.GG](https://op.gg)**

## Contributing

Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
