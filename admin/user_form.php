<?php
require_once __DIR__ . '/../includes/auth.php';
require_role('admin');

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$editing = $id > 0;
$roles = ['admin','student','teacher','staff'];
$errors = [];

// Load faculties and departments
$faculties = [];$departments=[];
$res = mysqli_query($mysqli, "SELECT id, name FROM faculties ORDER BY name");
while ($res && $row = mysqli_fetch_assoc($res)) { $faculties[] = $row; }
$res2 = mysqli_query($mysqli, "SELECT id, name FROM departments ORDER BY name");
while ($res2 && $row = mysqli_fetch_assoc($res2)) { $departments[] = $row; }

$user = ['role'=>'student','login'=>'','reg_no'=>'','name'=>'','email'=>'','faculty_id'=>null,'department_id'=>null,'is_active'=>1];
if ($editing) {
  $stmt = mysqli_prepare($mysqli, "SELECT role, login, reg_no, name, email, faculty_id, department_id, is_active FROM users WHERE id=?");
  mysqli_stmt_bind_param($stmt, 'i', $id);
  mysqli_stmt_execute($stmt);
  $resu = mysqli_stmt_get_result($stmt);
  $user = mysqli_fetch_assoc($resu);
  if (!$user) { die('User not found'); }
}

if ($_SERVER['REQUEST_METHOD']==='POST') {
  if (!csrf_check($_POST['csrf'] ?? '')) { $errors[]='Invalid CSRF token'; }
  $role = in_array($_POST['role'] ?? '', $roles, true) ? $_POST['role'] : 'student';
  $login = trim((string)($_POST['login'] ?? ''));
  $reg_no = trim((string)($_POST['reg_no'] ?? ''));
  $name = trim((string)($_POST['name'] ?? ''));
  $email = trim((string)($_POST['email'] ?? ''));
  $faculty_id = (int)($_POST['faculty_id'] ?? 0); if ($faculty_id<=0) $faculty_id = null;
  $department_id = (int)($_POST['department_id'] ?? 0); if ($department_id<=0) $department_id = null;
  $is_active = isset($_POST['is_active']) ? 1 : 0;
  $password = (string)($_POST['password'] ?? '');

  if ($name==='') $errors[]='Name required';
  if ($role==='student') { if ($reg_no==='') $errors[]='Registration number required'; }
  else { if ($login==='') $errors[]='Username required'; }
  if (!$editing && $password==='') $errors[]='Password required';

  if (!$errors) {
    if ($editing) {
      if ($password!=='') {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $sql = "UPDATE users SET role=?, login=?, reg_no=?, name=?, email=?, faculty_id=?, department_id=?, is_active=?, password_hash=? WHERE id=?";
        $stmt = mysqli_prepare($mysqli, $sql);
        // types: role(s) login(s) reg_no(s) name(s) email(s) faculty(i) department(i) active(i) hash(s) id(i)
        mysqli_stmt_bind_param($stmt, 'sssssiiisi', $role, $login, $reg_no, $name, $email, $faculty_id, $department_id, $is_active, $hash, $id);
      } else {
        $sql = "UPDATE users SET role=?, login=?, reg_no=?, name=?, email=?, faculty_id=?, department_id=?, is_active=? WHERE id=?";
        $stmt = mysqli_prepare($mysqli, $sql);
        // types: 5 strings + 3 ints + id int
        mysqli_stmt_bind_param($stmt, 'sssssiiii', $role, $login, $reg_no, $name, $email, $faculty_id, $department_id, $is_active, $id);
      }
      mysqli_stmt_execute($stmt);
    } else {
      $hash = password_hash($password, PASSWORD_DEFAULT);
      $sql = "INSERT INTO users (role, login, reg_no, name, email, faculty_id, department_id, is_active, password_hash) VALUES (?,?,?,?,?,?,?,?,?)";
      $stmt = mysqli_prepare($mysqli, $sql);
      // types: 5 strings + 3 ints + 1 string
      mysqli_stmt_bind_param($stmt, 'sssssiiis', $role, $login, $reg_no, $name, $email, $faculty_id, $department_id, $is_active, $hash);
      mysqli_stmt_execute($stmt);
    }
    redirect('/admin/users.php');
  } else {
    $user = ['role'=>$role,'login'=>$login,'reg_no'=>$reg_no,'name'=>$name,'email'=>$email,'faculty_id'=>$faculty_id,'department_id'=>$department_id,'is_active'=>$is_active];
  }
}

