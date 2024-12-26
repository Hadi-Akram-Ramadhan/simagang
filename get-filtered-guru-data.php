<?php
require('koneksi.php');

// Get pembimbing list first
$pembimbingSql = "SELECT nama FROM akun WHERE role = '4' ORDER BY nama";
$pembimbingResult = $conn->query($pembimbingSql);
$pembimbingList = [];
while($row = $pembimbingResult->fetch_assoc()) {
    $pembimbingList[] = $row['nama'];
}

$school = $_GET['school'] ?? '';
$period = $_GET['period'] ?? '';
$search = $_GET['search'] ?? '';

$sql = "SELECT a1.no_surat, a1.no_surat_p, a1.asal_sekolah, a1.magang_masuk, a1.magang_keluar, 
               GROUP_CONCAT(DISTINCT a2.nama SEPARATOR ', ') as nama_guru,
               a1.pembimbing
        FROM akun a1
        LEFT JOIN akun a2 ON LOWER(REPLACE(a1.asal_sekolah, ' ', '')) = LOWER(REPLACE(a2.asal_sekolah, ' ', '')) 
                          AND a2.role = '3'
                          AND a2.magang_masuk = a1.magang_masuk
                          AND a2.magang_keluar = a1.magang_keluar
        WHERE a1.role = '1'";

if (!empty($school)) {
    $sql .= " AND LOWER(a1.asal_sekolah) = LOWER('" . $conn->real_escape_string($school) . "')";    
}

if (!empty($period)) {
    list($masuk, $keluar) = explode(' to ', $period);
    $sql .= " AND a1.magang_masuk = '" . $conn->real_escape_string($masuk) . "'";
    $sql .= " AND a1.magang_keluar = '" . $conn->real_escape_string($keluar) . "'";
}

if (!empty($search)) {
    $sql .= " AND (LOWER(a1.asal_sekolah) LIKE LOWER('%" . $conn->real_escape_string($search) . "%')";
    $sql .= " OR LOWER(a2.nama) LIKE LOWER('%" . $conn->real_escape_string($search) . "%'))";
}

$sql .= " GROUP BY a1.asal_sekolah, a1.magang_masuk, a1.magang_keluar, a1.no_surat, a1.no_surat_p";

$result = $conn->query($sql);

if ($result->num_rows > 0) {
    $key = 1;
    while($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<th scope='row'>" . $key++ . "</th>";
        echo "<td>";
        if (empty($row['no_surat'])) {
            echo "<span class='highlight'>Belum ada nomor</span>";
        } else {
            echo "<a href='show-surat-tugas.php?sekolah=" . urlencode($row['asal_sekolah']) . "&masuk=" . urlencode($row['magang_masuk']) . "&keluar=" . urlencode($row['magang_keluar']) . "' target='_blank'>" . htmlspecialchars($row['no_surat']) . "</a>";
        }
        echo "</td>";
        echo "<td>";
        if (empty($row['no_surat_p'])) {
            echo "<span class='highlight' onclick='openPersetujuanModal(\"" . $row['asal_sekolah'] . "\", \"" . $row['magang_masuk'] . "\", \"" . $row['magang_keluar'] . "\")'>Belum ada nomor</span>";
        } else {
            echo "<a href='show-surat-persetujuan.php?sekolah=" . urlencode($row['asal_sekolah']) . "&masuk=" . urlencode($row['magang_masuk']) . "&keluar=" . urlencode($row['magang_keluar']) . "' target='_blank'>" . htmlspecialchars($row['no_surat_p']) . "</a>";
        }
        echo "</td>";
        echo "<td>" . htmlspecialchars($row['asal_sekolah']) . "</td>";
        echo "<td>" . htmlspecialchars($row['magang_masuk']) . "</td>";
        echo "<td>" . htmlspecialchars($row['magang_keluar']) . "</td>";
        echo "<td>" . (empty($row['nama_guru']) ? "<span class='highlight'>Belum ada data guru</span>" : htmlspecialchars(trim($row['nama_guru']))) . "</td>";
        echo "<td>
                <select class='pembimbing-select' 
                        data-sekolah='" . htmlspecialchars($row['asal_sekolah']) . "'
                        data-masuk='" . htmlspecialchars($row['magang_masuk']) . "'
                        data-keluar='" . htmlspecialchars($row['magang_keluar']) . "'>
                    <option value=''>Pilih Pembimbing</option>";
                    foreach($pembimbingList as $pembimbing) {
                        $selected = ($pembimbing == $row['pembimbing']) ? 'selected' : '';
                        echo "<option value='" . htmlspecialchars($pembimbing) . "' $selected>" . 
                             htmlspecialchars($pembimbing) . "</option>";
                    }
                echo "</select></td>";
        echo "<td>" . (empty($row['nama_guru']) ? "<a href='auth-admin.php' class='highlight'>Tambah data guru</a>" : "<a href='#' class='detail-link' data-nama='" . htmlspecialchars($row['nama_guru'], ENT_QUOTES) . "'>Detail</a>") . "</td>";
        echo "</tr>";
    }
} else {
    echo "<tr><td colspan='9' class='no-data highlight'>Belum ada data nih</td></tr>";
}