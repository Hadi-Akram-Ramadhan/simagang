<?php
session_start();
require_once('koneksi.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $status = $_POST['status'];
    $comment = $_POST['comment'];
    $waktu = $_POST['waktu'];

    // Validasi input
    if (empty($name) || !isset($status) || empty($waktu)) {
        echo json_encode(['success' => false, 'error' => 'Data tidak lengkap']);
        exit;
    }

    // Update status tugas
    $sql = "UPDATE laporan 
            SET status_g = ?, comment_g = ? 
            WHERE nama = ? AND waktu = ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("isss", $status, $comment, $name, $waktu);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Status tugas berhasil diupdate']);
    } else {
        echo json_encode(['success' => false, 'error' => 'Gagal mengupdate status tugas: ' . $conn->error]);
    }

    $stmt->close();
} else {
    echo json_encode(['success' => false, 'error' => 'Invalid request method']);
}

$conn->close();