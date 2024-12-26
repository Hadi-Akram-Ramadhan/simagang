<?php
require('koneksi.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['name']) && isset($_POST['date'])) {
    $name = $_POST['name'];
    $date = $_POST['date'];

    $query = "
        SELECT img_dir, latitude, longitude, waktu
        FROM images
        WHERE user = ? AND DATE(waktu) = ?
        ORDER BY waktu ASC
    ";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ss", $name, $date);
    $stmt->execute();
    $result = $stmt->get_result();

    $details = [];
    while ($row = $result->fetch_assoc()) {
        // Encode the image data as base64 to be able to send it via JSON
        $base64Image = base64_encode($row['img_dir']);
        $details[] = [
            'img' => 'data:image/jpeg;base64,' . $base64Image,
            'lat' => $row['latitude'],
            'lng' => $row['longitude'],
            'time' => $row['waktu']
        ];
    }

    if (count($details) == 2) {
        $response = [
            'in' => $details[0],
            'out' => $details[1]
        ];
    } else {
        $response = [
            'in' => null,
            'out' => null
        ];
    }

    echo json_encode($response);
} else {
    http_response_code(400);
    echo json_encode(['error' => 'Permintaan ga valid, bro']);
}
