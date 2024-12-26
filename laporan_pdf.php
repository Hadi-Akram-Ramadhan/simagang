<?php
session_start();
include('koneksi.php');
require('auth.php');


// Ambil nama user dari session
$nama_user = $_SESSION['nama'];

// Ambil bulan yang dipilih dari GET request, jika ada
$selected_month = isset($_GET['month']) ? $_GET['month'] : date('m');

// Query untuk ambil data dari tabel akun sesuai user yang login
$sql = "SELECT nis, asal_sekolah, magang_masuk, magang_keluar FROM akun WHERE nama = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $nama_user);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $nis = $row['nis'];
    $asal_sekolah = $row['asal_sekolah'];
    $periode = $row['magang_masuk'] . " - " . $row['magang_keluar'];
} else {
    $nis = "";
    $asal_sekolah = "";
    $periode = "";
}

$stmt->close();

// Query untuk ambil data laporan dari tabel laporan sesuai user yang login dan bulan yang dipilih
$sql_laporan = "SELECT waktu, laporan, img_dir, comment, comment_g FROM laporan WHERE nama = ? AND MONTH(waktu) = ? AND status = '1' AND status_g = '1'";
$stmt_laporan = $conn->prepare($sql_laporan);
$stmt_laporan->bind_param("si", $nama_user, $selected_month);
$stmt_laporan->execute();
$result_laporan = $stmt_laporan->get_result();

$laporan_data = [];
if ($result_laporan->num_rows > 0) {
    while ($row_laporan = $result_laporan->fetch_assoc()) {
        $laporan_data[] = $row_laporan;
    }
} else {
    $laporan_data[] = ['waktu' => 'Belum ada tugas', 'laporan' => 'Belum ada tugas', 'img_dir' => '', 'comment' => '', 'comment_g' => ''];
}

$stmt_laporan->close();

// Query untuk ambil nama pembimbing sekolah
$sql_pembimbing = "SELECT nama FROM akun WHERE role = 3 AND asal_sekolah = ?";
$stmt_pembimbing = $conn->prepare($sql_pembimbing);
$stmt_pembimbing->bind_param("s", $asal_sekolah);
$stmt_pembimbing->execute();
$result_pembimbing = $stmt_pembimbing->get_result();

$nama_pembimbing = "";
if ($result_pembimbing->num_rows > 0) {
    $row_pembimbing = $result_pembimbing->fetch_assoc();
    $nama_pembimbing = $row_pembimbing['nama'];
}
$stmt_pembimbing->close();

// Query untuk ambil nama pembimbing instansi
$sql_pembimbing_instansi = "SELECT a2.nama as nama_pembimbing 
                           FROM akun a1 
                           JOIN akun a2 ON a1.pembimbing = a2.nama 
                           WHERE a1.nama = ? AND a2.role = 4";

$stmt_pembimbing_instansi = $conn->prepare($sql_pembimbing_instansi);
$stmt_pembimbing_instansi->bind_param("s", $nama_user);
$stmt_pembimbing_instansi->execute();
$result_pembimbing_instansi = $stmt_pembimbing_instansi->get_result();



$nama_pembimbing_instansi = "";
if ($result_pembimbing_instansi->num_rows > 0) {
    $row_pembimbing_instansi = $result_pembimbing_instansi->fetch_assoc();
    $nama_pembimbing_instansi = $row_pembimbing_instansi['nama_pembimbing'];
}
$stmt_pembimbing_instansi->close();

