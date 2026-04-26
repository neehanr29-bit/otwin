<?php
// ══════════════════════════════════════════════════════════
//  api/databps.php
//  Endpoint ambil data BPS Wisatawan Nusantara
//  URL: /api/databps.php?type=total
//       /api/databps.php?type=provinsi
// ══════════════════════════════════════════════════════════
 
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
 
define('BPS_KEY',  '8a298f03b114b6f2d212a5f3dac5b57e');
define('BPS_BASE', 'https://webapi.bps.go.id/v1/api/list');
 
// ── Fetch dari BPS pakai cURL ──
function fetch_bps($params) {
    $url = BPS_BASE . '?' . http_build_query(array_merge(['key' => BPS_KEY], $params));
 
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL            => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 15,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_USERAGENT      => 'Mozilla/5.0 OTWin/1.0',
    ]);
    $response = curl_exec($ch);
    $error    = curl_error($ch);
    unset($ch);
 
    if ($error || !$response) return null;
    return json_decode($response, true);
}
 
// ── Coba ambil data dengan statictable (lebih stabil) ──
function ambil_statictable($table_id) {
    $json = fetch_bps([
        'model'  => 'statictable',
        'domain' => '0000',
        'id'     => $table_id,
    ]);
    return $json;
}
 
// ── Coba ambil data dengan model data ──
function ambil_data($var_id, $th = '') {
    $params = [
        'model'  => 'data',
        'domain' => '0000',
        'var'    => $var_id,
    ];
    if ($th) $params['th'] = $th;
 
    $json = fetch_bps($params);
    return $json;
}
 
// ── Cari tahu tahun yang tersedia ──
function ambil_tahun() {
    $json = fetch_bps([
        'model'  => 'th',
        'domain' => '0000',
    ]);
    if (!$json || ($json['status'] ?? '') !== 'OK') return '2023';
    // Ambil tahun terbaru
    $data = $json['data'] ?? [];
    if (empty($data)) return '2023';
    $last = end($data);
    return $last['th_id'] ?? '2023';
}
 
$type = $_GET['type'] ?? 'total';
 
// ── TABLE ID untuk statictable BPS ──
// Jumlah Perjalanan Wisatawan Nusantara (table_id dari bps.go.id)
$TABLE_TOTAL    = '1383'; // Jumlah perjalanan wisnus nasional
$TABLE_PROVINSI = '1384'; // Wisnus per provinsi tujuan
 
