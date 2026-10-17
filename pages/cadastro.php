<?php
// cadastro.php (Portal ANS) — página pública
require_once __DIR__ . '/../include/init.php'; // ensure_session() + log_access()

$page_title = 'Cadastro | Portal ANS';

// -------------------------------
// Helpers
// -------------------------------
function ans_trim($v): string {
  return trim((string)$v);
}
function ans_only_digits($v): string {
  return preg_replace('/\D+/', '', (string)$v);
}
function ans_email_norm($v): string {
  return strtolower(trim((string)$v));
}
function ans_password_ok($pwd): array {
  $pwd = (string)$pwd;
  if (strlen($pwd) < 8 || strlen($pwd) > 12) {
    return [false, 'A senha deve ter entre 8 e 12 caracteres.'];
  }
  if (!preg_match('/[A-Z]/', $pwd)) {
    return [false, 'A senha deve ter pelo menos 1 letra maiúscula.'];
  }
  preg_match_all('/\d/', $pwd, $m);
  $digits = isset($m[0]) ? count($m[0]) : 0;
  if ($digits < 4) {
    return [false, 'A senha deve ter pelo menos 4 números.'];
  }
  return [true, ''];
}

// tenta obter um PDO se houver configuração
function ans_try_pdo() {
  // 1) se o projeto já expõe $pdo
  if (isset($GLOBALS['pdo']) && $GLOBALS['pdo'] instanceof PDO) return $GLOBALS['pdo'];

  // 2) se existe função helper no config (não assumimos o nome)
  foreach (['get_pdo', 'db', 'pdo'] as $fn) {
    if (function_exists($fn)) {
      try {
        $p = $fn();
        if ($p instanceof PDO) return $p;
      } catch (Throwable $e) { /* ignora */ }
    }
  }

  // 3) se existem constantes (não garantido)
  if (defined('DB_HOST') && defined('DB_NAME') && defined('DB_USER') && defined('DB_PASS')) {
    try {
      $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4';
      return new PDO($dsn, DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
      ]);
    } catch (Throwable $e) {
      return null;
    }
  }
  return null;
}

// -------------------------------
// Estado
// -------------------------------
$DEV_MODE = defined('DEV_MODE') ? (bool)DEV_MODE : true;

$errors = [];
$success = '';
$old = [
  'nome_completo' => '',
  'email' => '',
  'telefone' => '',
  'cpf' => '',
  'possui_cin' => '',
  'rg' => '',
  'cep' => '',
  'rua' => '',
  'numero' => '',
  'complemento' => '',
  'bairro' => '',
  'cidade' => '',
  'uf' => '',
  'possui_procurador' => '',
  'sou_procurador' => '',
  'representado_nome' => '',
  'representado_email' => '',
  'aceite_indep' => '',
];

