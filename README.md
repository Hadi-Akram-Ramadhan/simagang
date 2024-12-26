# Sistem Absensi Digital PKL Kementerian Perdagangan

Sistem absensi digital canggih buat ngedata kehadiran siswa magang di Kementerian Perdagangan. Dibuat pake PHP, JavaScript, MySQL, dan dilengkapi fitur foto selfie + GPS tracking.

## Fitur Utama

- Absen pake foto selfie (2x sehari: masuk & pulang)
- Deteksi lokasi GPS real-time
- Dashboard buat admin, guru, dan siswa
- Laporan absensi detail + export PDF
- Manajemen data user (admin, guru, siswa)
- Integrasi dengan data sekolah
- Sistem login multi-role (admin, guru, siswa)

## Tech Stack

- Backend: PHP
- Frontend: HTML, CSS, JavaScript
- Database: MySQL
- Framework CSS: Bootstrap
- Library JS: SweetAlert2, Leaflet (maps)

## Cara Setup

1. Clone repo ini
2. Import database dari file `kred3876_absenpkl (5).sql`
3. Konfigurasi koneksi database di `koneksi.php`:

`$server = "localhost";
$username = "root"; // sesuaikan dengan user MySQL kalian
$password = ""; // sesuaikan dengan password MySQL kalian
$dbname = "kred3876_absenpkl";
$conn = mysqli_connect($server, $username, $password, $dbname) or die ("Koneksi Gagal");`


4. Jalanin di web server lo (Apache/Nginx)
5. Akses `index.php` buat mulai
6. Gw make library google-api jadi lo harus setup dulu make composer. ini command nya `composer require google/apiclient:^2.12.1` dan `require google/apiclient:"^2.0"`

## Struktur Projek Penting

- `index.php`: Halaman login
- `homeUser.php`: Dashboard siswa
- `homeAdmin.php`: Dashboard admin
- `homeTeacher.php`: Dashboard guru
- `photoUser.php`: Proses absensi (foto + GPS)
- `manage-admin.php`: Manajemen data user
- `laporan_pdf.php`: Generate laporan PDF

## Kontribusi

Lo punya ide keren atau nemuin bug? Langsung aja bikin pull request atau buka issue. Gua open banget sama kontribusi lo!

## Keamanan

Sistem ini udah didesain dengan mempertimbangkan aspek keamanan:
- Password di-hash pake bcrypt
- Validasi input ketat
- Penggunaan prepared statements buat cegah SQL injection

## License

Projek ini pake [Apache License 2.0](LICENSE).

---

Dibuat dengan ❤️ oleh Hadi Akram Ramadhan & Tim buat Kementerian Perdagangan RI

Jangan lupa star repo ini kalo lo suka ya! ⭐
