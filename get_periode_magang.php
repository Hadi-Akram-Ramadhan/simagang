<?php
header('Content-Type: application/json');
require('koneksi.php');

$school = isset($_GET['school']) ? $_GET['school'] : '';

if (empty($school)) {
    echo json_encode([]);
    exit;
}

$query = "SELECT DISTINCT CONCAT(magang_masuk, ' - ', magang_keluar) AS periode 
          FROM akun 
          WHERE role = 1 AND asal_sekolah = ? 
          AND magang_masuk IS NOT NULL AND magang_keluar IS NOT NULL 
          ORDER BY magang_masuk";

$stmt = $conn->prepare($query);
$stmt->bind_param("s", $school);
$stmt->execute();
$result = $stmt->get_result();

$periods = [];
while ($row = $result->fetch_assoc()) {
    $periods[] = $row['periode'];
}

echo json_encode($periods);

$stmt->close();
$conn->close();