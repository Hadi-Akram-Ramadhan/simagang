<?php
require('koneksi.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama_tugas = $_POST['nama_tugas'];
    $penerima_tugas = $_POST['penerima_tugas'];
    $status = true;

    // Loop through setiap penerima tugas
    foreach ($penerima_tugas as $user) {
        $query = "INSERT INTO tugas (nama_tugas, user) VALUES (?, ?)";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, "ss", $nama_tugas, $user);

        if (!mysqli_stmt_execute($stmt)) {
            $status = false;
            break;
        }
    }

    echo json_encode([
        'status' => $status ? 'success' : 'error'
    ]);
} else {
    echo json_encode([
        'status' => 'error',
        'message' => 'Invalid request method'
    ]);
}
