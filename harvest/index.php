<?php
require __DIR__ . '/../includes/init.php';
require_login();

$list = stmt_all($conn, 'SELECT h.id, h.harvest_date, h.quantity, h.unit, h.quality, c.crop_name FROM harvests h JOIN crops c ON c.id = h.crop_id ORDER BY h.harvest_date DESC, h.id DESC', '', array());
$listError = '';
if ($list === null) {
    $listError = 'Could not load harvest records.';
    $list = array();
}

$pageTitle = 'Harvest';
require __DIR__ . '/../includes/header.php';
?>
<div class="page-top">
    <h1>Harvest</h1>
    <a class="btn btn-primary" href="create.php">Add Harvest</a>
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
                        <th>Quantity</th>
                        <th>Quality</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                <?php if (!$list) { ?>
                    <tr><td colspan="5">No harvest records yet.</td></tr>
                <?php } else {
                    foreach ($list as $row) { ?>
                        <tr>
                            <td><?php echo e($row['crop_name']); ?></td>
                            <td><?php echo e($row['harvest_date']); ?></td>
                            <td><?php echo e($row['quantity']); ?> <?php echo e($row['unit']); ?></td>
                            <td><?php echo e($row['quality']); ?></td>
                            <td class="actions">
                                <a class="icon-btn" href="view.php?id=<?php echo (int)$row['id']; ?>" title="View"><i class="bi bi-eye"></i></a>
                                <a class="icon-btn" href="update.php?id=<?php echo (int)$row['id']; ?>" title="Edit"><i class="bi bi-pencil"></i></a>
                                <form method="post" action="view.php" class="d-inline" onsubmit="return confirm('Delete this harvest?');">
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
