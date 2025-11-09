<?php
require_once __DIR__ . '/../includes/auth.php';
require_login();
if ($_SESSION['role'] !== 'student') { http_response_code(403); die('Forbidden'); }

$errors = [];
$subject = '';
$type = 'inquiry';
$target = 'teacher';
$recipients = [];
$message = '';

// Load recipient lists
$teachers = [];$staff = [];
$res = mysqli_query($mysqli, "SELECT id, name FROM users WHERE role='teacher' AND is_active=1 ORDER BY name");
while ($row = $res && mysqli_fetch_assoc($res)) { $teachers[] = $row; }
$res2 = mysqli_query($mysqli, "SELECT id, name FROM users WHERE role='staff' AND is_active=1 ORDER BY name");
while ($row = $res2 && mysqli_fetch_assoc($res2)) { $staff[] = $row; }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if (!csrf_check($_POST['csrf'] ?? '')) { $errors[] = 'Invalid CSRF token'; }
  $subject = trim((string)($_POST['subject'] ?? ''));
  $type = ($_POST['type'] ?? 'inquiry') === 'claim' ? 'claim' : 'inquiry';
  $target = in_array($_POST['target'] ?? 'teacher', ['teacher','staff'], true) ? $_POST['target'] : 'teacher';
  $recipients = array_map('intval', $_POST['recipients'] ?? []);
  $message = trim((string)($_POST['message'] ?? ''));

  if ($subject === '') $errors[] = 'Subject is required';
  if (!$recipients) $errors[] = 'Select at least one recipient';
  if ($message === '') $errors[] = 'Initial message is required';

  if (!$errors) {
    // Create thread
    $sql = "INSERT INTO threads (type, subject, created_by) VALUES (?,?,?)";
    if ($stmt = mysqli_prepare($mysqli, $sql)) {
      mysqli_stmt_bind_param($stmt, 'ssi', $type, $subject, $_SESSION['user_id']);
      mysqli_stmt_execute($stmt);
      $thread_id = mysqli_insert_id($mysqli);
      // participants: creator + recipients
      $ins = mysqli_prepare($mysqli, "INSERT INTO thread_participants (thread_id, user_id) VALUES (?,?)");
      // add creator
      mysqli_stmt_bind_param($ins, 'ii', $thread_id, $_SESSION['user_id']);
      mysqli_stmt_execute($ins);
      foreach ($recipients as $uid) {
        mysqli_stmt_bind_param($ins, 'ii', $thread_id, $uid);
        mysqli_stmt_execute($ins);
      }
      // initial message
      $insm = mysqli_prepare($mysqli, "INSERT INTO messages (thread_id, sender_id, body) VALUES (?,?,?)");
      mysqli_stmt_bind_param($insm, 'iis', $thread_id, $_SESSION['user_id'], $message);
      mysqli_stmt_execute($insm);

      redirect('/thread.php?id=' . $thread_id);
    } else {
      $errors[] = 'Server error';
    }
  }
}

define('APP_TITLE', 'New Inquiry/Claim - iSCSS');
include __DIR__ . '/../partials/head.php';
include __DIR__ . '/../partials/nav.php';
?>
<h1 class="h4 mb-3">Create Inquiry/Claim</h1>
<?php if ($errors): ?><div class="alert alert-danger py-2"><?php echo h(implode('\n', $errors)); ?></div><?php endif; ?>
<form method="post" class="card">
  <div class="card-body">
    <input type="hidden" name="csrf" value="<?php echo h(csrf_token()); ?>">
    <div class="row g-3">
      <div class="col-md-8">
        <label class="form-label">Subject</label>
        <input type="text" class="form-control" name="subject" value="<?php echo h($subject); ?>">
      </div>
      <div class="col-md-4">
        <label class="form-label">Type</label>
        <select class="form-select" name="type">
          <option value="inquiry" <?php echo $type==='inquiry'?'selected':''; ?>>Inquiry</option>
          <option value="claim" <?php echo $type==='claim'?'selected':''; ?>>Claim</option>
        </select>
      </div>
      <div class="col-md-4">
        <label class="form-label">Target</label>
        <select class="form-select" name="target" id="target" onchange="toggleRecipients()">
          <option value="teacher" <?php echo $target==='teacher'?'selected':''; ?>>Teachers</option>
          <option value="staff" <?php echo $target==='staff'?'selected':''; ?>>Office Staff</option>
        </select>
      </div>
      <div class="col-md-8">
        <label class="form-label">Recipients</label>
        <div id="teacher_list" style="display: <?php echo $target==='teacher'?'block':'none'; ?>">
          <div class="d-flex flex-wrap gap-3">
            <?php foreach ($teachers as $t): ?>
              <div class="form-check">
                <input class="form-check-input" type="checkbox" name="recipients[]" value="<?php echo (int)$t['id']; ?>" id="t<?php echo (int)$t['id']; ?>">
                <label class="form-check-label" for="t<?php echo (int)$t['id']; ?>"><?php echo h($t['name']); ?></label>
              </div>
            <?php endforeach; ?>
          </div>
        </div>
        <div id="staff_list" style="display: <?php echo $target==='staff'?'block':'none'; ?>">
          <div class="d-flex flex-wrap gap-3">
            <?php foreach ($staff as $s): ?>
              <div class="form-check">
                <input class="form-check-input" type="checkbox" name="recipients[]" value="<?php echo (int)$s['id']; ?>" id="s<?php echo (int)$s['id']; ?>">
                <label class="form-check-label" for="s<?php echo (int)$s['id']; ?>"><?php echo h($s['name']); ?></label>
              </div>
            <?php endforeach; ?>
          </div>
        </div>
      </div>
      <div class="col-12">
        <label class="form-label">Initial Message</label>
        <textarea name="message" rows="4" class="form-control"><?php echo h($message); ?></textarea>
      </div>
    </div>
  </div>
  <div class="card-footer text-end">
    <button class="btn btn-primary">Create</button>
  </div>
</form>
<script>
function toggleRecipients(){
  const v = document.getElementById('target').value;
  document.getElementById('teacher_list').style.display = (v==='teacher')?'block':'none';
  document.getElementById('staff_list').style.display = (v==='staff')?'block':'none';
}
</script>
<?php include __DIR__ . '/../partials/footer.php'; ?>
