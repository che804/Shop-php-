<?php
require __DIR__ . '/../includes/functions.php';
require_admin();

$id = (int)($_GET['id'] ?? 0);
$st = db()->prepare('SELECT * FROM orders WHERE id = ?'); $st->execute([$id]);
$order = $st->fetch();
if (!$order) { flash('Order not found.', 'error'); redirect('orders.php'); }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $new = $_POST['status'] ?? '';
    if (in_array($new, ORDER_STATUSES, true)) {
        db()->prepare('UPDATE orders SET status = ? WHERE id = ?')->execute([$new, $id]);
        flash('Order #' . $id . ' is now ' . $new . '.');
    }
    redirect('order.php?id=' . $id);
}

$st = db()->prepare('SELECT oi.*, p.name, p.image FROM order_items oi JOIN products p ON p.id = oi.product_id WHERE oi.order_id = ?');
$st->execute([$id]);
$items = $st->fetchAll();

$pageTitle = 'Order #' . $id; $adminNav = 'orders';
require __DIR__ . '/../includes/admin_header.php';
?>
<div class="main-head"><h1>Order #<?= $id ?></h1><a href="orders.php">← All orders</a></div>
<div class="cols" style="grid-template-columns:1.6fr 1fr">
  <section class="panel">
    <h3>Items</h3>
    <?php foreach ($items as $i): ?>
      <div class="sum-line"><img src="<?= e(img_src($i['image'], '../')) ?>" alt="">
        <div class="grow"><strong><?= e($i['name']) ?></strong><br><span class="muted small"><?= money($i['price']) ?> × <?= (int)$i['qty'] ?></span></div>
        <span><?= money($i['price'] * $i['qty']) ?></span></div>
    <?php endforeach; ?>
    <div class="totals" style="margin-top:12px">
      <div><span>Subtotal</span><span><?= money($order['subtotal']) ?></span></div>
      <div><span>Shipping</span><span><?= money($order['shipping']) ?></span></div>
      <div class="grand"><span>Total</span><span><?= money($order['total']) ?></span></div>
    </div>
  </section>
  <div>
    <section class="panel"><h3>Status</h3>
      <form method="post"><?= csrf_field() ?>
        <div class="field"><label for="status">Order status</label>
          <select id="status" name="status"><?php foreach (ORDER_STATUSES as $s): ?><option value="<?= $s ?>" <?= $order['status'] === $s ? 'selected' : '' ?>><?= ucfirst($s) ?></option><?php endforeach; ?></select></div>
        <button class="btn btn-block">Update status</button>
      </form>
    </section>
    <section class="panel"><h3>Customer</h3>
      <p style="margin:0"><?= e($order['full_name']) ?><br><a href="mailto:<?= e($order['email']) ?>"><?= e($order['email']) ?></a><br><br>
        <?= e($order['address']) ?><br><?= e($order['city']) ?><?= $order['state'] ? ', ' . e($order['state']) : '' ?> <?= e($order['zip']) ?><br><?= e($order['country']) ?></p>
      <p class="muted small" style="margin:12px 0 0">Placed <?= e(date('j M Y, H:i', strtotime($order['created_at']))) ?></p>
    </section>
  </div>
</div>
<?php require __DIR__ . '/../includes/admin_footer.php'; ?>
