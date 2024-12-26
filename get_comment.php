<?php
// Pastikan tidak ada output sebelum JSON
error_reporting(0); // Temporary fix, better handling below
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

    $sql = "SELECT comment FROM laporan 
            WHERE nama = ? 
            AND MONTH(waktu) = ? 
            AND YEAR(waktu) = ? 
            AND status = 1 
            LIMIT 1";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sii", $nama, $bulan, $tahun);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        echo json_encode([
            'success' => true,
            'comment' => $row['comment']
        ]);
    } else {
        throw new Exception('No comment found');
    }
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

$conn->close();
