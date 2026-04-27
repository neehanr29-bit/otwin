<?php

if (!isset($_COOKIE['otwin_role']) || $_COOKIE['otwin_role'] !== 'admin') {
    header("Location: /");
    exit();
}

include '../Koneksi.php';

$pesan = '';  
$tipe  = '';

// ════════════════════════════════════════════
//  HANDLE POST ACTIONS
// ════════════════════════════════════════════
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {

    $aksi = $_POST['action'];

    // ── CRUD AKUN (Multi-Tabel: Admin & User) ──
    if (in_array($aksi, ['create_akun', 'update_akun', 'delete_akun'])) {

        if ($aksi === 'create_akun') {
            $nama          = trim($_POST['nama']);
            $tanggal_lahir = $_POST['tanggal_lahir'] ?? null;
            $email         = trim($_POST['email']);
            $username      = trim($_POST['username']);
            $password      = $_POST['password'];
            $target_role   = $_POST['role']; // 'admin' atau 'user'

            if ($nama && $email && $username && $password) {
                $hash = password_hash($password, PASSWORD_DEFAULT);
                if ($target_role === 'admin') {
                    $stmt = mysqli_prepare($koneksi, "INSERT INTO admin (nama, email, username, password, is_master) VALUES (?,?,?,?,0)");
                    mysqli_stmt_bind_param($stmt, "ssss", $nama, $email, $username, $hash);
                } else {
                    $stmt = mysqli_prepare($koneksi, "INSERT INTO users (nama, tanggal_lahir, email, username, password) VALUES (?,?,?,?,?)");
                    mysqli_stmt_bind_param($stmt, "sssss", $nama, $tanggal_lahir, $email, $username, $hash);
                }

                if (mysqli_stmt_execute($stmt)) {
                    $pesan = "✅ Akun <strong>$username</strong> berhasil ditambahkan!"; $tipe = 'sukses';
                } else {
                    $pesan = "❌ Gagal: " . mysqli_error($koneksi); $tipe = 'error';
                }
            } else {
                $pesan = "⚠️ Semua field wajib diisi!"; $tipe = 'warning';
            }
        }

        if ($aksi === 'update_akun') {
            $id          = (int) $_POST['id'];
            $nama        = trim($_POST['nama']);
            $email       = trim($_POST['email']);
            $username    = trim($_POST['username']);
            $target_role = $_POST['role'];
            $pw_baru     = trim($_POST['password_baru']);

            if ($target_role === 'admin') {
                if ($pw_baru !== '') {
                    $hash = password_hash($pw_baru, PASSWORD_DEFAULT);
                    $stmt = mysqli_prepare($koneksi, "UPDATE admin SET nama=?, email=?, username=?, password=? WHERE id=?");
                    mysqli_stmt_bind_param($stmt, "ssssi", $nama, $email, $username, $hash, $id);
                } else {
                    $stmt = mysqli_prepare($koneksi, "UPDATE admin SET nama=?, email=?, username=? WHERE id=?");
                    mysqli_stmt_bind_param($stmt, "sssi", $nama, $email, $username, $id);
                }
            } else {
                $tgl = $_POST['tanggal_lahir'];
                if ($pw_baru !== '') {
                    $hash = password_hash($pw_baru, PASSWORD_DEFAULT);
                    $stmt = mysqli_prepare($koneksi, "UPDATE users SET nama=?, tanggal_lahir=?, email=?, username=?, password=? WHERE id=?");
                    mysqli_stmt_bind_param($stmt, "sssssi", $nama, $tgl, $email, $username, $hash, $id);
                } else {
                    $stmt = mysqli_prepare($koneksi, "UPDATE users SET nama=?, tanggal_lahir=?, email=?, username=? WHERE id=?");
                    mysqli_stmt_bind_param($stmt, "ssssi", $nama, $tgl, $email, $username, $id);
                }
            }
            if (mysqli_stmt_execute($stmt)) { $pesan = "✅ Data <strong>$username</strong> berhasil diperbarui!"; $tipe = 'sukses'; }
            else { $pesan = "❌ Gagal: " . mysqli_error($koneksi); $tipe = 'error'; }
        }

        if ($aksi === 'delete_akun') {
            $id          = (int) $_POST['id'];
            $target_role = $_POST['role'];

            if ($target_role === 'admin') {
                $cek = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT username, is_master FROM admin WHERE id=$id"));
                if ($cek['is_master'] == 1) {
                    $pesan = "Lau sape mpruy mau hapus raja admin"; $tipe = 'error';
                } elseif ($cek['username'] === $_COOKIE['otwin_username']) {
                    $pesan = "⚠️ Kamu tidak bisa menghapus akun sendiri!"; $tipe = 'warning';
                } else {
                    mysqli_query($koneksi, "DELETE FROM admin WHERE id=$id");
                    $pesan = "🗑️ Admin berhasil dihapus."; $tipe = 'sukses';
                }
            } else {
                mysqli_query($koneksi, "DELETE FROM users WHERE id=$id");
                $pesan = "🗑️ User berhasil dihapus."; $tipe = 'sukses';
            }
        }
    }

    // ── CRUD DESTINASI ──
    if (in_array($aksi, ['create_dest', 'update_dest', 'delete_dest'])) {
        if ($aksi === 'create_dest') {
            $stmt = mysqli_prepare($koneksi, "INSERT INTO destinasi (nama, emoji, tema, deskripsi, harga) VALUES (?,?,?,?,?)");
            mysqli_stmt_bind_param($stmt, "ssssi", $_POST['nama_dest'], $_POST['emoji'], $_POST['tema'], $_POST['deskripsi'], $_POST['harga']);
            if (mysqli_stmt_execute($stmt)) { $pesan = "✅ Destinasi berhasil ditambahkan!"; $tipe = 'sukses'; }
        }
        if ($aksi === 'update_dest') {
            $stmt = mysqli_prepare($koneksi, "UPDATE destinasi SET nama=?,emoji=?,tema=?,deskripsi=?,harga=? WHERE id=?");
            mysqli_stmt_bind_param($stmt, "ssssii", $_POST['nama_dest'], $_POST['emoji'], $_POST['tema'], $_POST['deskripsi'], $_POST['harga'], $_POST['id']);
            if (mysqli_stmt_execute($stmt)) { $pesan = "✅ Destinasi berhasil diperbarui!"; $tipe = 'sukses'; }
        }
        if ($aksi === 'delete_dest') {
            mysqli_query($koneksi, "DELETE FROM destinasi WHERE id=" . (int)$_POST['id']);
            $pesan = "🗑️ Destinasi berhasil dihapus."; $tipe = 'sukses';
        }
    }

    // ── KELOLA BOOKING ──
    if ($aksi === 'update_status') {
        $stmt = mysqli_prepare($koneksi, "UPDATE booking SET status=? WHERE id=?");
        mysqli_stmt_bind_param($stmt, "si", $_POST['status'], $_POST['id']);
        if (mysqli_stmt_execute($stmt)) { $pesan = "✅ Status booking diubah."; $tipe = 'sukses'; }
    }
    if ($aksi === 'delete_booking') {
        mysqli_query($koneksi, "DELETE FROM booking WHERE id=" . (int)$_POST['id']);
        $pesan = "🗑️ Booking berhasil dihapus."; $tipe = 'sukses';
    }
}

