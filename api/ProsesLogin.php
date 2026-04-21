<?php
    session_start();
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
                // Simpan sesi sebagai Admin
                $_SESSION['username']  = $admin['username'];
                $_SESSION['nama']      = $admin['nama'];
                $_SESSION['role']      = 'admin'; // Kita set manual karena tabelnya udah pisah
                $_SESSION['is_master'] = $admin['is_master']; // Bawa status masternya
 
                header("Location: admin/Dashboard.php");
                exit();
            } else {
                echo "<script>
                        alert('Password yang Anda masukkan salah!');
                        window.location.href='../index.php';
                      </script>";
                exit();
            }
        }
 
        // ── 2. JIKA BUKAN ADMIN, CEK DI TABEL USERS ──
        $stmt_user = mysqli_prepare($koneksi, "SELECT * FROM users WHERE username = ?");
        mysqli_stmt_bind_param($stmt_user, "s", $username);
        mysqli_stmt_execute($stmt_user);
        $result_user = mysqli_stmt_get_result($stmt_user);
 
        if (mysqli_num_rows($result_user) === 1) {
            $user = mysqli_fetch_assoc($result_user);
 
            if (password_verify($password, $user['password'])) {
                // Simpan sesi sebagai User biasa
                $_SESSION['username'] = $user['username'];
                $_SESSION['nama']     = $user['nama'];
                $_SESSION['role']     = 'user'; 
 
                header("Location: ../index.php");
                exit();
            } else {
                echo "<script>
                        alert('Password yang Anda masukkan salah!');
                        window.location.href='../index.php';
                      </script>";
                exit();
            }
        } 
        
        // ── 3. JIKA TIDAK DITEMUKAN DI KEDUA TABEL ──
        echo "<script>
                alert('Nama akun (Username) tidak ditemukan!');
                window.location.href='../index.php';
              </script>";
        exit();
    }
?>