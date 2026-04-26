<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
 
define('BPS_KEY',    '8a298f03b114b6f2d212a5f3dac5b57e');
define('BPS_BASE',   'https://webapi.bps.go.id/v1/api/list');
define('BPS_DOMAIN', '5100');
define('TABLE_DOMESTIK',    '29');
define('TABLE_MANCANEGARA', '28');
 
function fetch_bps($params) {
    $url = BPS_BASE . '?' . http_build_query(array_merge(['key' => BPS_KEY], $params));
    $ch  = curl_init();
    curl_setopt_array($ch, [CURLOPT_URL=>$url,CURLOPT_RETURNTRANSFER=>true,CURLOPT_TIMEOUT=>15,CURLOPT_SSL_VERIFYPEER=>false,CURLOPT_USERAGENT=>'Mozilla/5.0']);
    $r = curl_exec($ch); unset($ch);
    return $r ? json_decode($r, true) : null;
}
 
function ambil_statictable($id) {
    return fetch_bps(['model'=>'statictable','domain'=>BPS_DOMAIN,'id'=>$id]);
}
 
function parse_total($json) {
    if (!$json || ($json['data-availability']??'') !== 'available') return [];
    $dc   = $json['datacontent'] ?? [];
    $thn  = $dc['tahun']       ?? ($json['tahun']       ?? []);
    $raw  = $dc['datacontent'] ?? ($json['datacontent'] ?? []);
    if (empty($thn)||empty($raw)) return [];
    $hasil = [];
    foreach ($thn as $kt => $nt) {
        $row = $raw[$kt] ?? [];
        $tot = 0;
        if (is_array($row)) foreach ($row as $v) { $tot += (float)str_replace([',','.',' '],['','',''],$v); }
        else $tot = (float)str_replace([',','.',' '],['','',''],$row);
        if ($tot > 0) $hasil[] = ['tahun'=>(string)$nt,'jumlah'=>(int)$tot];
    }
    return $hasil;
}
 
$type = $_GET['type'] ?? 'total';
 
if ($type === 'total') {
    $json  = ambil_statictable(TABLE_DOMESTIK);
    $rows  = parse_total($json);
    if (empty($rows)) { echo json_encode(['status'=>'error','message'=>'Gagal parse data BPS.','raw'=>$json,'data'=>[]]); exit; }
    // Agregasi per tahun (data bulanan)
    $agg = [];
    foreach ($rows as $r) {
        preg_match('/\d{4}/',$r['tahun'],$m);
        $thn = $m[0] ?? $r['tahun'];
        $agg[$thn] = ($agg[$thn]??0) + $r['jumlah'];
    }
    ksort($agg);
    $final = [];
    foreach ($agg as $t=>$j) $final[] = ['tahun'=>$t,'jumlah'=>$j];
    echo json_encode(['status'=>'ok','sumber'=>'API BPS RI — webapi.bps.go.id','domain'=>'5100 (Provinsi Bali)','table_id'=>TABLE_DOMESTIK,'indikator'=>'Banyaknya Wisatawan Domestik ke Bali','data'=>$final]);
 
} elseif ($type === 'provinsi') {
    $json = ambil_statictable(TABLE_MANCANEGARA);
    if (!$json || ($json['data-availability']??'') !== 'available') { echo json_encode(['status'=>'error','message'=>'Gagal.','data'=>[]]); exit; }
    $dc  = $json['datacontent'] ?? [];
    $thn = $dc['tahun']       ?? ($json['tahun']??[]);
    $tv  = $dc['turvar']      ?? ($json['turvar']??[]);
    $raw = $dc['datacontent'] ?? ($json['datacontent']??[]);
    end($thn); $kt = key($thn); $nt = $thn[$kt];
    $rd = $raw[$kt] ?? [];
    $dd = [];
    foreach ($tv as $kw=>$nw) { $v=(float)str_replace([',','.',' '],['','',''],$rd[$kw]??0); if($v>0&&strlen($nw)>1) $dd[$nw]=(int)$v; }
    arsort($dd); $top5=array_slice($dd,0,5,true); $final=[];
    foreach ($top5 as $n=>$j) $final[]=['provinsi'=>$n,'jumlah'=>$j];
    echo json_encode(['status'=>'ok','sumber'=>'API BPS RI','tahun'=>$nt,'indikator'=>'Wisatawan ke Bali per Kategori','data'=>$final]);
 
} elseif ($type === 'debug') {
    echo json_encode(['status'=>'debug','domain'=>BPS_DOMAIN,'results'=>['table_29'=>ambil_statictable('29'),'table_28'=>ambil_statictable('28')]],JSON_PRETTY_PRINT);
 
} else {
    echo json_encode(['status'=>'error','message'=>'Gunakan ?type=total, ?type=provinsi, atau ?type=debug']);
}
?>