<?php
require __DIR__ . '/includes/functions.php';

$activeCat = $_GET['cat'] ?? '';
$query     = trim($_GET['q'] ?? '');
$sort      = $_GET['sort'] ?? 'new';
$products  = fetch_products($activeCat, $query, $sort);
$filtered  = $activeCat !== '' || $query !== '';

$pageTitle = $activeCat ?: ($query ? 'Search' : 'Men’s jeans, tees and shoes');
require __DIR__ . '/includes/header.php';
$hero = array_slice(fetch_products('', '', 'new'), 0, 3);
?>
<?php if (!$filtered): ?>
<section class="hero">
  <div class="wrap hero-in">
    <div>
      <h1>The power of fit.</h1>
      <p>Baggy jeans, clean tees and shoes that hold up. Everything here is picked for how it wears, not just how it looks.</p>
      <div class="hero-cta">
        <a class="btn btn-accent" href="#products">Shop all products</a>
        <?php if (!current_user()): ?><a class="btn btn-ghost" href="register.php">Create an account</a><?php endif; ?>
      </div>
    </div>
    <div class="hero-art" aria-hidden="true">
      <?php foreach ($hero as $h): ?>
        <div class="tile"><img src="<?= e(img_src($h['image'])) ?>" alt=""></div>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<div class="wrap perks">
  <div><strong>Flat $<?= SHIPPING_FLAT ?> shipping</strong><span>One price, whatever is in your cart.</span></div>
  <div><strong>Safer checkout</strong><span>Card details are checked but never saved.</span></div>
  <div><strong>Track your orders</strong><span>See every order and its status in your account.</span></div>
</div>
<?php endif; ?>

<section class="section" id="products">
  <div class="wrap">
    <div class="toolbar">
      <div>
        <h2><?= $query ? 'Results for “' . e($query) . '”' : e($activeCat ?: 'All products') ?></h2>
        <span class="muted"><?= count($products) ?> <?= count($products) === 1 ? 'product' : 'products' ?></span>
      </div>
      <form method="get" action="index.php">
        <?php if ($activeCat !== ''): ?><input type="hidden" name="cat" value="<?= e($activeCat) ?>"><?php endif; ?>
        <?php if ($query !== ''): ?><input type="hidden" name="q" value="<?= e($query) ?>"><?php endif; ?>
        <label for="sort" class="muted small">Sort by</label>
        <select id="sort" name="sort" onchange="this.form.submit()">
          <?php foreach (['new' => 'Newest', 'low' => 'Price: low to high', 'high' => 'Price: high to low', 'name' => 'Name'] as $k => $l): ?>
            <option value="<?= $k ?>" <?= $sort === $k ? 'selected' : '' ?>><?= $l ?></option>
          <?php endforeach; ?>
        </select>
        <noscript><button class="btn btn-sm">Apply</button></noscript>
      </form>
    </div>

    <?php if (!$products): ?>
      <div class="empty">
        <h3>No products found</h3>
        <p class="muted">Try a different word, or browse everything.</p>
        <a class="btn" href="index.php">Show all products</a>
      </div>
    <?php else: ?>
      <div class="grid">
        <?php foreach ($products as $p) require __DIR__ . '/includes/product_card.php'; ?>
      </div>
    <?php endif; ?>
  </div>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
