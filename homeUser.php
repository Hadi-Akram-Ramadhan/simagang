<?php
session_start(); // Mulai session
require_once('koneksi.php');
require('auth.php');
require('navUser.php');

// Pastikan session 'role' sudah ter-set dan nilainya adalah 'user'
if (!isset($_SESSION['role']) || $_SESSION['role'] !== '1') {
    header("Location: index.php");
    exit();
}

$userNama = $_SESSION['nama'];

// Ambil img_dir dari tabel akun
$sqlUserImg = "SELECT img_dir FROM akun WHERE nama = ?";
$stmtUserImg = $conn->prepare($sqlUserImg);
$stmtUserImg->bind_param('s', $userNama);
$stmtUserImg->execute();
$resultUserImg = $stmtUserImg->get_result();

if ($resultUserImg->num_rows > 0) {
    $userImg = $resultUserImg->fetch_assoc()['img_dir'];
    $userImg = 'data:image/jpeg;base64,' . base64_encode($userImg); // Konversi biner ke base64 untuk ditampilkan sebagai gambar
} else {
    $userImg = 'data:image/jpeg;base64,' . base64_encode(file_get_contents('default.png')); // Gambar default jika img_dir tidak ditemukan
}

$stmtUserImg->close();

// Periksa apakah ada laporan kerja untuk user hari ini
$sqlCheckLaporan = "SELECT * FROM laporan WHERE DATE(waktu) = ? AND nama = ?";
$stmtCheckLaporan = $conn->prepare($sqlCheckLaporan);
$stmtCheckLaporan->bind_param('ss', $dateToday, $userNama);
$stmtCheckLaporan->execute();
$resultCheckLaporan = $stmtCheckLaporan->get_result();

$laporanExists = $resultCheckLaporan->num_rows > 0;

// Ambil data laporan jika ada
if ($laporanExists) {
    $laporan = $resultCheckLaporan->fetch_assoc();
}

$stmtCheckLaporan->close();

// Query untuk mengambil data absensi berdasarkan user yang sedang login
$sql = "SELECT * FROM images WHERE DATE(waktu) = CURDATE() AND user = ? ORDER BY waktu ASC LIMIT 2";
$stmt = $conn->prepare($sql);
$stmt->bind_param('s', $userNama);
$stmt->execute();
$result = $stmt->get_result();

$absenMasuk = null;
$absenKeluar = null;

if ($result->num_rows > 0) {
    $row1 = $result->fetch_assoc();
    $absenMasuk = $row1;
    if ($result->num_rows > 1) {
        $row2 = $result->fetch_assoc();
        $absenKeluar = $row2;
    }
}

