<?php
session_start();
require('koneksi.php');
require('auth.php');
require('navUser.php');

function getDaysInMonth($month, $year)
{
    return cal_days_in_month(CAL_GREGORIAN, $month, $year);
}

// Basic auth check
if (!isset($_SESSION['role']) || $_SESSION['role'] !== '1') {
    header("Location: index.php");
    exit();
}

// Ambil periode magang user
$sql_periode = "SELECT magang_masuk, magang_keluar FROM akun WHERE nama = ?";
$stmt_periode = $conn->prepare($sql_periode);
$stmt_periode->bind_param("s", $_SESSION['nama']);
$stmt_periode->execute();
$result_periode = $stmt_periode->get_result();
$periode = $result_periode->fetch_assoc();

// Convert ke DateTime objects untuk lebih mudah handle periodenya
$start_date = new DateTime($periode['magang_masuk']);
$end_date = new DateTime($periode['magang_keluar']);

// Generate array of months between start and end date
$months = [];
$current = clone $start_date;

while ($current <= $end_date) {
    $months[] = [
        'num' => (int)$current->format('n'),
        'year' => (int)$current->format('Y'),
        'name' => $current->format('F')
    ];
    $current->modify('+1 month');
}

// Get current month and year for comparison
$current_month = (int)date('n');
$current_year = (int)date('Y');
$current_day = (int)date('j');
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pengumpulan Tugas</title>
    <style>
        * {
            margin: 0;
            padding: 0;


        }

        body {
            font-family: 'Poppins', sans-serif;
            background-color: #fafafa;
            color: #484b6a;

            margin: 0;

        }

        .container {
            max-width: 1200px;
            margin-top: 6rem;
            padding: 0 15px;
            margin-bottom: 6rem;
        }

        .card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
            padding: 16px;
            margin-top: 2rem;
        }

        .card-timeline {
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 12px rgba(0, 75, 143, 0.1);
            padding: 16px;
            margin-bottom: 16px;
        }

        .section-title {
            font-size: 24px;
            font-weight: 600;
            color: #2d3748;
            margin-bottom: 1rem;
        }

        /* Form Styling */
        .upload-form textarea {
            width: 100%;
            padding: 12px;
            border: 2px solid #e2e8f0;
            border-radius: 8px;
            resize: vertical;
            min-height: 100px;
            font-size: 0.9rem;
            transition: border-color 0.3s ease;
        }

        .upload-form textarea:focus {
            outline: none;
            border-color: #004B8F;
        }

        .file-input {
            background: #f8fafc;
            border: 2px dashed #cbd5e0;
            padding: 24px;
            border-radius: 8px;
            text-align: center;
            margin: 20px 0;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .file-input:hover {
            border-color: #4CAF50;
            background: #f0fff4;
        }

        .file-input i {
            font-size: 2rem;
            color: #4CAF50;
            margin-bottom: 12px;
        }

        .file-input p {
            color: #718096;
            font-size: 0.9rem;
        }

        .file-input input {
            display: none;
        }

        .submit-btn {
            background: linear-gradient(135deg, #004B8F, #0072BC);
            color: white;
            padding: 12px 24px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 500;
            font-size: 1rem;
            transition: all 0.3s ease;
            width: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            margin-top: 15px;
        }

        .submit-btn:hover {
            background: linear-gradient(135deg, #002E5D, #004B8F);
            transform: translateY(-2px);
        }

        .submit-btn i {
            font-size: 1.1rem;
        }

        /* Timeline Styling */
        .timeline-wrapper {
            overflow-x: auto;
            cursor: grab;
            user-select: none;
            -webkit-user-select: none;
            -moz-user-select: none;
            -ms-user-select: none;
            position: relative;
            margin: 0 -24px;
            padding: 0 24px;
        }

        .timeline-wrapper:active {
            cursor: grabbing;
        }

        .timeline-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            white-space: nowrap;
        }

        .timeline-table th,
        .timeline-table td {
            border: 1px solid #e2e8f0;
            padding: 6px;
            text-align: center;
            min-width: 30px;
            font-size: 0.85rem;
        }

        .task-header {
            background: #004B8F;
            color: white;
            width: 300px !important;
            min-width: 300px !important;
            max-width: 300px !important;
            font-weight: 500;
            white-space: normal;
        }

        .month-header {
            background: #2d3748;
            padding: 8px 16px;
            cursor: pointer;
            display: flex;
            justify-content: space-between;
            align-items: center;
            transition: all 0.3s ease;
            color: white;
            font-size: 0.9rem;
        }

        .month-header:hover {
            background: #4a5568;
        }

        .month-section.active .month-header {
            background: linear-gradient(135deg, #004B8F, #0072BC);
            color: white;
        }

        .date-row td {
            background: #f8fafc;
            font-size: 0.9rem;
            color: #4a5568;
        }

        .has-task {
            background: #C5DCF0 !important;
            position: relative;
        }

        .has-task.approved::after {
            content: '✓';
            color: #004B8F;
            font-weight: bold;
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
        }

        /* Hover effect tetep sama untuk semua has-task */
        .has-task:hover {
            background-color: #9DC3E6 !important;
        }

        .task-row td:first-child {
            width: 300px !important;
            min-width: 300px !important;
            max-width: 300px !important;
            text-align: left;
            font-weight: 500;
            white-space: normal;
            word-wrap: break-word;
            padding: 6px 8px;
            font-size: 0.85rem;
        }

        .task-row td {
            height: 40px;
            vertical-align: middle;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .row {
                flex-direction: column;
            }

            .tugas-card {
                flex: 0 0 auto;
            }

            .container {
                margin-top: 4rem;
                padding: 0 10px;
            }

            .card,
            .card-timeline {
                padding: 12px;
            }

            .task-header,
            .task-row td:first-child {
                min-width: 200px !important;
                max-width: 200px !important;
            }

            .timeline-table th:not(.task-header),
            .timeline-table td:not(:first-child) {
                min-width: 40px !important;
                max-width: 40px !important;
            }

            .section-title {
                font-size: 1rem;
            }

            .file-input {
                padding: 16px;
            }

            .month-header {
                padding: 6px 12px;
                font-size: 0.85rem;
            }

            .timeline-table th,
            .timeline-table td {
                padding: 4px;
                font-size: 0.8rem;
            }

            .task-row td:first-child {
                padding: 4px 6px;
                font-size: 0.8rem;
            }
        }

        @media (max-width: 480px) {

            .task-header,
            .task-row td:first-child {
                min-width: 150px !important;
                max-width: 150px !important;
            }

            .timeline-table th:not(.task-header),
            .timeline-table td:not(:first-child) {
                min-width: 35px !important;
                max-width: 35px !important;
            }
        }

        .file-input-wrapper {
            margin: 20px 0;
        }

        .file-label {
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 20px;
            background: #f8fafc;
            border: 2px dashed #cbd5e0;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .file-label:hover {
            border-color: #004B8F;
            background: #E8F1F8;
        }

        .file-label i {
            font-size: 2rem;
            color: #004B8F;
            margin-bottom: 10px;
        }

        .file-label span {
            color: #718096;
            font-size: 0.9rem;
        }

        input[type="file"] {
            opacity: 0;
            width: 0.1px;
            height: 0.1px;
            position: absolute;
        }

        .current-date {
            background: #F7941D !important;
            color: white !important;
            font-weight: bold;
            position: relative;
        }

        .current-date::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            border: 2px solid #FBB040;
        }

        .months-container {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .month-section {
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            overflow: hidden;
            margin-bottom: 8px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }

        .month-header {
            background: #f8fafc;
            padding: 12px 20px;
            cursor: pointer;
            display: flex;
            justify-content: space-between;
            color: black;
            align-items: center;
            transition: background 0.3s ease;
        }

        .month-header:hover {
            background: #e2e8f0;
        }

        .month-header i {
            transition: transform 0.3s ease;
        }

        .month-section.active .month-header {
            background: linear-gradient(135deg, #004B8F, #0072BC);
            color: white;
        }

        .month-section.active .month-header i {
            transform: rotate(180deg);
        }

        .month-content {
            display: none;
            padding: 10px;
            background: white;
        }

        .month-section.active .month-content {
            display: block;
        }

        /* Tambahan CSS untuk handling scroll */


        .dataTables_scrollBody {
            overflow-x: auto !important;
            width: 100% !important;
        }

        /* Fix lebar kolom */
        .task-header,
        .task-row td:first-child {
            min-width: 300px !important;
            max-width: 300px !important;
        }

        /* Lebar kolom tanggal */
        .timeline-table th:not(.task-header),
        .timeline-table td:not(:first-child) {
            min-width: 50px !important;
            /* Sesuaikan dengan kebutuhan */
            max-width: 50px !important;
        }

        /* Smooth scroll */
        .dataTables_scrollBody {
            scroll-behavior: smooth;
        }

        .table-responsive {
            overflow-x: auto;
            cursor: grab;
            -webkit-overflow-scrolling: touch;
            /* Smooth scroll di iOS */
            scroll-behavior: smooth;
            position: relative;
        }

        .table-responsive:active {
            cursor: grabbing;
        }

        /* Sembunyiin scrollbar tapi tetep bisa scroll */
        .table-responsive::-webkit-scrollbar {
            display: none;
        }

        .table-responsive {
            -ms-overflow-style: none;
            scrollbar-width: none;
        }

        /* Styling untuk kolom nomor dan tugas */
        .timeline-table th:first-child,
        .timeline-table td:first-child {
            background: #f8fafc;
            font-weight: 500;
            position: sticky;
            left: 0;
            z-index: 2;
            border-right: 2px solid #e2e8f0;
            text-align: center;
            vertical-align: middle;
            width: 50px !important;
            min-width: 50px !important;
        }

        .task-header,
        .task-row td:nth-child(2) {
            text-align: left;
            vertical-align: middle;
            padding: 8px 12px;
            width: 300px !important;
            min-width: 300px !important;
            max-width: 300px !important;
        }

        /* Styling untuk semua cell di tabel */
        .timeline-table th,
        .timeline-table td {
            height: 40px;
            vertical-align: middle;
            border: 1px solid #e2e8f0;
        }

        .task-row td {
            height: 40px;
            vertical-align: middle;
        }

        @media (max-width: 768px) {

            .task-header,
            .task-row td:first-child {
                min-width: 200px !important;
                max-width: 200px !important;
            }

            .timeline-table th:not(.task-header),
            .timeline-table td:not(:first-child) {
                min-width: 40px !important;
                max-width: 40px !important;
            }
        }

        @media (max-width: 480px) {

            .task-header,
            .task-row td:first-child {
                min-width: 150px !important;
                max-width: 150px !important;
            }

            .timeline-table th:not(.task-header),
            .timeline-table td:not(:first-child) {
                min-width: 35px !important;
                max-width: 35px !important;
            }
        }

        /* Update styling untuk mobile */
        @media (max-width: 768px) {

            .timeline-table th:first-child,
            .timeline-table td:first-child {
                width: 40px !important;
                min-width: 40px !important;
                max-width: 40px !important;
                text-align: center;
                position: sticky;
                left: 0;
                background: #f8fafc;
                z-index: 2;
            }

            .task-header,
            .task-row td:nth-child(2) {
                width: calc(100% - 40px) !important;
                min-width: 150px !important;
                max-width: none !important;
                text-align: left;
                padding: 8px;
                white-space: normal;
                word-wrap: break-word;
            }

            .timeline-table th,
            .timeline-table td {
                padding: 8px;
                font-size: 14px;
                vertical-align: middle;
                height: auto;
            }

            /* Pastikan semua row punya height yang sama */
            .task-row {
                height: auto;
                min-height: 40px;
            }

            .task-row td {
                height: 100%;
                display: table-cell;
                vertical-align: middle;
            }
        }

        /* Tambahan untuk fix alignment secara general */
        .timeline-table {
            border-collapse: collapse;
            width: 100%;
        }

        .timeline-table th,
        .timeline-table td {
            border: 1px solid #e2e8f0;
            line-height: 1.4;
        }

        /* Styling untuk kolom tugas di semua ukuran layar */
        .task-header,
        .task-row td:nth-child(2) {
            width: 300px !important;
            min-width: 300px !important;
            max-width: 300px !important;
            text-align: left;
            padding: 8px 12px;
            white-space: normal !important;
            /* Force text wrapping */
            word-wrap: break-word;
            overflow-wrap: break-word;
            hyphens: auto;
        }

        /* Styling untuk cell tanggal */
        .timeline-table th:not(.task-header):not(:first-child),
        .timeline-table td:not(:first-child):not(:nth-child(2)) {
            width: 50px !important;
            min-width: 50px !important;
            max-width: 50px !important;
            text-align: center;
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {

            .task-header,
            .task-row td:nth-child(2) {
                width: calc(100% - 40px) !important;
                min-width: 150px !important;
                max-width: none !important;
            }
        }

        h2 {
            margin-top: 10px;
            text-align: center;
            color: #484b6a;
            font-size: 24px;
            font-weight: 600;
        }

        /* Style buat custom loader */
        /* Style buat custom loader */
        .custom-loader {
            width: 50px;
            height: 50px;
            border: 5px solid #f3f3f3;
            border-top: 5px solid #004B8F;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin: 20px auto;
        }

        @keyframes spin {
            0% {
                transform: rotate(0deg);
            }

            100% {
                transform: rotate(360deg);
            }
        }

        /* Optional: bikin background modal transparan */
        .transparent-bg {
            background: rgba(255, 255, 255, 0.9) !important;
        }

        .loading-text {
            margin-top: 15px;
            color: #666;
        }

        /* Tambah function showTaskDetail */


        /* Update CSS untuk cursor pointer di has-task */
        .has-task {
            cursor: pointer;
            transition: background-color 0.2s;
        }

        .has-task:hover {
            background-color: #9DC3E6 !important;
        }

        .gambar {
            max-height: 250px;
        }

        /* Update style untuk form upload */
        .card.upload-form {
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 12px rgba(0, 75, 143, 0.1);
            padding: 20px;

        }

        .atas {
            margin-top: 8rem;
        }

        textarea {
            width: 100%;
            padding: 12px;
            border: 2px solid #e2e8f0;
            border-radius: 8px;
            resize: vertical;
            min-height: 100px;
            font-size: 0.9rem;
            transition: border-color 0.3s ease;
        }

        textarea:focus {
            outline: none;
            border-color: #004B8F;
        }

        .file-label {
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 20px;
            background: #f8fafc;
            border: 2px dashed #cbd5e0;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .file-label:hover {
            border-color: #004B8F;
            background: #E8F1F8;
        }

        .file-label i {
            font-size: 2rem;
            color: #004B8F;
            margin-bottom: 10px;
        }

        .submit-btn {
            background: linear-gradient(135deg, #004B8F, #0072BC);
            color: white;
            padding: 12px 24px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 500;
            font-size: 1rem;
            transition: all 0.3s ease;
            width: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            margin-top: 15px;
        }

        .submit-btn:hover {
            background: linear-gradient(135deg, #002E5D, #004B8F);
            transform: translateY(-2px);
        }

        .submit-btn i {
            font-size: 1.1rem;
        }

        /* Tambah CSS untuk sort icon */
        .sorting:before,
        .sorting:after,
        .sorting_asc:before,
        .sorting_asc:after,
        .sorting_desc:before,
        .sorting_desc:after {
            position: absolute;
            bottom: 0.9em;
            display: block;
            opacity: 0.3;
        }

        .sorting_asc:before,
        .sorting_desc:after {
            opacity: 1;
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
            transition: opacity 0.5s;
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

        .form-select {
            width: 100%;
            padding: 12px;
            border: 2px solid #e2e8f0;
            border-radius: 8px;
            font-size: 0.9rem;
            margin-bottom: 15px;
            transition: border-color 0.3s ease;
            background-color: white;
        }

        .form-select:focus {
            outline: none;
            border-color: #004B8F;
        }
    </style>
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.css">
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/responsive/2.2.9/css/responsive.dataTables.min.css">
    <script type="text/javascript" src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script type="text/javascript" src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script type="text/javascript" src="https://cdn.datatables.net/responsive/2.2.9/js/dataTables.responsive.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body>
    <div class="loader-container">
        <div class="loader"></div>
    </div>

    <div class="content">
        <div class="container">
            <!-- Form Upload -->
            <div class="card upload-form atas">
                <h2 class="section-title">Upload Tugas Baru</h2>
                <form method="POST" enctype="multipart/form-data" onsubmit="return submitForm()">
                    <!-- Tambah dropdown tugas -->
                    <select name="kategori" class="form-select" required>
                        <option value="">Pilih Tugas</option>
                        <?php
                        // Query untuk ambil tugas sesuai user
                        $sql_tugas = "SELECT nama_tugas FROM tugas WHERE user = ?";
                        $stmt_tugas = $conn->prepare($sql_tugas);
                        $stmt_tugas->bind_param("s", $_SESSION['nama']);
                        $stmt_tugas->execute();
                        $result_tugas = $stmt_tugas->get_result();

                        while ($row_tugas = $result_tugas->fetch_assoc()) {
                            echo "<option value='" . $row_tugas['nama_tugas'] . "'>" . $row_tugas['nama_tugas'] . "</option>";
                        }
                        ?>
                    </select>

                    <textarea name="laporan" placeholder="Apa tugas kamu hari ini?" required></textarea>

                    <!-- Update bagian file input -->
                    <div class="file-input-wrapper">
                        <label for="foto" class="file-label">
                            <i class="fas fa-cloud-upload-alt"></i>
                            <span id="file-name">Pilih file atau drag & drop disini</span>
                        </label>
                        <input type="file" name="foto" id="foto" required>
                    </div>

                    <button type="submit" class="submit-btn">
                        <i class="fas fa-paper-plane"></i> Upload Tugas
                    </button>
                </form>
            </div>

            <!-- Timeline -->
            <div class="card upload-form">
                <h2 class="section-title">Timeline Internship Program</h2>
                <div class="timeline-wrapper">
                    <div class="months-container">
                        <?php foreach ($months as $month): ?>
                            <div class="month-section <?php echo ($month['num'] == $current_month && $month['year'] == $current_year) ? 'active' : ''; ?>">
                                <div class="month-header" onclick="toggleMonth(<?php echo $month['num']; ?>, <?php echo $month['year']; ?>)">
                                    <span><?php echo strtoupper($month['name']) . ' ' . $month['year']; ?></span>
                                    <i class="fas fa-chevron-down"></i>
                                </div>

                                <div class="month-content" id="month-<?php echo $month['num']; ?>-<?php echo $month['year']; ?>">
                                    <div class="table-responsive" style="overflow-x: auto; width: 100%;">
                                        <table class="timeline-table" style="min-width: 1500px;">
                                            <thead>
                                                <tr>
                                                    <th style="width: 50px; min-width: 50px;">NO</th>
                                                    <th class="task-header">TUGAS</th>
                                                    <th class="task-header" style="width: 150px; min-width: 150px;">KATEGORI</th>
                                                    <?php
                                                    $days_in_month = getDaysInMonth($month['num'], $month['year']);
                                                    for ($i = 1; $i <= $days_in_month; $i++) {
                                                        $class = ($current_day == $i && $month['num'] == $current_month && $month['year'] == $current_year) ? 'current-date' : '';
                                                        echo "<th class='$class'>$i</th>";
                                                    }
                                                    ?>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php
                                                // Update query untuk ambil status juga
                                                $sql = "SELECT *, DATE_FORMAT(waktu, '%d-%m-%Y') as formatted_date, 
                                                        UNIX_TIMESTAMP(waktu) as timestamp_waktu 
                                                        FROM laporan 
                                                        WHERE nama = ? 
                                                        AND MONTH(waktu) = ? 
                                                        AND YEAR(waktu) = ? 
                                                        ORDER BY waktu DESC";
                                                $stmt = $conn->prepare($sql);
                                                $stmt->bind_param("sii", $_SESSION['nama'], $month['num'], $month['year']);
                                                $stmt->execute();
                                                $result = $stmt->get_result();

                                                $no = 1;
                                                while ($row = $result->fetch_assoc()) {
                                                    echo "<tr class='task-row'>";
                                                    echo "<td style='text-align: center;'>" . $no++ . "</td>";
                                                    echo "<td>" . $row['laporan'] . "</td>";
                                                    echo "<td style='text-align: center;'>" . ($row['kategori'] ? $row['kategori'] : 'Periode Juli - November') . "</td>";

                                                    for ($i = 1; $i <= $days_in_month; $i++) {
                                                        $task_date = date('j', strtotime($row['waktu']));
                                                        if ($task_date == $i) {
                                                            // Tambah class approved kalo status = 1
                                                            $approvedClass = ($row['status'] == 1) ? 'approved' : '';

                                                            $tooltipData = htmlspecialchars(json_encode([
                                                                'laporan' => $row['laporan'],
                                                                'tanggal' => $row['formatted_date'],
                                                                'kategori' => $row['kategori'] ? $row['kategori'] : 'Periode Juli - November',
                                                                'status' => $row['status'],
                                                                'img' => base64_encode($row['img_dir'])
                                                            ]), ENT_QUOTES);

                                                            echo "<td class='has-task {$approvedClass}' onclick='showTaskDetail(this)' data-task='{$tooltipData}'></td>";
                                                        } else {
                                                            echo "<td></td>";
                                                        }
                                                    }
                                                    echo "</tr>";
                                                }
                                                ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php
    // Proses Upload
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $nama = $_SESSION['nama'];
        $laporan = $_POST['laporan'];
        $kategori = $_POST['kategori']; // Ambil nilai kategori dari dropdown
        $waktu = time();

        // Handle foto upload
        $foto_blob = null;
        $uploadOk = 1;

        if (isset($_FILES['foto']) && $_FILES['foto']['error'] == 0) {
            $imageFileType = strtolower(pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION));

            // Proses resize & convert image ke blob
            $check = getimagesize($_FILES['foto']['tmp_name']);
            if ($check !== false) {
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
                }

                if ($uploadOk) {
                    // Resize image
                    $width = imagesx($img);
                    $height = imagesy($img);
                    $newWidth = 500;
                    $newHeight = floor($height * ($newWidth / $width));
                    $tmpImg = imagecreatetruecolor($newWidth, $newHeight);
                    imagecopyresampled($tmpImg, $img, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);

                    ob_start();
                    imagejpeg($tmpImg);
                    $foto_blob = ob_get_clean();

                    imagedestroy($img);
                    imagedestroy($tmpImg);
                }
            }
        }

        // Insert ke database
        if ($uploadOk) {
            $sql = "INSERT INTO laporan (nama, laporan, kategori, img_dir, waktu) VALUES (?, ?, ?, ?, FROM_UNIXTIME(?))";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssssi", $nama, $laporan, $kategori, $foto_blob, $waktu);

            if ($stmt->execute()) {
                echo "<script>
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil!',
                        text: 'Tugas berhasil diupload!',
                        showConfirmButton: false,
                        timer: 8000
                    }).then(function() {
                        window.location.href = 'timeline.php';
                    });
                </script>";
            } else {
                echo "<script>
                    Swal.fire({
                        icon: 'error',
                        title: 'Oops...',
                        text: 'Gagal upload tugas!'
                    });
                </script>";
            }
        }
    }
    ?>

    <!-- Tambahkan JavaScript -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const currentMonth = <?php echo $current_month; ?>;
            const currentYear = <?php echo $current_year; ?>;

            // Buka bulan sekarang dulu
            toggleMonth(currentMonth, currentYear);

            // Delay scroll ke tanggal hari ini
            setTimeout(() => {
                const currentDate = document.querySelector('.current-date');
                if (currentDate) {
                    currentDate.scrollIntoView({
                        behavior: 'smooth',
                        block: 'nearest',
                        inline: 'center'
                    });
                }
            }, 300);
        });

        function toggleMonth(monthNum, year) {
            const monthContent = document.querySelector(`#month-${monthNum}-${year}`);
            if (!monthContent) return;

            const section = monthContent.closest('.month-section');
            if (!section) return;

            const allSections = document.querySelectorAll('.month-section');

            if (section.classList.contains('active')) {
                section.classList.remove('active');
            } else {
                allSections.forEach(s => s.classList.remove('active'));
                section.classList.add('active');

                // Cek apakah data sudah di-load
                if (!monthContent.dataset.loaded) {
                    loadTasks(monthNum, year, monthContent);
                }
            }
        }

        function loadTasks(monthNum, year, monthContent) {
            $.ajax({
                url: 'load_timeline.php',
                method: 'POST',
                data: {
                    month: monthNum,
                    year: year
                },
                success: function(response) {
                    monthContent.innerHTML = response;
                    monthContent.dataset.loaded = true; // Tandai bahwa data sudah di-load
                },
                error: function() {
                    alert('Gagal memuat data. Coba lagi nanti.');
                }
            });
        }

        function showTaskDetail(element) {
            const taskData = JSON.parse(element.dataset.task);

            // Format status text
            const statusText = taskData.status == 1 ?
                '<span style="color: #4CAF50;">Sudah diterima ✓</span>' :
                '<span style="color: #FFA500;">Belum diterima</span>';

            Swal.fire({
                title: 'Detail Tugas',
                html: `
                    <div style="margin-bottom: 15px;">
                        <strong>Tanggal:</strong> ${taskData.tanggal}
                    </div>
                    <div style="margin-bottom: 15px;">
                        <strong>Kategori:</strong> ${taskData.kategori}
                    </div>
                    <div style="margin-bottom: 15px;">
                        <strong>Status:</strong> ${statusText}
                    </div>
                    <div style="margin-bottom: 15px;">
                        <strong>Deskripsi:</strong><br>
                        ${taskData.laporan}
                    </div>
                    <div>
                        <img class="gambar" src="data:image/jpeg;base64,${taskData.img}" 
                             style="max-width: 100%; height: auto; border-radius: 8px;">
                    </div>
                `,
                width: '450px',
                height: '250px',
                showCloseButton: true,
                showConfirmButton: false,
                customClass: {
                    container: 'task-detail-modal'
                }
            });
        }

        function loadTasks(monthNum) {
            // Ajax request untuk load tasks kalo perlu
            // ... kode ajax ...
        }

        // Script untuk update nama file yang dipilih
        document.getElementById('foto').addEventListener('change', function(e) {
            const fileName = e.target.files[0]?.name || 'Pilih file atau drag & drop disini';
            document.getElementById('file-name').textContent = fileName;
        });

        // Script untuk drag & drop
        const dropZone = document.querySelector('.file-label');

        ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
            dropZone.addEventListener(eventName, preventDefaults, false);
        });

        function preventDefaults(e) {
            e.preventDefault();
            e.stopPropagation();
        }

        ['dragenter', 'dragover'].forEach(eventName => {
            dropZone.addEventListener(eventName, highlight, false);
        });

        ['dragleave', 'drop'].forEach(eventName => {
            dropZone.addEventListener(eventName, unhighlight, false);
        });

        function highlight(e) {
            dropZone.classList.add('highlight');
        }

        function unhighlight(e) {
            dropZone.classList.remove('highlight');
        }

        dropZone.addEventListener('drop', handleDrop, false);

        function handleDrop(e) {
            const dt = e.dataTransfer;
            const files = dt.files;
            document.getElementById('foto').files = files;
            document.getElementById('file-name').textContent = files[0].name;
        }

        // Shared variables untuk scroll
        let isDown = false;
        let startX;
        let scrollLeft;

        // Timeline wrapper scroll
        const timelineSlider = document.querySelector('.timeline-wrapper');

        timelineSlider.addEventListener('mousedown', (e) => {
            isDown = true;
            timelineSlider.style.cursor = 'grabbing';
            startX = e.pageX - timelineSlider.offsetLeft;
            scrollLeft = timelineSlider.scrollLeft;
        });

        timelineSlider.addEventListener('mouseleave', () => {
            isDown = false;
            timelineSlider.style.cursor = 'grab';
        });

        timelineSlider.addEventListener('mouseup', () => {
            isDown = false;
            timelineSlider.style.cursor = 'grab';
        });

        timelineSlider.addEventListener('mousemove', (e) => {
            if (!isDown) return;
            e.preventDefault();
            const x = e.pageX - timelineSlider.offsetLeft;
            const walk = (x - startX);
            timelineSlider.scrollLeft = scrollLeft - walk;
        });

        // Table wrapper scroll
        const tableWrapper = document.querySelector('.table-responsive');

        tableWrapper.addEventListener('mousedown', (e) => {
            isDown = true;
            tableWrapper.style.cursor = 'grabbing';
            startX = e.pageX - tableWrapper.offsetLeft;
            scrollLeft = tableWrapper.scrollLeft;
        });

        tableWrapper.addEventListener('mouseleave', () => {
            isDown = false;
            tableWrapper.style.cursor = 'grab';
        });

        tableWrapper.addEventListener('mouseup', () => {
            isDown = false;
            tableWrapper.style.cursor = 'grab';
        });

        tableWrapper.addEventListener('mousemove', (e) => {
            if (!isDown) return;
            e.preventDefault();
            const x = e.pageX - tableWrapper.offsetLeft;
            const walk = (x - startX);
            tableWrapper.scrollLeft = scrollLeft - walk;
        });

        // Touch events
        tableWrapper.addEventListener('touchstart', (e) => {
            startX = e.touches[0].pageX - tableWrapper.offsetLeft;
            scrollLeft = tableWrapper.scrollLeft;
        }, {
            passive: true
        });

        tableWrapper.addEventListener('touchmove', (e) => {
            if (!startX) return;
            const x = e.touches[0].pageX - tableWrapper.offsetLeft;
            const walk = (x - startX) * 2;
            tableWrapper.scrollLeft = scrollLeft - walk;
        }, {
            passive: true
        });

        tableWrapper.addEventListener('touchend', () => {
            startX = null;
        });

        // Opsi 1: Loading dengan progress bar circular
        function submitForm() {
            Swal.fire({
                title: 'Uploading...',
                html: '<div class="loading-text">Sabar ya, file lagi diupload...</div>',
                timerProgressBar: true,
                allowOutsideClick: false,
                showConfirmButton: false,
                didOpen: () => {
                    Swal.showLoading()
                }
            });
            return true;
        }

        // Opsi 2: Custom loading animation pake CSS
        function submitForm() {
            Swal.fire({
                html: `
                    <div class="custom-loader"></div>
                    <div class="loading-text">Uploading file...</div>
                `,
                showConfirmButton: false,
                allowOutsideClick: false,
                customClass: {
                    popup: 'transparent-bg'
                }
            });
            return true;
        }

        // Tambah script untuk handle loading
        window.addEventListener('load', function() {
            const loader = document.querySelector('.loader-container');
            const content = document.querySelector('.content');

            setTimeout(() => {
                loader.style.opacity = '0';
                content.style.display = 'block';

                setTimeout(() => {
                    loader.style.display = 'none';
                }, 500);
            }, 1000);
        });
    </script>
    <script>
        $(document).ready(function() {
            $('.timeline-table').DataTable({
                scrollX: true,
                scrollCollapse: true,
                paging: false,
                searching: false,
                ordering: true, // Enable ordering
                order: [
                    [0, 'desc']
                ], // Default sort by first column (NO) descending
                info: false,
                fixedColumns: {
                    left: 1
                },
                autoWidth: false,
                language: {
                    emptyTable: "Belum ada tugas di bulan ini"
                },
                columnDefs: [{
                        targets: [0], // Target kolom NO
                        orderable: true // Bisa di-sort
                    },
                    {
                        targets: '_all', // Semua kolom lainnya
                        orderable: false // Ga bisa di-sort
                    }
                ]
            });

            // Fix width kolom
            $('.timeline-table').css('width', '100%');
            $('.dataTables_scrollHeadInner').css('width', '100%');

            // Tambah style untuk sort icon
            $('th').css('cursor', 'pointer');
        });
    </script>
</body>

</html>

</html>