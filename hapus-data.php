<?php
require('koneksi.php');

session_start();


if ($_SERVER['REQUEST_METHOD'] == 'GET' && isset($_GET['nama'])) {
    $nama = $_GET['nama'];

    $sql = "DELETE FROM akun WHERE nama = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $nama);

    if ($stmt->execute()) {
        echo "<script>window.location='manage-admin.php';</script>";
    } else {
        echo "<script>alert('Waduh, gagal hapus data: " . htmlspecialchars($stmt->error) . "');window.location='manage-admin.php';</script>";
    }

    $stmt->close();
} else {
    header('Location: manage-admin.php');
    exit();
}

$conn->close();
?>