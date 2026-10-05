<?php /* expects $pageTitle, $artTitle, $artText */
$noDrawer = true; require __DIR__ . '/header.php'; ?>
<div class="auth">
  <div class="auth-art">
    <h1><?= e($artTitle) ?></h1>
    <p><?= e($artText) ?></p>
  </div>
  <div class="auth-form"><div class="box">
