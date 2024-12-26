<?php
session_start();
require('koneksi.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];

    $sql = "SELECT DISTINCT DATE_FORMAT(waktu, '%Y-%m') as month, 
                   DATE_FORMAT(waktu, '%M %Y') as monthName,
                   SUM(CASE WHEN status = 0 THEN 1 ELSE 0 END) as unacceptedTasks
            FROM laporan 
            WHERE nama = ?
            GROUP BY DATE_FORMAT(waktu, '%Y-%m')
            ORDER BY month DESC";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $name);
    $stmt->execute();
    $result = $stmt->get_result();

    $months = array();
    while ($row = $result->fetch_assoc()) {
        $months[] = array(
            'month' => $row['month'],
            'monthName' => $row['monthName'],
            'hasUnacceptedTasks' => $row['unacceptedTasks'] > 0
        );
    }

    echo json_encode($months);

    $stmt->close();
} else {
    echo json_encode(['error' => 'Invalid request method']);
}

$conn->close();
