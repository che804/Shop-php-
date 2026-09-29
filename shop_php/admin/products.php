<?php
require __DIR__ . '/../includes/functions.php';
require_admin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $id = (int)($_POST['id'] ?? 0);
    $p  = find_product($id, false);
    if ($p) {
        if (($_POST['do'] ?? '') === 'toggle') {
            db()->prepare('UPDATE products SET is_active = 1 - is_active WHERE id = ?')->execute([$id]);
            flash($p['is_active'] ? '“' . $p['name'] . '” is now hidden from the shop.' : '“' . $p['name'] . '” is visible in the shop.');
        } elseif (($_POST['do'] ?? '') === 'delete') {
            try {
                db()->prepare('DELETE FROM products WHERE id = ?')->execute([$id]);
                if (strpos($p['image'], 'assets/uploads/') === 0) @unlink(__DIR__ . '/../' . $p['image']);
                flash('“' . $p['name'] . '” was deleted.');
            } catch (PDOException $ex) {
                // already ordered: keep for order history, just hide it
                db()->prepare('UPDATE products SET is_active = 0 WHERE id = ?')->execute([$id]);
                flash('“' . $p['name'] . '” is part of past orders, so it was hidden instead of deleted.', 'error');
            }
        }
    }
    redirect('products.php');
}

$q = trim($_GET['q'] ?? '');
$st = db()->prepare('SELECT * FROM products WHERE name LIKE ? OR category LIKE ? ORDER BY id DESC');
$like = '%' . addcslashes($q, '%_\\') . '%';
$st->execute([$like, $like]);
$rows = $st->fetchAll();

$pageTitle = 'Products'; $adminNav = 'products';
require __DIR__ . '/../includes/admin_header.php';
?>
<div class="main-head"><h1>Products</h1><a class="btn btn-accent" href="product_form.php">Add product</a></div>
<form method="get" class="search" style="max-width:360px;margin:0 0 16px">
  <label class="sr-only" for="q">Search products</label>
  <input id="q" type="search" name="q" value="<?= e($q) ?>" placeholder="Search name or category">
</form>
<div class="table-wrap"><table class="t">
  <thead><tr><th></th><th>Name</th><th>Category</th><th class="right">Price</th><th>Status</th><th class="right">Actions</th></tr></thead><tbody>
  <?php foreach ($rows as $r): ?>
    <tr>
      <td><img class="thumb" src="<?= e(img_src($r['image'], '../')) ?>" alt=""></td>
      <td><strong><?= e($r['name']) ?></strong></td>
      <td><?= e($r['category']) ?></td>
      <td class="right"><?= money($r['price']) ?></td>
      <td><span class="pill <?= $r['is_active'] ? 'on' : 'off' ?>"><?= $r['is_active'] ? 'Visible' : 'Hidden' ?></span></td>
      <td class="right" style="white-space:nowrap">
        <a href="product_form.php?id=<?= (int)$r['id'] ?>">Edit</a> ·
        <form method="post" class="inline"><?= csrf_field() ?><input type="hidden" name="id" value="<?= (int)$r['id'] ?>"><input type="hidden" name="do" value="toggle">
          <button class="link-btn"><?= $r['is_active'] ? 'Hide' : 'Show' ?></button></form> ·
        <form method="post" class="inline" onsubmit="return confirm('Delete this product?');"><?= csrf_field() ?><input type="hidden" name="id" value="<?= (int)$r['id'] ?>"><input type="hidden" name="do" value="delete">
          <button class="link-btn danger">Delete</button></form>
      </td>
    </tr>
  <?php endforeach; if (!$rows): ?><tr><td colspan="6" class="muted">No products found.</td></tr><?php endif; ?>
  </tbody></table></div>
<?php require __DIR__ . '/../includes/admin_footer.php'; ?>
