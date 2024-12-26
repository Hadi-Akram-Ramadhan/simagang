<?php
session_start();
require('koneksi.php');
require('auth.php');
?>

<!DOCTYPE html>
<html>

<head>
    <title>Manajemen Tugas</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/@sweetalert2/theme-bootstrap-4/bootstrap-4.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="shortcut icon" href="image\kementrian.png">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-color: #2D3F8E;
            /* Biru Kemendag */
            --secondary-color: #1B2559;
            /* Biru Gelap */
            --accent-color: #E6B014;
            /* Kuning Kemendag */
            --gradient: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            --shadow-sm: 0 2px 8px rgba(45, 63, 142, 0.08);
            --shadow-md: 0 4px 12px rgba(45, 63, 142, 0.12);
        }

        body {
            background-color: #F5F7FF;
            font-family: 'Poppins', sans-serif;
        }

        /* Navbar styling */
        .navbar {
            background: var(--gradient);
            box-shadow: var(--shadow-md);
            padding: 1.2rem 0;
        }

        .navbar-brand {
            color: white;
            font-weight: 700;
            font-size: 1.5rem;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .navbar-brand img {
            height: 32px;
        }

        /* Card styling */
        .task-card {
            transition: all 0.3s ease;
            border: none;
            border-radius: 12px;
            box-shadow: var(--shadow-sm);
            background: white;
            position: relative;
            overflow: hidden;
        }

        .task-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 4px;
            background: var(--gradient);
        }

        .task-card:hover {
            transform: translateY(-4px);
            box-shadow: var(--shadow-md);
        }

        .task-card .card-body {
            padding: 1.8rem;
        }

        .card-title {
            color: var(--primary-color);
            font-weight: 600;
            font-size: 1.15rem;
            margin-bottom: 1rem;
        }

        /* Avatar styling */
        .avatar {
            width: 40px;
            height: 40px;
            border-radius: 10px;
            background: var(--gradient);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            border: 3px solid white;
            box-shadow: var(--shadow-sm);
            font-family: 'Poppins', sans-serif;
        }

        /* Button styling */
        .btn-primary {
            background: var(--gradient);
            border: none;
            padding: 0.8rem 2rem;
            border-radius: 8px;
            font-weight: 600;
            transition: all 0.2s;
            color: white;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(45, 63, 142, 0.3);
        }

        .btn-light {
            background: rgba(255, 255, 255, 0.9);
            border: none;
            padding: 0.8rem 2rem;
            border-radius: 8px;
            font-weight: 600;
            color: var(--primary-color);
        }

        .btn-light:hover {
            background: white;
            transform: translateY(-2px);
        }

        /* Modal styling */
        .modal-content {
            border-radius: 12px;
            border: none;
            box-shadow: var(--shadow-md);
        }

        .modal-header {
            background: var(--gradient);
            color: white;
            border-bottom: none;
            border-radius: 12px 12px 0 0;
            padding: 1.5rem;
        }

        .modal-title {
            font-weight: 600;
            font-family: 'Poppins', sans-serif;
        }

        .btn-close {
            filter: brightness(0) invert(1);
        }

        .modal-body {
            padding: 2rem;
        }

        .form-label {
            color: var(--primary-color);
            font-weight: 500;
            margin-bottom: 0.5rem;
        }

        .form-control,
        .form-select {
            border-radius: 8px;
            padding: 0.8rem 1rem;
            border: 2px solid #E5E7EB;
            font-size: 0.95rem;
        }

        .form-control:focus,
        .form-select:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(45, 63, 142, 0.15);
        }

        /* Custom button styles */
        .btn-success {
            background: #10B981;
            border: none;
            border-radius: 8px;
            padding: 0.5rem 1rem;
            font-weight: 500;
        }

        .btn-danger {
            background: #EF4444;
            border: none;
            border-radius: 8px;
            padding: 0.5rem 1rem;
            font-weight: 500;
        }

        /* Status badges */
        .status-badge {
            padding: 0.4rem 1rem;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 500;
        }

        .status-pending {
            background: #FEF3C7;
            color: #92400E;
        }

        .status-completed {
            background: #D1FAE5;
            color: #065F46;
        }

        /* Task info */
        .task-info {
            display: flex;
            align-items: center;
            gap: 8px;
            color: #6B7280;
            font-size: 0.9rem;
            margin-top: 1rem;
        }

        .task-info i {
            color: var(--primary-color);
        }

        /* Update styling untuk container utama */
        .container {
            margin-top: 6rem;
            width: 100%;
            max-width: 1200px;
            min-width: 320px;
            margin-left: auto;
            margin-right: auto;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            margin-bottom: 10rem;
            background: #FFFFFF;
        }

        /* Card styling yang lebih modern */
        .task-card {
            background: #FFFFFF;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
            transition: all 0.3s ease;
            border: none;
            position: relative;
            overflow: hidden;
        }

        .task-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 4px;
            background: linear-gradient(135deg, #004B8F, #0072BC);
        }

        .task-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
        }

        .task-card .card-body {
            padding: 1.8rem;
        }

        /* Avatar styling yang lebih modern */
        .avatar-group {
            display: flex;
            align-items: center;
        }

        .avatar {
            width: 40px;
            height: 40px;
            border-radius: 10px;
            background: linear-gradient(135deg, #004B8F, #0072BC);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            font-family: 'Poppins', sans-serif;
            border: 3px solid white;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        /* Button styling yang lebih modern */
        .btn-primary {
            background: linear-gradient(135deg, #004B8F, #0072BC);
            border: none;
            padding: 0.8rem 2rem;
            border-radius: 8px;
            font-weight: 600;
            font-family: 'Poppins', sans-serif;
            transition: all 0.3s ease;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 75, 143, 0.3);
        }

        /* Modal styling yang lebih modern */
        .modal-content {
            border-radius: 20px;
            border: none;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
        }

        .modal-header {
            background: linear-gradient(135deg, #004B8F, #0072BC);
            color: white;
            border-radius: 20px 20px 0 0;
            padding: 1.5rem;
        }

        .modal-title {
            font-family: 'Poppins', sans-serif;
            font-weight: 600;
        }

        .form-control {
            border-radius: 8px;
            padding: 0.8rem 1rem;
            border: 2px solid #E7ECF3;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            border-color: #004B8F;
            box-shadow: 0 0 0 3px rgba(0, 75, 143, 0.1);
        }

        /* Loading state styling */
        .loading-overlay {
            background-color: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(5px);
        }

        .spinner {
            border: 4px solid #E7ECF3;
            border-top: 4px solid #004B8F;
            width: 40px;
            height: 40px;
        }

        @media (max-width: 576px) {
            .navbar-brand {
                font-size: 0.9rem !important;
            }

            .btn-sm {
                padding: 0.2rem 0.5rem;
            }
        }

        .btn-amber-400 {
            background-color: #FFC107;
            /* Warna amber */
            color: #000;
        }

        .btn-amber-400:hover {
            background-color: #FFB300;
            color: #000;
        }

        .navbar-brand {
            font-size: 1rem;
        }

        .btn {
            font-size: 0.9rem;
            padding: 0.5rem 1rem;
        }

        .list-group-item {
            padding: 1rem;
            margin-bottom: 0.8rem;
            border: 1px solid rgba(0, 0, 0, 0.05) !important;
            transition: all 0.3s ease;
            background: white;
        }

        .list-group-item:hover {
            transform: translateX(5px);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
            border-left: 4px solid var(--primary-color) !important;
        }

        .badge {
            font-size: 0.8rem;
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-weight: 500;
        }

        .bg-warning {
            background: #FFF3CD !important;
            color: #856404 !important;
        }

        .bg-success {
            background: #D4EDDA !important;
            color: #155724 !important;
        }

        /* Styling buat info user */
        .list-group-item h6 {
            color: var(--primary-color);
            font-weight: 600;
            margin-bottom: 0.3rem;
        }

        .list-group-item small {
            font-size: 0.85rem;
            opacity: 0.8;
        }

        /* Avatar styling */
        .avatar {
            width: 45px;
            height: 45px;
            font-size: 1.2rem;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }
    </style>
</head>

<body>
    <nav class="navbar mb-3 py-2">
        <div class="container-fluid px-2 px-sm-4 d-flex justify-content-between align-items-center">
            <span class="navbar-brand fs-6 d-none d-sm-block">Manajemen Kategori Tugas</span>
            <span class="navbar-brand fs-6 d-block d-sm-none">Tasks</span>
            <div class="d-flex gap-2">
                <button type="button" class="btn btn-light btn-sm" data-bs-toggle="modal" data-bs-target="#modalTambahTugas">
                    <span class="d-none d-sm-inline">+ Tambah Kategori Tugas</span>
                    <span class="d-inline d-sm-none">+</span>
                </button>
                <a href="javascript:history.back()" class="btn btn-amber-400 btn-sm">
                    <span class="d-none d-sm-inline">Kembali</span>
                    <span class="d-inline d-sm-block"><i class="fas fa-arrow-left"></i></span>
                </a>
            </div>
        </div>
    </nav>

    <div class="container mb-4">
        <div class="row g-3">
            <div class="col-md-4">
                <select class="form-select" id="filterSekolah" onchange="filterUsers()">
                    <option value="">Semua Sekolah</option>
                </select>
            </div>
            <div class="col-md-4">
                <select class="form-select" id="filterPeriode" onchange="filterUsers()">
                    <option value="">Semua Periode</option>
                </select>
            </div>
            <div class="col-md-4">
                <input type="text" class="form-control" id="searchUser" placeholder="Cari User" oninput="filterUsers()">
            </div>
        </div>
    </div>

    <div class="container">
        <div class="row g-4" id="taskContainer">
            <!-- Task cards will be dynamically added here -->
        </div>
    </div>

    <!-- Modal Tambah Tugas -->
    <div class="modal fade" id="modalTambahTugas" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Tambah Kategori Tugas Baru</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="formTambahTugas">
                        <div class="mb-3">
                            <label class="form-label">Nama Kategori Tugas</label>
                            <input type="text" class="form-control" name="nama_tugas" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Penerima Kategori Tugas</label>
                            <div class="penerima-list mb-2">
                                <div class="input-group mb-2">
                                    <select class="form-select" name="penerima_tugas[]" required>
                                        <option value="">Pilih Penerima</option>
                                        <?php
                                        $query = "SELECT nama FROM akun WHERE role = 1";
                                        $result = mysqli_query($conn, $query);
                                        while ($row = mysqli_fetch_assoc($result)) {
                                            echo "<option value='" . $row['nama'] . "'>" . $row['nama'] . "</option>";
                                        }
                                        ?>
                                    </select>
                                    <button type="button" class="btn btn-danger" onclick="this.parentElement.remove()">Hapus</button>
                                </div>
                            </div>
                            <button type="button" class="btn btn-success btn-sm" onclick="tambahPenerima()">+ Tambah Penerima</button>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-primary" onclick="simpanTugas()">Simpan</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Detail Tugas -->
    <div class="modal fade" id="modalDetailTugas" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Detail Kategori Tugas</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <h4 id="detailNamaTugas"></h4>
                    <div class="mt-3">
                        <h6>Penerima Tugas:</h6>
                        <ul id="detailPenerimaTugas"></ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Load tasks when page loads
        document.addEventListener('DOMContentLoaded', loadTasks);

        function tambahPenerima() {
            const penerimaList = document.querySelector('.penerima-list');
            const existingSelects = penerimaList.querySelectorAll('select');

            // Bikin array dari user yang udah dipilih
            const selectedUsers = Array.from(existingSelects).map(select => select.value);

            const newSelect = document.createElement('div');
            newSelect.className = 'input-group mb-2';
            newSelect.innerHTML = `
                <select class="form-select" name="penerima_tugas[]" required onchange="checkDuplicate(this)">
                    <option value="">Pilih Penerima</option>
                    <?php
                    $query = "SELECT nama FROM akun WHERE role = 1";
                    $result = mysqli_query($conn, $query);
                    while ($row = mysqli_fetch_assoc($result)) {
                        echo "<option value='" . $row['nama'] . "'>" . $row['nama'] . "</option>";
                    }
                    ?>
                </select>
                <button type="button" class="btn btn-danger" onclick="this.parentElement.remove()">Hapus</button>
            `;

            // Disable opsi yang udah dipilih di select baru
            const selectElement = newSelect.querySelector('select');
            Array.from(selectElement.options).forEach(option => {
                if (selectedUsers.includes(option.value) && option.value !== '') {
                    option.disabled = true;
                }
            });

            penerimaList.appendChild(newSelect);
        }

        function checkDuplicate(selectElement) {
            const selectedValue = selectElement.value;
            const allSelects = document.querySelectorAll('[name="penerima_tugas[]"]');

            // Reset semua disabled options dulu
            allSelects.forEach(select => {
                Array.from(select.options).forEach(option => {
                    option.disabled = false;
                });
            });

            // Disable options yang udah dipilih di semua select
            const selectedValues = Array.from(allSelects).map(select => select.value);
            allSelects.forEach(select => {
                Array.from(select.options).forEach(option => {
                    if (selectedValues.includes(option.value) && option.value !== '' && option.value !== select.value) {
                        option.disabled = true;
                    }
                });
            });
        }

        // Update event listener pas modal dibuka
        document.getElementById('modalTambahTugas').addEventListener('show.bs.modal', function() {
            const penerimaList = document.querySelector('.penerima-list');
            penerimaList.innerHTML = ''; // Reset dulu
            tambahPenerima(); // Tambah select pertama
        });

        function simpanTugas() {
            const form = document.getElementById('formTambahTugas');
            const namaTugas = form.querySelector('[name="nama_tugas"]').value;
            const penerimaTugas = Array.from(form.querySelectorAll('[name="penerima_tugas[]"]'))
                .map(select => select.value)
                .filter(value => value !== '');

            if (!namaTugas || penerimaTugas.length === 0) {
                Swal.fire({
                    title: 'Oops!',
                    text: 'Mohon isi semua field!',
                    icon: 'warning',
                    confirmButtonColor: '#2D3F8E'
                });
                return;
            }

            // Show loading
            Swal.fire({
                title: 'Menyimpan...',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            const formData = new FormData();
            formData.append('nama_tugas', namaTugas);
            penerimaTugas.forEach(penerima => {
                formData.append('penerima_tugas[]', penerima);
            });

            fetch('simpan_tugas.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.status === 'success') {
                        Swal.fire({
                            title: 'Berhasil!',
                            text: data.message,
                            icon: 'success',
                            confirmButtonColor: '#2D3F8E'
                        }).then(() => {
                            form.reset();
                            bootstrap.Modal.getInstance(document.getElementById('modalTambahTugas')).hide();
                            loadTasks();
                        });
                    } else {
                        throw new Error(data.message || 'Gagal menyimpan tugas!');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    Swal.fire({
                        title: 'Error!',
                        text: error.message || 'Terjadi kesalahan saat menyimpan tugas!',
                        icon: 'error',
                        confirmButtonColor: '#2D3F8E'
                    });
                });
        }

        function loadTasks() {
            Promise.all([
                    fetch('get-users.php').then(res => res.json()),
                    fetch('get-task.php').then(res => res.json())
                ])
                .then(([users, tasks]) => {
                    // Simpan data users ke variable global
                    window.allUsers = users;

                    // Update dropdown filters
                    updateFilters(users);

                    // Render users
                    renderUsers(users, tasks);
                })
                .catch(error => {
                    console.error('Error:', error);
                    Swal.fire({
                        title: 'Error!',
                        text: 'Gagal memuat data!',
                        icon: 'error',
                        confirmButtonColor: '#004B8F'
                    });
                });
        }

        function updateFilters(users) {
            const filterSekolah = document.getElementById('filterSekolah');
            const filterPeriode = document.getElementById('filterPeriode');

            // Update sekolah dropdown
            const sekolahSet = new Set(users.map(user => user.asal_sekolah));
            filterSekolah.innerHTML = '<option value="">Semua Sekolah</option>' +
                Array.from(sekolahSet).map(sekolah =>
                    `<option value="${sekolah}">${sekolah}</option>`
                ).join('');

            // Update periode dropdown based on selected school
            filterSekolah.addEventListener('change', () => {
                const selectedSekolah = filterSekolah.value;
                const filteredUsers = selectedSekolah ? users.filter(user => user.asal_sekolah === selectedSekolah) : users;

                const periodeSet = new Set(filteredUsers.map(user => user.periode));
                filterPeriode.innerHTML = '<option value="">Semua Periode</option>' +
                    Array.from(periodeSet).map(periode =>
                        `<option value="${periode}">${periode}</option>`
                    ).join('');
            });

            // Trigger change event to initialize periode dropdown
            filterSekolah.dispatchEvent(new Event('change'));
        }

        function filterUsers() {
            const selectedSekolah = document.getElementById('filterSekolah').value;
            const selectedPeriode = document.getElementById('filterPeriode').value;
            const searchQuery = document.getElementById('searchUser').value.toLowerCase();

            let filteredUsers = window.allUsers;

            if (selectedSekolah) {
                filteredUsers = filteredUsers.filter(user => user.asal_sekolah === selectedSekolah);
            }

            if (selectedPeriode) {
                filteredUsers = filteredUsers.filter(user => user.periode === selectedPeriode);
            }

            if (searchQuery) {
                filteredUsers = filteredUsers.filter(user => user.nama.toLowerCase().includes(searchQuery));
            } else {

            }

            // Re-render dengan data yang sudah difilter
            fetch('get-task.php')
                .then(res => res.json())
                .then(tasks => renderUsers(filteredUsers, tasks));
        }

        function renderUsers(users, tasks) {
            const tasksByUser = tasks.reduce((acc, task) => {
                if (!acc[task.user]) {
                    acc[task.user] = [];
                }
                acc[task.user].push(task);
                return acc;
            }, {});

            const container = document.getElementById('taskContainer');

            if (users.length === 0) {
                container.innerHTML = `
                    <div class="col-12 text-center">
                        <p>Ada Yang Salah Nih :/</p>
                    </div>
                `;
                return;
            }

            container.innerHTML = `
                <div class="col-12">
                    <div class="list-group">
                        ${users.map(user => {
                            const userTasks = tasksByUser[user.nama] || [];
                            return `
                                <div class="list-group-item list-group-item-action" 
                                     onclick="showUserTasks('${user.nama}', ${JSON.stringify(userTasks).replace(/"/g, '&quot;')})">
                                    <div class="d-flex w-100 justify-content-between align-items-center">
                                        <div class="d-flex align-items-center gap-3">
                                            <div class="avatar">
                                                ${user.nama.charAt(0)}
                                            </div>
                                            <div>
                                                <h6 class="mb-1">${user.nama}</h6>
                                                <small class="text-muted d-block">${user.asal_sekolah}</small>
                                                <small class="text-muted d-block">Periode: ${user.periode}</small>
                                            </div>
                                        </div>
                                        <div class="text-end">
                                            <span class="badge ${userTasks.length === 0 ? 'bg-warning' : 'bg-success'}">
                                                ${userTasks.length} tugas
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            `;
                        }).join('')}
                    </div>
                </div>
            `;
        }

        function showUserTasks(user, tasks) {
            const tasksList = tasks.length > 0 ? tasks.map(task => `
                <li class="list-group-item">
                    <h6 class="mb-1">${task.nama_tugas}</h6>
                </li>
            `).join('') : '<li class="list-group-item text-center">Belum Ada Tugas</li>';

            Swal.fire({
                title: `Daftar Kategori Tugas dari: ${user}`,
                html: `
                    <ul class="list-group">
                        ${tasksList}
                    </ul>
                `,
                confirmButtonColor: '#004B8F',
                width: '600px'
            });
        }
    </script>
</body>

</html>