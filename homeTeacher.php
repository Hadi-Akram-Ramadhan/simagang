<?php
session_start();
require_once('koneksi.php');
require('auth.php');

// Cek role dan sekolah dari session
if (!isset($_SESSION['role']) || $_SESSION['role'] !== '3' || !isset($_SESSION['sekolah'])) {
    header("Location: index.php");
    exit();
}

$sekolahGuru = $_SESSION['sekolah'];

// Ambil welcome text dan instruction text dari database
$sql = "SELECT first, second FROM settings WHERE id = 2";
$result = $conn->query($sql);
if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $welcomeText = $row['first'];
    $instructionText = $row['second'];
} else {
    $welcomeText = "Kementerian Perdagangan";
    $instructionText = "Direktorat Bahan Pokok dan Barang Penting";
}

// Handle date filter
$selectedDates = isset($_GET['dates']) && !empty($_GET['dates']) ? explode(' - ', $_GET['dates']) : null;

// Modify the student query to filter by selected dates or show all
if ($selectedDates) {
    $studentQuery = "SELECT nama, magang_masuk, magang_keluar FROM akun WHERE asal_sekolah = ? AND role = '1' AND magang_masuk = ? AND magang_keluar = ?";
    $stmt = mysqli_prepare($conn, $studentQuery);
    mysqli_stmt_bind_param($stmt, "sss", $sekolahGuru, $selectedDates[0], $selectedDates[1]);
} else {
    $studentQuery = "SELECT nama, magang_masuk, magang_keluar FROM akun WHERE asal_sekolah = ? AND role = '1'";
    $stmt = mysqli_prepare($conn, $studentQuery);
    mysqli_stmt_bind_param($stmt, "s", $sekolahGuru);
}
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$studentList = "
<div class='table-container'>
    <table class='student-table'>
        <thead>
            <tr>
                <th>Nama</th>
                <th>Masuk</th>
                <th>Keluar</th>
                <th>Detail</th>
                <th>Laporan</th>
            </tr>
        </thead>
        <tbody>";

while ($row = mysqli_fetch_assoc($result)) {
    $studentList .= "
        <tr data-murid-nama='{$row['nama']}'>
            <td>{$row['nama']}</td>
            <td>{$row['magang_masuk']}</td>
            <td>{$row['magang_keluar']}</td>
            <td><button class='detail-btn' data-name='{$row['nama']}'>Detail</button></td>
            <td><a href='laporan_admin.php?nama={$row['nama']}' class='report-btn' target='_blank'>Laporan</a></td>
        </tr>";
}

$studentList .= "</tbody></table></div>";

// Fetch unique internship start and end dates for dropdown
$dateQuery = "SELECT DISTINCT magang_masuk, magang_keluar FROM akun WHERE asal_sekolah = ? AND role = '1' ORDER BY magang_masuk";
$dateStmt = mysqli_prepare($conn, $dateQuery);
mysqli_stmt_bind_param($dateStmt, "s", $sekolahGuru);
mysqli_stmt_execute($dateStmt);
$dateResult = mysqli_stmt_get_result($dateStmt);
$datesDropdown = "<select id='dateFilter' class='bg-gray-100 border border-gray-300 text-gray-700'><option value=''>Pilih Periode</option>";
while ($dateRow = mysqli_fetch_assoc($dateResult)) {
    $datesDropdown .= "<option value='{$dateRow['magang_masuk']} - {$dateRow['magang_keluar']}'>{$dateRow['magang_masuk']} - {$dateRow['magang_keluar']}</option>";
}
$datesDropdown .= "</select>";

