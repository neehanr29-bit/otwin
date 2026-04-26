<?php session_start(); ?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>OTWkan – Sistem Manajemen Event Pariwisata</title>
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,700;0,900;1,700&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet"/>
  <script src="https://cdn.tailwindcss.com"></script>
  <script>
    tailwind.config = {
      theme: {
        extend: {
          colors: {
            teal:  { DEFAULT: '#1a6b6b', light: '#2a9090' },
            gold:  { DEFAULT: '#c8922a', light: '#e8b84b' },
            sand:  '#f5ede0',
            cream: '#fdf8f2',
            dark:  '#1a2530',
            muted: '#7a8c80',
          },
          fontFamily: {
            playfair: ['"Playfair Display"', 'serif'],
            dm:       ['"DM Sans"', 'sans-serif'],
          },
        }
      }
    }
  </script>
  <link rel="stylesheet" href="style.css"/>
</head>
<body class="bg-cream font-dm text-dark overflow-x-hidden">
 
<nav id="navbar" class="fixed top-0 w-full flex items-center justify-between px-[6%] py-5 z-50 bg-cream/90 backdrop-blur-md border-b border-gold/15 transition-all duration-300">
  <div class="font-playfair text-3xl font-black text-teal tracking-tight">OT<span class="text-gold">Win</span></div>
  <div class="flex items-center gap-3" id="navActions">
    <?php if(isset($_SESSION['username'])): ?>
        <span class="font-dm text-teal font-bold mr-2">Halo, <?= htmlspecialchars($_SESSION['username']); ?>!</span>
        <?php if(isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
            <a href="api/admin/Dashboard.php" class="px-5 py-2 rounded-full bg-gold text-white text-sm font-semibold hover:bg-gold-light transition-all duration-300 shadow-sm">⚙️ Dashboard Admin</a>
        <?php endif; ?>
        <a href="api/ProsesLogout.php" class="px-5 py-2 rounded-full border-[1.5px] border-red-500 text-red-500 text-sm font-medium hover:bg-red-500 hover:text-white transition-all duration-300">Logout</a>
    <?php else: ?>
        <button onclick="openModal('login')" class="px-5 py-2 rounded-full border-[1.5px] border-teal text-teal text-sm font-medium hover:bg-teal hover:text-white transition-all duration-300">Masuk</button>
        <button onclick="openModal('register')" class="px-5 py-2 rounded-full bg-teal text-white text-sm font-semibold hover:bg-teal-light transition-all duration-300 shadow-sm">Daftar</button>
    <?php endif; ?>
  </div>
</nav>
 
<section id="hero" class="min-h-screen flex items-center px-[6%] pt-28 pb-20 relative overflow-hidden">
  <div class="hero-bg absolute inset-0"></div>
  <div class="hero-dots absolute inset-0"></div>
  <div class="relative z-10 hero-content">
    <div class="inline-flex items-center gap-2 bg-gold/10 border border-gold/30 text-gold px-4 py-2 rounded-full text-xs font-bold uppercase tracking-widest mb-7">
      <span class="dot w-2 h-2 bg-gold rounded-full inline-block"></span>
      Platform Wisata #1 Indonesia
    </div>
    <h1 class="font-playfair text-5xl md:text-7xl leading-tight text-dark mb-6">
      Anda <em class="italic text-teal">Lelah?</em><br/>Yuk Liburan Sama Kita
    </h1>
  </div>
</section>
 
<div class="px-[6%] pb-20 relative z-10">
  <div class="max-w-4xl mx-auto bg-white rounded-3xl shadow-xl p-2 flex flex-wrap gap-2 items-center">
    <div class="flex-1 min-w-[150px] px-4 py-3 border-r border-gray-100">
      <label class="block text-[0.68rem] font-bold uppercase tracking-widest text-muted mb-1">Destinasi</label>
      <input type="text" placeholder="Mau ke mana?" class="w-full border-none bg-transparent text-sm text-dark outline-none font-dm"/>
    </div>
    <div class="flex-1 min-w-[150px] px-4 py-3 border-r border-gray-100">
      <label class="block text-[0.68rem] font-bold uppercase tracking-widest text-muted mb-1">Tanggal Berangkat</label>
      <input type="date" class="w-full border-none bg-transparent text-sm text-dark outline-none font-dm"/>
    </div>
    <div class="flex-1 min-w-[150px] px-4 py-3 border-r border-gray-100">
      <label class="block text-[0.68rem] font-bold uppercase tracking-widest text-muted mb-1">Jumlah Orang</label>
      <select class="w-full border-none bg-transparent text-sm text-dark outline-none font-dm cursor-pointer">
        <option>1 Orang</option><option>2 Orang</option><option>3-5 Orang</option><option>6-10 Orang</option><option>10+ Orang</option>
      </select>
    </div>
    <div class="flex-1 min-w-[150px] px-4 py-3">
      <label class="block text-[0.68rem] font-bold uppercase tracking-widest text-muted mb-1">Kategori</label>
      <select class="w-full border-none bg-transparent text-sm text-dark outline-none font-dm cursor-pointer">
        <option>Semua</option><option>Pantai</option><option>Gunung</option><option>Budaya</option><option>Kuliner</option>
      </select>
    </div>
    <button onclick="showToast('Mencari destinasi...')" class="w-12 h-12 bg-gold text-white rounded-xl text-xl flex-shrink-0 hover:bg-gold-light hover:scale-105 transition-all duration-200">🔍</button>
  </div>
</div>
 
<section id="tickets" class="px-[6%] py-20 bg-cream">
  <div class="flex justify-between items-end mb-12 flex-wrap gap-5 reveal">
    <div>
      <div class="text-[0.72rem] font-bold uppercase tracking-[2px] text-gold mb-3">Tiket Wisata</div>
      <h2 class="font-playfair text-4xl text-dark leading-snug">Pesan <em class="italic text-teal">Tiket</em> dengan Mudah &amp; Cepat</h2>
    </div>
    <button onclick="showToast('Memuat semua tiket...')" class="px-7 py-3 rounded-full border-[1.5px] border-teal text-teal text-sm font-medium hover:bg-teal hover:text-white transition-all duration-300">Lihat Semua</button>
  </div>
 
  <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
    <div class="reveal bg-white rounded-2xl overflow-hidden border-[1.5px] border-transparent hover:border-teal hover:-translate-y-1.5 hover:shadow-xl transition-all duration-300 cursor-pointer" onclick="openBooking('Bali','🌴','teal')">
      <div class="p-6 relative overflow-hidden" style="background:linear-gradient(135deg,#1a6b6b,#0d4040)">
        <div class="absolute w-28 h-28 rounded-full -top-10 -right-8" style="background:rgba(255,255,255,0.06)"></div>
        <div class="font-playfair text-2xl font-bold text-white">Bali</div>
        <span class="text-4xl mt-3 block">🌴</span>
      </div>
      <div class="px-5 py-4 flex items-center justify-between border-t border-gray-100" onclick="event.stopPropagation()">
        <div class="flex gap-1 rating-stars-interactive" id="rating-bali">
          <span class="star cursor-pointer transition-all duration-150" onclick="kasihRating('bali',1)">★</span>
          <span class="star cursor-pointer transition-all duration-150" onclick="kasihRating('bali',2)">★</span>
          <span class="star cursor-pointer transition-all duration-150" onclick="kasihRating('bali',3)">★</span>
          <span class="star cursor-pointer transition-all duration-150" onclick="kasihRating('bali',4)">★</span>
          <span class="star cursor-pointer transition-all duration-150" onclick="kasihRating('bali',5)">★</span>
        </div>
        <div class="flex items-center gap-1">
          <span id="score-bali" class="font-bold text-dark text-sm">4.9</span>
          <span id="count-bali" class="text-muted text-xs">· 2.340 ulasan</span>
        </div>
      </div>
    </div>
 
    <div class="reveal bg-white rounded-2xl overflow-hidden border-[1.5px] border-transparent hover:border-yellow-600 hover:-translate-y-1.5 hover:shadow-xl transition-all duration-300 cursor-pointer" onclick="openBooking('Taman Nasional Komodo','🦎','gold')">
      <div class="p-6 relative overflow-hidden" style="background:linear-gradient(135deg,#c8922a,#7a5510)">
        <div class="absolute w-28 h-28 rounded-full -top-10 -right-8" style="background:rgba(255,255,255,0.06)"></div>
        <div class="font-playfair text-2xl font-bold text-white">Taman Nasional Komodo</div>
        <span class="text-4xl mt-3 block">🦎</span>
      </div>
      <div class="px-5 py-4 flex items-center justify-between border-t border-gray-100" onclick="event.stopPropagation()">
        <div class="flex gap-1 rating-stars-interactive" id="rating-komodo">
          <span class="star cursor-pointer transition-all duration-150" onclick="kasihRating('komodo',1)">★</span>
          <span class="star cursor-pointer transition-all duration-150" onclick="kasihRating('komodo',2)">★</span>
          <span class="star cursor-pointer transition-all duration-150" onclick="kasihRating('komodo',3)">★</span>
          <span class="star cursor-pointer transition-all duration-150" onclick="kasihRating('komodo',4)">★</span>
          <span class="star cursor-pointer transition-all duration-150" onclick="kasihRating('komodo',5)">★</span>
        </div>
        <div class="flex items-center gap-1">
          <span id="score-komodo" class="font-bold text-dark text-sm">4.8</span>
          <span id="count-komodo" class="text-muted text-xs">· 1.875 ulasan</span>
        </div>
      </div>
    </div>
 
    <div class="reveal bg-white rounded-2xl overflow-hidden border-[1.5px] border-transparent hover:border-blue-700 hover:-translate-y-1.5 hover:shadow-xl transition-all duration-300 cursor-pointer" onclick="openBooking('Raja Ampat','🤿','blue')">
      <div class="p-6 relative overflow-hidden" style="background:linear-gradient(135deg,#1a4a8a,#0d2d6b)">
        <div class="absolute w-28 h-28 rounded-full -top-10 -right-8" style="background:rgba(255,255,255,0.06)"></div>
        <div class="font-playfair text-2xl font-bold text-white">Raja Ampat</div>
        <span class="text-4xl mt-3 block">🤿</span>
      </div>
      <div class="px-5 py-4 flex items-center justify-between border-t border-gray-100" onclick="event.stopPropagation()">
        <div class="flex gap-1 rating-stars-interactive" id="rating-raja">
          <span class="star cursor-pointer transition-all duration-150" onclick="kasihRating('raja',1)">★</span>
          <span class="star cursor-pointer transition-all duration-150" onclick="kasihRating('raja',2)">★</span>
          <span class="star cursor-pointer transition-all duration-150" onclick="kasihRating('raja',3)">★</span>
          <span class="star cursor-pointer transition-all duration-150" onclick="kasihRating('raja',4)">★</span>
          <span class="star cursor-pointer transition-all duration-150" onclick="kasihRating('raja',5)">★</span>
        </div>
        <div class="flex items-center gap-1">
          <span id="score-raja" class="font-bold text-dark text-sm">4.7</span>
          <span id="count-raja" class="text-muted text-xs">· 980 ulasan</span>
        </div>
      </div>
    </div>
  </div>
</section>
 
<!-- ══ MODAL LOGIN ══ -->
<div class="modal-overlay fixed inset-0 bg-black/55 z-[1000] flex items-center justify-center p-5 opacity-0 pointer-events-none transition-opacity duration-300" id="loginModal">
  <div class="modal bg-white rounded-3xl p-10 w-full max-w-sm relative translate-y-8 transition-transform duration-300 max-h-[90vh] overflow-y-auto">
    <button onclick="closeModal('login')" class="absolute top-4 right-4 w-9 h-9 rounded-full bg-black/[0.07] border-none text-base cursor-pointer hover:bg-black/[0.14] transition-colors">✕</button>
 
      <form action="api/ProsesLogin.php" method="POST">
      <div class="text-5xl text-center mb-4">🔑</div>
      <h3 class="font-playfair text-2xl text-dark text-center mb-2">Masuk ke Akun</h3>
      <p class="text-sm text-muted text-center mb-7">Selamat datang kembali di OTWin!</p>
      
      <div class="mb-4">
        <label class="block text-[0.72rem] font-bold uppercase tracking-widest text-muted mb-2">Nama Akun</label>
        <input type="text" name="username" id="loginUsername" placeholder="Masukkan nama akun otwin" required class="w-full border-[1.5px] border-gray-200 bg-sand rounded-xl px-4 py-3 text-sm font-dm text-dark outline-none focus:border-teal focus:bg-white transition-all"/>
      </div>
      
      <div class="mb-6">
        <label class="block text-[0.72rem] font-bold uppercase tracking-widest text-muted mb-2">Password</label>
        <div class="relative flex items-center">
          <input type="password" name="password" id="loginPassword" placeholder="Masukkan password" required class="w-full border-[1.5px] border-gray-200 bg-sand rounded-xl px-4 py-3 pr-12 text-sm font-dm text-dark outline-none focus:border-teal focus:bg-white transition-all"/>
          <button type="button" onclick="togglePw('loginPassword',this)" class="absolute right-3 border-none bg-transparent text-base cursor-pointer opacity-60 hover:opacity-100 transition-opacity">👁</button>
        </div>
      </div>
      
      <button type="submit" name="login" class="w-full bg-teal text-white py-3 rounded-full font-semibold text-sm hover:bg-teal-light transition-all duration-300">Masuk →</button>
    </form>
 
    <p class="text-center text-xs text-muted mt-4">Belum punya akun? <span onclick="switchModal('login','register')" class="text-teal font-semibold cursor-pointer underline">Daftar sekarang</span></p>
  </div>
</div>
 
<!-- ══ MODAL REGISTER ══ -->
<div class="modal-overlay fixed inset-0 bg-black/55 z-[1000] flex items-center justify-center p-5 opacity-0 pointer-events-none transition-opacity duration-300" id="registerModal">
  <div class="modal bg-white rounded-3xl p-10 w-full max-w-xl relative translate-y-8 transition-transform duration-300 max-h-[90vh] overflow-y-auto">
    <button onclick="closeModal('register')" class="absolute top-4 right-4 w-9 h-9 rounded-full bg-black/[0.07] border-none text-base cursor-pointer hover:bg-black/[0.14] transition-colors">✕</button>
    
      <form action="api/ProsesRegistrasti.php" method="POST">
      <div class="text-5xl text-center mb-4">📝</div>
      <h3 class="font-playfair text-2xl text-dark text-center mb-2">Buat Akun Baru</h3>
      <p class="text-sm text-muted text-center mb-7">Bergabunglah dengan OTWin!</p>
      
      <div class="grid grid-cols-2 gap-3 mb-4">
        <div>
          <label class="block text-[0.72rem] font-bold uppercase tracking-widest text-muted mb-2">Nama Lengkap</label>
          <input type="text" name="nama" id="regNama" placeholder="Nama lengkap Anda" required class="w-full border-[1.5px] border-gray-200 bg-sand rounded-xl px-4 py-3 text-sm font-dm outline-none focus:border-teal focus:bg-white transition-all"/>
        </div>
        <div>
          <label class="block text-[0.72rem] font-bold uppercase tracking-widest text-muted mb-2">Tanggal Lahir</label>
          <input type="date" name="tanggal_lahir" id="regTanggalLahir" required class="w-full border-[1.5px] border-gray-200 bg-sand rounded-xl px-4 py-3 text-sm font-dm outline-none focus:border-teal focus:bg-white transition-all"/>
        </div>
      </div>
      <div class="mb-4">
        <label class="block text-[0.72rem] font-bold uppercase tracking-widest text-muted mb-2">Email</label>
        <input type="email" name="email" id="regEmail" placeholder="otwin@gmail.com" required class="w-full border-[1.5px] border-gray-200 bg-sand rounded-xl px-4 py-3 text-sm font-dm outline-none focus:border-teal focus:bg-white transition-all"/>
      </div>
      <div class="mb-4">
        <label class="block text-[0.72rem] font-bold uppercase tracking-widest text-muted mb-2">Password</label>
        <div class="relative flex items-center">
          <input type="password" name="password" id="regPassword" placeholder="Buat password otwin" required class="w-full border-[1.5px] border-gray-200 bg-sand rounded-xl px-4 py-3 pr-12 text-sm font-dm outline-none focus:border-teal focus:bg-white transition-all"/>
          <button type="button" onclick="togglePw('regPassword',this)" class="absolute right-3 border-none bg-transparent text-base cursor-pointer opacity-60 hover:opacity-100 transition-opacity">👁</button>
        </div>
      </div>
      <div class="grid grid-cols-2 gap-3 mb-6">
        <div>
          <label class="block text-[0.72rem] font-bold uppercase tracking-widest text-muted mb-2">Nama Akun</label>
          <input type="text" name="username" id="regUsername" placeholder="Nama akun otwin" required class="w-full border-[1.5px] border-gray-200 bg-sand rounded-xl px-4 py-3 text-sm font-dm outline-none focus:border-teal focus:bg-white transition-all"/>
        </div>
        <div>
          <label class="block text-[0.72rem] font-bold uppercase tracking-widest text-muted mb-2">Password Akun (Ulangi)</label>
          <div class="relative flex items-center">
            <input type="password" id="regPasswordAkun" placeholder="Ulangi password  otwin" required class="w-full border-[1.5px] border-gray-200 bg-sand rounded-xl px-4 py-3 pr-12 text-sm font-dm outline-none focus:border-teal focus:bg-white transition-all"/>
            <button type="button" onclick="togglePw('regPasswordAkun',this)" class="absolute right-3 border-none bg-transparent text-base cursor-pointer opacity-60 hover:opacity-100 transition-opacity">👁</button>
          </div>
        </div>
      </div>
      
      <button type="submit" name="register" class="w-full bg-teal text-white py-3 rounded-full font-semibold text-sm hover:bg-teal-light transition-all duration-300">Daftar Sekarang →</button>
    </form>
 
    <p class="text-center text-xs text-muted mt-4">Sudah punya akun? <span onclick="switchModal('register','login')" class="text-teal font-semibold cursor-pointer underline">Masuk di sini</span></p>
  </div>
</div>
 
<!-- ══ MODAL BOOKING ══ -->
<div class="modal-overlay fixed inset-0 bg-black/55 z-[1000] flex items-center justify-center p-5 opacity-0 pointer-events-none transition-opacity duration-300" id="bookingModal">
  <div class="modal bg-white rounded-3xl p-10 w-full max-w-lg relative translate-y-8 transition-transform duration-300 max-h-[90vh] overflow-y-auto">
    <button onclick="closeModal('booking')" class="absolute top-4 right-4 w-9 h-9 rounded-full bg-black/[0.07] border-none text-base cursor-pointer hover:bg-black/[0.14] transition-colors z-10">✕</button>
 
    <form action="api/ProsesBooking.php" method="POST">
      <input type="hidden" name="booking" value="1"/>
      <input type="hidden" name="destinasi" id="bookDestinasiInput"/>
 
      <div id="bookingHeader" class="flex items-center gap-4 p-5 rounded-2xl mb-6" style="background:linear-gradient(135deg,#1a6b6b,#0d4040)">
        <div id="bookingEmoji" class="text-5xl flex-shrink-0">🌴</div>
        <div>
          <div class="text-[0.68rem] font-bold uppercase tracking-widest text-white/70 mb-1">Booking Tiket Wisata</div>
          <div id="bookingDestName" class="font-playfair text-2xl font-bold text-white">Bali</div>
        </div>
      </div>
 
      <div class="grid grid-cols-2 gap-3 mb-4">
        <div>
          <label class="block text-[0.72rem] font-bold uppercase tracking-widest text-muted mb-2">Nama Lengkap</label>
          <input type="text" name="nama_pemesan" id="bookNama" placeholder="Nama sesuai KTP" required
            class="w-full border-[1.5px] border-gray-200 bg-sand rounded-xl px-4 py-3 text-sm font-dm outline-none focus:border-teal focus:bg-white transition-all"/>
        </div>
        <div>
          <label class="block text-[0.72rem] font-bold uppercase tracking-widest text-muted mb-2">Tanggal Pemberangkatan</label>
          <input type="date" name="tanggal" id="bookTanggal" required
            class="w-full border-[1.5px] border-gray-200 bg-sand rounded-xl px-4 py-3 text-sm font-dm outline-none focus:border-teal focus:bg-white transition-all"/>
        </div>
      </div>
      <div class="grid grid-cols-2 gap-3 mb-6">
        <div>
          <label class="block text-[0.72rem] font-bold uppercase tracking-widest text-muted mb-2">Jumlah Orang</label>
          <select name="jumlah" id="bookJumlah" class="w-full border-[1.5px] border-gray-200 bg-sand rounded-xl px-4 py-3 text-sm font-dm outline-none focus:border-teal focus:bg-white transition-all cursor-pointer">
            <option value="1">1 Orang</option><option value="2">2 Orang</option><option value="3">3 Orang</option>
            <option value="4">4 Orang</option><option value="5">5 Orang</option><option value="6">6 Orang</option>
            <option value="7">7 Orang</option><option value="8">8 Orang</option><option value="9">9 Orang</option>
            <option value="10">10 Orang</option>
          </select>
        </div>
        <div>
          <label class="block text-[0.72rem] font-bold uppercase tracking-widest text-muted mb-2">Lama Berlibur</label>
          <select name="durasi" id="bookDurasi" class="w-full border-[1.5px] border-gray-200 bg-sand rounded-xl px-4 py-3 text-sm font-dm outline-none focus:border-teal focus:bg-white transition-all cursor-pointer">
            <option value="1">1 Hari</option><option value="3">3 Hari</option><option value="5">5 Hari (Maks)</option>
          </select>
        </div>
      </div>
      <button type="submit" class="w-full bg-teal text-white py-3 rounded-full font-semibold text-sm hover:bg-teal-light transition-all duration-300">Konfirmasi Booking →</button>
    </form>
  </div>
</div>
 
<!-- ══ SECTION: STATISTIK WISATAWAN NUSANTARA (DATA BPS) ══ -->
<section class="px-[6%] py-20 bg-white">
  <div class="max-w-5xl mx-auto">
 
    <div class="reveal mb-10">
      <div class="text-[0.72rem] font-bold uppercase tracking-[2px] text-gold mb-3">Sumber: Badan Pusat Statistik (BPS) RI</div>
      <h2 class="font-playfair text-4xl text-dark leading-snug mb-2">
        Statistik <em class="italic text-teal">Wisatawan Nusantara</em> Indonesia
      </h2>
      <p class="text-muted text-sm">Data resmi perjalanan wisatawan domestik · diambil langsung dari API BPS · <span id="bps-status" class="text-gold">Memuat data...</span></p>
    </div>
 
    <!-- 3 Kartu Highlight -->
    <div class="reveal grid grid-cols-1 md:grid-cols-3 gap-5 mb-10">
      <div class="bg-teal rounded-2xl p-7 text-white">
        <div class="text-xs font-bold uppercase tracking-widest opacity-70 mb-2">Total Perjalanan <span id="bps-tahun-terbaru">—</span></div>
        <div class="font-playfair text-4xl font-bold mb-1" id="bps-total">—</div>
        <div class="text-xs opacity-70">Perjalanan Wisatawan Nusantara</div>
      </div>
      <div class="bg-sand rounded-2xl p-7 border border-gold/20">
        <div class="text-xs font-bold uppercase tracking-widest text-muted mb-2">Pertumbuhan vs Tahun Lalu</div>
        <div class="font-playfair text-4xl font-bold mb-1 text-teal" id="bps-growth">—</div>
        <div class="text-xs text-muted" id="bps-growth-label">—</div>
      </div>
      <div class="bg-sand rounded-2xl p-7 border border-gold/20">
        <div class="text-xs font-bold uppercase tracking-widest text-muted mb-2">Rentang Data Tersedia</div>
        <div class="font-playfair text-4xl font-bold text-gold mb-1" id="bps-rentang-tahun">—</div>
        <div class="text-xs text-muted" id="bps-rentang-label">Tahun</div>
      </div>
    </div>
 
    <div class="reveal grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
 
      <!-- Tabel Tren Tahunan -->
      <div class="bg-white rounded-3xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
          <h3 class="font-playfair text-lg text-dark">Tren Perjalanan Tahunan</h3>
          <span class="text-xs text-muted bg-sand px-3 py-1 rounded-full border border-gold/20">Wisnus</span>
        </div>
        <table class="w-full text-sm">
          <thead>
            <tr class="border-b border-gray-100 bg-cream">
              <th class="text-left px-5 py-3 text-[0.65rem] uppercase tracking-widest font-bold text-muted">Tahun</th>
              <th class="text-left px-5 py-3 text-[0.65rem] uppercase tracking-widest font-bold text-muted">Jumlah</th>
              <th class="text-left px-5 py-3 text-[0.65rem] uppercase tracking-widest font-bold text-muted">Tren</th>
              <th class="text-left px-5 py-3 text-[0.65rem] uppercase tracking-widest font-bold text-muted">Growth</th>
            </tr>
          </thead>
          <tbody id="bps-tabel-body">
            <tr><td colspan="4" class="px-5 py-8 text-center text-muted text-xs">Memuat data dari BPS...</td></tr>
          </tbody>
        </table>
      </div>
 
      <!-- Top 5 Provinsi Tujuan -->
      <div class="bg-white rounded-3xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
          <h3 class="font-playfair text-lg text-dark">Top 5 Provinsi Tujuan</h3>
          <span class="text-xs text-muted bg-sand px-3 py-1 rounded-full border border-gold/20">Tahun Terbaru</span>
        </div>
        <div class="p-6 flex flex-col gap-4" id="bps-provinsi-list">
          <div class="text-center text-muted text-xs py-4">Memuat data dari BPS...</div>
        </div>
        <div class="px-6 pb-4 text-xs text-muted">Jumlah perjalanan wisatawan nusantara menurut provinsi tujuan</div>
      </div>
 
    </div>
 
    <div class="text-xs text-muted text-center py-2">
      📊 Data langsung dari <strong>API BPS RI</strong> · webapi.bps.go.id · Var. 2195 & 2201 · Domain 0000 (Nasional)
    </div>
 
  </div>
</section>
<!-- ══ END SECTION BPS ══ -->
 
<script>
// ── Helper format angka ──
function fmtJumlah(n) {
  if (n >= 1e9) return (n/1e9).toFixed(1).replace('.',',') + ' Miliar';
  if (n >= 1e6) return (n/1e6).toFixed(1).replace('.',',') + ' Juta';
  if (n >= 1e3) return (n/1e3).toFixed(1).replace('.',',') + ' Ribu';
  return n.toLocaleString('id-ID');
}
 
// ── Ambil data total BPS ──
async function muatDataBPS() {
  try {
    const res  = await fetch('/api/databps.php?type=total');
    const json = await res.json();
 
    if (json.status !== 'ok' || !json.data.length) {
      document.getElementById('bps-status').textContent = 'Gagal memuat data BPS.';
      document.getElementById('bps-status').className   = 'text-red-500';
      return;
    }
 
    document.getElementById('bps-status').textContent = '✅ Data berhasil dimuat dari API BPS';
    document.getElementById('bps-status').className   = 'text-green-600';
 
    const data    = json.data;
    const terbaru = data[data.length - 1];
    const sebelum = data[data.length - 2];
 
    // Update kartu
    document.getElementById('bps-tahun-terbaru').textContent = terbaru.tahun;
    document.getElementById('bps-total').textContent         = fmtJumlah(terbaru.jumlah);
    document.getElementById('bps-rentang-tahun').textContent = data.length + ' Tahun';
    document.getElementById('bps-rentang-label').textContent = data[0].tahun + ' – ' + terbaru.tahun;
 
    if (sebelum && sebelum.jumlah > 0) {
      const growth = ((terbaru.jumlah - sebelum.jumlah) / sebelum.jumlah * 100).toFixed(1);
      const el     = document.getElementById('bps-growth');
      el.textContent  = (growth >= 0 ? '+' : '') + growth + '%';
      el.className    = 'font-playfair text-4xl font-bold mb-1 ' + (growth >= 0 ? 'text-teal' : 'text-red-500');
      document.getElementById('bps-growth-label').textContent =
        'Dibanding ' + sebelum.tahun + ' (' + fmtJumlah(sebelum.jumlah) + ')';
    }
 
    // Isi tabel
    const maxJ  = Math.max(...data.map(d => d.jumlah));
    let tbody   = '';
    data.forEach((row, i) => {
      const prev   = i > 0 ? data[i-1].jumlah : null;
      const growth = prev ? ((row.jumlah - prev) / prev * 100).toFixed(1) : null;
      const pct    = Math.round(row.jumlah / maxJ * 100);
      const badge  = growth !== null
        ? `<span class="text-xs font-bold px-2 py-0.5 rounded-full ${growth >= 0 ? 'bg-green-50 text-green-600' : 'bg-red-50 text-red-500'}">${growth >= 0 ? '▲ +' : '▼ '}${growth}%</span>`
        : '<span class="text-xs text-muted">–</span>';
      tbody += `<tr class="border-b border-gray-50 hover:bg-cream/60 transition-colors">
        <td class="px-5 py-3 font-bold text-dark">${row.tahun}</td>
        <td class="px-5 py-3 font-semibold text-teal text-xs">${fmtJumlah(row.jumlah)}</td>
        <td class="px-5 py-3 w-20">
          <div class="h-2 bg-gray-100 rounded-full overflow-hidden">
            <div class="h-full bg-teal rounded-full" style="width:${pct}%"></div>
          </div>
        </td>
        <td class="px-5 py-3">${badge}</td>
      </tr>`;
    });
    document.getElementById('bps-tabel-body').innerHTML = tbody;
 
  } catch(e) {
    document.getElementById('bps-status').textContent = 'Error: ' + e.message;
    document.getElementById('bps-status').className   = 'text-red-500';
  }
}
 
// ── Ambil data provinsi BPS ──
async function muatProvinsi() {
  try {
    const res  = await fetch('/api/databps.php?type=provinsi');
    const json = await res.json();
    if (json.status !== 'ok' || !json.data.length) return;
 
    const max    = json.data[0].jumlah;
    const colors = ['#1a6b6b','#2a9090','#3aadad','#c8922a','#e8b84b'];
    let html     = '';
    json.data.forEach((p, i) => {
      const bar = Math.round(p.jumlah / max * 100);
      html += `<div>
        <div class="flex items-center justify-between mb-1">
          <div class="flex items-center gap-2">
            <span class="text-xs font-bold text-muted w-4">${i+1}</span>
            <span class="text-sm font-semibold text-dark">${p.provinsi}</span>
          </div>
          <span class="text-xs font-bold text-teal">${fmtJumlah(p.jumlah)}</span>
        </div>
        <div class="h-2.5 bg-gray-100 rounded-full overflow-hidden">
          <div class="h-full rounded-full transition-all" style="width:${bar}%;background:${colors[i]||'#1a6b6b'}"></div>
        </div>
      </div>`;
    });
    document.getElementById('bps-provinsi-list').innerHTML = html;
  } catch(e) { /* silent */ }
}
 
// Jalankan saat halaman load
document.addEventListener('DOMContentLoaded', () => {
  muatDataBPS();
  muatProvinsi();
});
</script>
 
 
<div id="toast" class="toast fixed bottom-8 right-8 bg-teal text-white px-5 py-3.5 rounded-2xl text-sm font-medium z-[9999] flex items-center gap-2.5 shadow-xl max-w-xs translate-y-20 opacity-0 transition-all duration-300">
  <span id="toastMsg">Berhasil!</span>
</div>
 
<!-- ── Kirim status login dari PHP ke JavaScript ── -->
<script>
  const IS_LOGGED_IN = <?= isset($_SESSION['username']) ? 'true' : 'false' ?> || false;
</script>
<script src="script.js"></script>
</body>
</html>