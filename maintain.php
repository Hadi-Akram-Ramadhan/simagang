<?php
// Koneksi ke database
include 'koneksi.php';

if (isset($_POST['compress_images'])) {
    // Query untuk mengambil semua data gambar yang akan dikompres
    $query = "SELECT user, waktu, img_dir FROM images";
    $result = mysqli_query($conn, $query);

    while ($row = mysqli_fetch_assoc($result)) {
        $imgData = $row['img_dir'];

        // Membuat image dari string binari
        $image = imagecreatefromstring($imgData);

        if ($image !== false) {
            // Output buffering untuk mendapatkan data gambar yang dikompresi
            ob_start();
            imagejpeg($image, null, 75); // 75 adalah kualitas kompresi
            $compressedImage = ob_get_contents();
            ob_end_clean();

            // Update gambar yang sudah dikompresi ke database
            $updateQuery = "UPDATE images SET img_dir = ? WHERE user = ? AND waktu = ?";
            $stmt = mysqli_prepare($conn, $updateQuery);
            mysqli_stmt_bind_param($stmt, 'sss', $compressedImage, $row['user'], $row['waktu']);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);

            // Menghancurkan image dari memory untuk menghemat resource
            imagedestroy($image);
        }
    }

    echo "<script>alert('Semua gambar berhasil dikompresi!');</script>";
}

// Mendapatkan nomor halaman dari query string, jika tidak ada, set ke halaman 1
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 50; // Jumlah data per halaman
$offset = ($page - 1) * $limit;

// Query untuk mengambil data dengan batasan limit dan offset
$query = "SELECT user, waktu, img_dir FROM images LIMIT $limit OFFSET $offset";
$result = mysqli_query($conn, $query);

// Menampilkan data
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Images dengan Kompresi</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            color: #333;
        }
        .container {
            width: 90%;
            margin: auto;
            overflow: hidden;
        }
        table {
            width: 100%;
            margin: 20px 0;
            border-collapse: collapse;
        }
        table, th, td {
            border: 1px solid #ddd;
        }
        th, td {
            padding: 10px;
            text-align: left;
        }
        th {
            background-color: #333;
            color: #fff;
        }
        .pagination {
            margin: 20px 0;
            text-align: center;
        }
        .pagination a {
            color: #333;
            padding: 8px 16px;
            text-decoration: none;
            border: 1px solid #ddd;
            margin: 0 4px;
        }
        .pagination a.active {
            background-color: #333;
            color: #fff;
            border: 1px solid #333;
        }
        .compress-btn {
            margin: 20px 0;
            text-align: center;
        }
        .compress-btn button {
            padding: 10px 20px;
            font-size: 16px;
            color: #fff;
            background-color: #333;
            border: none;
            cursor: pointer;
        }
        .compress-btn button:hover {
            background-color: #555;
        }
    </style>
</head>
<body>

<div class="container">
    <h1>Data Images dengan Kompresi</h1>
    
    <!-- Form untuk mengompres semua gambar -->
    <div class="compress-btn">
        <form method="post">
            <button type="submit" name="compress_images">Kompres Semua Gambar</button>
        </form>
    </div>

    <table>
        <thead>
            <tr>
                <th>User</th>
                <th>Waktu</th>
                <th>Image</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = mysqli_fetch_assoc($result)) { ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['user']); ?></td>
                    <td><?php echo htmlspecialchars($row['waktu']); ?></td>
                    <td>
                        <?php
                        // Menampilkan gambar
                        $imgData = $row['img_dir'];
                        echo '<img src="data:image/jpeg;base64,' . base64_encode($imgData) . '" width="100" />';
                        ?>
                    </td>
                </tr>
            <?php } ?>
        </tbody>
    </table>

    <div class="pagination">
        <?php
        // Menentukan jumlah halaman
        $totalQuery = "SELECT COUNT(*) AS total FROM images";
        $totalResult = mysqli_query($conn, $totalQuery);
        $totalData = mysqli_fetch_assoc($totalResult)['total'];
        $totalPages = ceil($totalData / $limit);

        // Menampilkan link halaman
        for ($i = 1; $i <= $totalPages; $i++) {
            echo '<a href="?page=' . $i . '" class="' . ($i == $page ? 'active' : '') . '">' . $i . '</a>';
        }
        ?>
    </div>
</div>

</body>
</html>
