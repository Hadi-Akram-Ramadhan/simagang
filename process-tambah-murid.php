<?php
session_start();
require('koneksi.php');
require('auth.php');

// Pastiin ga ada output sebelum ini
ob_start();

$response = ['status' => 'error', 'message' => ''];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $asal_sekolah = $_POST['asal_sekolah'];
    $alamat_sekolah = $_POST['alamat_sekolah'];
    $guru_pendamping = $_POST['guru_pendamping'];
    $no_telp_guru = $_POST['no_telp_guru'];
    $magang_masuk = $_POST['magang_masuk'];
    $magang_keluar = $_POST['magang_keluar'];
    $surat_tugas = $_FILES['surat_tugas'];
    $no_surat = $_POST['no_surat']; // Tambahan ini
    $no_surat_p = $_POST['no_surat_p']; // Tambahan ini
    $surat_persetujuan = $_FILES['surat_persetujuan']; // Tambahan ini

    $nama = $_POST['nama'];
    $tanggal_lahir = $_POST['tanggal_lahir'];
    $nis = $_POST['nis'];
    $nik = $_POST['nik'];
    $no_telp = $_POST['no_telp'];
    $gmail = $_POST['gmail'];
    $img_dir = $_FILES['img_dir'];

    // Upload file surat tugas sebagai longblob
    $surat_tugas_content = file_get_contents($surat_tugas['tmp_name']);
    $surat_tugas_content = addslashes($surat_tugas_content);

    // Upload file surat persetujuan sebagai longblob
    $surat_persetujuan_content = file_get_contents($surat_persetujuan['tmp_name']);
    $surat_persetujuan_content = addslashes($surat_persetujuan_content);

    foreach ($nama as $index => $name) {
        $tanggal_lahir_val = $tanggal_lahir[$index];
        $nis_val = $nis[$index];
        $nik_val = $nik[$index];
        $no_telp_val = $no_telp[$index];
        $gmail_val = $gmail[$index];
        $img_dir_val = $img_dir['tmp_name'][$index];

        // Cek apakah data sudah ada di database
        $cek_sql = "SELECT * FROM akun WHERE nis = '$nis_val' OR nik = '$nik_val' OR gmail = '$gmail_val'";
        $result = mysqli_query($conn, $cek_sql);
        if (mysqli_num_rows($result) > 0) {
            $response['message'] = "Data $name udah ada nih. Coba cek lagi ya!";
            break;
        }

        // Upload file img_dir sebagai longblob
        $img_dir_content = file_get_contents($img_dir_val);
        $img_dir_content = addslashes($img_dir_content);

        // Generate password
        $tahun_lahir = substr($tanggal_lahir_val, 0, 4);
        $password_raw = substr($name, 0, 4) . $tahun_lahir;
        $password_hashed = password_hash($password_raw, PASSWORD_BCRYPT);

        // Default role
        $role = 1;

        // Insert data ke tabel
        $sql = "INSERT INTO akun (asal_sekolah, alamat_sekolah, guru_pendamping, no_telp_guru, magang_masuk, magang_keluar, surat_tugas, nama, tanggal_lahir, nis, nik, no_telp, gmail, img_dir, role, pass, no_surat, no_surat_p, surat_persetujuan)
                VALUES ('$asal_sekolah', '$alamat_sekolah', '$guru_pendamping', '$no_telp_guru', '$magang_masuk', '$magang_keluar', '$surat_tugas_content', '$name', '$tanggal_lahir_val', '$nis_val', '$nik_val', '$no_telp_val', '$gmail_val', '$img_dir_content', '$role', '$password_hashed', '$no_surat', '$no_surat_p', '$surat_persetujuan_content')";
        
        if (mysqli_query($conn, $sql)) {
            $response['status'] = 'success';
            $response['message'] = "Data $name berhasil ditambahin nih!";
        } else {
            $response['message'] = "Ada error nih: " . mysqli_error($conn);
            break;
        }
    }

    mysqli_close($conn);
}

// Bersihin output buffer
ob_end_clean();

// Set header ke application/json
header('Content-Type: application/json');

// Echo response sebagai JSON
echo json_encode($response);
exit;