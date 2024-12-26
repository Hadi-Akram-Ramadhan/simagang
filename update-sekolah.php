<?php
require('koneksi.php');

$response = ['status' => 'error', 'message' => 'Ada yang salah nih'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nama = isset($_POST['nama']) ? $_POST['nama'] : '';
    $lokasi = isset($_POST['lokasi']) ? $_POST['lokasi'] : '';
    $email = isset($_POST['email']) ? $_POST['email'] : '';
    $no_telp = isset($_POST['no_telp']) ? $_POST['no_telp'] : '';

    // Debugging: print values
    error_log("Nama: $nama, Lokasi: $lokasi, Email: $email, No Telp: $no_telp");

    $sql = "UPDATE sekolah SET lokasi = ?, email = ?, no_telp = ? WHERE nama = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssss", $lokasi, $email, $no_telp, $nama);

    if ($stmt->execute()) {
        $response = ['status' => 'success', 'message' => 'Data sekolah berhasil diupdate!'];
    } else {
        $response = ['status' => 'error', 'message' => 'Gagal update data: ' . $conn->error];
    }

    $stmt->close();
}

$conn->close();
header('Content-Type: application/json');
echo json_encode($response);
exit;