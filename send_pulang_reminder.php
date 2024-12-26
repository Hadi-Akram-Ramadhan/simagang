<?php
require_once 'koneksi.php';
require_once 'telegram_helper.php';

// Ambil user dengan role = 1 aja
$sql = "SELECT nama, telegram_chat_id FROM akun WHERE telegram_chat_id IS NOT NULL AND role = '1'";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $message = "🔔 <b>Reminder Absen Pulang!</b>\n\n"
            . "Halo {$row['nama']},\n"
            . "Jangan lupa absen pulang untuk hari ini ya!\n\n"
            . "Klik link ini untuk absen: <a href='https://simagang.kreasimu.site/'>Absen Sekarang</a>\n\n"
            . "<b>Hati-hati di perjalanan pulang, ingat keluarga menunggu!</b>";

        sendTelegramMessage($row['telegram_chat_id'], $message);
    }
}

$conn->close();
