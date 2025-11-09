<?php
require_once __DIR__ . '/../includes/auth.php';
require_role('admin');
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id>0) {
  mysqli_query($mysqli, "UPDATE users SET is_active = 1 - is_active WHERE id = " . $id);
}
redirect('/admin/users.php');
