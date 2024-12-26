<?php
session_start();
require('koneksi.php');
require('auth.php');

// Fetch sekolah dari database
$sekolahQuery = "SELECT nama, lokasi FROM sekolah";
$sekolahResult = mysqli_query($conn, $sekolahQuery);
$sekolahData = mysqli_fetch_all($sekolahResult, MYSQLI_ASSOC);

// Fetch guru dengan role = 3 dari database
$guruQuery = "SELECT nama FROM akun WHERE role = 3";
$guruResult = mysqli_query($conn, $guruQuery);
$guruData = mysqli_fetch_all($guruResult, MYSQLI_ASSOC);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $asal_sekolah = $_POST['asal_sekolah'];
    $alamat_sekolah = $_POST['alamat_sekolah'];
    $guru_pendamping = $_POST['guru_pendamping'];
    $no_telp_guru = $_POST['no_telp_guru'];
    $magang_masuk = $_POST['magang_masuk'];
    $magang_keluar = $_POST['magang_keluar'];
    $surat_tugas = $_FILES['surat_tugas'];
    $no_surat = $_POST['no_surat'];
    $no_surat_p = $_POST['no_surat_p'];
    $surat_persetujuan = $_FILES['surat_persetujuan'];

    $nama = $_POST['nama'];
    $tanggal_lahir = $_POST['tanggal_lahir'];
    $nis = $_POST['nis'];
    $nik = $_POST['nik'];
    $no_telp = $_POST['no_telp'];
    $gmail = $_POST['gmail'];
    $img_dir = $_FILES['img_dir'];

    // Upload file surat tugas sebagai longblob
    $surat_tugas_content = file_get_contents($surat_tugas['tmp_name']);
    $surat_tugas_content = addslashes($surat_tugas_content);

    // Upload file surat persetujuan sebagai longblob
    $surat_persetujuan_content = file_get_contents($surat_persetujuan['tmp_name']);
    $surat_persetujuan_content = addslashes($surat_persetujuan_content);

    foreach ($nama as $index => $name) {
        $tanggal_lahir_val = $tanggal_lahir[$index];
        $nis_val = $nis[$index];
        $nik_val = $nik[$index];
        $no_telp_val = $no_telp[$index];
        $gmail_val = $gmail[$index];
        $img_dir_val = $img_dir['tmp_name'][$index];

        // Cek apakah data sudah ada di database
        $cek_sql = "SELECT * FROM akun WHERE nis = '$nis_val' OR nik = '$nik_val' OR gmail = '$gmail_val'";
        $result = mysqli_query($conn, $cek_sql);
        if (mysqli_num_rows($result) > 0) {
            echo "<script>Swal.fire({
                title: 'Waduh!',
                text: 'Data $name udah ada nih. Coba cek lagi ya!',
                icon: 'warning',
                confirmButtonText: 'Oke'
            });</script>";
            continue; // Skip insert jika data sudah ada
        }

        // Upload file img_dir sebagai longblob
        $img_dir_content = file_get_contents($img_dir_val);
        $img_dir_content = addslashes($img_dir_content);

        // Generate password
        $tahun_lahir = substr($tanggal_lahir_val, 0, 4);
        $password_raw = substr($name, 0, 4) . $tahun_lahir;
        $password_hashed = password_hash($password_raw, PASSWORD_BCRYPT);

        // Default role
        $role = 1;

        // Insert data ke tabel
        $sql = "INSERT INTO akun (asal_sekolah, alamat_sekolah, guru_pendamping, no_telp_guru, magang_masuk, magang_keluar, surat_tugas, nama, tanggal_lahir, nis, nik, no_telp, gmail, img_dir, role, pass, no_surat, no_surat_p, surat_persetujuan)
                VALUES ('$asal_sekolah', '$alamat_sekolah', '$guru_pendamping', '$no_telp_guru', '$magang_masuk', '$magang_keluar', '$surat_tugas_content', '$name', '$tanggal_lahir_val', '$nis_val', '$nik_val', '$no_telp_val', '$gmail_val', '$img_dir_content', '$role', '$password_hashed', '$no_surat', '$no_surat_p', '$surat_persetujuan_content')";
        if (mysqli_query($conn, $sql)) {
            echo "<script>Swal.fire({
                title: 'Mantap!',
                text: 'Data $name berhasil ditambahin nih!',
                icon: 'success',
                confirmButtonText: 'Sip'
            });</script>";
        } else {
            $error_message = mysqli_error($conn);
            echo "<script>Swal.fire({
                title: 'Yah, Gagal!',
                text: 'Ada error nih: $error_message',
                icon: 'error',
                confirmButtonText: 'Oke deh'
            });</script>";
        }
    }

    mysqli_close($conn);
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Akun</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.0.3/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="fontawesome/css/fontawesome.min.css">
    <!-- Memastikan SweetAlert di-load -->
    <link rel="shortcut icon" href="image\kementrian.png">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.1/css/all.min.css" rel="stylesheet">
    <style>
        .input-group {
            margin-bottom: 10px;
            border: 1px solid #e2e8f0;
            /* light gray border */
            padding: 10px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            /* subtle shadow */
        }

        .secondary-data-table {
            display: grid;
            grid-template-columns: repeat(7, 1fr) 50px;
            /* Sesuaikan lebar kolom */
            gap: 10px;
            margin-top: 20px;

        }

        .secondary-data-table input,
        .secondary-data-table label {
            width: 100%;
            box-sizing: border-box;
            padding: 6px;
            /* Menyesuaikan padding */

            font-size: 0.875rem;
            /* Menyesuaikan ukuran font */
        }

        .secondary-data-header {
            display: grid;
            grid-template-columns: repeat(7, 1fr) 50px;
            /* Sesuaikan lebar kolom untuk header */
            gap: 10px;
            margin-top: 20px;
            font-weight: bold;
            color: #4a5568;
            border: 1px solid #cbd5e0;
            /* Warna border tabel */
            padding: 6px;
            /* Menyesuaikan padding */
            font-size: 0.875rem;
            /* Menyesuaikan ukuran font */
        }

        .file-upload-wrapper {
            position: relative;
            overflow: hidden;
            display: inline-block;
            cursor: pointer;
            color: #4a5568;
            background-color: #f7fafc;
            border: 2px dashed #cbd5e0;
            border-radius: 8px;
            padding: 10px;
            text-align: center;
            transition: background-color 0.3s;
            /* Smooth transition for background color change */
        }

        .file-upload-wrapper:hover {
            background-color: #edf2f7;
        }

        .file-upload-input {
            position: absolute;
            left: 0;
            top: 0;
            right: 0;
            bottom: 0;
            width: 100%;
            height: 100%;
            opacity: 0;
            cursor: pointer;
        }

        .file-upload-icon {
            font-size: 24px;
            margin-bottom: 8px;
        }

        .file-uploaded {
            background-color: #e2e8f0;
            /* Light blue background to indicate file is uploaded */
            border: 2px solid #4a5568;
            /* Darker border */
        }

        .file-uploaded .file-upload-icon {
            color: #4a5568;
            /* Optional: change icon color */
        }

        .secondary-data-table button {
            height: 100%;
            /* Biar tingginya sama dengan input fields */
            width: 100%;
            /* Full width */
            display: flex;
            align-items: center;
            justify-content: center;
            margin-top: 0;
            /* Hapus margin atas */
            padding: 6px;
            /* Sesuaikan padding */
        }

        .file-upload-wrapper-surat {
            position: relative;
            overflow: hidden;
            display: inline-block;
            cursor: pointer;
            color: #4a5568;
            width: 100%;
            background-color: #f7fafc;
            border: 2px dashed #cbd5e0;
            border-radius: 8px;
            padding: 10px;
            text-align: center;
            transition: background-color 0.3s;
            /* Smooth transition for background color change */
        }

        .file-upload-wrapper-surat:hover {
            background-color: #edf2f7;
        }

        .file-upload-input-surat {
            position: absolute;
            left: 0;
            top: 0;
            right: 0;
            bottom: 0;
            width: 100%;
            height: 100%;
            opacity: 0;
            cursor: pointer;
        }

        .file-upload-icon-surat {
            font-size: 24px;
            margin-bottom: 8px;
        }

        .file-uploaded-surat {
            background-color: #e2e8f0;
            /* Light blue background to indicate file is uploaded */
            border: 2px solid #4a5568;
            /* Darker border */
        }

        .file-uploaded-surat .file-upload-icon-surat {
            color: #4a5568;
            /* Optional: change icon color */
        }

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
    <script>
        //digunakan untuk nambahin inputan data sekendari
        function addSecondaryData() {
            const container = document.getElementById('secondary-data-container');
            const div = document.createElement('div');
            div.className = 'secondary-data-table';
            div.innerHTML = `
                <input type="text" name="nama[]" placeholder="Nama" required class="border px-3 py-2 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500" />
                <input type="date" name="tanggal_lahir[]" required class="border px-3 py-2 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500" />
                <input type="text" name="nis[]" placeholder="NIS" required class="border px-3 py-2 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500" />
                <input type="text" name="nik[]" placeholder="NIK" required class="border px-3 py-2 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500" />
                <input type="text" name="no_telp[]" placeholder="No Telp" required class="border px-3 py-2 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500" />
                <input type="email" name="gmail[]" placeholder="Gmail" required class="border px-3 py-2 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500" />
                <div class="file-upload-wrapper" tabindex="0">
                    <i class="file-upload-icon fas fa-cloud-upload-alt"></i>
                    <span id="file-upload-filename"></span>
                    <input type="file" name="img_dir[]" accept="image/*" required class="file-upload-input" onchange="updateFileUpload(this)">
                </div>
                <button type="button" onclick="removeSecondaryData(this)" class="mt-4 px-4 py-2 bg-red-500 text-white rounded hover:bg-red-600"><i class="fas fa-trash-alt"></i></button>
            `;
            container.appendChild(div);
        }

        function removeSecondaryData(button) {
            button.parentElement.remove();
        }

        function updateFileUpload(input) {
            const fileWrapper = input.closest('.file-upload-wrapper');
            if (input.files && input.files.length > 0) {
                fileWrapper.style.backgroundColor = '#ccffcc'; // Soft green background
            } else {
                fileWrapper.style.backgroundColor = '#f7fafc'; // Reset to original background color
            }
        }

        function updateFileUploadSurat(input) {
            const fileWrapper = input.closest('.file-upload-wrapper-surat');
            if (input.files && input.files.length > 0) {
                fileWrapper.style.backgroundColor = '#ccffcc'; // Soft green background
            } else {
                fileWrapper.style.backgroundColor = '#f7fafc'; // Reset to original background color
            }
        }

        function updateFileUploadPersetujuan(input) {
            const fileWrapper = input.closest('.file-upload-wrapper-surat');
            if (input.files && input.files.length > 0) {
                fileWrapper.style.backgroundColor = '#ccffcc'; // Soft green background
            } else {
                fileWrapper.style.backgroundColor = '#f7fafc'; // Reset to original background color
            }
        }

        function updateLokasi() {
            const sekolahSelect = document.getElementById('asal_sekolah');
            const lokasiInput = document.getElementById('alamat_sekolah');
            const selectedOption = sekolahSelect.options[sekolahSelect.selectedIndex];
            lokasiInput.value = selectedOption.getAttribute('data-lokasi');
        }
    </script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function() {
            // Show content and hide loader when page is ready
            $('.loader-container').fadeOut('slow');
            $('.content').fadeIn('slow');

            $('form').on('submit', function(e) {
                e.preventDefault();

                // Show loader when form is submitted
                $('.loader-container').fadeIn('fast');

                var formData = new FormData(this);

                $.ajax({
                    url: 'process-tambah-murid.php',
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    dataType: 'json', // Tambahin ini biar jQuery otomatis parse JSON
                    success: function(result) {
                        // Hide loader on success
                        $('.loader-container').fadeOut('fast');

                        if (result.status === 'success') {
                            Swal.fire({
                                title: 'Mantap!',
                                text: 'Data berhasil ditambahin nih!',
                                icon: 'success',
                                showConfirmButton: false,
                                timer: 1500 // Auto close after 1.5 seconds
                            }).then(() => {
                                // Redirect ke halaman yang lo mau
                                window.location.href = 'manage-admin.php'; // Ganti sesuai halaman tujuan lo
                            });
                        } else {
                            Swal.fire({
                                title: 'Yah, Gagal!',
                                text: 'Ada error nih: ' + result.message,
                                icon: 'error',
                                confirmButtonText: 'Oke deh'
                            });
                        }
                    },
                    error: function(jqXHR, textStatus, errorThrown) {
                        // Hide loader on error
                        $('.loader-container').fadeOut('fast');

                        console.error("AJAX error:", textStatus, errorThrown);
                        Swal.fire({
                            title: 'Waduh!',
                            text: 'Koneksi error nih. Status: ' + textStatus,
                            icon: 'error',
                            confirmButtonText: 'Siap'
                        });
                    }
                });
            });
        });
    </script>
