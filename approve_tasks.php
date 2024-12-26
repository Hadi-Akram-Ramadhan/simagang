<?php
session_start();
require('koneksi.php');
require('auth.php');

// Check if user is admin
if (!isset($_SESSION['role']) || ($_SESSION['role'] !== '2' && $_SESSION['role'] !== '4')) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

// Get POST data
$data = json_decode(file_get_contents('php://input'), true);
$nama = $data['nama'];
$bulan = $data['bulan'];
$tahun = $data['tahun'];
$comment = $data['comment'];

try {
    // Start transaction
    $conn->begin_transaction();

    // Update semua tugas yang pending di bulan tersebut
    $sql = "UPDATE laporan 
            SET status = 1, 
                comment = ? 
            WHERE nama = ? 
            AND MONTH(waktu) = ? 
            AND YEAR(waktu) = ? 
            AND status = 0";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssii", $comment, $nama, $bulan, $tahun);
    $stmt->execute();

    // Commit transaction
    $conn->commit();

    echo json_encode(['success' => true]);
} catch (Exception $e) {
    // Rollback jika ada error
    $conn->rollback();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

$conn->close();
