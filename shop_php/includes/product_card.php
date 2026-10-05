<?php /* expects $p, optional $base */ $base = $base ?? ''; ?>
<article class="card">
  <a class="card-img" href="<?= $base ?>product.php?id=<?= $p['id'] ?>">
    <img src="<?= e(img_src($p['image'], $base)) ?>" alt="<?= e($p['name']) ?>" loading="lazy">
    <?php if (strtolower($p['category']) === 'new arrivals'): ?><span class="tag">New</span><?php endif; ?>
  </a>
  <div class="card-body">
    <span class="card-cat"><?= e($p['category']) ?></span>
    <a class="card-name" href="<?= $base ?>product.php?id=<?= $p['id'] ?>"><?= e($p['name']) ?></a>
    <div class="card-foot">
      <span class="price"><?= money($p['price']) ?></span>
      <form method="post" action="<?= $base ?>cart_action.php">
        <?= csrf_field() ?>
        <input type="hidden" name="action" value="add">
        <input type="hidden" name="id" value="<?= $p['id'] ?>">
        <input type="hidden" name="back" value="<?= e(current_url()) ?>">
        <button type="submit" class="btn btn-sm">Add to cart</button>
      </form>
    </div>
  </div>
</article>
