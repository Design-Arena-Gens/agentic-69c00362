<?php
require_once __DIR__ . '/config.php';

function current_user_id() {
    return isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : 0;
}

function current_user_role() {
    return isset($_SESSION['role']) ? $_SESSION['role'] : '';
}

function require_login() {
    if (!current_user_id()) {
        redirect('/login.php');
    }
}

function require_role($role) {
    require_login();
    if (current_user_role() !== $role) {
        http_response_code(403);
        die('Forbidden');
    }
}

function require_any_role($roles) {
    require_login();
    if (!in_array(current_user_role(), $roles, true)) {
        http_response_code(403);
        die('Forbidden');
    }
}
