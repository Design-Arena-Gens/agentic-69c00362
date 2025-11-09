<?php
require_once __DIR__ . '/includes/auth.php';
require_login();

$thread_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($thread_id <= 0) { http_response_code(404); die('Not found'); }

// Check participant access
$has_access = false;
$sql = "SELECT 1 FROM thread_participants WHERE thread_id = ? AND user_id = ? LIMIT 1";
if ($stmt = mysqli_prepare($mysqli, $sql)) {
  mysqli_stmt_bind_param($stmt, 'ii', $thread_id, $_SESSION['user_id']);
  mysqli_stmt_execute($stmt);
  $res = mysqli_stmt_get_result($stmt);
  $has_access = (bool)mysqli_fetch_row($res);
}
if (!$has_access && $_SESSION['role'] !== 'admin') {
  http_response_code(403); die('Forbidden');
}

// Send message
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['body'])) {
  if (!csrf_check($_POST['csrf'] ?? '')) { $error = 'Invalid CSRF token'; }
  else {
    $body = trim((string)$_POST['body']);
    if ($body === '') { $error = 'Message cannot be empty'; }
    else {
      $sql = "INSERT INTO messages (thread_id, sender_id, body) VALUES (?,?,?)";
      if ($stmt = mysqli_prepare($mysqli, $sql)) {
        mysqli_stmt_bind_param($stmt, 'iis', $thread_id, $_SESSION['user_id'], $body);
        mysqli_stmt_execute($stmt);
        redirect('/thread.php?id=' . $thread_id);
      } else { $error = 'Server error'; }
    }
  }
}

// Load thread
$thread = null;
$sql = "SELECT id, subject, type, created_at FROM threads WHERE id = ?";
if ($stmt = mysqli_prepare($mysqli, $sql)) {
  mysqli_stmt_bind_param($stmt, 'i', $thread_id);
  mysqli_stmt_execute($stmt);
  $res = mysqli_stmt_get_result($stmt);
  $thread = mysqli_fetch_assoc($res);
}

// Load messages
$messages = [];
$sql = "SELECT m.id, m.body, m.created_at, u.name, u.role, u.id AS uid
        FROM messages m JOIN users u ON u.id = m.sender_id
        WHERE m.thread_id = ? ORDER BY m.created_at ASC, m.id ASC";
if ($stmt = mysqli_prepare($mysqli, $sql)) {
  mysqli_stmt_bind_param($stmt, 'i', $thread_id);
  mysqli_stmt_execute($stmt);
  $res = mysqli_stmt_get_result($stmt);
  while ($row = mysqli_fetch_assoc($res)) { $messages[] = $row; }
}

define('APP_TITLE', 'Thread - ' . ($thread ? $thread['subject'] : '')); 
include __DIR__ . '/partials/head.php';
include __DIR__ . '/partials/nav.php';
?>
<div class="mb-3">
  <h1 class="h4 mb-0"><?php echo h($thread['subject'] ?? ''); ?> <span class="badge bg-secondary text-uppercase"><?php echo h($thread['type'] ?? ''); ?></span></h1>
</div>
<div class="mb-3">
  <?php foreach ($messages as $m): $out = ($m['uid'] == $_SESSION['user_id']); ?>
    <div class="mb-2 d-flex <?php echo $out ? 'justify-content-end' : 'justify-content-start'; ?>">
      <div class="message-bubble <?php echo $out ? 'message-out' : 'message-in'; ?>">
        <div class="small text-muted mb-1"><?php echo h($m['name'] . ' (' . $m['role'] . ') ? ' . $m['created_at']); ?></div>
        <div><?php echo nl2br(h($m['body'])); ?></div>
      </div>
    </div>
  <?php endforeach; ?>
</div>
<div class="card">
  <div class="card-body">
    <?php if ($error): ?><div class="alert alert-danger py-2"><?php echo h($error); ?></div><?php endif; ?>
    <form method="post">
      <input type="hidden" name="csrf" value="<?php echo h(csrf_token()); ?>">
      <div class="mb-3"><textarea name="body" class="form-control" rows="3" placeholder="Type your message..."></textarea></div>
      <button class="btn btn-primary">Send</button>
    </form>
  </div>
</div>
<?php include __DIR__ . '/partials/footer.php'; ?>
