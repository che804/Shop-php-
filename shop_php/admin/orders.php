<?php
require __DIR__ . '/../includes/functions.php';
require_admin();

$status = $_GET['status'] ?? '';
$sql = 'SELECT o.*, (SELECT SUM(qty) FROM order_items WHERE order_id = o.id) AS items FROM orders o';
$args = [];
if (in_array($status, ORDER_STATUSES, true)) { $sql .= ' WHERE o.status = ?'; $args[] = $status; } else { $status = ''; }
$st = db()->prepare($sql . ' ORDER BY o.id DESC'); $st->execute($args);
$rows = $st->fetchAll();

$pageTitle = 'Orders'; $adminNav = 'orders';
require __DIR__ . '/../includes/admin_header.php';
?>
<div class="main-head"><h1>Orders</h1></div>
<div class="filters">
  <a href="orders.php" class="<?= $status === '' ? 'on' : '' ?>">All</a>
  <?php foreach (ORDER_STATUSES as $s): ?><a href="orders.php?status=<?= $s ?>" class="<?= $status === $s ? 'on' : '' ?>"><?= ucfirst($s) ?></a><?php endforeach; ?>
</div>
<div class="table-wrap"><table class="t">
  <thead><tr><th>Order</th><th>Date</th><th>Customer</th><th>Items</th><th>Status</th><th class="right">Total</th></tr></thead><tbody>
  <?php foreach ($rows as $o): ?>
    <tr>
      <td><a href="order.php?id=<?= (int)$o['id'] ?>"><strong>#<?= (int)$o['id'] ?></strong></a></td>
      <td><?= e(date('j M Y, H:i', strtotime($o['created_at']))) ?></td>
      <td><?= e($o['full_name']) ?><br><span class="muted small"><?= e($o['email']) ?></span></td>
      <td><?= (int)$o['items'] ?></td>
      <td><span class="pill <?= e($o['status']) ?>"><?= e(ucfirst($o['status'])) ?></span></td>
      <td class="right"><?= money($o['total']) ?></td>
    </tr>
  <?php endforeach; if (!$rows): ?><tr><td colspan="6" class="muted">No orders here yet.</td></tr><?php endif; ?>
  </tbody></table></div>
<?php require __DIR__ . '/../includes/admin_footer.php'; ?>
