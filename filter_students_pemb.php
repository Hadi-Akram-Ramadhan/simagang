<?php
require('koneksi.php');

$query = "SELECT DISTINCT a.nama, a.asal_sekolah, a.magang_masuk, a.magang_keluar 
          FROM akun a 
          WHERE a.role = '1'";

$params = [];
$types = "";

if (!empty($_POST['school'])) {
    $query .= " AND a.asal_sekolah = ?";
    $params[] = $_POST['school'];
    $types .= "s";
}

if (!empty($_POST['periode'])) {
    $periode = explode(" - ", $_POST['periode']);
    if (count($periode) == 2) {
        $query .= " AND a.magang_masuk = ? AND a.magang_keluar = ?";
        $params[] = $periode[0];
        $params[] = $periode[1];
        $types .= "ss";
    }
}

$query .= " ORDER BY a.asal_sekolah, a.nama";

$stmt = mysqli_prepare($conn, $query);
if (!empty($params)) {
    mysqli_stmt_bind_param($stmt, $types, ...$params);
}
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

$output = "<table>
    <thead>
        <tr>
            <th>Nama</th>
            <th>Asal Sekolah</th>
            <th>Periode Magang</th>
            <th>Detail Absen</th>
            <th>Timeline Pekerjaan</th>
            <th>Tugas</th>
            <th>Cetak Laporan</th>
        </tr>
    </thead>
    <tbody>";

while ($row = mysqli_fetch_assoc($result)) {
    $output .= "<tr>
        <td>{$row['nama']}</td>
        <td>{$row['asal_sekolah']}</td>
        <td>{$row['magang_masuk']} - {$row['magang_keluar']}</td>
        <td><button class='detail-btn' data-name='{$row['nama']}'>Lihat</button></td>
        <td>
            <a href='timeline-pembimbing.php?nama=" . urlencode($row['nama']) . "' class='btn btn-primary timeline-btn'>
                <i class='fas fa-clock'></i> Lihat
            </a>
        </td>
        <td>
            <button class='btn btn-warning task-btn' onclick='beriTugas(\"{$row['nama']}\")'>
                <i class='fas fa-tasks'></i> Beri Tugas
            </button>
        </td>
        <td><a href='laporan_admin.php?nama=" . urlencode($row['nama']) . "' class='report-btn' target='_blank'>Cetak</a></td>
    </tr>";
}

$output .= "</tbody></table>";
echo $output;