$conn->close();
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" href="image/kementrian.png">
    <title>Laporan PDF</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f7f9;
            color: #333;
            line-height: 1.6;
        }

        .container {
            margin: 30px auto;
            background: #fff;
            padding: 30px;
            max-width: 90%;
            border-radius: 12px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        }

        h2 {
            color: #2c3e50;
            font-size: 28px;
            margin-bottom: 25px;
            font-weight: 600;
        }

        p {
            margin: 12px 0;
            font-size: 15px;
        }

        select {
            padding: 12px;
            font-size: 15px;
            border: 1px solid #e0e0e0;
            border-radius: 6px;
            background-color: #fff;
            transition: all 0.3s ease;
            font-family: 'Poppins', sans-serif;
        }

        select:focus {
            border-color: #3498db;
            box-shadow: 0 0 0 2px rgba(52, 152, 219, 0.2);
        }

        button {
            background-color: #3498db;
            color: #fff;
            border: none;
            padding: 12px 24px;
            border-radius: 6px;
            cursor: pointer;
            font-size: 15px;
            transition: background-color 0.3s ease;
            margin-bottom: 25px;
            font-family: 'Poppins', sans-serif;
            font-weight: 500;
        }

        button:hover {
            background-color: #2980b9;
        }

        table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            margin-bottom: 25px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
            overflow: hidden;
        }

        th,
        td {
            border: 1px solid #e0e0e0;
            padding: 15px;
            text-align: left;
        }

        th {
            background-color: #f2f6f9;
            color: #2c3e50;
            font-weight: 600;
        }

        td img {
            max-width: 100px;
            height: auto;
            border-radius: 4px;
        }

        .signature-section {
            margin-top: 60px;
        }

        .signature-container {
            display: flex;
            justify-content: space-around;
            margin-top: 40px;
        }

        .signature-box {
            text-align: center;
        }

        .sign-line {
            width: 200px;
            height: 1px;
            background-color: #2c3e50;
            margin: 80px auto 15px;
        }

        @media (max-width: 768px) {
            .container {
                padding: 20px;
            }

            table {
                font-size: 14px;
            }

            th,
            td {
                padding: 10px;
            }

            select,
            button {
                font-size: 14px;
            }
        }

        @media print {
            body {
                background-color: #fff;
            }

            .container {
                margin: 0;
                padding: 20px;
                max-width: 100%;
                box-shadow: none;
            }

            button {
                display: none;
            }

            table {
                page-break-inside: auto;
            }

            tr {
                page-break-inside: avoid;
                page-break-after: auto;
            }

            th,
            td {
                padding: 10px;
            }

            h2 {
                margin-top: 0;
            }

            .signature-section {
                margin-top: 30px;
            }

            .signature-container {
                page-break-inside: avoid;
            }

            @page {
                margin: 0.5cm;
            }
        }
    </style>
</head>

<body>
    <div class="container">
        <h2>Laporan Kegiatan Magang</h2>
        <p>Nama: <?php echo htmlspecialchars($nama_user); ?></p>
        <p>No.Pengenal: <?php echo htmlspecialchars($nis); ?></p>
        <p>Asal: <?php echo htmlspecialchars($asal_sekolah); ?></p>
        <p>Periode: <?php echo htmlspecialchars($periode); ?></p>

        <form method="get" action="">
            <label for="month">Laporan Bulan:</label>
            <select name="month" id="month" onchange="this.form.submit()">
                <?php
                for ($m = 1; $m <= 12; $m++) {
                    $month_num = str_pad($m, 2, '0', STR_PAD_LEFT);
                    $selected = $month_num == $selected_month ? 'selected' : '';
                    $month_name = [
                        '01' => 'Januari',
                        '02' => 'Februari',
                        '03' => 'Maret',
                        '04' => 'April',
                        '05' => 'Mei',
                        '06' => 'Juni',
                        '07' => 'Juli',
                        '08' => 'Agustus',
                        '09' => 'September',
                        '10' => 'Oktober',
                        '11' => 'November',
                        '12' => 'Desember'
                    ][$month_num];
                    echo "<option value=\"$month_num\" $selected>$month_name</option>";
                }
                ?>
            </select>
        </form>

        <button onclick="window.print();return false;">Cetak Laporan</button>

        <table>
            <thead>
                <tr>
                    <th>Tanggal</th>
                    <th>Laporan</th>
                    <th>Bukti Kerja</th>
                    <th>Komentar Pembimbing Instansi</th>
                    <th>Komentar Guru Pembimbing</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($laporan_data as $laporan): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($laporan['waktu']); ?></td>
                        <td><?php echo htmlspecialchars($laporan['laporan']); ?></td>
                        <td>
                            <?php if ($laporan['img_dir']): ?>
                                <?php echo '<img src="data:image/jpeg;base64,' . base64_encode($laporan['img_dir']) . '" alt="Bukti Kerja">'; ?>
                            <?php else: ?>
                                <span>Belum ada bukti</span>
                            <?php endif; ?>
                        </td>
                        <td><?php echo htmlspecialchars($laporan['comment']); ?></td>
                        <td><?php echo htmlspecialchars($laporan['comment_g']); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <div class="signature-section">
            <div class="signature-container">
                <div class="signature-box">
                    <p>Pembimbing Instansi</p>
                    <div class="sign-line"></div>
                    <p><?php echo !empty($nama_pembimbing_instansi) ? htmlspecialchars($nama_pembimbing_instansi) : '<span style="color: red;">Belum ada pembimbing instansi</span>'; ?></p>
                </div>
                <div class="signature-box">
                    <p>Pembimbing Sekolah</p>
                    <div class="sign-line"></div>
                    <p><?php echo !empty($nama_pembimbing) ? htmlspecialchars($nama_pembimbing) : '<span style="color: red;">Belum ada pembimbing sekolah</span>'; ?></p>
                </div>
            </div>
        </div>
    </div>
</body>

</html>