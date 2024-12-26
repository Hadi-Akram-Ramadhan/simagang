<?php
session_start();
include('koneksi.php');
require('auth.php');
require('navUser.php');

$user = $_SESSION['nama'];
$tanggal_pertama = '';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== '1') {
    header("Location: index.php");
    exit();
}

// Query untuk mendapatkan tanggal absensi per'tama kali
$query_first_date = "SELECT MIN(DATE_FORMAT(waktu, '%Y-%m-%d')) AS tanggal_pertama
                     FROM images
                     WHERE user = ?";
$stmt_first_date = $conn->prepare($query_first_date);
$stmt_first_date->bind_param("s", $user);
$stmt_first_date->execute();
$result_first_date = $stmt_first_date->get_result();
$row_first_date = $result_first_date->fetch_assoc();
$tanggal_pertama = $row_first_date['tanggal_pertama'];

$stmt_first_date->close();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['month'])) {
        $month = $_POST['month'];

        if ($month) {
            $start_date = $month . '-01';
            $end_date = date("Y-m-t", strtotime($start_date));

            // Query untuk mendapatkan data absensi dan tugas
            $query = "SELECT DATE_FORMAT(waktu, '%Y-%m-%d') AS tanggal, COUNT(*) AS jumlah_foto
                      FROM images
                      WHERE user = ? AND DATE_FORMAT(waktu, '%Y-%m-%d') BETWEEN ? AND ?
                      GROUP BY DATE_FORMAT(waktu, '%Y-%m-%d')";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("sss", $user, $start_date, $end_date);
            $stmt->execute();
            $result = $stmt->get_result();

            $attendance = [];
            while ($row = $result->fetch_assoc()) {
                $attendance[$row['tanggal']] = [
                    'date' => $row['tanggal'],
                    'status' => $row['jumlah_foto'] >= 1 ? 'Hadir' : 'Tidak Hadir'
                ];
            }

            // Cek untuk setiap hari di bulan yang dipilih, apakah ada tugas meskipun tidak ada absensi
            $period = new DatePeriod(
                new DateTime($start_date),
                new DateInterval('P1D'),
                new DateTime($end_date)
            );

            foreach ($period as $dt) {
                $formatted_date = $dt->format("Y-m-d");
                if (!isset($attendance[$formatted_date])) {
                    // Query untuk cek apakah ada tugas di tanggal ini
                    $query_task = "SELECT COUNT(*) AS task_count FROM laporan WHERE nama = ? AND DATE(waktu) = ?";
                    $stmt_task = $conn->prepare($query_task);
                    $stmt_task->bind_param("ss", $user, $formatted_date);
                    $stmt_task->execute();
                    $result_task = $stmt_task->get_result();
                    $row_task = $result_task->fetch_assoc();

                    if ($row_task['task_count'] > 0) {
                        $attendance[$formatted_date] = [
                            'date' => $formatted_date,
                            'status' => 'Izin'
                        ];
                    }
                }
            }

            $stmt->close();
        }
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Histori Absen</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #fafafa;
            color: #484b6a;

            margin: 0;
        }

        h2 {
            margin-top: 10px;
            text-align: center;
            color: #484b6a;
            font-size: 24px;
            font-weight: 600;
        }

        .container {
            margin-top: 10rem;
            max-width: 80%;
            margin-left: auto;
            margin-right: auto;
            background-color: #ffffff;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            margin-bottom: 10rem;
        }

        .calendar {
            margin-bottom: 30px;
            text-align: center;
        }

        form {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 15px;
        }

        label {
            font-weight: 500;
            color: #484b6a;
        }

        input[type="month"] {
            padding: 10px 15px;
            font-size: 16px;
            border: 2px solid #e4e5f1;
            border-radius: 8px;
            background-color: transparent;
            color: #484b6a;
            outline: none;
            transition: border-color 0.3s ease;
        }

        input[type="month"]:focus {
            border-color: #004B8F;
        }

        input[type="submit"],
        input[type="button"] {
            background: linear-gradient(135deg, #004B8F, #0072BC);
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 500;
            font-size: 0.9rem;
            transition: all 0.3s ease;
            width: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        input[type="submit"]:hover,
        input[type="button"]:hover {
            background: linear-gradient(135deg, #002E5D, #004B8F);
            transform: translateY(-2px);
        }

        table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            margin-top: 30px;
            overflow: hidden;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.05);
        }

        table th,
        table td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid #e4e5f1;
        }

        table th {
            background-color: #004B8F;
            color: white;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 14px;
            letter-spacing: 0.5px;
        }

        table tr:last-child td {
            border-bottom: none;
        }

        table td {
            background-color: #ffffff;
            transition: background-color 0.3s ease;
        }

        table tr:hover td {
            background-color: #E8F1F8;
        }

        .modal {
            display: none;
            position: fixed;
            z-index: 999999;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0, 0, 0, 0.4);
            padding-top: 60px;
        }

        .modal-content {
            background-color: #ffffff;
            margin: 5% auto;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            max-width: 600px;
        }

        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
        }

        .close:hover,
        .close:focus {
            color: black;
            text-decoration: none;
            cursor: pointer;
        }

        .task-details {
            background-color: #E8F1F8;
            border-left: 4px solid #004B8F;
            color: #484b6a;
            padding: 20px;
            border-radius: 10px;
        }

        .pemisah {
            margin-top: 50px;
        }

        /* Media Queries for responsiveness */
        @media (max-width: 768px) {
            .container {
                width: 95%;
                margin-top: 10rem;
                padding: 10px;
            }

            form {
                flex-direction: column;
                align-items: stretch;
            }

            input[type="month"],
            input[type="submit"],
            input[type="button"] {
                width: 100%;
                margin: 10px 0;
            }
        }

        @media (max-width: 480px) {

            body {

                margin-top: 40px;
            }

            .container {
                width: 95%;
                margin-top: 10rem;
                padding: 10px;
            }

            .modal-content {
                width: 95%;
            }
        }

        /* Styling untuk link detail */
        table a {
            color: #004B8F;
            text-decoration: none;
            font-weight: 500;
            transition: color 0.3s ease;
        }

        table a:hover {
            color: #F7941D;
        }

        /* Tambah styles untuk loader */
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
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body>
    <!-- Tambah loader di awal body -->
    <div class="loader-container">
        <div class="loader"></div>
    </div>

    <div class="content">
        <div class="container">
            <h2>Histori Absen</h2>

            <div class="calendar">
                <form method="post">
                    <label for="month">Pilih Bulan:</label>
                    <input type="month" id="month" name="month" value="<?= isset($_POST['month']) ? $_POST['month'] : '' ?>">
                    <input type="submit" value="Tampilkan">
                    <input type="button" value="Print Laporan" onclick="window.open('laporan_pdf.php', '_blank');">
                </form>
            </div>

            <table>
                <thead>
                    <tr>
                        <th>Tanggal</th>
                        <th>Status</th>
                        <th>Tugas</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($attendance)) {
                        if (count($attendance) > 0) {
                            foreach ($attendance as $item) {
                                $date = $item['date'];
                                $status = $item['status'];
                                echo "<tr>
                                        <td>{$date}</td>
                                        <td>{$status}</td>
                                        <td><a href='#' onclick='showTaskDetails(\"{$date}\", \"{$user}\")'>Detail</a></td>
                                      </tr>";
                            }
                        } else {
                            echo "<script>Swal.fire('Info', 'Tidak ada data absensi untuk bulan ini.', 'info');</script>";
                        }
                    } else {
                        echo "<script>Swal.fire('Info', 'Pilih bulan untuk melihat data absensi.', 'info');</script>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal untuk menampilkan detail tugas -->
    <div id="taskModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal()">&times;</span>
            <div class="task-details">
                <h2>Detail Tugas</h2>
                <p><b>Tanggal:</b> <span id="taskDate"></span></p>
                <p><b>User:</b> <span id="taskUser"></span></p>

                <!-- Container buat multiple tasks -->
                <div id="taskContainer"></div>
            </div>
        </div>
    </div>

    <?php

    ?>

    <script>
        function showTaskDetails(date, user) {
            // Show loader
            document.querySelector('.loader-container').style.display = 'flex';

            var xhr = new XMLHttpRequest();
            xhr.open('POST', 'fetch_task_details.php', true);
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
            xhr.onreadystatechange = function() {
                if (xhr.readyState === 4 && xhr.status === 200) {
                    // Hide loader
                    document.querySelector('.loader-container').style.display = 'none';

                    var response = JSON.parse(xhr.responseText);
                    if (response.success) {
                        document.getElementById('taskDate').innerText = date;
                        document.getElementById('taskUser').innerText = user;

                        // Clear container dulu
                        var taskContainer = document.getElementById('taskContainer');
                        taskContainer.innerHTML = '';

                        // Loop through semua tasks
                        response.tasks.forEach(function(task, index) {
                            var taskDiv = document.createElement('div');
                            taskDiv.className = 'task-item';
                            taskDiv.style.marginBottom = '20px';
                            taskDiv.style.padding = '15px';
                            taskDiv.style.backgroundColor = '#f5f5f5';
                            taskDiv.style.borderRadius = '8px';

                            taskDiv.innerHTML = `
                                <h3>Tugas ${index + 1}</h3>
                                <p><b>Laporan:</b> ${task.laporan}</p>
                                <p><b>Gambar Tugas:</b><br>
                                <img src="${task.img_dir}" alt="Gambar Tugas" width="200">
                                </p>
                            `;

                            taskContainer.appendChild(taskDiv);
                        });

                        document.getElementById('taskModal').style.display = 'block';
                    } else {
                        Swal.fire('Error', 'Wah, detail tugasnya ga ketemu nih.', 'error');
                    }
                }
            };
            xhr.send('date=' + date + '&user=' + user + '&_=' + new Date().getTime());
        }

        function closeModal() {
            document.getElementById('taskModal').style.display = 'none';
        }

        // Tambah script untuk handle loader
        window.addEventListener('load', function() {
            const loader = document.querySelector('.loader-container');
            const content = document.querySelector('.content');

            setTimeout(() => {
                loader.style.display = 'none';
                content.style.display = 'block';
            }, 1000); // Loading selama 1 detik
        });
    </script>
</body>

</html>