<?php
require __DIR__ . '/../includes/init.php';
require_login();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
}
$row = stmt_one($conn, 'SELECT * FROM crops WHERE id = ?', 'i', array($id));
require_row($row, 'crops/index.php');

$error = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (!csrf_ok()) {
        $error = 'Invalid request. Please try again.';
    } else {
        // delete crop
        $irr = stmt_one($conn, 'SELECT COUNT(*) AS total FROM irrigations WHERE crop_id = ?', 'i', array($id));
        $fer = stmt_one($conn, 'SELECT COUNT(*) AS total FROM fertilizers WHERE crop_id = ?', 'i', array($id));
        $har = stmt_one($conn, 'SELECT COUNT(*) AS total FROM harvests WHERE crop_id = ?', 'i', array($id));
        if (!is_array($irr) || !is_array($fer) || !is_array($har)) {
            $error = 'Something went wrong. Please try again.';
        } elseif ((int)$irr['total'] > 0 || (int)$fer['total'] > 0 || (int)$har['total'] > 0) {
            $error = 'This crop still has irrigation, fertilizer, or harvest records, so it cannot be deleted.';
        } else {
            $ok = stmt_run($conn, 'DELETE FROM crops WHERE id = ?', 'i', array($id));
            if (!$ok) {
                $error = 'Something went wrong. Please try again.';
            } else {
                log_activity($conn, 'Deleted crop', $row['crop_name']);
                flash('success', 'Crop deleted.');
                redirect('crops/index.php');
            }
        }
    }
}

$irrigations = stmt_all($conn, 'SELECT id, schedule_date, water_amount, method, status, notes FROM irrigations WHERE crop_id = ? ORDER BY schedule_date DESC, id DESC', 'i', array($id));
$fertilizers = stmt_all($conn, 'SELECT id, fertilizer_name, quantity, unit, applied_date, notes FROM fertilizers WHERE crop_id = ? ORDER BY applied_date DESC, id DESC', 'i', array($id));
$harvests = stmt_all($conn, 'SELECT id, harvest_date, quantity, unit, quality, notes FROM harvests WHERE crop_id = ? ORDER BY harvest_date DESC, id DESC', 'i', array($id));

$pageTitle = 'Crop Details';
require __DIR__ . '/../includes/header.php';
?>
<div class="page-top">
    <div>
        <h1>Crop Details</h1>
        <a class="back-link" href="index.php">Back to crops</a>
    </div>
    <div>
        <a class="btn btn-primary" href="update.php?id=<?php echo (int)$row['id']; ?>">Edit</a>
        <form method="post" class="d-inline" onsubmit="return confirm('Delete this crop?');">
            <?php echo csrf_field(); ?>
            <input type="hidden" name="id" value="<?php echo (int)$row['id']; ?>">
            <button class="btn btn-danger" type="submit" name="delete" value="1">Delete</button>
        </form>
    </div>
</div>
<?php if ($error != '') { ?>
    <div class="alert alert-danger"><?php echo e($error); ?></div>
<?php } ?>
<div class="card mb-4">
    <div class="card-body">
        <table class="table detail-table mb-0">
            <tr><th>Crop name</th><td><?php echo e($row['crop_name']); ?></td></tr>
            <tr><th>Variety</th><td><?php echo e($row['variety']); ?></td></tr>
            <tr><th>Season</th><td><?php echo e($row['season']); ?></td></tr>
            <tr><th>Area</th><td><?php echo e($row['area']); ?> <?php echo e($row['area_unit']); ?></td></tr>
            <tr><th>Planting date</th><td><?php echo e($row['planting_date']); ?></td></tr>
            <tr><th>Expected harvest date</th><td><?php echo $row['expected_harvest_date'] ? e($row['expected_harvest_date']) : '-'; ?></td></tr>
            <tr><th>Status</th><td><?php echo e($row['status']); ?></td></tr>
            <tr><th>Notes</th><td><?php echo nl2br(e($row['notes'])); ?></td></tr>
        </table>
    </div>
</div>

