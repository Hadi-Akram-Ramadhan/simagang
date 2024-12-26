<?php
require('koneksi.php');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nama = $_POST['nama'];

    $stmt = $conn->prepare("DELETE FROM akun WHERE nama = ?");
    $stmt->bind_param("s", $nama);

    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'User berhasil dihapus']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Gagal hapus user: ' . $stmt->error]);
    }

    $stmt->close();
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}

$conn->close();