// Handle attendance and task data fetching
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['name'])) {
    $name = $_POST['name'];
    $month = isset($_POST['month']) ? $_POST['month'] : null;

    $attendanceQuery = "
        SELECT 
            DATE(a.waktu) as tanggal,
            COUNT(CASE WHEN a.waktu IS NOT NULL THEN 1 END) as status,
            COALESCE(MAX(l.status_g), 0) as task_status,
            COUNT(l.id) as total_tasks
        FROM images a
        LEFT JOIN laporan l ON DATE(a.waktu) = DATE(l.waktu) AND l.nama = a.user
        WHERE a.user = ?
    ";

    $params = array($name);

    if ($month) {
        $attendanceQuery .= " AND DATE_FORMAT(a.waktu, '%Y-%m') = ?";
        $params[] = $month;
    }

    $attendanceQuery .= "
        GROUP BY DATE(a.waktu)
        HAVING COUNT(*) >= 2
        ORDER BY tanggal
    ";

    $attendanceStmt = $conn->prepare($attendanceQuery);
    $attendanceStmt->bind_param(str_repeat('s', count($params)), ...$params);
    $attendanceStmt->execute();
    $attendanceResult = $attendanceStmt->get_result();

    echo '<div class="table-wrapper">';
    echo '<table>';
    echo '<thead>';
    echo '<tr>';
    echo '<th>Tanggal</th>';
    echo '<th>Status</th>';
    echo '<th>Detail</th>';
    echo '<th>Tugas</th>';
    echo '</tr>';
    echo '</thead>';
    echo '<tbody>';

    while ($attendanceRow = $attendanceResult->fetch_assoc()) {
        $tanggal = $attendanceRow['tanggal'];
        $taskStatus = $attendanceRow['task_status'];
        $totalTasks = $attendanceRow['total_tasks'];

        // Tentukan status dan class untuk indikator
        $statusIndicator = '';
        if ($totalTasks > 0) {
            if ($taskStatus == 1) {
                $statusIndicator = '<span class="badge bg-success">Tugas Diterima</span>';
            } else {
                $statusIndicator = '<span class="badge bg-warning">Tugas Belum Diterima</span>';
            }
        }

        $taskQuery = "
            SELECT 
                laporan, img_dir, status_g, comment_g, waktu
            FROM laporan 
            WHERE nama = ? AND DATE(waktu) = ?
            ORDER BY waktu ASC
        ";

        $taskStmt = $conn->prepare($taskQuery);
        $taskStmt->bind_param("ss", $name, $tanggal);
        $taskStmt->execute();
        $taskResult = $taskStmt->get_result();

        $taskData = [];
        while ($taskRow = $taskResult->fetch_assoc()) {
            $taskData[] = [
                'laporan' => $taskRow['laporan'],
                'img_dir' => base64_encode($taskRow['img_dir']),
                'status' => $taskRow['status_g'],
                'comment' => $taskRow['comment_g'],
                'waktu' => $taskRow['waktu']
            ];
        }

        if (!empty($taskData)) {
            $taskLink = '<a href="#" class="task-link" data-tasks=\'' . json_encode($taskData) . '\' onclick="showTaskDetails(this)">Lihat Tugas (' . count($taskData) . ')</a>';
        } else {
            $taskLink = 'Tidak Ada Tugas';
        }

        echo '<tr>';
        echo '<td>' . htmlspecialchars($tanggal) . '</td>';
        echo '<td>Hadir ' . $statusIndicator . '</td>';
        echo '<td><a href="#" class="view-details" onclick="showAttendanceDetails(\'' . htmlspecialchars($name) . '\', \'' . htmlspecialchars($tanggal) . '\')">Lihat</a></td>';
        echo '<td>' . $taskLink . '</td>';
        echo '</tr>';
    }

    echo '</tbody>';
    echo '</table>';
    echo '</div>';
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="fontawesome/css/fontawesome.min.css">
    <link rel="stylesheet" href="bootstrap/css/bootstrap.min.css">
    <link rel="shortcut icon" href="image\kementrian.png">
    <link rel="stylesheet" href="css/kita.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
    <title>Data Absen - Guru</title>
    <style>
        body,
        .nama,
        .navbar,
        .container,
        h2,
        table,
        th,
        td,
        .detail-btn,
        .report-btn {
            font-family: 'Poppins', sans-serif;
        }

        .navbar {
            position: fixed;
            top: 0;
            width: 100%;
            z-index: 1001;
            background-color: #ffffff;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            padding: 0.5rem 1rem;
            transition: all 0.3s ease;
        }

        .navbar-brand {
            display: flex;
            align-items: center;
        }

        .foto {
            height: 50px;
            width: auto;
            filter: drop-shadow(1px 1px 20px rgb(0, 255, 238));
            transition: transform 0.3s ease;
        }

        .foto:hover {
            transform: scale(1.05);
        }

        .nama {
            color: #333333;
            font-size: 1.2rem;
            margin: 0;
            padding: 0 15px;
        }

        .logout {
            padding-left: 10px;
        }

        .separator {
            border-left: 1px solid #484b6a;
            height: 30px;
            margin: auto 20px;
        }

        @media (max-width: 768px) {
            .navbar {
                padding: 0.3rem;
            }

            .foto {
                height: 40px;
            }

            .nama {
                font-size: 1rem;
            }

            .separator {
                margin: auto 10px;
            }
        }

        .container {
            max-width: 1200px;
            margin: 100px auto 20px;
            padding: 20px;
            background-color: #ffffff;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        h2 {
            margin-top: 10px;
            text-align: center;
            color: #484b6a;
            font-size: 24px;
            font-weight: 600;
        }

        #dateFilter {
            width: 100%;
            padding: 10px;
            margin-bottom: 20px;
            border: 1px solid #d1d3e2;
            border-radius: 4px;
            font-size: 14px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            border: 1px solid #e3e6f0;
        }

        th,
        td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #e3e6f0;
        }

        th {
            background-color: #003366;
            font-weight: bold;
            color: #ffffff;
        }

        tr {
            transition: background-color 0.3s;
        }

        tr:hover {
            background-color: #f0f8ff;
        }

        .detail-btn,
        .report-btn {
            padding: 6px 12px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
            transition: background-color 0.3s;
        }

        .detail-btn {
            background-color: #003366;
            color: white;
        }

        .report-btn {
            background-color: #00509e;
            color: white;
            text-decoration: none;
            display: inline-block;
        }

        .detail-btn:hover,
        .report-btn:hover {
            opacity: 0.8;
            color: #ffffff;
        }

        .modal {
            display: none;
            position: fixed;
            z-index: 1050;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0, 0, 0, 0.4);
        }

        .modal-content {
            background-color: #fefefe;
            border-radius: 20px;
            border: none;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
            width: 90%;
            min-width: 320px;
            max-width: 800px;
            margin: 3% auto;
            padding: 25px;
            max-height: 90vh;
            overflow-y: auto;
        }

        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
        }

        .close:hover,
        .close:focus {
            color: #000;
            text-decoration: none;
            cursor: pointer;
        }

        #attendanceMonth {
            width: 100%;
            padding: 10px;
            margin-bottom: 10px;
            border: 1px solid #d1d3e2;
            border-radius: 4px;
        }

        #monthlyComment {
            width: 100%;
            min-height: 100px;
            padding: 12px;
            margin: 15px 0;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 14px;
            font-family: 'Poppins', sans-serif;
            resize: vertical;
        }

        #monthlyComment:focus {
            outline: none;
            border-color: #003366;
            box-shadow: 0 0 5px rgba(0, 51, 102, 0.2);
        }

        #acceptAllTasks {
            width: 100%;
            padding: 10px;
            background-color: #00509e;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            transition: background-color 0.3s;
        }

        #acceptAllTasks:hover {
            background-color: #003366;
        }

        #attendanceTable {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            border: 1px solid #e3e6f0;
        }

        #attendanceTable th,
        #attendanceTable td {
            padding: 10px;
            text-align: left;
            border-bottom: 1px solid #e3e6f0;
        }

        #attendanceTable th {
            background-color: #00509e;
            font-weight: bold;
            color: #ffffff;
        }

        #attendanceTable tr:hover {
            background-color: #f0f8ff;
        }

        @media screen and (max-width: 768px) {
            .container {
                padding: 10px;
                margin-top: 80px;
            }

            .header {
                flex-direction: column;
                align-items: flex-start;
                height: auto;
                padding: 10px;
            }

            .navbar-brand {
                margin-bottom: 10px;
            }

            .user-info {
                margin-top: 10px;
            }

            .nama {
                font-size: 18px;
            }

            .separator {
                display: none;
            }

            .logout {
                margin-top: 5px;
            }

            table {
                font-size: 14px;
            }

            th,
            td {
                padding: 8px 5px;
            }

            .detail-btn,
            .report-btn {
                padding: 4px 8px;
                font-size: 12px;
            }

            .modal-content {
                width: 95%;
                margin: 5% auto;
                padding: 15px;
            }

            #attendanceDetailModal .modal-content {
                flex-direction: column;
            }

            #attendanceDetailModal img {
                width: 100%;
                height: auto;
                margin-bottom: 10px;
            }

            #mapIn,
            #mapOut {
                height: 200px;
                margin-bottom: 20px;
            }
        }

        @media screen and (max-width: 480px) {
            .kemendag-text h2 {
                font-size: 14px;
            }

            .kemendag-text p {
                font-size: 10px;
            }

            .foto {
                width: 30px;
            }

            .nama {
                font-size: 16px;
            }

            table {
                font-size: 12px;
            }

            .detail-btn,
            .report-btn {
                padding: 3px 6px;
                font-size: 11px;
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

        /* Styling untuk tabel murid di halaman utama */
        .student-list table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            border: 1px solid #e3e6f0;
        }

        .student-list th,
        .student-list td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #e3e6f0;
        }

        .student-list th {
            background-color: #003366;
            font-weight: bold;
            color: #ffffff;
        }

        .student-list tr:hover {
            background-color: #f0f8ff;
        }

        /* Styling untuk tabel di dalam modal */
        #attendanceTable {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            border: 1px solid #e3e6f0;
        }

        #attendanceTable th,
        #attendanceTable td {
            padding: 10px;
            text-align: left;
            border-bottom: 1px solid #e3e6f0;
        }

        #attendanceTable th {
            background-color: #00509e;
            font-weight: bold;
            color: #ffffff;
        }

        #attendanceTable tr:hover {
            background-color: #f0f8ff;
        }

        /* Styling untuk modal absen */
        #attendanceDetailModal .modal-content {
            font-size: 14px;
            /* Atur ukuran font di sini */
            padding: 20px;
            border-radius: 8px;
            background-color: #fefefe;
        }

        #attendanceDetailModal h1 {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 10px;
        }

        /* Modal Task Styling */
        .task-container {
            padding: 12px;
            background: linear-gradient(145deg, #ffffff, #f5f8ff);
            border-radius: 8px;
        }

        .task-item {
            background: #fff;
            margin-bottom: 15px;
            padding: 15px;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0, 51, 102, 0.1);
        }

        .task-item h4 {
            font-size: 16px;
            margin-bottom: 12px;
        }

        .task-item p {
            font-size: 14px;
            margin: 8px 0;
        }

        .task-item img {
            max-width: 100%;
            height: auto;
            border-radius: 6px;
            margin: 8px 0;
        }

        /* Responsive styling */
        @media screen and (max-width: 768px) {
            .container {
                padding: 10px;
                margin-top: 80px;
            }

            .header {
                flex-direction: column;
                align-items: flex-start;
                height: auto;
                padding: 10px;
            }

            .navbar-brand {
                margin-bottom: 10px;
            }

            .user-info {
                margin-top: 10px;
            }

            .nama {
                font-size: 18px;
            }

            .separator {
                display: none;
            }

            .logout {
                margin-top: 5px;
            }

            table {
                font-size: 14px;
            }

            th,
            td {
                padding: 8px 5px;
            }

            .detail-btn,
            .report-btn {
                padding: 4px 8px;
                font-size: 12px;
            }

            .modal-content {
                width: 95%;
                margin: 5% auto;
                padding: 15px;
            }

            #attendanceDetailModal .modal-content {
                flex-direction: column;
            }

            #attendanceDetailModal img {
                width: 100%;
                height: auto;
                margin-bottom: 10px;
            }

            #mapIn,
            #mapOut {
                height: 200px;
                margin-bottom: 20px;
            }
        }

        @media screen and (max-width: 480px) {
            .kemendag-text h2 {
                font-size: 14px;
            }

            .kemendag-text p {
                font-size: 10px;
            }

            .foto {
                width: 30px;
            }

            .nama {
                font-size: 16px;
            }

            table {
                font-size: 12px;
            }

            .detail-btn,
            .report-btn {
                padding: 3px 6px;
                font-size: 11px;
            }

            .task-item {
                padding: 10px;
            }

            .task-item h4 {
                font-size: 14px;
            }

            .task-item p {
                font-size: 12px;
                margin: 6px 0;
            }

            .task-link {
                font-size: 12px;
                padding: 3px 6px;
            }

            #taskModal .modal-content {
                padding: 12px;
            }

            #taskModal h2 {
                font-size: 16px;
                margin-bottom: 15px;
            }

            .text-success,
            .text-warning {
                font-size: 11px;
                padding: 3px 6px;
            }
        }

        /* Update CSS untuk responsif */
        .table-responsive {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
            margin: 0 -15px;
            padding: 0 15px;
        }

        table {
            width: 100%;
            min-width: 500px;
            /* Minimum width untuk scrolling horizontal */
        }

        /* Modal styling update */
        .modal {
            padding: 15px;
        }

        .modal-content {
            width: 95%;
            max-height: 90vh;

            margin: 20px auto;
            padding: 15px;
            border-radius: 8px;
        }

        /* Task item styling update */
        .task-item {
            margin: 10px 0;
            padding: 12px;
        }

        .task-details {
            display: grid;
            gap: 8px;
        }

        .task-image img {
            width: 100%;
            max-width: 300px;
            height: auto;
            object-fit: cover;
        }

        /* Button styling update */
        .detail-btn,
        .report-btn {
            width: 100%;
            margin: 2px 0;
            padding: 6px 10px;
            font-size: 13px;
            white-space: nowrap;
        }

        /* Responsive breakpoints */
        @media screen and (max-width: 768px) {
            .container {
                padding: 10px;
            }

            table th,
            table td {
                padding: 8px 6px;
                font-size: 13px;
            }

            .modal-content {
                padding: 12px;
                margin: 10px auto;
            }

            .task-item {
                padding: 10px;
            }

            .task-item h4 {
                font-size: 15px;
            }

            .task-item p {
                font-size: 13px;
            }
        }

        @media screen and (max-width: 480px) {

            table th,
            table td {
                padding: 6px 4px;
                font-size: 12px;
            }

            .detail-btn,
            .report-btn {
                padding: 4px 8px;
                font-size: 12px;
            }

            .task-item h4 {
                font-size: 14px;
            }

            .task-item p {
                font-size: 12px;
            }

            .modal-content {
                padding: 10px;
            }
        }

        /* Navbar responsive update */
        @media screen and (max-width: 576px) {
            .navbar {
                padding: 5px;
            }

            .navbar-brand {
                flex-direction: column;
                align-items: center;
                text-align: center;
            }

            .nama {
                font-size: 14px;
                margin-top: 5px;
            }

            .foto {
                height: 35px;
            }
        }

        /* Styling untuk container dan tabel utama */
        .table-container {
            width: 100%;
            overflow-x: auto;
            margin: 20px 0;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .student-table {
            width: 100%;
            border-collapse: collapse;
            min-width: 600px;
        }

        .student-table th {
            background: #003366;
            color: white;
            padding: 12px 15px;
            text-align: left;
            font-weight: 500;
        }

        .student-table td {
            padding: 12px 15px;
            border-bottom: 1px solid #eee;
        }

        .student-table tr:hover {
            background-color: #f8f9fa;
        }

        /* Button styling */
        .detail-btn,
        .report-btn {
            padding: 6px 12px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
            text-align: center;
        }

        .detail-btn {
            background: #003366;
            color: white;
        }

        .report-btn {
            background: #00509e;
            color: white;
        }

        .detail-btn:hover,
        .report-btn:hover {
            opacity: 0.9;
        }

        /* Modal styling */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 100099;
            overflow-y: auto;
        }

        .modal-content {
            position: relative;
            background: #fff;
            border-radius: 20px;
            border: none;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
            width: 90%;
            min-width: 320px;
            max-width: 800px;
            margin: 3% auto;
            padding: 25px;
            max-height: 90vh;
            overflow-y: auto;
        }

        /* Attendance Detail Modal */
        #attendanceDetailModal .modal-content {
            max-width: 1200px;
            min-width: 320px;
            width: 90%;
        }

        #attendanceDetailModal h1 {
            font-size: 20px;
            color: #2E384D;
            margin-bottom: 20px;
            font-family: 'Poppins', sans-serif;
            text-align: center;
        }

        #attendanceDetailModal h3 {
            font-size: 16px;
            color: #566A7F;
            margin: 15px 0;
            font-family: 'Poppins', sans-serif;
        }

        #attendanceDetailModal .attendance-container {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-top: 20px;
        }

        #attendanceDetailModal .attendance-section {
            background: #F8F9FA;
            padding: 15px;
            border-radius: 12px;
        }

        #attendanceDetailModal img {
            width: 100%;
            height: 200px;
            object-fit: cover;
            border-radius: 8px;
            margin-bottom: 15px;
        }

        #attendanceDetailModal .map-container {
            height: 350px;
            min-height: 250px;
            border-radius: 8px;
            overflow: hidden;
            border: 2px solid #E7ECF3;
        }

        #mapIn,
        #mapOut {
            height: 100%;
            width: 100%;
        }

        /* Responsive styling */
        @media screen and (max-width: 768px) {
            .modal-content {
                width: 95%;
                margin: 10px auto;
                padding: 15px;
            }

            #attendanceDetailModal .attendance-container {
                grid-template-columns: 1fr;
                gap: 15px;
            }

            #attendanceDetailModal img {
                height: 180px;
            }

            #attendanceDetailModal .map-container {
                height: 250px;
            }
        }

        @media screen and (max-width: 480px) {
            .modal-content {
                padding: 12px;
            }

            #attendanceDetailModal h1 {
                font-size: 18px;
            }

            #attendanceDetailModal h3 {
                font-size: 14px;
            }

            #attendanceDetailModal img {
                height: 150px;
            }

            #attendanceDetailModal .map-container {
                height: 200px;
            }
        }

        /* Styling untuk container tabel di dalam modal */
        .table-wrapper {
            width: 100%;
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
            margin-bottom: 15px;
            border-radius: 8px;
            background: #fff;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        }

        /* Styling untuk tabel di dalam modal */
        .modal table {
            width: 100%;
            border-spacing: 0;
            border-collapse: separate;
            margin-top: 15px;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
        }

        .modal table th {
            background: #003366;
            color: white;
            padding: 12px 15px;
            font-weight: 500;
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .modal table td {
            padding: 12px 15px;
            font-size: 14px;
            color: #566A7F;
            border-bottom: 1px solid #E7ECF3;
            background: #FFFFFF;
        }

        /* Responsive styling untuk mobile */
        @media screen and (max-width: 768px) {
            .modal-content {
                width: 95%;
                margin: 10px auto;
                padding: 15px;
            }

            .modal table {
                width: 100%;
                min-width: unset;
                /* Hapus min-width di mobile */
            }

            .modal table th {
                padding: 8px;
                font-size: 12px;
            }

            .modal table td {
                padding: 8px;
                font-size: 12px;
            }

            /* Bikin tabel scroll horizontal di mobile */
            .table-wrapper {
                overflow-x: auto;
                -webkit-overflow-scrolling: touch;
                margin: 0 -15px;
                padding: 0 15px;
            }
        }

        @media screen and (max-width: 480px) {

            .modal table th,
            .modal table td {
                padding: 6px;
                font-size: 11px;
            }

            .view-details,
            .task-link {
                padding: 4px 8px;
                font-size: 11px;
            }
        }

        /* Styling untuk link dan button di dalam tabel */
        .modal table .view-details,
        .modal table .task-link {
            display: inline-block;
            padding: 6px 12px;
            background: #003366;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            font-size: 13px;
            transition: all 0.3s ease;
        }

        .modal table .view-details:hover,
        .modal table .task-link:hover {
            background: #004d99;
        }

        .swal2-container {
            z-index: 9999999999999 !important;
        }

        /* Responsive styling */
        @media screen and (max-width: 768px) {
            .table-wrapper {
                margin: 0 -15px;
                width: calc(100% + 30px);
                border-radius: 0;
            }

            .modal table th,
            .modal table td {
                padding: 10px 12px;
                font-size: 13px;
            }

            .modal table .view-details,
            .modal table .task-link {
                padding: 4px 8px;
                font-size: 12px;
            }
        }

        /* Styling untuk modal dan kontennya */
        .modal-content {
            position: relative;
            background: #fff;
            width: 90%;
            max-width: 800px;
            margin: 20px auto;
            padding: 20px;
            border-radius: 12px;
            box-shadow: 0 2px 15px rgba(0, 0, 0, 0.1);
        }

        /* Update styling tabel dalam modal */
        .modal table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
            /* Bikin kolom fixed width */
        }

        .modal table th,
        .modal table td {
            padding: 10px;
            text-align: left;
            border-bottom: 1px solid #eee;
            word-wrap: break-word;
            /* Bikin text wrap */
            white-space: normal;
            /* Override nowrap */
        }

        /* Atur width tiap kolom */
        .modal table th:nth-child(1),
        .modal table td:nth-child(1) {
            /* Tanggal */
            width: 25%;
        }

        .modal table th:nth-child(2),
        .modal table td:nth-child(2) {
            /* Status */
            width: 15%;
        }

        .modal table th:nth-child(3),
        .modal table td:nth-child(3) {
            /* Detail */
            width: 25%;
        }

        .modal table th:nth-child(4),
        .modal table td:nth-child(4) {
            /* Tugas */
            width: 35%;
        }

        /* Styling untuk button dan link */
        .view-details,
        .task-link {
            display: inline-block;
            padding: 6px 12px;
            background: #003366;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            font-size: 13px;
            text-align: center;
            transition: all 0.3s ease;
        }

        .view-details:hover,
        .task-link:hover {
            background: #004d99;
        }

        /* Responsive styling */
        @media screen and (max-width: 768px) {
            .modal-content {
                width: 95%;
                padding: 15px;
                margin: 10px auto;
            }

            .modal table th,
            .modal table td {
                padding: 8px;
                font-size: 13px;
            }

            .view-details,
            .task-link {
                padding: 4px 8px;
                font-size: 12px;
            }
        }

        @media screen and (max-width: 480px) {

            .modal table th,
            .modal table td {
                padding: 6px;
                font-size: 12px;
            }
        }

        .badge {
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 500;
            margin-left: 8px;
        }

        .bg-success {
            background-color: #28a745 !important;
            color: white;
        }

        .bg-warning {
            background-color: #ffc107 !important;
            color: #000;
        }

        /* Responsive styling untuk badge */
        @media screen and (max-width: 768px) {
            .badge {
                display: block;
                margin: 4px 0;
                text-align: center;
            }
        }
    </style>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body>
    <div class="loader-container" id="loader">
        <div class="loader"></div>
    </div>

    <div class="content" id="mainContent">
        <header>
            <nav class="navbar navbar-expand navbar-grey bg-grey topbar mb-4 static-top shadow">
                <div class="container-fluid">
                    <!-- Logo dan Nama (Kiri) -->
                    <div class="d-flex align-items-center">
                        <a class="navbar-brand" href="homeTeacher.php">
                            <img class="foto" src="image\kementrian.png" alt="">
                        </a>
                        <h1 class="nama mb-0"> Selamat Datang, <?php echo htmlspecialchars($_SESSION['nama']); ?>!</h1>
                    </div>

                    <!-- Tombol Keluar (Kanan) -->
                    <a class="btn btn-outline-danger" href="logout.php">Keluar</a>
                </div>
            </nav>
        </header>

        <div class="container">
            <h2>Dashboard Absen & Tugas</h2>
            <?php echo $datesDropdown; ?>
            <div class="student-list">
                <?php echo $studentList; ?>
            </div>
        </div>

        <div id="studentModal" class="modal">
            <div class="modal-content">
                <span class="close">&times;</span>
                <h2 id="studentName"></h2>
                <input type="month" id="attendanceMonth">
                <textarea id="monthlyComment" placeholder="Masukkan komentar bulanan di sini"></textarea>
                <button id="acceptAllTasks" class="btn btn-success">Terima Semua Tugas di Bulan Ini</button>
                <table id="attendanceTable">
                    <thead>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
            </div>
        </div>

        <div id="taskModal" class="modal">
            <div class="modal-content">
                <span class="close" onclick="closeModal('taskModal')">&times;</span>
                <h2>Detail Tugas</h2>
                <p id="taskDescription"></p>
            </div>
        </div>

        <div id="attendanceDetailModal" class="modal">
            <div class="modal-content">
                <span class="close" onclick="closeModal('attendanceDetailModal')">&times;</span>
                <h1>Detail Absensi</h1>
                <div class="attendance-container">
                    <div class="attendance-section">
                        <h3>Absen Masuk</h3>
                        <img id="attendanceInImage" src="" alt="Attendance In">
                        <div class="map-container">
                            <div id="mapIn"></div>
                        </div>
                    </div>
                    <div class="attendance-section">
                        <h3>Absen Keluar</h3>
                        <img id="attendanceOutImage" src="" alt="Attendance Out">
                        <div class="map-container">
                            <div id="mapOut"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        $(document).ready(function() {
            // Hide loader and show content when page is fully loaded
            $('#loader').hide();
            $('#mainContent').show();

            $('.detail-btn').click(function() {
                var studentName = $(this).data('name');
                $('#studentName').text(studentName);
                $('#studentModal').css('display', 'block');
                // Fetch and display student details here if needed
            });

            $('.close').click(function() {
                closeModal($(this).closest('.modal').attr('id'));
            });

            $('#attendanceMonth').change(function() {
                var studentName = $('#studentName').text();
                var selectedMonth = $(this).val();
                fetchAttendanceData(studentName, selectedMonth);
            });

            function fetchAttendanceData(studentName, month = null) {
                $('#loader').show();
                $.ajax({
                    url: 'homeTeacher.php',
                    type: 'POST',
                    data: {
                        name: studentName,
                        month: month
                    },
                    success: function(data) {
                        $('#attendanceTable tbody').html(data);
                        $('.task-link').click(function(event) {
                            event.preventDefault();
                            showTaskDetails(this);
                        });
                        $('#loader').hide();
                    },
                    error: function() {
                        Swal.fire({
                            title: 'Waduh!',
                            text: 'Gagal ambil data nih',
                            icon: 'error'
                        });
                        $('#loader').hide();
                    }
                });
            }

            // Definisi global function showTaskDetails
            window.showTaskDetails = function(element) {
                var tasks = $(element).data('tasks');
                var content = '<div class="task-container">';

                if (Array.isArray(tasks)) {
                    tasks.forEach(function(task, index) {
                        content += `
                            <div class="task-item">
                                <h4>Tugas ${index + 1}</h4>
                                <div class="task-details">
                                    <p><b>Waktu:</b> ${formatDateTime(task.waktu)}</p>
                                    <p><b>Laporan:</b> ${task.laporan}</p>
                                    ${task.img_dir ? `
                                        <div class="task-image">
                                            <p><b>Gambar:</b></p>
                                            <img src="data:image/jpeg;base64,${task.img_dir}" 
                                                 alt="Gambar Laporan" 
                                                 onclick="enlargeImage(this)">
                                        </div>
                                    ` : ''}
                                    <p><b>Status:</b> 
                                        <span class="${task.status === '1' ? 'text-success' : 'text-warning'}">
                                            ${task.status === '1' ? 'Diterima' : 'Pending'}
                                        </span>
                                    </p>
                                    ${task.comment ? `<p><b>Komentar:</b> ${task.comment}</p>` : ''}
                                </div>
                            </div>
                        `;
                    });
                } else {
                    content += '<p>Tidak ada tugas</p>';
                }

                content += '</div>';
                $('#taskDescription').html(content);
                $('#taskModal').css('display', 'block');
            }

            // Helper function untuk format datetime
            function formatDateTime(datetime) {
                if (!datetime) return '';
                const dt = new Date(datetime);
                return dt.toLocaleString('id-ID', {
                    day: 'numeric',
                    month: 'long',
                    year: 'numeric',
                    hour: '2-digit',
                    minute: '2-digit'
                });
            }

            // Function untuk memperbesar gambar
            window.enlargeImage = function(img) {
                Swal.fire({
                    imageUrl: img.src,
                    imageAlt: 'Gambar Laporan',
                    width: '90%',
                    confirmButtonText: 'Tutup'
                });
            }

            // Event handler untuk close modal
            $('.close').click(function() {
                $(this).closest('.modal').css('display', 'none');
            });

            // Close modal ketika klik di luar modal
            $(window).click(function(event) {
                if ($(event.target).hasClass('modal')) {
                    $('.modal').css('display', 'none');
                }
            });

            function updateTaskStatus(action) {
                var name = $('#studentName').text();
                var status = action === 'accept' ? 1 : 0;
                var comment = $('#monthlyComment').val();
                var waktu = $('#taskDescription').data('waktu');

                $.ajax({
                    url: 'updateTaskStatusTeacher.php',
                    type: 'POST',
                    data: {
                        name: name,
                        status: status,
                        comment: comment,
                        waktu: waktu
                    },
                    success: function(response) {
                        Swal.fire({
                            title: 'Mantap!',
                            text: 'Status berhasil diupdate',
                            icon: 'success'
                        });
                        $('#taskModal').css('display', 'none');
                        fetchAttendanceData(name, $('#attendanceMonth').val());
                    },
                    error: function() {
                        Swal.fire({
                            title: 'Waduh!',
                            text: 'Gagal update status',
                            icon: 'error'
                        });
                    }
                });
            }

            $('#acceptButton').click(function() {
                updateTaskStatus('accept');
            });
            $('#denyButton').click(function() {
                updateTaskStatus('deny');
            });

            $('#acceptAllTasks').click(function() {
                var studentName = $('#studentName').text();
                var monthlyComment = $('#monthlyComment').val();
                var selectedMonth = $('#attendanceMonth').val();
                Swal.fire({
                    title: 'Yakin nih?',
                    text: "Semua tugas " + studentName + " di bulan " + selectedMonth + " bakal diterima pakai komentar bulanan kamu.",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Oke, terima semua!',
                    cancelButtonText: 'Gajadi deh'
                }).then((result) => {
                    if (result.isConfirmed) {
                        acceptAllTasks(studentName, monthlyComment, selectedMonth);
                    }
                });
            });

            function acceptAllTasks(studentName, monthlyComment, month) {
                $.ajax({
                    url: 'acceptAllTasksTeacher.php',
                    type: 'POST',
                    data: {
                        name: studentName,
                        comment: monthlyComment,
                        month: month
                    },
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            Swal.fire(
                                'Mantap!',
                                response.message,
                                'success'
                            );
                            fetchAttendanceData(studentName, month);
                            $('#monthlyComment').val('');
                        } else {
                            Swal.fire(
                                'Duh!',
                                response.error || 'Gagal nerima tugas nih.',
                                'error'
                            );
                        }
                    },
                    error: function() {
                        Swal.fire(
                            'Yah!',
                            'Ada masalah nih pas nyambung ke server.',
                            'error'
                        );
                    }
                });
            }

            $('#attendanceMonth').change(function() {
                var studentName = $('#studentName').text();
                var selectedMonth = $(this).val();
                fetchAttendanceData(studentName, selectedMonth);
            });

            function fetchAttendanceData(studentName, month = null) {
                $('#loader').show();
                $.ajax({
                    url: 'homeTeacher.php',
                    type: 'POST',
                    data: {
                        name: studentName,
                        month: month
                    },
                    success: function(data) {
                        $('#attendanceTable tbody').html(data);
                        $('.task-link').click(function(event) {
                            event.preventDefault();
                            showTaskDetails(this);
                        });
                        $('#loader').hide();
                    },
                    error: function() {
                        Swal.fire({
                            title: 'Waduh!',
                            text: 'Gagal ambil data nih',
                            icon: 'error'
                        });
                        $('#loader').hide();
                    }
                });
            }

            window.showAttendanceDetails = function(name, date) {
                $.ajax({
                    url: 'fetchAttendanceDetails.php',
                    type: 'POST',
                    data: {
                        name: name,
                        date: date
                    },
                    dataType: 'json',
                    success: function(response) {
                        if (response.in && response.out) {
                            $('#attendanceInImage').attr('src', response.in.img);
                            $('#attendanceOutImage').attr('src', response.out.img);

                            // Tampilkan jam absen
                            $('#attendanceInTime').text('Waktu Masuk: ' + response.in.time);
                            $('#attendanceOutTime').text('Waktu Keluar: ' + response.out.time);

                            // Inisialisasi peta untuk absen masuk
                            initMap('mapIn', response.in.lat, response.in.lng);

                            // Inisialisasi peta untuk absen keluar
                            initMap('mapOut', response.out.lat, response.out.lng);

                            $('#attendanceDetailModal').css('display', 'block');
                        } else {
                            Swal.fire('Oops!', 'Data absen gak lengkap nih', 'warning');
                        }
                    },
                    error: function() {
                        Swal.fire('Duh!', 'Gagal ambil detail absen nih', 'error');
                    }
                });
            }

            function initMap(elementId, lat, lng) {
                if (window[elementId + 'Map']) {
                    window[elementId + 'Map'].remove();
                }
                window[elementId + 'Map'] = L.map(elementId).setView([lat, lng], 15);
                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
                }).addTo(window[elementId + 'Map']);
                L.marker([lat, lng]).addTo(window[elementId + 'Map']);
            }

            window.closeModal = function(modalId) {
                $('#' + modalId).css('display', 'none');

                // Reset isi modal
                if (modalId === 'studentModal') {
                    $('#studentName').text('');
                    $('#attendanceMonth').val('');
                    $('#monthlyComment').val('');
                    $('#attendanceTable tbody').empty();
                } else if (modalId === 'taskModal') {
                    $('#taskDescription').empty();
                } else if (modalId === 'attendanceDetailModal') {
                    $('#attendanceInImage').attr('src', '');
                    $('#attendanceOutImage').attr('src', '');
                    if (window.mapInMap) {
                        window.mapInMap.remove();
                        window.mapInMap = null;
                    }
                    if (window.mapOutMap) {
                        window.mapOutMap.remove();
                        window.mapOutMap = null;
                    }
                }
            }
        });
    </script>

    <script>
        document.getElementById('dateFilter').addEventListener('change', function() {
            var selectedDates = this.value;
            window.location.href = '?dates=' + encodeURIComponent(selectedDates);
        });
    </script>
</body>

</html>

</script>
</body>

</html>

</html>