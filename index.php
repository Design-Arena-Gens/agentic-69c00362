<?php
require_once __DIR__ . '/includes/config.php';

if (!empty($_SESSION['user_id'])) {
    redirect('/dashboard.php');
}

define('APP_TITLE', 'iSCSS - Welcome');
include __DIR__ . '/partials/head.php';
include __DIR__ . '/partials/nav.php';
?>
<div class="row justify-content-center">
  <div class="col-lg-6">
    <div class="p-4 bg-light rounded shadow-sm mt-5">
      <h1 class="h3 mb-3">Welcome to iSCSS</h1>
      <p class="mb-4">Student Collaboration and Support System</p>
      <a class="btn btn-primary" href="/login.php">Login</a>
    </div>
  </div>
</div>
<?php include __DIR__ . '/partials/footer.php'; ?>
