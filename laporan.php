<?php
session_start();
require('koneksi.php');
require('auth.php');
require('navUser.php');

if (!isset($_SESSION['role']) || $_SESSION['role'] !== '1') {
    header("Location: index.php");
    exit();
}

$showAlert = ''; // Variable untuk menyimpan SweetAlert

// Get selected month & year (default ke bulan ini)
$selected_month = isset($_GET['month']) ? $_GET['month'] : date('m');
$selected_year = isset($_GET['year']) ? $_GET['year'] : date('Y');

// Generate start & end date buat bulan yang dipilih
$start_date = "$selected_year-$selected_month-01";
$end_date = date('Y-m-t', strtotime($start_date)); // t = hari terakhir di bulan itu

// Update query untuk ambil data sesuai bulan
$sql = "SELECT UNIX_TIMESTAMP(waktu) as timestamp FROM laporan WHERE nama = ? AND waktu BETWEEN ? AND ? ORDER BY waktu DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("sss", $_SESSION['nama'], $start_date, $end_date);
$stmt->execute();
$result = $stmt->get_result();

// Array buat nyimpen tanggal yang udah submit
$submitted_dates = [];
while ($row = $result->fetch_assoc()) {
    $submitted_dates[] = date('Y-m-d', $row['timestamp']);
}

// Generate array untuk semua tanggal di bulan itu
$date_range = [];
$current = strtotime($start_date);
$last_day = strtotime($end_date);

while ($current <= $last_day) {
    $date_range[] = date('Y-m-d', $current);
    $current = strtotime('+1 day', $current);
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nama = $_SESSION['nama'];
    $selected_date = $_POST['selected_date'];
    $laporan = $_POST['laporan'];

    // Convert ke timestamp
    $waktu = strtotime($selected_date);

    // Cek duplikat (pake UNIX_TIMESTAMP)
    $check_sql = "SELECT COUNT(*) AS count FROM laporan WHERE nama = ? AND DATE(FROM_UNIXTIME(waktu)) = DATE(FROM_UNIXTIME(?))";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("si", $nama, $waktu);
    $check_stmt->execute();
    $result = $check_stmt->get_result();
    $row = $result->fetch_assoc();
    $count = $row['count'];

    $check_stmt->close();

    if ($count > 0) {
        $showAlert = "Swal.fire({
            title: 'Ups!',
            text: 'Maaf ya, kamu cuma bisa upload laporan sekali aja dalam sehari.',
            icon: 'warning',
            confirmButtonText: 'Oke'
        });";
    } else {
        // Lanjutkan proses upload seperti sebelumnya
        $laporan = $_POST['laporan'];

        // Handle foto upload
        $foto_blob = null;
        $uploadOk = 1;

        // Cek apakah ada file yang diupload
        if (isset($_FILES['foto']) && $_FILES['foto']['error'] == 0) {
            $imageFileType = strtolower(pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION));

            // Check if image file is a actual image or fake image
            $check = getimagesize($_FILES['foto']['tmp_name']);
            if ($check !== false) {
                // Create image from file
                switch ($imageFileType) {
                    case 'jpg':
                    case 'jpeg':
                        $img = imagecreatefromjpeg($_FILES['foto']['tmp_name']);
                        break;
                    case 'png':
                        $img = imagecreatefrompng($_FILES['foto']['tmp_name']);
                        break;
                    case 'gif':
                        $img = imagecreatefromgif($_FILES['foto']['tmp_name']);
                        break;
                    default:
                        $uploadOk = 0;
                        break;
                }

                // Resize image
                $width = imagesx($img);
                $height = imagesy($img);
                $newWidth = 500; // Set new width
                $newHeight = floor($height * ($newWidth / $width));
                $tmpImg = imagecreatetruecolor($newWidth, $newHeight);
                imagecopyresampled($tmpImg, $img, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);

                // Capture the image into a string output buffer
                ob_start();
                imagejpeg($tmpImg);
                $foto_blob = ob_get_clean();

                // Clean up
                imagedestroy($img);
                imagedestroy($tmpImg);
            } else {
                $showAlert = "Swal.fire({
                    title: 'Error',
                    text: 'Waduh, file yang kamu upload bukan gambar nih.',
                    icon: 'error',
                    confirmButtonText: 'Oke'
                });";
                $uploadOk = 0;
            }
        } else {
            $showAlert = "Swal.fire({
                title: 'Error',
                text: 'Ups, kayaknya kamu belum pilih file deh.',
                icon: 'error',
                confirmButtonText: 'Oke'
            });";
            $uploadOk = 0;
        }

        // Check file size
        if ($_FILES['foto']['size'] > 15) {
            $showAlert = "Swal.fire({
                title: 'Error',
                text: 'Maaf ya, ukuran file kamu terlalu besar.',
                icon: 'error',
                confirmButtonText: 'Oke'
            });";
            $uploadOk = 0;
        }

        // Allow certain file formats
        if ($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg" && $imageFileType != "gif") {
            $showAlert = "Swal.fire({
                title: 'Error',
                text: 'Maaf, cuma file JPG, JPEG, PNG & GIF yang bisa diupload ya.',
                icon: 'error',
                confirmButtonText: 'Oke'
            });";
            $uploadOk = 0;
        }

        if ($uploadOk == 0) {
            $showAlert = "Swal.fire({
                title: 'Error',
                text: 'Maaf ya, file kamu gagal diupload.',
                icon: 'error',
                confirmButtonText: 'Oke'
            });";
        }

        // Insert data dengan timestamp
        $sql = "INSERT INTO laporan (nama, laporan, img_dir, waktu) VALUES (?, ?, ?, FROM_UNIXTIME(?))";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssi", $nama, $laporan, $foto_blob, $waktu);

        if ($stmt->execute()) {
            error_log("Insert berhasil dengan waktu: " . $waktu);
            $showAlert = "Swal.fire({
                title: 'Berhasil',
                text: 'Laporan kamu udah berhasil diupload!',
                icon: 'success',
                confirmButtonText: 'Oke'
            });";
        } else {
            error_log("Error insert: " . $stmt->error);
            $showAlert = "Swal.fire({
                title: 'Error',
                text: 'Ups, ada kesalahan: " . $stmt->error . "',
                icon: 'error',
                confirmButtonText: 'Oke'
            });";
        }

        $stmt->close();
    }

    $conn->close();
}