define('APP_TITLE', ($editing?'Edit':'Add') . ' User - iSCSS');
include __DIR__ . '/../partials/head.php';
include __DIR__ . '/../partials/nav.php';
?>
<h1 class="h4 mb-3"><?php echo $editing?'Edit':'Add'; ?> User</h1>
<?php if ($errors): ?><div class="alert alert-danger py-2"><?php echo h(implode("\n", $errors)); ?></div><?php endif; ?>
<form method="post" class="card">
  <div class="card-body">
    <input type="hidden" name="csrf" value="<?php echo h(csrf_token()); ?>">
    <div class="row g-3">
      <div class="col-md-3">
        <label class="form-label">Role</label>
        <select name="role" class="form-select">
          <?php foreach ($roles as $r): ?>
            <option value="<?php echo h($r); ?>" <?php echo $user['role']===$r?'selected':''; ?>><?php echo ucfirst($r); ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-3">
        <label class="form-label">Username (for Admin/Teacher/Staff)</label>
        <input type="text" class="form-control" name="login" value="<?php echo h($user['login']); ?>">
      </div>
      <div class="col-md-3">
        <label class="form-label">Registration Number (for Students)</label>
        <input type="text" class="form-control" name="reg_no" value="<?php echo h($user['reg_no']); ?>">
      </div>
      <div class="col-md-3">
        <label class="form-label">Active</label>
        <div class="form-check form-switch mt-2">
          <input class="form-check-input" type="checkbox" name="is_active" <?php echo ((int)$user['is_active']===1)?'checked':''; ?>>
          <label class="form-check-label">Enable account</label>
        </div>
      </div>
      <div class="col-md-6">
        <label class="form-label">Full Name</label>
        <input type="text" class="form-control" name="name" value="<?php echo h($user['name']); ?>">
      </div>
      <div class="col-md-6">
        <label class="form-label">Email</label>
        <input type="email" class="form-control" name="email" value="<?php echo h($user['email']); ?>">
      </div>
      <div class="col-md-6">
        <label class="form-label">Faculty</label>
        <select class="form-select" name="faculty_id">
          <option value="">-- None --</option>
          <?php foreach ($faculties as $f): ?>
            <option value="<?php echo (int)$f['id']; ?>" <?php echo ((int)$user['faculty_id']===(int)$f['id'])?'selected':''; ?>><?php echo h($f['name']); ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-6">
        <label class="form-label">Department</label>
        <select class="form-select" name="department_id">
          <option value="">-- None --</option>
          <?php foreach ($departments as $d): ?>
            <option value="<?php echo (int)$d['id']; ?>" <?php echo ((int)$user['department_id']===(int)$d['id'])?'selected':''; ?>><?php echo h($d['name']); ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-6">
        <label class="form-label">Password <?php echo $editing?'(leave blank to keep)':''; ?></label>
        <input type="password" class="form-control" name="password">
      </div>
    </div>
  </div>
  <div class="card-footer d-flex justify-content-between">
    <a href="/admin/users.php" class="btn btn-outline-secondary">Cancel</a>
    <button class="btn btn-primary" type="submit"><?php echo $editing?'Save Changes':'Create User'; ?></button>
  </div>
</form>
<?php include __DIR__ . '/../partials/footer.php'; ?>
