<?php
session_start();
include('koneksi.php');
require('auth.php');
require('navAdmin.php');

// Query untuk mengambil data periode magang
$queryPeriods = "SELECT DISTINCT magang_masuk, magang_keluar FROM akun ORDER BY magang_masuk";
$resultPeriods = mysqli_query($conn, $queryPeriods);
$periods = [];

while ($period = mysqli_fetch_assoc($resultPeriods)) {
    if ($period['magang_masuk'] != '0000-00-00' && $period['magang_keluar'] != '0000-00-00') {
        $periods[] = $period['magang_masuk'] . ' - ' . $period['magang_keluar'];
    }
}

// Query untuk mengambil semua sekolah dengan guru pendamping atau murid yang masih aktif
$querySekolah = "SELECT DISTINCT asal_sekolah 
                 FROM akun 
                 WHERE role IN (1, 3) 
                 AND magang_masuk <= CURDATE() 
                 AND magang_keluar >= CURDATE()";
$resultSekolah = mysqli_query($conn, $querySekolah);

$schoolsInfo = [];

while ($school = mysqli_fetch_assoc($resultSekolah)) {
    $schoolName = $school['asal_sekolah'];

    // Query untuk guru pendamping yang aktif
    $queryGuru = "SELECT nama 
                  FROM akun 
                  WHERE asal_sekolah = '$schoolName' 
                  AND role = 3 
                  AND magang_masuk <= CURDATE() 
                  AND magang_keluar >= CURDATE() 
                  LIMIT 1";
    $resultGuru = mysqli_query($conn, $queryGuru);
    $guruName = mysqli_fetch_assoc($resultGuru)['nama'] ?? 'Belum Ada';

    // Query untuk murid yang aktif
    $queryMurid = "SELECT nama, img_dir 
                   FROM akun 
                   WHERE asal_sekolah = '$schoolName' 
                   AND role = 1 
                   AND magang_masuk <= CURDATE() 
                   AND magang_keluar >= CURDATE()";
    $resultMurid = mysqli_query($conn, $queryMurid);
    $muridNames = [];

    while ($murid = mysqli_fetch_assoc($resultMurid)) {
        $muridNames[] = [
            'nama' => $murid['nama'],
            'img' => 'data:image/jpeg;base64,' . base64_encode($murid['img_dir'])
        ];
    }

    // Hanya tambahkan ke schoolsInfo jika ada murid aktif
    if (!empty($muridNames)) {
        $schoolsInfo[$schoolName] = ['guru' => $guruName, 'murid' => $muridNames];
    }
}

// Query untuk mengambil jumlah murid per sekolah
$query = "SELECT asal_sekolah, COUNT(*) as jumlah_murid FROM akun WHERE role = 1 GROUP BY asal_sekolah";
$result = mysqli_query($conn, $query);

$schools = [];
$counts = [];

while ($row = mysqli_fetch_assoc($result)) {
    $schools[] = $row['asal_sekolah'];
    $counts[] = $row['jumlah_murid'];
}

// Encode data untuk JavaScript
$schools_json = json_encode($schools);
$counts_json = json_encode($counts);

// Ubah query untuk mengambil jumlah murid per sekolah dan periode
$queryMagang = "SELECT asal_sekolah, magang_masuk, magang_keluar, COUNT(*) as jumlah_murid 
                FROM akun 
                WHERE role = 1 
                GROUP BY asal_sekolah, magang_masuk, magang_keluar";
$resultMagang = mysqli_query($conn, $queryMagang);
$magangData = [];

while ($row = mysqli_fetch_assoc($resultMagang)) {
    $magangData[] = [
        'name' => $row['asal_sekolah'],
        'magang_masuk' => $row['magang_masuk'],
        'magang_keluar' => $row['magang_keluar'],
        'jumlah_murid' => $row['jumlah_murid']
    ];
}

$magang_json = json_encode($magangData);

// Ubah format data magang untuk timeline
$timelineData = [];
foreach ($magangData as $index => $data) {
    $timelineData[] = [
        'id' => $index,
        'content' => $data['name'] . ' (' . $data['jumlah_murid'] . ' murid)',
        'start' => $data['magang_masuk'],
        'end' => $data['magang_keluar']
    ];
}
$timeline_json = json_encode($timelineData);

?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Beranda</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto+Condensed:ital,wght@0,100..900;1,100..900&display=swap"
        rel="stylesheet">
    <link href='https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/5.10.1/main.min.css' rel='stylesheet' />
    <script src="
