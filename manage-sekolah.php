<?php
session_start();
require('koneksi.php');
require('auth.php');
require('navAdmin.php');

// Ambil data sekolah
$schoolSql = "SELECT nama, lokasi, email, no_telp FROM sekolah ORDER BY nama";
$schoolResult = $conn->query($schoolSql);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Sekolah</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <style>
        body {
            background-color: #ffffff;
            color: #484b6a;
            font-family: 'Poppins', sans-serif;
            margin: 0;
            padding: 0;
        }

        h2 {
            font-family: 'Poppins', sans-serif;
            padding-top: 10px;
            color: #333;
            font-weight: 500;
            font-size: 20px;
            letter-spacing: -0.5px;
        }

        .container {
            margin-top: 80px;
            display: flex;
            justify-content: center;
            align-items: flex-start;
            min-height: 100vh;
            padding: 20px;
            flex-wrap: wrap;
        }

        .info-box-wrapper {
            display: flex;
            flex-direction: column;
            align-items: center;
            width: 100%;
        }

        .info-box {
            max-width: 1200px;
            min-width: 320px;
            width: 95%;
            margin: 10px auto;
            padding: 15px;
            background-color: #ffffff;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .info-box h2 {
            margin-top: 10px;
            text-align: center;
            color: #484b6a;
            font-size: 24px;
            font-weight: 600;
        }

        .info-box table {
            min-width: 800px;
            max-height: 600px;
            overflow-y: auto;
            width: 100%;
            border-collapse: collapse;
            background-color: #ffffff;
            border-radius: 8px;
            overflow: hidden;
            table-layout: auto;
        }

        .info-box th {
            background-color: #015C9E;
            padding: 8px;
            text-align: left;
            border-bottom: 1px solid #d2d3db;
            font-size: 13px;
        }

        .info-box td {
            padding: 8px;
            text-align: left;
            border-bottom: 1px solid #d2d3db;
            font-size: 12px;
            max-width: 200px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .info-box th {
            background-color: #015C9E;
            color: #ffffff;
        }

        .info-box tbody tr:hover {
            background-color: rgba(1, 92, 158, 0.1);
            transition: background-color 0.3s ease;
        }

        .info-box .no-data {
            text-align: center;
            padding: 20px;
            color: #757575;
        }

        .info-box .table-icon {
            cursor: pointer;
            color: #3333ff;
            transition: color 0.3s ease;
        }

        .info-box .table-icon:hover {
            color: #0000b3;
        }

        .add-btn,
        .reset-btn {
            background-color: #015C9E;
            padding: 8px 15px;
            font-size: 12px;
            min-width: 100px;
            max-width: 200px;
            display: block;
            margin: 20px auto;
            color: #ffffff;
            text-align: center;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        .add-btn:hover,
        .reset-btn:hover {
            background-color: #014B80;
        }

        .modal {
            display: none;
            position: fixed;
            z-index: 10009999;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0, 0, 0, 0.4);
        }

        .modal-content {
            max-width: 800px;
            min-width: 320px;
            background-color: #ffffff;
            margin: 5% auto;
            padding: 0;
            border: none;
            border-radius: 10px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }

        .modal-header {
            background-color: #015C9E;
            color: white;
            padding: 15px;
            border-bottom: none;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .modal-header h2 {
            margin: 0;
            font-size: 18px;
            font-weight: 500;
            color: #ffffff;
        }

        .modal-body {
            padding: 20px;
            max-height: 80vh;
            overflow-y: auto;
        }

        .modal-body input,
        .modal-body textarea,
        .modal-body select {
            max-width: 100%;
            min-height: 35px;
            padding: 8px;
            font-size: 13px;
            border: 1px solid #e0e0e0;
            border-radius: 5px;
            background-color: #f8f9fa;
            color: #484b6a;
            transition: border-color 0.3s ease, background-color 0.3s ease;
        }

        .modal-body input:focus,
        .modal-body textarea:focus,
        .modal-body select:focus {
            border-color: #015C9E;
            background-color: #ffffff;
            outline: none;
        }

        .modal-body label {
            display: block;
            margin-top: 10px;
            color: #484b6a;
            font-weight: 500;
        }

        .close {
            color: #ffffff;
            font-size: 24px;
            font-weight: bold;
            cursor: pointer;
        }

        .close:hover {
            color: #e0e0e0;
        }

        .reset-btn {
            padding: 10px 14px;
            background-color: rgba(0, 130, 204, 1);
            color: #ffffff;
            text-align: center;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 12px;
            transition: background-color 0.3s ease;
        }

        .reset-btn:hover {
            background-color: rgba(0, 179, 142, 1);
        }

        .search-container {
            max-width: 100%;
            min-width: 320px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            width: 100%;
            gap: 10px;
        }

        .search-container select,
        .search-container input[type="text"],
        .search-container button {
            flex: 1;
            padding: 10px;
            border: 1px solid #d2d3db;
            border-radius: 5px;
            font-size: 14px;
        }

        .search-container button {
            background-color: #015C9E;
            color: white;
            border: none;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        .search-container button:hover {
            background-color: #014B80;
        }

        @media (max-width: 768px) {
            .container {
                padding: 10px;
            }

            .info-box {
                margin: 10px;
                padding: 15px;
            }

            .add-btn {
                width: 100%;
            }

            .modal-content {
                width: 90%;
            }
        }





        #editModal {
            z-index: 2000;
        }

        .search-container input[type=text],
        .search-container select {
            padding: 10px;
            margin-top: 8px;
            margin-bottom: 8px;
            border: 1px solid #d2d3db;
            border-radius: 5px;
            background-color: #ffffff;
            color: #484b6a;
            font-size: 14px;
            transition: border-color 0.3s ease;
        }

        .swal2-container {
            z-index: 99999999999999 !important;
        }

        .highlight {
            background-color: #ffff99;
            padding: 2px 5px;
            border-radius: 3px;
            font-weight: bold;
        }

        /* Styling buat modal detail */
        .detail-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0 10px;
        }

        .detail-table th,
        .detail-table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #e0e0e0;
        }

        .detail-table th {
            font-weight: bold;
            color: #015C9E;
            width: 40%;
        }

        .detail-table td {
            background-color: #f9f9f9;
            border-radius: 4px;
        }

        #detailModal .modal-content {
            padding: 20px;
        }

        #detailModal .modal-body {
            margin-top: 20px;
            margin-bottom: 20px;
        }

        #detailModal .modal-footer {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
            padding-top: 20px;
            border-top: 1px solid #e0e0e0;
        }

        #detailModal button {
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        #updateBtn {
            background-color: #015C9E;
            color: white;
        }

        #deleteBtn {
            background-color: #ff6b6b;
            color: white;
        }

        #updateBtn:hover,
        #deleteBtn:hover {
            opacity: 0.8;
        }

        .data-table {
            min-width: 800px;
            max-width: 1200px;
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            background-color: #fff;
            border-radius: 8px;
            overflow: hidden;

            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            font-size: 12px;
            table-layout: fixed;
            /* Tambahin ini buat bikin lebar kolom konsisten */
        }

        .data-table th,
        .data-table td {
            padding: 8px;
            text-align: left;
            border-right: 1px solid #e0e0e0;
            white-space: normal;
            word-wrap: break-word;
            vertical-align: top;
            max-width: 200px;
        }

        .data-table th {
            background-color: #015C9E;
            color: #ffffff;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 14px;
            letter-spacing: 0.5px;
            border-bottom: 2px solid #014B80;
        }

        .data-table tbody tr:nth-child(even) {
            background-color: #f8f9fa;
        }

        .data-table tbody tr:hover {
            background-color: rgba(1, 92, 158, 0.1);
            transition: background-color 0.3s ease;
        }

        /* Tambahin ini buat ngatur lebar tiap kolom */
        .data-table th:nth-child(1),
        .data-table td:nth-child(1) {
            width: 5%;
        }

        .data-table th:nth-child(2),
        .data-table td:nth-child(2) {
            width: 25%;
        }

        .data-table th:nth-child(3),
        .data-table td:nth-child(3) {
            width: 25%;
        }

        .data-table th:nth-child(4),
        .data-table td:nth-child(4) {
            width: 20%;
        }

        .data-table th:nth-child(5),
        .data-table td:nth-child(5) {
            width: 15%;
        }

        .data-table th:nth-child(6),
        .data-table td:nth-child(6) {
            width: 10%;
        }

        /* Styling untuk form inputs dan button di modal */
        .modal-body input {
            width: 100%;
            padding: 12px;
            margin: 8px 0;
            font-size: 14px;
            border: 1px solid #e0e0e0;
            border-radius: 5px;
            box-sizing: border-box;
        }

        .modal-body label {
            display: block;
            margin-top: 15px;
            margin-bottom: 5px;
            font-size: 14px;
            font-weight: 500;
            color: #484b6a;
        }

        /* Khusus untuk button submit */
        .modal-body input[type="submit"] {
            width: auto;
            min-width: 150px;
            margin-top: 20px;
            padding: 12px 25px;
            font-size: 14px;
            font-weight: 500;
            background-color: #015C9E;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        .modal-body input[type="submit"]:hover {
            background-color: #014B80;
        }

        /* Atur ukuran modal content */
        .modal-content {
            width: 90%;
            max-width: 500px;
            padding: 20px;
        }

        .modal-body {
            padding: 20px 25px;
        }

        .modal-header h2 {
            font-family: 'Poppins', sans-serif;
            font-weight: 500;
        }

        .modal-body input,
        .modal-body textarea,
        .modal-body select,
        .modal-body label,
        .search-container input,
        .search-container select,
        .search-container button,
        .data-table th,
        .data-table td,
        .add-btn,
        .reset-btn {
            font-family: 'Poppins', sans-serif;
        }
    </style>


