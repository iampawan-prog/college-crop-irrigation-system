<?php

function parse_env_string($text) {
    $vars = array();
    $lines = preg_split("/\r\n|\n|\r/", (string)$text);
    if ($lines === false) {
        return $vars;
    }
    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '' || $line[0] === '#') {
            continue;
        }
        if (!str_contains($line, '=')) {
            continue;
        }
        $parts = explode('=', $line, 2);
        $key = trim($parts[0]);
        $value = trim($parts[1]);
        $len = strlen($value);
        if ($len >= 2) {
            $quote = $value[0];
            if (($quote === '"' || $quote === "'") && $value[$len - 1] === $quote) {
                $value = substr($value, 1, -1);
            }
        }
        if ($key !== '') {
            $vars[$key] = $value;
        }
    }
    return $vars;
}

function load_env($path) {
    if (!is_readable($path)) {
        exit('Missing .env file. Copy .env.example to .env');
    }
    $text = file_get_contents($path);
    if ($text === false) {
        exit('Missing .env file. Copy .env.example to .env');
    }
    $vars = parse_env_string($text);
    foreach ($vars as $key => $value) {
        putenv($key . '=' . $value);
        $_ENV[$key] = $value;
        $_SERVER[$key] = $value;
    }
}

function db_connect() {
    mysqli_report(MYSQLI_REPORT_OFF);
    $host = getenv('DB_HOST');
    $user = getenv('DB_USER');
    $pass = getenv('DB_PASSWORD');
    $name = getenv('DB_NAME');
    $port = (int)getenv('DB_PORT');
    if ($host === false || $user === false || $pass === false || $name === false || $port <= 0) {
        exit('Database settings are missing in .env');
    }
    $conn = mysqli_connect($host, $user, $pass, $name, $port);
    if (!$conn) {
        error_log('DB connect failed: ' . mysqli_connect_error());
        exit('Could not connect to the database. Check .env settings.');
    }
    if (!mysqli_set_charset($conn, 'utf8mb4')) {
        error_log('DB charset failed: ' . mysqli_error($conn));
        exit('Could not connect to the database. Check .env settings.');
    }
    return $conn;
}
