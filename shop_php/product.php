<?php
require __DIR__ . '/includes/functions.php';
$product = find_product($_GET['id'] ?? 0);
if (!$product) { http_response_code(404); }

$pageTitle  = $product['name'] ?? 'Product not found';
$activeCat  = $product['category'] ?? '';
require __DIR__ . '/includes/header.php';

if (!$product): ?>
  <div class="wrap page"><div class="empty"><h2>We couldn’t find that product</h2>
    <p class="muted">It may have been removed.</p><a class="btn" href="index.php">Back to shop</a></div></div>
<?php else:
  $related = array_slice(array_filter(fetch_products($product['category']), fn($r) => $r['id'] !== $product['id']), 0, 4);
?>
<div class="wrap">
  <div class="crumbs"><a href="index.php">Shop</a> / <a href="index.php?cat=<?= urlencode($product['category']) ?>"><?= e($product['category']) ?></a> / <?= e($product['name']) ?></div>
  <div class="pdp">
    <div class="pdp-img"><img src="<?= e(img_src($product['image'])) ?>" alt="<?= e($product['name']) ?>"></div>
    <div>
      <span class="muted"><?= e($product['category']) ?></span>
      <h1 style="font-size:clamp(2.4rem,5vw,3.6rem)"><?= e($product['name']) ?></h1>
      <div class="price"><?= money($product['price']) ?></div>
      <p style="margin-top:1rem;max-width:32em"><?= e($product['description']) ?></p>
      <form method="post" action="cart_action.php" class="pdp-buy">
        <?= csrf_field() ?>
        <input type="hidden" name="action" value="add">
        <input type="hidden" name="id" value="<?= $product['id'] ?>">
        <input type="hidden" name="back" value="<?= e(current_url()) ?>">
        <label class="sr-only" for="qty">Quantity</label>
        <input id="qty" type="number" name="qty" value="1" min="1" max="20">
        <button type="submit" class="btn btn-accent" style="flex:1">Add to cart</button>
      </form>
      <ul class="plain facts">
        <li>Flat $<?= SHIPPING_FLAT ?> shipping on every order</li>
        <li>Secure checkout, card details are never stored</li>
        <li>Track the order from your account</li>
      </ul>
    </div>
  </div>

  <?php if ($related): ?>
    <section class="section" style="padding-top:8px">
      <div class="toolbar"><h2>More in <?= e($product['category']) ?></h2></div>
      <div class="grid">
        <?php foreach ($related as $p) require __DIR__ . '/includes/product_card.php'; ?>
      </div>
    </section>
  <?php endif; ?>
</div>
<?php endif;
require __DIR__ . '/includes/footer.php';
