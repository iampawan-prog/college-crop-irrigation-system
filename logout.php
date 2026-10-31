<?php
require __DIR__ . '/includes/init.php';
require_login();

if ($_SERVER['REQUEST_METHOD'] != 'POST' || !csrf_ok()) {
    redirect('dashboard.php');
}

$_SESSION = array();
session_destroy();
start_app_session();
flash('success', 'You have logged out.');
redirect('login.php');
