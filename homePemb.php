<?php
session_start();
require('koneksi.php');
require('auth.php');
require_once('navPembimbing.php');


if (!isset($_SESSION['role']) || $_SESSION['role'] !== '4') {
    header("Location: index.php");
    exit();
}

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

// Fetch school data for dropdown
$sekolahQuery = "SELECT DISTINCT asal_sekolah FROM akun WHERE role = '1'"; // Query baru untuk mengambil sekolah dari akun murid
$sekolahResult = mysqli_query($conn, $sekolahQuery);
$sekolahOptions = "";
while ($row = mysqli_fetch_assoc($sekolahResult)) {
    $sekolahOptions .= "<option value='{$row['asal_sekolah']}'>{$row['asal_sekolah']}</option>";
}

// Fetch unique internship periods for dropdown
$periodeQuery = "SELECT DISTINCT magang_masuk, magang_keluar FROM akun WHERE role = '1' ORDER BY magang_masuk";
$periodeResult = mysqli_query($conn, $periodeQuery);
$periodeOptions = "";
while ($row = mysqli_fetch_assoc($periodeResult)) {
    $periodeOptions .= "<option value='{$row['magang_masuk']} - {$row['magang_keluar']}'>{$row['magang_masuk']} - {$row['magang_keluar']}</option>";
}

// Tambahkan query default untuk menampilkan semua data
$defaultQuery = "SELECT DISTINCT a.nama, a.asal_sekolah, a.magang_masuk, a.magang_keluar 
                FROM akun a 
                WHERE a.role = '1' 
                ORDER BY a.asal_sekolah, a.nama";
$defaultResult = mysqli_query($conn, $defaultQuery);

// Siapkan data default untuk ditampilkan
$defaultStudentList = "<table>
    <thead>
        <tr>
            <th>Nama</th>
            <th>Asal Sekolah</th>
            <th>Periode Magang</th>
            <th>Detail Absen</th>
            <th>Timeline Pekerjaan</th>
            <th>Tugas</th>
            <th>Cetak Laporan</th>
        </tr>
    </thead>
    <tbody>";

while ($row = mysqli_fetch_assoc($defaultResult)) {
    $defaultStudentList .= "<tr>
        <td>{$row['nama']}</td>
        <td>{$row['asal_sekolah']}</td>
        <td>{$row['magang_masuk']} - {$row['magang_keluar']}</td>
        <td><button class='detail-btn' data-name='{$row['nama']}'>Lihat</button></td>
        <td>
            <a href='timeline-pembimbing.php?nama=" . urlencode($row['nama']) . "' class='btn btn-primary timeline-btn'>
                <i class='fas fa-clock'></i> Lihat
            </a>
        </td>
        <td>
            <button class='btn btn-warning task-btn' onclick='beriTugas(\"{$row['nama']}\")'>
                <i class='fas fa-tasks'></i> Beri Tugas
            </button>
        </td>
        <td><a href='laporan_admin.php?nama={$row['nama']}' class='report-btn' target='_blank'>Cetak</a></td>
    </tr>";
}
$defaultStudentList .= "</tbody></table>";

// Fetch student data based on selected school and period
if (isset($_POST['school'])) {
    $selectedSchool = '%' . trim($_POST['school']) . '%';
    $periode = isset($_POST['periode']) && strpos($_POST['periode'], ' - ') !== false ? explode(" - ", $_POST['periode']) : null;

    if ($periode) {
        $magang_masuk = $periode[0];
        $magang_keluar = $periode[1];
        $studentQuery = "SELECT nama, asal_sekolah, magang_masuk, magang_keluar FROM akun WHERE asal_sekolah LIKE ? AND magang_masuk = ? AND magang_keluar = ? AND role = '1'";
        $stmt = mysqli_prepare($conn, $studentQuery);
        mysqli_stmt_bind_param($stmt, "sss", $selectedSchool, $magang_masuk, $magang_keluar);
    } else {
        $studentQuery = "SELECT nama, asal_sekolah, magang_masuk, magang_keluar FROM akun WHERE asal_sekolah LIKE ? AND role = '1'";
        $stmt = mysqli_prepare($conn, $studentQuery);
        mysqli_stmt_bind_param($stmt, "s", $selectedSchool);
    }

    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $studentList = "<table id='attendanceTable'>
        <thead>
            <tr>
                <th>Nama</th>
                <th>Asal Sekolah</th>
                <th>Periode Magang</th>
                <th>Detail Absen</th>
                <th>Timeline Pekerjaan</th>
                <th>Tugas</th>
                <th>Cetak Laporan</th>
            </tr>
        </thead>
        <tbody>";

    while ($row = mysqli_fetch_assoc($result)) {
        $studentList .= "<tr>
            <td>" . htmlspecialchars($row['nama']) . "</td>
            <td>" . htmlspecialchars($row['asal_sekolah']) . "</td>
            <td>" . htmlspecialchars($row['magang_masuk']) . " - " . htmlspecialchars($row['magang_keluar']) . "</td>
            <td><button class='detail-btn' data-name='" . htmlspecialchars($row['nama']) . "'>Lihat</button></td>
            <td>
                <a href='timeline-pembimbing.php?nama=" . urlencode($row['nama']) . "' class='btn btn-primary timeline-btn'>
                    <i class='fas fa-clock'></i> Lihat
                </a>
            </td>
            <td>
                <button class='btn btn-warning task-btn' onclick='beriTugas(\"" . htmlspecialchars($row['nama']) . "\")'>
                    <i class='fas fa-tasks'></i> Beri Tugas
                </button>
            </td>
            <td><a href='laporan_admin.php?nama=" . urlencode($row['nama']) . "' class='report-btn' target='_blank'>Cetak</a></td>
        </tr>";
    }
    $studentList .= "</tbody></table>";
    echo $studentList;
    exit();
}

