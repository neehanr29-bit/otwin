<?php
include 'Koneksi.php';
 
if (isset($_POST['login'])) {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
 
    // ── 1. CEK DI TABEL ADMIN DULU ──
    $stmt_admin = mysqli_prepare($koneksi, "SELECT * FROM admin WHERE username = ?");
    mysqli_stmt_bind_param($stmt_admin, "s", $username);
    mysqli_stmt_execute($stmt_admin);
    $result_admin = mysqli_stmt_get_result($stmt_admin);
 
    if (mysqli_num_rows($result_admin) === 1) {
        $admin = mysqli_fetch_assoc($result_admin);
 
        if (password_verify($password, $admin['password'])) {
            // Simpan ke cookie (berlaku 1 hari)
            $expire = time() + 86400;
            setcookie('otwin_username',  $admin['username'],             $expire, '/', '', true, true);
            setcookie('otwin_nama',      $admin['nama'],                 $expire, '/', '', true, true);
            setcookie('otwin_role',      'admin',                        $expire, '/', '', true, true);
            setcookie('otwin_is_master', (string)$admin['is_master'],    $expire, '/', '', true, true);
 
            header("Location: /api/admin/Dashboard.php");
            exit();
        } else {
            echo "<script>alert('Password yang Anda masukkan salah!'); window.location.href='/';</script>";
            exit();
        }
    }
 
    // ── 2. CEK DI TABEL USERS ──
    $stmt_user = mysqli_prepare($koneksi, "SELECT * FROM users WHERE username = ?");
    mysqli_stmt_bind_param($stmt_user, "s", $username);
    mysqli_stmt_execute($stmt_user);
    $result_user = mysqli_stmt_get_result($stmt_user);
 
    if (mysqli_num_rows($result_user) === 1) {
        $user = mysqli_fetch_assoc($result_user);
 
        if (password_verify($password, $user['password'])) {
            $expire = time() + 86400;
            setcookie('otwin_username', $user['username'], $expire, '/', '', true, true);
            setcookie('otwin_nama',     $user['nama'],     $expire, '/', '', true, true);
            setcookie('otwin_role',     'user',            $expire, '/', '', true, true);
 
            header("Location: /");
            exit();
        } else {
            echo "<script>alert('Password yang Anda masukkan salah!'); window.location.href='/';</script>";
            exit();
        }
    }
 
    // ── 3. USERNAME TIDAK DITEMUKAN ──
    echo "<script>alert('Nama akun tidak ditemukan!'); window.location.href='/';</script>";
    exit();
}
?>