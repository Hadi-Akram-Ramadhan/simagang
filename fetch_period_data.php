<?php
include ('koneksi.php');

header('Content-Type: application/json');

$period = $_GET['period'] ?? '';
if ($period) {
    [$startDate, $endDate] = explode(' - ', $period);

    // Query untuk nyari murid berdasarkan periode
    $queryMurid = "SELECT asal_sekolah, nama, img_dir FROM akun WHERE role = 1 AND magang_masuk = '$startDate' AND magang_keluar = '$endDate'";
    $resultMurid = mysqli_query($conn, $queryMurid);

    $schoolsInfo = [];
    while ($row = mysqli_fetch_assoc($resultMurid)) {
        $schoolsInfo[$row['asal_sekolah']]['murid'][] = [
            'nama' => $row['nama'],
            'img' => 'data:image/jpeg;base64,' . base64_encode($row['img_dir'])
        ];
    }

    // Cari guru kalau ada murid di periode itu
    foreach ($schoolsInfo as $school => $info) {
        $queryGuru = "SELECT nama FROM akun WHERE asal_sekolah = '$school' AND role = 3 LIMIT 1";
        $resultGuru = mysqli_query($conn, $queryGuru);
        if ($guru = mysqli_fetch_assoc($resultGuru)) {
            $schoolsInfo[$school]['guru'] = $guru['nama'];
        } else {
            $schoolsInfo[$school]['guru'] = 'Belum Ada';
        }
    }

    $output = [];
    foreach ($schoolsInfo as $school => $info) {
        if (!empty($info['murid'])) { // Tampilin sekolah cuma kalo ada muridnya
            $output[] = [
                'asal_sekolah' => $school,
                'guru' => $info['guru'],
                'murid' => $info['murid']
            ];
        }
    }

    echo json_encode($output);
}
?>