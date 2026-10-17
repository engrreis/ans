<?php
// pages/quem_somos.php
require_once __DIR__ . '/../include/init.php';

$page_title = 'Quem Somos';

// se você quiser carregar um css específico, descomente:
// $page_css = 'css/quem_somos.css';

require_once __DIR__ . '/../include/head.php';
require_once __DIR__ . '/../include/header.php';
?>

<main class="main main--quem-somos">
  <div class="container">

    <h1 style="margin: 10px 0 10px;">Quem Somos</h1>
    <p style="margin: 0 0 18px; color:#2b2b2b; line-height:1.6;">
      Um portal independente, criado por acionistas, para organizar informações, fortalecer o diálogo e apoiar
      uma convivência societária mais madura.
    </p>

    <div class="grid">
      <div class="card">
        <h3>Nosso Propósito</h3>
        <p>
          Este portal nasceu como um desdobramento natural da experiência construída no site credoressmar.com.br,
          a partir da necessidade de transformar um processo complexo e sensível em um ambiente de diálogo qualificado,
          responsável e construtivo entre acionistas.
        </p>
        <p>
          A conversão de créditos trabalhistas em ações formou uma base societária ampla e diversa, com diferentes histórias,
          expectativas e níveis de conhecimento. Quando faltam informação clara e canais adequados, aumentam os ruídos,
          os conflitos e as decisões mal orientadas.
        </p>
        <p>
          Nosso propósito é reduzir esses riscos: organizar informações, promover educação societária e fortalecer uma cultura
          genuína de comunicação, respeito e racionalidade. Queremos um espaço orientado por fatos, bom senso e visão de longo prazo.
        </p>
      </div>

      <div class="card">
        <h3>Nosso Compromisso</h3>
        <p>
          Nosso compromisso é agir com integridade, confiança e transparência em tudo o que é produzido e compartilhado aqui.
          Entendemos esses valores como condições essenciais — sem eles, nosso propósito não se sustenta.
        </p>
        <p>
          Isso significa tratar informações com responsabilidade, comunicar com clareza e preservar a independência do portal,
          evitando ruídos, conflitos desnecessários e interpretações equivocadas.
        </p>
        <p>
          A transparência é a base da credibilidade; e a credibilidade é o que permite a construção de um ambiente de diálogo
          qualificado e de crescimento coletivo ao longo do tempo.
        </p>
      </div>

      <div class="card">
        <h3>Nossa Visão</h3>
        <p>
          Contribuir para a construção de um ambiente societário mais maduro, sustentado por informação de qualidade, diálogo
          responsável e participação consciente dos acionistas.
        </p>
        <p>
          Acreditamos que uma base acionária organizada e bem informada favorece decisões mais equilibradas, reduz conflitos e
          fortalece a convivência societária — com foco em continuidade, governança e sustentabilidade no longo prazo.
        </p>
        <p><strong>Produção independente:</strong> relatórios, análises e materiais educativos, sempre com caráter informativo.</p>
      </div>
    </div>
    <!-- A INICIATIVA (com foto + LinkedIn) -->
    <div class="card card-iniciativa" style="margin-top:14px;">
      <div class="iniciativa-wrap">
        <div class="iniciativa-foto">
          <img src="/ans/img/rrr.png" alt="Ricardo Reis Ricci">
        </div>

        <div class="iniciativa-texto">
          <h3>A Iniciativa</h3>
        <p>
          A iniciativa por trás deste portal é conduzida por
          <a href="https://www.linkedin.com/in/ricardoreis/" target="_blank" rel="noopener noreferrer" class="link-linkedin">
            Ricardo Reis Ricci
          </a>,
          economista, engenheiro eletricista e especialista em
          <strong>modelagem de dados aplicada à área de risco em asset management</strong>,
          com <strong>15 anos de atuação no mercado financeiro</strong>.
          Atuou como <strong>engenheiro de aplicações na Smar entre 2008 e 2013</strong>.
        </p>
        </div>
      </div>
    </div>

    <!-- EXPERIÊNCIA (este é o card que está “vazando”) -->
    <div class="card" style="margin-top:14px;">
      <p>
        Graduado em <strong>Engenharia Elétrica pela UNESP</strong> e em <strong>Ciências Econômicas pela USP</strong>, atua no campo quantitativo do mercado financeiro com modelagem de dados, análise de riscos e estruturação de métricas para decisões estratégicas.
      </p>

      <p>
        Possui certificações como <strong>CPA-20 (ANBIMA)</strong>, <strong>PQO (BM&FBOVESPA)</strong> e <strong>Agente Autônomo (Ancord)</strong>, além de formações em gestão de risco, ciência de dados e programação (ex.: Saint Paul e University of Colorado).
      </p>

      <p style="margin-bottom:0;">
        Sua experiência inclui desenvolvimento e validação de modelos quantitativos, estruturação de bases, risco de mercado e crédito, simulações e indicadores de governança, incluindo vivência em <strong>precificação de derivativos</strong>.
      </p>
    </div>

  </div> <!-- /container -->
</main>

<?php require_once __DIR__ . '/../include/footer.php'; ?>
