<?php
// include/logger.php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/db.php';

function client_ip(): string {
  // DEV: normalmente 127.0.0.1
  $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
  return $ip;
}

function ip_hash(string $ip): string {
  // Hash simples (não expõe IP no log). Você pode trocar o SALT depois.
  $salt = 'ACS_DEV_SALT_CHANGE_LATER';
  return hash('sha256', $salt . '|' . $ip);
}

function request_path(): string {
  $uri = $_SERVER['REQUEST_URI'] ?? '/';
  // remove querystring para agrupar acessos por página
  $parts = explode('?', $uri, 2);
  return $parts[0];
}

function log_access(): void {
  ensure_session();

  $entry = [
    'ts'         => date('c'),
    'usuario_id' => current_user_id(),              // null se visitante
    'sessao_id'  => session_id(),
    'pagina'     => request_path(),
    'metodo'     => $_SERVER['REQUEST_METHOD'] ?? 'GET',
    'referer'    => $_SERVER['HTTP_REFERER'] ?? null,
    'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null,
    'ip_hash'    => ip_hash(client_ip()),
  ];

  // DEV: grava em arquivo .jsonl (1 linha por evento)
  if (DEV_MODE) {
    if (!is_dir(DATA_PATH)) {
      @mkdir(DATA_PATH, 0755, true);
    }
    @file_put_contents(DEV_LOG_FILE, json_encode($entry, JSON_UNESCAPED_UNICODE) . PHP_EOL, FILE_APPEND);
    return;
  }

  // Produção: grava no banco (se existir conexão)
  $pdo = db();
  if (!$pdo) return;

  // Você cria a tabela depois (quando subir). Por enquanto, só deixo pronto.
  $sql = "INSERT INTO log_navegacao
          (usuario_id, sessao_id, pagina, metodo, referer, user_agent, ip_hash, criado_em)
          VALUES (:usuario_id, :sessao_id, :pagina, :metodo, :referer, :user_agent, :ip_hash, NOW())";

  try {
    $st = $pdo->prepare($sql);
    $st->execute([
      ':usuario_id' => $entry['usuario_id'],
      ':sessao_id'  => $entry['sessao_id'],
      ':pagina'     => $entry['pagina'],
      ':metodo'     => $entry['metodo'],
      ':referer'    => $entry['referer'],
      ':user_agent' => $entry['user_agent'],
      ':ip_hash'    => $entry['ip_hash'],
    ]);
  } catch (Throwable $e) {
    // silencioso para não quebrar a navegação
  }
}
