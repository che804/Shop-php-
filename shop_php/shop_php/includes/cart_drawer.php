<?php
$open   = isset($_GET['cart']);
$back   = url_with(current_url(), 'cart', null);
$total  = cart_total($lines);
$hidden = fn($id) => csrf_field() . '<input type="hidden" name="id" value="' . $id . '"><input type="hidden" name="back" value="' . e($back) . '">';
?>
<?php if ($open): ?><a class="backdrop" href="<?= e($back) ?>" aria-label="Close cart"></a><?php endif; ?>
<aside class="drawer <?= $open ? 'open' : '' ?>" aria-label="Shopping cart" <?= $open ? '' : 'inert' ?>>
  <div class="drawer-head">
    <h2>Your cart (<?= $cartN ?>)</h2>
    <a class="x" href="<?= e($back) ?>" aria-label="Close cart">×</a>
  </div>
  <div class="drawer-items">
    <?php if (!$lines): ?>
      <p class="muted" style="padding:16px 0">Your cart is empty. Pick something you like and it will show up here.</p>
    <?php endif; ?>
    <?php foreach ($lines as $it): ?>
      <div class="line">
        <img src="<?= e(img_src($it['image'], $base)) ?>" alt="">
        <div class="line-info">
          <a class="line-name" href="<?= $base ?>product.php?id=<?= $it['id'] ?>" style="color:inherit;text-decoration:none"><?= e($it['name']) ?></a>
          <span class="muted small"><?= money($it['price']) ?> each</span>
          <div class="line-actions">
            <div class="qty">
              <form method="post" action="<?= $base ?>cart_action.php" class="inline"><input type="hidden" name="action" value="dec"><?= $hidden($it['id']) ?><button aria-label="Decrease quantity">−</button></form>
              <span><?= $it['qty'] ?></span>
              <form method="post" action="<?= $base ?>cart_action.php" class="inline"><input type="hidden" name="action" value="inc"><?= $hidden($it['id']) ?><button aria-label="Increase quantity">+</button></form>
            </div>
            <form method="post" action="<?= $base ?>cart_action.php" class="inline"><input type="hidden" name="action" value="remove"><?= $hidden($it['id']) ?><button class="link-btn danger small">Remove</button></form>
          </div>
        </div>
        <strong><?= money($it['price'] * $it['qty']) ?></strong>
      </div>
    <?php endforeach; ?>
  </div>
  <?php if ($lines): ?>
    <div class="drawer-foot">
      <div class="row-between" style="margin-bottom:4px"><span>Subtotal</span><strong><?= money($total) ?></strong></div>
      <p class="muted small">Shipping is added at checkout.</p>
      <a class="btn btn-accent btn-block" href="<?= $base ?>checkout.php">Go to checkout</a>
    </div>
  <?php endif; ?>
</aside>
