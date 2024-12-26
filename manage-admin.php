<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
require('koneksi.php');
require('auth.php');

// Di bagian awal file, tambahkan query untuk ambil data pembimbing
$pembimbingQuery = "SELECT nama FROM akun WHERE role = '4' ORDER BY nama";
$pembimbingResult = $conn->query($pembimbingQuery);
$pembimbingList = [];
while($row = $pembimbingResult->fetch_assoc()) {
    $pembimbingList[] = $row['nama'];
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update'])) {
    header('Content-Type: application/json');

    $nama = $_POST['nama'];
    $tanggal_lahir = $_POST['editTanggal'];
    $nis = $_POST['editNis'];
    $nik = $_POST['editNik'];
    $no_telp = $_POST['editNoTelp'];
    $gmail = $_POST['editEmail'];
    $asal_sekolah = $_POST['editAsal'];
    $alamat_sekolah = $_POST['editAlamat'];
    $guru_pendamping = $_POST['editGuru'];
    $magang_masuk = $_POST['editMasuk'];
    $magang_keluar = $_POST['editKeluar'];
    $no_telp_guru = $_POST['editNoTelpGuru'];

    $sql = "UPDATE akun SET 
            tanggal_lahir = ?, 
            nis = ?, 
            nik = ?, 
            no_telp = ?, 
            gmail = ?, 
            asal_sekolah = ?, 
            alamat_sekolah = ?, 
            guru_pendamping = ?, 
            magang_masuk = ?, 
            magang_keluar = ?, 
            no_telp_guru = ?
            WHERE nama = ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssssssssss", $tanggal_lahir, $nis, $nik, $no_telp, $gmail, $asal_sekolah, $alamat_sekolah, $guru_pendamping, $magang_masuk, $magang_keluar, $no_telp_guru, $nama);

    if ($stmt->execute()) {
        echo json_encode(['status' => 'success', 'message' => 'Mantap, data berhasil diperbarui nih!']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Waduh, gagal memperbarui data nih. Coba lagi yuk!']);
    }

    $stmt->close();
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_surat'])) {
    $no_surat = $_POST['no_surat'];
    $sekolah = $_POST['sekolah'];
    $masuk = $_POST['masuk'];
    $keluar = $_POST['keluar'];

    $updateSql = "UPDATE akun SET no_surat = ? WHERE asal_sekolah = ? AND magang_masuk = ? AND magang_keluar = ?";
    $stmt = $conn->prepare($updateSql);
    $stmt->bind_param("ssss", $no_surat, $sekolah, $masuk, $keluar);

    if ($stmt->execute()) {
        echo json_encode(['status' => 'success', 'message' => 'Nomor surat berhasil diupdate!']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Gagal update nomor surat.']);
    }
    exit;
}

require('navAdmin.php');

// Tambahkan ini di bagian atas file, setelah koneksi database
$schoolSql = "SELECT DISTINCT asal_sekolah FROM akun WHERE role = '1' ORDER BY asal_sekolah";
$schoolResult = $conn->query($schoolSql);
$schools = [];
if ($schoolResult->num_rows > 0) {
    while ($schoolRow = $schoolResult->fetch_assoc()) {
        $schools[] = $schoolRow['asal_sekolah'];
    }
}

// Inisialisasi variabel filter
$schoolFilter = isset($_GET['school']) ? $_GET['school'] : '';
$periodFilter = isset($_GET['period']) ? $_GET['period'] : '';
$searchFilter = isset($_GET['search']) ? $_GET['search'] : '';

// Buat query dasar
$sql = "SELECT asal_sekolah, magang_masuk, magang_keluar, nama, nis, no_surat, pembimbing,
        COUNT(*) OVER (PARTITION BY asal_sekolah, magang_masuk, magang_keluar) as group_count
        FROM akun 
        WHERE role = '1'";

// Tambahkan filter ke query
if (!empty($schoolFilter)) {
    $sql .= " AND LOWER(asal_sekolah) LIKE LOWER('%" . $conn->real_escape_string($schoolFilter) . "%')";
}
if (!empty($periodFilter)) {
    list($start, $end) = explode(" to ", $periodFilter);
    $sql .= " AND magang_masuk = '" . $conn->real_escape_string($start) . "' AND magang_keluar = '" . $conn->real_escape_string($end) . "'";
}
if (!empty($searchFilter)) {
    $sql .= " AND (LOWER(nama) LIKE LOWER('%" . $conn->real_escape_string($searchFilter) . "%') 
              OR nis LIKE '%" . $conn->real_escape_string($searchFilter) . "%'
              OR LOWER(asal_sekolah) LIKE LOWER('%" . $conn->real_escape_string($searchFilter) . "%')
              OR LOWER(no_surat) LIKE LOWER('%" . $conn->real_escape_string($searchFilter) . "%'))";
}

// Tambahkan ordering
$sql .= " ORDER BY asal_sekolah, magang_masuk, magang_keluar, nama";

$result = $conn->query($sql);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nama = $_POST['nama'];

    // Query untuk hapus data
    $sql = "DELETE FROM akun WHERE nama=?";

    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("s", $nama);
        if ($stmt->execute()) {
            echo "Data berhasil dihapus";
        } else {
            echo "Ups, ada kesalahan saat menghapus data: " . $conn->error;
        }
    }

    $conn->close();

    header("Location: manage-admin.php"); // Redirect balik ke halaman utama setelah hapus
    exit();
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Siswa</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">

    <style>
    body {
        background-color: #ffffff;
        color: #333;
        font-family: 'Poppins', sans-serif;
        margin: 0;
        padding: 0;
    }

    .container {
        margin-top: 6rem;
        max-width: 90%;
        min-width: 320px;
        margin-left: auto;
        margin-right: auto;
        background-color: #ffffff;
        padding: 30px;
        border-radius: 15px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        margin-bottom: 10rem;
    }

    .info-box {
        background-color: #ffffff;
        border-radius: 8px;
        padding: 18px;
        box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
    }

    .info-box h2 {
        margin-top: 10px;
        text-align: center;
        color: #484b6a;
        font-size: 24px;
        font-weight: 600;
    }

    .data-table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0;
        margin-top: 16px;
        border-radius: 8px;
    }

    .data-table th {
        background-color: #005B94;
        color: #fff;
        font-weight: 500;
        padding: 8px 12px;
        text-transform: none;
        font-size: 13px;
    }

    .data-table td {
        padding: 8px 12px;
        font-size: 13px;
        border-bottom: 1px solid #edf2f7;
    }

    .data-table tbody tr:hover {
        background-color: rgba(0, 91, 148, 0.1);
    }

    .data-table th:last-child,
    .data-table td:last-child {
        border-right: none;
    }

    .data-table thead {
        background-color: rgba(0, 179, 142, 1);
        color: #ffffff;
        font-size: 13px;
    }

    .data-table tbody {
        font-size: 12px;
    }

    .data-table th {
        font-weight: 600;
        text-transform: uppercase;
        font-size: 14px;
        letter-spacing: 0.5px;
        border-bottom: 2px solid rgba(0, 130, 204, 0.7);
    }

    .data-table tbody tr:nth-child(even) {
        background-color: #f8f9fa;
    }

    .data-table tbody tr:hover {
        background-color: rgba(0, 179, 142, 0.1);
        transition: background-color 0.3s ease;
    }

    .data-table td {
        border-top: 1px solid #e9ecef;
    }

    .data-table a {
        color: #005B94;
        text-decoration: none;
        transition: color 0.3s ease;
    }

    .data-table a:hover {
        color: #004875;
        text-decoration: underline;
    }

    .no-data {
        text-align: center;
        padding: 20px;
        color: #6c757d;
        font-style: italic;
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

    .reset-btn,
    .add-btn {
        background-color: #005B94;
        color: white;
        border: none;
        padding: 8px 12px;
        border-radius: 6px;
        font-family: 'Poppins', sans-serif;
        font-size: 13px;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.2s;
    }

    .reset-btn:hover,
    .add-btn:hover {
        background-color: #004875;
        transform: translateY(-1px);
    }

    .modal {
        display: none;
        position: fixed;
        z-index: 9999;
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
        width: 80%;
        min-width: 300px;
        max-width: 1200px;
        max-height: 90vh;
        border-radius: 10px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
        overflow: hidden;
    }

    .modal-header {
        background-color: #005B94;
        color: white;
        padding: 15px;
        border-bottom: none;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .modal-header h2 {
        margin: 0;
        font-size: 16px;
        font-weight: 500;
        color: white;
    }

    .modal-body {
        padding: 15px;
        max-height: 80vh;
        overflow-y: auto;
    }

    .modal-body input,
    .modal-body textarea,
    .modal-body select {
        width: 100%;
        padding: 8px;
        margin: 8px 0;
        border: 1px solid #e0e0e0;
        border-radius: 5px;
        background-color: #f8f9fa;
        color: #484b6a;
        transition: border-color 0.3s ease, background-color 0.3s ease;
    }

    .modal-body input:focus,
    .modal-body textarea:focus,
    .modal-body select:focus {
        border-color: #005B94;
        background-color: #ffffff;
        outline: none;
    }

    .modal-body label {
        display: block;
        margin-top: 8px;
        color: #484b6a;
        font-weight: 500;
    }

    .modal-footer {
        background-color: #f8f9fa;
        padding: 20px;
        border-top: none;
        text-align: right;
    }

    .modal-footer button {
        padding: 8px 12px;
        border: none;
        border-radius: 5px;
        cursor: pointer;
        font-size: 13px;
        transition: background-color 0.3s ease;
    }


    #updateBtn {
        background-color: #005B94;
        color: white;
    }

    #deleteBtn {
        background-color: #C41E3A;
        color: white;
    }

    #updateBtn:hover,
    #deleteBtn:hover {
        opacity: 0.8;
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

    h2 {
        color: #333;
        font-weight: 400;
        font-size: 20px;
        letter-spacing: -0.5px;
        padding-bottom: 5px;
    }

    .search-container {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 15px;
        width: 100%;
        gap: 8px;
    }

    .search-container select,
    .search-container input[type="text"] {
        flex: 1;
        padding: 8px 12px;
        border: 1px solid #e2e8f0;
        border-radius: 6px;
        font-family: 'Poppins', sans-serif;
        font-size: 13px;
    }

    .search-container select:focus,
    .search-container input[type="text"]:focus {
        border-color: #005B94;
        outline: none;
        box-shadow: 0 0 0 3px rgba(0, 91, 148, 0.1);
    }

    .search-container button {
        background-color: #005B94;
        color: white;
        border: none;
        cursor: pointer;
        transition: background-color 0.3s ease;
    }

    .search-container button:hover {
        background-color: #004875;
    }

    .table-container {
        width: 100%;
        overflow-x: auto;
        border-radius: 8px;
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
            width: 95%;
            margin: 2% auto;
        }
    }

    #editModal {
        z-index: 2000;
        /* Nilai z-index yang lebih tinggi */
    }

    #detailModal {
        z-index: 1999;
        /* Nilai z-index yang lebih rendah dari editModal */
    }

    .swal2-container {
        z-index: 9999999999999 !important;
    }

    .sekolah {
        padding-top: 15px;
        text-align: center;
        color: white;
    }

    .detail-container {
        display: flex;
        gap: 20px;
    }

    .photo-frame {
        width: 200px;
        height: 200px;
        border: 2px solid #ddd;
        border-radius: 10px;
        overflow: hidden;
        display: flex;
        justify-content: center;
        align-items: center;
    }

    .photo-frame img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .no-photo {
        color: #999;
        text-align: center;
    }

    .tables-container {
        flex: 1;
        display: flex;
        justify-content: space-between;
        gap: 30px;
    }

    .detail-table {
        width: 48%;
        border-collapse: collapse;
        margin-top: 10px;
    }

    .detail-table th,
    .detail-table td {
        padding: 6px;
        text-align: left;
        border-bottom: 1px solid #ddd;
        border-radius: 8px;
    }

    .detail-table th {
        width: 40%;
        font-weight: bold;
        color: #333;
        border-radius: 8px;
    }

    .detail-table tr:nth-child(even) {
        background-color: #f2f2f2;
    }

    .detail-table tr:hover {
        background-color: #e6e6e6;
    }

    @media (max-width: 1024px) {
        .modal-content {
            width: 98%;
            margin: 2% auto;
        }
    }

    @media (max-width: 768px) {
        .detail-container {
            flex-direction: column;
            align-items: center;
        }

        .tables-container {
            flex-direction: column;
            width: 100%;
        }

        .detail-table {
            width: 100%;
        }

        .photo-frame {
            width: 200px;
            height: 200px;
        }
    }

    .modal-body input[readonly] {
        background-color: #e9ecef;
        cursor: not-allowed;
    }

    /* Add Poppins font */
    @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap');

    /* Styling untuk button update di form */
    .modal-body input[type="submit"].reset-btn {
        background-color: #005B94;
        color: white;
        padding: 8px 16px;
        border: none;
        border-radius: 6px;
        cursor: pointer;
        font-weight: 500;
        width: 100%;
        margin-top: 15px;
        transition: all 0.3s ease;
    }

    .modal-body input[type="submit"].reset-btn:hover {
        background-color: white;
        color: #005B94;
        border: 1px solid #005B94;
        transform: translateY(-1px);
        box-shadow: 0 2px 4px rgba(0, 91, 148, 0.2);
    }

    /* Update container width */
    .container {
        margin-top: 6rem;
        max-width: 90%;
        min-width: 320px;
        margin-left: auto;
        margin-right: auto;
    }

    /* Update table dimensions */
    .table-container {
        min-height: 200px;
        max-height: 70vh;
    }

    /* Update modal content size */
    .modal-content {
        width: 80%;
        min-width: 300px;
        max-width: 1200px;
        max-height: 90vh;
    }

    /* Update detail modal layout */
    .detail-container {
        min-height: 300px;
        max-height: 70vh;
    }

    .photo-frame {
        min-width: 180px;
        min-height: 180px;
        max-width: 250px;
        max-height: 250px;
    }

    /* Update form elements */
    .modal-body input,
    .modal-body textarea,
    .modal-body select {
        min-height: 36px;
        max-height: 100px;
    }

    /* Update button sizes */
    .reset-btn,
    .add-btn {
        min-width: 100px;
        min-height: 36px;
        max-width: 200px;
    }

    /* Update search container */
    .search-container select,
    .search-container input[type="text"] {
        min-width: 150px;
        min-height: 36px;
    }

    /* Responsive adjustments */
    @media (max-width: 768px) {
        .container {
            max-width: 95%;
            margin-top: 4rem;
        }

        .modal-content {
            width: 95%;
            margin: 2% auto;
        }

        .photo-frame {
            min-width: 150px;
            min-height: 150px;
        }
    }
    </style>


