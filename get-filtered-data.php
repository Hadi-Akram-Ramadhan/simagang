<?php
require('koneksi.php');

$schoolFilter = isset($_GET['school']) ? trim($_GET['school']) : '';
$periodFilter = isset($_GET['period']) ? $_GET['period'] : '';
$searchFilter = isset($_GET['search']) ? $_GET['search'] : '';

$sql = "SELECT asal_sekolah, magang_masuk, magang_keluar, nama, nis, pembimbing, no_surat,
        COUNT(*) OVER (PARTITION BY asal_sekolah, magang_masuk, magang_keluar) as group_count
        FROM akun 
        WHERE role = '1'";

if (!empty($schoolFilter)) {
    $sql .= " AND LOWER(TRIM(asal_sekolah)) LIKE LOWER('%" . $conn->real_escape_string($schoolFilter) . "%')";
}
if (!empty($periodFilter)) {
    list($start, $end) = explode(" to ", $periodFilter);
    $sql .= " AND magang_masuk = '" . $conn->real_escape_string($start) . "' AND magang_keluar = '" . $conn->real_escape_string($end) . "'";
}
if (!empty($searchFilter)) {
    $sql .= " AND (LOWER(nama) LIKE LOWER('%" . $conn->real_escape_string($searchFilter) . "%') 
              OR nis LIKE '%" . $conn->real_escape_string($searchFilter) . "%'
              OR LOWER(asal_sekolah) LIKE LOWER('%" . $conn->real_escape_string($searchFilter) . "%')
              OR LOWER(no_surat) LIKE LOWER('%" . $conn->real_escape_string($searchFilter) . "%'))";
}

$sql .= " ORDER BY asal_sekolah, magang_masuk, magang_keluar, nama";

$result = $conn->query($sql);

if ($result->num_rows > 0) {
    $prev_school = '';
    $prev_period = '';
    $surat_tugas_number = 0;
    while($row = $result->fetch_assoc()) {
        echo "<tr>";
        
        if ($row['asal_sekolah'] != $prev_school || $row['magang_masuk'] != $prev_period) {
            $surat_tugas_number++;
            echo "<th scope='row' rowspan='" . $row['group_count'] . "'>" . $surat_tugas_number . "</th>";
            echo "<td rowspan='" . $row['group_count'] . "'>";
            if (!empty($row['no_surat'])) {
                echo "<a href='show-surat-tugas.php?sekolah=" . urlencode($row['asal_sekolah']) . "&masuk=" . urlencode($row['magang_masuk']) . "&keluar=" . urlencode($row['magang_keluar']) . "' target='_blank'>" . htmlspecialchars($row['no_surat']) . "</a>";
            } else {
                echo "<a href='#' class='add-surat-link' data-sekolah='" . htmlspecialchars($row['asal_sekolah']) . "' data-masuk='" . htmlspecialchars($row['magang_masuk']) . "' data-keluar='" . htmlspecialchars($row['magang_keluar']) . "'>Tambah No. Surat</a>";
            }
            echo "</td>";
            echo "<td rowspan='" . $row['group_count'] . "'>" . htmlspecialchars($row['asal_sekolah']) . "</td>";
            $prev_school = $row['asal_sekolah'];
            $prev_period = $row['magang_masuk'];
        }
        
        echo "<td>" . htmlspecialchars($row['nama']) . "</td>";
        echo "<td>" . htmlspecialchars($row['nis']) . "</td>";
        echo "<td>" . htmlspecialchars($row['pembimbing']) . "</td>";
        echo "<td>" . htmlspecialchars($row['magang_masuk']) . "</td>";
        echo "<td>" . htmlspecialchars($row['magang_keluar']) . "</td>";
        echo "<td><a href='#' onclick='openDetailModal(\"" . htmlspecialchars($row['nama']) . "\")'>Detail</a></td>";
        echo "</tr>";
    }
} else {
    echo "<tr><td colspan='8' class='no-data'>Belum ada data nih</td></tr>";
}

$conn->close();
?>