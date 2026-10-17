<?php
require_once __DIR__ . '/include/init.php';
require_once __DIR__ . '/include/config.php';

// Menu logado real só em produção
$logado = (!DEV_MODE && !empty($_SESSION['usuario_id']));
if ($logado) {
  header('Location: /ans/home.php');
  exit;
}
?>
<!doctype html>
<html lang="pt-br">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Acionistas Nova Smar | Portal Independente</title>

  <link rel="stylesheet" href="/ans/css/header.css">
  <link rel="stylesheet" href="/ans/css/home.css">
  <link rel="stylesheet" href="/ans/css/footer.css">

  <!-- Modal de login -->
  <link rel="stylesheet" href="/ans/css/login_modal.css">
</head>
<body>

<?php include __DIR__ . '/include/header.php'; ?>

<section class="hero" aria-label="Destaque principal">
  <!-- Fallback de imagem SEMPRE existe via background no container -->
  <div class="hero-media" role="img" aria-label="Imagem de fundo">
    <video
      class="hero-video"
      autoplay
      muted
      loop
      playsinline
      preload="metadata"
      poster="/ans/img/Hero1.webp"
    >
      <source src="/ans/img/video_fundo.mp4" type="video/mp4">
      <!-- Se o browser não suportar vídeo, fica só a imagem de fundo -->
    </video>

    <div class="hero-overlay" aria-hidden="true"></div>
  </div>

  <div class="hero-content">
    <div class="hero-container">
      <h1>Transparência e acompanhamento</h1>
      <p class="hero-sub">
        Canal independente dedicado à comunicação de fatos, relatórios e informações relevantes<br> aos acionistas da Nova Smar.
      </p>

      <div class="hero-actions">
        <a class="btn btn-primary" href="/ans/pages/comunicados.php">Ver comunicados</a>

        <?php if (!$logado): ?>
          <!-- Abre modal (fallback: href vai para entrar.php se JS falhar) -->
          <a
            class="btn btn-outline"
            href="/ans/pages/entrar.php"
            data-open-login
            aria-haspopup="dialog"
          >
            Acessar
          </a>
        <?php else: ?>
          <a class="btn btn-outline" href="/ans/pages/balancos.php">Acessar área logada</a>
        <?php endif; ?>
      </div>

      <div class="hero-note">
        Não intermediamos compra e venda de ações.
      </div>
    </div>
  </div>
</section>

<main class="main">
  <div class="container">

    <section class="grid">
      <article class="card">
        <h3>Comunicados</h3>
        <p>Atualizações do portal organizadas por data e tema.</p>
        <a class="card-link" href="/ans/pages/comunicados.php">Acessar</a>
      </article>

      <article class="card">
        <h3>FAQ</h3>
        <p>Respostas objetivas sobre o portal, uso, assembleias e participação.</p>
        <a class="card-link" href="/ans/pages/faq.php">Acessar</a>
      </article>

      <article class="card">
        <h3>Contato</h3>
        <p>Canal para dúvidas e sugestões sobre o portal.</p>
        <a class="card-link" href="/ans/pages/contato.php">Acessar</a>
      </article>
    </section>

    <section class="banner">
      <div class="banner-text">
        <h2>Área do acionista (conteúdo completo)</h2>
        <p>Na área logada vc encontra: Balanços, relatórios personalizados, canal de denúncias,<br> Agenda de eventos, modelos de procurações, entre outras ferramentas úteis para se informar.</p>
      </div>
      <div class="banner-actions">
        <?php if (!$logado): ?>
          <!-- Abre modal (fallback: href vai para entrar.php se JS falhar) -->
          <a
            class="btn btn-primary"
            href="/ans/pages/entrar.php"
            data-open-login
            aria-haspopup="dialog"
          >
            Acessar
          </a>
          <a class="btn btn-outline" href="/ans/pages/cadastro.php">Criar cadastro</a>
        <?php else: ?>
          <a class="btn btn-primary" href="/ans/pages/balancos.php">Ir para Balanços</a>
        <?php endif; ?>
      </div>
    </section>

  </div>
</main>

<?php include __DIR__ . '/include/footer.php'; ?>

<!-- Modal (HTML) -->
<?php include __DIR__ . '/include/login_modal.php'; ?>

<!-- JS -->
<script src="/ans/js/login_modal.js"></script>
<script src="/ans/js/header.js"></script>
</body>
</html>
