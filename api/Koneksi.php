<?php
mysqli_report(MYSQLI_REPORT_OFF); 

$host = "localhost";
$user = "root";
$pass = "";
$db   = "otwin_db";

$koneksi = mysqli_connect($host, $user, $pass, $db);

if (!$koneksi) {
    die("Koneksi dengan database gagal: " . mysqli_connect_error());
}
?>