// -------------------------------
// POST
// -------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  // carrega antigos
  foreach ($old as $k => $_) {
    $old[$k] = isset($_POST[$k]) ? ans_trim($_POST[$k]) : '';
  }

  $nome = ans_trim($_POST['nome_completo'] ?? '');
  $email = ans_email_norm($_POST['email'] ?? '');
  $telefone = ans_trim($_POST['telefone'] ?? '');

  $cpf_digits = ans_only_digits($_POST['cpf'] ?? '');
  $possui_cin = !empty($_POST['possui_cin']) ? 1 : 0;

  // RG: se possui CIN, RG = CPF (como você pediu)
  $rg_raw = ans_only_digits($_POST['rg'] ?? '');
  $rg_digits = $possui_cin ? $cpf_digits : $rg_raw;

  $cep_digits = ans_only_digits($_POST['cep'] ?? '');
  $rua = ans_trim($_POST['rua'] ?? '');
  $numero = ans_trim($_POST['numero'] ?? '');
  $complemento = ans_trim($_POST['complemento'] ?? '');
  $bairro = ans_trim($_POST['bairro'] ?? '');
  $cidade = ans_trim($_POST['cidade'] ?? '');
  $uf = strtoupper(ans_trim($_POST['uf'] ?? ''));

  $possui_procurador = !empty($_POST['possui_procurador']) ? 1 : 0;
  $sou_procurador = !empty($_POST['sou_procurador']) ? 1 : 0;

  $representado_nome = ans_trim($_POST['representado_nome'] ?? '');
  $representado_email = ans_email_norm($_POST['representado_email'] ?? '');

  $senha = (string)($_POST['senha'] ?? '');
  $senha2 = (string)($_POST['senha_confirmar'] ?? '');
  $aceite = !empty($_POST['aceite_indep']) ? 1 : 0;

  // validações básicas (mensagens claras)
  if ($nome === '' || mb_strlen($nome) < 5) $errors[] = 'Informe seu nome completo.';
  if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Informe um e-mail válido.';
  if (!$aceite) $errors[] = 'É necessário confirmar a ciência do caráter independente do portal.';
  if ($telefone === '' || strlen(ans_only_digits($telefone)) < 10) $errors[] = 'Informe um telefone válido com DDD.';
  if (strlen($cpf_digits) !== 11) $errors[] = 'Informe um CPF válido.';
  if (!$possui_cin && $rg_digits === '') $errors[] = 'Informe seu RG (ou marque que possui CIN).';
  if ($cep_digits !== '' && strlen($cep_digits) !== 8) $errors[] = 'O CEP deve ter 8 números.';
  if ($rua === '' || $numero === '' || $bairro === '' || $cidade === '' || $uf === '') $errors[] = 'Preencha seu endereço completo (rua, número, bairro, cidade e UF).';

  if ($senha === '' || $senha2 === '') $errors[] = 'Informe a senha e a confirmação.';
  if ($senha !== '' && $senha2 !== '' && $senha !== $senha2) $errors[] = 'A confirmação de senha não confere.';
  if ($senha !== '') {
    [$okPwd, $msgPwd] = ans_password_ok($senha);
    if (!$okPwd) $errors[] = $msgPwd;
  }

  if ($sou_procurador) {
    if ($representado_nome === '' || mb_strlen($representado_nome) < 5) $errors[] = 'Informe o nome de quem você está representando.';
    if ($representado_email === '' || !filter_var($representado_email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Informe o e-mail de quem você está representando.';
  }

  // se ok, processa
  if (empty($errors)) {
    $senha_hash = password_hash($senha, PASSWORD_DEFAULT);

    // termos
    $termos_versao = defined('TERMO_VERSAO') ? (string) constant('TERMO_VERSAO') : date('Y-m-d');
    $agora = date('Y-m-d H:i:s');

    if ($DEV_MODE) {
      // simulação local (sem banco)
      $_SESSION['ans_cadastro_demo'] = [
        'nome' => $nome,
        'email' => $email,
        'telefone' => $telefone,
        'cpf' => $cpf_digits,
        'possui_cin' => $possui_cin,
        'rg' => $rg_digits,
        'endereco' => [
          'cep' => $cep_digits,
          'rua' => $rua,
          'numero' => $numero,
          'complemento' => $complemento,
          'bairro' => $bairro,
          'cidade' => $cidade,
          'uf' => $uf,
        ],
        'possui_procurador' => $possui_procurador,
        'sou_procurador' => $sou_procurador,
        'representado' => [
          'nome' => $representado_nome,
          'email' => $representado_email,
        ],
        'termos_versao' => $termos_versao,
        'termos_aceitos_at' => $agora,
      ];
      $success = 'Cadastro recebido com sucesso (modo desenvolvimento). Você já pode acessar o portal quando o login estiver habilitado.';
      // limpa campos
      foreach ($old as $k => $_) $old[$k] = '';
    } else {
      $pdo = ans_try_pdo();
      if (!$pdo) {
        $errors[] = 'No momento, não foi possível concluir o cadastro. Tente novamente mais tarde.';
      } else {
        try {
          $pdo->beginTransaction();

          // 1) cria usuario
          $sqlU = "INSERT INTO usuarios (email, senha_hash, status, termos_versao, termos_aceitos_at, created_at, updated_at)
                   VALUES (:email, :senha_hash, :status, :termos_versao, :termos_aceitos_at, :created_at, :updated_at)";
          $stU = $pdo->prepare($sqlU);
          $stU->execute([
            ':email' => $email,
            ':senha_hash' => $senha_hash,
            ':status' => 'active',
            ':termos_versao' => $termos_versao,
            ':termos_aceitos_at' => $agora,
            ':created_at' => $agora,
            ':updated_at' => $agora,
          ]);
          $usuarioId = (int)$pdo->lastInsertId();

          // 2) cadastro
          $tipo_perfil = $sou_procurador ? ($possui_procurador ? 'AMBOS' : 'PROCURADOR') : ($possui_procurador ? 'ACIONISTA' : 'ACIONISTA');
          $sqlC = "INSERT INTO cadastro
                    (usuario_id, nome_completo, email, cpf, telefone, possui_cin, rg, tipo_perfil, possui_procurador,
                     cep, rua, numero, complemento, bairro, cidade, uf, created_at, updated_at)
                   VALUES
                    (:usuario_id, :nome, :email, :cpf, :telefone, :possui_cin, :rg, :tipo_perfil, :possui_procurador,
                     :cep, :rua, :numero, :complemento, :bairro, :cidade, :uf, :created_at, :updated_at)";
          $stC = $pdo->prepare($sqlC);
          $stC->execute([
            ':usuario_id' => $usuarioId,
            ':nome' => $nome,
            ':email' => $email,
            ':cpf' => $cpf_digits,
            ':telefone' => $telefone,
            ':possui_cin' => $possui_cin,
            ':rg' => $rg_digits,
            ':tipo_perfil' => $tipo_perfil,
            ':possui_procurador' => $possui_procurador,
            ':cep' => $cep_digits,
            ':rua' => $rua,
            ':numero' => $numero,
            ':complemento' => $complemento,
            ':bairro' => $bairro,
            ':cidade' => $cidade,
            ':uf' => $uf,
            ':created_at' => $agora,
            ':updated_at' => $agora,
          ]);

          // 3) vínculo (se procurador)
          if ($sou_procurador) {
            $idRepresentado = null;

            // tenta achar representado pelo email
            $stFind = $pdo->prepare("SELECT id FROM usuarios WHERE email = :email LIMIT 1");
            $stFind->execute([':email' => $representado_email]);
            $row = $stFind->fetch();
            if ($row && isset($row['id'])) $idRepresentado = (int)$row['id'];

            // se existir tabela e id encontrado, registra vínculo
            if ($idRepresentado) {
              $sqlV = "INSERT INTO usuario_vinculos
                        (id_procurador, id_representado, tipo_procuracao, status, created_at)
                       VALUES
                        (:id_procurador, :id_representado, :tipo_procuracao, :status, :created_at)";
              $stV = $pdo->prepare($sqlV);
              $stV->execute([
                ':id_procurador' => $usuarioId,
                ':id_representado' => $idRepresentado,
                ':tipo_procuracao' => 'REPRESENTACAO_GERAL',
                ':status' => 'ativo',
                ':created_at' => $agora,
              ]);
            }
          }

          $pdo->commit();
          $success = 'Cadastro realizado com sucesso. Você já pode acessar o portal com seu e-mail e senha.';
          foreach ($old as $k => $_) $old[$k] = '';
        } catch (Throwable $e) {
          if ($pdo && $pdo->inTransaction()) $pdo->rollBack();

          // erro comum: email duplicado
          $msg = strtolower($e->getMessage());
          if (strpos($msg, 'duplicate') !== false || strpos($msg, 'unique') !== false) {
            $errors[] = 'Este e-mail já possui cadastro no portal. Tente fazer login ou redefinir sua senha.';
          } else {
            $errors[] = 'No momento, não foi possível concluir o cadastro. Tente novamente mais tarde.';
          }
        }
      }
    }
  }
}
?>
<!doctype html>
<html lang="pt-br">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <title><?= isset($page_title) ? htmlspecialchars($page_title) : 'Cadastro | Portal ANS' ?></title>

  <link rel="icon" href="/img/logo-ans-header.svg" type="image/svg+xml">

  <!-- CSS padrão do portal -->
  <link rel="stylesheet" href="/ans/css/header.css">
  <link rel="stylesheet" href="/ans/css/footer.css">
  <link rel="stylesheet" href="/ans/css/cadastro.css">
</head>
<body>
<?php include __DIR__ . '/../include/header.php'; ?>
<main class="cadastro-wrap">
  <section class="cadastro-hero">
    <h1>Cadastro no Portal ANS</h1>
    <p class="cadastro-sub">
      Este portal é uma iniciativa independente, voltada a acionistas. O cadastro não cria vínculo com a Nova Smar e não envolve intermediação, recomendação ou negociação de ações.
    </p>
  </section>

  <section class="cadastro-container">

    <?php if (!empty($success)): ?>
      <div class="ans-alert ans-alert--ok" role="status">
        <?= htmlspecialchars($success) ?>
      </div>
    <?php endif; ?>

    <?php if (!empty($errors)): ?>
      <div class="ans-alert ans-alert--err" role="alert">
        <strong>Não foi possível concluir:</strong>
        <ul>
          <?php foreach ($errors as $e): ?>
            <li><?= htmlspecialchars($e) ?></li>
          <?php endforeach; ?>
        </ul>
      </div>
    <?php endif; ?>

    <form id="cadastroForm" class="cadastro-form" method="post" action="">
      <!-- CARD: Dados pessoais -->
      <div class="ans-card">
        <div class="ans-card__header">
          <h2>Dados pessoais</h2>
        </div>

        <div class="ans-grid">
          <div class="ans-field ans-col-12">
            <label for="nome_completo">Nome completo</label>
            <input type="text" id="nome_completo" name="nome_completo" autocomplete="name" required
                   value="<?= htmlspecialchars($old['nome_completo']) ?>">
          </div>

          <div class="ans-field ans-col-6">
            <label for="email">E-mail</label>
            <input type="email" id="email" name="email" autocomplete="email" required
                   value="<?= htmlspecialchars($old['email']) ?>">
          </div>

          <div class="ans-field ans-col-6">
            <label for="telefone">Telefone (DDD)</label>
            <input type="text" id="telefone" name="telefone" autocomplete="tel" required placeholder="(11) 99999-9999"
                   value="<?= htmlspecialchars($old['telefone']) ?>">
          </div>
        </div>
      </div>

      <!-- CARD: Documentos -->
      <div class="ans-card">
        <div class="ans-card__header">
          <h2>Documentos</h2>
        </div>

        <div class="ans-grid">
          <div class="ans-field ans-col-6">
            <label for="cpf">CPF</label>
            <input type="text" id="cpf" name="cpf" required placeholder="000.000.000-00"
                   value="<?= htmlspecialchars($old['cpf']) ?>">
          </div>

          <div class="ans-field ans-col-6 ans-field--check">
            <label class="ans-check">
              <input type="checkbox" id="possui_cin" name="possui_cin" value="1" <?= !empty($old['possui_cin']) ? 'checked' : '' ?>>
              <span>Possuo CIN (novo RG). Neste caso, o RG repete o número do CPF.</span>
            </label>
          </div>

          <div class="ans-field ans-col-6" id="rgWrap">
            <label for="rg">RG</label>
            <input type="text" id="rg" name="rg" placeholder="00.000.000-0"
                   value="<?= htmlspecialchars($old['rg']) ?>">
            <small class="ans-hint">Formato automático com pontos e dígito final. Ex.: 24.111.452-6</small>
          </div>

          <input type="hidden" id="rg_hidden" name="rg_hidden" value="">
        </div>
      </div>

      <!-- CARD: Endereço -->
      <div class="ans-card">
        <div class="ans-card__header">
          <h2>Endereço</h2>
        </div>

        <div class="ans-grid">
          <div class="ans-field ans-col-4">
            <label for="cep">CEP</label>
            <input type="text" id="cep" name="cep" required placeholder="00000-000"
                   value="<?= htmlspecialchars($old['cep']) ?>">
            <small class="ans-hint" id="cepStatus"></small>
          </div>

          <div class="ans-field ans-col-8">
            <label for="rua">Rua</label>
            <input type="text" id="rua" name="rua" required
                   value="<?= htmlspecialchars($old['rua']) ?>">
          </div>

          <div class="ans-field ans-col-3">
            <label for="numero">Número</label>
            <input type="text" id="numero" name="numero" required
                   value="<?= htmlspecialchars($old['numero']) ?>">
          </div>

          <div class="ans-field ans-col-5">
            <label for="complemento">Complemento (opcional)</label>
            <input type="text" id="complemento" name="complemento"
                   value="<?= htmlspecialchars($old['complemento']) ?>">
          </div>

          <div class="ans-field ans-col-4">
            <label for="bairro">Bairro</label>
            <input type="text" id="bairro" name="bairro" required
                   value="<?= htmlspecialchars($old['bairro']) ?>">
          </div>

          <div class="ans-field ans-col-6">
            <label for="cidade">Cidade</label>
            <input type="text" id="cidade" name="cidade" required
                   value="<?= htmlspecialchars($old['cidade']) ?>">
          </div>

          <div class="ans-field ans-col-2">
            <label for="uf">UF</label>
            <input type="text" id="uf" name="uf" required maxlength="2" placeholder="SP"
                   value="<?= htmlspecialchars($old['uf']) ?>">
          </div>
        </div>
      </div>

      <!-- CARD: Procurador / Procurado -->
      <div class="ans-card">
        <div class="ans-card__header ans-card__header--row">
          <div>
            <h2>Representação (procuração)</h2>
            <p>Informe se você atua por meio de procurador ou se está se cadastrando como procurador.</p>
          </div>

          <button type="button" class="ans-help" id="helpProcuracaoBtn" aria-label="O que significa?">
            ?
          </button>
        </div>

        <div class="ans-grid">
          <div class="ans-field ans-col-6 ans-field--check">
            <label class="ans-check">
              <input type="checkbox" id="possui_procurador" name="possui_procurador" value="1" <?= !empty($old['possui_procurador']) ? 'checked' : '' ?>>
              <span>Possuo procurador</span>
            </label>
          </div>

          <div class="ans-field ans-col-6 ans-field--check">
            <label class="ans-check">
              <input type="checkbox" id="sou_procurador" name="sou_procurador" value="1" <?= !empty($old['sou_procurador']) ? 'checked' : '' ?>>
              <span>Estou me cadastrando como procurador</span>
            </label>
          </div>

          <div id="representadoBlock" class="ans-col-12" style="display:none;">
            <div class="ans-subcard">
              <div class="ans-subcard__title">Dados de quem você representa</div>
              <div class="ans-grid">
                <div class="ans-field ans-col-8">
                  <label for="representado_nome">Nome do representado</label>
                  <input type="text" id="representado_nome" name="representado_nome"
                         value="<?= htmlspecialchars($old['representado_nome']) ?>">
                </div>
                <div class="ans-field ans-col-4">
                  <label for="representado_email">E-mail do representado</label>
                  <input type="email" id="representado_email" name="representado_email"
                         value="<?= htmlspecialchars($old['representado_email']) ?>">
                </div>
              </div>
              <small class="ans-hint">
                Se o representado já tiver cadastro no portal com este e-mail, o sistema poderá vincular automaticamente.
              </small>
            </div>
          </div>
        </div>
      </div>

      <!-- CARD: Senha + termos -->
      <div class="ans-card">
        <div class="ans-card__header">
          <h2>Senha e ciência</h2>
          <p>Regras: 8–12 caracteres, pelo menos 1 letra maiúscula e pelo menos 4 números.</p>
        </div>

        <div class="ans-grid">
          <div class="ans-field ans-col-6">
            <label for="senha">Senha</label>
            <input type="password" id="senha" name="senha" required autocomplete="new-password">
          </div>

          <div class="ans-field ans-col-6">
            <label for="senha_confirmar">Confirmar senha</label>
            <input type="password" id="senha_confirmar" name="senha_confirmar" required autocomplete="new-password">
          </div>

          <div class="ans-field ans-col-12 ans-field--check">
            <label class="ans-check">
              <input type="checkbox" id="aceite_indep" name="aceite_indep" value="1" required <?= !empty($old['aceite_indep']) ? 'checked' : '' ?>>
              <span>
                Declaro ciência de que este portal é uma iniciativa independente, sem vínculo institucional com a Nova Smar, e que não realiza intermediação, recomendação ou negociação de ações.
              </span>
            </label>
          </div>
        </div>

        <div class="ans-actions">
          <button class="ans-btn" type="submit">Finalizar cadastro</button>
          <a class="ans-link" href="/login.php">Já tenho cadastro</a>
        </div>
      </div>

      <div class="cadastro-legal">
        <p>
          <strong>Importante:</strong> O Portal ANS é uma iniciativa independente, voltada à comunicação e organização de acionistas, sem qualquer vínculo institucional com a empresa.
          O cadastro não cria relação com a Nova Smar e não implica oferta, recomendação, intermediação ou negociação de valores mobiliários.<br>
          Os dados informados neste cadastro são tratados nos termos da Lei nº 13.709/2018 (LGPD) e utilizados exclusivamente para fins de identificação, autenticação, organização de informações e registro de interações no Portal ANS.
          O portal não compartilha dados com terceiros nem os utiliza para fins comerciais ou promocionais.
        </p>
      </div>
    </form>
  </section>
</main>

<!-- Modal help -->
<div class="ans-modal" id="helpModal" aria-hidden="true">
  <div class="ans-modal__backdrop" data-close="1"></div>
  <div class="ans-modal__panel" role="dialog" aria-modal="true" aria-labelledby="helpTitle">
    <div class="ans-modal__header">
      <div>
        <div id="helpTitle" class="ans-modal__title">O que significa “representação (procuração)”? </div>
        <div class="ans-modal__sub">Entenda como isso aparece no portal.</div>
      </div>
      <button type="button" class="ans-modal__close" data-close="1" aria-label="Fechar">×</button>
    </div>
    <div class="ans-modal__body">
      <p>
        “Procuração” é quando uma pessoa (procurador) atua em nome de outra (representado) em situações específicas,
        como reuniões, assembleias e formalidades. No portal, essa informação serve apenas para organizar o cadastro
        e os registros de comunicação, mantendo clareza e boa governança entre os acionistas.
      </p>
      <p class="ans-modal__note">
        O portal não valida documentos nesta etapa e não intermedeia decisões ou negociações.
      </p>
    </div>
    <div class="ans-modal__footer">
      <button type="button" class="ans-btn ans-btn--ghost" data-close="1">Entendi</button>
    </div>
  </div>
</div>
<?php require_once __DIR__ . '/login_modal.php'; ?>

<?php include __DIR__ . '/../include/footer.php'; ?>

<script src="/ans/js/header.js"></script>
<script src="/ans/js/cadastro.js"></script>

</body>
</html>



