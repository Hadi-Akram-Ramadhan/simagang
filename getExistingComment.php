<?php
session_start();
require('koneksi.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $month = $_POST['month'];

    $sql = "SELECT DISTINCT comment FROM laporan WHERE nama = ? AND DATE_FORMAT(waktu, '%Y-%m') = ? LIMIT 1";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $name, $month);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        echo json_encode(['comment' => $row['comment']]);
    } else {
        echo json_encode(['comment' => null]);
    }

    $stmt->close();
} else {
    echo json_encode(['error' => 'Invalid request method']);
}

$conn->close();
