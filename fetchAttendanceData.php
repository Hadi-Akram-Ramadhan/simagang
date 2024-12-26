<?php
session_start();
require('koneksi.php');
require('auth.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $month = $_POST['month'];

    // Ambil data absensi
    $attendanceQuery = "SELECT DATE(waktu) as tanggal, COUNT(CASE WHEN waktu IS NOT NULL THEN 1 END) as status FROM images WHERE user = ? ";
    $params = array($name);

    if ($month) {
        $attendanceQuery .= "AND DATE_FORMAT(waktu, '%Y-%m') = ? ";
        $params[] = $month;
    }

    $attendanceQuery .= "GROUP BY DATE(waktu) HAVING COUNT(*) >= 2 ORDER BY tanggal";

    $stmt = $conn->prepare($attendanceQuery);
    $stmt->bind_param(str_repeat('s', count($params)), ...$params);
    $stmt->execute();
    $result = $stmt->get_result();

    $attendanceData = "";
    while ($row = $result->fetch_assoc()) {
        // Tambahkan kode untuk menampilkan data absensi seperti sebelumnya
        $attendanceData .= "<tr>......</tr>";
    }

    // Ambil komentar bulanan
    $monthlyComment = getMonthlyComment($conn, $name, $month);

    echo json_encode([
        'attendanceData' => $attendanceData,
        'monthlyComment' => $monthlyComment
    ]);
}

function getMonthlyComment($conn, $name, $month)
{
    $sql = "SELECT DISTINCT comment FROM laporan WHERE nama = ? AND DATE_FORMAT(waktu, '%Y-%m') = ? AND comment IS NOT NULL LIMIT 1";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $name, $month);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        return $row['comment'];
    }
    return null;
}
