<?php

include 'Koneksi.php';

// Harus sudah login
if (!isset($_COOKIE['otwin_username']) || $_COOKIE['otwin_username'] === '') {
    echo "<script>alert('Kamu harus login dulu!'); window.location.href='../index.php';</script>";
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['booking'])) {
    $username     = $_COOKIE['otwin_username'];
    $destinasi    = trim($_POST['destinasi']);
    $nama_pemesan = trim($_POST['nama_pemesan']);
    $tanggal      = $_POST['tanggal'];
    $jumlah       = (int) $_POST['jumlah'];
    $durasi       = (int) $_POST['durasi'];

    if (!$nama_pemesan || !$tanggal || !$destinasi) {
        echo "<script>alert('Semua field wajib diisi!'); window.history.back();</script>";
        exit();
    }

    $stmt = mysqli_prepare($koneksi,
        "INSERT INTO booking (username, destinasi, nama_pemesan, tanggal, jumlah_orang, durasi, status)
         VALUES (?, ?, ?, ?, ?, ?, 'pending')");
    mysqli_stmt_bind_param($stmt, "ssssii", $username, $destinasi, $nama_pemesan, $tanggal, $jumlah, $durasi);

    if (mysqli_stmt_execute($stmt)) {
        echo "<script>alert('🎉 Booking berhasil! Menunggu konfirmasi admin.'); window.location.href='../index.php';</script>";
    } else {
        echo "<script>alert('Booking gagal: " . mysqli_error($koneksi) . "'); window.history.back();</script>";
    }
}
?>
