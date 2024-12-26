<?php
require('koneksi.php');

if (isset($_GET['sekolah']) && isset($_GET['masuk']) && isset($_GET['keluar'])) {
    $sekolah = $_GET['sekolah'];
    $masuk = $_GET['masuk'];
    $keluar = $_GET['keluar'];

    // Query buat ngambil data surat persetujuan berdasarkan sekolah dan periode magang
    $sql = "SELECT surat_persetujuan FROM akun 
            WHERE asal_sekolah = ? AND magang_masuk = ? AND magang_keluar = ? 
            AND role = '1' LIMIT 1";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sss", $sekolah, $masuk, $keluar);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $stmt->bind_result($surat_persetujuan);
        $stmt->fetch();

        if ($surat_persetujuan) {
            // Set header buat output PDF
            header("Content-Type: application/pdf");
            header("Content-Disposition: inline; filename='surat_persetujuan_{$sekolah}.pdf'");
            echo $surat_persetujuan;
        } else {
            echo "Surat persetujuan belom diupload nih.";
        }
    } else {
        echo "Surat persetujuan ga ketemu buat sekolah dan periode magang yang dipilih.";
    }

    $stmt->close();
} else {
    echo "Parameter ga lengkap nih. Masukin sekolah, tanggal masuk, sama tanggal keluar ya.";
}

$conn->close();
?>