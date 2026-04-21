// ── NAVBAR SCROLL ──
window.addEventListener('scroll', () => {
  document.getElementById('navbar').classList.toggle('scrolled', window.scrollY > 50);
});
 
// ── REVEAL ON SCROLL ──
const observer = new IntersectionObserver(entries => {
  entries.forEach((e, i) => {
    if (e.isIntersecting) {
      setTimeout(() => e.target.classList.add('visible'), i * 80);
    }
  });
}, { threshold: 0.1 });
document.querySelectorAll('.reveal').forEach(el => observer.observe(el));
 
// ── TOAST ──
function showToast(msg) {
  const t = document.getElementById('toast');
  document.getElementById('toastMsg').textContent = msg;
  t.classList.add('show');
  setTimeout(() => t.classList.remove('show'), 3200);
}
 
// ── MODAL UTIL ──
const MODAL_IDS = {
  login:    'loginModal',
  register: 'registerModal',
  booking:  'bookingModal',
};
 
function openModal(type) {
  const id = MODAL_IDS[type];
  if (!id) return;
  document.getElementById(id).classList.add('active');
  document.body.style.overflow = 'hidden';
}
 
function closeModal(type) {
  const id = MODAL_IDS[type];
  if (!id) return;
  document.getElementById(id).classList.remove('active');
  document.body.style.overflow = '';
}
 
// Klik di luar modal = tutup
document.querySelectorAll('.modal-overlay').forEach(overlay => {
  overlay.addEventListener('click', e => {
    if (e.target === overlay) {
      overlay.classList.remove('active');
      document.body.style.overflow = '';
    }
  });
});
 
// Beralih antar modal (Login ↔ Register)
function switchModal(dari, ke) {
  closeModal(dari);
  setTimeout(() => openModal(ke), 280);
}
 
// Toggle visibilitas password
function togglePw(inputId, btn) {
  const input = document.getElementById(inputId);
  if (input.type === 'password') {
    input.type = 'text';
    btn.textContent = '⊗';
  } else {
    input.type = 'password';
    btn.textContent = '👁';
  }
}
 
 
// ── FITUR 1: REGISTER ──
function handleRegister() {
  const nama         = document.getElementById('regNama').value.trim();
  const tglLahir     = document.getElementById('regTanggalLahir').value;
  const email        = document.getElementById('regEmail').value.trim();
  const password     = document.getElementById('regPassword').value;
  const username     = document.getElementById('regUsername').value.trim();
  const passwordAkun = document.getElementById('regPasswordAkun').value;
 
  if (!nama || !tglLahir || !email || !password || !username || !passwordAkun) {
    showToast('⚠️ Semua field wajib diisi!');
    return;
  }
 
  closeModal('register');
  showToast('🎉 Akun berhasil dibuat!');
}
 
// ── FITUR 2: LOGIN ──
function handleLogin() {
  const username = document.getElementById('loginUsername').value.trim();
  const password = document.getElementById('loginPassword').value;
 
  if (!username || !password) {
    showToast('⚠️ Isi Nama Akun dan Password terlebih dahulu!');
    return;
  }
 
  closeModal('login');
  showToast('✅ Berhasil masuk!');
}
 
 
// ── FITUR 3: BOOKING TIKET ──1
// TUGAS 3: Cek login sebelum buka modal booking
function openBooking(dest, emoji, tema) {
 
  // Jika belum login, arahkan ke modal register
  if (!IS_LOGGED_IN) {
    showToast('⚠️ Silakan daftar atau masuk terlebih dahulu!');
    setTimeout(() => openModal('register'), 600);
    return;
  }
 
  // Isi info destinasi di header modal
  document.getElementById('bookingEmoji').textContent    = emoji;
  document.getElementById('bookingDestName').textContent = dest;
 
  // Set nilai hidden input destinasi agar terkirim ke PHP
  document.getElementById('bookDestinasiInput').value = dest;
 
  // Terapkan warna tema header
  const header = document.getElementById('bookingHeader');
  const temaGradient = {
    teal: 'linear-gradient(135deg,#1a6b6b,#0d4040)',
    gold: 'linear-gradient(135deg,#c8922a,#7a5510)',
    blue: 'linear-gradient(135deg,#1a4a8a,#0d2d6b)',
  };
  header.style.background = temaGradient[tema] || temaGradient.teal;
 
  // Set tanggal minimal = hari ini
  const today = new Date().toISOString().split('T')[0];
  document.getElementById('bookTanggal').min   = today;
  document.getElementById('bookTanggal').value = '';
  document.getElementById('bookNama').value    = '';
 
  // Reset pilihan ke default
  document.getElementById('bookJumlah').value = '1';
  document.getElementById('bookDurasi').value = '1';
 
  openModal('booking');
}
 
 
// ── FITUR 4: RATING BINTANG INTERAKTIF ──
const ratingData = {
  bali:   { score: 4.9, count: 2340 },
  komodo: { score: 4.8, count: 1875 },
  raja:   { score: 4.7, count: 980  },
};
 
// Hover preview bintang
document.querySelectorAll('.rating-stars-interactive').forEach(container => {
  const stars = container.querySelectorAll('.star');
  stars.forEach((star, i) => {
    star.addEventListener('mouseenter', () => {
      stars.forEach((s, j) => s.style.color = j <= i ? '#c8922a' : '#ddd');
    });
    star.addEventListener('mouseleave', () => {
      const dest = container.id.replace('rating-', '');
      renderBintang(dest);
    });
  });
});
 
function kasihRating(dest, nilai) {
  const data = ratingData[dest];
 
  // Hitung rata-rata baru
  const totalLama = data.score * data.count;
  data.count += 1;
  data.score = Math.round(((totalLama + nilai) / data.count) * 10) / 10;
 
  // Update tampilan
  document.getElementById('score-' + dest).textContent = data.score.toFixed(1);
  document.getElementById('count-' + dest).textContent = '· ' + data.count.toLocaleString('id-ID') + ' ulasan';
  renderBintang(dest);
 
  const label = nilai === 5 ? 'luar biasa! 🤩' : nilai >= 4 ? 'bagus! 😊' : nilai >= 3 ? 'cukup 😐' : 'kurang memuaskan 😕';
  showToast(`⭐ Terima kasih! Kamu memberi ${nilai} bintang — ${label}`);
}
 
function renderBintang(dest) {
  const stars = document.querySelectorAll('#rating-' + dest + ' .star');
  const score = ratingData[dest].score;
  stars.forEach((s, i) => {
    s.style.color = i < Math.round(score) ? '#c8922a' : '#ddd';
  });
}
 
// Inisialisasi warna bintang saat halaman load
document.addEventListener('DOMContentLoaded', () => {
  ['bali', 'komodo', 'raja'].forEach(renderBintang);
});