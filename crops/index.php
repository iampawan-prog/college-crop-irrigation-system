<?php
require __DIR__ . '/../includes/init.php';
require_login();

$list = stmt_all($conn, 'SELECT id, crop_name, variety, season, area, area_unit, planting_date, status FROM crops ORDER BY crop_name, id', '', array());
$listError = '';
if ($list === null) {
    $listError = 'Could not load crops.';
    $list = array();
}

$pageTitle = 'Crops';
require __DIR__ . '/../includes/header.php';
?>
<div class="page-top">
    <h1>Crops</h1>
    <a class="btn btn-primary" href="create.php">Add Crop</a>
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
                        <th>Crop name</th>
                        <th>Variety</th>
                        <th>Season</th>
                        <th>Area</th>
                        <th>Planting date</th>
                        <th>Status</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                <?php if (!$list) { ?>
                    <tr><td colspan="7">No crops yet.</td></tr>
                <?php } else {
                    foreach ($list as $row) { ?>
                        <tr>
                            <td><?php echo e($row['crop_name']); ?></td>
                            <td><?php echo e($row['variety']); ?></td>
                            <td><?php echo e($row['season']); ?></td>
                            <td><?php echo e($row['area']); ?> <?php echo e($row['area_unit']); ?></td>
                            <td><?php echo e($row['planting_date']); ?></td>
                            <td><?php echo e($row['status']); ?></td>
                            <td class="actions">
                                <a class="icon-btn" href="view.php?id=<?php echo (int)$row['id']; ?>" title="View"><i class="bi bi-eye"></i></a>
                                <a class="icon-btn" href="update.php?id=<?php echo (int)$row['id']; ?>" title="Edit"><i class="bi bi-pencil"></i></a>
                                <form method="post" action="view.php" class="d-inline" onsubmit="return confirm('Delete this crop?');">
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
