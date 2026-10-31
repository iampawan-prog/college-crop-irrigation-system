<?php
require __DIR__ . '/includes/init.php';

if (!empty($_SESSION['user_id'])) {
    redirect('dashboard.php');
}

$error = '';
$username = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (!csrf_ok()) {
        $error = 'Invalid request. Please try again.';
    } else {
        $username = post_text('username');
        $password = isset($_POST['password']) ? (string)$_POST['password'] : '';
        $user = stmt_one($conn, 'SELECT id, username, full_name, password_hash FROM users WHERE username = ? LIMIT 1', 's', array($username));
        if ($user === null) {
            $error = 'Something went wrong. Please try again.';
        } elseif (!is_array($user) || !password_verify($password, $user['password_hash'])) {
            $error = 'Invalid username or password';
        } else {
            $_SESSION['user_id'] = (int)$user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['full_name'] = $user['full_name'];
            log_activity($conn, 'Logged in', $user['username']);
            flash('success', 'Welcome.');
            redirect('dashboard.php');
        }
    }
}

$pageTitle = 'Login';
require __DIR__ . '/includes/header.php';
?>
<div class="login-wrap">
    <div class="card">
        <div class="card-body">
            <h1 class="h4 mb-3">Login</h1>
            <?php if ($error != '') { ?>
                <div class="alert alert-danger"><?php echo e($error); ?></div>
            <?php } ?>
            <form method="post">
                <?php echo csrf_field(); ?>
                <div class="mb-3">
                    <label class="form-label" for="username">Username</label>
                    <input class="form-control" type="text" name="username" id="username" required value="<?php echo e($username); ?>">
                </div>
                <div class="mb-3">
                    <label class="form-label" for="password">Password</label>
                    <input class="form-control" type="password" name="password" id="password" required>
                </div>
                <button class="btn btn-success w-100" type="submit">Login</button>
            </form>
            <p class="text-muted small mt-3 mb-0">Demo: admin / admin123 and farmer / farmer123</p>
        </div>
    </div>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
