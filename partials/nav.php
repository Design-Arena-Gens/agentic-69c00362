<?php require_once __DIR__ . '/../includes/config.php'; ?>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark fixed-top">
  <div class="container-fluid">
    <a class="navbar-brand" href="/">iSCSS</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarNav">
      <ul class="navbar-nav me-auto mb-2 mb-lg-0">
        <?php if (!empty($_SESSION['user_id'])): ?>
          <?php if ($_SESSION['role'] === 'admin'): ?>
            <li class="nav-item"><a class="nav-link" href="/admin/users.php">Users</a></li>
            <li class="nav-item"><a class="nav-link" href="/admin/faculties.php">Faculties</a></li>
            <li class="nav-item"><a class="nav-link" href="/admin/departments.php">Departments</a></li>
            <li class="nav-item"><a class="nav-link" href="/admin/threads.php">All Threads</a></li>
          <?php else: ?>
            <li class="nav-item"><a class="nav-link" href="/dashboard.php">Inbox</a></li>
            <?php if ($_SESSION['role'] === 'student'): ?>
              <li class="nav-item"><a class="nav-link" href="/student/new_thread.php">New Inquiry/Claim</a></li>
            <?php endif; ?>
          <?php endif; ?>
        <?php endif; ?>
      </ul>
      <ul class="navbar-nav">
        <?php if (empty($_SESSION['user_id'])): ?>
          <li class="nav-item"><a class="nav-link" href="/login.php">Login</a></li>
        <?php else: ?>
          <li class="nav-item"><span class="navbar-text me-3"><?php echo h($_SESSION['name'] ?? ''); ?> (<?php echo h($_SESSION['role']); ?>)</span></li>
          <li class="nav-item"><a class="nav-link" href="/logout.php">Logout</a></li>
        <?php endif; ?>
      </ul>
    </div>
  </div>
</nav>
<div class="container">