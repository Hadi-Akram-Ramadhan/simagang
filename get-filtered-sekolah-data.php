<?php
require('koneksi.php');

$search = isset($_GET['search']) ? $conn->real_escape_string(trim($_GET['search'])) : '';

$sql = "SELECT nama, lokasi, email, no_telp FROM sekolah WHERE 1=1";

if (!empty($search)) {
    $sql .= " AND (LOWER(TRIM(nama)) LIKE LOWER('%" . $search . "%') 
               OR LOWER(lokasi) LIKE LOWER('%" . $search . "%')
               OR LOWER(email) LIKE LOWER('%" . $search . "%')
               OR LOWER(no_telp) LIKE LOWER('%" . $search . "%'))";
}

$result = $conn->query($sql);

if ($result->num_rows > 0) {
    $key = 1;
    while($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<th scope='row'>" . $key++ . "</th>";
        echo "<td>" . htmlspecialchars(trim($row['nama'])) . "</td>";
        echo "<td>" . htmlspecialchars($row['lokasi']) . "</td>";
        echo "<td>" . htmlspecialchars($row['email']) . "</td>";
        echo "<td>" . htmlspecialchars($row['no_telp']) . "</td>";
        echo "<td>
                <a href='#' onclick='openEditModal(" . json_encode(['nama' => trim($row['nama']), 'lokasi' => $row['lokasi'], 'email' => $row['email'], 'no_telp' => $row['no_telp']]) . ")'>Edit</a> | 
                <a href='#' onclick='confirmDelete(\"" . htmlspecialchars(trim($row['nama'])) . "\")'>Hapus</a>
              </td>";
        echo "</tr>";
    }
} else {
    echo "<tr><td colspan='6' class='no-data highlight'>Belum ada data nih</td></tr>";
}
?>