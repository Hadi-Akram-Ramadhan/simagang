<?php
require('koneksi.php');

header('Content-Type: application/json');

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['schoolName']) && isset($_POST['schoolLocation']) && isset($_POST['schoolEmail']) && isset($_POST['schoolPhone'])) {
    $schoolName = $_POST['schoolName'];
    $schoolLocation = $_POST['schoolLocation'];
    $schoolEmail = $_POST['schoolEmail'];
    $schoolPhone = $_POST['schoolPhone'];

    $sql = "INSERT INTO sekolah (nama, lokasi, email, no_telp) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssss", $schoolName, $schoolLocation, $schoolEmail, $schoolPhone);
    
    if ($stmt->execute()) {
        echo json_encode([
            'status' => 'success',
            'message' => 'Mantap! Sekolah baru udah berhasil ditambahin nih'
        ]);
    } else {
        echo json_encode([
            'status' => 'error',
            'message' => 'Waduh, gagal nambah sekolah nih. Coba lagi deh!'
        ]);
    }
    $stmt->close();
} else {
    echo json_encode([
        'status' => 'error',
        'message' => 'Invalid request'
    ]);
}
?>