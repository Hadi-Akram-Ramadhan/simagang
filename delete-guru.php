<?php
session_start();
require('koneksi.php');
require('auth.php');

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['nama'])) {
    $nama = $_POST['nama'];
    
    $sql = "DELETE FROM akun WHERE nama = ? AND role = '3'";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $nama);
    
    if ($stmt->execute()) {
        echo json_encode(["status" => "success", "message" => "Data guru berhasil dihapus"]);
    } else {
        echo json_encode(["status" => "error", "message" => "Gagal menghapus data guru"]);
    }
    
    $stmt->close();
} else {
    echo json_encode(["status" => "error", "message" => "Invalid request"]);
}

$conn->close();
?>