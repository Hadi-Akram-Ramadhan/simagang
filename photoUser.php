<?php
session_start(); // Memulai session
require_once('koneksi.php');
require('auth.php');
require('navUser.php');

// Cek jika role sudah terisi dan role bukan 'admin'
if (!isset($_SESSION['role']) || $_SESSION['role'] === '2') {
    header("Location: index.php"); // Redirect ke login.php
    exit();
}

$nama_user = $_SESSION['nama']; // Mengambil nama user dari session

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    // Validasi data
    if (!isset($data['image']) || !isset($data['latitude']) || !isset($data['longitude'])) {
        echo "Data tidak lengkap.";
        exit;
    }

    $image = $data['image'];
    $latitude = $data['latitude'];
    $longitude = $data['longitude'];

    $image = str_replace('data:image/png;base64,', '', $image);
    $image = str_replace(' ', '+', $image);
    $imageData = base64_decode($image);

    // Cek jumlah foto yang sudah diambil user pada hari ini
    $sql = "SELECT COUNT(*) as count FROM images WHERE user = ? AND DATE(waktu) = CURDATE()";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $nama_user);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    if ($row['count'] >= 2) {
        echo "Wah, kamu udah ambil foto 2 kali hari ini. Cukup dulu ya, besok lagi.";
        exit;
    }

    // Simpan foto jika belum mencapai batas
    // Query untuk menyimpan data gambar sebagai LONGBLOB
    $sql = "INSERT INTO images (name, img_dir, latitude, longitude, user, waktu) VALUES (?, ?, ?, ?, ?, NOW())";
    $stmt = $conn->prepare($sql);
    // Bind parameter dengan tipe data baru untuk LONGBLOB
    $stmt->bind_param("sbdds", $nama_user, $null, $latitude, $longitude, $nama_user);
    $stmt->send_long_data(1, $imageData); // Mengirim data gambar sebagai long data

    if ($stmt->execute()) {
        echo "Mantap! Foto kamu udah berhasil diupload dan disimpan.";
    } else {
        echo "Ups, ada masalah nih: " . $sql . "<br>" . $conn->error;
    }

    $stmt->close();
    $conn->close();
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Foto Absen</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #fafafa;
            margin: 0;
            padding: 0;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        .centered-content {
            flex: 1;
            display: flex;
            flex-direction: column;

            align-items: center;
            background-color: #fafafa;
            padding: 40px 20px;
            margin-top: 60px;
        }

        .camera-container {
            width: 100%;
            max-width: 500px;
            margin-bottom: 30px;
            position: relative;
            overflow: hidden;
            border-radius: 20px;
        }

        video {
            width: 100%;
            height: auto;
            border-radius: 20px;
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
            transform: scaleX(-1);
        }

        canvas {
            display: none;
        }

        button {
            background: linear-gradient(135deg, #004B8F, #0072BC);
            color: white;
            padding: 14px 28px;
            margin: 15px 0;
            border: none;
            border-radius: 50px;
            cursor: pointer;
            box-shadow: 0 4px 8px rgba(0, 75, 143, 0.3);
            transition: all 0.3s ease;
            font-size: 18px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        button:hover {
            background: linear-gradient(135deg, #002E5D, #004B8F);
            transform: translateY(-3px);
            box-shadow: 0 6px 12px rgba(0, 75, 143, 0.4);
        }



        p {
            font-size: 18px;
            margin-bottom: 15px;
            text-align: center;
            color: #484b6a;
            font-weight: 600;
            padding: 10px;
        }

        .absen {
            font-size: 24px;
            font-weight: 600;
            color: #484b6a;
            font-family: 'Poppins', sans-serif;
        }

        @media (max-width: 768px) {
            .centered-content {
                padding: 20px 10px;
                margin-top: 70px;
            }

            absen {
                font-size: 22px;
                margin-bottom: 10px;
            }

            p {
                font-size: 16px;
                margin-bottom: 20px;
            }

            .camera-container {
                max-width: 90%;
                max-height: 100px;
                margin-bottom: 20px;
            }

            button {
                padding: 12px 20px;
                font-size: 16px;
                width: 80%;
                max-width: 250px;

            }
        }

        @media (max-width: 480px) {
            .centered-content {
                padding: 15px 5px;
            }

            h1 {
                font-size: 20px;
            }

            p {
                font-size: 16px;
            }

            .camera-container {
                max-width: 90%;
                max-height: 22rem;
                margin-bottom: 20px;
            }

            button {
                padding: 10px 18px;
                font-size: 14px;
            }
        }

        .navbar {
            background-color: #fafafa !important;
            border-bottom: 1px solid #e0e0e0;
            z-index: 10000;
        }

        .swal2-container {
            z-index: 100000 !important;
        }

        /* Tambah CSS loader */
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
            z-index: 9999999;
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

</head>

<body>
    <!-- Tambah loader -->
    <div class="loader-container">
        <div class="loader"></div>
    </div>

    <!-- Wrap existing content -->
    <div class="content">
        <div class="centered-content">
            <h1 class="absen">Ambil Foto Absen</h1>
            <p><i>Kesempatan ambil foto 2 kali (Absen Masuk & Absen Keluar)</i></p>

            <div class="camera-container">
                <video id="video" autoplay></video>
            </div>
            <button id="snap">Foto</button>
            <canvas id="canvas"></canvas>

            <script>
                const video = document.getElementById('video');
                const snap = document.getElementById('snap');
                const canvas = document.getElementById('canvas');

                if (navigator.mediaDevices && navigator.mediaDevices.getUserMedia) {
                    navigator.mediaDevices.getUserMedia({
                        video: true
                    }).then(stream => {
                        video.srcObject = stream;
                        video.play();
                    }).catch(error => {
                        console.error('Error accessing the camera:', error);
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal mengakses kamera',
                            text: error.message
                        });
                    });
                }

                snap.addEventListener('click', () => {
                    snap.disabled = true;

                    const scaleFactor = 0.2; // Reduce resolution to 50%
                    canvas.width = video.videoWidth * scaleFactor;
                    canvas.height = video.videoHeight * scaleFactor;
                    const ctx = canvas.getContext('2d');
                    ctx.drawImage(video, 0, 0, canvas.width, canvas.height);
                    const data = canvas.toDataURL('image/png');

                    if ("geolocation" in navigator) {
                        navigator.geolocation.getCurrentPosition(function(position) {
                            const latitude = position.coords.latitude;
                            const longitude = position.coords.longitude;

                            fetch('', { // POST request to the same page
                                    method: 'POST',
                                    body: JSON.stringify({
                                        image: data,
                                        latitude: latitude,
                                        longitude: longitude
                                    }),
                                    headers: {
                                        'Content-Type': 'application/json'
                                    }
                                })
                                .then(response => response.text())
                                .then(data => {
                                    if (data.includes("Wah, kamu udah ambil foto 2 kali hari ini")) {
                                        Swal.fire({
                                            icon: 'warning',
                                            title: 'Udah Cukup Hari Ini',
                                            text: 'Kamu udah ambil foto absen 2 kali hari ini. Besok aja ya ambil lagi.'
                                        });
                                    } else {
                                        Swal.fire({
                                            icon: 'success',
                                            title: 'Foto Berhasil Diambil',
                                            showConfirmButton: false,
                                            timer: 1500
                                        });
                                    }
                                    snap.disabled = false;
                                })
                                .catch((error) => {
                                    console.error('Error:', error);
                                    Swal.fire({
                                        icon: 'error',
                                        title: 'Waduh, Gagal Upload!',
                                        text: 'Ada masalah nih: ' + error.message
                                    });
                                    snap.disabled = false;
                                });
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Wah, Browser Kamu Gak Support Geolocation'
                        });
                    }
                });

                // Tambah script untuk handle loading
                window.addEventListener('load', function() {
                    const loader = document.querySelector('.loader-container');
                    const content = document.querySelector('.content');

                    setTimeout(() => {
                        loader.style.display = 'none';
                        content.style.display = 'block';
                    }, 1000);
                });
            </script>
        </div>
</body>

</html>