</head>

<body>
    <div class="container">
        <div class="info-box-wrapper">
            <div class="info-box">
                <h2>Dashboard Sekolah</h2>

                <div class="search-container">
                    <button onclick="openAddModal()" class="reset-btn">Tambah Sekolah</button>
                    <input type="text" id="searchInput" placeholder="Cari nama sekolah atau lokasi...">
                    <button onclick="resetPage()" class="reset-btn">Reset</button>

                </div>

                <div class="table-container">
                    <table id="dataTable" class="data-table">
                        <thead>
                            <tr>
                                <th scope="col">NO</th>
                                <th scope="col">NAMA SEKOLAH</th>
                                <th scope="col">LOKASI</th>
                                <th scope="col">EMAIL</th>
                                <th scope="col">NO TELP</th>
                                <th scope="col">AKSI</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            if ($schoolResult->num_rows > 0) {
                                $key = 1;
                                while ($row = $schoolResult->fetch_assoc()) {
                                    echo "<tr>";
                                    echo "<th scope='row'>" . $key++ . "</th>";
                                    echo "<td>" . htmlspecialchars($row['nama']) . "</td>";
                                    echo "<td>" . htmlspecialchars($row['lokasi']) . "</td>";
                                    echo "<td>" . htmlspecialchars($row['email']) . "</td>";
                                    echo "<td>" . htmlspecialchars($row['no_telp']) . "</td>";
                                    echo "<td>
                                            <a href='#' onclick='openEditModal(" . json_encode(['nama' => $row['nama'], 'lokasi' => $row['lokasi'], 'email' => $row['email'], 'no_telp' => $row['no_telp']]) . ")'>Edit</a> | 
                                            <a href='#' onclick='confirmDelete(\"" . htmlspecialchars($row['nama']) . "\")'>Hapus</a>
                                          </td>";
                                    echo "</tr>";
                                }
                            } else {
                                echo "<tr><td colspan='6' class='no-data highlight'>Tidak ada datas</td></tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div id="editModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <span class="close">&times;</span>
                <h2>Update Data Sekolah</h2>
            </div>
            <div class="modal-body">
                <form id="editForm" action="update-sekolah.php" method="post">
                    <input type="text" id="editNama" name="nama" readonly>
                    <label for="editLokasi">Lokasi:</label>
                    <input type="text" id="editLokasi" name="lokasi" required>
                    <label for="editEmail">Email:</label>
                    <input type="email" id="editEmail" name="email" required>
                    <label for="editNoTelp">Nomor Telepon:</label>
                    <input type="tel" id="editNoTelp" name="no_telp" required>
                    <input type="submit" class="reset-btn" value="Update">
                </form>
            </div>
        </div>
    </div>

    <div id="addModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <span class="close">&times;</span>
                <h2>Tambah Sekolah</h2>
            </div>
            <div class="modal-body">
                <form id="addSchoolForm">
                    <label for="schoolName">Nama Sekolah:</label>
                    <input type="text" id="schoolName" name="schoolName" required>
                    <label for="schoolLocation">Lokasi:</label>
                    <input type="text" id="schoolLocation" name="schoolLocation" required>
                    <label for="schoolEmail">Email:</label>
                    <input type="email" id="schoolEmail" name="schoolEmail" required>
                    <label for="schoolPhone">Nomor Telepon:</label>
                    <input type="tel" id="schoolPhone" name="schoolPhone" required>
                    <input type="submit" value="Tambah" class="reset-btn">
                </form>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const editModal = document.getElementById('editModal');
            const addModal = document.getElementById('addModal');
            const closeBtns = document.querySelectorAll('.close');

            closeBtns.forEach(btn => {
                btn.onclick = function() {
                    editModal.style.display = "none";
                    addModal.style.display = "none";
                }
            });

            window.onclick = function(event) {
                if (event.target === editModal || event.target === addModal) {
                    editModal.style.display = "none";
                    addModal.style.display = "none";
                }
            }

            const editForm = document.getElementById('editForm');

            // Tambahin style buat SweetAlert
            const style = document.createElement('style');
            style.textContent = `
                .swal2-container {
                    z-index: 9999 !important;
                }
            `;
            document.head.appendChild(style);

            if (editForm) {
                editForm.addEventListener('submit', function(event) {
                    event.preventDefault();
                    const formData = new FormData(this);

                    $.ajax({
                        url: 'update-sekolah.php',
                        type: 'POST',
                        data: formData,
                        processData: false,
                        contentType: false,
                        dataType: 'json', // Tambahin ini
                        success: function(response) {
                            console.log(response); // Buat debugging
                            if (response.status === 'success') {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Mantap!',
                                    text: response.message,
                                    showConfirmButton: false,
                                    timer: 1500,
                                    customClass: {
                                        container: 'swal-on-top'
                                    }
                                }).then(() => {
                                    window.location.reload();
                                });
                            } else {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Oops...',
                                    text: response.message || 'Ada yang salah nih',
                                    customClass: {
                                        container: 'swal-on-top'
                                    }
                                });
                            }
                        },
                        error: function(xhr, status, error) {
                            console.error('Error:', xhr.responseText);
                            Swal.fire({
                                icon: 'error',
                                title: 'Waduh!',
                                text: 'Ada error nih. Coba lagi yuk! Error: ' + error,
                                customClass: {
                                    container: 'swal-on-top'
                                }
                            });
                        }
                    });
                });
            } else {
                console.error('Form dengan id "editForm" ga ketemu nih');
            }

            // Fungsi untuk membuka modal edit
            window.openEditModal = function(data) {
                document.getElementById('editNama').value = data.nama;
                document.getElementById('editLokasi').value = data.lokasi;
                document.getElementById('editEmail').value = data.email;
                document.getElementById('editNoTelp').value = data.no_telp;

                editModal.style.display = "block";
            }
        });

        let timer;

        document.getElementById('searchInput').addEventListener('input', debounceSearch);

        function debounceSearch() {
            clearTimeout(timer);
            timer = setTimeout(applyFilters, 300);
        }

        function applyFilters() {
            const search = document.getElementById('searchInput').value;

            const xhr = new XMLHttpRequest();
            xhr.open('GET', `get-filtered-sekolah-data.php?search=${encodeURIComponent(search)}`, true);

            xhr.onload = function() {
                if (this.status === 200) {
                    document.querySelector('#dataTable tbody').innerHTML = this.responseText;
                }
            };

            xhr.send();
        }

        function resetPage() {
            document.getElementById('searchInput').value = '';
            applyFilters();
        }

        function confirmDelete(nama) {
            Swal.fire({
                title: 'Yakin nih?',
                text: `Kamu beneran mau hapus data sekolah ${nama}?`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Ya, hapus aja!',
                cancelButtonText: 'Gak jadi deh'
            }).then((result) => {
                if (result.isConfirmed) {
                    deleteSekolah(nama);
                }
            });
        }

        function deleteSekolah(nama) {
            fetch('delete-sekolah.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `nama=${encodeURIComponent(nama)}`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        Swal.fire({
                            icon: 'success',
                            title: 'Mantap!',
                            text: data.message,
                            showConfirmButton: false,
                            timer: 1500
                        }).then(() => {
                            location.reload();
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Oops...',
                            text: data.message
                        });
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    Swal.fire({
                        icon: 'error',
                        title: 'Waduh!',
                        text: 'Ada error nih. Coba lagi yuk!'
                    });
                });
        }

        function openAddModal() {
            document.getElementById('addModal').style.display = 'block';
        }

        document.getElementById('addSchoolForm').onsubmit = function(event) {
            event.preventDefault();
            const schoolName = document.getElementById('schoolName').value;
            const schoolLocation = document.getElementById('schoolLocation').value;
            const schoolEmail = document.getElementById('schoolEmail').value;
            const schoolPhone = document.getElementById('schoolPhone').value;

            fetch('add-school.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `schoolName=${encodeURIComponent(schoolName)}&schoolLocation=${encodeURIComponent(schoolLocation)}&schoolEmail=${encodeURIComponent(schoolEmail)}&schoolPhone=${encodeURIComponent(schoolPhone)}`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        Swal.fire({
                            icon: 'success',
                            title: 'Mantap!',
                            text: 'Oke, sekolah berhasil ditambahkan!',
                            showConfirmButton: false,
                            timer: 1500
                        }).then(() => {
                            window.location.reload();
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Oops...',
                            text: 'Ups, gagal nambah sekolah nih. Coba lagi yuk!'
                        });
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    Swal.fire({
                        icon: 'error',
                        title: 'Waduh!',
                        text: 'Ada error nih. Coba lagi yuk!'
                    });
                });
        };
    </script>
</body>

</html>