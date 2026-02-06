<?php
declare(strict_types=1);

function vm_bootstrap(?string $contentType = null): array {
  $APP_CONFIG = [];
  if (file_exists(__DIR__ . '/config.php')) {
    include __DIR__ . '/config.php';
  }

  $verifyTls = (bool)($APP_CONFIG['tls_verify'] ?? false);
  $verifyHost = $verifyTls ? 2 : 0;
  $debug = (bool)($APP_CONFIG['debug'] ?? false);

  if ($debug) {
    ini_set('display_errors', '1');
    ini_set('display_startup_errors', '1');
    error_reporting(E_ALL);
  }
  ini_set('log_errors', '1');
  if (!empty($APP_CONFIG['error_log'])) {
    ini_set('error_log', (string)$APP_CONFIG['error_log']);
  }

  set_error_handler(function($severity, $message, $file, $line) {
    error_log("[php] {$severity} {$message} in {$file}:{$line}");
    return false;
  });
  set_exception_handler(function($e) {
    error_log('[exception] ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine());
  });

  if ($debug) {
    register_shutdown_function(function() {
      $err = error_get_last();
      if (!$err) return;
      $fatal = [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR];
      if (!in_array($err['type'], $fatal, true)) return;
      http_response_code(500);
      header('Content-Type: text/plain; charset=utf-8');
      echo "Fatal PHP error: {$err['message']} in {$err['file']}:{$err['line']}";
    });

    if (isset($_GET['debug'])) {
      header('Content-Type: text/plain; charset=utf-8');
      echo "BOOT\n";
      echo 'php=' . PHP_VERSION . "\n";
      echo 'verify_tls=' . ($verifyTls ? '1' : '0') . "\n";
      echo 'error_log=' . ($APP_CONFIG['error_log'] ?? '') . "\n";
      exit;
    }
  }

  if ($contentType) {
    header('Content-Type: ' . $contentType);
  }

  return [
    'config' => $APP_CONFIG,
    'verify_tls' => $verifyTls,
    'verify_host' => $verifyHost,
    'debug' => $debug,
  ];
}
