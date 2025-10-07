<?php
function db() {
  static $pdo;
  if ($pdo) return $pdo;
  $dsn = 'mysql:host=localhost;dbname=estoquepecas;charset=utf8mb4';
  $user = 'root';          // USBWebserver
  $pass = 'usbw';          // senha padrão
  $opts = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
  ];
  $pdo = new PDO($dsn, $user, $pass, $opts);
  return $pdo;
}
?>
