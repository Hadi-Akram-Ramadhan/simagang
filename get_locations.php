<?php
require_once('koneksi.php');

// Ambil data lokasi dari database
$sql = "SELECT id, img_dir, latitude, longitude FROM images";
$result = $conn->query($sql);

$locations = array();
if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $locations[] = array(
            'id' => $row['id'],
            'img_dir' => $row['img_dir'],
            'latitude' => $row['latitude'],
            'longitude' => $row['longitude']
        );
    }
}
$conn->close();

header('Content-Type: application/json');
echo json_encode($locations);
?>