// Handle attendance and task data fetching
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['name'])) {
    $name = $_POST['name'];
    $month = isset($_POST['month']) ? $_POST['month'] : null;

    $attendanceQuery = "
        SELECT 
            DATE(waktu) as tanggal,
            COUNT(CASE WHEN waktu IS NOT NULL THEN 1 END) as status
        FROM images
        WHERE user = ?
    ";

    $params = array($name);

    if ($month) {
        $attendanceQuery .= " AND DATE_FORMAT(waktu, '%Y-%m') = ?";
        $params[] = $month;
    }

    $attendanceQuery .= "
        GROUP BY DATE(waktu)
        HAVING COUNT(*) >= 2
        ORDER BY tanggal
    ";

    $attendanceStmt = $conn->prepare($attendanceQuery);
    $attendanceStmt->bind_param(str_repeat('s', count($params)), ...$params);
    $attendanceStmt->execute();
    $attendanceResult = $attendanceStmt->get_result();

    echo '<table>';
    echo '<thead>';
    echo '<tr>';
    echo '<th>Tanggal</th>';
    echo '<th>Status</th>';
    echo '<th>Detail Absen</th>';
    echo '</tr>';
    echo '</thead>';
    echo '<tbody>';

    while ($attendanceRow = $attendanceResult->fetch_assoc()) {
        echo '<tr>';
        echo '<td>' . htmlspecialchars($attendanceRow['tanggal']) . '</td>';
        echo '<td>' . ($attendanceRow['status'] == 1 ? 'Hadir' : 'Hadir') . '</td>';
        echo '<td><a href="#" class="view-details" data-name="' . htmlspecialchars($name) . '" data-date="' . htmlspecialchars($attendanceRow['tanggal']) . '" onclick="showAttendanceDetails(\'' . htmlspecialchars($name) . '\', \'' . htmlspecialchars($attendanceRow['tanggal']) . '\')">View Details</a></td>';
        echo '</tr>';
    }

    echo '</tbody>';
    echo '</table>';
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
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&family=Roboto:wght@300;400;700&display=swap" rel="stylesheet">
    <title>Data Absen</title>
    <style>
        :root {
            --primary-color: #004F9F;
            --secondary-color: #2E8B57;
            --danger-color: #dc3545;
            --light-color: #f8f9fa;
            --dark-color: #343a40;
        }

        .btn-success,
        .month-indicator.accepted {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
            color: var(--light-color);
        }

        .btn-success:hover,
        .month-indicator.accepted:hover {
            background-color: #218838;
            /* Warna hover hijau Bootstrap */
            border-color: #1e7e34;
        }

        .month-indicator.unaccepted {
            background-color: var(--danger-color);
            border-color: var(--danger-color);
            color: var(--light-color);
        }

        .month-indicator.unaccepted:hover {
            background-color: #c82333;
            /* Warna hover merah Bootstrap */
            border-color: #bd2130;
        }

        .month-indicator {
            padding: 5px 10px;
            margin: 5px;
            border-radius: 15px;
            cursor: pointer;
            font-size: 14px;
            transition: all 0.3s ease;
        }

        .loading-overlay {
            background-color: rgba(52, 58, 64, 0.8);
            /* Menggunakan warna dark dengan opacity */
        }

        .spinner {
            border: 5px solid var(--light-color);
            border-top: 5px solid var(--primary-color);
        }

        /* Tambahan styling untuk elemen lain */
        #attendanceMonth {
            border: 1px solid var(--primary-color);
            color: var(--dark-color);
        }

        #monthlyComment {
            border: 1px solid var(--primary-color);
            color: var(--dark-color);
        }

        #attendanceTable {
            border-collapse: collapse;
            width: 100%;
        }

        #attendanceTable th,
        #attendanceTable td {
            border: 1px solid var(--primary-color);
            padding: 8px;
            text-align: left;
            color: #ffffff;
        }

        #attendanceTable th {
            background-color: var(--primary-color);
            color: var(--light-color);
            color: #ffffff;
        }

        body {
            font-family: 'Roboto', sans-serif;
            font-size: 16px;
            line-height: 1.6;
            color: #333;
            background-color: #ffffff;
        }

        h1,
        h2,
        h3,
        h4,
        h5,
        h6 {
            font-family: 'Poppins', sans-serif;

        }

        .navbar,
        .kemendag-text h2 {
            font-family: 'Poppins', sans-serif;
            font-weight: 600;
        }

        .kemendag-text h2 {
            font-size: 20px;
        }

        .kemendag-text p {
            font-family: 'Roboto', sans-serif;
            font-weight: 400;
            font-size: 14px;
        }

        .nama {
            font-family: 'Poppins', sans-serif;

            font-size: 18px;
        }

        .logout {
            font-family: 'Roboto', sans-serif;
            font-weight: 400;
            font-size: 14px;
        }

        .container h2 {
            font-size: 18px;
            margin-bottom: 20px;
        }

        select,
        button {
            font-family: 'Roboto', sans-serif;
            font-size: 14px;
        }

        table {
            font-family: 'Roboto', sans-serif;
            font-size: 14px;
        }

        th {
            font-family: 'Poppins', sans-serif;
            font-weight: 600;
            color: #ffffff;
        }

        .modal-content h2 {
            font-family: 'Poppins', sans-serif;
            font-weight: 600;
            font-size: 20px;
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

        .header {
            position: fixed;
            background-color: #ffffff;
            width: 100%;
            padding: 0.5rem 1rem;
            height: auto;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            z-index: 1002;
        }

        .container-fluid {
            max-width: 1400px;
            margin: 0 auto;
            padding-right: 15px;
            margin-left: 80px;
            transition: margin-left 0.3s ease;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .navbar-brand {
            display: flex;
            align-items: center;
        }

        .foto {
            height: 45px;
            width: auto;
            filter: drop-shadow(1px 1px 20px rgba(0, 255, 238, 0.5));
            transition: transform 0.3s ease;
            margin-right: 15px;
        }

        .kemendag-text {
            display: flex;
            flex-direction: column;
        }

        .kemendag-text h2 {
            color: #004F9F;
            font-size: 18px;
            margin: 0;
            font-family: 'Poppins', sans-serif;
            font-weight: 600;
        }

        .kemendag-text p {
            color: #2E8B57;
            font-size: 14px;
            margin: 0;
            font-family: 'Poppins', sans-serif;
            font-weight: 400;
        }

        .nama {
            color: #333333;
            font-size: 16px;
            font-weight: 500;
            margin: 0;
            padding: 0 15px;
            font-family: 'Poppins', sans-serif;
        }

        .logout {
            color: #dc3545;
            text-decoration: none;
            font-size: 14px;
            font-weight: 500;
            padding: 8px 20px;
            border-radius: 5px;
            transition: all 0.3s ease;
            border: 1px solid #dc3545;
        }

        .logout:hover {
            background-color: #dc3545;
            color: #ffffff;
            transform: translateY(-2px);
            box-shadow: 0 2px 5px rgba(220, 53, 69, 0.2);
        }

        .separator {
            border-left: 1px solid #484b6a;
            height: 25px;
            margin: 0 15px;
            opacity: 0.3;
        }

        .sidebar {
            position: fixed;
            left: 0;
            top: 0;
            bottom: 0;
            width: 80px;
            background-color: #ffffff;
            border-right: 1px solid rgba(0, 79, 159, 0.1);
            z-index: 1000;
            box-shadow: 2px 0 5px rgba(0, 0, 0, 0.1);
            padding-top: 60px;
            display: flex;
            flex-direction: column;
            align-items: center;
            transition: width 0.2s ease;
            padding-bottom: 40px;
            overflow-x: hidden;
        }

        .sidebar.expanded {
            width: 200px;
        }

        .icon-container {
            width: 60px;
            height: 60px;
            border-radius: 15px;
            display: flex;
            justify-content: center;
            align-items: center;
            margin-bottom: 20px;
            margin-top: 50px;
            transition: background-color 0.2s ease, color 0.2s ease;
            cursor: pointer;
            color: #004F9F;
            position: relative;
            overflow: hidden;
        }

        .icon-container.active {
            color: #ffffff;
            background-color: #004F9F;
        }

        .icon-container:hover {
            background-color: rgba(0, 79, 159, 0.1);
            color: #004F9F;
        }

        .submenu {
            opacity: 0;
            display: none;
            flex-direction: column;
            align-items: flex-start;
            padding: 5px;
            transition: all 0.3s ease;
            max-height: 0;
            overflow: hidden;
            transform: translateY(-10px);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .submenu-active {
            display: flex;
            max-height: 300px;
            opacity: 1;
            transform: translateY(0);
        }

        .submenu ul {
            list-style-type: none;
            padding: 0;
            margin: 0;
            width: 100%;
        }

        .submenu li {
            width: 100%;
            margin: 2px 0;
        }

        .submenu a {
            color: #004F9F;
            text-decoration: none;
            padding: 10px 15px;
            display: block;
            border-radius: 8px;
            transition: all 0.2s ease;
            font-size: 14px;
            width: 100%;
            position: relative;
            background: linear-gradient(90deg, rgba(0, 79, 159, 0) 0%, rgba(0, 79, 159, 0) 100%);
            background-size: 200% 100%;
            background-position: 100% 0;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .submenu a:hover {
            color: #004F9F;
            background-color: rgba(0, 79, 159, 0.08);
            transform: translateX(5px);
            background-position: 0 0;
            padding-left: 20px;
        }

        .spacer {
            flex-grow: 1;
        }

        /* Icon container hover effect */
        .icon-container::after {
            content: '';
            position: absolute;
            width: 100%;
            height: 100%;
            background: rgba(0, 79, 159, 0.08);
            border-radius: 15px;
            top: 0;
            left: -100%;
            transition: all 0.3s ease;
        }

        .icon-container:hover::after {
            left: 0;
        }



        h2,
        p {
            text-align: center;
            margin: 20px 0;
        }

        select {
            width: 100%;
            padding: 10px;
            background-color: #E7E8D8;
            border: none;
            border-radius: 5px;
            color: #484b6a;
            font-size: 14px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        th,
        td {
            padding: 10px;
            text-align: left;
            border-bottom: 1px solid #d2d3db;
            color: #ffffff;
        }

        th {
            background-color: #50B498;
            /* Warna biru */
            color: #ffffff;
        }

        td {
            background-color: #ffffff;
            color: #484b6a;
        }

        .modal {
            display: none;
            position: fixed;
            z-index: 99999999999 !important;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            color: #484b6a;
            background-color: rgba(0, 0, 0, 0.8);
        }

        .modal-content {
            background-color: #e4e5f1;
            color: #484b6a;
            margin: 5% auto;
            padding: 20px;
            border: 1px solid #d2d3db;
            width: 90%;
            max-width: 600px;
            border-radius: 8px;
        }

        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
        }

        .close:hover,
        .close:focus {
            color: #484b6a;
            text-decoration: none;
            cursor: pointer;
        }

        button {
            background-color: #E7E8D8;
            padding: 10px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        button:hover {
            background-color: #9394a5;
        }

        #attendanceTable {
            width: 100%;
            border-collapse: collapse;
        }

        #attendanceTable th,
        #attendanceTable td {
            padding: 10px;
            text-align: left;
            border-bottom: 1px solid #d2d3db;
            border-right: 1px solid #d2d3db;
            vertical-align: top;
            color: #ffffff;
        }

        #attendanceTable th {
            background-color: #e4e5f1;
            color: #484b6a;
        }

        #attendanceTable td {
            background-color: #ffffff;
            color: #484b6a;
        }

        #attendanceTable tr:last-child td {
            border-bottom: none;
        }

        #attendanceTable th:last-child,
        #attendanceTable td:last-child {
            border-right: none;
            color: #ffffff;
        }

        .acceptButton {
            background-color: #47d147;
            color: #ffffff;
        }

        .denyButton {
            background-color: #ff4d4d;
            color: #ffffff;
        }

        h2 {
            color: #333;
            font-weight: 400;
            font-size: 20px;
            /* Update font size */
            letter-spacing: -0.5px;

        }

        label,
        p,
        li {
            font-size: 15px;
            font-weight: 400;
        }

        .pemisah {
            margin-top: 100px;
        }

        .school-header {
            background: #e4e5f1;
            color: white;
            padding: 10px;
            border-radius: 4px;
            margin-bottom: 10px;
            text-align: center;
        }

        .period-container {
            width: 100%;
            padding: 20px;
            position: sticky;
            top: 0;
            background-color: white;
            margin-bottom: 10px;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            z-index: 0;
        }

        .sidebar {
            display: flex;
            flex-direction: column;
            padding-bottom: 20px;
        }

        .spacer {
            flex-grow: 1;
        }

        .sidebar .icon-container:last-child {
            margin-bottom: 10px;
        }

        .modal {
            display: none;
            position: fixed;
            z-index: 9999999;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.4);
        }

        .modal-content {
            background-color: #fefefe;
            margin: 15% auto;
            padding: 20px;
            border: 1px solid #888;
            width: 80%;
            max-width: 500px;
        }

        .modal-content input,
        .modal-content textarea {
            width: 100%;
            margin-bottom: 10px;
            padding: 5px;
        }

        .button-group {
            text-align: right;
        }

        .button-group button {
            margin-left: 10px;
        }

        .jud {
            padding-left: 30px;
        }

        .loading-indicator {
            display: none;
            text-align: center;
            padding: 20px;
        }

        .loading-indicator i {
            font-size: 24px;
            color: #50B498;
        }

        .swal2-container {
            z-index: 9999999999999 !important;
        }

        .month-indicators {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            margin-bottom: 15px;
        }

        .month-indicator {
            padding: 5px 10px;
            margin: 5px;
            border-radius: 15px;
            cursor: pointer;
            font-size: 14px;
        }

        .month-indicator.unaccepted {
            background-color: #ff4d4d;
            color: white;
        }

        .month-indicator.accepted {
            background-color: #47d147;
            color: white;
        }

        #attendanceMonth {
            display: block;
            margin: 10px auto;
            padding: 5px;
        }

        .loading-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.7);
            z-index: 99999999;
            justify-content: center;
            align-items: center;
        }

        .spinner {
            width: 50px;
            height: 50px;
            border: 5px solid #f3f3f3;
            border-top: 5px solid #3498db;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% {
                transform: rotate(0deg);
            }

            100% {
                transform: rotate(360deg);
            }
        }

        .modal-content-p {
            background-color: #fefefe;
            margin: 15% auto;
            padding: 20px;
            border: 1px solid #888;
            width: 80%;
            max-width: 500px;
        }

        .modal-content-p input,
        .modal-content-p textarea {
            width: 100%;
            margin-bottom: 10px;
            padding: 5px;
        }

        .button-group {
            text-align: right;
        }

        .button-group button {
            margin-left: 10px;
        }

        /* Update styles untuk header */
        .container-fluid {
            padding: 0 20px;
            margin-left: 80px;
        }

        .kemendag-text {
            text-align: left !important;

        }

        .kemendag-text h2 {
            font-size: 18px;
            color: #004F9F;
            margin: 0;
            line-height: 1.2;
            text-align: left;
        }

        .kemendag-text p {
            font-size: 14px;
            color: #2E8B57;
            margin: 0;
            line-height: 1.2;
            text-align: left;
        }

        .foto {
            height: 45px;
            width: auto;
        }

        .nama {
            font-size: 16px;
            margin: 0;
        }

        .logout {
            white-space: nowrap;
        }

        /* Update styles untuk container utama */
        .container {
            margin-top: 6rem;
            width: 100%;
            max-width: 1200px;
            min-width: 320px;
            margin-left: auto;
            margin-right: auto;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            margin-bottom: 10rem;
        }

        /* Update styles untuk heading dan text */
        .container h2 {
            margin-top: 10px;
            text-align: center;
            color: #484b6a;
            font-size: 24px;
            font-weight: 600;
        }


        .container p {
            font-family: 'Poppins', sans-serif;
            font-size: 16px;
            color: #566A7F;
            margin: 20px 0 10px;
        }

        /* Update styles untuk select boxes */
        select {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #E7ECF3;
            border-radius: 12px;
            font-family: 'Poppins', sans-serif;
            font-size: 14px;
            color: #566A7F;
            background: #FFFFFF;
            transition: all 0.3s ease;
            margin-bottom: 20px;
        }

        select:focus {
            border-color: var(--primary-color) !important;
            box-shadow: 0 0 0 3px rgba(0, 79, 159, 0.1) !important;
            outline: none;
        }

        /* Update styles untuk table */
        table {
            width: 100%;
            border-spacing: 0;
            border-collapse: separate;
            margin-top: 25px;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
            min-width: 800px;
            max-width: 100%;
        }

        th {
            background: var(--primary-color) !important;
            color: #FFFFFF !important;
            font-family: 'Poppins', sans-serif;
            font-weight: 500;
            font-size: 14px;
            padding: 15px 20px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            min-width: 120px;
            max-width: 300px;
        }

        td {
            padding: 15px 20px;
            font-size: 14px;
            color: #566A7F;
            border-bottom: 1px solid #E7ECF3;
            background: #FFFFFF;
            min-width: 120px;
            max-width: 300px;
        }

        tr:last-child td {
            border-bottom: none;
        }

        /* Update styles untuk buttons */
        .detail-btn,
        .report-btn,
        .timeline-btn,
        .task-btn {
            padding: 8px 16px;
            border-radius: 8px;
            font-family: 'Poppins', sans-serif;
            font-size: 13px;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .detail-btn {
            background: var(--primary-color) !important;
            color: #FFFFFF;
        }

        .detail-btn:hover {
            background: #003d7a !important;
            transform: translateY(-2px);
        }

        .report-btn {
            background: #E7ECF3;
            color: #566A7F;
            text-decoration: none;
            display: inline-block;
        }

        .report-btn:hover {
            background: #d1d7e0;
            color: #2E384D;
            transform: translateY(-2px);
        }

        .timeline-btn {
            background: linear-gradient(135deg, #004B8F, #0072BC);
            color: white;
        }

        .task-btn {
            background: #ffc107;
            color: #000;
            border: none;
        }

        .task-btn:hover {
            background: #e0a800;
            transform: translateY(-2px);
            color: #000;
        }

        .fa-tasks,
        .fa-clock {
            font-size: 13px;
            margin-right: 5px;
        }

        /* Loading indicator styles */
        .loading-indicator {
            text-align: center;
            padding: 40px;
            color: #50B498;
        }

        .loading-indicator i {
            font-size: 30px;
        }

        /* Empty state styles */
        .student-list:empty {
            text-align: center;
            padding: 40px;
            color: #566A7F;
            font-family: 'Poppins', sans-serif;
            font-size: 15px;
        }

        /* Hover effect untuk table rows */
        tr:hover td {
            background: #F8FAFC;
        }

        /* Update modal styles */
        .modal-content {
            border-radius: 20px;
            border: none;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
            width: 90%;
            min-width: 320px;
            max-width: 1000px;
            margin: 3% auto;
            max-height: 90vh;
            overflow-y: auto;
        }

        .modal-content h2 {
            font-family: 'Poppins', sans-serif;
            font-size: 24px;
            color: #2E384D;
            margin-bottom: 25px;
        }

        /* Month indicator styles */
        .month-indicators {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-bottom: 20px;
        }

        .month-indicator {
            padding: 8px 16px;
            border-radius: 20px;
            font-family: 'Poppins', sans-serif;
            font-size: 13px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .month-indicator:hover {
            transform: translateY(-2px);
        }

        /* Update styling untuk modal lokasi absen */
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

        /* Styling untuk popup di map */
        .leaflet-popup-content {
            font-family: 'Poppins', sans-serif;
            font-size: 13px;
            color: #566A7F;
        }

        .leaflet-container {
            font-family: 'Poppins', sans-serif;
        }

        /* Styling untuk modal task */
        #taskModal .modal-content {
            max-width: 900px;
            min-width: 320px;
        }

        .task-item {
            background: #F8F9FA;
            border-radius: 12px;
            padding: 15px;
            margin-bottom: 15px;
        }

        .task-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 12px;
            padding-bottom: 8px;
            border-bottom: 1px solid #E7ECF3;
        }

        .task-time {
            font-family: 'Poppins', sans-serif;
            font-size: 14px;
            color: #566A7F;
        }

        .task-status {
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 500;
        }

        .task-status.accepted {
            background: #E1F6EC;
            color: #2E8B57;
        }

        .task-status.pending {
            background: #FFF4E5;
            color: #FF9800;
        }

        .task-content {
            margin-top: 10px;
        }

        .task-description {
            font-size: 14px;
            color: #2E384D;
            margin-bottom: 12px;
            line-height: 1.5;
        }

        .task-image {
            width: 100%;
            max-height: 400px;
            min-height: 200px;
            object-fit: contain;
            border-radius: 8px;
            border: 1px solid #E7ECF3;
            max-width: 100%;
        }

        .mt-4 {
            margin-top: 1.5rem;
        }

        .timeline-btn {
            background: linear-gradient(135deg, #004B8F, #0072BC);
            color: white;
        }

        /* Tambahkan CSS ini */
        .task-modal {
            max-width: 800px !important;
            width: 90% !important;
            padding: 20px !important;
        }

        .task-title {
            color: #2E384D;
            font-size: 18px;
            margin-bottom: 20px;
            font-family: 'Poppins', sans-serif;
            font-weight: 600;
        }

        .task-container {
            display: flex;
            gap: 20px;
            height: 400px;
        }

        .task-list,
        .task-form {
            background: #F8F9FA;
            padding: 20px;
            border-radius: 12px;
        }

        .task-list {
            flex: 1;
        }

        .task-form {
            width: 300px;
        }

        .task-list h3,
        .task-form h3 {
            color: #2E384D;
            font-size: 14px;
            margin-bottom: 15px;
            font-family: 'Poppins', sans-serif;
            font-weight: 500;
        }

        .task-items {
            height: calc(100% - 40px);
            overflow-y: auto;
            padding-right: 10px;
        }

        .task-item {
            background: white;
            padding: 12px 15px;
            border-radius: 8px;
            margin-bottom: 8px;
            border: 1px solid #E7ECF3;
            font-size: 13px;
            color: #566A7F;
            font-family: 'Roboto', sans-serif;
        }

        .form-group label {
            display: block;
            color: #2E384D;
            font-size: 13px;
            margin-bottom: 8px;
            font-family: 'Roboto', sans-serif;
        }

        .form-group input {
            width: 100%;
            padding: 8px 12px;
            border: 2px solid #E7ECF3;
            border-radius: 8px;
            font-size: 13px;
            margin-bottom: 15px;
            font-family: 'Roboto', sans-serif;
        }

        .submit-task {
            margin-top: auto;
            background: var(--primary-color);
            color: white;
            border: none;
            padding: 8px 12px;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
            font-family: 'Poppins', sans-serif;
        }

        .submit-task:hover {
            background: #003d7a;
            transform: translateY(-2px);
        }

        /* Custom scrollbar yang lebih kecil */
        .task-items::-webkit-scrollbar {
            width: 6px;
        }

        .task-items::-webkit-scrollbar-track {
            background: #F8F9FA;
        }

        .task-items::-webkit-scrollbar-thumb {
            background: #CED4DA;
            border-radius: 3px;
        }

        .task-items::-webkit-scrollbar-thumb:hover {
            background: #ADB5BD;
        }

        /* Update close button */
        .task-modal .close {
            font-size: 20px;
            color: #566A7F;
            opacity: 0.7;
        }

        .task-modal .close:hover {
            opacity: 1;
        }

        /* Responsive styles */
        @media screen and (max-width: 768px) {
            .container {
                padding: 15px;
                margin-top: 2rem;
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

    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.7.1/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.7.1/dist/leaflet.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body>
    <!-- Tambahkan ini di bagian atas body -->
    <div id="loadingOverlay" class="loading-overlay">
        <div class="spinner"></div>
    </div>

    <div class="container">
        <h2>Dashboard Absen & Tugas</h2>
        <div class="filter-section">
            <div class="row">
                <div class="col-md-6">
                    <p>Filter Sekolah</p>
                    <select id="schoolSelect">
                        <option value="">Semua Sekolah</option>
                        <?php echo $sekolahOptions; ?>
                    </select>
                </div>
                <div class="col-md-6">
                    <p>Filter Periode</p>
                    <select id="periodeSelect">
                        <option value="">Semua Periode</option>
                        <?php echo $periodeOptions; ?>
                    </select>
                </div>
            </div>
        </div>

        <div class="loading-indicator">
            <i class="fas fa-spinner fa-spin"></i> Loading...
        </div>

        <div class="student-list">
            <?php echo $defaultStudentList; ?>
        </div>
    </div>

    <div id="studentModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal('studentModal')">&times;</span>
            <h2 id="studentName"></h2>

            <input type="month" id="attendanceMonth">
            <table id="attendanceTable">
                <thead>

                </thead>
                <tbody>
                </tbody>
            </table>
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

    <div id="settingsModal" class="modal">
        <div class="modal-content-p">
            <span class="close" onclick="closeSettingsModal()">&times;</span>
            <h2>Login Layout Settings</h2>
            <form id="settingsForm">
                <input type="text" id="first" name="first" placeholder="Welcome Text">
                <input type="text" id="second" name="second" placeholder="Instruction Text">
                <textarea id="description" name="description" placeholder="Description"></textarea>
                <div class="button-group">
                    <button type="submit">Simpan</button>
                    <button type="button" onclick="closeSettingsModal()">Batal</button>
                </div>
            </form>
        </div>
    </div>

    <div id="editAdminLayoutModal" class="modal">
        <div class="modal-content-p">
            <span class="close" onclick="closeEditAdminLayoutModal()">&times;</span>
            <h2>Admin Layout Settings</h2>
            <form id="editAdminLayoutForm">
                <input type="text" id="editAdminWelcome" name="first" placeholder="Welcome Text">
                <input type="text" id="editAdminInstruction" name="second" placeholder="Instruction Text">
                <textarea id="editAdminDescription" name="description" placeholder="Description"></textarea>
                <div class="button-group">
                    <button type="submit">Simpan</button>
                    <button type="button" onclick="closeEditAdminLayoutModal()">Batal</button>
                </div>
            </form>
        </div>
    </div>

    <div id="taskModal" class="modal">
        <div class="modal-content task-modal">
            <span class="close" onclick="closeTaskModal()">&times;</span>
            <h2 class="task-title">Beri Tugas - <span id="selectedStudent"></span></h2>

            <div class="task-container">
                <!-- Bagian kiri - List tugas -->
                <div class="task-list">
                    <h3>Daftar Tugas</h3>
                    <div class="task-items">
                        <!-- Nanti diisi pake JS -->
                    </div>
                </div>

                <!-- Bagian kanan - Form simplified -->
                <div class="task-form">
                    <h3>Tambah Tugas Baru</h3>
                    <form id="newTaskForm">
                        <div class="form-group">
                            <label for="taskType">Jenis Tugas</label>
                            <input type="text" id="taskType" required placeholder="Masukkan jenis tugas">
                        </div>
                        <button type="submit" class="submit-task">Kirim Tugas</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="fontawesome/js/all.min.js"></script>
    <script>
        function toggleSubmenu(id, clickedIcon) {
            const submenu = document.getElementById(id);
            const sidebar = document.querySelector('.sidebar');
            const container = document.querySelector('.container-fluid');
            const allIcons = document.querySelectorAll('.icon-container');

            if (submenu.classList.contains('submenu-active')) {
                submenu.classList.remove('submenu-active');
                sidebar.classList.remove('expanded');
                container.classList.remove('expanded');
                setTimeout(() => {
                    submenu.style.display = 'none';
                }, 500);
                clickedIcon.classList.remove('active');
            } else {
                const activeSubmenus = document.querySelectorAll('.submenu-active');
                activeSubmenus.forEach(sub => {
                    sub.classList.remove('submenu-active');
                    sub.style.display = 'none';
                });
                allIcons.forEach(icon => {
                    icon.classList.remove('active');
                });
                submenu.style.display = 'flex';
                setTimeout(() => {
                    submenu.classList.add('submenu-active');
                    sidebar.classList.add('expanded');
                    container.classList.add('expanded');
                }, 10);
                clickedIcon.classList.add('active');
            }
        }

        window.closeModal = function(modalId) {
            document.getElementById(modalId).style.display = 'none';
        }

        $(document).ready(function() {
            $('#schoolSelect').change(function() {
                var selectedSchool = $(this).val();
                if (selectedSchool) {
                    fetchPeriodeOptions(selectedSchool);
                } else {
                    $('#periodeSelect').html('<option value="">Pilih Periode</option>');
                }
            });

            function fetchPeriodeOptions(schoolName) {
                $.ajax({
                    url: 'fetch_periode.php',
                    type: 'POST',
                    data: {
                        school: schoolName
                    },
                    success: function(data) {
                        $('#periodeSelect').html(data);
                    },
                    error: function() {
                        Swal.fire({
                            title: 'Error!',
                            text: 'Gagal ambil data periode',
                            icon: 'error'
                        });
                    }
                });
            }

            $('#schoolSelect, #periodeSelect').change(function() {
                var selectedSchool = $('#schoolSelect').val();
                var selectedPeriode = $('#periodeSelect').val();

                if (selectedSchool || selectedPeriode) {
                    filterData(selectedSchool, selectedPeriode);
                } else {
                    // Jika kedua dropdown kosong, tampilkan semua data
                    location.reload();
                }
            });

            function filterData(school, periode) {
                $('.loading-indicator').show();
                $('.student-list').hide();

                $.ajax({
                    url: 'filter_students_pemb.php', // Buat file baru untuk handle filter
                    type: 'POST',
                    data: {
                        school: school,
                        periode: periode
                    },
                    success: function(data) {
                        $('.student-list').html(data).show();
                        $('.loading-indicator').hide();
                    },
                    error: function() {
                        $('.loading-indicator').hide();
                        Swal.fire({
                            title: 'Error!',
                            text: 'Gagal filter data',
                            icon: 'error'
                        });
                    }
                });
            }

            function fetchAttendanceData(studentName, month = null) {
                showLoading();

                fetchAttendanceTable(studentName, month);
                hideLoading();
            }



            $('.close, #attendanceDetailModal .close').click(function() {
                $(this).closest('.modal').css('display', 'none');

                if ($(this).closest('.modal').attr('id') === 'attendanceDetailModal') {
                    $('#attendanceInImage').attr('src', '');
                    $('#attendanceOutImage').attr('src', '');

                    if (window.mapIn) {
                        window.mapIn.remove();
                        window.mapIn = null;
                    }
                    if (window.mapOut) {
                        window.mapOut.remove();
                        window.mapOut = null;
                    }

                    $('#mapIn').html('');
                    $('#mapOut').html('');
                }
                $(this).closest('.modal-content').find('table tbody').html('');
                $(this).closest('.modal-content').find('#taskDescription').html('');
                $(this).closest('.modal-content').find('#studentName').text('');

                $('#attendanceMonth').val('');
            });

            $('body').on('click', 'a.view-details', function(event) {
                event.preventDefault();
                var name = $(this).data('name');
                var date = $(this).data('date');
                showAttendanceDetails(name, date);
            });

            function showAttendanceDetails(name, date) {
                $.ajax({
                    url: 'fetchAttendanceDetails.php',
                    type: 'POST',
                    data: {
                        name: name,
                        date: date
                    },
                    success: function(response) {
                        var data = JSON.parse(response);
                        $('#attendanceInImage').attr('src', data.in.img);
                        $('#attendanceOutImage').attr('src', data.out.img);

                        window.mapIn = L.map('mapIn').setView([data.in.lat, data.in.lng], 13);
                        window.mapOut = L.map('mapOut').setView([data.out.lat, data.out.lng], 13);

                        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                            attribution: '© OpenStreetMap contributors'
                        }).addTo(window.mapIn);
                        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                            attribution: '© OpenStreetMap contributors'
                        }).addTo(window.mapOut);

                        L.marker([data.in.lat, data.in.lng]).addTo(window.mapIn);
                        L.marker([data.out.lat, data.out.lng]).addTo(window.mapOut);

                        $('#attendanceDetailModal').css('display', 'block');
                    },
                    error: function() {
                        Swal.fire({
                            title: 'Error!',
                            text: 'Gagal ambil detail absensi',
                            icon: 'error'
                        });
                    }
                });
            }

            $('#attendanceMonth').change(function() {
                var studentName = $('#studentName').text();
                var selectedMonth = $(this).val();
                fetchAttendanceData(studentName, selectedMonth);
            });

            // Tambahkan event handler untuk tombol detail
            $('body').on('click', '.detail-btn', function() {
                var studentName = $(this).data('name');
                $('#studentName').text(studentName);
                $('#studentModal').css('display', 'block');
                fetchAttendanceData(studentName);
            });
        });

        // Login Layout Modal
        function openSettingsModal() {
            $('#settingsModal').css('display', 'block');
            $.ajax({
                url: 'get_settings.php',
                type: 'GET',
                success: function(response) {
                    const data = JSON.parse(response);
                    $('#first').val(data.first);
                    $('#second').val(data.second);
                    $('#description').val(data.description);
                },
                error: function() {
                    Swal.fire('Error', 'Gagal load data settings', 'error');
                }
            });
        }

        function closeSettingsModal() {
            $('#settingsModal').css('display', 'none');
        }

        $('#settingsForm').on('submit', function(e) {
            e.preventDefault();
            $.ajax({
                url: 'update_settings.php',
                type: 'POST',
                data: $(this).serialize(),
                success: function(response) {
                    const data = JSON.parse(response);
                    if (data.success) {
                        Swal.fire('Sukses', 'Settings berhasil diupdate', 'success');
                        closeSettingsModal();
                        location.reload();
                    } else {
                        Swal.fire('Error', 'Gagal update settings', 'error');
                    }
                },
                error: function() {
                    Swal.fire('Error', 'Gagal koneksi ke server', 'error');
                }
            });
        });

        // Admin Layout Modal
        function openEditAdminLayoutModal() {
            $('#editAdminLayoutModal').css('display', 'block');
            $.ajax({
                url: 'get_data.php',
                type: 'GET',
                data: {
                    id: 2
                },
                success: function(response) {
                    const data = JSON.parse(response);
                    $('#editAdminWelcome').val(data.first);
                    $('#editAdminInstruction').val(data.second);
                    $('#editAdminDescription').val(data.description);
                },
                error: function() {
                    Swal.fire('Error', 'Gagal load data admin layout', 'error');
                }
            });
        }

        function closeEditAdminLayoutModal() {
            $('#editAdminLayoutModal').css('display', 'none');
        }

        $('#editAdminLayoutForm').on('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            formData.append('id', 2);

            $.ajax({
                url: 'update_data.php',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    const data = JSON.parse(response);

                    if (data.success) {
                        Swal.fire('Sukses', 'Admin layout berhasil diupdate', 'success')
                            .then(() => {
                                closeEditAdminLayoutModal();
                                location.reload();
                            });
                    } else {
                        Swal.fire('Error', 'Gagal update admin layout', 'error');
                    }
                },
                error: function() {
                    Swal.fire('Error', 'Gagal koneksi ke server', 'error');
                }
            });
        });

        function showLoading() {
            $('#loadingOverlay').css('display', 'flex');
        }

        function hideLoading() {
            $('#loadingOverlay').css('display', 'none');
        }

        // Tambahkan fungsi ini di bagian JavaScript
        function fetchAttendanceTable(studentName, month = null) {
            $.ajax({
                url: 'fetch_attendance.php', // Ganti ke file baru
                type: 'POST',
                data: {
                    name: studentName,
                    month: month
                },
                success: function(response) {
                    $('#attendanceTable tbody').html(response);
                },
                error: function() {
                    Swal.fire({
                        title: 'Error!',
                        text: 'Gagal mengambil data absensi',
                        icon: 'error'
                    });
                }
            });
        }

        // Update bagian JavaScript untuk load tasks
        function loadTasks(username) {
            $.ajax({
                url: 'fetch_tugas.php',
                type: 'POST',
                data: {
                    user: username
                },
                success: function(response) {
                    const tasks = JSON.parse(response);
                    const taskItems = document.querySelector('.task-items');

                    if (tasks.length > 0) {
                        taskItems.innerHTML = tasks.map(task => `
                            <div class="task-item">
                                ${task.nama_tugas}
                            </div>
                        `).join('');
                    } else {
                        taskItems.innerHTML = '<div class="task-item">Belum ada tugas</div>';
                    }
                },
                error: function() {
                    Swal.fire('Error', 'Gagal load data tugas', 'error');
                }
            });
        }

        // Update fungsi beriTugas untuk load tasks ketika modal dibuka
        function beriTugas(nama) {
            // Redirect ke halaman tugas.php
            window.location.href = 'tugas.php';
        }

        function closeTaskModal() {
            document.getElementById('taskModal').style.display = 'none';
            document.getElementById('newTaskForm').reset();
        }

        // Update bagian event listener form submission
        document.getElementById('newTaskForm').addEventListener('submit', function(e) {
            e.preventDefault();
            showLoading();

            const taskData = {
                nama_tugas: document.getElementById('taskType').value,
                user: document.getElementById('selectedStudent').textContent
            };

            // Kirim data ke backend
            $.ajax({
                url: 'add_task.php',
                type: 'POST',
                data: taskData,
                success: function(response) {
                    hideLoading();
                    const result = JSON.parse(response);

                    if (result.success) {
                        Swal.fire({
                            title: 'Berhasil!',
                            text: 'Tugas berhasil ditambahkan',
                            icon: 'success'
                        }).then(() => {
                            closeTaskModal();
                            // Refresh list tugas
                            loadTasks(taskData.user);
                        });
                    } else {
                        Swal.fire({
                            title: 'Error!',
                            text: result.message || 'Gagal menambahkan tugas',
                            icon: 'error'
                        });
                    }
                },
                error: function() {
                    hideLoading();
                    Swal.fire({
                        title: 'Error!',
                        text: 'Terjadi kesalahan pada server',
                        icon: 'error'
                    });
                }
            });
        });

        // Tambahkan ini di bagian $(document).ready
        $(document).ready(function() {
            // Show content after page loads
            setTimeout(function() {
                $('.loader-container').fadeOut('slow');
                $('.content').fadeIn('slow');
            }, 1000);

            // Update fungsi showLoading dan hideLoading
            function showLoading() {
                $('.loader-container').fadeIn('fast');
            }

            function hideLoading() {
                $('.loader-container').fadeOut('fast');
            }
        });
    </script>
</body>

</html>