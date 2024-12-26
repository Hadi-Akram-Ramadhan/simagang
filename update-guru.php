<?php
session_start();
require('koneksi.php');
require('auth.php');

header('Content-Type: application/json');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nama = $_POST['nama'];
    $nik = $_POST['editNik'];
    $no_telp = $_POST['editNoTelp'];
    $gmail = $_POST['editEmail'];
    $asal_sekolah = $_POST['editAsal'];

    // Logging untuk debugging
    error_log("Updating guru: $nama, $nik, $no_telp, $gmail, $asal_sekolah");

    $sql = "UPDATE akun SET 
            nik = ?, 
            no_telp = ?, 
            gmail = ?, 
            asal_sekolah = ?
            WHERE nama = ? AND role = '3'";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssss", $nik, $no_telp, $gmail, $asal_sekolah, $nama);

    if ($stmt->execute()) {
        error_log("Update berhasil untuk guru: $nama");
        echo json_encode(['status' => 'success', 'message' => 'Mantap, data guru berhasil diupdate nih!']);
    } else {
        error_log("Update gagal untuk guru: $nama. Error: " . $stmt->error);
        echo json_encode(['status' => 'error', 'message' => 'Waduh, gagal update data guru nih. Coba lagi yuk!']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
}