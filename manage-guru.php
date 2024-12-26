<?php
session_start();
require('koneksi.php');
require('auth.php');
require('navAdmin.php');

// Tambahkan query ini di bagian atas file setelah require statements
$pembimbingSql = "SELECT nama FROM akun WHERE role = '4' ORDER BY nama";
$pembimbingResult = $conn->query($pembimbingSql);
$pembimbingList = [];
while($row = $pembimbingResult->fetch_assoc()) {
    $pembimbingList[] = $row['nama'];
}

// Ambil data sekolah yang udah ada user-nya
$schoolSql = "SELECT DISTINCT asal_sekolah FROM akun WHERE role = '1' ORDER BY asal_sekolah";
$schoolResult = $conn->query($schoolSql);
$schools = [];
if ($schoolResult->num_rows > 0) {
    while ($schoolRow = $schoolResult->fetch_assoc()) {
        $schools[] = $schoolRow['asal_sekolah'];
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Persetujuan Magang</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <style>
        body {
            background-color: #ffffff;
            color: #333333;
            font-family: 'Poppins', sans-serif;
            margin: 0;
            padding: 0;
        }

        h2 {
            padding-top: 10px;
            color: #333;
            font-weight: 400;
            font-size: 20px;
            /* Update font size */
            letter-spacing: -0.5px;

        }

        .container {
            margin-top: 6rem;
            min-height: 80vh;
            padding: 15px 30px;
            max-width: 90% !important;
            min-width: 320px;
        }

        .info-box-wrapper {
            display: flex;
            margin-top: 3rem;
            flex-direction: column;
            align-items: center;
            width: 100%;
        }

        .info-box {
            margin-top: 6rem;
            padding: 20px;
            width: 100%;
            max-width: 1300px;
            background-color: #ffffff;
            border-radius: 12px;
            box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
        }

        .info-box h2 {
            margin-top: 10px;
            text-align: center;
            color: #484b6a;
            font-size: 24px;
            font-weight: 600;
        }

        .info-box table {
            width: 100%;
            border-collapse: collapse;
            background-color: #ffffff;
            border-radius: 8px;
            overflow: hidden;
            table-layout: auto;
        }

        .info-box th {
            padding: 10px;
            text-align: left;
            border-bottom: 1px solid #d2d3db;
            font-size: 14px;
        }

        .info-box td {
            padding: 10px;
            text-align: left;
            border-bottom: 1px solid #d2d3db;
            font-size: 12px;
        }

        .info-box th {
            background-color: rgba(0, 179, 142, 0.8);
            color: #ffffff;
        }

        .info-box tbody tr:hover {
            background-color: rgba(0, 179, 142, 0.2);
            transition: background-color 0.3s ease;
        }

        .info-box .no-data {
            text-align: center;
            padding: 20px;
            color: #757575;
        }

        .info-box .table-icon {
            cursor: pointer;
            color: #3333ff;
            transition: color 0.3s ease;
        }

        .info-box .table-icon:hover {
            color: #0000b3;
        }

        .add-btn {
            display: block;
            margin: 20px auto;
            padding: 10px 20px;
            background-color: #2D4B9A;
            color: #ffffff;
            text-align: center;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        .add-btn:hover {
            background-color: #1e3573;
            transform: translateY(-1px);
        }

        .modal {
            display: none;
            position: fixed;
            z-index: 99999;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0, 0, 0, 0.4);
        }

        .modal-content {
            background-color: #ffffff;
            margin: 3% auto;
            padding: 0;
            border: none;
            width: 90%;
            max-width: 800px;
            min-width: 300px;
            border-radius: 12px;
            overflow: hidden;
        }

        .modal-header {
            background-color: #2D4B9A;
            color: white;
            padding: 16px 24px;
            border-bottom: none;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .modal-header h2 {
            margin: 0;
            font-size: 18px;
            font-weight: 500;
        }

        .modal-body {
            padding: 24px;
            max-height: 80vh;
            overflow-y: auto;
        }

        .modal-body input,
        .modal-body textarea,
        .modal-body select {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            border: 1px solid #e0e0e0;
            border-radius: 5px;
            background-color: #f8f9fa;
            color: #484b6a;
            transition: border-color 0.3s ease, background-color 0.3s ease;
        }

        .modal-body input:focus,
        .modal-body textarea:focus,
        .modal-body select:focus {
            border-color: rgba(0, 179, 142, 0.8);
            background-color: #ffffff;
            outline: none;
        }

        .modal-body label {
            display: block;
            margin-top: 10px;
            color: #484b6a;
            font-weight: 500;
        }

        .close {
            color: #ffffff;
            font-size: 24px;
            font-weight: bold;
            cursor: pointer;
        }

        .close:hover {
            color: #e0e0e0;
        }

        .reset-btn {
            padding: 10px 14px;
            background-color: #2D4B9A;
            color: #ffffff;
            text-align: center;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 12px;
            transition: background-color 0.3s ease;
        }

        .reset-btn:hover {
            background-color: #1e3573;
            transform: translateY(-1px);
        }

        .search-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
            gap: 8px;
            max-width: 1200px;
        }

        .search-container select,
        .search-container input[type="text"] {
            flex: 1;
            padding: 10px;
            border: 1px solid #d2d3db;
            border-radius: 5px;
            font-size: 14px;
            min-width: 150px;
            max-width: 300px;
        }

        .search-container button {
            background-color: rgba(0, 179, 142, 0.8);
            color: white;
            border: none;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        .search-container button:hover {
            background-color: rgba(0, 179, 142, 1);
        }

        @media (max-width: 768px) {
            .container {
                padding: 10px;
            }

            .info-box {
                margin: 10px;
                padding: 15px;
            }

            .add-btn {
                width: 100%;
            }

            .modal-content {
                width: 90%;
            }
        }

        #editModal {
            z-index: 9999999;
        }

        .search-container input[type=text],
        .search-container select {
            padding: 10px;
            margin-top: 8px;
            margin-bottom: 8px;
            border: 1px solid #d2d3db;
            border-radius: 5px;
            background-color: #ffffff;
            color: #484b6a;
            font-size: 14px;
            transition: border-color 0.3s ease;
        }

        .swal2-container {
            z-index: 9999999 !important;
        }

        .highlight {
            background-color: rgba(45, 75, 154, 0.1);
            color: #2D4B9A;
            padding: 4px 8px;
            border-radius: 4px;
            font-weight: 500;
        }

        /* Styling buat modal detail */
        .detail-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0 10px;
        }

        .detail-table th,
        .detail-table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #e0e0e0;
        }

        .detail-table th {
            font-weight: bold;
            color: rgba(0, 179, 142, 1);
            width: 40%;
        }

        .detail-table td {
            background-color: #f9f9f9;
            border-radius: 4px;
        }

        #detailModal .modal-content {
            padding: 20px;
        }

        #detailModal .modal-body {
            margin-top: 20px;
            margin-bottom: 20px;
        }

        #detailModal .modal-footer {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
            padding-top: 20px;
            border-top: 1px solid #e0e0e0;
        }

        #detailModal button {
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        #updateBtn {
            background-color: rgba(0, 179, 142, 0.8);
            color: white;
        }

        #deleteBtn {
            background-color: rgba(0, 130, 204, 0.8);
            color: white;
        }

        #updateBtn:hover,
        #deleteBtn:hover {
            opacity: 0.8;
        }

        .data-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            background-color: #fff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
            font-size: 12px;
            table-layout: fixed;
            /* Tambahan ini buat bikin lebar kolom konsisten */
        }

        .data-table th,
        .data-table td {
            padding: 12px 16px;
            text-align: left;
            border-right: 1px solid #e0e0e0;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .data-table th {
            background-color: #2D4B9A;
            color: #ffffff;
            font-weight: 500;
            padding: 12px 16px;
            font-size: 13px;
        }

        .data-table td {
            padding: 12px 16px;
            font-size: 13px;
            color: #444;
        }

        .data-table tbody tr:nth-child(even) {
            background-color: #f8f9fa;
        }

        .data-table tbody tr:hover {
            background-color: rgba(0, 179, 142, 0.2);
            transition: background-color 0.3s ease;
        }

        /* Tambahin ini buat ngatur lebar tiap kolom */
        .data-table th:nth-child(1),
        .data-table td:nth-child(1) {
            width: 5%;
        }

        .data-table th:nth-child(2),
        .data-table td:nth-child(2) {
            width: 12%;
        }

        .data-table th:nth-child(3),
        .data-table td:nth-child(3) {
            width: 12%;
        }

        .data-table th:nth-child(4),
        .data-table td:nth-child(4) {
            width: 20%;
        }

        .data-table th:nth-child(5),
        .data-table td:nth-child(5) {
            width: 12%;
        }

        .data-table th:nth-child(6),
        .data-table td:nth-child(6) {
            width: 12%;
        }

        .data-table th:nth-child(7),
        .data-table td:nth-child(7) {
            width: 17%;
        }

        .data-table th:nth-child(8),
        .data-table td:nth-child(8) {
            width: 10%;
        }

        /* Button & Modal Styling */
        .modal-header {
            background-color: #2D4B9A;
            padding: 16px 24px;
        }

        .modal-header h2 {
            color: #ffffff;
            margin: 0;
            font-size: 18px;
            font-weight: 500;
        }

        .close {
            color: #ffffff;
            font-size: 24px;
            font-weight: bold;
            cursor: pointer;
            transition: opacity 0.2s ease;
        }

        .close:hover {
            opacity: 0.8;
        }

        /* Button Styling */
        .reset-btn,
        .add-btn,
        #updateBtn,
        #deleteBtn,
        .modal-body input[type="submit"],
        .search-container button {
            background-color: #2D4B9A;
            color: #ffffff;
            padding: 10px 20px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 14px;
            transition: all 0.2s ease;
        }

        .reset-btn:hover,
        .add-btn:hover,
        #updateBtn:hover,
        #deleteBtn:hover,
        .modal-body input[type="submit"]:hover,
        .search-container button:hover {
            background-color: #1e3573;
            transform: translateY(-1px);
            box-shadow: 0 2px 8px rgba(45, 75, 154, 0.2);
        }

        /* Modal Form Elements */
        .modal-body input,
        .modal-body textarea,
        .modal-body select {
            width: 100%;
            padding: 12px;
            margin: 8px 0;
            border: 1px solid #e0e0e0;
            border-radius: 6px;
            background-color: #f8f9fa;
            font-size: 14px;
            transition: all 0.2s ease;
        }

        .modal-body input:focus,
        .modal-body textarea:focus,
        .modal-body select:focus {
            border-color: #2D4B9A;
            box-shadow: 0 0 0 2px rgba(45, 75, 154, 0.1);
            outline: none;
        }

        .modal-body label {
            display: block;
            margin-top: 12px;
            color: #2D4B9A;
            font-weight: 500;
            font-size: 14px;
        }

        /* Detail Modal Footer */
        .modal-footer {
            padding: 16px 24px;
            border-top: 1px solid #e0e0e0;
            display: flex;
            justify-content: flex-end;
            gap: 12px;
        }

        /* SweetAlert Customization */
        .swal2-popup {
            border-radius: 12px !important;
        }

        .swal2-title {
            color: #2D4B9A !important;
        }

        .swal2-confirm {
            background-color: #2D4B9A !important;
        }

        /* Update font-family untuk semua elemen yang perlu */
        h2,
        .info-box h2,
        .modal-header h2,
        .modal-body input,
        .modal-body textarea,
        .modal-body select,
        .modal-body label,
        .search-container input[type=text],
        .search-container select,
        .data-table th,
        .data-table td,
        .reset-btn,
        .add-btn,
        #updateBtn,
        #deleteBtn,
        .modal-body input[type="submit"],
        .search-container button {
            font-family: 'Poppins', sans-serif;
        }

        /* Update table container & table styling */
        .table-container {
            width: 100%;
            overflow-x: auto;
            margin: 20px 0;
            padding-bottom: 10px;
        }

        .data-table {
            min-width: 1200px;
            width: 100%;
            margin: 0 auto;
        }

        .data-table th:nth-child(1),
        .data-table td:nth-child(1) {
            width: 4%;
        }

        .data-table th:nth-child(2),
        .data-table td:nth-child(2) {
            width: 12%;
        }

        .data-table th:nth-child(3),
        .data-table td:nth-child(3) {
            width: 12%;
        }

        .data-table th:nth-child(4),
        .data-table td:nth-child(4) {
            width: 18%;
        }

        .data-table th:nth-child(5),
        .data-table td:nth-child(5) {
            width: 10%;
        }

        .data-table th:nth-child(6),
        .data-table td:nth-child(6) {
            width: 10%;
        }

        .data-table th:nth-child(7),
        .data-table td:nth-child(7) {
            width: 15%;
        }

        .data-table th:nth-child(8),
        .data-table td:nth-child(8) {
            width: 12%;
        }

        .data-table th:nth-child(9),
        .data-table td:nth-child(9) {
            width: 7%;
        }

        .data-table th,
        .data-table td {
            padding: 12px 16px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            max-width: 0;
        }

        .data-table td:hover {
            white-space: normal;
            overflow: visible;
            position: relative;
            z-index: 1;
        }

        .pembimbing-select {
            width: 100%;
            min-width: 120px;
        }

        .container {
            max-width: 95%;
            margin: 6rem auto 2rem;
            padding: 0 15px;
        }

        .info-box {
            max-width: 100%;
            margin: 2rem auto;
        }
    </style>


