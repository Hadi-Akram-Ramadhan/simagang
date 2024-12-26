<?php
session_start();
require('koneksi.php');
require('auth.php');
require('navAdmin.php');

// Handle POST request for adding an account
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['addNama'])) {
    $addNama = $_POST['addNama'];
    $addGmail = $_POST['addGmail'];
    $addNik = $_POST['addNik'];
    $addRole = $_POST['addRole'];
    $addSekolah = $_POST['addSekolah'];
    $addNoTelp = $_POST['addNoTelp'];
    $addPeriode = $_POST['addPeriode'];

    // Generate password from first 4 letters of name and last 4 digits of NIK
    $addPass = substr($addNama, 0, 4) . substr($addNik, -4);
    $hashedPass = password_hash($addPass, PASSWORD_DEFAULT); // Hash the generated password

    // Validate inputs
    if (!empty($addNama) && !empty($addGmail) && !empty($addNik) && !empty($addRole) && !empty($addNoTelp)) {
        // Prepare SQL statement to avoid SQL injection
        $stmt = $conn->prepare("INSERT INTO akun (nama, gmail, nik, pass, role, asal_sekolah, no_telp, magang_masuk, magang_keluar) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssissss", $addNama, $addGmail, $addNik, $hashedPass, $addRole, $addSekolah, $addNoTelp, $magang_masuk, $magang_keluar);

        // Extract magang_masuk and magang_keluar from addPeriode
        list($magang_masuk, $magang_keluar) = explode(' - ', $addPeriode);

        // Execute the statement
        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Akun baru udah berhasil ditambahin nih']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Ada error nih: ' . $stmt->error]);
        }

        // Close statement
        $stmt->close();
    } else {
        echo json_encode(['success' => false, 'message' => 'Semua field harus diisi']);
    }
    exit;
}