</head>

<body class="bg-gray-100 p-5">
    <!-- Tambah loader di awal body -->
    <div class="loader-container">
        <div class="loader"></div>
    </div>

    <!-- Wrap semua content dalam div.content -->
    <div class="content">
        <form action="" method="post" enctype="multipart/form-data" class="max-w-7xl mx-auto bg-white p-8 rounded-lg shadow-md">
            <h2 class="text-2xl font-bold text-gray-800 mb-6">Tambah Data Murid</h2>

            <div class="mb-8">
                <h3 class="text-xl font-semibold text-gray-700 mb-4">Data Utama:</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Asal Sekolah:</label>
                        <select name="asal_sekolah" id="asal_sekolah" onchange="updateLokasi()" required class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                            <?php foreach ($sekolahData as $sekolah): ?>
                                <option value="<?php echo $sekolah['nama']; ?>" data-lokasi="<?php echo $sekolah['lokasi']; ?>"><?php echo $sekolah['nama']; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Alamat Sekolah:</label>
                        <input type="text" name="alamat_sekolah" id="alamat_sekolah" readonly required class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 bg-gray-100">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Guru Pendamping:</label>
                        <input type="text" name="guru_pendamping" required class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">No Telp Guru:</label>
                        <input type="text" name="no_telp_guru" required class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Magang Masuk:</label>
                        <input type="date" name="magang_masuk" required class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Magang Keluar:</label>
                        <input type="date" name="magang_keluar" required class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Nomor Surat Tugas:</label>
                        <input type="text" name="no_surat" required class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Surat Tugas:</label>
                        <div class="flex items-center">
                            <input type="file" name="surat_tugas" id="surat_tugas" accept="application/pdf" required class="hidden" onchange="updateFileName(this, 'file-name-tugas')">
                            <label for="surat_tugas" class="cursor-pointer bg-white py-2 px-3 border border-gray-300 rounded-md shadow-sm text-sm leading-4 font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                Pilih File
                            </label>
                            <span id="file-name-tugas" class="ml-3 text-sm text-gray-500">Belum ada file dipilih</span>
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Nomor Surat Persetujuan Magang:</label>
                        <input type="text" name="no_surat_p" required class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Surat Persetujuan Magang:</label>
                        <div class="flex items-center">
                            <input type="file" name="surat_persetujuan" id="surat_persetujuan" accept="application/pdf" required class="hidden" onchange="updateFileName(this, 'file-name-persetujuan')">
                            <label for="surat_persetujuan" class="cursor-pointer bg-white py-2 px-3 border border-gray-300 rounded-md shadow-sm text-sm leading-4 font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                Pilih File
                            </label>
                            <span id="file-name-persetujuan" class="ml-3 text-sm text-gray-500">Belum ada file dipilih</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Bagian Data Sekunder tetap sama -->
            <h3 class="mt-6 mb-2 font-bold text-gray-700">Data Sekunder:</h3>
            <button type="button" onclick="addSecondaryData()" class="mt-4 px-4 py-2 bg-green-500 text-white rounded hover:bg-green-600">Tambah Data Sekunder</button>
            <button type="submit" class="mt-4 px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600">Kirim</button>
            <button type="button" onclick="window.location.href='manage-admin.php'" class="mt-4 px-4 py-2 bg-red-500 text-white rounded hover:bg-red-600">Batal</button>
            <div class="secondary-data-header">
                <label>Nama</label>
                <label>Tanggal Lahir</label>
                <label>NIS</label>
                <label>NIK</label>
                <label>No Telp</label>
                <label>Gmail</label>
                <label>Foto</label>
            </div>
            <div id="secondary-data-container">
                <div class="secondary-data-table">
                    <input type="text" name="nama[]" placeholder="Nama" required class="border px-3 py-2 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500" />
                    <input type="date" name="tanggal_lahir[]" required class="border px-3 py-2 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500" />
                    <input type="text" name="nis[]" placeholder="NIS" required class="border px-3 py-2 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500" />
                    <input type="text" name="nik[]" placeholder="NIK" required class="border px-3 py-2 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500" />
                    <input type="text" name="no_telp[]" placeholder="No Telp" required class="border px-3 py-2 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500" />
                    <input type="email" name="gmail[]" placeholder="Gmail" required class="border px-3 py-2 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500" />
                    <div class="file-upload-wrapper" tabindex="0">
                        <i class="file-upload-icon fas fa-cloud-upload-alt"></i>
                        <span id="file-upload-filename"></span>
                        <input type="file" name="img_dir[]" accept="image/*" required class="file-upload-input" onchange="updateFileUpload(this)">
                    </div>
                    <button type="button" onclick="removeSecondaryData(this)" class="mt-4 px-4 py-2 bg-red-500 text-white rounded hover:bg-red-600"><i class="fas fa-trash-alt"></i></button>
                </div>
            </div>

        </form>

        <script>
            function updateFileName(input, spanId) {
                const fileName = input.files[0]?.name;
                document.getElementById(spanId).textContent = fileName || 'Belum ada file dipilih';
            }
        </script>
    </div>
</body>

</html>