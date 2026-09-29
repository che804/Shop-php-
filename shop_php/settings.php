<?php
require __DIR__ . '/includes/functions.php';
require_login('settings.php');
$user = current_user();

$_SESSION['notifications'] = $_SESSION['notifications'] ?? ['orderUpdates' => true, 'promotions' => false, 'newsletter' => true];
$profileError = $passwordError = $deleteError = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $do = $_POST['do'] ?? '';

    if ($do === 'profile') {
        $name = trim($_POST['name'] ?? ''); $email = trim($_POST['email'] ?? '');
        if ($name === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $profileError = 'Enter your name and a valid email address.';
        } else {
            try {
                db()->prepare('UPDATE users SET name = ?, email = ? WHERE id = ?')->execute([$name, $email, $user['id']]);
                flash('Profile saved.'); redirect('settings.php');
            } catch (PDOException $ex) {
                if ($ex->getCode() === '23000') $profileError = 'Another account already uses that email.'; else throw $ex;
            }
        }
        $user['name'] = $name; $user['email'] = $email;
    }

    if ($do === 'password') {
        $st = db()->prepare('SELECT password_hash FROM users WHERE id = ?'); $st->execute([$user['id']]);
        $hash = $st->fetchColumn();
        $cur = $_POST['current'] ?? ''; $new = $_POST['next'] ?? ''; $con = $_POST['confirm'] ?? '';
        if ($cur === '' || $new === '' || $con === '') $passwordError = 'Fill in all three password fields.';
        elseif (!password_verify($cur, $hash))          $passwordError = 'Your current password is incorrect.';
        elseif (strlen($new) < 8)                       $passwordError = 'The new password needs at least 8 characters.';
        elseif ($new !== $con)                          $passwordError = 'The new passwords don’t match.';
        else {
            db()->prepare('UPDATE users SET password_hash = ? WHERE id = ?')->execute([password_hash($new, PASSWORD_DEFAULT), $user['id']]);
            session_regenerate_id(true);
            flash('Password changed.'); redirect('settings.php');
        }
    }

    if ($do === 'toggle') {
        $key = $_POST['key'] ?? '';
        if (isset($_SESSION['notifications'][$key])) $_SESSION['notifications'][$key] = !$_SESSION['notifications'][$key];
        redirect('settings.php');
    }

    if ($do === 'delete') {
        $st = db()->prepare('SELECT password_hash FROM users WHERE id = ?'); $st->execute([$user['id']]);
        if (!password_verify($_POST['password'] ?? '', (string)$st->fetchColumn())) {
            $deleteError = 'That password is incorrect, so your account was not deleted.';
        } else {
            db()->prepare('DELETE FROM users WHERE id = ?')->execute([$user['id']]);   // orders keep user_id = NULL
            $_SESSION = []; session_destroy();
            redirect('index.php');
        }
    }
}

$notes   = $_SESSION['notifications'];
$toggles = [
    'orderUpdates' => ['Order updates', 'Shipping and delivery status for your orders.'],
    'promotions'   => ['Promotions', 'Sales, discounts and limited-time offers.'],
    'newsletter'   => ['Newsletter', 'Occasional notes on new arrivals and restocks.'],
];
$pageTitle = 'Settings';
$noDrawer  = true;
require __DIR__ . '/includes/header.php';
?>
<div class="wrap page" style="max-width:760px">
  <div class="page-head"><h1>Settings</h1><p class="muted">Manage your profile, password and notifications. <a href="orders.php">View my orders</a></p></div>

  <section class="panel"><h3>Profile</h3>
    <form method="post" action="settings.php"><?= csrf_field() ?><input type="hidden" name="do" value="profile">
      <div class="two">
        <div class="field"><label for="name">Full name</label><input id="name" name="name" type="text" value="<?= e($user['name']) ?>" required></div>
        <div class="field"><label for="email">Email address</label><input id="email" name="email" type="email" value="<?= e($user['email']) ?>" required></div>
      </div>
      <?php if ($profileError): ?><div class="alert error"><?= e($profileError) ?></div><?php endif; ?>
      <button class="btn" type="submit">Save profile</button>
    </form>
  </section>

  <section class="panel"><h3>Password</h3>
    <form method="post" action="settings.php"><?= csrf_field() ?><input type="hidden" name="do" value="password">
      <div class="field"><label for="current">Current password</label><input id="current" name="current" type="password" autocomplete="current-password"></div>
      <div class="two">
        <div class="field"><label for="next">New password</label><input id="next" name="next" type="password" autocomplete="new-password"><p class="hint">At least 8 characters.</p></div>
        <div class="field"><label for="confirm">Confirm new password</label><input id="confirm" name="confirm" type="password" autocomplete="new-password"></div>
      </div>
      <?php if ($passwordError): ?><div class="alert error"><?= e($passwordError) ?></div><?php endif; ?>
      <button class="btn" type="submit">Change password</button>
    </form>
  </section>

  <section class="panel"><h3>Notifications</h3>
    <?php foreach ($toggles as $key => [$label, $desc]): ?>
      <form method="post" action="settings.php" class="row-between" style="padding:10px 0;border-bottom:1px solid var(--line);align-items:center">
        <?= csrf_field() ?><input type="hidden" name="do" value="toggle"><input type="hidden" name="key" value="<?= e($key) ?>">
        <div><strong><?= e($label) ?></strong><br><span class="muted small"><?= e($desc) ?></span></div>
        <button type="submit" class="btn btn-sm <?= $notes[$key] ? 'btn-accent' : 'btn-ghost' ?>" aria-pressed="<?= $notes[$key] ? 'true' : 'false' ?>"><?= $notes[$key] ? 'On' : 'Off' ?></button>
      </form>
    <?php endforeach; ?>
  </section>

  <section class="panel danger"><h3>Delete account</h3>
    <p class="muted">This removes your account for good. Past orders are kept without your name attached, for our sales records.</p>
    <form method="post" action="settings.php" onsubmit="return confirm('Delete your account permanently? This cannot be undone.');">
      <?= csrf_field() ?><input type="hidden" name="do" value="delete">
      <div class="field"><label for="dpw">Enter your password to confirm</label><input id="dpw" name="password" type="password" required autocomplete="current-password"></div>
      <?php if ($deleteError): ?><div class="alert error"><?= e($deleteError) ?></div><?php endif; ?>
      <button class="btn btn-danger" type="submit">Delete my account</button>
    </form>
  </section>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