// ── READ DATA ──
$semua_user    = mysqli_query($koneksi, "SELECT * FROM users ORDER BY id ASC");
$semua_admin   = mysqli_query($koneksi, "SELECT * FROM admin ORDER BY id ASC");
$semua_dest    = mysqli_query($koneksi, "SELECT * FROM destinasi ORDER BY id ASC");
$semua_booking = mysqli_query($koneksi, "SELECT * FROM booking ORDER BY id DESC");

$total_user    = mysqli_num_rows($semua_user);
$total_admin   = mysqli_num_rows($semua_admin);
$total_dest    = mysqli_num_rows($semua_dest);
$total_booking = mysqli_num_rows($semua_booking);

$tab = $_GET['tab'] ?? 'user';
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Dashboard Admin – OTWin</title>
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700;900&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet"/>
  <link rel="stylesheet" href="dashboard.css"/>
</head>
<body>

<nav class="navbar">
  <div class="navbar-brand">
    <div class="navbar-logo">OT<span>Win</span></div>
    <span class="badge-admin">Admin Panel</span>
  </div>
  <div class="navbar-right">
    <span class="navbar-greeting">Halo, <strong><?= htmlspecialchars($_COOKIE['username']) ?></strong> 👋</span>
    <a href="../../index.php" class="btn-outline-teal">🏠 Ke Beranda</a>
    <a href="../ProsesLogout.php" class="btn-danger">Logout</a>
  </div>
