<?php
require_once 'koneksi.php';

$first = $_POST['first'] ?? '';
$second = $_POST['second'] ?? '';
$description = $_POST['description'] ?? '';

$query = "UPDATE settings SET first = ?, second = ?, description = ? WHERE id = 1";
$stmt = $conn->prepare($query);
$stmt->bind_param("sss", $first, $second, $description);

$response = ['success' => false];

if ($stmt->execute()) {
    $response['success'] = true;
}

echo json_encode($response);

$stmt->close();
$conn->close();
