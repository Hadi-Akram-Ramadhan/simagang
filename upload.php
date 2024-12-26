<?php
require_once('koneksi.php');        
$data = json_decode(file_get_contents('php://input'), true);

// Validasi data
if (!isset($data['image']) || !isset($data['latitude']) || !isset($data['longitude']) || !isset($data['user'])) {
    echo "Data tidak lengkap.";
    exit;
}

$image = $data['image'];
$latitude = $data['latitude'];
$longitude = $data['longitude'];
$user = $data['user'];

// Decode base64 image
$image = str_replace('data:image/png;base64,', '', $image);
$image = str_replace(' ', '+', $image);
$imageData = base64_decode($image);

// Buat nama file unik dengan nomor
$target_dir = 'uploads/';
$files = glob($target_dir . 'foto*.png');
$fileCount = count($files) + 1;
$fileName = $target_dir . 'foto' . $fileCount . '.png';

if (!file_put_contents($fileName, $imageData)) {
    echo "Error saving file.";
    exit;
}

// Simpan ke database
$name = basename($fileName);
$img_dir = $fileName;

$sql = "INSERT INTO images (name, img_dir, latitude, longitude, user, waktu) VALUES (?, ?, ?, ?, ?, NOW())";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ssdds", $name, $img_dir, $latitude, $longitude, $user);

if ($stmt->execute()) {
    echo "Foto berhasil di-upload dan disimpan.";
} else {
    echo "Error: " . $sql . "<br>" . $conn->error;
}

$stmt->close();
$conn->close();
?>
