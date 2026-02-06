<?php
require_once __DIR__ . '/bootstrap.php';
$boot = vm_bootstrap(null);
$APP_CONFIG = $boot['config'];
$verifyTls = $boot['verify_tls'];
$verifyHost = $boot['verify_host'];

header('Content-Type: application/xml; charset=utf-8');

$aziendaId = (int)($APP_CONFIG['azienda_id'] ?? 1);
$siteUrl = rtrim($APP_CONFIG['site_url'] ?? '', '/');
$videoPrefix = $APP_CONFIG['video_path_prefix'] ?? '/video/';
if ($videoPrefix === '' || $videoPrefix[0] !== '/') $videoPrefix = '/' . $videoPrefix;

function curl_json(string $url, int $verifyHost, bool $verifyTls): ?array {
  $ch = curl_init($url);
  curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_TIMEOUT => 20,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_SSL_VERIFYHOST => $verifyHost,
    CURLOPT_SSL_VERIFYPEER => $verifyTls,
  ]);
  $raw = curl_exec($ch);
  $http = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
  curl_close($ch);
  if ($raw === false || $http < 200 || $http >= 300) return null;
  $data = json_decode($raw, true);
  return is_array($data) ? $data : null;
}

$urls = [];
$urls[] = $siteUrl . '/index.php';
$urls[] = $siteUrl . '/authors.php';

// Videos
$limit = 50;
$offset = 0;
for ($i = 0; $i < 2000; $i++) {
  $url = "https://www.videometro.tv/Api/get_video?azienda_id={$aziendaId}&limit={$limit}&offset={$offset}";
  $data = curl_json($url, $verifyHost, $verifyTls);
  if (!$data || !is_array($data)) break;
  if (count($data) === 0) break;
  foreach ($data as $v) {
    $slug = $v['slug'] ?? null;
    if ($slug) {
      $urls[] = $siteUrl . $videoPrefix . $slug;
    }
  }
  if (count($data) < $limit) break;
  $offset += $limit;
}

// Authors (if slug exists)
$limit = 50;
$offset = 0;
for ($i = 0; $i < 2000; $i++) {
  $url = "https://www.videometro.tv/Api/get_authors?azienda_id={$aziendaId}&limit={$limit}&offset={$offset}";
  $data = curl_json($url, $verifyHost, $verifyTls);
  if (!$data || !is_array($data)) break;
  if (count($data) === 0) break;
  foreach ($data as $a) {
    $slug = $a['slug'] ?? null;
    if ($slug) {
      $urls[] = $siteUrl . '/protagonisti/' . $slug;
    }
  }
  if (count($data) < $limit) break;
  $offset += $limit;
}

$urls = array_values(array_unique($urls));

echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
echo "<urlset xmlns=\"http://www.sitemaps.org/schemas/sitemap/0.9\">\n";
foreach ($urls as $u) {
  $loc = htmlspecialchars($u, ENT_QUOTES | ENT_XML1, 'UTF-8');
  echo "  <url><loc>{$loc}</loc></url>\n";
}
echo "</urlset>";
