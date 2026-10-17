<?php
// pages/agenda.php
$page_title = 'Agenda';
$page_js = ['/ans/js/agenda.js'];


require_once __DIR__ . '/../include/init.php';

$page_title = 'Agenda';

require_once __DIR__ . '/../include/head.php';
require_once __DIR__ . '/../include/header.php';
// ======================
// CONFIG SIMPLES
// ======================
define('DIAS_A_FRENTE', 10); // mostrar apenas 10 dias à frente

// janelas do dia (15 min). Edite aqui quando quiser.
$JANELAS_HORARIO = [
  ['08:00', '09:00'], // 08:00, 08:15, 08:30, 08:45
  // ['14:00', '17:00'], // (exemplo) se quiser mais horários depois
];

$DURACAO_MIN = 15;
$PHONE = '16-99177-9863';
$PHONE_FMT = '(16) 99177-9863';

$erro = '';
$sucesso = false;
$sucesso_msg = '';
$agendamento = null;

// tenta obter PDO do config (padrão do seu projeto)
if (!isset($pdo) || !($pdo instanceof PDO)) {
  $erro = 'Conexão com banco não disponível. Verifique include/config.php.';
}

// ======================
// Funções auxiliares
// ======================
function only_digits($s) {
  return preg_replace('/\D+/', '', (string)$s);
}

function h($s) {
  return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');
}

function build_slots($dateYmd, $janelas, $duracaoMin) {
  $slots = [];
  foreach ($janelas as $jan) {
    [$ini, $fim] = $jan;
    $start = DateTime::createFromFormat('Y-m-d H:i', $dateYmd . ' ' . $ini);
    $end   = DateTime::createFromFormat('Y-m-d H:i', $dateYmd . ' ' . $fim);
    if (!$start || !$end) continue;

    while ($start < $end) {
      $slots[] = $start->format('H:i');
      $start->modify("+{$duracaoMin} minutes");
    }
  }
  return array_values(array_unique($slots));
}

function in_range_date($dateYmd) {
  $tz = new DateTimeZone('America/Sao_Paulo');
  $today = new DateTime('today', $tz);
  $max = (clone $today)->modify('+' . DIAS_A_FRENTE . ' days');

  $d = DateTime::createFromFormat('Y-m-d', $dateYmd, $tz);
  if (!$d) return false;

  // permitido: hoje até hoje+DIAS_A_FRENTE (inclusive)
  return ($d >= $today && $d <= $max);
}

function mail_simple($to, $subject, $body) {
  $headers = [];
  $headers[] = 'MIME-Version: 1.0';
  $headers[] = 'Content-Type: text/plain; charset=UTF-8';

  // se você tiver uma constante MAIL_FROM no config.php, usa; senão, fallback
  $from = defined('MAIL_FROM') ? MAIL_FROM : ('no-reply@' . ($_SERVER['HTTP_HOST'] ?? 'localhost'));
  $headers[] = 'From: ' . $from;

  $subj = '=?UTF-8?B?' . base64_encode($subject) . '?=';

  return @mail($to, $subj, $body, implode("\r\n", $headers));
}

// ======================
// Carrega dia selecionado
// ======================
$tz = new DateTimeZone('America/Sao_Paulo');
$hoje = new DateTime('today', $tz);

$selectedDate = $_GET['d'] ?? $hoje->format('Y-m-d');
if (!in_range_date($selectedDate)) {
  $selectedDate = $hoje->format('Y-m-d');
}

$slots = build_slots($selectedDate, $JANELAS_HORARIO, $DURACAO_MIN);

