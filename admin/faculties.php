<?php
require_once __DIR__ . '/../includes/auth.php';
require_role('admin');

$err = '';
if ($_SERVER['REQUEST_METHOD']==='POST'){
  if (!csrf_check($_POST['csrf'] ?? '')) { $err='Invalid CSRF token'; }
  else {
    $name = trim((string)($_POST['name'] ?? ''));
    if ($name==='') { $err='Name required'; }
    else {
      $stmt = mysqli_prepare($mysqli, 'INSERT INTO faculties (name) VALUES (?)');
      mysqli_stmt_bind_param($stmt, 's', $name);
      mysqli_stmt_execute($stmt);
      redirect('/admin/faculties.php');
    }
  }
}
$items = [];
$res = mysqli_query($mysqli, 'SELECT id, name FROM faculties ORDER BY name');
while ($res && $row = mysqli_fetch_assoc($res)) { $items[] = $row; }

define('APP_TITLE', 'Faculties - iSCSS');
include __DIR__ . '/../partials/head.php';
include __DIR__ . '/../partials/nav.php';
?>
<h1 class="h4 mb-3">Faculties</h1>
<?php if ($err): ?><div class="alert alert-danger py-2"><?php echo h($err); ?></div><?php endif; ?>
<form method="post" class="row g-2 mb-3">
  <input type="hidden" name="csrf" value="<?php echo h(csrf_token()); ?>">
  <div class="col-auto"><input class="form-control" name="name" placeholder="New faculty name"></div>
  <div class="col-auto"><button class="btn btn-primary">Add</button></div>
</form>
<ul class="list-group">
  <?php foreach ($items as $it): ?>
    <li class="list-group-item d-flex justify-content-between">
      <span><?php echo h($it['name']); ?></span>
      <a class="btn btn-sm btn-outline-danger" href="/admin/remove_faculty.php?id=<?php echo (int)$it['id']; ?>">Remove</a>
    </li>
  <?php endforeach; ?>
</ul>
<?php include __DIR__ . '/../partials/footer.php'; ?>
