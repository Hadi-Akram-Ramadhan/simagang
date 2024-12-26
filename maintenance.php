<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SIMAGANG - Under Maintenance</title>
    <!-- Pake style yang udah ada sebelumnya -->
    <link rel="shortcut icon" href="image/kementrian.png">
    <link rel="stylesheet" href="bootstrap/css/bootstrap.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/three.js/r134/three.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/vanta@latest/dist/vanta.globe.min.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">

    <style>
    body {
        margin: 0;
        padding: 0;
        min-height: 100vh;
        display: flex;
        justify-content: center;
        align-items: center;
        font-family: 'Poppins', sans-serif;
        background-color: #f8f9fa;
        position: relative;
        overflow: hidden;
    }

    #vanta-bg {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        z-index: 0;
    }

    .logo {
        position: absolute;
        top: 20px;
        left: 20px;
        width: 100px;
        height: auto;
        z-index: 2;
    }

    .maintenance-container {
        position: relative;
        z-index: 1;
        background-color: rgba(255, 255, 255, 0.85);
        backdrop-filter: blur(12px);
        max-width: 90%;
        width: 600px;
        padding: 50px;
        border-radius: 24px;
        box-shadow: 0 12px 40px rgba(0, 0, 0, 0.15);
        text-align: center;
        margin: 20px;
    }

    .maintenance-icon {
        font-size: 72px;
        margin-bottom: 25px;
        background: linear-gradient(135deg, #005B94, #8DC63F);
        -webkit-background-clip: text;
        background-clip: text;
        color: transparent;
    }

    .maintenance-status {
        display: inline-block;
        padding: 10px 20px;
        background: linear-gradient(135deg, #005B94, #8DC63F);
        color: white;
        border-radius: 30px;
        font-size: 16px;
        margin-bottom: 25px;
        font-weight: 500;
        letter-spacing: 0.5px;
    }

    h2 {
        font-size: 32px;
        margin-bottom: 20px;
        color: #2c3e50;
        font-weight: 600;
    }

    .text-muted {
        font-size: 16px;
        line-height: 1.6;
        color: #666;
        margin-bottom: 25px;
    }

    .progress {
        height: 12px;
        margin: 30px 0;
        background-color: rgba(0, 0, 0, 0.08);
        border-radius: 10px;
        overflow: hidden;
    }

    .progress-bar {
        background: linear-gradient(135deg, #005B94, #8DC63F);
        animation: progress-animation 2.5s ease-in-out infinite;
    }

    @keyframes progress-animation {
        0% {
            width: 15%;
        }

        50% {
            width: 85%;
        }

        100% {
            width: 15%;
        }
    }

    .estimated-time {
        color: #555;
        font-size: 16px;
        margin-top: 25px;
        font-weight: 500;
    }

    .estimated-time i {
        margin-right: 8px;
        color: #005B94;
    }

    .contact-info {
        margin-top: 30px;
        padding-top: 20px;
        border-top: 1px solid rgba(0, 0, 0, 0.1);
    }

    .contact-info a {
        color: #005B94;
        text-decoration: none;
        font-weight: 500;
        transition: color 0.3s;
    }

    .contact-info a:hover {
        color: #8DC63F;
    }
    </style>
</head>

<body>
    <div id="vanta-bg"></div>


    <div class="maintenance-container">
        <div class="maintenance-icon">
            <i class="fas fa-cogs"></i>
        </div>
        <div class="maintenance-status">
            Sedang Maintenance
        </div>
        <h2>Website Dalam Perbaikan</h2>
        <p class="text-muted">
            Maaf, SIMAGANG sedang maintenance. Kita sedang upgrade sistem agar pengalaman kamu makin mantap! 🚀
        </p>

        <div class="progress">
            <div class="progress-bar" role="progressbar"></div>
        </div>

        <div class="estimated-time">
            <i class="far fa-clock"></i> Estimasi: ± 30 Menit
        </div>

        <p class="mt-4">
            Untuk info lebih lanjut, bisa hubungi:<br>
            <a href="mailto:kk.hadi.akram@gmail.com" style="color: #005B94;">kk.hadi.akram@gmail.com</a>
        </p>
    </div>

    <script src="https://kit.fontawesome.com/your-kit-code.js"></script>
    <script>
    VANTA.GLOBE({
        el: "#vanta-bg",
        mouseControls: true,
        touchControls: true,
        gyroControls: false,
        minHeight: 200.00,
        minWidth: 200.00,
        scale: 1.00,
        scaleMobile: 1.00,
        color: 0x005B94,
        color2: 0x8DC63F,
        backgroundColor: 0xf8f9fa,
        size: 1.50,
        spacing: 20.00
    });
    </script>
</body>

</html>