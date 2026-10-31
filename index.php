<?php
require __DIR__ . '/includes/init.php';

if (!empty($_SESSION['user_id'])) {
    redirect('dashboard.php');
}
redirect('login.php');
