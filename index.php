<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

$error = ''; // Inisialisasi variabel error

if (!file_exists("koneksi.php")) {
  $error = 'Waduh, file koneksi.php ga ketemu nih.';
  exit();
}

require_once("koneksi.php");

// Ambil teks dari database
$sql = "SELECT first, second FROM settings WHERE id = 1";
$result = $conn->query($sql);
if ($result->num_rows > 0) {
  $row = $result->fetch_assoc();
  $welcomeText = $row['first'];
  $instructionText = $row['second'];
} else {
  $welcomeText = "Selamat Datang";
  $instructionText = "Silahkan masukkan e-mail dan password kamu";
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
  $gmail = $_POST['gmail'];
  $password = $_POST['pass'];

  // Update query untuk ambil magang_keluar juga
  $sql = "SELECT * FROM akun WHERE gmail = ?";
  $stmt = $conn->prepare($sql);
  $stmt->bind_param("s", $gmail);
  $stmt->execute();
  $result = $stmt->get_result();

  if ($result->num_rows == 1) {
    $user = $result->fetch_assoc();
    // Verifikasi password
    if (password_verify($password, $user['pass'])) {
      
      // Cek tanggal magang_keluar
      $today = new DateTime();
      $magang_keluar = new DateTime($user['magang_keluar']);
      
      // Kalo udah lewat masa magangnya
      if ($today > $magang_keluar && $user['role'] === '1') {
        $error = 'Waduh, masa magang kamu sudah habis nih. Hubungi admin ya!';
        // Skip login
        goto skip_login;
      }

      // Logika untuk menangani role user
      if ($user['role'] === '1') {
        $_SESSION['user'] = $user;
        $_SESSION['role'] = $user['role'];
        $_SESSION['nama'] = $user['nama'];
        header("Location: homeUser.php");
        exit();
      } else if ($user['role'] === '2') {
        $_SESSION['admin'] = $user;
        $_SESSION['role'] = $user['role'];
        $_SESSION['nama'] = $user['nama'];
        header("Location: admin.php");
        exit();
      } else if ($user['role'] === '3') {
        $_SESSION['teacher'] = $user;
        $_SESSION['role'] = $user['role'];
        $_SESSION['nama'] = $user['nama'];
        $_SESSION['sekolah'] = $user['asal_sekolah'];
        header("Location: homeTeacher.php");
        exit();
      } else if ($user['role'] === '4') {
        $_SESSION['pembimbing'] = $user;
        $_SESSION['role'] = $user['role'];
        $_SESSION['nama'] = $user['nama'];
        header("Location: homePemb.php");
        exit();
      }
    } else {
      $error = 'Waduh, email atau password kamu salah nih. Coba cek lagi ya!';
    }
  }
  
  skip_login:
  if (empty($error)) {
    $error = 'Hmm, kayaknya kamu belum terdaftar di sistem nih. Udah yakin punya akun?';
  }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Management Data Magang</title>
    <link rel="stylesheet" href="fontawesome/css/fontawesome.min.css">
    <link rel="shortcut icon" href="image\kementrian.png">
    <link rel="stylesheet" href="bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="css/kita.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous">
    </script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" />
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" />
    <script src="https://accounts.google.com/gsi/client" async defer></script>
    <meta name="google-signin-client_id"
        content="630202116221-u9o6kd6g5rf3gdj637pf924fs1r53gbu.apps.googleusercontent.com">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/three.js/r134/three.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/vanta@latest/dist/vanta.globe.min.js"></script>
    <style>
    @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap');

    body {
        font-family: 'Poppins', sans-serif;
        background: #f8f9fa;
        margin: 0;
        min-height: 100vh;
        display: flex;
        justify-content: center;
        align-items: center;
        overflow: hidden;
        position: relative;
        color: #333;
    }

    h2 {
        font-size: 26px;
    }

    .starry-background {
        display: none;
    }

    .star,
    .planet {
        position: absolute;
        border-radius: 50%;
        background: white;
        pointer-events: none;
        animation: twinkle 5s infinite;
    }

    .star {
        width: 2px;
        height: 2px;
        animation-duration: 2s;
    }

    .planet {
        width: 20px;
        height: 20px;
        background: radial-gradient(circle, #ffeb3b, #ff9800);
        animation-duration: 20s;
    }

    @keyframes twinkle {

        0%,
        100% {
            opacity: 0.8;
        }

        50% {
            opacity: 0.2;
        }
    }

    .login-container {
        position: relative;
        z-index: 1;
        background-color: rgba(255, 255, 255, 0.4);
        backdrop-filter: blur(8px);
        max-width: 80%;
        width: 420px;
        padding: 40px;
        border-radius: 16px;
        box-shadow: 0 8px 30px rgba(0, 0, 0, 0.2);
        color: #333;
        box-sizing: border-box;
        text-align: left;
    }

    .selek {
        text-align: center;
    }

    .login-header {
        margin-bottom: 20px;
    }

    .login-header img {
        max-width: 80px;
        margin-bottom: 20px;
    }

    .login-form label {
        color: #333;
    }

    .login-form input {
        background-color: rgba(255, 255, 255, 0.8);
        border: 1px solid #ddd;
        color: #333;
        border-bottom: 2px solid #4a4a4a;
        border-radius: 4px;
        padding: 10px 5px;
        transition: all 0.3s ease;
    }

    .login-form input::placeholder {
        color: #666;
    }

    .login-form input:focus {
        border-color: #007bff;
        background-color: rgba(255, 255, 255, 0.15);
        box-shadow: none;
    }

    .login-form .btn-primary {
        background-color: #007bff;
        border: none;
        border-radius: 30px;
        padding: 12px 20px;
        font-size: 16px;
        font-weight: 500;
        letter-spacing: 0.5px;
        transition: all 0.3s ease;
    }

    .login-form .btn-primary:hover {
        background-color: #0056b3;
        transform: translateY(-2px);
        box-shadow: 0 4px 15px rgba(0, 123, 255, 0.3);
    }

    .login-footer {
        margin-top: 20px;
        font-size: 14px;
        color: #666;
    }

    .login-footer a {
        color: #007bff;
    }

    .logo {
        position: absolute;
        top: 15px;
        left: 15px;
        z-index: 2;
        width: 30px;
        height: 30px;
        filter: drop-shadow(1px 3px 3px rgba(255, 255, 255, 0.8));
    }

    .cursor-star {
        position: absolute;
        width: 5px;
        height: 5px;
        background: radial-gradient(circle, #ffffff, #d0f5f9);
        border-radius: 50%;
        pointer-events: none;
        transform: translate(-50%, -50%);
        z-index: 2;
        opacity: 0;
        transition: opacity 0.5s ease-out;
    }

    @keyframes cursor-twinkle {

        0%,
        100% {
            opacity: 0.8;
        }

        50% {
            opacity: 0.2;
        }
    }

    @media (max-width: 768px) {
        .login-header img {
            max-width: 100px;
        }

        .login-container {
            padding: 20px;
        }

        .logo {
            width: 25px;
            height: 25px;
        }
    }

    @media (max-width: 576px) {
        .login-header img {
            max-width: 80px;
        }

        .login-container {
            width: 90%;
            padding: 15px;
        }

        .logo {
            width: 20px;
            height: 20px;
        }
    }

    .kiri {
        text-align: left;
    }

    .alert {
        padding: 10px;
        margin-top: 20px;
        border: 1px solid transparent;
        border-radius: 4px;
        text-align: center;
    }

    .alert-danger {
        color: #721c24;
        background-color: #f8d7da;
        border-color: #f5c6cb;
        text-align: center;
    }

    .popover {
        max-width: 300px;
    }

    .popover-body {
        padding: 10px;
    }

    .popover-body h6 {
        margin-bottom: 10px;
    }

    .popover-body ul {
        padding-left: 20px;
        margin-bottom: 10px;
    }

    .google-signin-container {
        display: flex;
        justify-content: center;
        align-items: center;
        margin-top: 20px;
    }

    #vanta-bg {
        position: fixed;
        z-index: -1;
        top: 0;
        left: 0;
        width: 100%;
        height: 100vh;
        background: #000;
    }

    .text-muted {
        color: #6c757d !important;
    }

    /* Tambahin style buat loading screen */
    #loading-screen {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: #f8f9fa;
        display: flex;
        justify-content: center;
        align-items: center;
        z-index: 999999999999 !important;
        transition: opacity 0.5s ease-out;
    }

    .atomic-loader {
        position: relative;
        width: 80px;
        height: 80px;
    }

    .electron {
        position: absolute;
        width: 100%;
        height: 100%;
        border-radius: 50%;
        border: 2px solid transparent;
        border-top-color: #4a90e2;
        border-right-color: #2ecc71;
        animation: orbit 2s linear infinite;
    }

    .electron:nth-child(1) {
        animation-duration: 2s;
    }

    .electron:nth-child(2) {
        width: 60%;
        height: 60%;
        margin: 20%;
        animation-duration: 1.5s;
        animation-direction: reverse;
    }

    .electron:nth-child(3) {
        width: 40%;
        height: 40%;
        margin: 30%;
        animation-duration: 1s;
    }

    .nucleus {
        position: absolute;
        width: 15px;
        height: 15px;
        background: #4a90e2;
        border-radius: 50%;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        box-shadow: 0 0 15px rgba(74, 144, 226, 0.5);
        animation: pulse 1.5s ease-in-out infinite;
    }

    @keyframes orbit {
        from {
            transform: rotate(0deg);
        }

        to {
            transform: rotate(360deg);
        }
    }

    @keyframes pulse {
        0% {
            transform: translate(-50%, -50%) scale(0.8);
            opacity: 0.5;
        }

        50% {
            transform: translate(-50%, -50%) scale(1.2);
            opacity: 1;
        }

        100% {
            transform: translate(-50%, -50%) scale(0.8);
            opacity: 0.5;
        }
    }

    .form-group input {
        background-color: #fff !important;
        border: 1px solid #ced4da !important;
        color: #333 !important;
    }

    .form-group input::placeholder {
        color: #999 !important;
    }

    .input-group-text {
        color: #333 !important;
        border-left: none !important;
        background-color: #fff !important;
    }

    .fa-eye,
    .fa-eye-slash {
        color: #666 !important;
    }

    /* Styling buat focus state */
    .form-group input:focus {
        background-color: #fff !important;
        border-color: #80bdff !important;
        color: #333 !important;
    }

    .event-popup {
        display: none;
        position: fixed;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        z-index: 1000;
        width: 90%;
        max-width: 420px;
    }

    .event-content {
        background: rgba(255, 255, 255, 0.95);
        border: 1px solid rgba(0, 0, 0, 0.1);
        box-shadow:
            0 25px 50px -12px rgba(0, 0, 0, 0.15),
            0 0 0 1px rgba(0, 0, 0, 0.05);
        border-radius: 24px;
        overflow: hidden;
        transform-style: preserve-3d;
        perspective: 1000px;
    }

    .event-header {
        background: linear-gradient(135deg, #005B94, #8DC63F);
        padding: 25px;
        position: relative;
        box-shadow: 0 4px 15px rgba(0, 91, 148, 0.2);
    }

    .event-header::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: url('data:image/svg+xml,<svg width="20" height="20" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><rect width="1" height="1" fill="rgba(255,255,255,0.05)"/></svg>');
        opacity: 0.3;
    }

    .event-header h3 {
        color: #fff;
        font-size: 1.6rem;
        font-weight: 700;
        margin: 0;
        display: flex;
        align-items: center;
        gap: 12px;
        text-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
    }

    .close-btn {
        position: absolute;
        top: 15px;
        right: 15px;
        background: rgba(255, 255, 255, 0.15);
        border: none;
        color: white;
        width: 35px;
        height: 35px;
        border-radius: 50%;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        font-size: 1.2rem;
        backdrop-filter: blur(5px);
    }

    .close-btn:hover {
        background: rgba(255, 255, 255, 0.25);
        transform: rotate(90deg) scale(1.1);
    }

    .event-body {
        padding: 30px;
        color: #fff;
    }

    .event-body h4 {
        background: linear-gradient(to right, #8DC63F, #005B94);
        -webkit-background-clip: text;
        background-clip: text;
        color: transparent;
        font-size: 1.4rem;
        margin-bottom: 15px;
        font-weight: 700;
        letter-spacing: -0.02em;
    }

    .event-desc {
        color: #4a5568;
        line-height: 1.7;
        margin-bottom: 25px;
        font-size: 1.05rem;
    }

    .event-btn {
        display: inline-block;
        width: 100%;
        padding: 14px 28px;
        background: linear-gradient(135deg, #005B94, #8DC63F);
        color: white;
        text-decoration: none;
        border-radius: 14px;
        font-weight: 600;
        text-align: center;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        border: none;
        position: relative;
        overflow: hidden;
        font-size: 1.1rem;
        letter-spacing: 0.3px;
        box-shadow: 0 4px 15px rgba(0, 91, 148, 0.35);
    }

    .event-btn::before {
        content: '';
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: linear-gradient(90deg,
                transparent,
                rgba(255, 255, 255, 0.3),
                transparent);
        transition: 0.5s;
    }

    .event-btn:hover::before {
        left: 100%;
    }

    .event-btn:hover {
        transform: translateY(-3px);
        box-shadow:
            0 15px 25px -10px rgba(0, 91, 148, 0.5),
            0 0 15px rgba(141, 198, 63, 0.2);
        color: white;
        text-decoration: none;
    }

    @keyframes fadeOverlay {
        from {
            opacity: 0;
        }

        to {
            opacity: 1;
        }
    }

    @keyframes scaleIn {
        from {
            opacity: 0;
            transform: scale(0.9) translateY(20px);
        }

        to {
            opacity: 1;
            transform: scale(1) translateY(0);
        }
    }

    .event-content {
        animation: scaleIn 0.4s cubic-bezier(0.4, 0, 0.2, 1) forwards;
    }

    /* Overlay background gelap */
    .overlay {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.8);
        backdrop-filter: blur(8px);
        z-index: 999;
        animation: fadeOverlay 0.3s ease-in-out;
    }
    </style>
</head>

<body>

    <div id="vanta-bg"></div>
    <img src="image/kementrian.png" alt="Logo" class="logo" />

    <div class="login-container">
        <div class="login-header text-center">
            <img src="image\kementrian.png" alt="Logo Simagang" class="mb-4" />
            <h2><?php echo htmlspecialchars($welcomeText); ?></h2>
            <p class="text-muted mb-4"><?php echo htmlspecialchars($instructionText); ?></p>
        </div>
        <form class="login-form" method="POST" action="">
            <div class="form-group mb-4">
                <label for="email" class="sr-only">Email</label>
                <input type="email" class="form-control" id="email" name="gmail" placeholder="Email kamu" required>
            </div>
            <div class="form-group mb-4">
                <label for="password" class="sr-only">Password</label>
                <div class="input-group">
                    <input type="password" class="form-control" id="password" name="pass" placeholder="Password kamu"
                        required>
                    <div class="input-group-append">
                        <span class="input-group-text bg-transparent border-0" id="togglePassword">
                            <i class="fa fa-eye"></i>
                        </span>
                    </div>
                </div>
            </div>
            <button type="submit" class="btn btn-primary btn-block mb-4">Masuk</button>

            <!-- Tambahin button Google Sign-In di sini -->
            <div class="text-center">
                <p>atau</p>
                <div class="google-signin-container">
                    <div id="g_id_onload"
                        data-client_id="630202116221-u9o6kd6g5rf3gdj637pf924fs1r53gbu.apps.googleusercontent.com"
                        data-callback="handleCredentialResponse">
                    </div>
                    <div class="g_id_signin" data-type="standard" data-size="large" data-theme="outline"
                        data-text="sign_in_with" data-shape="rectangular" data-logo_alignment="left">
                    </div>
                </div>
            </div>

            <div id="error-message" class="alert alert-danger"
                style="<?php echo !empty($error) ? 'display: block;' : 'display: none;'; ?>"><?php echo $error; ?></div>
        </form>
        <div class="login-footer text-center">
            <p class="mb-0">
                <small><span id="copyright-info" data-toggle="popover" data-placement="top">© TIM PKL PRESMA
                        2024</span></small>
            </p>
        </div>
    </div>

    <!-- Popup Notifikasi Event -->
    <div class="event-popup" id="eventPopup">
        <div class="event-content">
            <div class="event-header">
                <h3>✨ Fitur Baru yang Keren!</h3>
                <button class="close-btn" onclick="closeEventPopup()">×</button>
            </div>
            <div class="event-body">
                <h4>Notifikasi Absen via Telegram</h4>
                <p class="event-desc">
                    Ga perlu takut lupa absen lagi! Sekarang SIMAGANG bisa kirim pengingat absen langsung ke Telegram
                    kamu.
                    Aktifin sekarang biar absensi kamu makin lancar! 🚀
                </p>
                <a href="https://t.me/NotifyMagangBot" target="_blank" class="event-btn">
                    Hubungkan dengan Telegram →
                </a>
            </div>
        </div>
    </div>

    <!-- Tambah div overlay -->
    <div class="overlay" id="overlay"></div>

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script>
    const togglePassword = document.getElementById("togglePassword");
    const password = document.getElementById("password");

    togglePassword.addEventListener("click", function() {
        const type =
            password.getAttribute("type") === "password" ? "text" : "password";
        password.setAttribute("type", type);
        this.querySelector("i").classList.toggle("fa-eye");
        this.querySelector("i").classList.toggle("fa-eye-slash");
    });

    // Tunggu sampe page load baru jalanin VANTA
    window.addEventListener('DOMContentLoaded', () => {
        // Start vanta effect
        VANTA.GLOBE({
            el: "#vanta-bg",
            mouseControls: true,
            touchControls: true,
            gyroControls: true,
            minHeight: 200.00,
            minWidth: 200.00,
            scale: 1.20,
            scaleMobile: 1.00,
            color: 0x005B94,
            color2: 0x8DC63F,
            size: 1.50,
            backgroundColor: 0xf8f9fa,
            points: 15.00,
            maxDistance: 25.00,
            spacing: 10.00,
            showDots: true,
            showLines: true,
            mouseEase: true,
            amplitude: 1.5,
            waveSpeed: 0.5,
            zoom: 0.65,
            rotation: [0, 0.5, 0],
            rotationSpeed: 0.2
        });

        // Hide loading screen after vanta is loaded
        setTimeout(() => {
            const loadingScreen = document.getElementById('loading-screen');
            loadingScreen.style.opacity = '0';
            setTimeout(() => {
                loadingScreen.style.display = 'none';
            }, 500);
        }, 1000);
    });

    $(function() {
        $('#copyright-info').popover({
            html: true,
            trigger: 'hover click',
            content: `
            <div>
              <h6>Tim Pengembang Simagang</h6>
              <ul>
                <li>Hadi - Lead Developer</li>
                <li>Ardy - UI/UX Designer</li>
                <li>Ihsan - UI/UX Designer</li>
                <li>Ferdy - Frontend Developer</li>
                <li>Faiz - Backend Developer</li>
              </ul>
              <p>Dikembangkan dengan ❤️ oleh Tim PKL SMK Prestasi Prima 2024</p>
            </div>
          `
        });
    });

    function handleCredentialResponse(response) {
        var xhr = new XMLHttpRequest();
        xhr.open('POST', 'google_login.php');
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        xhr.onload = function() {
            try {
                var jsonResponse = JSON.parse(xhr.responseText);
                if (jsonResponse.success) {
                    window.location.href = jsonResponse.redirect;
                } else {
                    document.getElementById('error-message').style.display = 'block';
                    document.getElementById('error-message').innerText = jsonResponse.message ||
                        'Gagal login pake Google';
                }
            } catch (e) {
                console.error('Error:', e);
                document.getElementById('error-message').style.display = 'block';
                document.getElementById('error-message').innerText = 'Ada masalah pas login';
            }
        };
        xhr.onerror = function() {
            document.getElementById('error-message').style.display = 'block';
            document.getElementById('error-message').innerText = 'Koneksi bermasalah';
        };
        xhr.send('credential=' + response.credential);
    }

    // Fungsi buat ngecek dan update counter popup
    function handleEventPopup() {
        let popupCount = parseInt(localStorage.getItem('popupCount')) || 0;

        if (popupCount < 10) {
            setTimeout(() => {
                document.getElementById('overlay').style.display = 'block';
                document.getElementById('eventPopup').style.display = 'block';
                popupCount++;
                localStorage.setItem('popupCount', popupCount);
            }, 2000);
        }
    }

    function closeEventPopup() {
        document.getElementById('overlay').style.display = 'none';
        document.getElementById('eventPopup').style.display = 'none';
    }

    // Jalanin fungsi pas page load
    handleEventPopup();
    </script>

    <!-- Tambahin ini setelah <body> -->
    <div id="loading-screen">
        <div class="atomic-loader">
            <div class="electron"></div>
            <div class="electron"></div>
            <div class="electron"></div>
            <div class="nucleus"></div>
        </div>
    </div>

</body>

</html>

</body>

</html>