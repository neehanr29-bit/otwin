<?php
// Hapus semua cookie OTWin
$expire = time() - 3600;
setcookie('otwin_username',  '', $expire, '/', '', true, true);
setcookie('otwin_nama',      '', $expire, '/', '', true, true);
setcookie('otwin_role',      '', $expire, '/', '', true, true);
setcookie('otwin_is_master', '', $expire, '/', '', true, true);
 
header("Location: /");
exit();
?>