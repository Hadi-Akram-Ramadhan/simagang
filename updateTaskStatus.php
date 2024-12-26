<?php
require('koneksi.php'); // Pastikan file koneksi database sudah benar

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['name']) && isset($_POST['status']) && isset($_POST['waktu'])) {
    $name = $_POST['name'];
    $status = $_POST['status'];
    $waktu = $_POST['waktu']; // Tambahkan parameter waktu

    $query = "UPDATE laporan SET status = ? WHERE nama = ? AND DATE(waktu) = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("iss", $status, $name, $waktu); // Bind parameter waktu juga
    if ($stmt->execute()) {
        echo "Status updated successfully.";
    } else {
        echo "Failed to update status.";
    }
    $stmt->close();
} else {
    echo "Invalid request";
}
?>