</nav>

<div class="main">

  <div class="page-header">
    <h1>Dashboard <em>Admin</em></h1>
    <p>Kelola data User, Admin, Destinasi, dan Booking.</p>
  </div>

  <?php if ($pesan): ?>
    <div class="alert alert-<?= $tipe ?>"><?= $pesan ?></div>
  <?php endif; ?>

  <div class="stats">
    <div class="stat-card teal">  <div class="label">Total User</div>      <div class="value"><?= $total_user ?></div>    </div>
    <div class="stat-card gold">  <div class="label">Total Admin</div>     <div class="value"><?= $total_admin ?></div>   </div>
    <div class="stat-card blue">  <div class="label">Total Destinasi</div> <div class="value"><?= $total_dest ?></div>    </div>
    <div class="stat-card green"> <div class="label">Total Booking</div>   <div class="value"><?= $total_booking ?></div> </div>
  </div>

  <div class="tabs">
    <a href="?tab=user"    class="tab <?= $tab==='user'    ? 'active-teal'  : '' ?>">👥 Kelola User</a>
    <a href="?tab=admin"   class="tab <?= $tab==='admin'   ? 'active-gold'  : '' ?>">⚙️ Kelola Admin</a>
    <a href="?tab=dest"    class="tab <?= $tab==='dest'    ? 'active-blue'  : '' ?>">🏝️ Kelola Destinasi</a>
    <a href="?tab=booking" class="tab <?= $tab==='booking' ? 'active-green' : '' ?>">🎟️ Kelola Booking</a>
  </div>

<?php
// ════════════════════════════════
//  TAB: USER & ADMIN
// ════════════════════════════════
if ($tab === 'user' || $tab === 'admin'):
    $is_admin_tab = ($tab === 'admin');
    $data_akun    = $is_admin_tab ? $semua_admin : $semua_user;
    $label        = $is_admin_tab ? 'Admin' : 'User';
    $btn_warna    = $is_admin_tab ? 'gold' : 'teal';
