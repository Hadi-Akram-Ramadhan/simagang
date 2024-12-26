<?php
require('koneksi.php');

$response = ['status' => 'error', 'message' => 'Ada yang salah nih'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nama = isset($_POST['nama']) ? $_POST['nama'] : '';

    $sql = "DELETE FROM sekolah WHERE nama = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $nama);

    if ($stmt->execute()) {
        $response = ['status' => 'success', 'message' => 'Data sekolah berhasil dihapus!'];
    } else {
        $response = ['status' => 'error', 'message' => 'Gagal hapus data: ' . $conn->error];
    }

    $stmt->close();
}

$conn->close();
echo json_encode($response);