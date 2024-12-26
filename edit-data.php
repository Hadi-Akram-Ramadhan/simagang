<?php
session_start();
require('koneksi.php');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Menerima data dari form
    $nama = $_POST['nama'];
    $tanggal_lahir = $_POST['editTanggal'];
    $nik = $_POST['editNik'];
    $no_telp = $_POST['editNoTelp'];
    $no_telp_guru = $_POST['editNoTelpGuru']; // Menambahkan no_telp_guru
    $gmail = $_POST['editEmail'];
    $asal_sekolah = $_POST['editAsal'];
    $alamat_sekolah = $_POST['editAlamat'];
    $guru_pendamping = $_POST['editGuru'];
    $magang_masuk = $_POST['editMasuk'];
    $magang_keluar = $_POST['editKeluar'];

    // Query untuk update data
    $sql = "UPDATE akun SET 
            tanggal_lahir = ?, 
            nik = ?, 
            no_telp = ?, 
            no_telp_guru = ?, 
            gmail = ?, 
            asal_sekolah = ?, 
            alamat_sekolah = ?, 
            guru_pendamping = ?, 
            magang_masuk = ?, 
            magang_keluar = ?
            WHERE nama = ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssssssssss", $tanggal_lahir, $nik, $no_telp, $no_telp_guru, $gmail, $asal_sekolah, $alamat_sekolah, $guru_pendamping, $magang_masuk, $magang_keluar, $nama);

    if ($stmt->execute()) {
        echo "<script>alert('Mantap, data udah diupdate nih!');window.location='manage-admin.php';</script>";
    } else {
        echo "<script>alert('Waduh, gagal update data: " . htmlspecialchars($stmt->error) . "');</script>";
    }

    $stmt->close();
    $conn->close();
}
?>