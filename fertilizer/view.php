<?php
require __DIR__ . '/../includes/init.php';
require_login();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
}
$row = stmt_one($conn, 'SELECT f.*, c.crop_name FROM fertilizers f JOIN crops c ON c.id = f.crop_id WHERE f.id = ?', 'i', array($id));
require_row($row, 'fertilizer/index.php');

$error = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (!csrf_ok()) {
        $error = 'Invalid request. Please try again.';
    } else {
        $ok = stmt_run($conn, 'DELETE FROM fertilizers WHERE id = ?', 'i', array($id));
        if (!$ok) {
            $error = 'Something went wrong. Please try again.';
        } else {
            log_activity($conn, 'Deleted fertilizer', $row['fertilizer_name'] . ' for ' . $row['crop_name']);
            flash('success', 'Fertilizer record deleted.');
            redirect('fertilizer/index.php');
        }
    }
}

$pageTitle = 'Fertilizer Details';
require __DIR__ . '/../includes/header.php';
?>
<div class="page-top">
    <div>
        <h1>Fertilizer Details</h1>
        <a class="back-link" href="index.php">Back to fertilizer</a>
    </div>
    <div>
        <a class="btn btn-primary" href="update.php?id=<?php echo (int)$row['id']; ?>">Edit</a>
        <form method="post" class="d-inline" onsubmit="return confirm('Delete this fertilizer record?');">
            <?php echo csrf_field(); ?>
            <input type="hidden" name="id" value="<?php echo (int)$row['id']; ?>">
            <button class="btn btn-danger" type="submit" name="delete" value="1">Delete</button>
        </form>
    </div>
</div>
<?php if ($error != '') { ?>
    <div class="alert alert-danger"><?php echo e($error); ?></div>
<?php } ?>
<div class="card">
    <div class="card-body">
        <table class="table detail-table mb-0">
            <tr><th>Crop</th><td><?php echo e($row['crop_name']); ?></td></tr>
            <tr><th>Fertilizer name</th><td><?php echo e($row['fertilizer_name']); ?></td></tr>
            <tr><th>Quantity</th><td><?php echo e($row['quantity']); ?> <?php echo e($row['unit']); ?></td></tr>
            <tr><th>Applied date</th><td><?php echo e($row['applied_date']); ?></td></tr>
            <tr><th>Notes</th><td><?php echo nl2br(e($row['notes'])); ?></td></tr>
        </table>
    </div>
</div>
<?php require __DIR__ . '/../includes/footer.php'; ?>