</head>

<body>
    <div class="container">
        <div class="info-box-wrapper">
            <div class="info-box">
                <h2>Dashboard Persetujuan Magang</h2>


                <div class="search-container">
                    <select id="schoolDropdown">
                        <option value="">Pilih Sekolah</option>
                        <?php
                        foreach ($schools as $school) {
                            $selected = (strtolower($school) == strtolower($schoolFilter)) ? 'selected' : '';
                            echo "<option value='" . htmlspecialchars($school) . "' $selected>" . htmlspecialchars($school) . "</option>";
                        }
                        ?>
                    </select>
                    <select id="periodDropdown">
                        <option value="">Pilih Periode Magang</option>
                        <?php
                        $periodSql = "SELECT DISTINCT magang_masuk, magang_keluar FROM akun WHERE role = '1'";
                        $periodResult = $conn->query($periodSql);
                        if ($periodResult->num_rows > 0) {
                            while ($periodRow = $periodResult->fetch_assoc()) {
                                echo "<option value='" . htmlspecialchars($periodRow['magang_masuk']) . " to " . htmlspecialchars($periodRow['magang_keluar']) . "'>" . htmlspecialchars($periodRow['magang_masuk']) . " to " . htmlspecialchars($periodRow['magang_keluar']) . "</option>";
                            }
                        }
                        ?>
                    </select>
                    <input type="text" id="searchInput" placeholder="Cari nama atau sekolah...">
                    <button onclick="resetPage()" class="reset-btn">Reset</button>
                </div>


                <div class="table-container">
                    <table id="dataTable" class="data-table">
                        <thead>
                            <tr>
                                <th scope="col">NO</th>
                                <th scope="col">SURAT TUGAS</th>
                                <th scope="col">SURAT PERSETUJUAN</th>
                                <th scope="col">NAMA SEKOLAH</th>
                                <th scope="col">MAGANG MASUK</th>
                                <th scope="col">MAGANG KELUAR</th>
                                <th scope="col">NAMA GURU</th>
                                <th scope="col">PEMBIMBING</th>
                                <th scope="col">DETAIL</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $sql = "SELECT a1.no_surat, a1.no_surat_p, a1.asal_sekolah, a1.magang_masuk, a1.magang_keluar, 
                            GROUP_CONCAT(DISTINCT TRIM(a2.nama) SEPARATOR ', ') as nama_guru,
                            a1.pembimbing
                            FROM akun a1
                            LEFT JOIN akun a2 ON a1.asal_sekolah = a2.asal_sekolah 
                                            AND a2.role = '3'
                                            AND a2.magang_masuk = a1.magang_masuk
                                            AND a2.magang_keluar = a1.magang_keluar
                            WHERE a1.role = '1'
                            GROUP BY a1.asal_sekolah, a1.magang_masuk, a1.magang_keluar, a1.no_surat, a1.no_surat_p";
                            $result = $conn->query($sql);

                            if ($result->num_rows > 0) {
                                $key = 1;
                                while ($row = $result->fetch_assoc()) {
                                    echo "<tr>";
                                    echo "<th scope='row'>" . $key++ . "</th>";
                                    echo "<td>";
                                    if (empty($row['no_surat'])) {
                                        echo "<span class='highlight'>Belum ada nomor</span>";
                                    } else {
                                        echo "<a href='show-surat-tugas.php?sekolah=" . urlencode($row['asal_sekolah']) . "&masuk=" . urlencode($row['magang_masuk']) . "&keluar=" . urlencode($row['magang_keluar']) . "' target='_blank'>" . htmlspecialchars($row['no_surat']) . "</a>";
                                    }
                                    echo "</td>";
                                    echo "<td>";
                                    if (empty($row['no_surat_p'])) {
                                        echo "<span class='highlight' onclick='openPersetujuanModal(\"" . $row['asal_sekolah'] . "\", \"" . $row['magang_masuk'] . "\", \"" . $row['magang_keluar'] . "\")'>Belum ada nomor</span>";
                                    } else {
                                        echo "<a href='show-surat-persetujuan.php?sekolah=" . urlencode($row['asal_sekolah']) . "&masuk=" . urlencode($row['magang_masuk']) . "&keluar=" . urlencode($row['magang_keluar']) . "' target='_blank'>" . htmlspecialchars($row['no_surat_p']) . "</a>";
                                    }
                                    echo "</td>";
                                    echo "<td>" . htmlspecialchars($row['asal_sekolah']) . "</td>";
                                    echo "<td>" . htmlspecialchars($row['magang_masuk']) . "</td>";
                                    echo "<td>" . htmlspecialchars($row['magang_keluar']) . "</td>";
                                    echo "<td>" . (empty($row['nama_guru']) ? "<span class='highlight'>Belum ada data guru</span>" : htmlspecialchars(trim($row['nama_guru']))) . "</td>";
                                    echo "<td>
                                            <select class='pembimbing-select' 
                                                    data-sekolah='" . htmlspecialchars($row['asal_sekolah']) . "'
                                                    data-masuk='" . htmlspecialchars($row['magang_masuk']) . "'
                                                    data-keluar='" . htmlspecialchars($row['magang_keluar']) . "'>
                                                <option value=''>Pilih Pembimbing</option>";
                                                foreach($pembimbingList as $pembimbing) {
                                                    $selected = ($pembimbing == $row['pembimbing']) ? 'selected' : '';
                                                    echo "<option value='" . htmlspecialchars($pembimbing) . "' $selected>" . 
                                                         htmlspecialchars($pembimbing) . "</option>";
                                                }
                                            echo "</select></td>";
                                    echo "<td>" . (empty($row['nama_guru']) ? "<a href='auth-admin.php' class='highlight'>Tambah data guru</a>" : "<a href='#' class='detail-link' data-nama='" . htmlspecialchars($row['nama_guru'], ENT_QUOTES) . "'>Detail</a>") . "</td>";
                                    echo "</tr>";
                                }
                            } else {
                                echo "<tr><td colspan='8' class='no-data highlight'>Belum ada data nih</td></tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div id="editModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <span class="close">&times;</span>
                <h2>Update Data Guru</h2>
            </div>
            <div class="modal-body">
                <form id="editForm" action="update-guru.php" method="post">
                    <input type="text" id="editNama" name="nama" readonly>
                    <label for="editNik" No.Identitas:</label>
                        <input type="text" id="editNik" name="editNik" required>
                        <label for="editNoTelp">No Handphone:</label>
                        <input type="text" id="editNoTelp" name="editNoTelp" required>
                        <label for="editEmail">Email:</label>
                        <input type="email" id="editEmail" name="editEmail" required>
                        <label for="editAsal">Asal Sekolah:</label>
                        <input type="text" id="editAsal" name="editAsal" readonly>
                        <input type="submit" class="reset-btn" value="Update">
                </form>
            </div>
        </div>
    </div>

    <div id="detailModal" class="modal">
        <div class="modal-content" style="max-width: 500px;">
            <div class="modal-header">
                <span class="close">&times;</span>
                <h2 class="sekolah">Detail Data Guru</h2>
            </div>
            <div class="modal-body">
                <div id="detailData"></div>
            </div>
            <div class="modal-footer">
                <button id="updateBtn">Update</button>
                <button id="deleteBtn">Delete</button>
            </div>
        </div>

    </div>

    <div id="persetujuanModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <span class="close">&times;</span>
                <h2>Input Surat Persetujuan</h2>
            </div>
            <div class="modal-body">
                <form id="persetujuanForm" enctype="multipart/form-data">
                    <input type="hidden" id="persetujuanSekolah" name="sekolah">
                    <input type="hidden" id="persetujuanMasuk" name="masuk">
                    <input type="hidden" id="persetujuanKeluar" name="keluar">
                    <label for="noSuratP">Nomor Surat Persetujuan:</label>
                    <input type="text" id="noSuratP" name="no_surat_p" required>
                    <label for="fileSuratP">Upload Surat Persetujuan (PDF only, max 5MB):</label>
                    <input type="file" id="fileSuratP" name="surat_persetujuan" accept=".pdf" required>
                    <input type="submit" class="reset-btn" value="Simpan">
                </form>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const detailModal = document.getElementById('detailModal');
            const editModal = document.getElementById('editModal');
            const closeBtns = document.querySelectorAll('.close');

            // Fungsi buat nutup semua modal
            function closeAllModals() {
                const modals = document.querySelectorAll('.modal');
                modals.forEach(modal => {
                    modal.style.display = "none";
                });
            }

            // Event listener buat semua tombol close
            document.querySelectorAll('.close').forEach(closeBtn => {
                closeBtn.addEventListener('click', closeAllModals);
            });

            // Event listener buat klik di luar modal
            window.addEventListener('click', function(event) {
                if (event.target.classList.contains('modal')) {
                    closeAllModals();
                }
            });

            const editForm = document.getElementById('editForm');

            // Tambahin style buat SweetAlert
            const style = document.createElement('style');
            style.textContent = `
                .swal2-container {
                    z-index: 9999999 !important;
                }
            `;
            document.head.appendChild(style);

            if (editForm) {
                editForm.addEventListener('submit', function(event) {
                    event.preventDefault();
                    const formData = new FormData(this);

                    fetch('update-guru.php', {
                            method: 'POST',
                            body: formData
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.status === 'success') {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Mantap!',
                                    text: data.message,
                                    showConfirmButton: false,
                                    timer: 1500,
                                    customClass: {
                                        container: 'swal-on-top'
                                    }
                                }).then(() => {
                                    window.location.reload();
                                });
                            } else {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Oops...',
                                    text: data.message,
                                    customClass: {
                                        container: 'swal-on-top'
                                    }
                                });
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            Swal.fire({
                                icon: 'error',
                                title: 'Waduh!',
                                text: 'Ada error nih. Coba lagi yuk! Error: ' + error,
                                customClass: {
                                    container: 'swal-on-top'
                                }
                            });
                        });
                });
            } else {
                console.error('Form dengan id "editForm" ga ketemu nih');
            }

            // Fungsi untuk membuka modal edit
            window.openEditModal = function(data) {
                document.getElementById('editNama').value = data.nama;
                document.getElementById('editNik').value = data.nik;
                document.getElementById('editNoTelp').value = data.no_telp;
                document.getElementById('editEmail').value = data.gmail;
                document.getElementById('editAsal').value = data.asal_sekolah;

                editModal.style.display = "block";
            }

            document.getElementById('persetujuanForm').addEventListener('submit', function(e) {
                e.preventDefault();
                let formData = new FormData(this);

                // Validasi file
                let fileInput = document.getElementById('fileSuratP');
                let file = fileInput.files[0];
                if (file) {
                    if (file.type !== 'application/pdf') {
                        Swal.fire({
                            icon: 'error',
                            title: 'Waduh!',
                            text: 'File harus PDF ya bro!'
                        });
                        return;
                    }
                    if (file.size > 5 * 1024 * 1024) { // 5MB
                        Swal.fire({
                            icon: 'error',
                            title: 'Kegedean bro!',
                            text: 'File max 5MB ya!'
                        });
                        return;
                    }
                }

                fetch('update-surat-persetujuan.php', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.status === 'success') {
                            Swal.fire({
                                icon: 'success',
                                title: 'Mantap!',
                                text: data.message,
                                showConfirmButton: false,
                                timer: 1500
                            }).then(() => {
                                location.reload();
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Oops...',
                                text: data.message
                            });
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        Swal.fire({
                            icon: 'error',
                            title: 'Waduh!',
                            text: 'Ada error nih. Coba lagi yuk!'
                        });
                    });
            });

            function openPersetujuanModal(sekolah, masuk, keluar) {
                document.getElementById('persetujuanSekolah').value = sekolah;
                document.getElementById('persetujuanMasuk').value = masuk;
                document.getElementById('persetujuanKeluar').value = keluar;
                document.getElementById('persetujuanModal').style.display = 'block';
            }

            // Tambah event listener buat semua link detail
            document.querySelectorAll('.detail-link').forEach(link => {
                link.addEventListener('click', function(e) {
                    e.preventDefault();
                    const nama = this.getAttribute('data-nama');
                    openDetailModal(nama);
                });
            });

            // Event listener untuk pembimbing dropdown
            document.querySelectorAll('.pembimbing-select').forEach(select => {
                select.addEventListener('change', function() {
                    const sekolah = this.dataset.sekolah;
                    const masuk = this.dataset.masuk;
                    const keluar = this.dataset.keluar;
                    const pembimbing = this.value;

                    fetch('update-guru-pembimbing.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: `sekolah=${encodeURIComponent(sekolah)}&masuk=${encodeURIComponent(masuk)}&keluar=${encodeURIComponent(keluar)}&pembimbing=${encodeURIComponent(pembimbing)}`
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.status === 'success') {
                            Swal.fire({
                                icon: 'success',
                                title: 'Mantap!',
                                text: 'Pembimbing berhasil diupdate',
                                showConfirmButton: false,
                                timer: 1500
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Waduh!',
                                text: 'Gagal update pembimbing nih'
                            });
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        Swal.fire({
                            icon: 'error',
                            title: 'Waduh!',
                            text: 'Ada error nih. Coba lagi yuk!'
                        });
                    });
                });
            });
        });

        let timer;

        document.getElementById('schoolDropdown').addEventListener('change', debounceSearch);
        document.getElementById('periodDropdown').addEventListener('change', debounceSearch);
        document.getElementById('searchInput').addEventListener('input', debounceSearch);

        function debounceSearch() {
            clearTimeout(timer);
            timer = setTimeout(applyFilters, 300);
        }

        function applyFilters() {
            const school = document.getElementById('schoolDropdown').value;
            const period = document.getElementById('periodDropdown').value;
            const search = document.getElementById('searchInput').value;

            const xhr = new XMLHttpRequest();
            xhr.open('GET', `get-filtered-guru-data.php?school=${encodeURIComponent(school)}&period=${encodeURIComponent(period)}&search=${encodeURIComponent(search)}`, true);

            xhr.onload = function() {
                if (this.status === 200) {
                    document.querySelector('#dataTable tbody').innerHTML = this.responseText;
                }
            };

            xhr.send();
        }

        function resetPage() {
            location.reload();
        }

        function confirmDelete(nama) {
            Swal.fire({
                title: 'Yakin nih?',
                text: `Kamu beneran mau hapus data guru ${nama}?`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Ya, hapus aja!',
                cancelButtonText: 'Gak jadi deh'
            }).then((result) => {
                if (result.isConfirmed) {
                    deleteGuru(nama);
                }
            });
        }

        function deleteGuru(nama) {
            fetch('delete-guru.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `nama=${encodeURIComponent(nama)}`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        Swal.fire({
                            icon: 'success',
                            title: 'Mantap!',
                            text: data.message,
                            showConfirmButton: false,
                            timer: 1500
                        }).then(() => {
                            location.reload();
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Oops...',
                            text: data.message
                        });
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    Swal.fire({
                        icon: 'error',
                        title: 'Waduh!',
                        text: 'Ada error nih. Coba lagi yuk!'
                    });
                });
        }

        function openDetailModal(nama) {
            const xhr = new XMLHttpRequest();
            xhr.open("GET", "get-guru-details.php?nama=" + encodeURIComponent(nama.trim()), true);
            xhr.onload = function() {
                if (this.status === 200) {
                    try {
                        const response = JSON.parse(this.responseText);
                        if (response.error) {
                            document.getElementById('detailData').innerHTML = `
                                <p class="highlight">${response.error}</p>
                                <p>Silakan <a href='auth-admin.php'>tambah data guru</a> terlebih dahulu.</p>
                            `;
                        } else {
                            const detailData = `
                                <table class="detail-table">
                                    <tr><th>Nama</th><td>${response.nama || '<span class="highlight">Belum ada data</span>'}</td></tr>
                                    <tr><th>No.Identitas</th><td>${response.nik || '<span class="highlight">Belum ada data</span>'}</td></tr>
                                    <tr><th>Email</th><td>${response.gmail || '<span class="highlight">Belum ada data</span>'}</td></tr>
                                    <tr><th>No Telp</th><td>${response.no_telp || '<span class="highlight">Belum ada data</span>'}</td></tr>
                                    <tr><th>Asal Sekolah</th><td>${response.asal_sekolah || '<span class="highlight">Belum ada data</span>'}</td></tr>
                                </table>
                            `;
                            document.getElementById('detailData').innerHTML = detailData;
                        }
                        document.getElementById('detailModal').style.display = 'block';

                        // Set up close button
                        document.querySelector('#detailModal .close').onclick = function() {
                            document.getElementById('detailModal').style.display = "none";
                        }

                        // Set up update and delete buttons only if data exists
                        if (!response.error) {
                            document.getElementById('updateBtn').style.display = 'inline-block';
                            document.getElementById('deleteBtn').style.display = 'inline-block';
                            document.getElementById('updateBtn').onclick = function() {
                                openEditModal(response);
                            };
                            document.getElementById('deleteBtn').onclick = function() {
                                confirmDelete(response.nama);
                            };
                        } else {
                            document.getElementById('updateBtn').style.display = 'none';
                            document.getElementById('deleteBtn').style.display = 'none';
                        }
                    } catch (e) {
                        console.error("Error parsing JSON:", e);
                        document.getElementById('detailData').innerHTML = "<p class='highlight'>Waduh, ada masalah saat memproses data. Coba lagi ya!</p>";
                    }
                } else {
                    document.getElementById('detailData').innerHTML = "<p class='highlight'>Waduh, gagal ambil data nih. Status: " + this.status + "</p>";
                }
            };
            xhr.onerror = function() {
                document.getElementById('detailData').innerHTML = "<p class='highlight'>Waduh, ada error network nih. Cek koneksi lo ya!</p>";
            };
            xhr.send();
        }

        function openPersetujuanModal(sekolah, masuk, keluar) {
            document.getElementById('persetujuanSekolah').value = sekolah;
            document.getElementById('persetujuanMasuk').value = masuk;
            document.getElementById('persetujuanKeluar').value = keluar;
            document.getElementById('persetujuanModal').style.display = 'block';
        }

        document.getElementById('persetujuanForm').addEventListener('submit', function(e) {
            e.preventDefault();
            let formData = new FormData(this);

            // Validasi file
            let fileInput = document.getElementById('fileSuratP');
            let file = fileInput.files[0];
            if (file) {
                if (file.type !== 'application/pdf') {
                    Swal.fire({
                        icon: 'error',
                        title: 'Waduh!',
                        text: 'File harus PDF ya bro!'
                    });
                    return;
                }
                if (file.size > 5 * 1024 * 1024) { // 5MB
                    Swal.fire({
                        icon: 'error',
                        title: 'Kegedean bro!',
                        text: 'File max 5MB ya!'
                    });
                    return;
                }
            }

            fetch('update-surat-persetujuan.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        Swal.fire({
                            icon: 'success',
                            title: 'Mantap!',
                            text: data.message,
                            showConfirmButton: false,
                            timer: 1500
                        }).then(() => {
                            location.reload();
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Oops...',
                            text: data.message
                        });
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    Swal.fire({
                        icon: 'error',
                        title: 'Waduh!',
                        text: 'Ada error nih. Coba lagi yuk!'
                    });
                });
        });

        // Event listener buat close button di modal persetujuan
        document.querySelector('#persetujuanModal .close').onclick = function() {
            document.getElementById('persetujuanModal').style.display = "none";
        }

        // Nambahin event listener buat nutup modal kalo user klik di luar modal
        window.onclick = function(event) {
            if (event.target == document.getElementById('persetujuanModal')) {
                document.getElementById('persetujuanModal').style.display = "none";
            }
        }
    </script>
</body>

</html>