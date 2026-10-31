<?php
require __DIR__ . '/../includes/init.php';
require_login();

$list = stmt_all($conn, 'SELECT i.id, i.schedule_date, i.water_amount, i.method, i.status, c.crop_name FROM irrigations i JOIN crops c ON c.id = i.crop_id ORDER BY i.schedule_date DESC, i.id DESC', '', array());
$listError = '';
if ($list === null) {
    $listError = 'Could not load irrigation records.';
    $list = array();
}

$pageTitle = 'Irrigation';
require __DIR__ . '/../includes/header.php';
?>
<div class="page-top">
    <h1>Irrigation</h1>
    <a class="btn btn-primary" href="create.php">Add Irrigation</a>
</div>
<?php if ($listError != '') { ?>
    <div class="alert alert-danger"><?php echo e($listError); ?></div>
<?php } ?>
<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table mb-0">
                <thead>
                    <tr>
                        <th>Crop</th>
                        <th>Date</th>
                        <th>Water</th>
                        <th>Method</th>
                        <th>Status</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                <?php if (!$list) { ?>
                    <tr><td colspan="6">No irrigation records yet.</td></tr>
                <?php } else {
                    foreach ($list as $row) { ?>
                        <tr>
                            <td><?php echo e($row['crop_name']); ?></td>
                            <td><?php echo e($row['schedule_date']); ?></td>
                            <td><?php echo e($row['water_amount']); ?> liters</td>
                            <td><?php echo e($row['method']); ?></td>
                            <td><?php echo e($row['status']); ?></td>
                            <td class="actions">
                                <a class="icon-btn" href="view.php?id=<?php echo (int)$row['id']; ?>" title="View"><i class="bi bi-eye"></i></a>
                                <a class="icon-btn" href="update.php?id=<?php echo (int)$row['id']; ?>" title="Edit"><i class="bi bi-pencil"></i></a>
                                <form method="post" action="view.php" class="d-inline" onsubmit="return confirm('Delete this irrigation schedule?');">
                                    <?php echo csrf_field(); ?>
                                    <input type="hidden" name="id" value="<?php echo (int)$row['id']; ?>">
                                    <button class="icon-btn danger" type="submit" name="delete" value="1" title="Delete"><i class="bi bi-trash"></i></button>
                                </form>
                            </td>
                        </tr>
                    <?php }
                } ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php require __DIR__ . '/../includes/footer.php'; ?>
