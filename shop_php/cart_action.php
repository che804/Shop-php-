<?php
require __DIR__ . '/includes/functions.php';
if ($_SERVER['REQUEST_METHOD'] !== 'POST') redirect('index.php');
csrf_check();

$id     = (int)($_POST['id'] ?? 0);
$action = $_POST['action'] ?? '';
$back   = safe_back($_POST['back'] ?? '');
$qty    = max(1, min(20, (int)($_POST['qty'] ?? 1)));

if (find_product($id)) {
    $cart = $_SESSION['cart'] ?? [];
    switch ($action) {
        case 'add':    $cart[$id] = min(99, ($cart[$id] ?? 0) + $qty); break;
        case 'inc':    if (isset($cart[$id])) $cart[$id] = min(99, $cart[$id] + 1); break;
        case 'dec':    if (isset($cart[$id])) { $cart[$id]--; if ($cart[$id] <= 0) unset($cart[$id]); } break;
        case 'remove': unset($cart[$id]); break;
    }
    $_SESSION['cart'] = $cart;
}
redirect(url_with($back, 'cart', 1));
