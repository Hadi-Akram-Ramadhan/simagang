<?php
session_start();
require('koneksi.php');

if (!isset($_SESSION['nama']) || !isset($_GET['date'])) {
    exit(json_encode(['error' => 'Invalid request']));
}

$date = $_GET['date'];
$nama = $_SESSION['nama'];

$sql = "SELECT laporan, img_dir FROM laporan WHERE nama = ? AND DATE(waktu) = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ss", $nama, $date);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
    // Convert image blob to base64
    $img_base64 = $row['img_dir'] ? base64_encode($row['img_dir']) : null;

    echo json_encode([
        'laporan' => $row['laporan'],
        'img_dir' => $img_base64
    ]);
} else {
    echo json_encode(['error' => 'Data not found']);
}
