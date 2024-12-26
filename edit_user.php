<?php
require('koneksi.php');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nama = $_POST['editNama'];
    $sekolah = $_POST['editSekolah'];
    $gmail = $_POST['editGmail'];
    $role = $_POST['editRole'];
    $pass = $_POST['editPass'];

    $sql = "UPDATE akun SET asal_sekolah=?, gmail=?, role=?";
    $params = [$sekolah, $gmail, $role];

    if (!empty($pass)) {
        $hashedPass = password_hash($pass, PASSWORD_DEFAULT);
        $sql .= ", pass=?";
        $params[] = $hashedPass;
    }

    $sql .= " WHERE nama=?";
    $params[] = $nama;

    $stmt = $conn->prepare($sql);
    $stmt->bind_param(str_repeat('s', count($params)), ...$params);

    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Data user berhasil diupdate']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Gagal update data: ' . $stmt->error]);
    }

    $stmt->close();
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}

$conn->close();