<?php
/* expects: $pageTitle, optional $base (path to project root), $activeCat, $noChrome */
$base    = $base ?? '';
$user    = current_user();
$lines   = cart_lines();
$cartN   = cart_count($lines);
$openCart = url_with(current_url(), 'cart', 1);
$flash   = take_flash();
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= e($pageTitle ?? 'SHOP & TUFF') ?> — SHOP &amp; TUFF</title>
  <link rel="icon" type="image/svg+xml" href="<?= $base ?>assets/img/favicon.svg">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Barlow:wght@400;500;600&family=Barlow+Condensed:wght@500;600;700&display=swap">
  <link rel="stylesheet" href="<?= $base ?>assets/css/app.css">
</head>
<body class="shop">
<div class="topbar">Flat $<?= SHIPPING_FLAT ?> shipping on every order</div>
<header class="nav">
  <div class="wrap nav-in">
    <a class="brand" href="<?= $base ?>index.php">SHOP<span>&amp;</span>TUFF</a>
    <form class="search" method="get" action="<?= $base ?>index.php" role="search">
      <label class="sr-only" for="q">Search products</label>
      <input id="q" type="search" name="q" placeholder="Search jeans, tees, shoes" value="<?= e($_GET['q'] ?? '') ?>">
    </form>
    <div class="nav-actions">
      <?php if ($user): ?>
        <details class="menu">
          <summary class="nav-link"><?= e(explode(' ', trim($user['name']))[0] ?: 'Account') ?> ▾</summary>
          <div class="menu-pop">
            <a href="<?= $base ?>orders.php">My orders</a>
            <a href="<?= $base ?>settings.php">Settings</a>
            <?php if ($user['role'] === 'admin'): ?><a href="<?= $base ?>admin/index.php">Admin panel</a><?php endif; ?>
            <hr>
            <form method="post" action="<?= $base ?>logout.php"><?= csrf_field() ?><button type="submit">Log out</button></form>
          </div>
        </details>
      <?php else: ?>
        <a class="nav-link" href="<?= $base ?>login.php">Log in</a>
      <?php endif; ?>
      <a class="cart-btn" href="<?= e($openCart) ?>">Cart <?php if ($cartN): ?><span class="count"><?= $cartN ?></span><?php endif; ?></a>
    </div>
  </div>
  <?php $cats = categories(); ?>
  <nav class="cats" aria-label="Categories">
    <div class="cats-in">
      <a href="<?= $base ?>index.php" class="<?= ($activeCat ?? '') === '' ? 'on' : '' ?>">All</a>
      <?php foreach ($cats as $c): ?>
        <a href="<?= $base ?>index.php?cat=<?= urlencode($c) ?>" class="<?= ($activeCat ?? '') === $c ? 'on' : '' ?>"><?= e($c) ?></a>
      <?php endforeach; ?>
    </div>
  </nav>
</header>
<main>
<?php if ($flash): ?><div class="wrap" style="padding-top:16px"><div class="alert <?= e($flash[1]) ?>" role="status"><?= e($flash[0]) ?></div></div><?php endif; ?>
