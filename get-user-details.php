<?php
require('koneksi.php'); // Pastikan file ini mengandung koneksi ke database

if (isset($_GET['nama'])) {
    $nama = $_GET['nama'];

    $sql = "SELECT nama, nis, tanggal_lahir, nik, asal_sekolah, alamat_sekolah, guru_pendamping, no_telp, no_telp_guru, gmail, magang_masuk, magang_keluar, img_dir FROM akun WHERE nama = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $nama);
    $stmt->execute();
    $result = $stmt->get_result();
    $data = $result->fetch_assoc();

    // Konversi gambar dari BLOB ke base64
    if ($data['img_dir']) {
        $data['img_base64'] = base64_encode($data['img_dir']);
        unset($data['img_dir']); // Hapus data BLOB asli dari array
    } else {
        $data['img_base64'] = null;
    }

    echo json_encode($data);
} else {
    echo json_encode(array("error" => "Wah, nama belum diisi nih"));
}
?>