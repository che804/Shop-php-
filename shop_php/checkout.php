<?php
require __DIR__ . '/includes/functions.php';
require_login('checkout.php');

$user       = current_user();
$lines      = cart_lines();
$total      = cart_total($lines);
$shipping   = $lines ? SHIPPING_FLAT : 0;
$grandTotal = $total + $shipping;

$fields = ['email','fullName','address','city','state','zip','country','cardName','cardNumber','expiry','cvv'];
$form   = array_fill_keys($fields, '');
$form['email'] = $user['email']; $form['fullName'] = $user['name'];
$errors = [];

/* ----- confirmation page ----- */
if (isset($_GET['placed']) && isset($_SESSION['order'])) {
    $order = $_SESSION['order'];
    $pageTitle = 'Order placed';
    require __DIR__ . '/includes/header.php';
    ?>
    <div class="wrap narrow confirm">
      <div class="ok" aria-hidden="true">✓</div>
      <h1 style="font-size:clamp(2.2rem,5vw,3.2rem)">Thank you, <?= e(explode(' ', trim($order['fullName']))[0]) ?>.</h1>
      <p class="muted">Order #<?= (int)$order['id'] ?> is placed. We’ll send updates to <?= e($order['email']) ?>. Your total was <?= money($order['total']) ?>.</p>
      <div class="hero-cta" style="justify-content:center">
        <a class="btn" href="orders.php?id=<?= (int)$order['id'] ?>">View order</a>
        <a class="btn btn-ghost" href="index.php">Keep shopping</a>
      </div>
    </div>
    <?php
    require __DIR__ . '/includes/footer.php';
    exit;
}

/* ----- submit ----- */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $lines) {
    csrf_check();
    foreach ($fields as $f) $form[$f] = trim($_POST[$f] ?? '');

    if (!filter_var($form['email'], FILTER_VALIDATE_EMAIL)) $errors['email'] = 'Enter a valid email address.';
    if ($form['fullName'] === '') $errors['fullName'] = 'Enter your full name.';
    if ($form['address'] === '')  $errors['address']  = 'Enter your street address.';
    if ($form['city'] === '')     $errors['city']     = 'Enter your city.';
    if ($form['zip'] === '')      $errors['zip']      = 'Enter your ZIP / postal code.';
    if ($form['country'] === '')  $errors['country']  = 'Enter your country.';
    if ($form['cardName'] === '') $errors['cardName'] = 'Enter the name on the card.';
    if (!preg_match('/^\d{13,19}$/', preg_replace('/\s/', '', $form['cardNumber']))) $errors['cardNumber'] = 'Enter a valid card number.';
    if (!preg_match('/^\d{2}\/\d{2}$/', $form['expiry'])) $errors['expiry'] = 'Use the format MM/YY.';
    if (!preg_match('/^\d{3,4}$/', $form['cvv'])) $errors['cvv'] = 'Enter the 3 or 4 digit code.';

    if (!$errors) {
        // Card data is validated only and never stored.
        $pdo = db();
        try {
            $pdo->beginTransaction();
            $st = $pdo->prepare('INSERT INTO orders (user_id, email, full_name, address, city, state, zip, country, subtotal, shipping, total)
                                 VALUES (?,?,?,?,?,?,?,?,?,?,?)');
            $st->execute([$user['id'], $form['email'], $form['fullName'], $form['address'], $form['city'], $form['state'],
                          $form['zip'], $form['country'], $total, $shipping, $grandTotal]);
            $orderId = (int)$pdo->lastInsertId();
            $it = $pdo->prepare('INSERT INTO order_items (order_id, product_id, qty, price) VALUES (?,?,?,?)');
            foreach ($lines as $l) $it->execute([$orderId, $l['id'], $l['qty'], $l['price']]);
            $pdo->commit();
            $_SESSION['order'] = ['id' => $orderId, 'fullName' => $form['fullName'], 'email' => $form['email'], 'total' => $grandTotal];
            $_SESSION['cart']  = [];
            redirect('checkout.php?placed=1');
        } catch (Throwable $ex) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            $errors['form'] = 'We couldn’t save your order. Nothing was charged, please try again.';
        }
    }
}

