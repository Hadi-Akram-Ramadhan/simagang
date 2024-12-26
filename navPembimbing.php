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
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
</head>
<style>
    body {
        background-color: #fafafa;
        color: #484b6a;
        font-family: 'Poppins', sans-serif;
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
        position: relative;
        overflow: hidden;
    }

    .icon-container.active {
        color: #ffffff;
        background-color: #004F9F;
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
        display: flex;
        align-items: center;
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
        font-family: 'Poppins', sans-serif;
        font-weight: 500;
        font-size: 16px;
        margin: 0;
        padding: 0 15px;
        margin-bottom: 0;
    }

    .logout {
        font-family: 'Poppins', sans-serif;
        font-weight: 500;
        color: #dc3545;
        text-decoration: none;
        font-size: 14px;
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
</style>


<body>
    <header>
        <nav class="navbar navbar-expand navbar-grey bg-grey topbar mb-4 static-top shadow header">
            <div class="container-fluid d-flex">
                <a class="navbar-brand" href="homePemb.php">
                    <img class="foto" src="image\kementrian.png" alt="">
                </a>
                <div class="ms-auto d-flex align-items-center">
                    <h1 class="nama"><?php echo htmlspecialchars($_SESSION['nama']); ?></h1>
                    <div class="separator"></div>
                    <a class="logout" href="logout.php">Log Out</a>
                </div>
            </div>
        </nav>
    </header>

    <script src="bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="fontawesome/js/all.min.js"></script>
    <script>
        function aktifkanLink(elemenDiklik) {
            const semuaTautan = document.querySelectorAll('.icon-container a');
            semuaTautan.forEach(tautan => {
                tautan.classList.remove('active');
            });
            elemenDiklik.classList.add('active');
            setTimeout(function() {
                elemenDiklik.classList.remove('active');
            }, 1000);
        }
    </script>
</body>

</html>