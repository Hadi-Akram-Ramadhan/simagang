<?php
session_start();
require('koneksi.php');
require('auth.php');

if (!isset($_SESSION['role']) || $_SESSION['role'] !== '2') {
    echo json_encode(['success' => false, 'error' => 'Unauthorized access']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $month = $_POST['month'];
    $action = $_POST['action'];
    $comment = isset($_POST['comment']) ? $_POST['comment'] : '';

    if ($action === 'accept') {
        // Update semua tugas yang belum diterima
        $updateQuery = "UPDATE laporan 
                       SET status = 1 
                       WHERE nama = ? 
                       AND DATE_FORMAT(waktu, '%Y-%m') = ?
                       AND status = 0";

        $stmt = $conn->prepare($updateQuery);
        $stmt->bind_param("ss", $name, $month);

        if ($stmt->execute()) {
            echo json_encode([
                'success' => true,
                'message' => 'Semua tugas berhasil diterima'
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'error' => 'Gagal update status tugas'
            ]);
        }
    } elseif ($action === 'edit') {
        // Update atau insert komentar bulanan
        $checkQuery = "SELECT id FROM monthly_comments 
                      WHERE nama = ? AND month = ?";
        $stmt = $conn->prepare($checkQuery);
        $stmt->bind_param("ss", $name, $month);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            // Update existing comment
            $updateQuery = "UPDATE monthly_comments 
                          SET comment = ? 
                          WHERE nama = ? AND month = ?";
            $stmt = $conn->prepare($updateQuery);
            $stmt->bind_param("sss", $comment, $name, $month);
        } else {
            // Insert new comment
            $insertQuery = "INSERT INTO monthly_comments (nama, month, comment) 
                          VALUES (?, ?, ?)";
            $stmt = $conn->prepare($insertQuery);
            $stmt->bind_param("sss", $name, $month, $comment);
        }

        if ($stmt->execute()) {
            echo json_encode([
                'success' => true,
                'message' => 'Komentar berhasil diupdate'
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'error' => 'Gagal update komentar'
            ]);
        }
    }
} else {
    echo json_encode(['success' => false, 'error' => 'Invalid request method']);
}
