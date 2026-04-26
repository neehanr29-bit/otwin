<?php
    include 'Koneksi.php';

    if(isset($_POST['register'])) {
        $nama = $_POST['nama'];
        $tanggal_lahir = $_POST['tanggal_lahir'];
        $email = $_POST['email'];
        $username = $_POST['username'];
        $password = $_POST['password'];
        
        $password_hash = password_hash($password, PASSWORD_DEFAULT);

        $query = "INSERT INTO users (nama, tanggal_lahir, email, username, password) 
                  VALUES ('$nama', '$tanggal_lahir', '$email', '$username', '$password_hash')";
        
        $result = mysqli_query($koneksi, $query);

        if ($result){
            echo "<script>
                    alert('Akun OTWin berhasil dibuat! Silakan Masuk.'); 
                    window.location.href='../index.php';
                  </script>";
        } else {
            echo "Register Gagal: " . mysqli_error($koneksi);
        }
    }
?>