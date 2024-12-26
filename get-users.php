<?php
require('koneksi.php');

$query = "SELECT nama, magang_masuk, magang_keluar, asal_sekolah FROM akun WHERE role = 1";
$result = mysqli_query($conn, $query);
$users = [];

while ($row = mysqli_fetch_assoc($result)) {
    $magang_masuk = date('d/m/Y', strtotime($row['magang_masuk']));
    $magang_keluar = date('d/m/Y', strtotime($row['magang_keluar']));

    $users[] = [
        'nama' => $row['nama'],
        'periode' => "$magang_masuk - $magang_keluar",
        'asal_sekolah' => $row['asal_sekolah']
    ];
}

echo json_encode($users);
