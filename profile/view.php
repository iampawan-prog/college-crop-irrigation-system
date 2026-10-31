<?php
require __DIR__ . '/../includes/init.php';
require_login();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
}
$row = stmt_one($conn, 'SELECT id, username, full_name, password_hash, created_at FROM users WHERE id = ?', 'i', array($id));
require_row($row, 'profile/index.php');

$mine = (int)$row['id'] === (int)$_SESSION['user_id'];
$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (!csrf_ok()) {
        $error = 'Invalid request. Please try again.';
    } elseif (isset($_POST['delete'])) {
        if ($mine) {
            $error = 'You cannot delete the account you are logged in with.';
        } else {
            $cleared = stmt_run($conn, 'DELETE FROM activities WHERE user_id = ?', 'i', array($id));
            $ok = $cleared && stmt_run($conn, 'DELETE FROM users WHERE id = ?', 'i', array($id));
            if (!$ok) {
                $error = 'Something went wrong. Please try again.';
            } else {
                log_activity($conn, 'Deleted profile', $row['username']);
                flash('success', 'Profile deleted.');
                redirect('profile/index.php');
            }
        }
    } elseif (isset($_POST['save_password']) && $mine) {
        $current = isset($_POST['current_password']) ? (string)$_POST['current_password'] : '';
        $new = isset($_POST['new_password']) ? (string)$_POST['new_password'] : '';
        $confirm = isset($_POST['confirm_password']) ? (string)$_POST['confirm_password'] : '';
        if (!password_verify($current, $row['password_hash'])) {
            $error = 'Current password is incorrect';
        } elseif (strlen($new) < 8) {
            $error = 'New password must be at least 8 characters';
        } elseif ($new != $confirm) {
            $error = 'New password and confirmation do not match';
        } else {
            $hash = password_hash($new, PASSWORD_DEFAULT);
            if ($hash === false) {
                $error = 'Something went wrong. Please try again.';
            } else {
                $ok = stmt_run($conn, 'UPDATE users SET password_hash = ? WHERE id = ?', 'si', array($hash, $id));
                if (!$ok) {
                    $error = 'Something went wrong. Please try again.';
                } else {
                    log_activity($conn, 'Changed password', $row['username']);
                    flash('success', 'Password changed.');
                    redirect('profile/view.php?id=' . $id);
                }
            }
        }
    }
}

$pageTitle = 'Profile Details';
require __DIR__ . '/../includes/header.php';
?>
<div class="page-top">
    <div>
        <h1>Profile Details</h1>
        <a class="back-link" href="index.php">Back to profiles</a>
    </div>
    <div>
        <a class="btn btn-primary" href="update.php?id=<?php echo (int)$row['id']; ?>">Edit</a>
        <form method="post" class="d-inline" onsubmit="return confirm('Delete this profile?');">
            <?php echo csrf_field(); ?>
            <input type="hidden" name="id" value="<?php echo (int)$row['id']; ?>">
            <button class="btn btn-danger" type="submit" name="delete" value="1">Delete</button>
        </form>
    </div>
</div>
<?php if ($error != '') { ?>
    <div class="alert alert-danger"><?php echo e($error); ?></div>
<?php } ?>
<div class="card form-box mb-4">
    <div class="card-body">
        <table class="table detail-table mb-0">
            <tr><th>Full name</th><td><?php echo e($row['full_name']); ?></td></tr>
            <tr><th>Username</th><td><?php echo e($row['username']); ?></td></tr>
            <tr><th>Created</th><td><?php echo e($row['created_at']); ?></td></tr>
        </table>
    </div>
</div>
<?php if ($mine) { ?>
<div class="card form-box">
    <div class="card-body">
        <h2 class="h5">Change Password</h2>
        <form method="post">
            <?php echo csrf_field(); ?>
            <input type="hidden" name="id" value="<?php echo (int)$row['id']; ?>">
            <div class="mb-3">
                <label class="form-label" for="current_password">Current password</label>
                <input class="form-control" type="password" name="current_password" id="current_password">
            </div>
            <div class="mb-3">
                <label class="form-label" for="new_password">New password</label>
                <input class="form-control" type="password" name="new_password" id="new_password">
            </div>
            <div class="mb-3">
                <label class="form-label" for="confirm_password">Confirm new password</label>
                <input class="form-control" type="password" name="confirm_password" id="confirm_password">
            </div>
            <button class="btn btn-primary" type="submit" name="save_password" value="1">Update password</button>
        </form>
    </div>
</div>
<?php } ?>
<?php require __DIR__ . '/../includes/footer.php'; ?>