?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Beranda</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="https://code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://code.jquery.com/ui/1.13.2/jquery-ui.min.js"></script>
    <style>
        /* Body dan font */
        body {
            background-color: #fafafa;
            color: #484b6a;
            font-family: 'Poppins', sans-serif;
        }

        /* Konten utama */
        .centered-content {
            display: flex;
            justify-content: center;
            align-items: center;
            flex-direction: column;
        }

        /* Container belakang */
        .belakang {
            max-width: 100%;
            padding: 20px;
            border-radius: 25px;
            text-align: center;
            background-color: #ffffff;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        }

        /* Foto user */
        .foto-user {
            max-width: 100px;
            height: auto;
            padding: 20px;
        }

        /* Waktu container */
        .time-container {
            font-size: 18px;
            margin-bottom: 20px;
            color: #484b6a;
        }

        /* Button modal */
        .btn-outline-light {
            margin-top: 20px;
            color: #484b6a;
            border-color: #484b6a;
            background-color: #fafafa;
        }

        /* Modal */
        .modal {
            color: #484b6a;
            /* Ubah warna teks */
        }

        /* Absen container */
        .absen-container {
            display: flex;
            justify-content: center;
            gap: 20px;
            margin-top: 20px;
            flex-wrap: wrap;
        }

        /* Absen box */
        .absen-box {
            padding: 20px;
            border-radius: 10px;
            text-align: center;
            background-color: #d2d3db;
            margin-bottom: 100px;
            box-shadow: 2px 2px 5px rgba(0, 0, 0, 0.1);
        }

        /* Absen masuk */
        .absen-masuk {
            max-width: 300px;
            padding: 20px;
            border-radius: 20px;
            text-align: center;
            display: inline-block;
            margin-bottom: 5rem;
            background-color: #ffffff;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            flex: 1;
            min-width: 280px;
        }

        .absen-keluar {
            max-width: 300px;
            padding: 20px;
            border-radius: 20px;
            text-align: center;
            display: inline-block;
            margin-bottom: 5rem;
            background-color: #ffffff;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            flex: 1;
            min-width: 280px;
        }

        /* Gradient bukti-container */
        .bukti-container {
            width: 250px;
            padding: 20px;
            border-radius: 20px;
            text-align: center;
            display: inline-block;
            background-color: #e0e0e0;
            margin-bottom: 100px;
            box-shadow: 2px 2px 5px rgba(0, 0, 0, 0.2);
            color: #484b6a;
            /* Warna teks */
        }

        .nama-user {
            z-index: 1;
            color: #484b6a;
            padding-right: 10px;
            font-size: 22px;
            margin-left: auto;
            font-family: 'Poppins', sans-serif;
        }

        h2 {
            font-size: 20px;
        }

        p {
            font-size: 14px;
        }

        /* Update responsive styles */
        @media (max-width: 768px) {
            .belakang {
                margin: 10px;
                padding: 15px;
                max-width: 90%;
            }

            .foto-user {
                max-width: 80px;
            }

            .nama-user {
                font-size: 18px;
            }

            .time-container {
                font-size: 16 px;
            }

            .absen-container {
                gap: 10px;
            }

            .absen-masuk {
                margin-bottom: 20px;
            }

            .absen-keluar {
                margin-bottom: 5rem;
            }
        }

        .loader-container {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            background: #fafafa;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            z-index: 99999999;
        }

        .loader {
            width: 50px;
            height: 50px;
            border: 4px solid #333;
            border-top-color: #00ffaa;
            border-radius: 50%;
            animation: spin 0.8s linear infinite;
            filter: drop-shadow(0 0 5px #00ffaa);
        }

        @keyframes spin {
            to {
                transform: rotate(360deg);
            }
        }

        .content {
            display: none;
        }
    </style>


</head>

<body>
    <div class="loader-container" id="loader">
        <div class="loader"></div>
    </div>

    <div class="content" id="content">
        <div class="centered-content">

            <div class="belakang">
                <img src="<?php echo htmlspecialchars($userImg); ?>" alt="Foto User" class="foto-user">

                <p class="nama-user"><?php echo htmlspecialchars($_SESSION['nama']); ?></p>

                <div class="time-container" id="clock">
                    <!-- Waktu akan diupdate dengan JavaScript -->
                </div>
                <div class="pemisah"></div>
                <button type="button" class="btn btn-outline-dark" data-bs-toggle="modal" data-bs-target="#modalLaporan">

                    Lihat Laporan Pekerjaan
                </button>

                <div class="modal" id="modalLaporan" tabindex="-1" aria-labelledby="modalLaporanLabel" aria-hidden="true">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="modalLaporanLabel">Detail Pekerjaan</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                            </div>
                            <div class="modal-body">
                                <?php
                                // Query untuk mengambil semua laporan kerja user hari ini
                                $dateToday = date('Y-m-d');
                                $sqlGetLaporan = "SELECT * FROM laporan WHERE DATE(waktu) = ? AND nama = ? ORDER BY waktu DESC";
                                $stmtGetLaporan = $conn->prepare($sqlGetLaporan);
                                $stmtGetLaporan->bind_param('ss', $dateToday, $userNama);
                                $stmtGetLaporan->execute();
                                $resultGetLaporan = $stmtGetLaporan->get_result();

                                if ($resultGetLaporan->num_rows > 0) {
                                    $counter = 1;
                                    while ($laporan = $resultGetLaporan->fetch_assoc()) {
                                        echo "<div class='laporan-item mb-4'>";
                                        echo "<h6>Tugas #" . $counter . "</h6>";
                                        echo "<p><b>Waktu Laporan:</b> " . htmlspecialchars($laporan['waktu']) . "</p>";
                                        echo "<p><b>Penjelasan Pekerjaan:</b> " . htmlspecialchars($laporan['laporan']) . "</p>";
                                        echo "<p><b>Gambar Screenshot Pekerjaan:</b><br>";
                                        echo "<img src='data:image/jpeg;base64," . base64_encode($laporan['img_dir']) . "' class='img-fluid' alt='Screenshot Proyek' style='max-height: 200px; max-width: 150px;'></p>";

                                        if ($laporan['status'] == 0) {
                                            echo "<p><span class='badge bg-danger'>Belum Diterima</span></p>";
                                        } else {
                                            echo "<p><span class='badge bg-success'>Sudah Diterima</span></p>";
                                        }
                                        echo "<hr>";
                                        echo "</div>";
                                        $counter++;
                                    }
                                } else {
                                    echo "<p><i>Belum ada laporan kerjaan hari ini nih.</i></p>";
                                }

                                $stmtGetLaporan->close();
                                ?>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="absen-container">
                <div class="absen-masuk" id="absen-masuk">
                    <h2>Absen Masuk</h2>
                    <?php if ($absenMasuk) : ?>
                        <img src="data:image/jpeg;base64,<?php echo base64_encode($absenMasuk['img_dir']); ?>"
                            alt="Absen Masuk"
                            class="img-fluid"
                            style="max-width: 100%; min-width: 45%; width: 250px; height: 150px; border-radius: 8px;">
                        <p><?php echo htmlspecialchars($absenMasuk['waktu']); ?></p>
                    <?php else : ?>
                        <i class="fa-regular fa-camera fa-xl"></i>
                        <p>Belum Absen Nih</p>
                    <?php endif; ?>
                </div>
                <div class="absen-keluar" id="absen-keluar">
                    <h2>Absen Keluar</h2>
                    <?php if ($absenKeluar) : ?>
                        <img src="data:image/jpeg;base64,<?php echo base64_encode($absenKeluar['img_dir']); ?>"
                            alt="Absen Keluar"
                            class="img-fluid"
                            style="max-width: 100%; min-width: 45%; width: 250px; height: 150px; border-radius: 8px;">
                        <p><?php echo htmlspecialchars($absenKeluar['waktu']); ?></p>
                    <?php else : ?>
                        <i class="fa-regular fa-camera fa-xl"></i>
                        <p>Belum Absen Nih</p>
                    <?php endif; ?>
                </div>
            </div>

        </div>

        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js" integrity="sha384-YXKmqe61jcXM5frB9Q5jxO0u/RIWzCOpPX0Qw1w1QpNU7sYmP6oh+Gwv6NXj3YiS" crossorigin="anonymous"></script>
        <script>
            window.addEventListener('load', function() {
                document.getElementById('loader').style.display = 'none';
                document.getElementById('content').style.display = 'block';
            });

            // Function untuk mengambil waktu sekarang dan menampilkan
            function updateTime() {
                var now = new Date();
                var options = {
                    weekday: 'long',
                    year: 'numeric',
                    month: 'long',
                    day: 'numeric',
                    hour: 'numeric',
                    minute: 'numeric',
                    second: 'numeric'
                };
                var formattedTime = now.toLocaleDateString('en-US', options);
                document.getElementById('clock').innerHTML = formattedTime;
            }

            // Update waktu setiap detik
            setInterval(updateTime, 1000);

            // Pemanggilan pertama kali saat halaman dimuat
            updateTime();

            // Inisialisasi datepicker
            $(function() {
                $("#datepicker").datepicker();
            });
        </script>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.min.js" integrity="sha384-cLb3Z5scps3vD0RxmzLO2GZmP7Kl/C/3fj8d8v8KdG+USMoSQrG/poRwuDHL1w6c" crossorigin="anonymous"></script>
    </div>
</body>

</html>