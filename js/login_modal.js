/* =========================================================
   login_modal.js — Modal de Login | Portal ANS
   ========================================================= */

(function () {
  const modal = document.getElementById('loginModal');
  if (!modal) return;

  const openButtons = document.querySelectorAll('[data-open-login]');
  const closeButtons = modal.querySelectorAll('[data-close-login]');
  const body = document.body;

  function openModal() {
    modal.classList.add('is-open');
    modal.setAttribute('aria-hidden', 'false');
    body.style.overflow = 'hidden';

    // foco no primeiro input
    const emailInput = modal.querySelector('#login_email');
    if (emailInput) {
      setTimeout(() => emailInput.focus(), 100);
    }
  }

  function closeModal() {
    modal.classList.remove('is-open');
    modal.setAttribute('aria-hidden', 'true');
    body.style.overflow = '';
  }

  /* Abrir modal */
  openButtons.forEach(btn => {
    btn.addEventListener('click', function (e) {
      // fallback: se JS rodar, impede navegação
      e.preventDefault();
      openModal();
    });
  });

  /* Fechar modal */
  closeButtons.forEach(btn => {
    btn.addEventListener('click', closeModal);
  });

  /* ESC fecha */
  document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape' && modal.classList.contains('is-open')) {
      closeModal();
    }
  });
  // Abre o modal automaticamente se vier por fallback (?login=1)
  if (window.location.search.includes('login=1')) {
    openLoginModal();
  }
})();