// ======================
// Busca slots já reservados (PENDENTE/CONFIRMADA)
// ======================
$ocupados = [];
if (empty($erro)) {
  $iniDia = $selectedDate . ' 00:00:00';
  $fimDia = $selectedDate . ' 23:59:59';

  $st = $pdo->prepare("
    SELECT reuniao_em
    FROM agenda_reunioes
    WHERE reuniao_em BETWEEN :ini AND :fim
      AND status IN ('PENDENTE','CONFIRMADA')
  ");
  $st->execute([':ini' => $iniDia, ':fim' => $fimDia]);

  while ($r = $st->fetch(PDO::FETCH_ASSOC)) {
    $dt = new DateTime($r['reuniao_em'], $tz);
    $ocupados[$dt->format('H:i')] = true;
  }
}

// ======================
// POST: agendar
// ======================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && empty($erro)) {
  $nome = trim($_POST['nome'] ?? '');
  $email = trim($_POST['email'] ?? '');
  $telefone = trim($_POST['telefone'] ?? '');
  $obs = trim($_POST['observacao'] ?? '');

  $d = trim($_POST['data'] ?? '');
  $hslot = trim($_POST['hora'] ?? '');

  // validações
  if ($nome === '' || $email === '' || $telefone === '' || $d === '' || $hslot === '') {
    $erro = 'Preencha todos os campos obrigatórios.';
  } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $erro = 'E-mail inválido.';
  } elseif (!in_range_date($d)) {
    $erro = 'Data fora do período permitido.';
  } else {
    // valida slot existe
    $slotsPost = build_slots($d, $JANELAS_HORARIO, $DURACAO_MIN);
    if (!in_array($hslot, $slotsPost, true)) {
      $erro = 'Horário inválido.';
    }
  }

  // normaliza telefone (mantém formato para exibir, mas salva como veio)
  $telDigits = only_digits($telefone);
  if (empty($erro) && strlen($telDigits) < 10) {
    $erro = 'Telefone inválido.';
  }

  if (empty($erro)) {
    $reuniaoEm = $d . ' ' . $hslot . ':00';

    // checa se horário já foi ocupado (último check antes de inserir)
    $st = $pdo->prepare("
      SELECT COUNT(*) AS n
      FROM agenda_reunioes
      WHERE reuniao_em = :re
        AND status IN ('PENDENTE','CONFIRMADA')
      LIMIT 1
    ");
    $st->execute([':re' => $reuniaoEm]);
    $n = (int)$st->fetchColumn();

    if ($n > 0) {
      $erro = 'Esse horário acabou de ser reservado. Escolha outro.';
    } else {
      // procura usuario_id pelo email
      $usuarioId = null;
      $stU = $pdo->prepare("SELECT id FROM usuarios WHERE email = :e LIMIT 1");
      $stU->execute([':e' => $email]);
      $usuarioId = $stU->fetchColumn();
      if ($usuarioId !== false) $usuarioId = (int)$usuarioId;
      else $usuarioId = null;

      // insere agendamento
      $sql = "
        INSERT INTO agenda_reunioes
          (usuario_id, nome, email, telefone, criado_em, reuniao_em, duracao_min, status, observacao, ip, user_agent)
        VALUES
          (:usuario_id, :nome, :email, :telefone, NOW(), :reuniao_em, :duracao_min, 'CONFIRMADA', :obs, :ip, :ua)
      ";
      $stI = $pdo->prepare($sql);
      $ok = $stI->execute([
        ':usuario_id' => $usuarioId,
        ':nome' => $nome,
        ':email' => $email,
        ':telefone' => $telefone,
        ':reuniao_em' => $reuniaoEm,
        ':duracao_min' => $DURACAO_MIN,
        ':obs' => ($obs === '' ? null : $obs),
        ':ip' => $_SERVER['REMOTE_ADDR'] ?? '',
        ':ua' => substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 255),
      ]);

      if ($ok) {
        $sucesso = true;
        $agendamento = [
          'nome' => $nome,
          'email' => $email,
          'telefone' => $telefone,
          'data' => $d,
          'hora' => $hslot,
          'duracao' => $DURACAO_MIN,
        ];

        // envia email (sem confirmação)
        $assunto = "Agendamento confirmado";
        $corpo =
"Olá, {$nome}!

Seu horário foi agendado com sucesso.

Data: " . date('d/m/Y', strtotime($d)) . "
Horário: {$hslot}
Duração: {$DURACAO_MIN} minutos

📞 Para falar antes, ou se precisar ajustar, o telefone é: {$PHONE_FMT}

Mensagem automática do portal de acionistas.";

        $mailOk = mail_simple($email, $assunto, $corpo);

        $sucesso_msg = $mailOk
          ? 'Agendamento confirmado e e-mail enviado.'
          : 'Agendamento confirmado. (Não foi possível enviar o e-mail no momento.)';

        // recarrega ocupados/slots do dia selecionado
        $selectedDate = $d;
        $slots = build_slots($selectedDate, $JANELAS_HORARIO, $DURACAO_MIN);
        $ocupados[$hslot] = true;
      } else {
        $erro = 'Não foi possível salvar o agendamento. Tente novamente.';
      }
    }
  }
}

// lista de dias (hoje até hoje+DIAS_A_FRENTE)
$dias = [];
$i = 0;
while (count($dias) <= DIAS_A_FRENTE) {
  $dObj = (clone $hoje)->modify("+{$i} days");
  $dow = (int)$dObj->format('w'); // 0=dom, 6=sab
  if ($dow >= 1 && $dow <= 5) {   // seg..sex
    $dias[] = $dObj->format('Y-m-d');
  }
  $i++;
}


