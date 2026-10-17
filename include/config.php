<?php
// include/config.php
define('MAIL_FROM', 'contato@acionistassmar.com.br');
define('BASE_URL', '/ans');

// ✅ DEV MODE: true no XAMPP (sem banco, sem login). Em produção: false.
define('DEV_MODE', true);

// Caminhos (ajuda a evitar erros de include)
define('BASE_PATH', dirname(__DIR__));
define('DATA_PATH', BASE_PATH . DIRECTORY_SEPARATOR . 'data');

// Logger em arquivo (DEV)
define('DEV_LOG_FILE', DATA_PATH . DIRECTORY_SEPARATOR . 'log_navegacao_dev.jsonl');

// (Produção) Config de banco — deixe preenchido quando for subir
define('DB_HOST', 'localhost');
define('DB_NAME', 'SEU_BANCO');
define('DB_USER', 'SEU_USUARIO');
define('DB_PASS', 'SUA_SENHA');
