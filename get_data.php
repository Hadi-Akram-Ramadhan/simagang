<?php
require_once 'koneksi.php';

$id = 2; // ID fixed ke 2 sesuai permintaan lo

$query = "SELECT first, second, description FROM settings WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $id);

if ($stmt->execute()) {
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        echo json_encode($row);
    } else {
        echo json_encode(['error' => 'Data ga ketemu nih']);
    }
} else {
    echo json_encode(['error' => 'Gagal eksekusi query']);
}

$stmt->close();
$conn->close();