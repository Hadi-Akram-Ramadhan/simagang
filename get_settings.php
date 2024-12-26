<?php
include 'koneksi.php';

$sql = "SELECT first, second, description FROM settings WHERE id = 1";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    echo json_encode($row);
} else {
    echo json_encode(['error' => 'Wah, settingan nggak ketemu nih']);
}

$conn->close();
?>