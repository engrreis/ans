<?php
// pages/entrar.php
require_once __DIR__ . '/../include/init.php';

/*
 * Página de fallback para login.
 * Se alguém cair aqui (sem JS ou erro de interceptação),
 * redirecionamos para a Home e abrimos o modal.
 */

header('Location: /ans/index.php?login=1');
exit;
