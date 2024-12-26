<?php
require('koneksi.php');

$sekolah = $_GET['sekolah'];
$masuk = $_GET['masuk'];
$keluar = $_GET['keluar'];

$sql = "SELECT nama, nis, tanggal_lahir, nik, no_telp, gmail, alamat_sekolah, guru_pendamping, no_telp_guru 
        FROM akun 
        WHERE role = '1' 
        AND asal_sekolah = ? 
        AND magang_masuk = ? 
        AND magang_keluar = ?
        ORDER BY nama";

$stmt = $conn->prepare($sql);
$stmt->bind_param("sss", $sekolah, $masuk, $keluar);
$stmt->execute();
$result = $stmt->get_result();

$data = array(
    'sekolah' => $sekolah,
    'periode_magang' => "$masuk - $keluar",
    'murid' => array()
);

while ($row = $result->fetch_assoc()) {
    $data['murid'][] = $row;
}

echo json_encode($data);

$stmt->close();
$conn->close();