?>
  <div class="toolbar">
    <button class="btn-add <?= $btn_warna ?>" onclick="bukaModalAkun('<?= $tab ?>')">＋ Tambah <?= $label ?> Baru</button>
  </div>
  <div class="table-wrap">
    <table>
      <thead>
        <tr>
          <th>ID</th><th>Nama</th><th>Username</th><th>Email</th><?php if(!$is_admin_tab) echo "<th>Tgl. Lahir</th>"; ?><th>Role</th><th class="center">Aksi</th>
        </tr>
      </thead>
      <tbody>
        <?php if (mysqli_num_rows($data_akun) === 0): ?>
          <tr><td colspan="7" class="empty">Belum ada data <?= $label ?>.</td></tr>
        <?php else: while ($u = mysqli_fetch_assoc($data_akun)): ?>
          <tr>
            <td class="td-id">#<?= $u['id'] ?></td>
            <td class="td-name"><?= htmlspecialchars($u['nama']) ?></td>
            <td class="<?= $is_admin_tab ? 'td-gold' : 'td-teal' ?>">@<?= htmlspecialchars($u['username']) ?></td>
            <td class="td-muted"><?= htmlspecialchars($u['email']) ?></td>
            <?php if(!$is_admin_tab) echo "<td class='td-muted'>".htmlspecialchars($u['tanggal_lahir'] ?? '-')."</td>"; ?>
            <td>
              <?php if ($is_admin_tab && $u['is_master'] == 1): ?>
                  <span class="badge" style="background:rgba(200,146,42,.1); color:var(--gold); border:1px solid var(--gold)">👑 Master</span>
              <?php else: ?>
                  <span class="badge badge-<?= $is_admin_tab ? 'admin' : 'user' ?>"><?= $label ?></span>
              <?php endif; ?>
            </td>
            <td class="center">
              <div class="action-group">
                <?php if ($is_admin_tab && $u['is_master'] == 1): ?>
                    <small class="td-muted">N/A</small>
                <?php else: ?>
                    <button class="btn-edit" onclick='bukaModalEditAkun(<?= json_encode($u) ?>, "<?= $tab ?>")'>✏️ Edit</button>
                    <form method="POST" action="?tab=<?= $tab ?>" onsubmit="return confirm('Hapus akun ini?')" style="display:inline">
                      <input type="hidden" name="action" value="delete_akun"/>
                      <input type="hidden" name="id" value="<?= $u['id'] ?>"/>
                      <input type="hidden" name="role" value="<?= $tab ?>"/>
                      <button type="submit" class="btn-del">🗑️ Hapus</button>
                    </form>
                <?php endif; ?>
              </div>
            </td>
          </tr>
        <?php endwhile; endif; ?>
      </tbody>
    </table>
  </div>

<?php
// ════════════════════════════════
//  TAB: DESTINASI
// ════════════════════════════════
elseif ($tab === 'dest'):
?>
  <div class="toolbar">
    <button class="btn-add blue" onclick="bukaModalDest()">＋ Tambah Destinasi Baru</button>
  </div>
  <div class="table-wrap">
    <table>
      <thead>
        <tr>
          <th>ID</th><th>Nama Destinasi</th><th>Emoji</th><th>Tema</th><th>Harga</th><th>Deskripsi</th><th class="center">Aksi</th>
        </tr>
      </thead>
      <tbody>
        <?php if (mysqli_num_rows($semua_dest) === 0): ?>
          <tr><td colspan="7" class="empty">Belum ada destinasi.</td></tr>
        <?php else:
          $temaLabel = ['teal' => '🟢 Teal', 'gold' => '🟡 Gold', 'blue' => '🔵 Biru'];
          while ($d = mysqli_fetch_assoc($semua_dest)): ?>
          <tr>
            <td class="td-id">#<?= $d['id'] ?></td>
            <td class="td-name"><?= htmlspecialchars($d['nama']) ?></td>
            <td style="font-size:1.4rem"><?= htmlspecialchars($d['emoji']) ?></td>
            <td class="td-muted"><?= $temaLabel[$d['tema']] ?? $d['tema'] ?></td>
            <td class="td-price">Rp <?= number_format($d['harga'], 0, ',', '.') ?></td>
            <td class="td-muted td-truncate"><?= htmlspecialchars($d['deskripsi'] ?? '-') ?></td>
            <td class="center">
              <div class="action-group">
                <button class="btn-edit" onclick='bukaModalEditDest(<?= json_encode($d) ?>)'>✏️ Edit</button>
                <form method="POST" action="?tab=dest" onsubmit="return confirm('Hapus destinasi ini?')" style="display:inline">
                  <input type="hidden" name="action" value="delete_dest"/>
                  <input type="hidden" name="id" value="<?= $d['id'] ?>"/>
                  <button type="submit" class="btn-del">🗑️ Hapus</button>
                </form>
              </div>
            </td>
          </tr>
        <?php endwhile; endif; ?>
      </tbody>
    </table>
  </div>

