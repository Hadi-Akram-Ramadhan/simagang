<?php
session_start();
require_once('koneksi.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $comment = $_POST['comment'];
    $month = $_POST['month'];

    // Validasi input
    if (empty($name) || empty($month)) {
        echo json_encode(['success' => false, 'error' => 'Wah, datanya belum lengkap nih']);
        exit;
    }

    // Update semua tugas untuk siswa tertentu di bulan tertentu
    $sql = "UPDATE laporan 
            SET status_g = 1, comment_g = ? 
            WHERE nama = ? AND DATE_FORMAT(waktu, '%Y-%m') = ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sss", $comment, $name, $month);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Sip, semua tugas udah diterima']);
    } else {
        echo json_encode(['success' => false, 'error' => 'Waduh, gagal update tugas: ' . $conn->error]);
    }

    $stmt->close();
} else {
    echo json_encode(['success' => false, 'error' => 'Metode request-nya salah nih']);
}

$conn->close();