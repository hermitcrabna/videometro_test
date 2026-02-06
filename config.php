<?php
$env = 'local'; // 'local' (sviluppo) oppure 'prod'
$tlsVerify = ($env !== 'local'); // in locale disattiva la verifica TLS
$debug = ($env === 'local');
$errorLog = __DIR__ . '/storage/php-error.log';

$APP_CONFIG = [
  'azienda_id' => 6,
  'env' => $env,
  'tls_verify' => $tlsVerify,
  'debug' => $debug,
  'error_log' => $errorLog,
  // Base URL del sito pubblico (senza slash finale)
  'site_url' => 'https://videometro.tv',
  // Prefisso URL per i video pubblici (es: /video/ oppure /video/single/)
  'video_path_prefix' => '/video/',
];
