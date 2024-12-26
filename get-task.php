<?php
require('koneksi.php');

// Tambah error handling
header('Content-Type: application/json');

try {
    // Fix query syntax: WHERE before ORDER BY
    $query = "SELECT * FROM tugas WHERE status = 0 ORDER BY id DESC";
    $result = mysqli_query($conn, $query);

    if (!$result) {
        throw new Exception(mysqli_error($conn));
    }

    $tasks = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $tasks[] = [
            'id' => $row['id'],
            'nama_tugas' => $row['nama_tugas'],
            'user' => $row['user']
        ];
    }

    echo json_encode($tasks);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'error' => true,
        'message' => $e->getMessage()
    ]);
}
