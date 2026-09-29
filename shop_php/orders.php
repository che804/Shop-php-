<?php
require __DIR__ . '/includes/functions.php';
require_login('orders.php');
$user = current_user();

$pageTitle = 'My orders';
$noDrawer  = false;

/* single order */
if (isset($_GET['id'])) {
    $st = db()->prepare('SELECT * FROM orders WHERE id = ? AND user_id = ?');
    $st->execute([(int)$_GET['id'], $user['id']]);
    $order = $st->fetch();
    if ($order) {
        $st = db()->prepare('SELECT oi.*, p.name, p.image FROM order_items oi JOIN products p ON p.id = oi.product_id WHERE oi.order_id = ?');
        $st->execute([$order['id']]);
        $items = $st->fetchAll();
    }
    $pageTitle = $order ? 'Order #' . $order['id'] : 'Order not found';
    require __DIR__ . '/includes/header.php';
    ?>
    <div class="wrap page">
    <?php if (!$order): ?>
      <div class="empty"><h2>Order not found</h2><a class="btn" href="orders.php">Back to my orders</a></div>
    <?php else: ?>
      <p class="small"><a href="orders.php">← All orders</a></p>
      <div class="page-head"><h1>Order #<?= (int)$order['id'] ?></h1>
        <span class="pill <?= e($order['status']) ?>"><?= e(ucfirst($order['status'])) ?></span>
        <span class="muted" style="margin-left:8px">Placed <?= e(date('j M Y, H:i', strtotime($order['created_at']))) ?></span></div>
      <div class="cols">
        <section class="panel">
          <h3>Items</h3>
          <?php foreach ($items as $i): ?>
            <div class="sum-line">
              <img src="<?= e(img_src($i['image'])) ?>" alt="">
              <div class="grow"><strong><?= e($i['name']) ?></strong><br><span class="muted small"><?= money($i['price']) ?> × <?= (int)$i['qty'] ?></span></div>
              <span><?= money($i['price'] * $i['qty']) ?></span>
            </div>
          <?php endforeach; ?>
          <div class="totals" style="margin-top:12px">
            <div><span>Subtotal</span><span><?= money($order['subtotal']) ?></span></div>
            <div><span>Shipping</span><span><?= money($order['shipping']) ?></span></div>
            <div class="grand"><span>Total</span><span><?= money($order['total']) ?></span></div>
          </div>
        </section>
        <section class="panel">
          <h3>Shipping to</h3>
          <p style="margin:0"><?= e($order['full_name']) ?><br><?= e($order['address']) ?><br>
            <?= e($order['city']) ?><?= $order['state'] ? ', ' . e($order['state']) : '' ?> <?= e($order['zip']) ?><br><?= e($order['country']) ?></p>
        </section>
      </div>
    <?php endif; ?>
    </div>
    <?php
    require __DIR__ . '/includes/footer.php';
    exit;
}

/* list */
$st = db()->prepare('SELECT o.*, (SELECT SUM(qty) FROM order_items WHERE order_id = o.id) AS items
                     FROM orders o WHERE o.user_id = ? ORDER BY o.id DESC');
$st->execute([$user['id']]);
$orders = $st->fetchAll();
require __DIR__ . '/includes/header.php';
?>
<div class="wrap page">
  <div class="page-head"><h1>My orders</h1><p class="muted">Everything you’ve ordered, newest first.</p></div>
  <?php if (!$orders): ?>
    <div class="empty"><h3>No orders yet</h3><p class="muted">When you place an order it will appear here.</p><a class="btn" href="index.php">Start shopping</a></div>
  <?php else: ?>
    <div class="table-wrap"><table class="t">
      <thead><tr><th>Order</th><th>Date</th><th>Items</th><th>Status</th><th class="right">Total</th><th></th></tr></thead>
      <tbody>
      <?php foreach ($orders as $o): ?>
        <tr>
          <td><strong>#<?= (int)$o['id'] ?></strong></td>
          <td><?= e(date('j M Y', strtotime($o['created_at']))) ?></td>
          <td><?= (int)$o['items'] ?></td>
          <td><span class="pill <?= e($o['status']) ?>"><?= e(ucfirst($o['status'])) ?></span></td>
          <td class="right"><?= money($o['total']) ?></td>
          <td class="right"><a href="orders.php?id=<?= (int)$o['id'] ?>">View</a></td>
        </tr>
      <?php endforeach; ?>
      </tbody></table></div>
  <?php endif; ?>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
