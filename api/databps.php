<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

define('BPS_KEY',    '8a298f03b114b6f2d212a5f3dac5b57e');
define('BPS_BASE',   'https://webapi.bps.go.id/v1/api/list');
define('BPS_DOMAIN', '5100');
define('VAR_WISNUS', '412'); // Wisnus per Kab/Kota Asal
define('VAR_TUJUAN', '413'); // Wisnus per Kab/Kota Tujuan

// th_id dari API BPS domain 5100
$TH_MAP = [
    '119' => '2019',
    '120' => '2020',
    '121' => '2021',
    '122' => '2022',
    '123' => '2023',
    '124' => '2024',
];

function fetch_bps($params) {
    $url = BPS_BASE . '?' . http_build_query(array_merge(['key' => BPS_KEY], $params));
    $ch  = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL=>$url, CURLOPT_RETURNTRANSFER=>true,
        CURLOPT_TIMEOUT=>15, CURLOPT_SSL_VERIFYPEER=>false,
        CURLOPT_USERAGENT=>'Mozilla/5.0 OTWin/1.0'
    ]);
    $r = curl_exec($ch); unset($ch);
    return $r ? json_decode($r, true) : null;
}

function ambil_per_tahun($var_id, $th_id) {
    return fetch_bps([
        'model'  => 'data',
        'domain' => BPS_DOMAIN,
        'var'    => $var_id,
        'th'     => $th_id,
    ]);
}

$type = $_GET['type'] ?? 'total';

if ($type === 'total') {
    global $TH_MAP;
    $hasil = [];

    foreach ($TH_MAP as $th_id => $tahun) {
        $json = ambil_per_tahun(VAR_WISNUS, $th_id);
        if (!$json || ($json['data-availability'] ?? '') !== 'available') continue;

        $dc  = $json['datacontent'] ?? [];
        $raw = $dc['datacontent'] ?? ($json['datacontent'] ?? []);

        $total = 0;
        if (is_array($raw)) {
            array_walk_recursive($raw, function($v) use (&$total) {
                $angka = (float) str_replace([',', ' '], ['', ''], $v);
                $total += $angka;
            });
        }

        if ($total > 0) {
            $hasil[] = ['tahun' => $tahun, 'jumlah' => (int)$total];
        }
    }

    if (empty($hasil)) {
        echo json_encode(['status'=>'error','message'=>'Tidak ada data yang berhasil diambil dari BPS.','data'=>[]]);
        exit;
    }

    echo json_encode([
        'status'    => 'ok',
        'sumber'    => 'API BPS RI — webapi.bps.go.id',
        'domain'    => '5100 (Provinsi Bali)',
        'var_id'    => VAR_WISNUS,
        'indikator' => 'Jumlah Perjalanan Wisatawan Nusantara di Bali',
        'data'      => $hasil
    ]);

} elseif ($type === 'provinsi') {
    global $TH_MAP;

    // Ambil tahun terbaru yang tersedia
    $th_ids   = array_keys($TH_MAP);
    $tahun_nm = '';
    $json     = null;

    foreach (array_reverse($th_ids) as $th_id) {
        $json = ambil_per_tahun(VAR_TUJUAN, $th_id);
        if ($json && ($json['data-availability'] ?? '') === 'available') {
            $tahun_nm = $TH_MAP[$th_id];
            break;
        }
    }

    if (!$json || ($json['data-availability'] ?? '') !== 'available') {
        echo json_encode(['status'=>'error','message'=>'Gagal ambil data provinsi.','data'=>[]]);
        exit;
    }

    // Struktur BPS vervar = nama kab/kota, datacontent = nilai
    $vervar = $json['vervar'] ?? [];
    $raw    = $json['datacontent'] ?? [];
    $dd     = [];

    foreach ($vervar as $item) {
        $kode = (string)($item['val'] ?? '');
        $nama = trim($item['label'] ?? '');
        if (!$nama || $nama === 'Tidak ada') continue;

        // Cari nilai di datacontent[turvar_key][kode]
        $nilai = 0;
        if (is_array($raw)) {
            foreach ($raw as $turvar_key => $row) {
                if (is_array($row) && isset($row[$kode])) {
                    $nilai = (float) str_replace([',', ' '], ['', ''], $row[$kode]);
                    break;
                }
            }
        }
        if ($nilai > 0) $dd[$nama] = (int)$nilai;
    }

    arsort($dd);
    $top5  = array_slice($dd, 0, 5, true);
    $final = [];
    foreach ($top5 as $n => $j) $final[] = ['provinsi' => $n, 'jumlah' => $j];

    echo json_encode([
        'status'    => 'ok',
        'sumber'    => 'API BPS RI — webapi.bps.go.id',
        'tahun'     => $tahun_nm,
        'indikator' => 'Wisatawan Nusantara per Kab/Kota Tujuan di Bali',
        'data'      => $final
    ]);

} elseif ($type === 'debug') {
    global $TH_MAP;
    $test = ambil_per_tahun(VAR_WISNUS, '123'); // 2023
    echo json_encode(['status'=>'debug','th_map'=>$TH_MAP,'sample_2023'=>$test], JSON_PRETTY_PRINT);

} else {
    echo json_encode(['status'=>'error','message'=>'Gunakan ?type=total, ?type=provinsi, atau ?type=debug']);
}
?>
