<?php

function start_app_session() {
    if (session_status() === PHP_SESSION_ACTIVE) {
        return;
    }
    session_start();
}

function csrf_token() {
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}

function csrf_field() {
    return '<input type="hidden" name="csrf" value="' . e(csrf_token()) . '">';
}

function csrf_ok() {
    $sent = isset($_POST['csrf']) ? (string)$_POST['csrf'] : '';
    $saved = isset($_SESSION['csrf']) ? (string)$_SESSION['csrf'] : '';
    if ($sent === '' || $saved === '') {
        return false;
    }
    return hash_equals($saved, $sent);
}

function require_login() {
    if (empty($_SESSION['user_id'])) {
        redirect('login.php');
    }
}
