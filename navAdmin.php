<?php
$sql = "SELECT first, second FROM settings WHERE id = 2";
$result = $conn->query($sql);
if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $welcomeText = $row['first'];
    $instructionText = $row['second'];
} else {
    $welcomeText = "Kementerian Perdagangan";
    $instructionText = "Direktorat Bahan Pokok dan Barang Penting";
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link rel="stylesheet" href="fontawesome/css/fontawesome.min.css">
    <link rel="stylesheet" href="bootstrap/css/bootstrap.min.css">
    <link rel="shortcut icon" href="image\kementrian.png">
    <link rel="stylesheet" href="css/kita.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&family=Roboto:wght@300;400;700&display=swap" rel="stylesheet">
</head>
<style>
    body {
        background-color: #fafafa;
        color: #484b6a;
        font-family: 'Roboto', sans-serif;
    }

    .navbar {
        position: fixed;
        top: 0;
        width: 100%;
        z-index: 1001;
        background-color: #ffffff;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        padding: 0.5rem 1rem;
        transition: all 0.3s ease;
    }

    .header {
        position: fixed;
        background-color: #ffffff;
        width: 100%;
        padding: 0.5rem 1rem;
        height: auto;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        z-index: 1002;
    }

    .kemendag {
        position: absolute;
        top: 10px;
        left: 10px;
        z-index: 1003;
        color: #484b6a;
        font-size: 22px;
        font-family: 'Poppins', sans-serif;
        font-weight: 600;
    }

    .sidebar {
        position: fixed;
        left: 0;
        top: 0;
        bottom: 0;
        width: 80px;
        background-color: #ffffff;
        border-right: 1px solid rgba(0, 79, 159, 0.1);
        z-index: 1000;
        box-shadow: 2px 0 5px rgba(0, 0, 0, 0.1);
        padding-top: 60px;
        display: flex;
        flex-direction: column;
        align-items: center;
        transition: width 0.2s ease;
        padding-bottom: 40px;
        overflow-x: hidden;
    }

    .sidebar.expanded {
        width: 200px;
    }

    .icon-container {
        width: 60px;
        height: 60px;
        border-radius: 15px;
        display: flex;
        justify-content: center;
        align-items: center;
        margin-bottom: 20px;
        margin-top: 50px;
        transition: background-color 0.2s ease, color 0.2s ease;
        cursor: pointer;
        color: #004F9F;
        /* Default icon color */
        position: relative;
        overflow: hidden;
    }

    .icon-container.active {
        color: #ffffff;
        /* Color when active */
        background-color: #004F9F;
        /* Background color when active */
    }

    .icon-container:hover {
        background-color: rgba(0, 79, 159, 0.1);
        color: #004F9F;
    }

    .container-fluid {
        max-width: 1400px;
        margin: 0 auto;
        padding-right: 15px;
        margin-left: 80px;
        transition: margin-left 0.3s ease;
    }

    .container-fluid.expanded {
        margin-left: 220px;
    }

    .foto {
        height: 45px;
        width: auto;
        filter: drop-shadow(1px 1px 20px rgba(0, 255, 238, 0.5));
        transition: transform 0.3s ease;
        margin-right: 15px;
    }

    .foto:hover {
        transform: scale(1.05);
    }

    .nama {
        color: #333333;
        font-size: 16px;
        font-weight: 500;
        margin: 0;
        padding: 0 15px;
        font-family: 'Poppins', sans-serif;
    }

    .logout {
        color: #dc3545;
        text-decoration: none;
        font-size: 14px;
        font-weight: 500;
        padding: 8px 20px;
        border-radius: 5px;
        transition: all 0.3s ease;
        border: 1px solid #dc3545;
    }

    .logout:hover {
        background-color: #dc3545;
        color: #ffffff;
        transform: translateY(-2px);
        box-shadow: 0 2px 5px rgba(220, 53, 69, 0.2);
    }

    .separator {
        border-left: 1px solid #484b6a;
        height: 25px;
        margin: 0 15px;
        opacity: 0.3;
    }

    .submenu {
        opacity: 0;
        display: none;
        flex-direction: column;
        align-items: flex-start;
        padding: 5px;
        transition: all 0.3s ease;
        max-height: 0;
        overflow: hidden;
        transform: translateY(-10px);
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .submenu-active {
        display: flex;
        max-height: 300px;
        opacity: 1;
        transform: translateY(0);
    }

    .submenu ul {
        list-style-type: none;
        padding: 0;
        margin: 0;
        width: 100%;
    }

    .submenu li {
        width: 100%;
        margin: 2px 0;
    }

    .submenu a {
        color: #004F9F;
        text-decoration: none;
        padding: 10px 15px;
        display: block;
        border-radius: 8px;
        transition: all 0.2s ease;
        font-size: 14px;
        width: 100%;
        position: relative;
        background: linear-gradient(90deg, rgba(0, 79, 159, 0) 0%, rgba(0, 79, 159, 0) 100%);
        background-size: 200% 100%;
        background-position: 100% 0;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .submenu a:hover {
        color: #004F9F;
        background-color: rgba(0, 79, 159, 0.08);
        transform: translateX(5px);
        background-position: 0 0;
        padding-left: 20px;
    }

    .sidebar {
        display: flex;
        flex-direction: column;
        padding-bottom: 20px;
        /* Nambahin padding di bawah */
    }

    .spacer {
        flex-grow: 1;
    }

    .sidebar .icon-container:last-child {
        margin-bottom: 10px;
        /* Kurangin margin bawah */
    }

    .modal {
        display: none;
        position: fixed;
        z-index: 999999999999999;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.4);
    }

    .modal-content-p {
        background-color: #fefefe;
        margin: 15% auto;
        padding: 20px;
        border: 1px solid #888;
        width: 80%;
        max-width: 500px;
    }

    .modal-content-p input,
    .modal-content-p textarea {
        width: 100%;
        margin-bottom: 10px;
        padding: 5px;
    }

    .button-group {
        text-align: right;
    }

    .button-group button {
        margin-left: 10px;
    }

    button {
        background-color: #E7E8D8;

        padding: 10px;
        border: none;
        border-radius: 5px;
        cursor: pointer;
    }

    button:hover {
        background-color: #9394a5;
    }

    .container-fluid {
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .foto {
        width: 50px;
        /* Sesuaiin ukurannya */
        height: auto;
        margin-right: 10px;
        margin-left: 10px;
    }

    .kemendag-text {
        display: flex;
        flex-direction: column;
    }

    .kemendag-text h2 {
        color: #004F9F;
        font-size: 18px;
        margin: 0;
        font-family: 'Poppins', sans-serif;
        font-weight: 600;
    }

    .kemendag-text p {
        color: #2E8B57;
        font-size: 14px;
        margin: 0;
        font-family: 'Poppins', sans-serif;
        font-weight: 400;
    }

    .navbar-brand {
        display: flex;
        align-items: center;
    }

    .navbar-brand img {
        margin-right: 10px;
    }

    .icon-container::after {
        content: '';
        position: absolute;
        width: 100%;
        height: 100%;
        background: rgba(0, 79, 159, 0.08);
        border-radius: 15px;
        top: 0;
        left: -100%;
        transition: all 0.3s ease;
    }

    .icon-container:hover::after {
        left: 0;
    }

    /* Animasi untuk icon container */
    .icon-container {
        position: relative;
        overflow: hidden;
    }

    .icon-container::after {
        content: '';
        position: absolute;
        width: 100%;
        height: 100%;
        background: rgba(0, 79, 159, 0.08);
        border-radius: 15px;
        top: 0;
        left: -100%;
        transition: all 0.3s ease;
    }

    .icon-container:hover::after {
        left: 0;
    }
</style>

<body>
    <header>
        <nav class="navbar navbar-expand navbar-grey bg-grey topbar mb-4 static-top shadow header">
            <div class="container-fluid">
                <div class="d-flex align-items-center">
                    <img class="foto" src="image\kementrian.png" alt="">
                    <div class="kemendag-text">
                        <h2><?php echo htmlspecialchars($welcomeText); ?></h2>
                        <p><?php echo htmlspecialchars($instructionText); ?></p>
                    </div>
                </div>
                <div class="d-flex align-items-center ml-auto">
                    <h1 class="nama"><?php echo htmlspecialchars($_SESSION['nama']); ?></h1>
                    <div class="separator"></div>
                    <a class="logout" href="logout.php">Log Out</a>
                </div>
            </div>
        </nav>
    </header>

    <div class="sidebar">
        <div class="icon-container" onclick="window.location.href='admin.php'">
            <i class="fa-solid fa-house"></i>
        </div>

        <div class="icon-container" onclick="toggleSubmenu('submenu-manage', this)">
            <i class="fa-solid fa-pen-to-square"></i>
        </div>
        <div class="submenu" id="submenu-manage">
            <ul>
                <li><a href="homeAdmin.php">Data Absen</a></li>
                <li><a href="manage-admin.php">Data Siswa</a></li>
                <li><a href="manage-guru.php">Persetujuan Magang</a></li>
                <li><a href="manage-sekolah.php">Data Sekolah</a></li>
            </ul>
        </div>

        <div class="icon-container" onclick="toggleSubmenu('submenu-auth', this)">
            <i class="fa-solid fa-key"></i>
        </div>
        <div class="submenu" id="submenu-auth">
            <ul>
                <li><a href="auth-admin.php">Manage Auth</a></li>
            </ul>
        </div>

        <div class="spacer"></div>

        <div class="icon-container" onclick="toggleSubmenu('submenu-settings', this)">
            <i class="fa-solid fa-gear"></i>
        </div>
        <div class="submenu" id="submenu-settings">
            <ul>
                <li><a href="#" onclick="openSettingsModal()">Login Layout</a></li>
                <li><a href="#" onclick="openEditAdminLayoutModal()">Admin Layout</a></li>
            </ul>
        </div>
    </div>

    <div id="settingsModal" class="modal">
        <div class="modal-content-p">
            <h2>Settings</h2>
            <form id="settingsForm">
                <input type="text" id="first" name="first" placeholder="First">
                <input type="text" id="second" name="second" placeholder="Second">
                <textarea id="description" name="description" placeholder="Description"></textarea>
                <div class="button-group">
                    <button type="submit">Save</button>
                    <button type="button" onclick="closeSettingsModal()">Cancel</button>
                </div>
            </form>
        </div>
    </div>

    <div id="editAdminLayoutModal" class="modal">
        <div class="modal-content-p">
            <h2>Edit Admin Layout</h2>
            <form id="editAdminLayoutForm">
                <input type="text" id="editAdminWelcome" name="first" placeholder="Welcome Text">
                <input type="text" id="editAdminInstruction" name="second" placeholder="Instruction Text">
                <textarea id="editAdminDescription" name="description" placeholder="Description"></textarea>
                <div class="button-group">
                    <button type="submit">Simpan</button>
                    <button type="button" onclick="closeEditAdminLayoutModal()">Batal</button>
                </div>
            </form>
        </div>
    </div>

    <script src="bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="fontawesome/js/all.min.js"></script>
    <script>
        function toggleSubmenu(id, clickedIcon) {
            const submenu = document.getElementById(id);
            const sidebar = document.querySelector('.sidebar');
            const allIcons = document.querySelectorAll('.icon-container');

            if (submenu.classList.contains('submenu-active')) {
                submenu.classList.remove('submenu-active');
                sidebar.classList.remove('expanded');
                setTimeout(() => {
                    submenu.style.display = 'none';
                }, 500);
                clickedIcon.classList.remove('active');
            } else {
                const activeSubmenus = document.querySelectorAll('.submenu-active');
                activeSubmenus.forEach(sub => {
                    sub.classList.remove('submenu-active');
                    sub.style.display = 'none';
                });
                allIcons.forEach(icon => {
                    icon.classList.remove('active');
                });
                submenu.style.display = 'flex';
                setTimeout(() => {
                    submenu.classList.add('submenu-active');
                    sidebar.classList.add('expanded');
                }, 10);
                clickedIcon.classList.add('active');
            }
        }

        function openSettingsModal() {
            document.getElementById('settingsModal').style.display = 'block';
            loadSettings();
        }

        function closeSettingsModal() {
            document.getElementById('settingsModal').style.display = 'none';
        }

        function loadSettings() {
            fetch('get_settings.php')
                .then(response => response.json())
                .then(data => {
                    document.getElementById('first').value = data.first;
                    document.getElementById('second').value = data.second;
                    document.getElementById('description').value = data.description;
                });
        }

        document.getElementById('settingsForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);

            fetch('update_settings.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Mantap!',
                            text: 'Pengaturan berhasil diperbarui!'
                        }).then(() => {
                            closeSettingsModal();
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Waduh...',
                            text: 'Ada masalah nih pas update pengaturan'
                        });
                    }
                });
        });

        function openEditAdminLayoutModal() {
            document.getElementById('editAdminLayoutModal').style.display = 'block';
            loadAdminLayoutData();
        }

        function closeEditAdminLayoutModal() {
            document.getElementById('editAdminLayoutModal').style.display = 'none';
        }

        function loadAdminLayoutData() {
            fetch('get_data.php?id=2')
                .then(response => response.json())
                .then(data => {
                    document.getElementById('editAdminWelcome').value = data.first;
                    document.getElementById('editAdminInstruction').value = data.second;
                    document.getElementById('editAdminDescription').value = data.description;
                });
        }

        document.getElementById('editAdminLayoutForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            formData.append('id', 2);

            fetch('update_data.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Mantap!',
                            text: 'Layout admin udah diupdate nih!'
                        }).then(() => {
                            closeEditAdminLayoutModal();
                            location.reload();
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Duh...',
                            text: 'Gagal update layout admin. Coba lagi deh!'
                        });
                    }
                });
        });
    </script>
</body>

</html>

</html>