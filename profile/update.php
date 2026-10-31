<?php
require __DIR__ . '/../includes/init.php';
require_login();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
}
$row = stmt_one($conn, 'SELECT id, username, full_name FROM users WHERE id = ?', 'i', array($id));
require_row($row, 'profile/index.php');

$error = '';
$username = $row['username'];
$full_name = $row['full_name'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (!csrf_ok()) {
        $error = 'Invalid request. Please try again.';
    } else {
        $username = post_text('username');
        $full_name = post_text('full_name');
        $taken = stmt_one($conn, 'SELECT id FROM users WHERE username = ? AND id <> ?', 'si', array($username, $id));

        if ($full_name == '') {
            $error = 'Please enter full name';
        } elseif ($username == '') {
            $error = 'Please enter username';
        } elseif (is_array($taken)) {
            $error = 'Username is already used';
        } elseif ($taken === null) {
            $error = 'Something went wrong. Please try again.';
        } else {
            // save profile
            $ok = stmt_run($conn, 'UPDATE users SET username = ?, full_name = ? WHERE id = ?', 'ssi', array($username, $full_name, $id));
            if (!$ok) {
                $error = 'Something went wrong. Please try again.';
            } else {
                if ((int)$id === (int)$_SESSION['user_id']) {
                    $_SESSION['username'] = $username;
                    $_SESSION['full_name'] = $full_name;
                }
                log_activity($conn, 'Updated profile', $username);
                flash('success', 'Profile updated.');
                redirect('profile/view.php?id=' . $id);
            }
        }
    }
}

$pageTitle = 'Update Profile';
require __DIR__ . '/../includes/header.php';
?>
<div class="page-top">
    <div>
        <h1>Update Profile</h1>
        <a class="back-link" href="view.php?id=<?php echo (int)$id; ?>">Back to details</a>
    </div>
</div>
<div class="card form-box">
    <div class="card-body">
        <?php if ($error != '') { ?>
            <div class="alert alert-danger"><?php echo e($error); ?></div>
        <?php } ?>
        <form method="post">
            <?php echo csrf_field(); ?>
            <input type="hidden" name="id" value="<?php echo (int)$id; ?>">
            <div class="mb-3">
                <label class="form-label" for="full_name">Full name</label>
                <input class="form-control" type="text" name="full_name" id="full_name" value="<?php echo e($full_name); ?>">
            </div>
            <div class="mb-3">
                <label class="form-label" for="username">Username</label>
                <input class="form-control" type="text" name="username" id="username" value="<?php echo e($username); ?>">
            </div>
            <button class="btn btn-primary" type="submit">Update</button>
        </form>
    </div>
</div>
<?php require __DIR__ . '/../includes/footer.php'; ?>
