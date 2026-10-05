<?php
require __DIR__ . '/includes/functions.php';

$next = safe_next($_GET['next'] ?? $_POST['next'] ?? '');
if (current_user()) redirect($next);

$errors = []; $name = $email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $name = trim($_POST['name'] ?? ''); $email = trim($_POST['email'] ?? '');
    $pass = $_POST['password'] ?? '';   $confirm = $_POST['confirm'] ?? '';

    if ($name === '') $errors['name'] = 'Enter your full name.';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors['email'] = 'Enter a valid email address.';
    if (strlen($pass) < 8) $errors['password'] = 'Use at least 8 characters.';
    elseif ($pass !== $confirm) $errors['confirm'] = 'The two passwords don’t match.';

    if (!$errors) {
        try {
            $st = db()->prepare('INSERT INTO users (name, email, password_hash) VALUES (?,?,?)');
            $st->execute([$name, $email, password_hash($pass, PASSWORD_DEFAULT)]);
            login_user(db()->lastInsertId());
            flash('Welcome, ' . explode(' ', $name)[0] . '. Your account is ready.');
            redirect($next);
        } catch (PDOException $ex) {
            if ($ex->getCode() === '23000') $errors['email'] = 'That email already has an account. Try logging in.';
            else throw $ex;
        }
    }
}
function ferr($errors, $k) { return isset($errors[$k]) ? '<p class="err">' . e($errors[$k]) . '</p>' : ''; }

$pageTitle = 'Create account';
$artTitle  = 'Fits worth coming back for.';
$artText   = 'An account keeps your details ready for checkout and your orders in one place.';
require __DIR__ . '/includes/auth_layout_top.php';
?>
<h2>Create account</h2>
<form method="post" action="register.php" novalidate>
  <?= csrf_field() ?>
  <input type="hidden" name="next" value="<?= e($next) ?>">
  <div class="field <?= isset($errors['name']) ? 'invalid' : '' ?>"><label for="name">Full name</label>
    <input id="name" name="name" type="text" value="<?= e($name) ?>" required autocomplete="name"><?= ferr($errors, 'name') ?></div>
  <div class="field <?= isset($errors['email']) ? 'invalid' : '' ?>"><label for="email">Email address</label>
    <input id="email" name="email" type="email" value="<?= e($email) ?>" required autocomplete="email"><?= ferr($errors, 'email') ?></div>
  <div class="field <?= isset($errors['password']) ? 'invalid' : '' ?>"><label for="password">Password</label>
    <input id="password" name="password" type="password" required autocomplete="new-password"><?= ferr($errors, 'password') ?></div>
  <div class="field <?= isset($errors['confirm']) ? 'invalid' : '' ?>"><label for="confirm">Confirm password</label>
    <input id="confirm" name="confirm" type="password" required autocomplete="new-password"><?= ferr($errors, 'confirm') ?></div>
  <button type="submit" class="btn btn-block">Create account</button>
</form>
<p class="muted" style="margin-top:1.2rem">Already registered? <a href="login.php?next=<?= e(urlencode($next)) ?>">Log in</a></p>
<?php require __DIR__ . '/includes/auth_layout_bottom.php'; ?>