<?php
// ════════════════════════════════
//  TAB: BOOKING
// ════════════════════════════════
elseif ($tab === 'booking'):
?>
  <div class="table-wrap" style="margin-top:16px">
    <table>
      <thead>
        <tr>
          <th>ID</th><th>Username</th><th>Destinasi</th><th>Nama Pemesan</th>
          <th>Tanggal</th><th class="center">Orang</th><th class="center">Durasi</th>
          <th>Status</th><th class="center">Aksi</th>
        </tr>
      </thead>
      <tbody>
        <?php if (mysqli_num_rows($semua_booking) === 0): ?>
          <tr><td colspan="9" class="empty">Belum ada data booking.</td></tr>
        <?php else: while ($b = mysqli_fetch_assoc($semua_booking)): ?>
          <tr>
            <td class="td-id">#<?= $b['id'] ?></td>
            <td class="td-teal">@<?= htmlspecialchars($b['username']) ?></td>
            <td class="td-name"><?= htmlspecialchars($b['destinasi']) ?></td>
            <td><?= htmlspecialchars($b['nama_pemesan']) ?></td>
            <td class="td-muted"><?= htmlspecialchars($b['tanggal']) ?></td>
            <td class="center"><?= $b['jumlah_orang'] ?></td>
            <td class="center"><?= $b['durasi'] ?> hari</td>
            <td><span class="badge badge-<?= $b['status'] ?>"><?= ucfirst($b['status']) ?></span></td>
            <td class="center">
              <div class="action-group">
                <form method="POST" action="?tab=booking" style="display:inline">
                  <input type="hidden" name="action" value="update_status"/>
                  <input type="hidden" name="id" value="<?= $b['id'] ?>"/>
                  <input type="hidden" name="status" value="dikonfirmasi"/>
                  <button type="submit" class="btn-confirm" title="Konfirmasi">✅</button>
                </form>
                <form method="POST" action="?tab=booking" style="display:inline">
                  <input type="hidden" name="action" value="update_status"/>
                  <input type="hidden" name="id" value="<?= $b['id'] ?>"/>
                  <input type="hidden" name="status" value="dibatalkan"/>
                  <button type="submit" class="btn-cancel" title="Batalkan">❌</button>
                </form>
                <form method="POST" action="?tab=booking" onsubmit="return confirm('Hapus booking ini?')" style="display:inline">
                  <input type="hidden" name="action" value="delete_booking"/>
                  <input type="hidden" name="id" value="<?= $b['id'] ?>"/>
                  <button type="submit" class="btn-del" title="Hapus">🗑️</button>
                </form>
              </div>
            </td>
          </tr>
        <?php endwhile; endif; ?>
      </tbody>
    </table>
  </div>

<?php endif; ?>
</div><div class="modal-overlay" id="modalAkun">
  <div class="modal-box">
    <button class="modal-close" onclick="tutupModal('modalAkun')">✕</button>
    <div class="modal-icon" id="akunEmoji">👤</div>
    <div class="modal-title" id="akunTitle">Tambah Akun</div>
    <div class="modal-sub">Isi semua field di bawah ini.</div>
    <form method="POST" id="formAkun">
      <input type="hidden" name="action" value="create_akun"/>
      <input type="hidden" name="role" id="akunRole" value="user"/>
      <div class="form-row">
        <div class="form-col"><label class="form-label">Nama Lengkap</label><input type="text" name="nama" required placeholder="Nama lengkap" class="form-input"/></div>
        <div class="form-col" id="groupTgl"><label class="form-label">Tanggal Lahir</label><input type="date" name="tanggal_lahir" class="form-input"/></div>
      </div>
      <div class="form-group"><label class="form-label">Email</label><input type="email" name="email" required placeholder="email@contoh.com" class="form-input"/></div>
      <div class="form-group"><label class="form-label">Username</label><input type="text" name="username" required placeholder="nama_akun" class="form-input"/></div>
      <div class="form-group form-mb"><label class="form-label">Password</label><input type="password" name="password" required placeholder="Buat password" class="form-input"/></div>
      <button type="submit" id="akunBtn" class="btn-submit teal">＋ Buat Akun →</button>
    </form>
  </div>