</head>

<body>
    <div class="container">
        <div class="info-box-wrapper">
            <div class="info-box">
                <h2>Data Siswa</h2>
                <div class="search-container">
                    <button id="addSchoolBtn" class="reset-btn">Tambah Sekolah</button>
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
                        // Query untuk mengambil periode magang unik
                        $periodSql = "SELECT DISTINCT magang_masuk, magang_keluar FROM akun WHERE role = '1'";
                        $periodResult = $conn->query($periodSql);
                        if ($periodResult->num_rows > 0) {
                            while ($periodRow = $periodResult->fetch_assoc()) {
                                echo "<option value='" . htmlspecialchars($periodRow['magang_masuk']) . " to " . htmlspecialchars($periodRow['magang_keluar']) . "'>" . htmlspecialchars($periodRow['magang_masuk']) . " to " . htmlspecialchars($periodRow['magang_keluar']) . "</option>";
                            }
                        }
                        ?>
                    </select>
                    <input type="text" id="searchInput" placeholder="Search nama, NIS, sekolah, atau nomor surat...">

                    <button onclick="resetPage()" class="reset-btn">Reset</button>
                    <button class="reset-btn" id="openModal" onclick="window.location.href='tambah-murid.php'">Tambah
                        Murid</button>
                </div>
                <div class="table-container">
                    <table id="dataTable" class="data-table">
                        <thead>
                            <tr>
                                <th scope="col">NO</th>
                                <th scope="col">SURAT TUGAS</th>
                                <th scope="col">ASAL SEKOLAH</th>
                                <th scope="col">NAMA</th>
                                <th scope="col">NIS</th>
                                <th scope="col">PEMBIMBING</th>
                                <th scope="col">MAGANG MASUK</th>
                                <th scope="col">MAGANG KELUAR</th>
                                <th scope="col">DETAIL</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            if ($result->num_rows > 0) {
                                $prev_school = '';
                                $prev_period = '';
                                $surat_tugas_number = 0;
                                while ($row = $result->fetch_assoc()) {
                                    echo "<tr>";

                                    // Nomor, Surat Tugas, dan Asal Sekolah (hanya ditampilkan sekali per kelompok)
                                    if ($row['asal_sekolah'] != $prev_school || $row['magang_masuk'] != $prev_period) {
                                        $surat_tugas_number++;
                                        echo "<th scope='row' rowspan='" . $row['group_count'] . "'>" . $surat_tugas_number . "</th>";
                                        echo "<td rowspan='" . $row['group_count'] . "'>";
                                        if (!empty($row['no_surat'])) {
                                            echo "<a href='show-surat-tugas.php?sekolah=" . urlencode($row['asal_sekolah']) . "&masuk=" . urlencode($row['magang_masuk']) . "&keluar=" . urlencode($row['magang_keluar']) . "' target='_blank'>" . htmlspecialchars($row['no_surat']) . "</a>";
                                        } else {
                                            echo "<a href='#' class='add-surat-link' data-sekolah='" . htmlspecialchars($row['asal_sekolah']) . "' data-masuk='" . htmlspecialchars($row['magang_masuk']) . "' data-keluar='" . htmlspecialchars($row['magang_keluar']) . "'>Tambah No. Surat</a>";
                                        }
                                        echo "</td>";
                                        echo "<td rowspan='" . $row['group_count'] . "'>" . htmlspecialchars($row['asal_sekolah']) . "</td>";
                                        $prev_school = $row['asal_sekolah'];
                                        $prev_period = $row['magang_masuk'];
                                    }

                                    echo "<td>" . htmlspecialchars($row['nama']) . "</td>";
                                    echo "<td>" . htmlspecialchars($row['nis']) . "</td>";
                                    echo "<td>" . htmlspecialchars($row['pembimbing']) . "</td>";
                                    echo "<td>" . htmlspecialchars($row['magang_masuk']) . "</td>";
                                    echo "<td>" . htmlspecialchars($row['magang_keluar']) . "</td>";
                                    echo "<td><a href='#' onclick='openDetailModal(\"" . htmlspecialchars($row['nama']) . "\")'>Detail</a></td>";
                                    echo "</tr>";
                                }
                            } else {
                                echo "<tr><td colspan='9' class='no-data'>Belum ada data nih</td></tr>";
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
                <h2>Update Data Murid</h2>
            </div>
            <div class="modal-body">
                <form id="editForm" action="manage-admin.php" method="post">
                    <input type="hidden" id="editNama" name="nama">
                    <label for="editTanggal">Tanggal Lahir:</label>
                    <input type="date" id="editTanggal" name="editTanggal" required>
                    <label for="editNis">NIS:</label>
                    <input type="text" id="editNis" name="editNis" required>
                    <label for="editNik">NIK:</label>
                    <input type="text" id="editNik" name="editNik" required>
                    <label for="editNoTelp">No Handphone:</label>
                    <input type="text" id="editNoTelp" name="editNoTelp" required>
                    <label for="editEmail">Email:</label>
                    <input type="email" id="editEmail" name="editEmail" required>
                    <label for="editAsal">Asal Sekolah:</label>
                    <input type="text" id="editAsal" name="editAsal" required readonly>
                    <label for="editAlamat">Alamat Sekolah:</label>
                    <textarea id="editAlamat" name="editAlamat" required readonly></textarea>
                    <label for="editGuru">Guru Pendamping:</label>
                    <input type="text" id="editGuru" name="editGuru" required readonly>
                    <label for="editNoTelpGuru">No Telp Guru:</label>
                    <input type="text" id="editNoTelpGuru" name="editNoTelpGuru" required readonly>

                    <label for="editMasuk">Magang Masuk:</label>
                    <input type="date" id="editMasuk" name="editMasuk" required readonly>
                    <label for="editKeluar">Magang Keluar:</label>
                    <input type="date" id="editKeluar" name="editKeluar" required readonly>
                    <input type="submit" class="reset-btn" value="Update">
                </form>
            </div>
        </div>
    </div>

    <div id="detailModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <span class="close">&times;</span>
                <h2 class="sekolah">Detail Data</h2>
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

    <div id="suratModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <span class="close">&times;</span>
                <h2>Tambah Nomor Surat Tugas</h2>
            </div>
            <div class="modal-body">
                <form id="suratForm">
                    <input type="hidden" id="suratSekolah" name="sekolah">
                    <input type="hidden" id="suratMasuk" name="masuk">
                    <input type="hidden" id="suratKeluar" name="keluar">
                    <label for="noSurat">Nomor Surat:</label>
                    <input type="text" id="noSurat" name="no_surat" required>
                    <input type="submit" value="Simpan" class="reset-btn">
                </form>
            </div>
        </div>
    </div>

    <script>
    const editModal = document.getElementById("editModal");
    const closeBtns = document.querySelectorAll(".close");

    closeBtns.forEach(btn => {
        btn.onclick = function() {
            editModal.style.display = "none";
            document.getElementById('detailModal').style.display = "none";
        }
    });

    window.onclick = function(event) {
        if (event.target === editModal || event.target === document.getElementById('detailModal')) {
            editModal.style.display = "none";
            document.getElementById('detailModal').style.display = "none";
        }
    }

    function openEditModal(data) {
        document.getElementById('editNama').value = data.nama;
        document.getElementById('editTanggal').value = data.tanggal_lahir;
        document.getElementById('editNis').value = data.nis;
        document.getElementById('editNik').value = data.nik;
        document.getElementById('editNoTelp').value = data.no_telp;
        document.getElementById('editEmail').value = data.gmail;
        document.getElementById('editAsal').value = data.asal_sekolah;
        document.getElementById('editAlamat').value = data.alamat_sekolah;
        document.getElementById('editGuru').value = data.guru_pendamping;
        document.getElementById('editNoTelpGuru').value = data.no_telp_guru;
        document.getElementById('editMasuk').value = data.magang_masuk;
        document.getElementById('editKeluar').value = data.magang_keluar;

        editModal.style.display = "block";
        editModal.style.zIndex = "2000";
    }

    document.getElementById('editForm').onsubmit = function(event) {
        event.preventDefault();
        const formData = new FormData(this);
        formData.append('update', '1');

        fetch('manage-admin.php', {
                method: 'POST',
                body: formData
            })
            .then(response => {
                const contentType = response.headers.get("content-type");
                if (contentType && contentType.indexOf("application/json") !== -1) {
                    return response.json();
                } else {
                    throw new Error("Oops, kita gak dapet JSON nih!");
                }
            })
            .then(data => {
                if (data.status === 'success') {
                    Swal.fire({
                        icon: 'success',
                        title: 'Mantap!',
                        text: data.message,
                        showConfirmButton: false,
                        timer: 1500
                    }).then(() => {
                        window.location.reload();
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
                    text: 'Ada error nih. Coba lagi yuk! Error: ' + error.message
                });
            });
    };

    function confirmDelete(name) {
        Swal.fire({
            title: 'Yakin nih?',
            text: `Lo beneran mau hapus data ${name}?`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Ya, hapus aja!',
            cancelButtonText: 'Gak jadi deh'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = `hapus-data.php?nama=${encodeURIComponent(name)}`;
            }
        });
    }

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
        xhr.open('GET',
            `get-filtered-data.php?school=${encodeURIComponent(school)}&period=${encodeURIComponent(period)}&search=${encodeURIComponent(search)}`,
            true);

        xhr.onload = function() {
            if (this.status === 200) {
                document.querySelector('#dataTable tbody').innerHTML = this.responseText;
                attachSuratListeners();
            }
        };

        xhr.send();
    }

    function resetPage() {
        document.getElementById('schoolDropdown').value = '';
        document.getElementById('periodDropdown').value = '';
        document.getElementById('searchInput').value = '';
        applyFilters();
    }

    function openDetailModal(nama) {
        const xhr = new XMLHttpRequest();
        xhr.open("GET", "get-user-details.php?nama=" + encodeURIComponent(nama), true);
        xhr.onload = function() {
            if (this.status === 200) {
                const data = JSON.parse(this.responseText);
                const detailData = `
                        <div class="detail-container">
                            <div class="photo-frame">
                                ${data.img_base64 ? 
                                    `<img src="data:image/jpeg;base64,${data.img_base64}" alt="Foto Murid">` :
                                    `<div class="no-photo">Belum ada foto</div>`
                                }
                            </div>
                            <div class="tables-container">
                                <table class="detail-table left-table">
                                    <tr><th>Nama</th><td>${data.nama}</td></tr>
                                    <tr><th>Tanggal Lahir</th><td>${data.tanggal_lahir}</td></tr>
                                    <tr><th>NIS</th><td>${data.nis}</td></tr>
                                    <tr><th>NIK</th><td>${data.nik}</td></tr>
                                    <tr><th>Email</th><td>${data.gmail}</td></tr>
                                    <tr><th>No Telp</th><td>${data.no_telp}</td></tr>
                                </table>
                                <table class="detail-table right-table">
                                    <tr><th>Asal Sekolah</th><td>${data.asal_sekolah}</td></tr>
                                    <tr><th>Alamat Sekolah</th><td>${data.alamat_sekolah}</td></tr>
                                    <tr><th>Guru Pendamping</th><td>${data.guru_pendamping}</td></tr>
                                    <tr><th>No Telp Guru</th><td>${data.no_telp_guru}</td></tr>
                                    <tr><th>Magang Masuk</th><td>${data.magang_masuk}</td></tr>
                                    <tr><th>Magang Keluar</th><td>${data.magang_keluar}</td></tr>
                                </table>
                            </div>
                        </div>
                    `;
                document.getElementById('detailData').innerHTML = detailData;
                document.getElementById('detailModal').style.display = 'block';

                // Set up update button
                document.getElementById('updateBtn').onclick = function() {
                    openEditModal(data);
                };

                // Set up delete button
                document.getElementById('deleteBtn').onclick = function() {
                    confirmDelete(data.nama);
                };
            } else {
                document.getElementById('detailData').innerHTML = "Waduh, gagal ambil data nih.";
            }
        };
        xhr.send();
    }

    document.getElementById('addSchoolBtn').onclick = function() {
        window.location.href = 'manage-sekolah.php';
    };

    const suratModal = document.getElementById('suratModal');
    const suratLinks = document.querySelectorAll('.add-surat-link');
    const suratForm = document.getElementById('suratForm');

    function attachSuratListeners() {
        const suratLinks = document.querySelectorAll('.add-surat-link');
        suratLinks.forEach(link => {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                document.getElementById('suratSekolah').value = this.dataset.sekolah;
                document.getElementById('suratMasuk').value = this.dataset.masuk;
                document.getElementById('suratKeluar').value = this.dataset.keluar;
                suratModal.style.display = 'block';
            });
        });
    }

    suratForm.addEventListener('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        formData.append('update_surat', '1');

        fetch('manage-admin.php', {
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
                        window.location.reload();
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

    // Tutup modal kalo user klik di luar atau di tombol close
    window.onclick = function(event) {
        if (event.target === suratModal) {
            suratModal.style.display = "none";
        }
    }

    document.querySelector('#suratModal .close').onclick = function() {
        suratModal.style.display = "none";
    }

    // Panggil ini pas page load
    document.addEventListener('DOMContentLoaded', function() {
        attachSuratListeners();
    });
    </script>
</body>

</html>