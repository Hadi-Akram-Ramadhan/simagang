<?php
include 'koneksi.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $editNama = $_POST['editNama'];
    $editGmail = $_POST['editGmail'];
    $editPass = $_POST['editPass'];
    $editRole = $_POST['editRole'];

    // Update data di database
    $sql = "UPDATE akun SET gmail='$editGmail', pass='$editPass', role='$editRole' WHERE nama='$editNama'";

    if ($conn->query($sql) === TRUE) {
        echo "Record updated successfully";
    } else {
        echo "Error updating record: " . $conn->error;
    }

    $conn->close();
}
header('Location: auth-admin.php');
exit();
?>
