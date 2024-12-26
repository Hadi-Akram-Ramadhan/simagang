<?php
require('koneksi.php');

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $sekolah = $_POST['sekolah'];
    $masuk = $_POST['masuk'];
    $keluar = $_POST['keluar'];
    $pembimbing = $_POST['pembimbing'];

    $sql = "UPDATE akun SET pembimbing = ? WHERE asal_sekolah = ? AND magang_masuk = ? AND magang_keluar = ? AND role = '1'";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssss", $pembimbing, $sekolah, $masuk, $keluar);

    if ($stmt->execute()) {
        echo json_encode(['status' => 'success', 'message' => 'Pembimbing berhasil diupdate']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Gagal update pembimbing']);
    }

    $stmt->close();
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
}

$conn->close();