</div>

<div class="modal-overlay" id="modalEditAkun">
  <div class="modal-box">
    <button class="modal-close" onclick="tutupModal('modalEditAkun')">✕</button>
    <div class="modal-icon">✏️</div>
    <div class="modal-title">Edit Data Akun</div>
    <div class="modal-sub">Ubah data yang diinginkan, lalu simpan.</div>
    <form method="POST">
      <input type="hidden" name="action" value="update_akun"/>
      <input type="hidden" name="id" id="eAkunId"/>
      <input type="hidden" name="role" id="eAkunRoleTarget"/>
      <div class="form-row">
        <div class="form-col"><label class="form-label">Nama Lengkap</label><input type="text" name="nama" id="eAkunNama" required class="form-input"/></div>
        <div class="form-col" id="eGroupTgl"><label class="form-label">Tanggal Lahir</label><input type="date" name="tanggal_lahir" id="eAkunTgl" class="form-input"/></div>
      </div>
      <div class="form-group"><label class="form-label">Email</label><input type="email" name="email" id="eAkunEmail" required class="form-input"/></div>
      <div class="form-group"><label class="form-label">Username</label><input type="text" name="username" id="eAkunUsername" required class="form-input"/></div>
      <div class="form-group form-mb"><label class="form-label">Password Baru <span>(kosongkan jika tidak diubah)</span></label><input type="password" name="password_baru" placeholder="Isi untuk mengganti password" class="form-input"/></div>
      <button type="submit" id="eAkunBtn" class="btn-submit teal">💾 Simpan Perubahan →</button>
    </form>
  </div>
</div>

<div class="modal-overlay" id="modalDest">
  <div class="modal-box">
    <button class="modal-close" onclick="tutupModal('modalDest')">✕</button>
    <div class="modal-icon">🏝️</div>
    <div class="modal-title">Tambah Destinasi Baru</div>
    <div class="modal-sub">Isi data destinasi wisata baru.</div>
    <form method="POST" action="?tab=dest">
      <input type="hidden" name="action" value="create_dest"/>
      <div class="form-row">
        <div class="form-col"><label class="form-label">Nama Destinasi</label><input type="text" name="nama_dest" required placeholder="contoh: Labuan Bajo" class="form-input"/></div>
        <div class="form-col"><label class="form-label">Emoji</label><input type="text" name="emoji" required placeholder="🌊" class="form-input"/></div>
      </div>
      <div class="form-row">
        <div class="form-col"><label class="form-label">Tema Warna</label><select name="tema" class="form-select"><option value="teal">🟢 Teal (Hijau)</option><option value="gold">🟡 Gold (Emas)</option><option value="blue">🔵 Blue (Biru)</option></select></div>
        <div class="form-col"><label class="form-label">Harga Tiket (Rp)</label><input type="number" name="harga" placeholder="500000" min="0" class="form-input"/></div>
      </div>
      <div class="form-group form-mb"><label class="form-label">Deskripsi Singkat</label><textarea name="deskripsi" rows="3" placeholder="Deskripsi singkat destinasi..." class="form-textarea"></textarea></div>
      <button type="submit" class="btn-submit blue">＋ Tambah Destinasi →</button>
    </form>
  </div>
</div>

