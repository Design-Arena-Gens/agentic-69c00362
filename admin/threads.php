<?php
require_once __DIR__ . '/../includes/auth.php';
require_role('admin');

define('APP_TITLE', 'All Threads - iSCSS');
include __DIR__ . '/../partials/head.php';
include __DIR__ . '/../partials/nav.php';

$threads = [];
$sql = "SELECT t.id, t.subject, t.type, t.created_at,
       u.name AS creator_name, u.role AS creator_role,
       (SELECT body FROM messages m WHERE m.thread_id = t.id ORDER BY m.created_at DESC, m.id DESC LIMIT 1) AS last_msg,
       (SELECT created_at FROM messages m WHERE m.thread_id = t.id ORDER BY m.created_at DESC, m.id DESC LIMIT 1) AS last_time
        FROM threads t JOIN users u ON u.id = t.created_by
        ORDER BY COALESCE(last_time, t.created_at) DESC";
$res = mysqli_query($mysqli, $sql);
while ($res && $row = mysqli_fetch_assoc($res)) { $threads[] = $row; }
?>
<h1 class="h4 mb-3">All Conversations</h1>
<div class="list-group">
  <?php foreach ($threads as $t): ?>
    <a class="list-group-item list-group-item-action" href="/thread.php?id=<?php echo (int)$t['id']; ?>">
      <div class="d-flex w-100 justify-content-between">
        <h5 class="mb-1"><?php echo h($t['subject']); ?> <span class="badge bg-secondary text-uppercase"><?php echo h($t['type']); ?></span></h5>
        <small><?php echo h($t['last_time'] ?: $t['created_at']); ?></small>
      </div>
      <small class="text-muted">By <?php echo h($t['creator_name'] . ' (' . $t['creator_role'] . ')'); ?></small>
      <p class="mb-1 text-truncate"><?php echo h($t['last_msg'] ?: 'No messages yet'); ?></p>
    </a>
  <?php endforeach; ?>
</div>
<?php include __DIR__ . '/../partials/footer.php'; ?>