https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.js
"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/vis-timeline/7.5.1/vis-timeline-graph2d.min.js"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/vis-timeline/7.5.1/vis-timeline-graph2d.min.css" rel="stylesheet"
        type="text/css" />
    <style>
    @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap');

    body {
        font-family: 'Poppins', sans-serif;
        background-color: #f8f9fa;
        margin: 0;
        padding: 0;
    }

    .container {
        display: flex;
        justify-content: center;
        align-items: flex-start;
        flex-wrap: wrap;
        margin: 15px auto;
        width: 92%;
        gap: 20px;
    }

    .info-container,
    .chart-container {
        flex: 1;
        min-width: 300px;
        margin: 0;
        padding: 20px;
        background-color: #ffffff;
        border-radius: 16px;
        box-shadow: 0 2px 20px rgba(0, 0, 0, 0.03);
    }

    .info-section {
        flex: 1 0 calc(45% - 24px);
        margin: 8px;
        padding: 16px;
        background-color: #ffffff;
        border: 1px solid #f0f0f0;
        border-radius: 12px;
        transition: all 0.3s ease;
    }

    .info-section:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 20px rgba(0, 130, 204, 0.1);
    }

    .select-period {
        background: #ffffff;
        padding: 15px;
        border-radius: 12px;
        margin-bottom: 20px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.03);
    }

    .select-period>div {
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .period-container {
        display: flex;
        align-items: center;
        gap: 12px;
    }

    /* Optional: Kalo mau bikin dropdown lebih lebar */
    select {
        min-width: 200px;
    }

    .reset-button {
        padding: 8px 16px;
        background-color: #0082CC;
        color: white;
        border: none;
        border-radius: 8px;
        cursor: pointer;
        font-size: 13px;
        font-weight: 500;
        transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .reset-button:hover {
        background-color: #006ba8;
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(0, 130, 204, 0.2);
    }

    .reset-button:active {
        transform: translateY(0);
    }

    .swal2-container {
        z-index: 9999999999999 !important;
    }

    .school-header {
        background: linear-gradient(135deg, #0082CC, #006ba8);
        color: white;
        padding: 10px;
        border-radius: 8px;
        margin-bottom: 12px;
        text-align: center;
        font-weight: 500;
        font-size: 14px;
        letter-spacing: 0.3px;
    }

    select {
        padding: 8px 14px;
        border: 1.5px solid #e0e0e0;
        border-radius: 8px;
        background-color: white;
        font-size: 13px;
        color: #333;
        cursor: pointer;
        transition: all 0.2s ease;
        font-family: 'Poppins', sans-serif;
    }

    select:hover {
        border-color: #0082CC;
        transform: translateY(-1px);
    }

    select:focus {
        transform: translateY(-1px);
        box-shadow: 0 5px 15px rgba(0, 130, 204, 0.1);
    }

    .chart-section {
        background-color: white;
        border-radius: 12px;
        padding: 20px;
        height: 280px;
        margin-bottom: 20px;
        animation: fadeIn 0.5s ease-in;
    }

    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: translateY(10px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .ringkasan {
        color: #2d3436;
        font-weight: 600;
        font-size: 16px;
        letter-spacing: -0.3px;
        padding-bottom: 12px;
        border-bottom: 2px solid #f5f6f7;
        margin-bottom: 15px;
    }

    /* Timeline & Chart Customization */
    .vis-item {
        border-radius: 6px;
        border: none;
        background: linear-gradient(135deg, #0082CC, #006ba8) !important;
        color: white;
        font-weight: 500;
        font-size: 11px;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        transition: none !important;
    }

    .vis-timeline {
        border: none;
        background-color: #ffffff;
        border-radius: 12px;
        font-family: 'Poppins', sans-serif;
        font-size: 12px;
        transition: none !important;
    }

    /* List Styling */
    ul {
        padding-left: 15px;
    }

    li {
        font-size: 13px;
        margin: 8px 0;
        color: #4a4a4a;
        display: flex;
        align-items: center;
        gap: 8px;
        transition: all 0.2s ease;
        padding: 5px 10px;
        border-radius: 6px;
    }

    li:hover {
        background: rgba(0, 130, 204, 0.05);
        transform: translateX(5px);
    }

    li img {
        width: 28px !important;
        height: 28px !important;
        border-radius: 50%;
        object-fit: cover;
        border: 2px solid #f0f0f0;
        transition: all 0.2s ease;
    }

    li:hover img {
        transform: scale(1.1);
    }

    /* Labels & Text */
    label {
        font-size: 13px;
        color: #666;
        margin-right: 8px;
    }

    p {
        font-size: 13px;
        color: #4a4a4a;
        margin: 8px 0;
    }

    /* Loading Overlay */
    #loading-overlay {
        background-color: rgba(255, 255, 255, 0.95);
    }

    .spinner {
        border: 3px solid #f3f3f3;
        border-top: 3px solid #0082CC;
        border-radius: 50%;
        width: 30px;
        height: 30px;
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

    /* Responsive adjustments */
    @media (max-width: 768px) {
        .container {
            width: 95%;
        }

        .info-section {
            flex: 1 0 100%;
        }
    }

    .pemisah {
        margin-top: 6rem;
    }

    /* Smooth transitions untuk semua elemen */
    * {
        transition: all 0.3s ease;
    }

    /* Hover effect untuk info sections */
    .info-section {
        transform: translateY(0);
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .info-section:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 20px rgba(0, 130, 204, 0.1);
    }

    /* Animasi untuk button */
    .reset-button {
        transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .reset-button:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(0, 130, 204, 0.2);
    }

    .reset-button:active {
        transform: translateY(0);
    }

    /* Hover effect untuk select/dropdown */
    select {
        transition: all 0.2s ease;
    }

    select:hover {
        border-color: #0082CC;
        transform: translateY(-1px);
    }

    select:focus {
        transform: translateY(-1px);
        box-shadow: 0 5px 15px rgba(0, 130, 204, 0.1);
    }

    /* Hover effect untuk list items */
    li {
        transition: all 0.2s ease;
        padding: 5px 10px;
        border-radius: 6px;
    }

    li:hover {
        background: rgba(0, 130, 204, 0.05);
        transform: translateX(5px);
    }

    li img {
        transition: all 0.2s ease;
    }

    li:hover img {
        transform: scale(1.1);
    }

    /* Loading animation */
    .spinner {
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

    /* Fade in animation untuk chart sections */
    .chart-section {
        animation: fadeIn 0.5s ease-in;
    }

    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: translateY(10px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    /* Smooth scroll behavior */
    html {
        scroll-behavior: smooth;
    }
    </style>
</head>

<body>
    <div id="loading-overlay"
        style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(255, 255, 255, 0.9); z-index: 9999; justify-content: center; align-items: center;">
        <div class="spinner"></div>
    </div>

    <div class="pemisah"></div>
    <div class="container">
        <div class="info-container">
            <h1 class="ringkasan">Ringkasan Magang</h1>
            <div class="period-container">
                <div class="select-period">
                    <div>
                        <label for="period">Pilih Periode:</label>
                        <select id="period">
                            <option value=""><i>Pilih Periode</i></option>
                            <?php foreach ($periods as $period): ?>
                            <option value="<?= $period ?>"><?= $period ?></option>
                            <?php endforeach; ?>
                        </select>
                        <button class="reset-button" onclick="resetPage()">Segarkan</button>
                    </div>
                </div>
            </div>
            <?php foreach ($schoolsInfo as $schoolName => $info): ?>
            <div class="info-section">
                <h2 class="school-header"><?= $schoolName ?></h2>

                <p>Guru Pendamping: <?= $info['guru'] ?></p>
                <p>Murid:</p>
                <ul>
                    <?php foreach ($info['murid'] as $muridName): ?>
                    <li><img src="<?= $muridName['img'] ?>"
                            style="width:30px; height:30px; border-radius:50%; vertical-align:middle;">
                        <?= $muridName['nama'] ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <?php endforeach; ?>
        </div>
        <div class="chart-container">
            <div class="chart-section">
                <h1 class="ringkasan">Jumlah Magang Murid Sekolah Mitra Kemendag</h1>
                <canvas id="attendanceChart" width="250" height="100"></canvas>
            </div>
            <div class="chart-section">
                <h1 class="ringkasan">Timeline Periode Magang</h1>
                <div id="timeline"></div>
            </div>
        </div>
    </div>


    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src='https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/5.10.1/main.min.js'></script>
    <script>
    const schools = <?php echo $schools_json; ?>;
    const counts = <?php echo $counts_json; ?>;

    function getGradient(ctx, chartArea) {
        const width = chartArea.right - chartArea.left;
        const height = chartArea.bottom - chartArea.top;
        const gradient = ctx.createLinearGradient(0, chartArea.bottom, 0, chartArea.top);
        gradient.addColorStop(0, 'rgba(0,130,204,0.5)');
        gradient.addColorStop(1, 'rgba(0,179,142,0.5)');
        return gradient;
    }

    const ctx = document.getElementById('attendanceChart').getContext('2d');
    const attendanceChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: schools,
            datasets: [{
                data: counts,
                backgroundColor: function(context) {
                    const chart = context.chart;
                    const {
                        ctx,
                        chartArea
                    } = chart;

                    if (!chartArea) {
                        return null;
                    }
                    return getGradient(ctx, chartArea);
                }
            }]
        },
        options: {
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1
                    }
                }
            }
        }
    });

    // Array warna cerah
    const brightColors = [
        '#FF9AA2', '#FFB7B2', '#FFDAC1', '#E2F0CB', '#B5EAD7',
        '#C7CEEA', '#FF99C8', '#FCF6BD', '#9ADCFF', '#ADF7B6'
    ];
    let colorIndex = 0;

    function getNextBrightColor() {
        const color = brightColors[colorIndex];
        colorIndex = (colorIndex + 1) % brightColors.length;
        return color;
    }

    document.addEventListener('DOMContentLoaded', function() {
        var calendarEl = document.getElementById('calendar');
        var calendar = new FullCalendar.Calendar(calendarEl, {
            headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: 'dayGridMonth,timeGridWeek,timeGridDay'
            },
            titleFormat: {
                month: 'long',
                year: 'numeric'
            },
            dayCellContent: function(e) {
                e.dayNumberText = '';
            },
            initialView: 'dayGridMonth',
            events: <?php echo $magang_json; ?>.map(data => ({
                title: data.name,
                start: data.magang_masuk,
                end: data.magang_keluar,
                display: 'block',
                backgroundColor: getNextBrightColor(),
                borderColor: getNextBrightColor(),
                textColor: 'black' // Set warna teks jadi hitam
            })),
            eventContent: function(arg) {
                let dom = document.createElement('div');
                dom.className = 'event-gradient';
                dom.innerHTML = arg.event.title;
                dom.style.background = arg.event.backgroundColor;
                dom.style.borderColor = arg.event.borderColor;
                dom.style.color = 'black'; // Set warna teks jadi hitam
                return {
                    domNodes: [dom]
                };
            },
            dayCellClassNames: function(date) {
                var today = new Date();
                today.setHours(0, 0, 0, 0);
                if (date.date.valueOf() === today.valueOf()) {
                    return ['today-highlight'];
                }
                return [];
            },
            buttonText: {
                today: 'Hari Ini',
                month: 'Bulan',
                week: 'Minggu',
                day: 'Hari'
            }
        });
        calendar.render();
    });

    document.getElementById('period').addEventListener('change', function() {
        var selectedPeriod = this.value;
        if (selectedPeriod) {
            showLoading();
            fetch('fetch_period_data.php?period=' + encodeURIComponent(selectedPeriod))
                .then(response => response.json())
                .then(data => {
                    const infoContainer = document.querySelector('.info-container');
                    document.querySelectorAll('.info-section').forEach(section => section.remove());

                    data.forEach(item => {
                        const section = document.createElement('div');
                        section.className = 'info-section';
                        section.innerHTML =
                            `<h2 class="school-header">${item.asal_sekolah}</h2><p>Guru Pendamping: ${item.guru}</p><p>Murid:</p>`;
                        const ul = document.createElement('ul');
                        item.murid.forEach(murid => {
                            const li = document.createElement('li');
                            li.innerHTML =
                                `<img src="${murid.img}" style="width:30px; height:30px; border-radius:50%; vertical-align:middle;"> ${murid.nama}`;
                            ul.appendChild(li);
                        });
                        section.appendChild(ul);
                        infoContainer.appendChild(section);
                    });
                })
                .catch(error => console.error('Error:', error))
                .finally(() => {
                    hideLoading();
                });
        }
    });

    // Kode untuk timeline
    var container = document.getElementById('timeline');
    var items = new vis.DataSet(<?php echo $timeline_json; ?>);
    var options = {
        stack: true,
        start: new Date(new Date().getFullYear(), 0, 1),
        end: new Date(new Date().getFullYear(), 11, 31),
        editable: false,
        margin: {
            item: 10,
            axis: 5
        },
        orientation: 'top',
        height: '250px',
        verticalScroll: false,
        zoomable: true,
        moveable: true,
        zoomMin: 1000 * 60 * 60 * 24 * 7, // 1 minggu
        zoomMax: 1000 * 60 * 60 * 24 * 365, // 1 tahun
        template: function(item) {
            return `<div class="timeline-item">${item.content}</div>`;
        }
    };
    var timeline = new vis.Timeline(container, items, options);

    // Fungsi untuk generate warna random
    function getRandomColor() {
        var letters = '0123456789ABCDEF';
        var color = '#';
        for (var i = 0; i < 6; i++) {
            color += letters[Math.floor(Math.random() * 16)];
        }
        return color;
    }

    // Set warna random untuk setiap item
    items.forEach(function(item) {
        item.style = `background-color: ${getRandomColor()}`;
        timeline.itemsData.update(item);
    });

    document.addEventListener('DOMContentLoaded', function() {
        // Sembunyikan loading overlay setelah halaman selesai dimuat
        document.getElementById('loading-overlay').style.display = 'none';
    });

    // Tambahkan ini di awal fungsi yang melakukan fetch data
    function showLoading() {
        document.getElementById('loading-overlay').style.display = 'flex';
    }

    // Tambahkan ini di akhir fungsi yang melakukan fetch data
    function hideLoading() {
        document.getElementById('loading-overlay').style.display = 'none';
    }
    </script>
    <script>
    function resetPage() {
        window.location.reload();
    }
    </script>
</body>

</html>