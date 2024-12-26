<?php
require('koneksi.php');

$asal_sekolah = $_GET['asal_sekolah'];

$sql = "SELECT * FROM akun";
if (!empty($asal_sekolah)) {
    $sql .= " WHERE asal_sekolah = ?";
}

$stmt = $conn->prepare($sql);
if (!empty($asal_sekolah)) {
    $stmt->bind_param("s", $asal_sekolah);
}

$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        echo '<tr>';
        echo '<td>' . htmlspecialchars($row['nama']) . '</td>';
        echo '<td>' . htmlspecialchars($row['tanggal_lahir']) . '</td>';
        echo '<td>' . htmlspecialchars($row['nis']) . '</td>';
        echo '<td>' . htmlspecialchars($row['nik']) . '</td>';
        echo '<td>' . htmlspecialchars($row['no_telp']) . '</td>';
        echo '<td>' . htmlspecialchars($row['gmail']) . '</td>';
        echo '<td>' . htmlspecialchars($row['asal_sekolah']) . '</td>';
        echo '<td>' . htmlspecialchars($row['alamat_sekolah']) . '</td>';
        echo '<td>' . htmlspecialchars($row['guru_pendamping']) . '</td>';
        echo '<td>' . htmlspecialchars($row['magang_masuk']) . '</td>';
        echo '<td>' . htmlspecialchars($row['magang_keluar']) . '</td>';
        echo '<td><button class="edit-btn" data-nama="' . htmlspecialchars($row['nama']) . '" data-tanggal_lahir="' . htmlspecialchars($row['tanggal_lahir']) . '" data-nis="' . htmlspecialchars($row['nis']) . '" data-nik="' . htmlspecialchars($row['nik']) . '" data-no_telp="' . htmlspecialchars($row['no_telp']) . '" data-gmail="' . htmlspecialchars($row['gmail']) . '" data-asal_sekolah="' . htmlspecialchars($row['asal_sekolah']) . '" data-alamat_sekolah="' . htmlspecialchars($row['alamat_sekolah']) . '" data-guru_pendamping="' . htmlspecialchars($row['guru_pendamping']) . '" data-magang_masuk="' . htmlspecialchars($row['magang_masuk']) . '" data-magang_keluar="' . htmlspecialchars($row['magang_keluar']) . '"><i class="fas fa-edit table-icon"></i></button></td>';
        echo '</tr>';
    }
} else {
    echo '<tr><td colspan="12">Waduh, datanya kosong nih bro!</td></tr>';
}

$stmt->close();
$conn->close();
?>