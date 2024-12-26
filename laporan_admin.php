<?php
session_start();
include('koneksi.php');
require('auth.php');


// Ambil nama user dari session
$nama_user = isset($_GET['nama']) ? $_GET['nama'] : '';

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

// Ambil bulan dari form POST
$selected_month = isset($_POST['bulan']) ? (int)$_POST['bulan'] : null;

// Query untuk ambil data laporan dari tabel laporan sesuai user yang login dan bulan yang dipilih
$sql_laporan = "SELECT waktu, laporan, img_dir FROM laporan WHERE nama = ?";
if ($selected_month) {
    $sql_laporan .= " AND MONTH(waktu) = ?";
    $stmt_laporan = $conn->prepare($sql_laporan);
    $stmt_laporan->bind_param("si", $nama_user, $selected_month);
} else {
    $stmt_laporan = $conn->prepare($sql_laporan);
    $stmt_laporan->bind_param("s", $nama_user);
}
$stmt_laporan->execute();
$result_laporan = $stmt_laporan->get_result();

$laporan_data = [];
if ($result_laporan->num_rows > 0) {
    while ($row_laporan = $result_laporan->fetch_assoc()) {
        $laporan_data[] = $row_laporan;
    }
} else {
    $laporan_data[] = ['waktu' => '', 'laporan' => 'Belum ada tugas', 'img_dir' => ''];
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
                           WHERE a1.nama = ?";
$stmt_pembimbing_instansi = $conn->prepare($sql_pembimbing_instansi);
$stmt_pembimbing_instansi->bind_param("s", $nama_user);
$stmt_pembimbing_instansi->execute();
$result_pembimbing_instansi = $stmt_pembimbing_instansi->get_result();

$nama_pembimbing_instansi = "";
if ($result_pembimbing_instansi->num_rows > 0) {
    $row_pembimbing_instansi = $result_pembimbing_instansi->fetch_assoc();
    $nama_pembimbing_instansi = $row_pembimbing_instansi['nama_pembimbing'];
} else {
    $nama_pembimbing_instansi = "Belum ditentukan";
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
    <style>
        body {
            font-family: 'Arial', sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f0f0f0;
            color: #333;
        }

        .container {
            margin: 20px auto;
            background: #fff;
            padding: 20px;
            max-width: 90%;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            color: #333;
            position: relative;
            overflow: hidden;
        }

        h2 {
            color: #444;
            font-size: 24px;
            margin-bottom: 20px;
        }

        p {
            margin: 10px 0;
        }

        form {
            margin-bottom: 20px;
        }

        select {
            padding: 10px;
            font-size: 16px;
            border: 1px solid #ddd;
            border-radius: 4px;
            background-color: #fff;
            transition: border-color 0.3s ease;
        }

        select:focus {
            border-color: #007bff;
            outline: none;
        }

        button {
            background-color: #007bff;
            color: #fff;
            border: none;
            padding: 10px 20px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            transition: background-color 0.3s ease;
            margin-bottom: 20px;
        }

        button:hover {
            background-color: #0056b3;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        th,
        td {
            border: 1px solid #ddd;
            padding: 12px;
            text-align: left;
        }

        th {
            background-color: #f7f7f7;
            color: #555;
        }

        td img {
            max-width: 100px;
            height: auto;
            border-radius: 4px;
        }

        @media (max-width: 768px) {
            table {
                font-size: 14px;
            }

            th,
            td {
                padding: 8px;
            }

            select,
            button {
                font-size: 14px;
            }
        }

        @media print {
            button {
                display: none;
            }
        }

        .signature-section {
            margin-top: 50px;
        }

        .signature-container {
            display: flex;
            justify-content: space-around;
            margin-top: 30px;
        }

        .signature-box {
            text-align: center;
        }

        .sign-line {
            width: 200px;
            height: 1px;
            background-color: #000;
            margin: 70px auto 10px;
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

        <form method="POST">
            <label for="month">Laporan Bulan:</label>
            <select name="bulan" onchange="this.form.submit()">
                <option value="">Pilih Bulan</option>
                <?php for ($i = 1; $i <= 12; $i++): ?>
                    <option value="<?php echo $i; ?>" <?php echo (isset($_POST['bulan']) && $_POST['bulan'] == $i) ? 'selected' : ''; ?>>
                        <?php echo date('F', mktime(0, 0, 0, $i, 10)); ?>
                    </option>
                <?php endfor; ?>
            </select>
        </form>

        <button onclick="window.print();return false;">Cetak Laporan</button>

        <table>
            <thead>
                <tr>
                    <th>Tanggal</th>
                    <th>Laporan</th>
                    <th>Bukti Kerja</th>
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
                                <span><?php echo htmlspecialchars($laporan['laporan']); ?></span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <div class="signature-section">
            <div class="signature-container">
                <div class="signature-box">
                    <p>Pembimbing Instansi</p>
                    <div class="sign-line"></div>
                    <p <?php echo ($nama_pembimbing_instansi == "Belum ditentukan") ? 'style="color: #dc3545; font-style: italic;"' : ''; ?>>
                        <?php echo htmlspecialchars($nama_pembimbing_instansi); ?>
                    </p>
                </div>
                <div class="signature-box">
                    <p>Pembimbing Sekolah</p>
                    <div class="sign-line"></div>
                    <p><?php echo htmlspecialchars($nama_pembimbing); ?></p>
                </div>
            </div>
        </div>
    </div>
</body>

</html>