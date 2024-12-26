<?php
require('koneksi.php');

if (isset($_GET['sekolah']) && isset($_GET['masuk']) && isset($_GET['keluar'])) {
    $sekolah = $_GET['sekolah'];
    $masuk = $_GET['masuk'];
    $keluar = $_GET['keluar'];

    // Query untuk mendapatkan data surat tugas berdasarkan sekolah dan periode magang
    $sql = "SELECT surat_tugas FROM akun 
            WHERE asal_sekolah = ? AND magang_masuk = ? AND magang_keluar = ? 
            AND role = '1' LIMIT 1";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sss", $sekolah, $masuk, $keluar);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $stmt->bind_result($surat_tugas);
        $stmt->fetch();

        if ($surat_tugas) {
            // Set header untuk output PDF
            header("Content-Type: application/pdf");
            header("Content-Disposition: inline; filename='surat_tugas_{$sekolah}.pdf'");
            echo $surat_tugas;
        } else {
            echo "Surat tugas belum diupload.";
        }
    } else {
        echo "Surat tugas tidak ditemukan untuk sekolah dan periode magang yang dipilih.";
    }

    $stmt->close();
} else {
    echo "Parameter tidak lengkap. Harap sertakan sekolah, tanggal masuk, dan tanggal keluar.";
}

$conn->close();
?>