function field($id, $label, $form, $errors, $opts = []) {
    $secret = in_array($id, ['cardNumber', 'cvv'], true);   // never echo card data back
    $val = $secret ? '' : $form[$id];
    echo '<div class="field ' . (isset($errors[$id]) ? 'invalid' : '') . '"><label for="' . $id . '">' . e($label) . '</label>';
    echo '<input id="' . $id . '" name="' . $id . '" type="' . ($id === 'email' ? 'email' : 'text') . '" value="' . e($val) . '" '
       . ($opts['attrs'] ?? '') . '>';
    if (isset($errors[$id])) echo '<p class="err">' . e($errors[$id]) . '</p>';
    echo '</div>';
}

$pageTitle = 'Checkout';
$noDrawer  = true;
require __DIR__ . '/includes/header.php';
?>
<div class="wrap page">
  <div class="page-head"><h1>Checkout</h1><p class="muted">Shipping details and payment, all on one page.</p></div>

  <?php if (!$lines): ?>
    <div class="empty"><h3>Your cart is empty</h3><p class="muted">Add something before checking out.</p><a class="btn" href="index.php">Browse products</a></div>
  <?php else: ?>
  <form method="post" action="checkout.php" novalidate autocomplete="off" class="cols">
    <?= csrf_field() ?>
    <div>
      <?php if (isset($errors['form'])): ?><div class="alert error" role="alert"><?= e($errors['form']) ?></div><?php endif; ?>
      <section class="panel"><h3>Contact</h3>
        <?php field('email', 'Email address', $form, $errors, ['attrs' => 'autocomplete="email"']); ?>
      </section>
      <section class="panel"><h3>Shipping address</h3>
        <?php field('fullName', 'Full name', $form, $errors); field('address', 'Street address', $form, $errors); ?>
        <div class="two"><?php field('city', 'City', $form, $errors); field('state', 'State / province', $form, $errors); ?></div>
        <div class="two"><?php field('zip', 'ZIP / postal code', $form, $errors); field('country', 'Country', $form, $errors); ?></div>
      </section>
      <section class="panel"><h3>Payment</h3>
        <?php field('cardName', 'Name on card', $form, $errors);
              field('cardNumber', 'Card number', $form, $errors, ['attrs' => 'inputmode="numeric" placeholder="1234 1234 1234 1234"']); ?>
        <div class="two"><?php field('expiry', 'Expiry (MM/YY)', $form, $errors, ['attrs' => 'placeholder="08/29"']);
                               field('cvv', 'Security code', $form, $errors, ['attrs' => 'inputmode="numeric" placeholder="123"']); ?></div>
        <p class="muted small" style="margin:0">Card details are checked here and never saved.</p>
      </section>
    </div>

    <aside class="panel summary">
      <h3>Order summary</h3>
      <?php foreach ($lines as $item): ?>
        <div class="sum-line">
          <img src="<?= e(img_src($item['image'])) ?>" alt="">
          <div class="grow"><strong><?= e($item['name']) ?></strong><br><span class="muted small">Qty <?= $item['qty'] ?></span></div>
          <span><?= money($item['price'] * $item['qty']) ?></span>
        </div>
      <?php endforeach; ?>
      <div class="totals" style="margin:12px 0 18px">
        <div><span>Subtotal</span><span><?= money($total) ?></span></div>
        <div><span>Shipping</span><span><?= money($shipping) ?></span></div>
        <div class="grand"><span>Total</span><span><?= money($grandTotal) ?></span></div>
      </div>
      <button type="submit" class="btn btn-accent btn-block">Place order · <?= money($grandTotal) ?></button>
      <p class="small" style="margin:12px 0 0"><a href="index.php">Continue shopping</a></p>
    </aside>
  </form>
  <?php endif; ?>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
