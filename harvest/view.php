<?php
require __DIR__ . '/../includes/init.php';
require_login();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
}
$row = stmt_one($conn, 'SELECT h.*, c.crop_name FROM harvests h JOIN crops c ON c.id = h.crop_id WHERE h.id = ?', 'i', array($id));
require_row($row, 'harvest/index.php');

$error = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (!csrf_ok()) {
        $error = 'Invalid request. Please try again.';
    } else {
        $ok = stmt_run($conn, 'DELETE FROM harvests WHERE id = ?', 'i', array($id));
        if (!$ok) {
            $error = 'Something went wrong. Please try again.';
        } else {
            log_activity($conn, 'Deleted harvest', $row['crop_name']);
            flash('success', 'Harvest deleted.');
            redirect('harvest/index.php');
        }
    }
}

$pageTitle = 'Harvest Details';
require __DIR__ . '/../includes/header.php';
?>
<div class="page-top">
    <div>
        <h1>Harvest Details</h1>
        <a class="back-link" href="index.php">Back to harvest</a>
    </div>
    <div>
        <a class="btn btn-primary" href="update.php?id=<?php echo (int)$row['id']; ?>">Edit</a>
        <form method="post" class="d-inline" onsubmit="return confirm('Delete this harvest?');">
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
            <tr><th>Harvest date</th><td><?php echo e($row['harvest_date']); ?></td></tr>
            <tr><th>Quantity</th><td><?php echo e($row['quantity']); ?> <?php echo e($row['unit']); ?></td></tr>
            <tr><th>Quality</th><td><?php echo e($row['quality']); ?></td></tr>
            <tr><th>Notes</th><td><?php echo nl2br(e($row['notes'])); ?></td></tr>
        </table>
    </div>
</div>
<?php require __DIR__ . '/../includes/footer.php'; ?>
