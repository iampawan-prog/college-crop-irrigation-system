# Crop Irrigation Management System

College project for crops, irrigation, fertilizer, and harvest. Plain PHP pages, MariaDB, Bootstrap, and Chart.js. No framework and no Composer.

PHP 8.0.30 or newer. Database is MariaDB 10.4 (XAMPP ships 10.4.32). Write PHP that still runs on 8.0.30: no enums, `readonly`, or `mysqli_execute_query`.

## Run on XAMPP

1. Copy this folder into `htdocs`.
2. Copy `.env.example` to `.env`. Set `DB_USER=root` and `DB_PASSWORD=` with nothing after the equals sign. Leave host `127.0.0.1`, port `3306`, and the database name.
3. In phpMyAdmin, create a database with the same name as `DB_NAME`, open it, and import `sql/schema.sql`. Importing again wipes the tables and reloads the demo rows.
4. Open http://localhost/college-crop-irrigation-system/

PHP uses `DB_USER` and `DB_PASSWORD`. Do not commit `.env`.

## Database

MariaDB 10.4. Tables use `utf8mb4_unicode_ci`. Do not use the MySQL 8 collation `utf8mb4_0900_ai_ci`. `sql/schema.sql` does not create the database.

Tables: `users`, `crops`, `irrigations`, `fertilizers`, `harvests`, `activities`.

A crop that still has irrigation, fertilizer, or harvest rows cannot be deleted.

## Logins

- admin / admin123
- farmer / farmer123

## How a page works

Crops, irrigation, fertilizer, harvest, and profile each have a folder. Inside it: `index.php` (the list), `create.php`, `update.php`, and `view.php`. The list has view, edit, and delete icons. The view page still has Edit and Delete. Change password is on your own profile view. Reports are in `reports/`. Login, logout, and the dashboard stay in the project root.

Links use `url()` so the same files work at `http://localhost:8000/` and in an `htdocs` subfolder.

`includes/init.php` loads `.env`, connects, and starts the session. A page in a feature folder looks like this:

```php
require __DIR__ . '/../includes/init.php';
require_login();
```

A page in the project root uses `require __DIR__ . '/includes/init.php'`.

Read the POST, then `redirect()`, then include `includes/header.php`. Do not print HTML before the redirect.

Queries that take user input use `?` placeholders with `stmt_run()` or `stmt_all()`. Escape text with `e()`. Every POST form includes `csrf_field()`. After a successful save, call `log_activity()`.

To add a screen: create a PHP file in that feature folder and, if it needs a menu item, add a link in `includes/header.php`.

## Checks

```
php check.php
```

That checks the `.env` parser and dates. It does not connect to the database.
