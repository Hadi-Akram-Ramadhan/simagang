<?php
require('koneksi.php');

if (isset($_POST['user'])) {
    $user = $_POST['user'];

    $query = "SELECT nama_tugas FROM tugas WHERE user = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $user);
    $stmt->execute();

    $result = $stmt->get_result();
    $tasks = [];

    while ($row = $result->fetch_assoc()) {
        $tasks[] = $row;
    }

    echo json_encode($tasks);
    exit;
}

echo json_encode(['error' => 'Invalid request']);
