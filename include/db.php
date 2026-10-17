<?php
// include/db.php
require_once __DIR__ . '/config.php';

function db(): ?PDO {
  static $pdo = null;

  // DEV: sem banco
  if (DEV_MODE) return null;

  if ($pdo instanceof PDO) return $pdo;

  $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4';

  try {
    $pdo = new PDO($dsn, DB_USER, DB_PASS, [
      PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
      PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
    return $pdo;
  } catch (Throwable $e) {
    // Em produção você pode logar isso em arquivo, mas não exponha ao usuário
    return null;
  }
}
