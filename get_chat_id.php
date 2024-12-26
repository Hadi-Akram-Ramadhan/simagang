<?php
require_once 'koneksi.php';
require_once 'telegram_helper.php';

$update = json_decode(file_get_contents('php://input'), true);

if (isset($update['message'])) {
    $chat_id = $update['message']['chat']['id'];
    $text = $update['message']['text'];

    if ($text == '/start') {
        $message = "Halo! Untuk daftar notifikasi, kirim Email kamu yang terdaftar di sistem ya!";
        sendTelegramMessage($chat_id, $message);
        exit;
    }

    // Cek gmail + role = 1
    $sql = "SELECT nama FROM akun WHERE gmail = ? AND role = '1'";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $text);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();

        // Update telegram_chat_id
        $sql = "UPDATE akun SET telegram_chat_id = ? WHERE gmail = ? AND role = '1'";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $chat_id, $text);
        $stmt->execute();

        $message = "🎉 Berhasil! Halo {$row['nama']}, kamu akan dapat notifikasi absen mulai besok.";
    } else {
        $message = "❌ Email tidak ditemukan atau kamu tidak punya akses. Coba lagi ya!";
    }

    sendTelegramMessage($chat_id, $message);
}
