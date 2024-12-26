<?php
error_reporting(0);
header('Content-Type: application/json');

session_start();
require('koneksi.php');
require('auth.php');

try {
    if (!isset($_SESSION['role']) || ($_SESSION['role'] !== '2' && $_SESSION['role'] !== '4')) {
        throw new Exception('Unauthorized');
    }

    $data = json_decode(file_get_contents('php://input'), true);
    if (!$data) {
        throw new Exception('Invalid request data');
    }

    $nama = $data['nama'];
    $bulan = $data['bulan'];
    $tahun = $data['tahun'];
    $comment = $data['comment'];

    $conn->begin_transaction();

    $sql = "UPDATE laporan 
            SET comment = ? 
            WHERE nama = ? 
            AND MONTH(waktu) = ? 
            AND YEAR(waktu) = ? 
            AND status = 1";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssii", $comment, $nama, $bulan, $tahun);
    $stmt->execute();

    $conn->commit();
    echo json_encode(['success' => true]);
} catch (Exception $e) {
    if ($conn->connect_errno) {
        $conn->rollback();
    }
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

$conn->close();
