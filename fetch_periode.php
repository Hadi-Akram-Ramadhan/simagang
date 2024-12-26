<?php
require('koneksi.php');

if (isset($_POST['school'])) {
    $school = mysqli_real_escape_string($conn, $_POST['school']);

    $periodeQuery = "SELECT DISTINCT magang_masuk, magang_keluar FROM akun WHERE role = '1' AND asal_sekolah = ? ORDER BY magang_masuk";
    $stmt = mysqli_prepare($conn, $periodeQuery);
    mysqli_stmt_bind_param($stmt, "s", $school);
    mysqli_stmt_execute($stmt);
    $periodeResult = mysqli_stmt_get_result($stmt);

    echo "<option value=''>Pilih Periode</option>";
    while ($row = mysqli_fetch_assoc($periodeResult)) {
        echo "<option value='{$row['magang_masuk']} - {$row['magang_keluar']}'>{$row['magang_masuk']} - {$row['magang_keluar']}</option>";
    }
}
