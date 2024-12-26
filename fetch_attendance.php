<?php
session_start();
require('koneksi.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['name'])) {
    $name = $_POST['name'];
    $month = isset($_POST['month']) ? $_POST['month'] : null;

    $attendanceQuery = "
        SELECT 
            DATE(waktu) as tanggal,
            COUNT(CASE WHEN waktu IS NOT NULL THEN 1 END) as status
        FROM images
        WHERE user = ?
    ";

    $params = array($name);

    if ($month) {
        $attendanceQuery .= " AND DATE_FORMAT(waktu, '%Y-%m') = ?";
        $params[] = $month;
    }

    $attendanceQuery .= "
        GROUP BY DATE(waktu)
        HAVING COUNT(*) >= 2
        ORDER BY tanggal
    ";

    $attendanceStmt = $conn->prepare($attendanceQuery);
    $attendanceStmt->bind_param(str_repeat('s', count($params)), ...$params);
    $attendanceStmt->execute();
    $attendanceResult = $attendanceStmt->get_result();

    $output = '<table>';
    $output .= '<thead>';
    $output .= '<tr>';
    $output .= '<th>Tanggal</th>';
    $output .= '<th>Status</th>';
    $output .= '<th>Detail Absen</th>';
    $output .= '</tr>';
    $output .= '</thead>';
    $output .= '<tbody>';

    while ($attendanceRow = $attendanceResult->fetch_assoc()) {
        $output .= '<tr>';
        $output .= '<td>' . htmlspecialchars($attendanceRow['tanggal']) . '</td>';
        $output .= '<td>' . ($attendanceRow['status'] == 1 ? 'Hadir' : 'Hadir') . '</td>';
        $output .= '<td><a href="#" class="view-details" data-name="' . htmlspecialchars($name) . '" data-date="' . htmlspecialchars($attendanceRow['tanggal']) . '" onclick="showAttendanceDetails(\'' . htmlspecialchars($name) . '\', \'' . htmlspecialchars($attendanceRow['tanggal']) . '\')">View Details</a></td>';
        $output .= '</tr>';
    }

    $output .= '</tbody>';
    $output .= '</table>';

    echo $output;
    exit();
}
