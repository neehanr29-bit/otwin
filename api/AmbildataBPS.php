<?php
// ══════════════════════════════════════════════════════════
//  AmbildataBPS.php – Data Pariwisata dari API BPS RI
//  Include file ini di OTWin.php dengan:
//  include 'AmbildataBPS.php';
// ══════════════════════════════════════════════════════════

define('BPS_KEY',    '8a298f03b114b6f2d212a5f3dac5b57e');
define('BPS_BASE',   'https://webapi.bps.go.id/v1/api/list');
define('BPS_DOMAIN', '0000');

// ── Kode variabel yang sudah diverifikasi relevan ──
// Sumber: decode dari URL tabel statistik bps.go.id
define('BPS_VAR_WISNUS_TUJUAN', '2201');  // Perjalanan Wisnus per Provinsi Tujuan
define('BPS_VAR_WISNUS_ASAL',   '1189');  // Perjalanan Wisnus per Provinsi Asal
define('BPS_VAR_WISNUS_TOTAL',  '2195');  // Jumlah Perjalanan Wisatawan Nasional

define('CACHE_DIR', __DIR__ . '/');
define('CACHE_TTL',  86400); // 1 hari

// ── Fetch pakai cURL (lebih handal dari file_get_contents) ──
function bps_fetch($var_id) {
    $url        = BPS_BASE . '?model=data&domain=' . BPS_DOMAIN . '&var=' . $var_id . '&key=' . BPS_KEY;
    $cache_file = CACHE_DIR . 'bps_cache_' . $var_id . '.json';

    // Pakai cache kalau masih fresh
    if (file_exists($cache_file) && (time() - filemtime($cache_file)) < CACHE_TTL) {
        $cached = json_decode(file_get_contents($cache_file), true);
        if ($cached) return $cached;
    }

    // Coba cURL dulu
    if (function_exists('curl_init')) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 15);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0');
        $response = curl_exec($ch);
        curl_close($ch);
    } else {
        // Fallback ke file_get_contents
        $ctx      = stream_context_create(['http' => ['timeout' => 15]]);
        $response = @file_get_contents($url, false, $ctx);
    }

    if (!$response) return null;

    $json = json_decode($response, true);
    if (!$json || ($json['data-availability'] ?? '') !== 'available') return null;

    // Simpan cache
    file_put_contents($cache_file, json_encode($json));

    return $json;
}

// ── Parse data BPS ke format array bersih ──
function bps_parse($json) {
    if (!$json) return [];

    // BPS menyimpan data dalam datacontent bersarang
    // Format: datacontent[kode_periode][kode_wilayah] = nilai
    $datacontent = $json['datacontent'] ?? [];
    $tahun_list  = $datacontent['tahun']       ?? ($json['tahun']       ?? []);
    $turvar_list = $datacontent['turvar']       ?? ($json['turvar']      ?? []);
    $data_raw    = $datacontent['datacontent']  ?? ($json['datacontent'] ?? []);

    if (empty($tahun_list) || empty($data_raw)) return [];

    $hasil = [];
    foreach ($tahun_list as $kode_tahun => $nama_tahun) {
        $row = ['tahun' => $nama_tahun, 'data' => []];
        foreach ($turvar_list as $kode_wil => $nama_wil) {
            $nilai = $data_raw[$kode_tahun][$kode_wil] ?? 0;
            $angka = (float) str_replace([',', ' '], ['', ''], $nilai);
            $row['data'][$nama_wil] = $angka;
        }
        $hasil[] = $row;
    }
    return $hasil;
}

// ── Ambil data total perjalanan wisnus (untuk chart & kartu) ──
function ambilDataBPS() {
    $json = bps_fetch(BPS_VAR_WISNUS_TOTAL);
    if (!$json) return dataDummy();

    $parsed = bps_parse($json);
    if (empty($parsed)) return dataDummy();

    // Flatten: ambil total (kolom pertama / jumlah semua)
    $hasil = [];
    foreach ($parsed as $row) {
        $total = array_sum($row['data']) ?: (reset($row['data']) ?: 0);
        $hasil[] = ['tahun' => $row['tahun'], 'jumlah' => (int) $total];
    }
    return $hasil ?: dataDummy();
}

// ── Ambil top 5 provinsi tujuan terbanyak (tahun terbaru) ──
function ambilTopProvinsiTujuan() {
    $json = bps_fetch(BPS_VAR_WISNUS_TUJUAN);
    if (!$json) return topDummy();

    $parsed = bps_parse($json);
    if (empty($parsed)) return topDummy();

    // Ambil tahun terbaru
    $terbaru = end($parsed);
    $data    = $terbaru['data'];

    // Sort descending, ambil top 5
    arsort($data);
    $top5 = array_slice($data, 0, 5, true);

    $hasil = [];
    foreach ($top5 as $provinsi => $jumlah) {
        $hasil[] = ['provinsi' => $provinsi, 'jumlah' => (int) $jumlah];
    }
    return $hasil ?: topDummy();
}

// ── DATA DUMMY (fallback jika API tidak tersedia) ──
function dataDummy() {
    return [
        ['tahun' => '2019', 'jumlah' => 722600000],
        ['tahun' => '2020', 'jumlah' => 383000000],
        ['tahun' => '2021', 'jumlah' => 352000000],
        ['tahun' => '2022', 'jumlah' => 703600000],
        ['tahun' => '2023', 'jumlah' => 733700000],
    ];
}

function topDummy() {
    return [
        ['provinsi' => 'Jawa Barat',   'jumlah' => 119000000],
        ['provinsi' => 'Jawa Timur',   'jumlah' => 98000000],
        ['provinsi' => 'Jawa Tengah',  'jumlah' => 95000000],
        ['provinsi' => 'DKI Jakarta',  'jumlah' => 72000000],
        ['provinsi' => 'Bali',         'jumlah' => 65000000],
    ];
}

// ── Helper format angka ──
function formatJumlah($angka) {
    if ($angka >= 1000000000) return number_format($angka / 1000000000, 1, ',', '.') . ' Miliar';
    if ($angka >= 1000000)    return number_format($angka / 1000000, 1, ',', '.')    . ' Juta';
    if ($angka >= 1000)       return number_format($angka / 1000, 1, ',', '.')       . ' Ribu';
    return number_format($angka, 0, ',', '.');
}
?>
