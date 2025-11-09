<?php
require_once __DIR__ . '/includes/auth.php';
require_login();

$role = $_SESSION['role'];
if ($role === 'admin') {
  redirect('/admin/users.php');
}

define('APP_TITLE', 'Dashboard - iSCSS');
include __DIR__ . '/partials/head.php';
include __DIR__ . '/partials/nav.php';

$user_id = $_SESSION['user_id'];

// Fetch threads where user is a participant (or creator)
$sql = "SELECT t.id, t.subject, t.type, t.created_at,
       (SELECT body FROM messages m WHERE m.thread_id = t.id ORDER BY m.created_at DESC, m.id DESC LIMIT 1) AS last_msg,
       (SELECT created_at FROM messages m WHERE m.thread_id = t.id ORDER BY m.created_at DESC, m.id DESC LIMIT 1) AS last_time
        FROM threads t
        JOIN thread_participants p ON p.thread_id = t.id
        WHERE p.user_id = ?
        GROUP BY t.id
        ORDER BY COALESCE(last_time, t.created_at) DESC";
$threads = [];
if ($stmt = mysqli_prepare($mysqli, $sql)) {
  mysqli_stmt_bind_param($stmt, 'i', $user_id);
  mysqli_stmt_execute($stmt);
  $res = mysqli_stmt_get_result($stmt);
  while ($row = mysqli_fetch_assoc($res)) { $threads[] = $row; }
}
?>
<div class="d-flex justify-content-between align-items-center mb-3">
  <h1 class="h4 mb-0">Inbox</h1>
  <?php if ($role === 'student'): ?>
    <a href="/student/new_thread.php" class="btn btn-primary">New Inquiry/Claim</a>
  <?php endif; ?>
</div>
<div class="list-group">
  <?php if (!$threads): ?>
    <div class="text-muted">No conversations yet.</div>
  <?php endif; ?>
  <?php foreach ($threads as $t): ?>
    <a class="list-group-item list-group-item-action" href="/thread.php?id=<?php echo (int)$t['id']; ?>">
      <div class="d-flex w-100 justify-content-between">
        <h5 class="mb-1"><?php echo h($t['subject']); ?> <span class="badge bg-secondary text-uppercase"><?php echo h($t['type']); ?></span></h5>
        <small><?php echo h($t['last_time'] ?: $t['created_at']); ?></small>
      </div>
      <p class="mb-1 text-truncate"><?php echo h($t['last_msg'] ?: 'No messages yet'); ?></p>
    </a>
  <?php endforeach; ?>
</div>
<?php include __DIR__ . '/partials/footer.php'; ?>
