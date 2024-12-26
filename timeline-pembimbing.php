<?php
session_start();
require('koneksi.php');
require('auth.php');
require_once('navPembimbing.php');

function getDaysInMonth($month, $year)
{
    return cal_days_in_month(CAL_GREGORIAN, $month, $year);
}

// Basic auth check - Update to allow admin role
if (!isset($_SESSION['role']) || $_SESSION['role'] !== '4') {
    header("Location: index.php");
    exit();
}

// Get nama from URL parameter
$nama = isset($_GET['nama']) ? $_GET['nama'] : '';
if (empty($nama)) {
    header("Location: homePemb.php");
    exit();
}

// Tambah query untuk ambil periode magang
$sql_periode = "SELECT magang_masuk, magang_keluar FROM akun WHERE nama = ?";
$stmt_periode = $conn->prepare($sql_periode);
$stmt_periode->bind_param("s", $nama);
$stmt_periode->execute();
$result_periode = $stmt_periode->get_result();
$periode = $result_periode->fetch_assoc();

// Convert dates ke DateTime objects untuk lebih mudah handle
$start_date = new DateTime($periode['magang_masuk']);
$end_date = new DateTime($periode['magang_keluar']);

// Get start and end months/years
$start_month = (int)$start_date->format('n');
$start_year = (int)$start_date->format('Y');
$end_month = (int)$end_date->format('n');
$end_year = (int)$end_date->format('Y');
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <title>Timeline Magang</title>
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
            background-color: #C5DCF0 !important;
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
            margin-bottom: 7rem;
        }

        .card h2.section-title-nama {
            margin: 0;
            padding: 10px;
            text-align: center;
            background: linear-gradient(135deg, #004B8F, #0072BC);
            color: white;
            border-radius: 8px;
        }

        /* Update style untuk has-task dan approved */
        .has-task.approved {
            background-color: #A5D6A7 !important;
        }

        /* Tambah style khusus untuk yang approved */
        .has-task.approved::after {
            content: '✓';
            color: #004B8F;
            font-weight: bold;
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
        }

        /* Tambah CSS ini */
        nav {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 1000;
            background: white;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .container {
            padding-top: 80px;
            /* Sesuaiin sama tinggi navbar */
            margin-top: 0 !important;
            /* Reset margin top yang lama */
        }

        /* Untuk konten yang perlu sticky */
        .section-title-nama {
            position: sticky;
            top: 80px;
            /* Sesuaiin dengan jarak dari navbar */
            z-index: 900;
            background: white;
        }

        /* Optional: Kalo mau smooth scroll */
        html {
            scroll-behavior: smooth;
        }

        /* Pastikan body ga punya margin yang ganggu */
        body {
            margin: 0;
            padding: 0;
            overflow-x: hidden;
        }

        /* Fix untuk iOS momentum scroll */
        body {
            -webkit-overflow-scrolling: touch;
        }

        /* Styling untuk month header */
        .month-info {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .task-stats {
            display: flex;
            gap: 10px;
            font-size: 0.9em;
        }

        .stat {
            display: flex;
            align-items: center;
            gap: 5px;
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 0.85em;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
            transition: all 0.2s ease;
        }

        .stat:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .stat.approved {
            background: linear-gradient(135deg, #A5D6A7, #81C784);
            color: #1B5E20;
        }

        .stat.pending {
            background: linear-gradient(135deg, #FFE0B2, #FFCC80);
            color: #E65100;
        }

        .stat i {
            font-size: 0.9em;
        }

        /* Responsive adjustment */
        @media (max-width: 768px) {
            .month-info {
                flex-direction: column;
                align-items: flex-start;
                gap: 5px;
            }

            .task-stats {
                font-size: 0.8em;
            }
        }

        /* Tambah style untuk button terima tugas */
        .approve-btn {
            background: linear-gradient(135deg, #4CAF50, #45A049);
            color: white;
            border: none;
            padding: 6px 12px;
            border-radius: 8px;
            cursor: pointer;
            font-size: 0.85em;
            display: flex;
            align-items: center;
            gap: 6px;
            transition: all 0.2s ease;
            box-shadow: 0 2px 4px rgba(76, 175, 80, 0.2);
        }

        .approve-btn:hover {
            background: linear-gradient(135deg, #45A049, #388E3C);
            transform: translateY(-1px);
            box-shadow: 0 4px 8px rgba(76, 175, 80, 0.3);
        }

        .approve-btn:active {
            transform: translateY(0);
            box-shadow: 0 2px 4px rgba(76, 175, 80, 0.2);
        }

        .approve-btn i {
            font-size: 0.9em;
        }

        /* Update style untuk month header */
        .month-header {
            background: white;
            padding: 15px 20px;
            border-radius: 12px;
            cursor: pointer;
            display: flex;
            justify-content: space-between;
            align-items: center;
            transition: all 0.3s ease;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
            border: 1px solid #edf2f7;
        }

        .month-header:hover {
            background: #f8fafc;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
        }

        .month-section.active .month-header {
            background: linear-gradient(135deg, #004B8F, #0072BC);
            color: white;
            box-shadow: 0 4px 15px rgba(0, 75, 143, 0.2);
        }

        /* Style untuk modal */
        .swal2-popup {
            border-radius: 16px !important;
            padding: 2em !important;
        }

        .swal2-title {
            font-size: 1.5em !important;
            color: #2D3748 !important;
        }

        .swal2-textarea {
            min-height: 120px !important;
            font-size: 0.95em !important;
            padding: 12px !important;
            border-radius: 12px !important;
            border: 2px solid #E2E8F0 !important;
            transition: all 0.2s ease !important;
        }

        .swal2-textarea:focus {
            border-color: #004B8F !important;
            box-shadow: 0 0 0 2px rgba(0, 75, 143, 0.1) !important;
        }

        .swal2-confirm {
            background: linear-gradient(135deg, #004B8F, #0072BC) !important;
            border-radius: 8px !important;
            padding: 12px 24px !important;
            font-size: 0.95em !important;
            transition: all 0.2s ease !important;
        }

        .swal2-confirm:hover {
            transform: translateY(-1px) !important;
            box-shadow: 0 4px 12px rgba(0, 75, 143, 0.2) !important;
        }

        .swal2-cancel {
            background: #EDF2F7 !important;
            color: #4A5568 !important;
            border-radius: 8px !important;
            padding: 12px 24px !important;
            font-size: 0.95em !important;
            transition: all 0.2s ease !important;
        }

        .swal2-cancel:hover {
            background: #E2E8F0 !important;
            transform: translateY(-1px) !important;
        }

        /* Responsive adjustment */
        @media (max-width: 768px) {
            .month-info {
                flex-direction: column;
                align-items: flex-start;
                gap: 5px;
            }

            .task-stats {
                font-size: 0.8em;
            }

            .month-header {
                padding: 12px 15px;
            }

            .stat {
                padding: 3px 8px;
                font-size: 0.8em;
            }

            .approve-btn {
                padding: 4px 10px;
                font-size: 0.8em;
            }
        }

        .edit-btn {
            background: linear-gradient(135deg, #3498db, #2980b9);
            color: white;
            border: none;
            padding: 6px 12px;
            border-radius: 8px;
            cursor: pointer;
            font-size: 0.85em;
            display: flex;
            align-items: center;
            gap: 6px;
            transition: all 0.2s ease;
            box-shadow: 0 2px 4px rgba(52, 152, 219, 0.2);
        }

        .edit-btn:hover {
            background: linear-gradient(135deg, #2980b9, #2573a7);
            transform: translateY(-1px);
            box-shadow: 0 4px 8px rgba(52, 152, 219, 0.3);
        }

        .edit-btn:active {
            transform: translateY(0);
            box-shadow: 0 2px 4px rgba(52, 152, 219, 0.2);
        }

        .edit-btn i {
            font-size: 0.9em;
        }

        /* Update task stats container */
        .task-stats {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: #f8fafc;
            padding: 4px;
            border-radius: 10px;
            border: 1px solid #e2e8f0;
        }

        /* Stats badges */
        .stat {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 6px 12px;
            border-radius: 8px;
            font-weight: 500;
            font-size: 0.85em;
            min-width: 80px;
            /* Biar lebar badge konsisten */
            justify-content: center;
        }

        .stat.approved {
            background: linear-gradient(135deg, #A5D6A7, #81C784);
            color: #1B5E20;
        }

        .stat.pending {
            background: linear-gradient(135deg, #FFE0B2, #FFCC80);
            color: #E65100;
        }

        /* Buttons */
        .approve-btn,
        .edit-btn {
            min-width: 120px;
            /* Biar lebar button konsisten */
            height: 32px;
            /* Fixed height */
            justify-content: center;
            padding: 0 16px;
            font-weight: 500;
        }

        /* Container layout */
        .month-header {
            padding: 12px 16px;
        }

        .month-info {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        /* Month name */
        .month-name {
            font-weight: 600;
            min-width: 100px;
            /* Biar nama bulan align */
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            .month-info {
                flex-wrap: wrap;
                gap: 8px;
            }

            .task-stats {
                flex-wrap: wrap;
                width: 100%;
                justify-content: flex-start;
                padding: 6px;
            }

            .stat {
                min-width: auto;
                flex: 1;
            }

            .approve-btn,
            .edit-btn {
                flex: 1;
            }
        }

        /* Update styling untuk back button */
        .back-section {
            margin-top: 2rem;
            margin-bottom: 20px;
            position: sticky;
            top: 20px;
            z-index: 100;
        }

        .back-button {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            padding: 12px 20px;
            background: linear-gradient(135deg, #004B8F, #0072BC);
            /* Warna biru kemendag */
            border: none;
            border-radius: 8px;
            color: white;
            text-decoration: none;
            font-size: 0.95em;
            font-weight: 500;
            transition: all 0.2s ease;
            box-shadow: 0 4px 12px rgba(0, 75, 143, 0.15);
            position: relative;
            overflow: hidden;
        }

        .back-button:hover {
            background: linear-gradient(135deg, #003972, #004B8F);
            color: white;
            transform: translateY(-1px);
            box-shadow: 0 6px 16px rgba(0, 75, 143, 0.2);
        }

        .back-button:active {
            transform: translateY(1px);
            box-shadow: 0 2px 8px rgba(0, 75, 143, 0.15);
        }

        /* Subtle arrow animation */
        .back-button i {
            font-size: 1em;
            transition: transform 0.2s ease;
        }

        .back-button:hover i {
            transform: translateX(-4px);
        }

        /* Subtle highlight effect */
        .back-button::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg,
                    transparent,
                    rgba(255, 255, 255, 0.1),
                    transparent);
            transition: 0.5s;
        }

        .back-button:hover::before {
            left: 100%;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .back-section {
                position: fixed;
                top: 15px;
                left: 15px;
                right: 15px;
            }

            .back-button {
                width: auto;
                padding: 10px 16px;
                font-size: 0.9em;
                top: 10px;
                left: 10px;
                right: 10px;
            }
        }

        /* Update styling untuk mobile responsiveness */
        @media (max-width: 768px) {

            /* Atur ukuran tabel untuk mobile */
            .timeline-table {
                min-width: auto !important;
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

            /* Atur kolom kategori */
            .task-header:nth-child(3),
            .task-row td:nth-child(3) {
                width: 100px !important;
                min-width: 100px !important;
                max-width: 100px !important;
                font-size: 12px;
            }

            /* Atur kolom tanggal */
            .timeline-table th:not(.task-header):not(:first-child),
            .timeline-table td:not(:first-child):not(:nth-child(2)):not(:nth-child(3)) {
                min-width: 35px !important;
                max-width: 35px !important;
                padding: 4px;
                font-size: 12px;
            }

            /* Atur ukuran font dan padding */
            .timeline-table th,
            .timeline-table td {
                padding: 8px;
                font-size: 14px;
            }

            /* Pastikan semua row punya height yang sama */
            .task-row {
                height: auto;
                min-height: 40px;
            }

            /* Atur modal detail tugas */
            .swal2-popup {
                width: 90% !important;
                padding: 1em !important;
            }

            .gambar {
                max-height: 200px;
            }

            /* Atur stats dan button container */
            .month-info {
                flex-direction: column;
                align-items: flex-start;
                gap: 8px;
            }

            .task-stats {
                flex-wrap: wrap;
                gap: 5px;
            }

            .stat {
                font-size: 0.8em;
                padding: 4px 8px;
            }

            .approve-btn,
            .edit-btn {
                font-size: 0.8em;
                padding: 4px 10px;
                min-width: auto;
            }
        }

        /* Tambahan untuk device yang lebih kecil */
        @media (max-width: 480px) {

            .task-header,
            .task-row td:nth-child(2) {
                min-width: 120px !important;
            }

            .task-header:nth-child(3),
            .task-row td:nth-child(3) {
                width: 80px !important;
                min-width: 80px !important;
                max-width: 80px !important;
            }

            .timeline-table th:not(.task-header):not(:first-child),
            .timeline-table td:not(:first-child):not(:nth-child(2)):not(:nth-child(3)) {
                min-width: 30px !important;
                max-width: 30px !important;
                font-size: 11px;
            }

            /* Atur ukuran font lebih kecil */
            .timeline-table th,
            .timeline-table td {
                font-size: 12px;
                padding: 6px;
            }

            /* Atur container stats */
            .month-header {
                padding: 8px;
            }

            .task-stats {
                width: 100%;
            }

            .stat {
                flex: 1;
                min-width: auto;
                justify-content: center;
            }
        }

        /* Fix untuk sticky header dan kolom pertama */
        @media (max-width: 768px) {
            .timeline-table thead th {
                position: sticky;
                top: 0;
                background: #fff;
                z-index: 10;
            }

            .timeline-table td:first-child,
            .timeline-table th:first-child {
                position: sticky;
                left: 0;
                background: #fff;
                z-index: 5;
            }

            /* Fix untuk shadow effect */
            .timeline-table td:first-child::after,
            .timeline-table th:first-child::after {
                content: '';
                position: absolute;
                top: 0;
                right: -5px;
                bottom: 0;
                width: 5px;
                background: linear-gradient(to right, rgba(0, 0, 0, 0.1), transparent);
            }
        }

        /* Tambahan untuk smooth scrolling */
        .table-responsive {
            -webkit-overflow-scrolling: touch;
            scroll-behavior: smooth;
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
            <!-- Tambah section nama di sini -->
            <div class="back-section">
                <a href="homePemb.php" class="back-button">
                    <i class="fas fa-arrow-left"></i>
                    <span>Kembali ke Dashboard</span>
                </a>
            </div>
            <div class="card" style="margin-bottom: 20px;">
                <h2 class="section-title-nama" style="margin: 0;"><?php echo htmlspecialchars($nama); ?></h2>
            </div>

            <!-- Timeline -->
            <div class="card upload-form">
                <h2 class="section-title">Timeline Internship Program</h2>
                <div class="timeline-wrapper">
                    <?php
                    $current_day = date('j');
                    $current_month = date('n');
                    $current_year = date('Y');

                    // Perlu ditambah logic untuk handle multiple years
                    $sql = "SELECT DISTINCT YEAR(waktu) as years 
                            FROM laporan 
                            WHERE nama = ? 
                            ORDER BY years DESC";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param("s", $nama);
                    $stmt->execute();
                    $years_result = $stmt->get_result();

                    // Loop untuk tiap tahun
                    while ($year_row = $years_result->fetch_assoc()) {
                        $year = $year_row['years'];

                        // Skip tahun yang di luar periode magang
                        if ($year < $start_year || $year > $end_year) continue;

                        $month_names = [
                            1 => 'JANUARI',
                            2 => 'FEBRUARI',
                            3 => 'MARET',
                            4 => 'APRIL',
                            5 => 'MEI',
                            6 => 'JUNI',
                            7 => 'JULI',
                            8 => 'AGUSTUS',
                            9 => 'SEPTEMBER',
                            10 => 'OKTOBER',
                            11 => 'NOVEMBER',
                            12 => 'DESEMBER'
                        ];
                    ?>
                        <div class="months-container">
                            <?php foreach ($month_names as $month_num => $month_name):
                                // Skip bulan yang di luar periode magang
                                if ($year == $start_year && $month_num < $start_month) continue;
                                if ($year == $end_year && $month_num > $end_month) continue;

                                // Update bagian query untuk cek tugas
                                $sql_check = "SELECT 
                                    COUNT(*) as task_count,
                                    SUM(CASE WHEN status = 1 THEN 1 ELSE 0 END) as approved_count,
                                    SUM(CASE WHEN status = 0 THEN 1 ELSE 0 END) as pending_count
                                    FROM laporan 
                                    WHERE nama = ? 
                                    AND MONTH(waktu) = ? 
                                    AND YEAR(waktu) = ?";
                                $stmt_check = $conn->prepare($sql_check);
                                $stmt_check->bind_param("sii", $nama, $month_num, $year);
                                $stmt_check->execute();
                                $task_stats = $stmt_check->get_result()->fetch_assoc();
                            ?>
                                <div class="month-section <?php echo ($month_num == $current_month && $year == $current_year) ? 'active' : ''; ?>">
                                    <div class="month-header" onclick="toggleMonth(<?php echo $month_num; ?>)">
                                        <div class="month-info">
                                            <span class="month-name"><?php echo $month_name; ?></span>
                                            <?php if ($task_stats['task_count'] > 0): ?>
                                                <div class="task-stats">
                                                    <span class="stat approved" title="Tugas disetujui">
                                                        <i class="fas fa-check-circle"></i>
                                                        <?php echo $task_stats['approved_count']; ?>
                                                    </span>
                                                    <span class="stat pending" title="Tugas pending">
                                                        <i class="fas fa-clock"></i>
                                                        <?php echo $task_stats['pending_count']; ?>
                                                    </span>
                                                    <?php if ($task_stats['pending_count'] > 0): ?>
                                                        <button
                                                            class="approve-btn"
                                                            onclick="showApproveModal(event, '<?php echo $nama; ?>', <?php echo $month_num; ?>, <?php echo $year; ?>, <?php echo $task_stats['pending_count']; ?>)"
                                                            title="Terima semua tugas">
                                                            <i class="fas fa-check"></i> Terima
                                                        </button>
                                                    <?php elseif ($task_stats['approved_count'] > 0): ?>
                                                        <button
                                                            class="edit-btn"
                                                            onclick="showEditCommentModal(event, '<?php echo $nama; ?>', <?php echo $month_num; ?>, <?php echo $year; ?>)"
                                                            title="Edit komentar">
                                                            <i class="fas fa-edit"></i> Edit
                                                        </button>
                                                    <?php endif; ?>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                        <i class="fas fa-chevron-down"></i>
                                    </div>

                                    <div class="month-content" id="month-<?php echo $month_num; ?>">
                                        <div class="table-responsive" style="overflow-x: auto; width: 100%;">
                                            <?php if ($task_stats['task_count'] > 0): ?>
                                                <table class="timeline-table" style="min-width: 1500px;">
                                                    <thead>
                                                        <tr>
                                                            <th style="width: 50px; min-width: 50px;">NO</th>
                                                            <th class="task-header">TUGAS</th>
                                                            <th class="task-header" style="width: 150px; min-width: 150px;">KATEGORI</th>
                                                            <?php
                                                            $days_in_month = getDaysInMonth($month_num, $year);
                                                            for ($i = 1; $i <= $days_in_month; $i++) {
                                                                $class = ($current_day == $i && $month_num == $current_month) ? 'current-date' : '';
                                                                echo "<th class='$class'>$i</th>";
                                                            }
                                                            ?>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <?php
                                                        // Query untuk ambil tugas sesuai bulannya
                                                        $sql = "SELECT *, DATE_FORMAT(waktu, '%d-%m-%Y') as formatted_date, 
                                                                UNIX_TIMESTAMP(waktu) as timestamp_waktu 
                                                                FROM laporan 
                                                                WHERE nama = ? 
                                                                AND MONTH(waktu) = ? 
                                                                AND YEAR(waktu) = ? 
                                                                ORDER BY waktu DESC";
                                                        $stmt = $conn->prepare($sql);
                                                        $stmt->bind_param("sii", $nama, $month_num, $year);
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

                                                                    // Update tooltipData untuk include kategori
                                                                    $tooltipData = htmlspecialchars(json_encode([
                                                                        'laporan' => $row['laporan'],
                                                                        'tanggal' => $row['formatted_date'],
                                                                        'kategori' => $row['kategori'] ? $row['kategori'] : 'Tidak ada kategori',
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
                                            <?php else: ?>
                                                <div class="no-tasks-message" style="text-align: center; padding: 20px; color: #666;">
                                                    <i class="fas fa-info-circle" style="font-size: 24px; color: #004B8F; margin-bottom: 10px;"></i>
                                                    <p>Belum ada tugas yang dikumpulkan di bulan ini.</p>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php } ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Tambahkan JavaScript -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const currentMonth = <?php echo $current_month; ?>;

            // Buka bulan sekarang dulu
            toggleMonth(currentMonth);

            // Delay scroll ke tanggal hari ini biar ga konflik
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

        function toggleMonth(monthNum) {
            const monthContent = document.querySelector(`#month-${monthNum}`);
            if (!monthContent) return; // Early return kalo element ga ketemu

            const section = monthContent.closest('.month-section');
            if (!section) return; // Early return kalo ga ketemu parent section

            const allSections = document.querySelectorAll('.month-section');

            // Kalo section yang diklik udah active, berarti mau di-collapse
            if (section.classList.contains('active')) {
                section.classList.remove('active');
                return;
            }

            // Kalo belum active, tutup semua dulu baru buka yang diklik
            allSections.forEach(s => s.classList.remove('active'));
            section.classList.add('active');
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
        const fotoInput = document.getElementById('foto');
        if (fotoInput) {
            fotoInput.addEventListener('change', function(e) {
                const fileName = e.target.files[0]?.name || 'Pilih file atau drag & drop disini';
                const fileNameElement = document.getElementById('file-name');
                if (fileNameElement) {
                    fileNameElement.textContent = fileName;
                }
            });
        }

        // Script untuk drag & drop
        const dropZone = document.querySelector('.file-label');
        if (dropZone) {
            ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
                dropZone.addEventListener(eventName, preventDefaults, false);
            });

            ['dragenter', 'dragover'].forEach(eventName => {
                dropZone.addEventListener(eventName, highlight, false);
            });

            ['dragleave', 'drop'].forEach(eventName => {
                dropZone.addEventListener(eventName, unhighlight, false);
            });

            dropZone.addEventListener('drop', handleDrop, false);
        }

        function preventDefaults(e) {
            e.preventDefault();
            e.stopPropagation();
        }

        function highlight(e) {
            dropZone.classList.add('highlight');
        }

        function unhighlight(e) {
            dropZone.classList.remove('highlight');
        }

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
        if (timelineSlider) {
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
        }

        // Table wrapper scroll
        const tableWrapper = document.querySelector('.table-responsive');
        if (tableWrapper) {
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
        }

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

        function showApproveModal(event, nama, bulan, tahun, jumlahTugas) {
            // Stop event propagation biar ga bentrok sama toggleMonth
            event.stopPropagation();

            Swal.fire({
                title: 'Terima Semua Tugas',
                html: `
                    <div style="margin-bottom: 15px; text-align: left;">
                        <p>Jumlah tugas pending: <strong>${jumlahTugas}</strong></p>
                    </div>
                    <div style="text-align: left;">
                        <label for="comment" style="display: block; margin-bottom: 8px;">Komentar:</label>
                        <textarea id="comment" class="swal2-textarea" placeholder="Masukkan komentar untuk semua tugas..."></textarea>
                    </div>
                `,
                showCancelButton: true,
                confirmButtonText: 'Submit',
                cancelButtonText: 'Batal',
                showLoaderOnConfirm: true,
                preConfirm: () => {
                    const comment = document.getElementById('comment').value;
                    if (!comment) {
                        Swal.showValidationMessage('Komentar harus diisi!');
                        return false;
                    }

                    return approveAllTasks(nama, bulan, tahun, comment);
                },
                allowOutsideClick: () => !Swal.isLoading()
            });
        }

        async function approveAllTasks(nama, bulan, tahun, comment) {
            try {
                const response = await fetch('approve_tasks.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        nama: nama,
                        bulan: bulan,
                        tahun: tahun,
                        comment: comment
                    })
                });

                const data = await response.json();

                if (data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil!',
                        text: 'Semua tugas berhasil diterima',
                        showConfirmButton: false,
                        timer: 1500
                    }).then(() => {
                        // Reload halaman
                        window.location.reload();
                    });
                } else {
                    throw new Error(data.message || 'Terjadi kesalahan');
                }
            } catch (error) {
                Swal.fire({
                    icon: 'error',
                    title: 'Oops...',
                    text: error.message
                });
            }
        }

        async function showEditCommentModal(event, nama, bulan, tahun) {
            event.stopPropagation();

            try {
                // Fetch existing comment first
                const response = await fetch('get_comment.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        nama: nama,
                        bulan: bulan,
                        tahun: tahun
                    })
                });

                const data = await response.json();

                if (!data.success) {
                    throw new Error(data.message || 'Failed to fetch comment');
                }

                Swal.fire({
                    title: 'Edit Komentar',
                    html: `
                        <div style="margin-bottom: 15px; text-align: left;">
                            <p>Edit komentar untuk semua tugas di bulan ini</p>
                        </div>
                        <div style="text-align: left;">
                            <label for="comment" style="display: block; margin-bottom: 8px;">Komentar:</label>
                            <textarea id="comment" class="swal2-textarea">${data.comment}</textarea>
                        </div>
                    `,
                    showCancelButton: true,
                    confirmButtonText: 'Update',
                    cancelButtonText: 'Batal',
                    showLoaderOnConfirm: true,
                    preConfirm: () => {
                        const comment = document.getElementById('comment').value;
                        if (!comment) {
                            Swal.showValidationMessage('Komentar harus diisi!');
                            return false;
                        }

                        return updateComment(nama, bulan, tahun, comment);
                    },
                    allowOutsideClick: () => !Swal.isLoading()
                });
            } catch (error) {
                Swal.fire({
                    icon: 'error',
                    title: 'Oops...',
                    text: error.message
                });
            }
        }

        async function updateComment(nama, bulan, tahun, comment) {
            try {
                const response = await fetch('update_comment.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        nama: nama,
                        bulan: bulan,
                        tahun: tahun,
                        comment: comment
                    })
                });

                const data = await response.json();

                if (data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil!',
                        text: 'Komentar berhasil diupdate',
                        showConfirmButton: false,
                        timer: 1500
                    }).then(() => {
                        window.location.reload();
                    });
                } else {
                    throw new Error(data.message || 'Terjadi kesalahan');
                }
            } catch (error) {
                Swal.fire({
                    icon: 'error',
                    title: 'Oops...',
                    text: error.message
                });
            }
        }
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