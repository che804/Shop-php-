</main>
<?php
$base = $base ?? '';
if (empty($noDrawer)) require __DIR__ . '/cart_drawer.php';
?>
<footer class="footer">
  <div class="wrap footer-in">
    <div>
      <a class="brand" href="<?= $base ?>index.php">SHOP<span>&amp;</span>TUFF</a>
      <p style="margin-top:.6em;max-width:24em">Jeans, tees and shoes for men, chosen for how they wear.</p>
    </div>
    <div><h4>Shop</h4>
      <a href="<?= $base ?>index.php">All products</a>
      <?php foreach (array_slice(categories(), 0, 4) as $c): ?><a href="<?= $base ?>index.php?cat=<?= urlencode($c) ?>"><?= e($c) ?></a><?php endforeach; ?>
    </div>
    <div><h4>Account</h4>
      <a href="<?= $base ?>orders.php">My orders</a>
      <a href="<?= $base ?>settings.php">Settings</a>
      <a href="<?= $base ?>login.php">Log in</a>
    </div>
    <div><h4>Good to know</h4>
      <span>Flat $<?= SHIPPING_FLAT ?> shipping</span>
      <span style="display:block">Card details are never stored</span>
    </div>
  </div>
  <div class="legal">© <?= date('Y') ?> SHOP &amp; TUFF. Thank you for shopping with us.</div>
</footer>
</body>
</html>
