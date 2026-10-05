<?php
require __DIR__ . '/../includes/functions.php';
require_admin();

$id   = (int)($_GET['id'] ?? 0);
$edit = $id ? find_product($id, false) : null;
if ($id && !$edit) { flash('That product no longer exists.', 'error'); redirect('products.php'); }

$f = $edit ?: ['name' => '', 'category' => '', 'price' => '', 'image' => '', 'description' => '', 'is_active' => 1];
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $f['name']        = trim($_POST['name'] ?? '');
    $f['category']    = trim($_POST['category'] ?? '');
    $f['price']       = trim($_POST['price'] ?? '');
    $f['description'] = trim($_POST['description'] ?? '');
    $f['is_active']   = isset($_POST['is_active']) ? 1 : 0;
    $imageUrl         = trim($_POST['image_url'] ?? '');

    if ($f['name'] === '')     $errors['name'] = 'Enter a product name.';
    if ($f['category'] === '') $errors['category'] = 'Enter a category.';
    if (!is_numeric($f['price']) || $f['price'] < 0 || $f['price'] > 99999999) $errors['price'] = 'Enter a price, for example 19.99.';

    $image = $edit['image'] ?? '';
    try {
        $uploaded = save_upload($_FILES['image_file'] ?? null, __DIR__ . '/../assets/uploads');
        if ($uploaded) $image = $uploaded;
        elseif ($imageUrl !== '') {
            if (!preg_match('#^https?://#i', $imageUrl)) $errors['image'] = 'The image link must start with http:// or https://';
            else $image = $imageUrl;
        }
    } catch (RuntimeException $ex) { $errors['image'] = $ex->getMessage(); }
    if ($image === '' && !isset($errors['image'])) $errors['image'] = 'Add an image link or upload a file.';

    if (!$errors) {
        if ($edit) {
            db()->prepare('UPDATE products SET name=?, category=?, price=?, image=?, description=?, is_active=? WHERE id=?')
                ->execute([$f['name'], $f['category'], $f['price'], $image, $f['description'], $f['is_active'], $id]);
            if ($image !== $edit['image'] && strpos($edit['image'], 'assets/uploads/') === 0) @unlink(__DIR__ . '/../' . $edit['image']);
            flash('Product saved.');
        } else {
            db()->prepare('INSERT INTO products (name, category, price, image, description, is_active) VALUES (?,?,?,?,?,?)')
                ->execute([$f['name'], $f['category'], $f['price'], $image, $f['description'], $f['is_active']]);
            flash('Product added.');
        }
        redirect('products.php');
    }
    $f['image'] = $image;
}
$cats = db()->query('SELECT DISTINCT category FROM products ORDER BY category')->fetchAll(PDO::FETCH_COLUMN);
function ferr($errors, $k) { return isset($errors[$k]) ? '<p class="err">' . e($errors[$k]) . '</p>' : ''; }

$pageTitle = $edit ? 'Edit product' : 'Add product'; $adminNav = 'products';
require __DIR__ . '/../includes/admin_header.php';
?>
<div class="main-head"><h1><?= e($pageTitle) ?></h1><a href="products.php">← All products</a></div>
<form method="post" enctype="multipart/form-data" class="cols" style="grid-template-columns:1.5fr 1fr" novalidate>
  <?= csrf_field() ?>
  <section class="panel">
    <div class="field <?= isset($errors['name']) ? 'invalid' : '' ?>"><label for="name">Name</label>
      <input id="name" name="name" type="text" value="<?= e($f['name']) ?>" required><?= ferr($errors, 'name') ?></div>
    <div class="two">
      <div class="field <?= isset($errors['category']) ? 'invalid' : '' ?>"><label for="category">Category</label>
        <input id="category" name="category" type="text" list="cats" value="<?= e($f['category']) ?>" required>
        <datalist id="cats"><?php foreach ($cats as $c): ?><option value="<?= e($c) ?>"><?php endforeach; ?></datalist><?= ferr($errors, 'category') ?></div>
      <div class="field <?= isset($errors['price']) ? 'invalid' : '' ?>"><label for="price">Price (USD)</label>
        <input id="price" name="price" type="number" step="0.01" min="0" value="<?= e($f['price']) ?>" required><?= ferr($errors, 'price') ?></div>
    </div>
    <div class="field"><label for="description">Description</label><textarea id="description" name="description" rows="4"><?= e($f['description']) ?></textarea></div>
    <label class="check"><input type="checkbox" name="is_active" <?= $f['is_active'] ? 'checked' : '' ?>> Visible in the shop</label>
  </section>

  <section class="panel">
    <h3>Image</h3>
    <?php if ($f['image']): ?><img class="thumb-lg" src="<?= e(img_src($f['image'], '../')) ?>" alt="" style="margin-bottom:12px"><?php endif; ?>
    <div class="field <?= isset($errors['image']) ? 'invalid' : '' ?>"><label for="image_url">Image link</label>
      <input id="image_url" name="image_url" type="url" placeholder="https://…" value="<?= e(preg_match('#^https?://#', $f['image']) ? $f['image'] : '') ?>"><?= ferr($errors, 'image') ?></div>
    <div class="field"><label for="image_file">Or upload a file</label>
      <input id="image_file" name="image_file" type="file" accept="image/jpeg,image/png,image/webp"><p class="hint">JPG, PNG or WebP, up to 2 MB. An upload replaces the link.</p></div>
    <button type="submit" class="btn btn-accent btn-block"><?= $edit ? 'Save changes' : 'Add product' ?></button>
  </section>
</form>
<?php require __DIR__ . '/../includes/admin_footer.php'; ?>
