<?php

error_reporting(E_ALL);
ini_set('display_errors', '0');
ini_set('log_errors', '1');

require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/auth.php';

// start the app: env, database, session
load_env(dirname(__DIR__) . '/.env');
start_app_session();

$conn = db_connect();
