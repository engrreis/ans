<?php
// include/login_modal.php
// Modal de login – apenas estrutura (HTML)
// JS controla abertura/fechamento
?>
<?php require_once __DIR__ . '/login_modal.php'; ?>

<div class="ans-modal ans-modal--login" id="loginModal" aria-hidden="true">

  <div class="ans-modal__backdrop" data-close-login></div>

  <div class="ans-modal__panel" role="dialog" aria-modal="true" aria-labelledby="loginModalTitle">

    <header class="ans-modal__header">
      <div>
        <h2 class="ans-modal__title" id="loginModalTitle">Acesso ao Portal ANS</h2>
        <p class="ans-modal__sub">
          Área exclusiva para acionistas da Nova Smar.
        </p>
      </div>
      
      <button
        type="button"
        class="ans-modal__close"
        aria-label="Fechar"
        data-close-login
      >x
      </button>
    </header>

    <form
      class="ans-modal__body"
      method="post"
      action="/ans/pages/entrar.php"
      novalidate
    >

      <div class="ans-field">
        <label for="login_email">E-mail</label>
        <input
          type="email"
          id="login_email"
          name="email"
          required
          autocomplete="email"
        >
      </div>
      <div class="ans-login-row">
        <div class="ans-field ans-field--senha">
          <label for="login_senha">Senha</label>
          <input
            type="password"
            id="login_senha"
            name="senha"
            required
            autocomplete="current-password"
          >
        </div>
      
        <button type="submit" class="ans-btn ans-btn--primary ans-btn--enter">
          Entrar
        </button>
      </div>
      

      <!-- Mensagem de erro/sucesso (JS/PHP) -->
      <div class="ans-alert ans-alert--err" id="loginError" hidden>
        E-mail ou senha inválidos.
      </div>

      <footer class="ans-modal__footer">
        <!-- Ação secundária -->
        <a href="/ans/pages/cadastro.php" class="ans-btn ans-btn--secondary">
          Criar cadastro
        </a>

        <!-- Ação auxiliar -->
        <a href="/ans/pages/esqueci_senha.php" class="ans-link ans-link--muted">
          Esqueci minha senha
        </a>

      </footer>

    </form>

  </div>
</div>
