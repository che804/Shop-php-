<?php
require __DIR__ . '/../includes/functions.php';
require_admin();

$pdo = db();
$stats = [
  'Orders'      => (int)$pdo->query("SELECT COUNT(*) FROM orders")->fetchColumn(),
  'Revenue'     => money($pdo->query("SELECT COALESCE(SUM(total),0) FROM orders WHERE status <> 'cancelled'")->fetchColumn()),
  'Products'    => (int)$pdo->query("SELECT COUNT(*) FROM products WHERE is_active = 1")->fetchColumn(),
  'Customers'   => (int)$pdo->query("SELECT COUNT(*) FROM users WHERE role = 'customer'")->fetchColumn(),
];
$pending = (int)$pdo->query("SELECT COUNT(*) FROM orders WHERE status = 'pending'")->fetchColumn();
$recent  = $pdo->query("SELECT o.id, o.full_name, o.total, o.status, o.created_at FROM orders o ORDER BY o.id DESC LIMIT 8")->fetchAll();
$top     = $pdo->query("SELECT p.id, p.name, SUM(oi.qty) AS sold, SUM(oi.qty * oi.price) AS revenue
                        FROM order_items oi JOIN orders o ON o.id = oi.order_id AND o.status <> 'cancelled'
                        JOIN products p ON p.id = oi.product_id GROUP BY p.id, p.name ORDER BY sold DESC LIMIT 5")->fetchAll();

$pageTitle = 'Dashboard'; $adminNav = 'dashboard';
require __DIR__ . '/../includes/admin_header.php';
?>
<div class="main-head"><h1>Dashboard</h1><a class="btn btn-accent" href="product_form.php">Add product</a></div>

<div class="stats">
  <?php foreach ($stats as $label => $v): ?><div class="stat"><b><?= e($v) ?></b><span><?= e($label) ?></span></div><?php endforeach; ?>
</div>
<?php if ($pending): ?><div class="alert"><strong><?= $pending ?></strong> pending <?= $pending === 1 ? 'order needs' : 'orders need' ?> attention. <a href="orders.php?status=pending">Review them</a></div><?php endif; ?>

<div class="cols" style="grid-template-columns:1.6fr 1fr">
  <section>
    <div class="main-head" style="margin-bottom:12px"><h2 style="margin:0">Recent orders</h2><a href="orders.php">All orders</a></div>
    <div class="table-wrap"><table class="t">
      <thead><tr><th>Order</th><th>Customer</th><th>Status</th><th class="right">Total</th></tr></thead><tbody>
      <?php foreach ($recent as $o): ?>
        <tr><td><a href="order.php?id=<?= (int)$o['id'] ?>">#<?= (int)$o['id'] ?></a></td><td><?= e($o['full_name']) ?></td>
          <td><span class="pill <?= e($o['status']) ?>"><?= e(ucfirst($o['status'])) ?></span></td><td class="right"><?= money($o['total']) ?></td></tr>
      <?php endforeach; if (!$recent): ?><tr><td colspan="4" class="muted">No orders yet.</td></tr><?php endif; ?>
      </tbody></table></div>
  </section>
  <section>
    <h2 style="margin-bottom:12px">Best sellers</h2>
    <div class="table-wrap"><table class="t" style="min-width:0">
      <thead><tr><th>Product</th><th class="right">Sold</th></tr></thead><tbody>
      <?php foreach ($top as $t): ?><tr><td><?= e($t['name']) ?></td><td class="right"><?= (int)$t['sold'] ?></td></tr>
      <?php endforeach; if (!$top): ?><tr><td colspan="2" class="muted">Sales will show here.</td></tr><?php endif; ?>
      </tbody></table></div>
  </section>
</div>
<?php require __DIR__ . '/../includes/admin_footer.php'; ?>
