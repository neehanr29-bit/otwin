<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
 
define('BPS_KEY',    '8a298f03b114b6f2d212a5f3dac5b57e');
define('BPS_BASE',   'https://webapi.bps.go.id/v1/api/list');
define('BPS_DOMAIN', '5100'); // Bali
define('VAR_WISNUS_ASAL',   '412'); // Wisnus per Kab/Kota Asal
define('VAR_WISNUS_TUJUAN', '413'); // Wisnus per Kab/Kota Tujuan
 
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
 
function ambil_var($var_id) {
    return fetch_bps(['model'=>'data','domain'=>BPS_DOMAIN,'var'=>$var_id]);
}
 
function parse_var($json) {
    if (!$json || ($json['data-availability']??'') !== 'available') return [];
 
    $dc  = $json['datacontent'] ?? [];
    $thn = $dc['tahun']        ?? ($json['tahun']       ?? []);
    $tv  = $dc['turvar']       ?? ($json['turvar']      ?? []);
    $raw = $dc['datacontent']  ?? ($json['datacontent'] ?? []);
 
    if (empty($thn) || empty($raw)) return [];
 
    $hasil = [];
    foreach ($thn as $kt => $nt) {
        $row = $raw[$kt] ?? [];
        $tot = 0;
        if (is_array($row)) {
            foreach ($row as $v) {
                $tot += (float) str_replace([',','.',' '], ['','',''], $v);
            }
        } else {
            $tot = (float) str_replace([',','.',' '], ['','',''], $row);
        }
        if ($tot > 0) $hasil[] = ['tahun' => (string)$nt, 'jumlah' => (int)$tot];
    }
    return $hasil;
}
 
function parse_per_wilayah($json) {
    if (!$json || ($json['data-availability']??'') !== 'available') return [];
 
    $dc  = $json['datacontent'] ?? [];
    $thn = $dc['tahun']       ?? ($json['tahun']  ?? []);
    $tv  = $dc['turvar']      ?? ($json['turvar'] ?? []);
    $raw = $dc['datacontent'] ?? ($json['datacontent'] ?? []);
 
    if (empty($thn) || empty($raw) || empty($tv)) return [];
 
    // Ambil tahun terbaru
    end($thn); $kt = key($thn); $nt = $thn[$kt];
    $row = $raw[$kt] ?? [];
 
    $data = [];
    foreach ($tv as $kw => $nw) {
        $v = (float) str_replace([',','.',' '], ['','',''], $row[$kw] ?? 0);
        if ($v > 0 && strlen(trim($nw)) > 1) $data[trim($nw)] = (int)$v;
    }
    arsort($data);
    return ['tahun' => $nt, 'data' => array_slice($data, 0, 5, true)];
}
 
$type = $_GET['type'] ?? 'total';
 
if ($type === 'total') {
    $json  = ambil_var(VAR_WISNUS_ASAL);
    $rows  = parse_var($json);
 
    if (empty($rows)) {
        echo json_encode(['status'=>'error','message'=>'Gagal parse data BPS.','raw'=>$json,'data'=>[]]);
        exit;
    }
 
    // Agregasi per tahun
    $agg = [];
    foreach ($rows as $r) {
        preg_match('/\d{4}/', $r['tahun'], $m);
        $thn = $m[0] ?? $r['tahun'];
        $agg[$thn] = ($agg[$thn] ?? 0) + $r['jumlah'];
    }
    ksort($agg);
    $final = [];
    foreach ($agg as $t => $j) $final[] = ['tahun' => $t, 'jumlah' => $j];
 
    echo json_encode([
        'status'    => 'ok',
        'sumber'    => 'API BPS RI — webapi.bps.go.id',
        'domain'    => '5100 (Provinsi Bali)',
        'var_id'    => VAR_WISNUS_ASAL,
        'indikator' => 'Jumlah Perjalanan Wisatawan Nusantara di Bali',
        'data'      => $final
    ]);
 
} elseif ($type === 'provinsi') {
    $json   = ambil_var(VAR_WISNUS_TUJUAN);
    $result = parse_per_wilayah($json);
 
    if (empty($result)) {
        echo json_encode(['status'=>'error','message'=>'Gagal ambil data wilayah.','data'=>[]]);
        exit;
    }
 
    $final = [];
    foreach ($result['data'] as $n => $j) $final[] = ['provinsi' => $n, 'jumlah' => $j];
 
    echo json_encode([
        'status'    => 'ok',
        'sumber'    => 'API BPS RI — webapi.bps.go.id',
        'tahun'     => $result['tahun'],
        'indikator' => 'Wisatawan Nusantara per Kab/Kota Tujuan di Bali',
        'data'      => $final
    ]);
 
} elseif ($type === 'debug') {
    echo json_encode([
        'status'  => 'debug',
        'domain'  => BPS_DOMAIN,
        'results' => [
            'var_412_asal'   => ambil_var('412'),
            'var_413_tujuan' => ambil_var('413'),
        ]
    ], JSON_PRETTY_PRINT);
 
} else {
    echo json_encode(['status'=>'error','message'=>'Gunakan ?type=total, ?type=provinsi, atau ?type=debug']);
}
?>