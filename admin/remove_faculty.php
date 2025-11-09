<?php
require_once __DIR__ . '/../includes/auth.php';
require_role('admin');
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id>0) {
  $stmt = mysqli_prepare($mysqli, 'DELETE FROM faculties WHERE id=?');
  mysqli_stmt_bind_param($stmt, 'i', $id);
  mysqli_stmt_execute($stmt);
}
redirect('/admin/faculties.php');
