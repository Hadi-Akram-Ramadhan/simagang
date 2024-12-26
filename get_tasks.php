<?php
session_start();
require('koneksi.php');

$month = $_GET['month'];
$year = $_GET['year'];
$nama = $_SESSION['nama'];

$sql = "SELECT DATE(waktu) as date, laporan 
        FROM laporan 
        WHERE nama = ? 
        AND MONTH(waktu) = ? 
        AND YEAR(waktu) = ?
        ORDER BY waktu ASC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("sii", $nama, $month, $year);
$stmt->execute();
$result = $stmt->get_result();

$tasks = [];
while ($row = $result->fetch_assoc()) {
    $tasks[] = $row;
}

header('Content-Type: application/json');
echo json_encode($tasks);
