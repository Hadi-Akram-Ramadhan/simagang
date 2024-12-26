<?php
require('koneksi.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['date'])) {
    $date = $_POST['date'];

    $query = "
        SELECT title, description, file_path, waktu
        FROM assignments
        WHERE DATE(waktu) = ?
        ORDER BY waktu ASC
    ";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $date);
    $stmt->execute();
    $result = $stmt->get_result();

    $assignments = [];
    while ($row = $result->fetch_assoc()) {
        $assignments[] = [
            'title' => $row['title'],
            'description' => $row['description'],
            'file' => $row['file_path'],
            'time' => $row['waktu']
        ];
    }

    echo json_encode($assignments);
} else {
    http_response_code(400);
    echo json_encode(['error' => 'Permintaan ga valid, bro']);
}
