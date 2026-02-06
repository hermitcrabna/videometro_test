<?php
declare(strict_types=1);

require_once __DIR__ . '/../bootstrap.php';
$boot = vm_bootstrap('application/json; charset=utf-8');
$APP_CONFIG = $boot['config'];
$verifyTls = $boot['verify_tls'];
$verifyHost = $boot['verify_host'];
$debug = $boot['debug'];

$azienda_id = isset($_GET['azienda_id']) ? (int)$_GET['azienda_id'] : 1;

$query = [
  'azienda_id' => $azienda_id,
];

$url = 'https://www.videometro.tv/Api/get_azienda?' . http_build_query($query);

$ch = curl_init($url);
curl_setopt_array($ch, [
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_TIMEOUT => 15,
  CURLOPT_FOLLOWLOCATION => true,
  CURLOPT_SSL_VERIFYHOST => $verifyHost,
  CURLOPT_SSL_VERIFYPEER => $verifyTls,
]);

$raw = curl_exec($ch);
$http = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
$err  = curl_error($ch);
$errno = curl_errno($ch);
curl_close($ch);

if ($raw === false || $http < 200 || $http >= 300) {
  http_response_code(502);
  echo json_encode([
    'ok' => false,
    'error' => 'Errore chiamata upstream',
    'http' => $http,
    'detail' => $err ?: null,
    'errno' => $errno ?: null,
    'url' => $url,
  ]);
  exit;
}

echo $raw;
