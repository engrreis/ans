<?php
// include/init.php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/logger.php';

// inicia sessão sempre
ensure_session();

// ✅ DEV: se você quiser navegar como “logado” SEM login real, ligue esta linha nas páginas logadas.
// require_login();

// registra o acesso (mesmo sem banco)
log_access();