<ul class="nav nav-tabs">
    <li class="nav-item">
        <button class="nav-link active" type="button" data-bs-toggle="tab" data-bs-target="#irrigation-tab">Irrigation (<?php echo is_array($irrigations) ? count($irrigations) : 0; ?>)</button>
    </li>
    <li class="nav-item">
        <button class="nav-link" type="button" data-bs-toggle="tab" data-bs-target="#fertilizer-tab">Fertilizer (<?php echo is_array($fertilizers) ? count($fertilizers) : 0; ?>)</button>
    </li>
    <li class="nav-item">
        <button class="nav-link" type="button" data-bs-toggle="tab" data-bs-target="#harvest-tab">Harvest (<?php echo is_array($harvests) ? count($harvests) : 0; ?>)</button>
    </li>
</ul>
<div class="tab-content tab-panel mb-4">
    <div class="tab-pane fade show active" id="irrigation-tab">
        <div class="table-responsive">
            <table class="table mb-0">
                <thead>
                    <tr><th>Date</th><th>Water</th><th>Method</th><th>Status</th><th>Notes</th><th></th></tr>
                </thead>
                <tbody>
                <?php if (!is_array($irrigations) || !$irrigations) { ?>
                    <tr><td colspan="6">No irrigation records for this crop.</td></tr>
                <?php } else {
                    foreach ($irrigations as $item) { ?>
                        <tr>
                            <td><?php echo e($item['schedule_date']); ?></td>
                            <td><?php echo e($item['water_amount']); ?> liters</td>
                            <td><?php echo e($item['method']); ?></td>
                            <td><?php echo e($item['status']); ?></td>
                            <td><?php echo nl2br(e($item['notes'])); ?></td>
                            <td><a class="icon-btn" href="<?php echo e(url('irrigation/view.php?id=' . (int)$item['id'])); ?>" title="View"><i class="bi bi-eye"></i></a></td>
                        </tr>
                    <?php }
                } ?>
                </tbody>
            </table>
        </div>
    </div>
    <div class="tab-pane fade" id="fertilizer-tab">
        <div class="table-responsive">
            <table class="table mb-0">
                <thead>
                    <tr><th>Name</th><th>Quantity</th><th>Date</th><th>Notes</th><th></th></tr>
                </thead>
                <tbody>
                <?php if (!is_array($fertilizers) || !$fertilizers) { ?>
                    <tr><td colspan="5">No fertilizer records for this crop.</td></tr>
                <?php } else {
                    foreach ($fertilizers as $item) { ?>
                        <tr>
                            <td><?php echo e($item['fertilizer_name']); ?></td>
                            <td><?php echo e($item['quantity']); ?> <?php echo e($item['unit']); ?></td>
                            <td><?php echo e($item['applied_date']); ?></td>
                            <td><?php echo nl2br(e($item['notes'])); ?></td>
                            <td><a class="icon-btn" href="<?php echo e(url('fertilizer/view.php?id=' . (int)$item['id'])); ?>" title="View"><i class="bi bi-eye"></i></a></td>
                        </tr>
                    <?php }
                } ?>
                </tbody>
            </table>
        </div>
    </div>
    <div class="tab-pane fade" id="harvest-tab">
        <div class="table-responsive">
            <table class="table mb-0">
                <thead>
                    <tr><th>Date</th><th>Quantity</th><th>Quality</th><th>Notes</th><th></th></tr>
                </thead>
                <tbody>
                <?php if (!is_array($harvests) || !$harvests) { ?>
                    <tr><td colspan="5">No harvest records for this crop.</td></tr>
                <?php } else {
                    foreach ($harvests as $item) { ?>
                        <tr>
                            <td><?php echo e($item['harvest_date']); ?></td>
                            <td><?php echo e($item['quantity']); ?> <?php echo e($item['unit']); ?></td>
                            <td><?php echo e($item['quality']); ?></td>
                            <td><?php echo nl2br(e($item['notes'])); ?></td>
                            <td><a class="icon-btn" href="<?php echo e(url('harvest/view.php?id=' . (int)$item['id'])); ?>" title="View"><i class="bi bi-eye"></i></a></td>
                        </tr>
                    <?php }
                } ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php require __DIR__ . '/../includes/footer.php'; ?>
