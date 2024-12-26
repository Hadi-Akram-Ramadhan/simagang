<?php
require('koneksi.php');
session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $month = $_POST['month'];
    $year = $_POST['year'];
    $nama = $_SESSION['nama'];

    $sql = "SELECT *, DATE_FORMAT(waktu, '%d-%m-%Y') as formatted_date, 
            UNIX_TIMESTAMP(waktu) as timestamp_waktu 
            FROM laporan 
            WHERE nama = ? 
            AND MONTH(waktu) = ? 
            AND YEAR(waktu) = ? 
            ORDER BY waktu DESC";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sii", $nama, $month, $year);
    $stmt->execute();
    $result = $stmt->get_result();

    $days_in_month = cal_days_in_month(CAL_GREGORIAN, $month, $year);
    $no = 1;
    $output = '<table class="timeline-table" style="min-width: 1500px;">
                <thead>
                    <tr>
                        <th style="width: 50px; min-width: 50px;">NO</th>
                        <th class="task-header">TUGAS</th>
                        <th class="task-header" style="width: 150px; min-width: 150px;">KATEGORI</th>';
    for ($i = 1; $i <= $days_in_month; $i++) {
        $output .= "<th>$i</th>";
    }
    $output .= '</tr></thead><tbody>';

    while ($row = $result->fetch_assoc()) {
        $output .= "<tr class='task-row'>";
        $output .= "<td style='text-align: center;'>" . $no++ . "</td>";
        $output .= "<td>" . $row['laporan'] . "</td>";
        $output .= "<td style='text-align: center;'>" . ($row['kategori'] ? $row['kategori'] : 'Periode Juli - November') . "</td>";

        for ($i = 1; $i <= $days_in_month; $i++) {
            $task_date = date('j', strtotime($row['waktu']));
            if ($task_date == $i) {
                $approvedClass = ($row['status'] == 1) ? 'approved' : '';
                $tooltipData = htmlspecialchars(json_encode([
                    'laporan' => $row['laporan'],
                    'tanggal' => $row['formatted_date'],
                    'kategori' => $row['kategori'] ? $row['kategori'] : 'Periode Juli - November',
                    'status' => $row['status'],
                    'img' => base64_encode($row['img_dir'])
                ]), ENT_QUOTES);

                $output .= "<td class='has-task {$approvedClass}' onclick='showTaskDetail(this)' data-task='{$tooltipData}'></td>";
            } else {
                $output .= "<td></td>";
            }
        }
        $output .= "</tr>";
    }
    $output .= '</tbody></table>';

    echo $output;
}