<div class="modal-overlay" id="modalEditDest">
  <div class="modal-box">
    <button class="modal-close" onclick="tutupModal('modalEditDest')">✕</button>
    <div class="modal-icon">✏️</div>
    <div class="modal-title">Edit Destinasi</div>
    <div class="modal-sub">Ubah data destinasi wisata.</div>
    <form method="POST" action="?tab=dest">
      <input type="hidden" name="action" value="update_dest"/>
      <input type="hidden" name="id" id="eDestId"/>
      <div class="form-row">
        <div class="form-col"><label class="form-label">Nama Destinasi</label><input type="text" name="nama_dest" id="eDestNama" required class="form-input"/></div>
        <div class="form-col"><label class="form-label">Emoji</label><input type="text" name="emoji" id="eDestEmoji" required class="form-input"/></div>
      </div>
      <div class="form-row">
        <div class="form-col"><label class="form-label">Tema Warna</label><select name="tema" id="eDestTema" class="form-select"><option value="teal">🟢 Teal (Hijau)</option><option value="gold">🟡 Gold (Emas)</option><option value="blue">🔵 Blue (Biru)</option></select></div>
        <div class="form-col"><label class="form-label">Harga Tiket (Rp)</label><input type="number" name="harga" id="eDestHarga" min="0" class="form-input"/></div>
      </div>
      <div class="form-group form-mb"><label class="form-label">Deskripsi Singkat</label><textarea name="deskripsi" id="eDestDesk" rows="3" class="form-textarea"></textarea></div>
      <button type="submit" class="btn-submit blue">💾 Simpan Perubahan →</button>
    </form>
  </div>
</div>


<script>
function tutupModal(id) {
  document.getElementById(id).classList.remove('active');
  document.body.style.overflow = '';
}
function bukaModal(id) {
  document.getElementById(id).classList.add('active');
  document.body.style.overflow = 'hidden';
}

function bukaModalAkun(role) {
  const isAdmin = role === 'admin';
  document.getElementById('akunEmoji').textContent = isAdmin ? '⚙️' : '👤';
  document.getElementById('akunTitle').textContent = isAdmin ? 'Tambah Admin Baru' : 'Tambah User Baru';
  document.getElementById('akunRole').value        = role;
  document.getElementById('formAkun').action       = '?tab=' + role;
  document.getElementById('akunBtn').className     = 'btn-submit ' + (isAdmin ? 'gold' : 'teal');
  document.getElementById('groupTgl').style.display = isAdmin ? 'none' : 'flex';
  bukaModal('modalAkun');
}

function bukaModalEditAkun(data, role) {
  const isAdmin = role === 'admin';
  document.getElementById('eAkunId').value       = data.id;
  document.getElementById('eAkunRoleTarget').value = role;
  document.getElementById('eAkunNama').value     = data.nama;
  document.getElementById('eAkunEmail').value    = data.email;
  document.getElementById('eAkunUsername').value = data.username;
  document.getElementById('eGroupTgl').style.display = isAdmin ? 'none' : 'flex';
  if (!isAdmin) document.getElementById('eAkunTgl').value = data.tanggal_lahir || '';
  document.getElementById('eAkunBtn').className  = 'btn-submit ' + (isAdmin ? 'gold' : 'teal');
  bukaModal('modalEditAkun');
}

function bukaModalDest() { bukaModal('modalDest'); }
function bukaModalEditDest(data) {
  document.getElementById('eDestId').value    = data.id;
  document.getElementById('eDestNama').value  = data.nama;
  document.getElementById('eDestEmoji').value = data.emoji;
  document.getElementById('eDestTema').value  = data.tema;
  document.getElementById('eDestHarga').value = data.harga;
  document.getElementById('eDestDesk').value  = data.deskripsi || '';
  bukaModal('modalEditDest');
}
function bukaModalDest() { bukaModal('modalDest'); }

function bukaModalEditDest(data) {
  document.getElementById('eDestId').value    = data.id;
  document.getElementById('eDestNama').value  = data.nama;
  document.getElementById('eDestEmoji').value = data.emoji;
  document.getElementById('eDestTema').value  = data.tema;
  document.getElementById('eDestHarga').value = data.harga;
  document.getElementById('eDestDesk').value  = data.deskripsi || '';
  bukaModal('modalEditDest');
}

</script>
</body>
</html>