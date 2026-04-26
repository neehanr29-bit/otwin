<?php
// ══════════════════════════════════════════════════════════
//  api/data-bps.php
//  Endpoint khusus ambil data BPS, dipanggil via JS fetch()
//  URL: /api/data-bps.php?type=total   → data tren tahunan
//       /api/data-bps.php?type=provinsi → top 5 provinsi tujuan
// ══════════════════════════════════════════════════════════

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

define('BPS_KEY',    '8a298f03b114b6f2d212a5f3dac5b57e');
define('BPS_BASE',   'https://webapi.bps.go.id/v1/api/list');

// Var ID BPS yang sudah diverifikasi
// Sumber: webapi.bps.go.id — Pariwisata, domain nasional (0000)
define('VAR_TOTAL',    '2195'); // Jumlah Perjalanan Wisatawan Nusantara
define('VAR_PROVINSI', '2201'); // Wisnus per Provinsi Tujuan

function fetch_bps($var_id) {
    $url = BPS_BASE . '?model=data&domain=0000&var=' . $var_id . '&key=' . BPS_KEY;

    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL            => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 15,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_USERAGENT      => 'Mozilla/5.0 OTWin/1.0',
        CURLOPT_HTTPHEADER     => ['Accept: application/json'],
    ]);
    $response = curl_exec($ch);
    $error    = curl_error($ch);
    curl_close($ch);

    if ($error || !$response) return null;

    $json = json_decode($response, true);
    if (!$json) return null;

    return $json;
}

function parse_bps($json) {
    if (!$json || ($json['data-availability'] ?? 'not-available') !== 'available') return [];

    // Coba berbagai kemungkinan struktur respons BPS
    $datacontent = $json['datacontent'] ?? $json['data'] ?? [];

    // Struktur 1: datacontent.tahun + datacontent.datacontent
    $tahun_list = $datacontent['tahun']      ?? $json['tahun']      ?? [];
    $turvar     = $datacontent['turvar']     ?? $json['turvar']     ?? [];
    $data_raw   = $datacontent['datacontent']?? $json['datacontent']?? [];

    if (empty($tahun_list) || empty($data_raw)) return [];

    $hasil = [];
    foreach ($tahun_list as $kode_t => $nama_t) {
        $row_data = [];
        foreach ($turvar as $kode_w => $nama_w) {
            $nilai = $data_raw[$kode_t][$kode_w] ?? 0;
            $angka = (float) str_replace([',', ' ', '.'], ['', '', ''], $nilai);
            $row_data[$nama_w] = $angka;
        }
        $hasil[] = ['tahun' => $nama_t, 'data' => $row_data];
    }
    return $hasil;
}

// ── HANDLER BERDASARKAN TYPE ──
$type = $_GET['type'] ?? 'total';

if ($type === 'total') {
    $json   = fetch_bps(VAR_TOTAL);
    $parsed = parse_bps($json);

    if (empty($parsed)) {
        echo json_encode([
            'status'  => 'error',
            'message' => 'Gagal mengambil data dari API BPS. Respons: ' . json_encode($json),
            'data'    => []
        ]);
        exit;
    }

    $hasil = [];
    foreach ($parsed as $row) {
        $total = !empty($row['data']) ? array_sum($row['data']) : 0;
        $hasil[] = ['tahun' => $row['tahun'], 'jumlah' => (int) $total];
    }

    echo json_encode([
        'status'   => 'ok',
        'sumber'   => 'API BPS RI — webapi.bps.go.id',
        'var_id'   => VAR_TOTAL,
        'domain'   => '0000 (Nasional)',
        'indikator'=> 'Jumlah Perjalanan Wisatawan Nusantara',
        'data'     => $hasil
    ]);

} elseif ($type === 'provinsi') {
    $json   = fetch_bps(VAR_PROVINSI);
    $parsed = parse_bps($json);

    if (empty($parsed)) {
        echo json_encode([
            'status'  => 'error',
            'message' => 'Gagal mengambil data provinsi dari API BPS.',
            'data'    => []
        ]);
        exit;
    }

    // Ambil tahun terbaru
    $terbaru = end($parsed);
    $data    = $terbaru['data'];
    arsort($data);
    $top5 = array_slice($data, 0, 5, true);

    $hasil = [];
    foreach ($top5 as $provinsi => $jumlah) {
        $hasil[] = ['provinsi' => $provinsi, 'jumlah' => (int) $jumlah];
    }

    echo json_encode([
        'status'   => 'ok',
        'sumber'   => 'API BPS RI — webapi.bps.go.id',
        'var_id'   => VAR_PROVINSI,
        'tahun'    => $terbaru['tahun'],
        'indikator'=> 'Jumlah Perjalanan Wisatawan Nusantara per Provinsi Tujuan',
        'data'     => $hasil
    ]);

} else {
    echo json_encode(['status' => 'error', 'message' => 'type tidak dikenal. Gunakan ?type=total atau ?type=provinsi']);
}
?>
