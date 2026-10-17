<?php
if (!isset($page_title)) {
  $page_title = 'Acionistas Nova Smar';
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="utf-8">
  <title><?= htmlspecialchars($page_title) ?></title>
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <!-- CSS base (sempre) -->
  <link rel="stylesheet" href="/ans/css/header.css">
  <link rel="stylesheet" href="/ans/css/home.css">
  <link rel="stylesheet" href="/ans/css/footer.css">
  <link rel="stylesheet" href="/ans/css/login_modal.css">

  <!-- CSS da página (opcional) -->
  <?php if (!empty($page_css)): ?>
    <link rel="stylesheet" href="/ans/<?= ltrim($page_css, '/') ?>">
  <?php endif; ?>

  <!-- JS da página (opcional) -->
  <?php
    if (!empty($page_js)) {
      $scripts = is_array($page_js) ? $page_js : [$page_js];
      foreach ($scripts as $js) {
        $js = trim($js);
        if ($js !== '') {
          echo '<script src="' . htmlspecialchars($js) . '" defer></script>' . PHP_EOL;
        }
      }
    }
  ?>

  <!-- JS global -->
  <script src="/ans/js/login_modal.js" defer></script>
</head>
<body>