// Ganti query pembimbing instansi yang lama dengan yang ini
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
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <title>Laporan Kerja</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap');

        body {
            font-family: 'Poppins', sans-serif;
            background-color: #fafafa;
            color: #484b6a;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }

        .container {
            width: 50rem;
            max-width: 80%;
            padding: 30px;
            border-radius: 12px;
            background-color: #ffffff;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            margin-top: 9rem;
            margin-bottom: 8rem;
        }

        h2 {
            margin-bottom: 30px;
            text-align: center;
            color: #484b6a;
            font-size: 24px;
            font-weight: 600;
        }

        .form-group {
            margin-bottom: 25px;
        }

        .form-group label {
            display: block;
            margin-bottom: 10px;
            font-weight: 600;
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .form-group input[type="text"],
        .form-group input[type="file"],
        .form-group textarea {
            width: 100%;
            padding: 12px 15px;
            font-size: 12px;
            border: 2px solid #d2d3db;
            background-color: #fafafa;
            color: #484b6a;
            border-radius: 8px;
            box-sizing: border-box;
            transition: all 0.3s ease;
        }

        .form-group input[type="text"]:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #9394a5;
            box-shadow: 0 0 0 3px rgba(147, 148, 165, 0.2);
        }

        .form-group input[type="file"] {
            padding: 10px;
            background-color: #fafafa;
        }

        .form-group input[type="file"]::file-selector-button {
            background-color: #41B3A2;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        .form-group input[type="file"]::file-selector-button:hover {
            background-color: #0D7C66;
        }

        .form-group textarea {
            min-height: 120px;
            resize: vertical;
        }

        .form-group button {
            width: 100%;
            padding: 14px;
            font-size: 16px;
            font-weight: 600;
            background-color: #41B3A2;
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .form-group button:hover {
            background-color: #0D7C66;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }

        .spinner {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 999999 !important;
        }

        .spinner::after {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 50px;
            height: 50px;
            border: 5px solid #fff;
            border-top: 5px solid #9394a5;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% {
                transform: translate(-50%, -50%) rotate(0deg);
            }

            100% {
                transform: translate(-50%, -50%) rotate(360deg);
            }
        }

        .timeline-container {
            padding: 15px;
            margin-bottom: 20px;
            width: 100%;
        }

        .timeline {
            position: relative;
            max-height: 500px;
            overflow-y: auto;
            padding: 20px;
        }

        /* Garis vertikal timeline */
        .timeline::before {
            content: '';
            position: absolute;
            left: 20px;
            top: 0;
            bottom: 0;
            width: 2px;
            background: #e0e0e0;
        }

        .timeline-item {
            position: relative;
            margin-left: 30px;
            margin-bottom: 15px;
            padding: 12px;
            font-size: 14px;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        /* Dot di timeline */
        .timeline-dot {
            position: absolute;
            left: -40px;
            width: 20px;
            height: 20px;
            border-radius: 50%;
            background: #fff;
            border: 2px solid #e0e0e0;
            top: 50%;
            transform: translateY(-50%);
        }

        .timeline-dot.submitted {
            background: #41B3A2;
            border-color: #41B3A2;
        }

        .timeline-dot.missing {
            background: #ff4444;
            border-color: #ff4444;
        }

        .timeline-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .timeline-date {
            font-weight: 600;
            color: #333;
        }

        .timeline-status {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .badge {
            padding: 5px 10px;
            border-radius: 15px;
            font-size: 12px;
        }

        .badge.submitted {
            background: #e8f5e9;
            color: #2e7d32;
        }

        .badge.missing {
            background: #ffebee;
            color: #c62828;
        }

        .submit-btn {
            background: #41B3A2;
            color: white;
            border: none;
            padding: 5px 15px;
            border-radius: 15px;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .submit-btn:hover {
            background: #2a9d8f;
            transform: translateY(-2px);
        }

        /* Custom scrollbar buat timeline */
        .timeline::-webkit-scrollbar {
            width: 8px;
        }

        .timeline::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 4px;
        }

        .timeline::-webkit-scrollbar-thumb {
            background: #888;
            border-radius: 4px;
        }

        .timeline::-webkit-scrollbar-thumb:hover {
            background: #555;
        }

        .month-selector {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 40px;
            margin-bottom: 15px;
            position: relative;
            background-color: #ffffff;
            width: calc(100% - 80px);
            margin: 0 auto;
            height: 3rem;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        .month-nav {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            width: 30px;
            height: 30px;
            border-radius: 50%;
            background: #41B3A2;
            color: white;
            border: none;
            cursor: pointer;
            font-size: 14px;
            z-index: 1;
            transition: all 0.3s ease;
        }

        /* Tombol previous (kiri) */
        .month-nav:first-child {
            left: 10px;
        }

        /* Tombol next (kanan) */
        .month-nav:last-child {
            right: 10px;
        }

        .month-selector h3 {
            font-size: 16px;
            margin: 0 auto;
            padding: 0 40px;
        }

        .timeline-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px;
            background: #41B3A2;
            color: white;
            border-radius: 8px;
            cursor: pointer;
            transition: 0.3s;
        }

        .timeline-header:hover {
            background: #0D7C66;
        }

        .toggle-icon {
            transition: 0.3s;
        }

        .toggle-icon.active {
            transform: rotate(180deg);
        }

        .timeline-content {
            margin-top: 15px;
            transition: all 0.3s ease;
        }

        .modal {
            display: none;
            position: fixed;
            z-index: 100001;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
        }

        .modal-content {
            background-color: #fff;
            margin: 10% auto;
            padding: 20px;
            border-radius: 8px;
            width: 80%;
            max-width: 600px;
            position: relative;
            animation: modalSlide 0.3s ease;
        }

        @keyframes modalSlide {
            from {
                transform: translateY(-100px);
                opacity: 0;
            }

            to {
                transform: translateY(0);
                opacity: 1;
            }
        }

        .close {
            position: absolute;
            right: 20px;
            top: 15px;
            font-size: 24px;
            cursor: pointer;
            color: #666;
        }

        .close:hover {
            color: #000;
        }

        #modalDate {
            color: #41B3A2;
            font-weight: bold;
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            .timeline-container {
                padding: 10px;
            }

            .timeline-item {
                margin-left: 25px;
                padding: 10px;
            }

            .timeline-content {
                justify-content: space-between;
            }

            .timeline-date {
                font-size: 13px;
            }

            .badge,
            .submit-btn {
                font-size: 11px;
            }
        }

        @media (max-width: 480px) {
            .timeline-dot {
                width: 15px;
                height: 15px;
                left: -30px;
            }

            .timeline::before {
                left: 15px;
            }
        }

        h3 {
            font-size: 14px;
        }

        /* Tambahan CSS untuk responsif timeline */
        @media (max-width: 768px) {
            .container {
                width: 95%;
                padding: 15px;
                margin-top: 6rem;
            }

            .timeline-item {
                padding: 8px;
            }

            .timeline-content {
                flex-direction: column;
                align-items: flex-start;
                gap: 8px;
            }

            .timeline-status {
                width: 100%;
                display: flex;
                flex-direction: column;
                align-items: flex-start;
                gap: 5px;
            }

            .badge {
                font-size: 11px;
                padding: 4px 8px;
                width: fit-content;
            }

            .submit-btn {
                padding: 4px 12px;
                font-size: 11px;
                width: 100%;
                margin-top: 2px;
            }

            .month-selector {
                width: calc(100% - 30px);
                padding: 0 15px;
                height: 2.5rem;
            }

            .month-selector h3 {
                font-size: 14px;
                padding: 0 25px;
            }

            .month-nav {
                width: 25px;
                height: 25px;
                font-size: 12px;
            }
        }

        @media (max-width: 480px) {
            .timeline::before {
                left: 12px;
            }

            .timeline-dot {
                left: -25px;
                width: 12px;
                height: 12px;
            }

            .timeline-item {
                margin-left: 20px;
                margin-bottom: 10px;
            }

            .timeline-date {
                font-size: 12px;
            }

            .badge,
            .submit-btn {
                font-size: 10px;
            }

            .timeline-status {
                margin-top: 4px;
            }

            .badge {
                width: 100%;
                text-align: center;
            }

            .submit-btn {
                width: 100%;
                text-align: center;
            }
        }
    </style>
</head>

<body>
    <div class="spinner" id="loadingSpinner"></div>
    <div class="container">
        <h2>Laporan Kerja</h2>
        <form action="laporan.php" method="post" enctype="multipart/form-data">
            <input type="hidden" id="selected_date" name="selected_date" value="<?php echo date('Y-m-d'); ?>">
            <div class="form-group">
                <label for="laporan">Laporan Tugas:</label>
                <textarea id="laporan" name="laporan" placeholder="Masukkan laporan tugas..." required></textarea>
            </div>
            <div class="form-group">
                <label for="foto">Upload Foto Tugas:</label>
                <input type="file" id="foto" name="foto" accept="image/*" required>
            </div>
            <div class="form-group">
                <button type="submit">Submit</button>
            </div>
        </form>

        <div class="timeline-container">
            <div class="timeline-header" onclick="toggleTimeline()">
                <h3>Riwayat Pekerjaan</h3>
                <span class="toggle-icon">▼</span>
            </div>

            <div class="timeline-content" style="display: none;">
                <div class="month-selector">
                    <button class="month-nav" onclick="changeMonth(-1)">←</button>
                    <h3><?php echo date('F Y', strtotime($start_date)); ?></h3>
                    <button class="month-nav" onclick="changeMonth(1)">→</button>
                </div>

                <div class="timeline">
                    <?php foreach ($date_range as $date): ?>
                        <div class="timeline-item">
                            <div class="timeline-dot <?php echo in_array($date, $submitted_dates) ? 'submitted' : 'missing'; ?>"></div>
                            <div class="timeline-content">
                                <div class="timeline-date"><?php echo date('d M Y', strtotime($date)); ?></div>
                                <div class="timeline-status">
                                    <?php if (in_array($date, $submitted_dates)): ?>
                                        <span class="badge submitted">Sudah Dikumpulkan ✓</span>
                                    <?php else: ?>
                                        <span class="badge missing">Belum Dikumpulkan</span>
                                        <?php if (strtotime($date) <= strtotime('today')): ?>
                                            <button class="submit-btn" data-date="<?php echo $date; ?>">Submit</button>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Tambah modal di bawah container -->
    <div id="submitModal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h3>Submit Laporan - <span id="modalDate"></span></h3>
            <form id="submitForm" action="laporan.php" method="post" enctype="multipart/form-data">
                <input type="hidden" id="modal_selected_date" name="selected_date">
                <div class="form-group">
                    <label for="laporan">Laporan Tugas:</label>
                    <textarea id="laporan" name="laporan" placeholder="Masukkan laporan tugas..." required></textarea>
                </div>
                <div class="form-group">
                    <label for="foto">Upload Foto Tugas:</label>
                    <input type="file" id="foto" name="foto" accept="image/*" required>
                </div>
                <div class="form-group">
                    <button type="submit">Submit Laporan</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        document.querySelector('form').addEventListener('submit', function(e) {
            document.getElementById('loadingSpinner').style.display = 'block';
        });

        <?php if ($showAlert != ''): ?>
            <?php echo $showAlert; ?>
            document.getElementById('loadingSpinner').style.display = 'none';
        <?php endif; ?>

        function changeMonth(offset) {
            const urlParams = new URLSearchParams(window.location.search);
            let currentMonth = parseInt(urlParams.get('month')) || new Date().getMonth() + 1;
            let currentYear = parseInt(urlParams.get('year')) || new Date().getFullYear();

            // Get current date untuk pembanding
            const today = new Date();
            const currentDate = new Date(currentYear, currentMonth - 1);

            // Cek apakah perlu disable tombol
            const prevBtn = document.querySelector('.month-nav:first-child');
            const nextBtn = document.querySelector('.month-nav:last-child');

            // Cek untuk next button (max = bulan sekarang)
            if (currentDate.getMonth() === today.getMonth() &&
                currentDate.getFullYear() === today.getFullYear()) {
                nextBtn.disabled = true;
                nextBtn.style.opacity = '0.5';
                nextBtn.style.cursor = 'not-allowed';
                if (offset > 0) return;
            } else {
                nextBtn.disabled = false;
                nextBtn.style.opacity = '1';
                nextBtn.style.cursor = 'pointer';
            }

            // Cek untuk prev button (min = 12 bulan ke belakang)
            const oneYearAgo = new Date(today.getFullYear() - 1, today.getMonth());
            if (currentDate <= oneYearAgo) {
                prevBtn.disabled = true;
                prevBtn.style.opacity = '0.5';
                prevBtn.style.cursor = 'not-allowed';
                if (offset < 0) return;
            } else {
                prevBtn.disabled = false;
                prevBtn.style.opacity = '1';
                prevBtn.style.cursor = 'pointer';
            }

            // Lanjut proses ganti bulan kalo ga ada masalah
            currentMonth += offset;

            if (currentMonth > 12) {
                currentMonth = 1;
                currentYear++;
            } else if (currentMonth < 1) {
                currentMonth = 12;
                currentYear--;
            }

            // Fetch timeline data pake AJAX
            fetch(`get_timeline.php?month=${currentMonth}&year=${currentYear}`)
                .then(response => response.text())
                .then(data => {
                    document.querySelector('.timeline').innerHTML = data;
                    // Update judul bulan
                    document.querySelector('.month-selector h3').textContent = new Date(currentYear, currentMonth - 1).toLocaleDateString('id-ID', {
                        month: 'long',
                        year: 'numeric'
                    });
                    // Update URL tanpa refresh
                    history.pushState({}, '', `?month=${currentMonth}&year=${currentYear}`);
                });
        }

        // Update selected date pas klik submit
        document.querySelectorAll('.submit-btn').forEach(button => {
            button.addEventListener('click', function() {
                document.getElementById('selected_date').value = this.dataset.date;
                document.querySelector('form').scrollIntoView({
                    behavior: 'smooth'
                });
            });
        });

        function toggleTimeline() {
            const content = document.querySelector('.timeline-content');
            const icon = document.querySelector('.toggle-icon');

            if (content.style.display === 'none') {
                content.style.display = 'block';
                icon.classList.add('active');
            } else {
                content.style.display = 'none';
                icon.classList.remove('active');
            }
        }

        // Update event listener untuk tombol submit
        document.addEventListener('click', function(e) {
            if (e.target && e.target.classList.contains('submit-btn')) {
                const date = e.target.dataset.date; // Format: YYYY-MM-DD
                const formattedDate = new Date(date).toLocaleDateString('id-ID', {
                    weekday: 'long',
                    year: 'numeric',
                    month: 'long',
                    day: 'numeric'
                });

                // Set value ke hidden input di modal
                document.getElementById('modal_selected_date').value = date;
                document.getElementById('modalDate').textContent = formattedDate;
                document.getElementById('submitModal').style.display = 'block';
            }
        });

        // Close modal
        document.querySelector('.close').onclick = function() {
            document.getElementById('submitModal').style.display = 'none';
        }

        // Close modal kalo user klik di luar modal
        window.onclick = function(event) {
            const modal = document.getElementById('submitModal');
            if (event.target == modal) {
                modal.style.display = 'none';
            }
        }

        // Handle form submission dengan AJAX
        document.getElementById('submitForm').onsubmit = function(e) {
            e.preventDefault();

            const formData = new FormData(this);

            // Show loading
            document.getElementById('loadingSpinner').style.display = 'block';

            fetch('laporan.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.text())
                .then(data => {
                    // Hide loading
                    document.getElementById('loadingSpinner').style.display = 'none';

                    // Close modal
                    document.getElementById('submitModal').style.display = 'none';

                    // Refresh timeline
                    const urlParams = new URLSearchParams(window.location.search);
                    const month = urlParams.get('month') || new Date().getMonth() + 1;
                    const year = urlParams.get('year') || new Date().getFullYear();
                    changeMonth(0); // Refresh current month

                    // Show success message
                    Swal.fire({
                        title: 'Berhasil!',
                        text: 'Laporan berhasil disubmit',
                        icon: 'success'
                    });
                })
                .catch(error => {
                    console.error('Error:', error);
                    Swal.fire({
                        title: 'Error!',
                        text: 'Gagal submit laporan',
                        icon: 'error'
                    });
                });
        };

        // Tambah function buat auto update timeline
        function autoUpdateTimeline() {
            const urlParams = new URLSearchParams(window.location.search);
            let currentMonth = parseInt(urlParams.get('month')) || new Date().getMonth() + 1;
            let currentYear = parseInt(urlParams.get('year')) || new Date().getFullYear();

            // Fetch timeline data
            fetch(`get_timeline.php?month=${currentMonth}&year=${currentYear}`)
                .then(response => response.text())
                .then(data => {
                    document.querySelector('.timeline').innerHTML = data;
                })
                .catch(error => console.error('Error:', error));
        }

        // Set interval buat auto update (misal tiap 30 detik)
        setInterval(autoUpdateTimeline, 30000);

        // Juga update pas pertama load
        document.addEventListener('DOMContentLoaded', autoUpdateTimeline);
    </script>
</body>

</html>