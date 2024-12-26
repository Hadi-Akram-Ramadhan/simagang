<?php
require('koneksi.php');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nama = $_POST['nama'];
    $tanggal_lahir = $_POST['tanggal_lahir'];
    $nis = $_POST['nis'];
    $nik = $_POST['nik'];
    $gmail = $_POST['gmail'];
    $asal_sekolah = $_POST['asal_sekolah'];
    $alamat_sekolah = $_POST['alamat_sekolah'];
    $guru_pendamping = $_POST['guru_pendamping'];
    $no_telp = $_POST['no_telp'];
    $no_telp_guru = $_POST['no_telp_guru'];
    $magang_masuk = $_POST['magang_masuk'];
    $magang_keluar = $_POST['magang_keluar'];

    // Handle file upload for profile image
    if (isset($_FILES["img_dir"]["tmp_name"]) && is_uploaded_file($_FILES["img_dir"]["tmp_name"])) {
        $img_dir = file_get_contents($_FILES["img_dir"]["tmp_name"]);
    } else {
        echo "Waduh, foto profil belum diupload nih. Tolong cek lagi ya!";
        exit();
    }

    // Handle file upload for surat tugas, only accept PDF
    if (isset($_FILES["surat_tugas"]["tmp_name"]) && $_FILES["surat_tugas"]["type"] == "application/pdf") {
        $surat_tugas = file_get_contents($_FILES["surat_tugas"]["tmp_name"]);
    } else {
        echo "Maaf, untuk surat tugas hanya bisa menerima file PDF. Mohon cek kembali ya!";
        exit();
    }

    // Generate password
    $pass = substr($nama, 0, 4) . substr($tanggal_lahir, 0, 4);
    $hashed_pass = password_hash($pass, PASSWORD_DEFAULT); // Hash the password

    // Default role value
    $role = 1;

    // Check if nik already exists
    $checkNik = $conn->prepare("SELECT nik FROM akun WHERE nik = ?");
    $checkNik->bind_param("s", $nik);
    $checkNik->execute();
    $result = $checkNik->get_result();
    if ($result->num_rows > 0) {
        echo "Wah, NIK ini sudah terdaftar. Mohon gunakan NIK yang lain ya!";
        exit();
    }

    // Insert data into database
    $sql = "INSERT INTO akun (nama, tanggal_lahir, nis, nik, gmail, asal_sekolah, alamat_sekolah, guru_pendamping, no_telp, no_telp_guru, magang_masuk, magang_keluar, img_dir, surat_tugas, pass, role)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssssssssssssssi", $nama, $tanggal_lahir, $nis, $nik, $gmail, $asal_sekolah, $alamat_sekolah, $guru_pendamping, $no_telp, $no_telp_guru, $magang_masuk, $magang_keluar, $img_dir, $surat_tugas, $hashed_pass, $role);

    if ($stmt->execute()) {
        echo "Berhasil! Data baru sudah ditambahkan.";
        header("Location: manage-admin.php"); // Redirect back to your main page after insertion
        exit();
    } else {
        echo "Ups, ada kesalahan nih: " . $stmt->error;
    }

    $conn->close();
}
?>