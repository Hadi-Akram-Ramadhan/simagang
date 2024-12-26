<?php
require_once 'koneksi.php';

$id = $_POST['id'] ?? 2;
$first = $_POST['first'] ?? '';
$second = $_POST['second'] ?? '';
$description = $_POST['description'] ?? '';

$query = "UPDATE settings SET first = ?, second = ?, description = ? WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("sssi", $first, $second, $description, $id);

$response = ['success' => false];

if ($stmt->execute()) {
    $response['success'] = true;
}

echo json_encode($response);

$stmt->close();
$conn->close();
