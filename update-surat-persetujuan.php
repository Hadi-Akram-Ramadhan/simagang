<?php
require('koneksi.php');

$response = ['status' => 'error', 'message' => 'Unknown error occurred'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $sekolah = $_POST['sekolah'];
    $masuk = $_POST['masuk'];
    $keluar = $_POST['keluar'];
    $no_surat_p = $_POST['no_surat_p'];

    if (isset($_FILES['surat_persetujuan']) && $_FILES['surat_persetujuan']['error'] == 0) {
        $file = $_FILES['surat_persetujuan'];
        
        // Cek tipe file
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);
        
        if ($mime != 'application/pdf') {
            $response = ['status' => 'error', 'message' => 'File harus PDF ya bro!'];
        } elseif ($file['size'] > 5 * 1024 * 1024) { // 5MB
            $response = ['status' => 'error', 'message' => 'File max 5MB ya!'];
        } else {
            $pdf_content = file_get_contents($file['tmp_name']);
            
            // Pake prepared statement buat hindarin SQL injection
            $sql = "UPDATE akun SET no_surat_p = ?, surat_persetujuan = ? WHERE asal_sekolah = ? AND magang_masuk = ? AND magang_keluar = ?";
            $stmt = $conn->prepare($sql);
            
            // 's' untuk string, 'b' untuk blob
            $stmt->bind_param("sssss", $no_surat_p, $pdf_content, $sekolah, $masuk, $keluar);
            
            if ($stmt->execute()) {
                $response = ['status' => 'success', 'message' => 'Surat persetujuan berhasil diupload!'];
            } else {
                $response = ['status' => 'error', 'message' => 'Gagal upload surat: ' . $stmt->error];
            }
            
            $stmt->close();
        }
    } else {
        $response = ['status' => 'error', 'message' => 'Ga ada file yang diupload nih'];
    }
}

echo json_encode($response);