?>
<!doctype html>
<html lang="pt-br">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Agenda | Portal ANS</title>

  <link rel="stylesheet" href="/ans/css/header.css">
  <link rel="stylesheet" href="/ans/css/home.css">
  <link rel="stylesheet" href="/ans/css/footer.css">

  <style>
    .agenda-hero{
      min-height: 92vh;
      position: relative;
      overflow: hidden;
      background: #24343A;
    }
    .agenda-bg{
      position:absolute; inset:0;
      background-image: url("/ans/img/agenda.jpeg");
      background-size: cover;
      background-position: center;
      background-repeat:no-repeat;
      filter: grayscale(20%) brightness(0.72) contrast(0.92);
      transform: scale(1.02);
    }
    .agenda-overlay{
      position:absolute; inset:0;
      background: linear-gradient(
        rgba(43,59,65,0.58) 0%,
        rgba(43,59,65,0.42) 55%,
        rgba(43,59,65,0.62) 100%
      );
    }
    .agenda-wrap{
      position:relative;
      z-index:2;
      padding-top: 96px;
    }
    .agenda-container{
      max-width: 980px;
      margin: 0 auto;
      padding: 44px 16px 60px;
    }
    .agenda-card{
      background: rgba(244,245,247,0.96);
      border: 1px solid rgba(0,0,0,0.08);
      border-radius: 16px;
      padding: 18px;
    }
    .agenda-title{
      margin: 0 0 6px;
      color: #2B3B41;
      font-size: 28px;
      line-height: 1.15;
      letter-spacing: .2px;
    }
    .agenda-sub{
      margin: 0 0 14px;
      color: rgba(22,22,22,0.75);
      line-height: 1.55;
    }

    .agenda-grid{
      display:grid;
      grid-template-columns: 1fr 1fr;
      gap: 14px;
    }
    @media (max-width: 860px){
      .agenda-grid{ grid-template-columns: 1fr; }
    }

    .pill-row{
      display:flex;
      flex-wrap:wrap;
      gap:8px;
      margin: 8px 0 10px;
    }
    .pill{
      border: 1px solid rgba(0,0,0,0.10);
      background: transparent;
      color: #2B3B41;
      padding: 8px 10px;
      border-radius: 999px;
      font-weight: 600;
      font-size: 13px;
      text-decoration:none;
      cursor:pointer;
    }
    .pill.is-active{
      background: #2B3B41;
      color: #fff;
      border-color: #2B3B41;
    }

    .slots{
      display:flex;
      flex-wrap:wrap;
      gap:10px;
      margin-top: 8px;
    }
    .slot-btn{
      border: 1px solid rgba(0,0,0,0.10);
      background: #fff;
      color: #2B3B41;
      padding: 10px 12px;
      border-radius: 12px;
      font-weight: 700;
      cursor:pointer;
    }
    .slot-btn[disabled]{
      opacity: .45;
      cursor:not-allowed;
    }

    .form{
      display:grid;
      grid-template-columns: 1fr 1fr;
      gap: 10px;
      margin-top: 10px;
    }
    .form .full{ grid-column: 1 / -1; }
    .field label{
      display:block;
      font-size: 13px;
      font-weight: 700;
      color: rgba(22,22,22,0.82);
      margin: 0 0 6px;
    }
    .field input, .field textarea{
      width:100%;
      padding: 10px 12px;
      border-radius: 12px;
      border: 1px solid rgba(0,0,0,0.12);
      outline: none;
      background:#fff;
      color:#161616;
      font-size: 14px;
    }
    .field textarea{ min-height: 90px; resize: vertical; }

    .btn-row{
      display:flex;
      align-items:center;
      gap: 10px;
      margin-top: 10px;
      justify-content: flex-end;
    }
    .btn-flat{
      border: 1px solid rgba(0,0,0,0.12);
      background: #2B3B41;
      color:#fff;
      padding: 10px 14px;
      border-radius: 12px;
      font-weight: 800;
      cursor:pointer;
      text-decoration:none;
    }
    .btn-flat.secondary{
      background: transparent;
      color:#2B3B41;
    }

    .alert{
      margin: 10px 0 0;
      padding: 10px 12px;
      border-radius: 12px;
      font-weight: 700;
      border: 1px solid rgba(0,0,0,0.10);
    }
    .alert.err{
      background: rgba(255, 92, 92, 0.12);
      color: #7a1f1f;
    }
    .alert.ok{
      background: rgba(111, 132, 107, 0.16);
      color: #1f3a24;
    }

    .info-phone{
      margin-top: 10px;
      color: rgba(22,22,22,0.78);
      font-weight: 700;
    }
    .info-phone b{ color:#2B3B41; }
  </style>
</head>
<body>

<?php include __DIR__ . '/../include/header.php'; ?>

<section class="agenda-hero" aria-label="Agenda">
  <div class="agenda-bg" aria-hidden="true"></div>
  <div class="agenda-overlay" aria-hidden="true"></div>

  <div class="agenda-wrap">
    <div class="agenda-container">

      <div class="agenda-card">
        <h1 class="agenda-title">Agende uma reunião</h1>
        <p class="agenda-sub">
          Escolha um dia e um horário disponível.
        </p>

        <?php if ($erro): ?>
          <div class="alert err"><?= h($erro) ?></div>
        <?php elseif ($sucesso): ?>
          <div class="alert ok"><?= h($sucesso_msg) ?></div>
          <?php if ($agendamento): ?>
            <p class="info-phone">
              ✅ <b>Horário confirmado</b> —
              <?= date('d/m/Y', strtotime($agendamento['data'])) ?> às <?= h($agendamento['hora']) ?> (<?= (int)$agendamento['duracao'] ?> min).<br>
            </p>
          <?php endif; ?>
        <?php endif; ?>

        <div class="agenda-grid">

          <!-- COL 1: dias -->
          <div>
            <p style="margin:0 0 6px; font-weight:900; color:#2B3B41;">Selecione o dia</p>
            <div class="pill-row" role="list">
              <?php foreach ($dias as $d): 
                $isActive = ($d === $selectedDate);
                $label = date('d/m', strtotime($d));
                $dow = ['Dom','Seg','Ter','Qua','Qui','Sex','Sáb'][(int)date('w', strtotime($d))];
              ?>
                <a class="pill <?= $isActive ? 'is-active':'' ?>" href="/ans/pages/agenda.php?d=<?= h($d) ?>" role="listitem">
                  <?= h($dow) ?> <?= h($label) ?>
                </a>
              <?php endforeach; ?>
            </div>

            <p style="margin:10px 0 0; color:rgba(22,22,22,0.75); font-weight:700;">
              Agenda até <?= date('d/m/Y', strtotime(end($dias))) ?>.
            </p>
          </div>

          <!-- COL 2: horários + form -->
          <div>
            <p style="margin:0 0 6px; font-weight:900; color:#2B3B41;">Horários disponíveis (<?= (int)$DURACAO_MIN ?> min)</p>

            <div class="slots" aria-label="Horários">
              <?php if (empty($slots)): ?>
                <div class="alert err" style="margin:0;">Nenhum horário configurado.</div>
              <?php else: ?>
                <?php foreach ($slots as $hh): 
                  $isBusy = !empty($ocupados[$hh]);
                ?>
                  <button
                    class="slot-btn"
                    type="button"
                    data-slot="<?= h($hh) ?>"
                    <?= $isBusy ? 'disabled aria-disabled="true"' : '' ?>
                  >
                    <?= h($hh) ?>
                  </button>
                <?php endforeach; ?>
              <?php endif; ?>
            </div>

            <form method="post" id="agendaForm" style="display:none;">
              <input type="hidden" name="data" value="<?= h($selectedDate) ?>">
              <input type="hidden" name="hora" id="horaEscolhida" value="">

              <div class="form">
                <div class="field full">
                  <label>Horário escolhido</label>
                  <input type="text" id="horaVisual" value="" readonly>
                </div>

                <div class="field">
                  <label>Nome *</label>
                  <input type="text" name="nome" required maxlength="190" autocomplete="name">
                </div>

                <div class="field">
                  <label>E-mail *</label>
                  <input type="email" name="email" required maxlength="190" autocomplete="email">
                </div>

                <div class="field">
                  <label>Telefone *</label>
                  <input type="tel" name="telefone" id="telefone" required maxlength="20" autocomplete="tel" placeholder="(16) 99999-9999">
                </div>

                <div class="field full">
                  <label>Observação (opcional)</label>
                  <textarea name="observacao" maxlength="255" placeholder="Se quiser, deixe um contexto rápido."></textarea>
                </div>
              </div>

              <div class="btn-row">
                <button type="button" class="btn-flat secondary" id="cancelar">Cancelar</button>
                <button type="submit" class="btn-flat">Confirmar agendamento</button>
              </div>

            </form>

          </div>
        </div>

      </div>

    </div>
  </div>
</section>
<?php
require_once __DIR__ . '/../include/footer.php';
?>
