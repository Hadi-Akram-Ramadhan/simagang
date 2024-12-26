<?php
header('Cache-Control: no-cache, must-revalidate');
header('Pragma: no-cache');
session_start();
require('koneksi.php');

$selected_month = isset($_GET['month']) ? $_GET['month'] : date('m');
$selected_year = isset($_GET['year']) ? $_GET['year'] : date('Y');

$start_date = "$selected_year-$selected_month-01";
$end_date = date('Y-m-t', strtotime($start_date));

// Query untuk data submitted
$sql = "SELECT DATE(waktu) as tanggal FROM laporan WHERE nama = ? AND waktu BETWEEN ? AND ? ORDER BY waktu DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("sss", $_SESSION['nama'], $start_date, $end_date);
$stmt->execute();
$result = $stmt->get_result();

$submitted_dates = [];
while ($row = $result->fetch_assoc()) {
    $submitted_dates[] = $row['tanggal'];
}

// Generate dates
$date_range = [];
$current = strtotime($start_date);
$last_day = strtotime($end_date);

while ($current <= $last_day) {
    $date = date('Y-m-d', $current);
?>
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
<?php
    $current = strtotime('+1 day', $current);
}

// Tutup koneksi
$stmt->close();
$conn->close();
?>