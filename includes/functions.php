<?php

function e($value) {
    if ($value === null) {
        $value = '';
    }
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function app_base() {
    $script = isset($_SERVER['SCRIPT_NAME']) ? str_replace('\\', '/', $_SERVER['SCRIPT_NAME']) : '';
    $dir = dirname($script);
    $name = basename($dir);
    if ($name === 'crops' || $name === 'irrigation' || $name === 'fertilizer' || $name === 'harvest' || $name === 'reports' || $name === 'profile') {
        $dir = dirname($dir);
    }
    if ($dir === '/' || $dir === '.' || $dir === '\\') {
        return '';
    }
    return rtrim($dir, '/');
}

function url($path) {
    return app_base() . '/' . ltrim($path, '/');
}

function redirect($path) {
    header('Location: ' . url($path));
    exit;
}

function flash($type, $text) {
    $_SESSION['flash'] = array('type' => $type, 'text' => $text);
}

function take_flash() {
    if (empty($_SESSION['flash']) || !is_array($_SESSION['flash'])) {
        return null;
    }
    $flash = $_SESSION['flash'];
    unset($_SESSION['flash']);
    return $flash;
}

function nav_on($page) {
    $script = isset($_SERVER['SCRIPT_NAME']) ? str_replace('\\', '/', $_SERVER['SCRIPT_NAME']) : '';
    if ($page === 'crops' || $page === 'irrigation' || $page === 'fertilizer' || $page === 'harvest' || $page === 'reports' || $page === 'profile') {
        if (strpos($script, '/' . $page . '/') !== false) {
            return ' active';
        }
        return '';
    }
    if (basename($script) === $page) {
        return ' active';
    }
    return '';
}

function post_text($key) {
    if (!isset($_POST[$key]) || is_array($_POST[$key])) {
        return '';
    }
    return trim($_POST[$key]);
}

function query_text($key) {
    if (!isset($_GET[$key]) || is_array($_GET[$key])) {
        return '';
    }
    return trim($_GET[$key]);
}

function valid_date($value) {
    if (!is_string($value) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)) {
        return false;
    }
    $parts = explode('-', $value);
    return checkdate((int)$parts[1], (int)$parts[2], (int)$parts[0]);
}

function bind_values($stmt, $types, &$values) {
    $count = count($values);
    if ($count !== strlen($types)) {
        error_log('bind count mismatch');
        return false;
    }
    $args = array($stmt, $types);
    $i = 0;
    while ($i < $count) {
        $args[] = &$values[$i];
        $i++;
    }
    return call_user_func_array('mysqli_stmt_bind_param', $args);
}

function stmt_run($conn, $sql, $types, $params) {
    $stmt = mysqli_prepare($conn, $sql);
    if (!$stmt) {
        error_log(mysqli_error($conn));
        return false;
    }
    $values = array_values($params);
    if ($types !== '') {
        if (!bind_values($stmt, $types, $values)) {
            error_log(mysqli_error($conn));
            mysqli_stmt_close($stmt);
            return false;
        }
    }
    $ok = mysqli_stmt_execute($stmt);
    if (!$ok) {
        error_log(mysqli_stmt_error($stmt));
        mysqli_stmt_close($stmt);
        return false;
    }
    mysqli_stmt_close($stmt);
    return true;
}

function stmt_all($conn, $sql, $types, $params) {
    $stmt = mysqli_prepare($conn, $sql);
    if (!$stmt) {
        error_log(mysqli_error($conn));
        return null;
    }
    $values = array_values($params);
    if ($types !== '') {
        if (!bind_values($stmt, $types, $values)) {
            error_log(mysqli_error($conn));
            mysqli_stmt_close($stmt);
            return null;
        }
    }
    if (!mysqli_stmt_execute($stmt)) {
        error_log(mysqli_stmt_error($stmt));
        mysqli_stmt_close($stmt);
        return null;
    }
    $result = mysqli_stmt_get_result($stmt);
    if ($result === false) {
        error_log(mysqli_stmt_error($stmt));
        mysqli_stmt_close($stmt);
        return null;
    }
    $rows = array();
    while ($row = mysqli_fetch_assoc($result)) {
        $rows[] = $row;
    }
    mysqli_free_result($result);
    mysqli_stmt_close($stmt);
    return $rows;
}

function stmt_one($conn, $sql, $types, $params) {
    $rows = stmt_all($conn, $sql, $types, $params);
    if ($rows === null) {
        return null;
    }
    if (count($rows) === 0) {
        return false;
    }
    return $rows[0];
}

function require_row($row, $back) {
    if ($row === null) {
        flash('danger', 'Something went wrong. Please try again.');
        redirect($back);
    }
    if (!is_array($row)) {
        flash('danger', 'Record not found.');
        redirect($back);
    }
}

function log_activity($conn, $action, $details) {
    if (empty($_SESSION['user_id'])) {
        return;
    }
    $userId = (int)$_SESSION['user_id'];
    $action = substr((string)$action, 0, 50);
    $details = substr((string)$details, 0, 255);
    stmt_run($conn, 'INSERT INTO activities (user_id, action, details) VALUES (?, ?, ?)', 'iss', array($userId, $action, $details));
}
