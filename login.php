<?php
require_once __DIR__ . '/includes/config.php';

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_check($_POST['csrf'] ?? '')) {
        $error = 'Invalid CSRF token';
    } else {
        $login_type = $_POST['login_type'] ?? 'student'; // 'admin'|'student'|'teacher'|'staff'
        $identifier = trim((string)($_POST['identifier'] ?? ''));
        $password = (string)($_POST['password'] ?? '');
        if ($identifier === '' || $password === '') {
            $error = 'Please fill all fields';
        } else {
            $sql = "SELECT id, role, login, reg_no, name, email, password_hash, is_active FROM users WHERE ";
            if ($login_type === 'student') {
                $sql .= "role='student' AND reg_no = ?";
            } else {
                $sql .= "role = ? AND login = ?";
            }
            if ($stmt = mysqli_prepare($mysqli, $sql)) {
                if ($login_type === 'student') {
                    mysqli_stmt_bind_param($stmt, 's', $identifier);
                } else {
                    mysqli_stmt_bind_param($stmt, 'ss', $login_type, $identifier);
                }
                mysqli_stmt_execute($stmt);
                $res = mysqli_stmt_get_result($stmt);
                if ($row = mysqli_fetch_assoc($res)) {
                    if ((int)$row['is_active'] !== 1) {
                        $error = 'Account inactive. Contact admin.';
                    } elseif (password_verify($password, $row['password_hash'])) {
                        $_SESSION['user_id'] = (int)$row['id'];
                        $_SESSION['role'] = $row['role'];
                        $_SESSION['name'] = $row['name'];
                        redirect('/dashboard.php');
                    } else {
                        $error = 'Invalid credentials';
                    }
                } else {
                    $error = 'Invalid credentials';
                }
            } else {
                $error = 'Server error';
            }
        }
    }
}

define('APP_TITLE', 'Login - iSCSS');
include __DIR__ . '/partials/head.php';
include __DIR__ . '/partials/nav.php';
?>
<div class="row justify-content-center">
  <div class="col-md-6 col-lg-5">
    <div class="card shadow-sm">
      <div class="card-body">
        <h1 class="h4 mb-3">Login</h1>
        <?php if ($error): ?>
          <div class="alert alert-danger py-2"><?php echo h($error); ?></div>
        <?php endif; ?>
        <form method="post">
          <input type="hidden" name="csrf" value="<?php echo h(csrf_token()); ?>">
          <div class="mb-3">
            <label class="form-label">Login as</label>
            <select name="login_type" class="form-select" id="login_type" onchange="updateLabel()">
              <option value="student">Student</option>
              <option value="teacher">Teacher</option>
              <option value="staff">Office Staff</option>
              <option value="admin">Admin</option>
            </select>
          </div>
          <div class="mb-3">
            <label id="identifier_label" class="form-label">Registration Number</label>
            <input type="text" class="form-control" name="identifier" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Password</label>
            <input type="password" class="form-control" name="password" required>
          </div>
          <button class="btn btn-primary w-100" type="submit">Login</button>
        </form>
      </div>
    </div>
  </div>
</div>
<script>
function updateLabel(){
  const t = document.getElementById('login_type').value;
  document.getElementById('identifier_label').innerText = (t==='student') ? 'Registration Number' : 'Username';
}
updateLabel();
</script>
<?php include __DIR__ . '/partials/footer.php'; ?>
