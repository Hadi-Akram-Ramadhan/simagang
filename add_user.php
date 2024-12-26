<?php
header('Content-Type: application/json');

// Matiin semua error reporting
error_reporting(0);
ini_set('display_errors', 0);

// Start output buffering
ob_start();

require('koneksi.php');

ini_set('log_errors', 1);
ini_set('error_log', '/path/to/your/php-error.log'); // Ganti path-nya sesuai server lo

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $addNama = $_POST['addNama'];
    $addGmail = $_POST['addGmail'];
    $addNik = $_POST['addNik'];
    $addRole = $_POST['addRole'];
    $addNoTelp = $_POST['addNoTelp'];
    $addSekolah = isset($_POST['addSekolah']) ? $_POST['addSekolah'] : '';
    $addPeriode = isset($_POST['addPeriode']) ? $_POST['addPeriode'] : '';

    error_log("Received POST request: " . json_encode($_POST));

    $addPass = substr($addNama, 0, 4) . substr($addNik, -4);
    $hashedPass = password_hash($addPass, PASSWORD_DEFAULT);

    // Validasi input
    if (empty($addNama) || empty($addGmail) || empty($addNik) || empty($addRole) || empty($addNoTelp)) {
        $result = ['success' => false, 'message' => 'Semua field harus diisi, kecuali sekolah untuk admin'];
    } else {
        // Validasi khusus untuk guru
        if ($addRole == '3') {
            if (empty($addSekolah) || empty($addPeriode)) {
                $result = ['success' => false, 'message' => 'Asal sekolah dan periode magang harus diisi untuk akun guru'];
            } else {
                // Check if the school already has a teacher for the selected period
                $stmt = $conn->prepare("SELECT COUNT(*) FROM akun WHERE role = 3 AND asal_sekolah = ? AND magang_masuk = ? AND magang_keluar = ?");
                list($magang_masuk, $magang_keluar) = explode(' - ', $addPeriode);
                $stmt->bind_param("sss", $addSekolah, $magang_masuk, $magang_keluar);
                $stmt->execute();
                $result = $stmt->get_result();
                $count = $result->fetch_row()[0];
                $stmt->close();

                if ($count > 0) {
                    $result = ['success' => false, 'message' => 'Udah ada guru buat periode ini. Gak bisa nambah lagi. Coba pilih periode lain atau ubah guru yang udah ada.'];
                } else {
                    // Proceed with adding the teacher
                    $result = addUser($conn, $addNama, $addGmail, $addNik, $hashedPass, $addRole, $addSekolah, $addNoTelp, $magang_masuk, $magang_keluar);
                }
            }
        } else {
            // For non-teacher roles
            if ($addRole == '2') {
                $addSekolah = ''; // Set empty string for admin
            }
            $result = addUser($conn, $addNama, $addGmail, $addNik, $hashedPass, $addRole, $addSekolah, $addNoTelp, '', '');
        }
    }
} else {
    http_response_code(405); // Method Not Allowed
    $result = ['success' => false, 'message' => 'Invalid request method'];
}

$conn->close();

$response = ob_get_clean();

// Log unexpected output
if (!empty($response)) {
    error_log("Unexpected output in add_user.php: " . $response);
}

// Pastiin cuma JSON yang dikirim balik
echo json_encode($result);
exit;

function addUser($conn, $nama, $gmail, $nik, $pass, $role, $sekolah, $noTelp, $magang_masuk, $magang_keluar) {
    $stmt = $conn->prepare("INSERT INTO akun (nama, gmail, nik, pass, role, asal_sekolah, no_telp, magang_masuk, magang_keluar) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssssssss", $nama, $gmail, $nik, $pass, $role, $sekolah, $noTelp, $magang_masuk, $magang_keluar);

    if ($stmt->execute()) {
        error_log("User added successfully: " . $nama);
        $result = ['success' => true, 'message' => 'Akun baru udah berhasil ditambahin nih'];
    } else {
        error_log("Error adding user: " . $stmt->error);
        $result = ['success' => false, 'message' => 'Ada error nih: ' . $stmt->error];
    }

    $stmt->close();
    return $result;
}