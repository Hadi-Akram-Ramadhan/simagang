<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link rel="stylesheet" href="fontawesome/css/fontawesome.min.css">
    <link rel="stylesheet" href="bootstrap/css/bootstrap.min.css">
    <link rel="shortcut icon" href="image\kementrian.png">
    <link rel="stylesheet" href="css/kita.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body,
        .nama,
        .kemendag {
            font-family: 'Poppins', sans-serif;
        }
    </style>
</head>
<style>
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

    .footer {
        position: fixed;
        bottom: 0;
        width: 100%;
        background-color: #ffffff;
        padding: 0.8rem 0;
        box-shadow: 0 -2px 5px rgba(0, 0, 0, 0.1);
        z-index: 1001;
    }

    .footer .list-group-item {
        padding: 0.5rem 1.5rem;
        transition: all 0.2s ease;
    }

    .footer .list-group-item:hover {
        background-color: rgba(0, 0, 0, 0.1);
        transform: translateY(-2px);
    }

    .footer i {
        font-size: 1.2rem;
        color: #333333 !important;
    }

    .container-fluid {
        max-width: 1400px;
        margin: 0 auto;

    }

    .foto {
        height: 50px;
        width: auto;
        filter: drop-shadow(1px 1px 20px rgb(0, 255, 238));
        transition: transform 0.3s ease;
    }

    .foto:hover {
        transform: scale(1.05);
    }

    .nama {
        color: #333333;
        font-size: 1.2rem;
        margin: 0;
        padding: 0 15px;
    }

    .logout {
        padding-left: 10px;
    }

    .separator {
        border-left: 1px solid #484b6a;
        height: 30px;
        margin: auto 20px;
    }

    .kemendag {
        z-index: 1;
        color: #E0E0E0;
        padding-left: 10px;
        font-size: 22px;
        margin-right: auto;
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }

    @media (max-width: 768px) {
        .navbar {
            padding: 0.3rem;
        }

        .foto {
            height: 40px;
        }

        .nama {
            font-size: 1rem;
        }

        .footer {
            padding: 0.5rem 0;
        }

        .footer .list-group-item {
            padding: 0.4rem 1rem;
        }

        .footer i {
            font-size: 1rem;
        }

        .separator {
            margin: auto 10px;
        }
    }
</style>

<body>
    <header>
        <nav class="navbar navbar-expand navbar-grey bg-grey topbar mb-4 static-top shadow">
            <div class="container-fluid">
                <!-- Logo dan Nama (Kiri) -->
                <div class="d-flex align-items-center">
                    <a class="navbar-brand" href="homeUser.php">
                        <img class="foto" src="image\kementrian.png" alt="">
                    </a>
                    <h1 class="nama mb-0"> Selamat Datang, <?php echo htmlspecialchars($_SESSION['nama']); ?>!</h1>
                </div>

                <!-- Tombol Keluar (Kanan) -->
                <a class="btn btn-outline-danger" href="logout.php">Keluar</a>
            </div>
        </nav>
    </header>



    <footer class="footer">
        <div class="container-fluid">
            <div class="list-group list-group-horizontal justify-content-center">
                <a href="homeUser.php" class="list-group-item list-group-item-action py-2 ripple text-center" aria-current="true">
                    <i class="fa-solid fa-house" style="color: #004F9F;"></i>
                </a>
                <a href="photoUser.php" class="list-group-item list-group-item-action py-2 ripple text-center">
                    <i class="fa-solid fa-camera" style="color: #004F9F;"></i>
                </a>
                <a href="timeline.php" class="list-group-item list-group-item-action py-2 ripple text-center">
                    <i class="fa-solid fa-file-import" style="color: #2E8B57;"></i>
                </a>
                <a href="historyUser.php" class="list-group-item list-group-item-action py-2 ripple text-center">
                    <i class="fa-solid fa-clock-rotate-left" style="color: #2E8B57;"></i>
                </a>
            </div>
        </div>
    </footer>

    <script src="bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="fontawesome/js/all.min.js"></script>
    <script>
        function aktifkanLink(elemenDiklik) {
            const semuaTautan = document.querySelectorAll('.list-group-item');
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