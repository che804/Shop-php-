<?php
session_start();

const SHIPPING_FLAT = 6;
const ORDER_STATUSES = ['pending','processing','shipped','delivered','cancelled'];

/* ---------- basics ---------- */
function e($v) { return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }
function money($n) { return '$' . number_format((float)$n, 2); }
function redirect($url) { header('Location: ' . $url); exit; }

function db() {
    global $pdo;
    if (!isset($pdo)) require __DIR__ . '/db.php';
    return $pdo;
}

/* ---------- CSRF + flash ---------- */
function csrf_token() {
    if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(32));
    return $_SESSION['csrf'];
}
function csrf_field() { return '<input type="hidden" name="csrf" value="' . e(csrf_token()) . '">'; }
function csrf_check() {
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && !hash_equals($_SESSION['csrf'] ?? '', (string)($_POST['csrf'] ?? ''))) {
        http_response_code(419);
        exit('Your session expired. Go back, refresh the page and try again.');
    }
}
function flash($msg, $type = 'success') { $_SESSION['flash'] = [$msg, $type]; }
function take_flash() { $f = $_SESSION['flash'] ?? null; unset($_SESSION['flash']); return $f; }

/* ---------- URLs ---------- */
function safe_back($u) {
    if (!is_string($u) || $u === '' || $u[0] !== '/' || strpos($u, '//') === 0
        || strpos($u, '\\') !== false || preg_match('/[\r\n]/', $u)) return 'index.php';
    return $u;
}
function url_with($url, $key, $val) {
    $p = parse_url($url); parse_str($p['query'] ?? '', $q);
    if ($val === null) unset($q[$key]); else $q[$key] = $val;
    return ($p['path'] ?? '') . ($q ? '?' . http_build_query($q) : '');
}
function current_url() { return safe_back($_SERVER['REQUEST_URI'] ?? '/'); }
function safe_next($n) { return preg_match('/^(admin\/)?[a-z_]+\.php$/', (string)$n) ? $n : 'index.php'; }
function img_src($image, $base = '') { return preg_match('#^https?://#', $image) ? $image : $base . $image; }

/* ---------- products ---------- */
function normalize_product($p) { $p['id'] = (int)$p['id']; $p['price'] = (float)$p['price']; return $p; }

function fetch_products($cat = '', $q = '', $sort = 'new') {
    $sql = 'SELECT * FROM products WHERE is_active = 1'; $args = [];
    if ($cat !== '' && $cat !== 'All') { $sql .= ' AND category = ?'; $args[] = $cat; }
    if ($q !== '') { $sql .= ' AND name LIKE ?'; $args[] = '%' . addcslashes($q, '%_\\') . '%'; }
    $order = ['new' => 'id DESC', 'low' => 'price ASC', 'high' => 'price DESC', 'name' => 'name ASC'];
    $sql .= ' ORDER BY ' . ($order[$sort] ?? $order['new']);
    $st = db()->prepare($sql); $st->execute($args);
    return array_map('normalize_product', $st->fetchAll());
}
function find_product($id, $onlyActive = true) {
    $st = db()->prepare('SELECT * FROM products WHERE id = ?' . ($onlyActive ? ' AND is_active = 1' : ''));
    $st->execute([(int)$id]);
    $r = $st->fetch();
    return $r ? normalize_product($r) : null;
}
function categories() {
    return db()->query('SELECT DISTINCT category FROM products WHERE is_active = 1 ORDER BY category')->fetchAll(PDO::FETCH_COLUMN);
}

/* ---------- cart (session: product_id => qty) ---------- */
function cart_lines() {
    $lines = [];
    foreach (($_SESSION['cart'] ?? []) as $id => $qty) {
        $p = find_product($id);
        if ($p && $qty > 0) { $p['qty'] = (int)$qty; $lines[] = $p; }
    }
    return $lines;
}
function cart_total($lines) { return array_sum(array_map(fn($i) => $i['price'] * $i['qty'], $lines)); }
function cart_count($lines) { return array_sum(array_column($lines, 'qty')); }

/* ---------- auth ---------- */
function current_user() {
    static $user = false;
    if ($user !== false) return $user;
    $user = null;
    if (!empty($_SESSION['user_id'])) {
        $st = db()->prepare('SELECT id, name, email, role FROM users WHERE id = ?');
        $st->execute([(int)$_SESSION['user_id']]);
        $user = $st->fetch() ?: null;
        if (!$user) unset($_SESSION['user_id']);
    }
    return $user;
}
function is_admin() { $u = current_user(); return $u && $u['role'] === 'admin'; }
function login_user($id) { session_regenerate_id(true); $_SESSION['user_id'] = (int)$id; }
function require_login($next = '') {
    if (!current_user()) redirect('login.php' . ($next ? '?next=' . urlencode($next) : ''));
}
function require_admin() {
    if (!current_user()) redirect('../login.php?next=' . urlencode('admin/index.php'));
    if (!is_admin()) { http_response_code(403); exit('403 — Admins only. <a href="../index.php">Back to shop</a>'); }
}

/* ---------- uploads ---------- */
function save_upload($file, $dir) {
    if (!$file || $file['error'] === UPLOAD_ERR_NO_FILE) return null;
    if ($file['error'] !== UPLOAD_ERR_OK) throw new RuntimeException('The image could not be uploaded.');
    if ($file['size'] > 2 * 1024 * 1024) throw new RuntimeException('Image must be smaller than 2 MB.');
    $info = @getimagesize($file['tmp_name']);
    $ext = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'][$info['mime'] ?? ''] ?? null;
    if (!$ext) throw new RuntimeException('Use a JPG, PNG or WebP image.');
    $name = bin2hex(random_bytes(8)) . '.' . $ext;
    if (!move_uploaded_file($file['tmp_name'], rtrim($dir, '/') . '/' . $name)) throw new RuntimeException('Could not save the image.');
    return 'assets/uploads/' . $name;
}
