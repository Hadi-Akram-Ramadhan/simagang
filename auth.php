<?php
require_once('koneksi.php');

if (!isset($_SESSION['user']) && !isset($_SESSION['admin']) && !isset($_SESSION['teacher']) && !isset($_SESSION['pembimbing'])) {
    header("Location: index.php");
    exit();
}
