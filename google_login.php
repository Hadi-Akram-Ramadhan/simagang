<?php
session_start();
require_once 'vendor/autoload.php';
require_once 'koneksi.php';

$client = new Google_Client(['client_id' => '630202116221-u9o6kd6g5rf3gdj637pf924fs1r53gbu.apps.googleusercontent.com']);

try {
    $payload = $client->verifyIdToken($_POST['credential']);
    if ($payload) {
        $gmail = $payload['email'];

        $sql = "SELECT * FROM akun WHERE gmail = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $gmail);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows == 1) {
            $user = $result->fetch_assoc();
            
            // Cek masa aktif magang untuk role murid (1)
            if ($user['role'] === '1') {
                $today = new DateTime();
                $magang_keluar = new DateTime($user['magang_keluar']);
                
                if ($today > $magang_keluar) {
                    $response = ['success' => false, 'message' => 'Waduh, masa magang kamu sudah habis nih. Hubungi admin ya!'];
                    header('Content-Type: application/json');
                    echo json_encode($response);
                    exit();
                }
            }

            switch ($user['role']) {
                case '1':
                    $_SESSION['user'] = $user;
                    $_SESSION['role'] = $user['role'];
                    $_SESSION['nama'] = $user['nama'];
                    $response = ['success' => true, 'redirect' => 'homeUser.php'];
                    break;
                case '2':
                    $_SESSION['admin'] = $user;
                    $_SESSION['role'] = $user['role'];
                    $_SESSION['nama'] = $user['nama'];
                    $response = ['success' => true, 'redirect' => 'admin.php'];
                    break;
                case '3':
                    $_SESSION['teacher'] = $user;
                    $_SESSION['role'] = $user['role'];
                    $_SESSION['nama'] = $user['nama'];
                    $_SESSION['sekolah'] = $user['asal_sekolah'];
                    $response = ['success' => true, 'redirect' => 'homeTeacher.php'];
                    break;
                case '4':
                    $_SESSION['teacher'] = $user;
                    $_SESSION['role'] = $user['role'];
                    $_SESSION['nama'] = $user['nama'];
                    $response = ['success' => true, 'redirect' => 'homePemb.php'];
                    break;
                default:
                    $response = ['success' => false, 'message' => 'Role ga valid'];
            }
        } else {
            $response = ['success' => false, 'message' => 'Hmm, kayaknya kamu belum terdaftar di sistem nih. Udah yakin punya akun?'];
        }
    } else {
        $response = ['success' => false, 'message' => 'Token ga valid'];
    }
} catch (Exception $e) {
    $response = ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
}

header('Content-Type: application/json');
echo json_encode($response);