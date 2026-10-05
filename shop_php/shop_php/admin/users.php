<?php
require __DIR__ . '/../includes/functions.php';
require_admin();
$me = current_user();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $uid = (int)($_POST['id'] ?? 0);
    if ($uid && $uid !== (int)$me['id']) {   // you can't change your own role
        db()->prepare("UPDATE users SET role = IF(role = 'admin', 'customer', 'admin') WHERE id = ?")->execute([$uid]);
        flash('Role updated.');
    }
    redirect('users.php');
}

$rows = db()->query('SELECT u.*, (SELECT COUNT(*) FROM orders WHERE user_id = u.id) AS orders FROM users u ORDER BY u.id DESC')->fetchAll();
$pageTitle = 'Customers'; $adminNav = 'users';
require __DIR__ . '/../includes/admin_header.php';
?>
<div class="main-head"><h1>Customers</h1></div>
<div class="table-wrap"><table class="t">
  <thead><tr><th>Name</th><th>Email</th><th>Joined</th><th>Orders</th><th>Role</th><th class="right">Actions</th></tr></thead><tbody>
  <?php foreach ($rows as $u): ?>
    <tr>
      <td><strong><?= e($u['name']) ?></strong></td><td><?= e($u['email']) ?></td>
      <td><?= e(date('j M Y', strtotime($u['created_at']))) ?></td><td><?= (int)$u['orders'] ?></td>
      <td><span class="pill <?= $u['role'] === 'admin' ? 'processing' : '' ?>"><?= e(ucfirst($u['role'])) ?></span></td>
      <td class="right">
        <?php if ((int)$u['id'] !== (int)$me['id']): ?>
          <form method="post" class="inline" onsubmit="return confirm('Change this user’s role?');"><?= csrf_field() ?><input type="hidden" name="id" value="<?= (int)$u['id'] ?>">
            <button class="link-btn"><?= $u['role'] === 'admin' ? 'Make customer' : 'Make admin' ?></button></form>
        <?php else: ?><span class="muted small">You</span><?php endif; ?>
      </td>
    </tr>
  <?php endforeach; ?>
  </tbody></table></div>
<?php require __DIR__ . '/../includes/admin_footer.php'; ?>
