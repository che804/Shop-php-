<?php
/* expects: $pageTitle, $adminNav (dashboard|products|orders|users) ; must be included after require_admin() */
$base  = '../';
$me    = current_user();
$flash = take_flash();
$nav = ['dashboard' => ['index.php', 'Dashboard'], 'products' => ['products.php', 'Products'],
        'orders' => ['orders.php', 'Orders'], 'users' => ['users.php', 'Customers']];
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= e($pageTitle ?? 'Admin') ?> — Admin</title>
  <link rel="icon" type="image/svg+xml" href="../assets/img/favicon.svg">
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Barlow:wght@400;500;600&family=Barlow+Condensed:wght@500;600;700&display=swap">
  <link rel="stylesheet" href="../assets/css/app.css">
</head>
<body>
<div class="admin">
  <aside class="side">
    <a class="brand" href="index.php" style="display:inline-flex;align-items:center"><img src="../assets/img/logo.svg" alt="SHOP &amp; TUFF" style="height:34px;width:auto"></a>
    <nav aria-label="Admin">
      <?php foreach ($nav as $k => [$href, $label]): ?>
        <a href="<?= $href ?>" class="<?= ($adminNav ?? '') === $k ? 'on' : '' ?>"><?= $label ?></a>
      <?php endforeach; ?>
    </nav>
    <div class="foot">
      <div style="margin-bottom:6px"><?= e($me['name']) ?></div>
      <a href="../index.php">View shop</a>
      <form method="post" action="../logout.php" style="margin:0"><?= csrf_field() ?><button class="link-btn" style="color:#c9d3e4">Log out</button></form>
    </div>
  </aside>
  <div class="main">
    <?php if ($flash): ?><div class="alert <?= e($flash[1]) ?>" role="status"><?= e($flash[0]) ?></div><?php endif; ?>
