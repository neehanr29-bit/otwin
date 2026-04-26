<?php
    session_start();
    // Menghapus semua data sesi
    session_destroy();
    
    // Arahkan kembali ke halaman utama
    header("Location: ../index.php");
    exit();
?>