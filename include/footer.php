<?php
// include/footer.php
?>
<footer class="site-footer" role="contentinfo">
  <div class="footer-inner">

    <!-- COLUNA ESQUERDA: TEXTO + FOOTER-BOTTOM ABAIXO -->
    <div class="footer-col footer-brand">
      <div class="footer-title">Acionistas Nova Smar</div>
      <div class="footer-subtitle">Canal independente de informação e acompanhamento.</div>

      <div class="footer-disclaimer">
        Este portal constitui uma iniciativa independente, destinada exclusivamente à comunicação, ao compartilhamento de informações, bem como à elaboração de relatórios e avaliações independentes, de natureza informativa, entre acionistas da Nova Smar, relacionadas a fatos, comunicados oficiais e demais assuntos de interesse vinculados à empresa.
        <br><br>
        O portal não mantém qualquer vínculo institucional, societário, contratual ou de representação com a Nova Smar, sua diretoria, administração judicial ou quaisquer terceiros, não atuando, em nenhuma hipótese, como porta-voz ou representante da companhia.
        <br><br>
        As informações, análises, relatórios e avaliações disponibilizadas possuem caráter estritamente informativo e não constituem, sob qualquer forma, oferta, solicitação, recomendação, assessoria, consultoria ou intermediação para aquisição, alienação ou negociação de ações, valores mobiliários ou quaisquer outros ativos.
      </div>

      <div class="footer-bottom-inline">
        <span>© <?php echo date('Y'); ?> Acionistas Nova Smar</span>
        <span class="footer-sep" aria-hidden="true"></span>
        <span>Portal independente</span>
      </div>
    </div>

    <!-- COLUNA DIREITA: CONTATO -->
    <div class="footer-col footer-contact">
      <div class="footer-head">Contato</div>

      <a class="footer-contact-item" href="/ans/pages/agenda.php">
        <span class="footer-contact-label">Telefone</span>
        <span class="footer-contact-value">Agende uma reunião</span>
        <span class="footer-contact-hint">Atendimento telefônico: 8h às 9h</span>
      </a>

      <!-- EMAIL -->
      <a class="footer-contact-item" href="mailto:atendimento@acionistasmar.com.br">
        <span class="footer-contact-label">E-mail</span>
        <span class="footer-contact-value">atendimento@acionistassmar.com.br</span>
      </a>

      <div class="footer-mini">
        Horário geral: seg–sex, 9h às 18h
      </div><br>
      <a href="/ans/pages/quem_somos.php" class="footer-link">
        | Quem somos | 
      </a>
    </div>

  </div>
</footer>
<?php require_once __DIR__ . '/login_modal.php'; ?>

</body>
</html>