// Handle POST request for deleting an account
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] == 'delete') {
    $nama = $_POST['nama'];

    // Prepare SQL statement to delete user
    $stmt = $conn->prepare("DELETE FROM akun WHERE nama = ?");
    $stmt->bind_param("s", $nama);

    // Execute the statement
    if ($stmt->execute()) {
        echo "<script>showAlert('Beres', 'User udah dihapus ya', 'success');</script>";
    } else {
        echo "<script>showAlert('Waduh', 'Gagal hapus user nih: " . $stmt->error . "', 'error');</script>";
    }

    // Close statement
    $stmt->close();
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" integrity="sha512-iecdLmaskl7CVkqkXNQ/ZH/XLlvWZOJyj7Yy7tcenmpD1ypASozpmT/E0iPtmFIB46ZmdtAc9eNBvH0H/ZpiBw==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <title>Manage User Auth</title>
    <style>
        :root {
            --primary-blue: #2D3F9B;
            --secondary-blue: #1e2b6e;
            --light-blue: #e8eaf6;
            --text-dark: #2c3345;
            --text-light: #6b7280;
        }

        body {
            background-color: #ffffff;
            color: var(--text-dark);
            font-family: 'Inter', -apple-system, sans-serif;
            margin: 0;
            padding: 0;
        }

        .container {
            margin-top: 60px;
            display: flex;
            justify-content: center;
            align-items: flex-start;
            min-height: 100vh;
            padding: 24px;
            flex-wrap: wrap;
        }

        .info-box {
            margin: 20px;
            background: #fff;
            border-radius: 16px;
            padding: 24px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            width: 95%;
            max-width: 1200px;
        }

        h2 {
            margin-top: 10px;
            text-align: center;
            color: #484b6a;
            font-size: 24px;
            font-weight: 600;
        }

        .filter-section {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            flex-wrap: wrap;
            gap: 10px;
        }

        .filter-section select,
        .filter-section input,
        .filter-section button {
            padding: 10px;
            border: 1px solid #d2d3db;
            border-radius: 5px;
            font-size: 14px;
        }

        .filter-section button,
        .add-btn {
            background-color: var(--primary-blue);
            color: white;
            border: none;
            cursor: pointer;
            transition: all 0.2s;
            padding: 10px 20px;
            border-radius: 8px;
            font-weight: 500;
            margin-bottom: 0.9rem;
        }

        .filter-section button:hover,
        .add-btn:hover {
            background-color: var(--secondary-blue);
            transform: translateY(-1px);
        }

        .data-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            background-color: #fff;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            font-size: 12px;
        }

        .data-table th,
        .data-table td {
            padding: 8px 10px;
            text-align: left;
            border-right: 1px solid #e0e0e0;
        }

        .data-table th:last-child,
        .data-table td:last-child {
            border-right: none;
        }

        .data-table thead {
            background-color: var(--primary-blue);
            color: #ffffff;
            font-size: 13px;
        }

        .data-table th {
            font-weight: 600;
            text-transform: uppercase;
            font-size: 14px;
            letter-spacing: 0.5px;
            border-bottom: 2px solid #3c8872;
        }

        .data-table tbody tr:nth-child(even) {
            background-color: #f8f9fa;
        }

        .data-table tbody tr:hover {
            background-color: var(--light-blue);
            transition: background-color 0.3s ease;
        }

        .modal {
            display: none;
            position: fixed;
            z-index: 99999999;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0, 0, 0, 0.4);
        }

        .modal-content {
            background-color: #ffffff;
            margin: 5% auto;
            padding: 0;
            border: none;
            width: 95%;
            max-width: 1200px;
            border-radius: 16px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }

        .modal-header {
            background-color: var(--primary-blue);
            color: white;
            padding: 20px 24px;
            border-bottom: none;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .modal-body {
            padding: 20px;
            max-height: 80vh;
            overflow-y: auto;
        }

        .modal-body input,
        .modal-body select {
            width: 100%;
            padding: 12px;
            margin: 10px 0;
            border-radius: 8px;
            border: 1px solid #e5e7eb;
            background-color: #f8f9fa;
            color: #484b6a;
            transition: all 0.2s;
        }

        .modal-body input:focus,
        .modal-body select:focus {
            border-color: var(--primary-blue);
            box-shadow: 0 0 0 3px rgba(45, 63, 155, 0.1);
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

        @media screen and (max-width: 768px) {
            .container {
                padding: 10px;
            }

            .info-box {
                margin: 10px;
                padding: 15px;
            }

            .modal-content {
                width: 95%;
                margin: 2% auto;
            }
        }

        .swal2-container {
            z-index: 999999999999 !important;
        }

        .data-table button {
            padding: 6px 12px;
            border-radius: 6px;
            border: none;
            background: var(--primary-blue);
            color: white;
            font-size: 13px;
            cursor: pointer;
            transition: all 0.2s;
        }

        .data-table button:hover {
            background: var(--secondary-blue);
            transform: translateY(-1px);
        }

        .data-table tbody tr:hover {
            background-color: var(--light-blue);
        }

        .modal-header h2,
        .modal-header .close {
            color: white;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="info-box">
            <h2>Dashboard Akun</h2>

            <br>
            <!-- Button to open add account modal -->
            <button class="add-btn" onclick="openAddModal()">Buat Akun Baru</button>

            <!-- Add Account Modal -->
            <div id="addModal" class="modal">
                <div class="modal-content">
                    <div class="modal-header">
                        <h2>Buat Akun Baru</h2>
                        <span class="close" onclick="closeModal()">&times;</span>
                    </div>
                    <div class="modal-body">
                        <form id="addUserForm">
                            <select id="addRole" name="addRole" class="modal-input" onchange="toggleSchoolInput()" required>
                                <option value="2">Admin</option>
                                <option value="3">Guru</option>
                                <option value="4">Pembimbing</option>
                            </select>
                            <input type="text" id="addNama" name="addNama" placeholder="Nama" class="modal-input" required>
                            <input type="email" id="addGmail" name="addGmail" placeholder="Email" class="modal-input" required>
                            <input type="text" id="addNik" name="addNik" placeholder="NUPTK/NIP/No.Identitas" class="modal-input" required>
                            <input type="text" id="addNoTelp" name="addNoTelp" placeholder="No Telp" class="modal-input" required>
                            <select id="addSekolah" name="addSekolah" placeholder="Asal Sekolah" class="modal-input" style="display: none;" onchange="updatePeriodeMagang()">
                                <option value="">Pilih Sekolah</option>
                                <?php
                                // Fetch school names from the akun table where role is 1 (student)
                                $query = "SELECT DISTINCT asal_sekolah FROM akun WHERE role = 1 AND asal_sekolah IS NOT NULL AND asal_sekolah != '' ORDER BY asal_sekolah";
                                $result = $conn->query($query);
                                if ($result->num_rows > 0) {
                                    while ($row = $result->fetch_assoc()) {
                                        $schoolName = $row['asal_sekolah']; // Removed trim()
                                        echo "<option value='" . htmlspecialchars($schoolName) . "'>" . htmlspecialchars($schoolName) . "</option>";
                                    }
                                } else {
                                    echo "<option value=''>Belum ada sekolah yang punya murid nih</option>";
                                }
                                ?>
                            </select>
                            <select id="addPeriode" name="addPeriode" class="modal-input" style="display: none;">
                                <option value="">Pilih Periode Magang</option>
                            </select>
                            <button type="submit" class="button">Tambah</button>
                        </form>
                    </div>
                </div>
            </div>

            <div class="filter-section">
                <label for="roleFilter">Pilih Role:</label>
                <select id="roleFilter">
                    <option value="all">Semua</option>
                    <option value="1">1 (user biasa)</option>
                    <option value="2">2 (admin)</option>
                    <option value="3">3 (guru)</option>
                    <option value="4">4 (pembimbing perusahaan)</option>
                </select>

                <label for="schoolFilter">Pilih Sekolah:</label>
                <select id="schoolFilter">
                    <option value="all">Semua</option>
                    <?php
                    // Fetch unique asal_sekolah from akun table
                    $sqlSekolah = "SELECT DISTINCT asal_sekolah FROM akun WHERE role='1'";
                    $resultSekolah = $conn->query($sqlSekolah);

                    if ($resultSekolah->num_rows > 0) {
                        while ($rowSekolah = $resultSekolah->fetch_assoc()) {
                            $schoolName = $rowSekolah["asal_sekolah"]; // Removed trim()
                            echo "<option value=\"" . htmlspecialchars($schoolName) . "\">" . htmlspecialchars($schoolName) . "</option>";
                        }
                    } else {
                        echo "<option disabled>Belum ada sekolah nih</option>";
                    }
                    ?>
                </select>

                <label for="searchInput">Cari:</label>
                <input type="text" id="searchInput" placeholder="Ketik buat nyari..." class="modal-input">
            </div>

            <table class="data-table" id="userTable">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Asal Sekolah</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Edit</th> <!-- New column for edit button -->
                        <th>Hapus</th> <!-- New column for delete button -->
                    </tr>
                </thead>
                <tbody>
                    <!-- Data akan diisi oleh JavaScript -->
                </tbody>
            </table>
        </div>
    </div>

    <!-- Edit Modal -->
    <div id="editModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Edit User</h2>
                <span class="close" onclick="closeModal()">&times;</span>
            </div>
            <div class="modal-body">
                <form id="editForm">
                    <input type="hidden" id="editNama" name="editNama">
                    <label for="editSekolah">Asal Sekolah:</label>
                    <input type="text" id="editSekolah" name="editSekolah" class="modal-input">
                    <label for="editGmail">Email:</label>
                    <input type="email" id="editGmail" name="editGmail" class="modal-input">
                    <label for="editPass">Password:</label>
                    <input type="password" id="editPass" name="editPass" class="modal-input">
                    <select id="editRole" name="editRole" class="modal-input">
                        <option value="1">1 (user biasa)</option>
                        <option value="2">2 (admin)</option>
                        <option value="3">3 (guru)</option>
                        <option value="4">4 (pembimbing)</option>
                    </select>
                    <button type="submit" class="modal-button">Save Changes</button>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Function to show alerts using SweetAlert
        function showAlert(title, text, icon) {
            Swal.fire({
                title: title,
                text: text,
                icon: icon,
                confirmButtonText: 'Sip'
            });
        }

        // Open modal and populate with existing data
        function openModal(nama, sekolah, gmail, role) {
            document.getElementById('editNama').value = nama;
            document.getElementById('editSekolah').value = sekolah;
            document.getElementById('editGmail').value = gmail;
            document.getElementById('editRole').value = role;
            document.getElementById('editModal').style.display = 'block';
        }

        // Function to reset form fields
        function resetForm(formId) {
            document.getElementById(formId).reset();
            if (formId === 'addUserForm') {
                document.getElementById('addSekolah').style.display = 'none';
                document.getElementById('addSekolah').required = false;
                document.getElementById('addPeriode').style.display = 'none';
                document.getElementById('addPeriode').required = false;
            }
        }

        // Close modal and reset form
        function closeModal() {
            document.getElementById('editModal').style.display = 'none';
            document.getElementById('addModal').style.display = 'none';
            resetForm('editForm');
            resetForm('addUserForm');
        }

        // Open add account modal
        function openAddModal() {
            document.getElementById('addModal').style.display = 'block';
            resetForm('addUserForm');
        }

        // Filter table by role
        document.getElementById('roleFilter').addEventListener('change', function() {
            var filter = this.value;
            var rows = document.querySelectorAll('#userTable tbody tr');
            rows.forEach(row => {
                var role = row.cells[3].innerText; // Adjusted index to match the correct column
                if (filter === 'all' || role.includes(filter)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        });

        // Filter table by asal sekolah
        document.getElementById('schoolFilter').addEventListener('change', function() {
            var filter = this.value;
            var rows = document.querySelectorAll('#userTable tbody tr');
            rows.forEach(row => {
                var sekolah = row.cells[1].innerText;
                if (filter === 'all' || sekolah === filter) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        });

        // Function to toggle school input based on role
        function toggleSchoolInput() {
            var role = document.getElementById('addRole').value;
            var schoolInput = document.getElementById('addSekolah');
            var periodeInput = document.getElementById('addPeriode');
            if (role === '2' || role === '4') { // Admin or Pembimbing
                schoolInput.style.display = 'none';
                periodeInput.style.display = 'none';
                schoolInput.required = false;
                periodeInput.required = false;
                schoolInput.value = ''; // Clear input when hidden
                periodeInput.value = ''; // Clear input when hidden
            } else { // Guru
                schoolInput.style.display = 'block';
                periodeInput.style.display = 'block';
                schoolInput.required = true;
                periodeInput.required = true;
            }
            updatePeriodeMagang(); // Reset periode magang saat role berubah
        }

        function updatePeriodeMagang() {
            var schoolSelect = document.getElementById('addSekolah');
            var periodeSelect = document.getElementById('addPeriode');
            var selectedSchool = schoolSelect.value;

            // Reset periode dropdown
            periodeSelect.innerHTML = '<option value="">Pilih Periode Magang</option>';

            if (selectedSchool) {
                // Fetch periode magang for the selected school
                fetch('get_periode_magang.php?school=' + encodeURIComponent(selectedSchool))
                    .then(response => response.json())
                    .then(data => {
                        data.forEach(periode => {
                            var option = document.createElement('option');
                            option.value = periode;
                            option.textContent = periode;
                            periodeSelect.appendChild(option);
                        });
                    })
                    .catch(error => console.error('Error:', error));
            }
        }

        // Call toggleSchoolInput on page load to set initial state
        document.addEventListener('DOMContentLoaded', function() {
            toggleSchoolInput();
        });

        // Function to search table
        document.getElementById('searchInput').addEventListener('input', function() {
            var searchQuery = this.value.toLowerCase();
            var rows = document.querySelectorAll('#userTable tbody tr');
            rows.forEach(row => {
                var visible = row.textContent.toLowerCase().includes(searchQuery);
                row.style.display = visible ? '' : 'none';
            });
        });

        // Function to load users
        function loadUsers() {
            fetch('get_users.php')
                .then(response => response.json())
                .then(data => {
                    const tableBody = document.querySelector('#userTable tbody');
                    tableBody.innerHTML = '';
                    data.forEach(user => {
                        const row = `
                            <tr>
                                <td>${user.nama}</td>
                                <td>${user.asal_sekolah}</td>
                                <td>${user.gmail}</td>
                                <td>${getRole(user.role)}</td>
                                <td><button onclick="openModal('${user.nama}', '${user.asal_sekolah}', '${user.gmail}', '${user.role}')">Edit</button></td>
                                <td><button onclick="deleteUser('${user.nama}')">Hapus</button></td>
                            </tr>
                        `;
                        tableBody.innerHTML += row;
                    });
                })
                .catch(error => console.error('Error:', error));
        }

        // Function to get role based on numeric value
        function getRole(role) {
            switch (parseInt(role)) {
                case 1:
                    return '1 (user biasa)';
                case 2:
                    return '2 (admin)';
                case 3:
                    return '3 (guru)';
                case 4:
                    return '4 (pembimbing)';
                default:
                    return '';
            }
        }

        // Function to check if a school already has a teacher for the selected period
        function schoolHasTeacherForPeriod(school, period) {
            return fetch('check_teacher.php', {
                    method: 'POST',
                    body: JSON.stringify({
                        school: school,
                        period: period
                    }),
                    headers: {
                        'Content-Type': 'application/json'
                    }
                })
                .then(response => response.json())
                .then(data => data.hasTeacher)
                .catch(error => {
                    console.error('Error:', error);
                    return false;
                });
        }

        // Function to add user
        document.getElementById('addUserForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);

            // Remove asal_sekolah from formData if role is admin
            if (formData.get('addRole') === '2') {
                formData.delete('addSekolah');
                formData.delete('addPeriode');
            }

            // Check if the school already has a teacher for the selected period
            if (formData.get('addRole') === '3') {
                schoolHasTeacherForPeriod(formData.get('addSekolah'), formData.get('addPeriode'))
                    .then(hasTeacher => {
                        if (hasTeacher) {
                            Swal.fire('Waduh', 'Udah ada guru buat periode ini. Gak bisa nambah lagi. Coba pilih periode lain atau ubah guru yang udah ada.', 'error');
                        } else {
                            submitForm(formData);
                        }
                    });
            } else {
                submitForm(formData);
            }
        });

        function submitForm(formData) {
            fetch('add_user.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => {
                    if (!response.ok) {
                        return response.text().then(text => {
                            throw new Error(`HTTP status ${response.status}: ${text}`);
                        });
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.success) {
                        Swal.fire({
                            title: 'Mantap',
                            text: data.message,
                            icon: 'success'
                        }).then(() => {
                            closeModal();
                            loadUsers();
                        });
                    } else {
                        Swal.fire('Waduh', data.message, 'error');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    Swal.fire('Error', 'Ada masalah nih: ' + error.message, 'error');
                });
        }

        // Function to edit user
        document.getElementById('editForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);

            fetch('edit_user.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire({
                            title: 'Mantap',
                            text: data.message,
                            icon: 'success'
                        }).then(() => {
                            closeModal();
                            loadUsers();
                        });
                    } else {
                        Swal.fire('Waduh', data.message, 'error');
                    }
                })
                .catch(error => {
                    Swal.fire('Error', 'Ada masalah nih: ' + error, 'error');
                });
        });

        // Function to delete user
        function deleteUser(nama) {
            Swal.fire({
                title: 'Yakin mau hapus?',
                text: "Kalo udah dihapus, gak bisa dibalikin lagi loh!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Iya, hapus aja!',
                cancelButtonText: 'Gak jadi deh'
            }).then((result) => {
                if (result.isConfirmed) {
                    const formData = new FormData();
                    formData.append('nama', nama);

                    fetch('delete_user.php', {
                            method: 'POST',
                            body: formData
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                Swal.fire({
                                    title: 'Udah kehapus!',
                                    text: data.message,
                                    icon: 'success'
                                }).then(() => {
                                    loadUsers();
                                });
                            } else {
                                Swal.fire('Waduh', data.message, 'error');
                            }
                        })
                        .catch(error => {
                            Swal.fire('Error', 'Ada masalah nih: ' + error, 'error');
                        });
                }
            });
        }

        // Load users when page loads
        document.addEventListener('DOMContentLoaded', loadUsers);
    </script>
</body>

</html>