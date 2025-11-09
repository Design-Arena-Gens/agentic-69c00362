<?php
require_once __DIR__ . '/../includes/auth.php';
require_role('admin');

define('APP_TITLE', 'Users - iSCSS');
include __DIR__ . '/../partials/head.php';
include __DIR__ . '/../partials/nav.php';

$users = [];
$sql = "SELECT u.id, u.role, u.login, u.reg_no, u.name, u.email, u.is_active, f.name AS fac, d.name AS dept
        FROM users u
        LEFT JOIN faculties f ON f.id = u.faculty_id
        LEFT JOIN departments d ON d.id = u.department_id
        ORDER BY u.role, u.name";
$res = mysqli_query($mysqli, $sql);
while ($res && $row = mysqli_fetch_assoc($res)) { $users[] = $row; }
?>
<div class="d-flex justify-content-between align-items-center mb-3">
  <h1 class="h4 mb-0">Users</h1>
  <a class="btn btn-primary" href="/admin/user_form.php">Add User</a>
</div>
<div class="table-responsive">
  <table class="table table-striped align-middle">
    <thead><tr>
      <th>ID</th><th>Role</th><th>Username/Reg No</th><th>Name</th><th>Email</th><th>Faculty</th><th>Department</th><th>Status</th><th>Actions</th>
    </tr></thead>
    <tbody>
      <?php foreach ($users as $u): ?>
        <tr>
          <td><?php echo (int)$u['id']; ?></td>
          <td class="text-uppercase small"><?php echo h($u['role']); ?></td>
          <td><?php echo h($u['role']==='student' ? ($u['reg_no'] ?: '-') : ($u['login'] ?: '-')); ?></td>
          <td><?php echo h($u['name']); ?></td>
          <td><?php echo h($u['email']); ?></td>
          <td><?php echo h($u['fac'] ?: '-'); ?></td>
          <td><?php echo h($u['dept'] ?: '-'); ?></td>
          <td><?php echo ((int)$u['is_active']===1)?'<span class="badge bg-success">Active</span>':'<span class="badge bg-secondary">Inactive</span>'; ?></td>
          <td>
            <a href="/admin/user_form.php?id=<?php echo (int)$u['id']; ?>" class="btn btn-sm btn-outline-primary">Edit</a>
            <a href="/admin/toggle_user.php?id=<?php echo (int)$u['id']; ?>" class="btn btn-sm btn-outline-warning">Toggle</a>
          </td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>
<?php include __DIR__ . '/../partials/footer.php'; ?>
