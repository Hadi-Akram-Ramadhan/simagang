<?php
header('Content-Type: application/json');
error_reporting(E_ALL);
session_start();
require_once('koneksi.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    if ($data['action'] === 'terima_tugas' && isset($data['muridNames'])) {
        $muridNames = $data['muridNames'];
        $success = true;
        $error = '';

        foreach ($muridNames as $muridName) {
            $stmt = $conn->prepare("UPDATE laporan SET status_g = 1 WHERE nama = ?");
            $stmt->bind_param("s", $muridName);
            
            if (!$stmt->execute()) {
                $success = false;
                $error = $stmt->error;
                break;
            }
            
            $stmt->close();
        }

        if ($success) {
            echo json_encode(['success' => true, 'pesan' => 'Tugas berhasil diterima, terima kasih!']);
        } else {
            echo json_encode(['success' => false, 'pesan' => 'Maaf, ada kesalahan: ' . $error]);
        }
    } else {
        echo json_encode(['success' => false, 'pesan' => 'Permintaan tidak valid, mohon coba lagi']);
    }
} else {
    echo json_encode(['success' => false, 'pesan' => 'Metode tidak valid, harap gunakan POST']);
}