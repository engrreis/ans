<?php
// include/header.php
// Header visual do Portal ANS
// IMPORTANTE: init.php DEVE ser carregado antes deste arquivo

require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/config.php';

/**
 * Regra do menu:
 * - DEV_MODE = true  → sempre menu público
 * - DEV_MODE = false → mostra menu logado se houver sessão válida
 */
$logado = (!DEV_MODE && !empty($_SESSION['usuario_id']));
?>
<header class="site-header" id="siteHeader">
  <div class="header-inner">

    <a class="brand" href="/ans/index.php" aria-label="Ir para a página inicial">
      <img
        class="brand-logo"
        src="/ans/img/logo-ans-header.svg"
        alt="Acionistas Nova Smar"
      >
    </a>

    <!-- Botão Mobile -->
    <button
      class="menu-toggle"
      id="menuToggle"
      type="button"
      aria-controls="siteNav"
      aria-expanded="false"
      aria-label="Abrir menu"
    >
      <span class="hamburger" aria-hidden="true"></span>
      <span class="menu-label">Menu</span>
    </button>

    <nav class="site-nav" id="siteNav" aria-label="Navegação principal">
      <!-- Público -->
      <a href="/ans/index.php">Home</a>
      <a href="/ans/pages/agenda.php">Agenda</a>
      <a href="/ans/pages/comunicados.php">Comunicados</a>
      <a href="/ans/pages/faq.php">FAQ</a>
      <a href="/ans/pages/contato.php">Contato</a>


      <?php if (!$logado): ?>
        <!-- CTA público -->
        <a
          class="nav-cta"
          href="#"
          data-open-login
          aria-haspopup="dialog"
        >
          Acessar
        </a>
      <?php else: ?>
        <!-- Menu logado -->
        <span class="nav-sep" aria-hidden="true"></span>
        <a href="/ans/pages/balancos.php">Balanços</a>
        <a href="/ans/pages/estatisticas.php">Estatísticas</a>
        <a href="/ans/pages/procuracoes.php">Procurações</a>
        <a href="/ans/pages/denuncia.php">Denúncia</a>
        <a class="nav-cta" href="/ans/pages/sair.php">Sair</a>
      <?php endif; ?>
    </nav>

  </div>

  <div class="nav-backdrop" id="navBackdrop" hidden></div>
</header>
