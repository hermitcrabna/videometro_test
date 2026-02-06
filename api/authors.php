<?php
declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');

$azienda_id = isset($_GET['azienda_id']) ? (int)$_GET['azienda_id'] : 1;
$limit      = isset($_GET['limit']) ? max(1, min(50, (int)$_GET['limit'])) : 20;
$offset     = isset($_GET['offset']) ? max(0, (int)$_GET['offset']) : 0;
$search_term = isset($_GET['search_term']) ? (string)$_GET['search_term'] : null;

$query = [
  'azienda_id' => $azienda_id,
  'limit' => $limit,
  'offset' => $offset,
];
if ($search_term !== null && $search_term !== '') $query['search_term'] = $search_term;

$url = 'https://www.videometro.tv/Api/get_authors?' . http_build_query($query);

$ch = curl_init($url);
curl_setopt_array($ch, [
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_TIMEOUT => 15,
  CURLOPT_FOLLOWLOCATION => true,
  // Dev-only: evita errori CA locali. In produzione usa un CA bundle valido.
  CURLOPT_SSL_VERIFYHOST => 0,
  CURLOPT_SSL_VERIFYPEER => 0,
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
