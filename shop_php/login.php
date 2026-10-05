<?php
require __DIR__ . '/includes/functions.php';

$next = safe_next($_GET['next'] ?? $_POST['next'] ?? '');
if (current_user()) redirect($next);

$error = ''; $email = '';
$_SESSION['fails'] = $_SESSION['fails'] ?? 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $email = trim($_POST['email'] ?? '');
    $pass  = $_POST['password'] ?? '';

    if ($_SESSION['fails'] >= 5 && time() - ($_SESSION['fail_time'] ?? 0) < 300) {
        $error = 'Too many attempts. Wait a few minutes and try again.';
    } else {
        $st = db()->prepare('SELECT id, password_hash FROM users WHERE email = ?');
        $st->execute([$email]);
        $u = $st->fetch();
        if ($u && password_verify($pass, $u['password_hash'])) {
            $_SESSION['fails'] = 0;
            login_user($u['id']);
            redirect($next);
        }
        $_SESSION['fails']++; $_SESSION['fail_time'] = time();
        $error = 'Email or password is incorrect.';
    }
}

$pageTitle = 'Log in';
$artTitle  = 'Good to see you again.';
$artText   = 'Log in to check out faster, follow your orders and manage your account.';
require __DIR__ . '/includes/auth_layout_top.php';
?>
<h2>Log in</h2>
<form method="post" action="login.php">
  <?= csrf_field() ?>
  <input type="hidden" name="next" value="<?= e($next) ?>">
  <div class="field"><label for="email">Email address</label>
    <input id="email" name="email" type="email" value="<?= e($email) ?>" required autofocus autocomplete="email"></div>
  <div class="field"><label for="password">Password</label>
    <input id="password" name="password" type="password" required autocomplete="current-password"></div>
  <?php if ($error): ?><div class="alert error" role="alert"><?= e($error) ?></div><?php endif; ?>
  <button type="submit" class="btn btn-block">Log in</button>
</form>
<p class="muted" style="margin-top:1.2rem">New here? <a href="register.php?next=<?= e(urlencode($next)) ?>">Create an account</a></p>
<?php require __DIR__ . '/includes/auth_layout_bottom.php'; ?>
