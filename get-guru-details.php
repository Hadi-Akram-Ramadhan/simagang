<?php
require('koneksi.php');

if(isset($_GET['nama'])) {
    $nama = trim($_GET['nama']);
    
    $sql = "SELECT * FROM akun WHERE TRIM(nama) = ? AND role = '3'";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $nama);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        echo json_encode($row);
    } else {
        echo json_encode(["error" => "Data guru tidak ditemukan"]);
    }
    
    $stmt->close();
} else {
    echo json_encode(["error" => "Nama guru tidak diberikan"]);
}

$conn->close();
?>