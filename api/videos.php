<?php
declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');

$azienda_id = isset($_GET['azienda_id']) ? (int)$_GET['azienda_id'] : 1;
$limit      = isset($_GET['limit']) ? max(1, min(50, (int)$_GET['limit'])) : 20;
$offset     = isset($_GET['offset']) ? max(0, (int)$_GET['offset']) : null;
$page       = isset($_GET['page']) ? max(1, (int)$_GET['page']) : null;

$search_term = isset($_GET['search_term']) ? (string)$_GET['search_term'] : null;
$cat_id      = isset($_GET['cat_id']) ? (string)$_GET['cat_id'] : null;
$subcat_id   = isset($_GET['subcat_id']) ? (string)$_GET['subcat_id'] : null;
$featured    = isset($_GET['featured']) ? (string)$_GET['featured'] : null;
$author_id   = isset($_GET['author_id']) ? (string)$_GET['author_id'] : null;

// Se non arriva offset, calcolalo da page (1-based)
if ($offset === null && $page !== null) {
  $offset = ($page - 1) * $limit;
}

$query = [
  'azienda_id' => $azienda_id,
  'limit' => $limit,
];
if ($offset !== null) $query['offset'] = $offset;
if ($search_term !== null && $search_term !== '') $query['search_term'] = $search_term;
if ($cat_id !== null && $cat_id !== '') $query['cat_id'] = $cat_id;
if ($subcat_id !== null && $subcat_id !== '') $query['subcat_id'] = $subcat_id;
if ($featured !== null && $featured !== '') $query['featured'] = $featured;
if ($author_id !== null && $author_id !== '') $query['author_id'] = $author_id;

$url = 'https://www.videometro.tv/Api/get_video?' . http_build_query($query);

// Se vuoi forzare page/limit al posto di offset/limit, puoi cambiare qui.

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

// Pass-through (volendo puoi normalizzare qui)
echo $raw;
