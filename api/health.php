<?php
header('Content-Type: text/plain; charset=utf-8');
echo "OK\n";
echo 'php_version=' . PHP_VERSION . "\n";
echo 'sapi=' . PHP_SAPI . "\n";
echo 'cwd=' . getcwd() . "\n";
exit;
