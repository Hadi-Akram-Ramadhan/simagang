<?php
header('Content-Type: application/json');
require('koneksi.php');

$data = json_decode(file_get_contents('php://input'), true);
$school = $data['school'];
$period = $data['period'];

list($magang_masuk, $magang_keluar) = explode(' - ', $period);

$stmt = $conn->prepare("SELECT COUNT(*) FROM akun WHERE role = 3 AND asal_sekolah = ? AND magang_masuk = ? AND magang_keluar = ?");
$stmt->bind_param("sss", $school, $magang_masuk, $magang_keluar);
$stmt->execute();
$result = $stmt->get_result();
$count = $result->fetch_row()[0];
$stmt->close();

echo json_encode(['hasTeacher' => $count > 0]);