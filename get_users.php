<?php
require('koneksi.php');

$sql = "SELECT nama, asal_sekolah, gmail, role FROM akun";
$result = $conn->query($sql);

$users = [];
if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $users[] = $row;
    }
}

echo json_encode($users);

$conn->close();