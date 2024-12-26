<?php
require('koneksi.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama_tugas = $_POST['nama_tugas'];
    $user = $_POST['user'];

    // Validasi input
    if (empty($nama_tugas) || empty($user)) {
        echo json_encode([
            'success' => false,
            'message' => 'Semua field harus diisi'
        ]);
        exit;
    }

    // Insert ke database
    $query = "INSERT INTO tugas (nama_tugas, user) VALUES (?, ?)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ss", $nama_tugas, $user);

    if ($stmt->execute()) {
        echo json_encode([
            'success' => true,
            'message' => 'Tugas berhasil ditambahkan'
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Gagal menambahkan tugas: ' . $conn->error
        ]);
    }

    $stmt->close();
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request method'
    ]);
}
