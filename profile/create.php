<?php
require __DIR__ . '/../includes/init.php';
require_login();

$error = '';
$username = '';
$full_name = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (!csrf_ok()) {
        $error = 'Invalid request. Please try again.';
    } else {
        $username = post_text('username');
        $full_name = post_text('full_name');
        $password = isset($_POST['password']) ? (string)$_POST['password'] : '';
        $confirm = isset($_POST['confirm_password']) ? (string)$_POST['confirm_password'] : '';
        $taken = stmt_one($conn, 'SELECT id FROM users WHERE username = ?', 's', array($username));

        if ($full_name == '') {
            $error = 'Please enter full name';
        } elseif ($username == '') {
            $error = 'Please enter username';
        } elseif (is_array($taken)) {
            $error = 'Username is already used';
        } elseif ($taken === null) {
            $error = 'Something went wrong. Please try again.';
        } elseif (strlen($password) < 8) {
            $error = 'New password must be at least 8 characters';
        } elseif ($password != $confirm) {
            $error = 'New password and confirmation do not match';
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            if ($hash === false) {
                $error = 'Something went wrong. Please try again.';
            } else {
                // save profile
                $ok = stmt_run(
                    $conn,
                    'INSERT INTO users (username, full_name, password_hash) VALUES (?, ?, ?)',
                    'sss',
                    array($username, $full_name, $hash)
                );
                if (!$ok) {
                    $error = 'Something went wrong. Please try again.';
                } else {
                    log_activity($conn, 'Added profile', $username);
                    flash('success', 'Profile added.');
                    redirect('profile/index.php');
                }
            }
        }
    }
}

$pageTitle = 'Add Profile';
require __DIR__ . '/../includes/header.php';
?>
<div class="page-top">
    <div>
        <h1>Add Profile</h1>
        <a class="back-link" href="index.php">Back to profiles</a>
    </div>
</div>
<div class="card form-box">
    <div class="card-body">
        <?php if ($error != '') { ?>
            <div class="alert alert-danger"><?php echo e($error); ?></div>
        <?php } ?>
        <form method="post">
            <?php echo csrf_field(); ?>
            <div class="mb-3">
                <label class="form-label" for="full_name">Full name</label>
                <input class="form-control" type="text" name="full_name" id="full_name" value="<?php echo e($full_name); ?>">
            </div>
            <div class="mb-3">
                <label class="form-label" for="username">Username</label>
                <input class="form-control" type="text" name="username" id="username" value="<?php echo e($username); ?>">
            </div>
            <div class="mb-3">
                <label class="form-label" for="password">Password</label>
                <input class="form-control" type="password" name="password" id="password">
            </div>
            <div class="mb-3">
                <label class="form-label" for="confirm_password">Confirm password</label>
                <input class="form-control" type="password" name="confirm_password" id="confirm_password">
            </div>
            <button class="btn btn-primary" type="submit">Save</button>
        </form>
    </div>
</div>
<?php require __DIR__ . '/../includes/footer.php'; ?>