if ($type === 'total') {
 
    // Coba statictable dulu
    $json = ambil_statictable($TABLE_TOTAL);
 
    if (!$json || ($json['data-availability'] ?? '') !== 'available') {
        // Fallback: coba model=data dengan berbagai var_id
        $var_ids = ['2195', '1916', '1917', '2201'];
        foreach ($var_ids as $vid) {
            $json = ambil_data($vid);
            if ($json && ($json['data-availability'] ?? '') === 'available') break;
            $json = null;
        }
    }
 
    if (!$json || ($json['data-availability'] ?? '') !== 'available') {
        echo json_encode([
            'status'  => 'error',
            'message' => 'Tidak dapat mengambil data dari API BPS. Respons: ' . json_encode($json),
            'data'    => []
        ]);
        exit;
    }
 
    // Parse data dari statictable
    $datacontent = $json['datacontent'] ?? [];
    $tahun_list  = $datacontent['tahun']       ?? ($json['tahun']       ?? []);
    $data_raw    = $datacontent['datacontent'] ?? ($json['datacontent'] ?? []);
 
    $hasil = [];
    if (!empty($tahun_list) && !empty($data_raw)) {
        foreach ($tahun_list as $kode_t => $nama_t) {
            // Ambil nilai — coba berbagai struktur
            $row_data = $data_raw[$kode_t] ?? [];
            $total    = 0;
            if (is_array($row_data)) {
                foreach ($row_data as $v) {
                    $angka = (float) str_replace([',', ' ', '.'], ['', '', ''], $v);
                    $total += $angka;
                }
            } else {
                $total = (float) str_replace([',', ' ', '.'], ['', '', ''], $row_data);
            }
            if ($total > 0) {
                $hasil[] = ['tahun' => (string)$nama_t, 'jumlah' => (int)$total];
            }
        }
    }
 
    if (empty($hasil)) {
        // Kalau parse gagal, kembalikan raw untuk debug
        echo json_encode([
            'status'  => 'error',
            'message' => 'Data diterima dari BPS tapi gagal di-parse.',
            'raw'     => array_slice($json, 0, 3),
            'data'    => []
        ]);
        exit;
    }
 
    echo json_encode([
        'status'    => 'ok',
        'sumber'    => 'API BPS RI — webapi.bps.go.id',
        'indikator' => 'Jumlah Perjalanan Wisatawan Nusantara',
        'domain'    => '0000 (Nasional)',
        'data'      => $hasil
    ]);
 
} elseif ($type === 'provinsi') {
 
    $json = ambil_statictable($TABLE_PROVINSI);
 
    if (!$json || ($json['data-availability'] ?? '') !== 'available') {
        $json = ambil_data('2201');
    }
 
    if (!$json || ($json['data-availability'] ?? '') !== 'available') {
        echo json_encode(['status' => 'error', 'message' => 'Gagal ambil data provinsi.', 'data' => []]);
        exit;
    }
 
    $datacontent = $json['datacontent'] ?? [];
    $tahun_list  = $datacontent['tahun']       ?? ($json['tahun']       ?? []);
    $turvar      = $datacontent['turvar']       ?? ($json['turvar']      ?? []);
    $data_raw    = $datacontent['datacontent'] ?? ($json['datacontent'] ?? []);
 
    if (empty($tahun_list) || empty($data_raw)) {
        echo json_encode(['status' => 'error', 'message' => 'Struktur data BPS tidak dikenali.', 'raw' => $json, 'data' => []]);
        exit;
    }
 
    // Ambil tahun terbaru
    end($tahun_list);
    $kode_terbaru = key($tahun_list);
    $nama_terbaru = $tahun_list[$kode_terbaru];
 
    $row_data = $data_raw[$kode_terbaru] ?? [];
    $provinsi_data = [];
    foreach ($turvar as $kode_w => $nama_w) {
        $nilai = $row_data[$kode_w] ?? 0;
        $angka = (float) str_replace([',', ' ', '.'], ['', '', ''], $nilai);
        if ($angka > 0) $provinsi_data[$nama_w] = $angka;
    }
 
    arsort($provinsi_data);
    $top5  = array_slice($provinsi_data, 0, 5, true);
    $hasil = [];
    foreach ($top5 as $prov => $jml) {
        $hasil[] = ['provinsi' => $prov, 'jumlah' => (int)$jml];
    }
 
    echo json_encode([
        'status'    => 'ok',
        'sumber'    => 'API BPS RI — webapi.bps.go.id',
        'tahun'     => $nama_terbaru,
        'indikator' => 'Wisatawan Nusantara per Provinsi Tujuan',
        'data'      => $hasil
    ]);
 
} elseif ($type === 'debug') {
    // Mode debug — tampilkan respons mentah dari BPS untuk troubleshoot
    $tests = [
        'statictable_1383' => ambil_statictable('1383'),
        'statictable_1384' => ambil_statictable('1384'),
        'data_var_2195'    => ambil_data('2195'),
        'data_var_1916'    => ambil_data('1916'),
        'model_th'         => fetch_bps(['model' => 'th', 'domain' => '0000']),
    ];
    echo json_encode(['status' => 'debug', 'results' => $tests], JSON_PRETTY_PRINT);
 
} else {
    echo json_encode(['status' => 'error', 'message' => 'type tidak dikenal. Gunakan ?type=total, ?type=provinsi, atau ?type=debug']);
}
?>