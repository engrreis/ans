<?php
// include/auth.php
require_once __DIR__ . '/config.php';

function ensure_session(): void {
  if (session_status() === PHP_SESSION_NONE) {
    session_start();
  }
}

function current_user_id(): ?int {
  ensure_session();
  return isset($_SESSION['usuario_id']) ? (int)$_SESSION['usuario_id'] : null;
}

/**
 * Em produção: exige login.
 * Em DEV_MODE: cria sessão fake para você navegar sem login.
 */
function require_login(): void {
  ensure_session();

  if (DEV_MODE) {
    if (empty($_SESSION['usuario_id'])) {
      $_SESSION['usuario_id']   = 1;
      $_SESSION['usuario_nome'] = 'Ricardo (DEV)';
      $_SESSION['usuario_email'] = 'dev@localhost';
    }
    return;
  }

  if (empty($_SESSION['usuario_id'])) {
    header('Location: /login.php');
    exit;
  }
}
