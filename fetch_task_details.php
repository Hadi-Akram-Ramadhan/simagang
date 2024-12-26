<?php
include('koneksi.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $date = $_POST['date'];
    $user = $_POST['user'];

    $query = "SELECT id, laporan, img_dir, status, TIME_FORMAT(waktu, '%H:%i') as jam
              FROM laporan
              WHERE DATE_FORMAT(waktu, '%Y-%m-%d') = ? AND nama = ?
              ORDER BY waktu ASC";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ss", $date, $user);
    $stmt->execute();
    $result = $stmt->get_result();

    $tasks = [];
    while ($row = $result->fetch_assoc()) {
        $statusText = $row['status'] === '0' ? 'Diterima' : 'Belum Diterima';
        $tasks[] = [
            'id' => $row['id'],
            'laporan' => $row['laporan'],
            'img_dir' => 'data:image/jpeg;base64,' . base64_encode($row['img_dir']),
            'status' => $statusText,
            'jam' => $row['jam']
        ];
    }

    if (!empty($tasks)) {
        echo json_encode([
            'success' => true,
            'tasks' => $tasks
        ]);
    } else {
        echo json_encode(['success' => false, 'pesan' => 'Data ga ketemu bro']);
    }

    $stmt->close();
    $conn->close();
} else {
    echo json_encode(['success' => false, 'pesan' => 'Metode request salah nih']);
}
