<?php
require __DIR__ . '/includes/init.php';
require_login();

// load dashboard counts
$cropRow = stmt_one($conn, 'SELECT COUNT(*) AS total FROM crops', '', array());
$harvestRow = stmt_one($conn, 'SELECT COUNT(*) AS total FROM harvests', '', array());
$statusRows = stmt_all($conn, 'SELECT status, COUNT(*) AS total FROM irrigations GROUP BY status', '', array());
$chartRows = stmt_all($conn, 'SELECT c.crop_name, COUNT(i.id) AS total FROM crops c LEFT JOIN irrigations i ON i.crop_id = c.id GROUP BY c.id, c.crop_name ORDER BY c.crop_name, c.id', '', array());
$activityRows = stmt_all($conn, 'SELECT a.created_at, a.action, a.details, u.username FROM activities a JOIN users u ON u.id = a.user_id ORDER BY a.created_at DESC, a.id DESC LIMIT 10', '', array());

$loadError = $cropRow === null || $cropRow === false
    || $harvestRow === null || $harvestRow === false
    || $statusRows === null || $chartRows === null || $activityRows === null;

$cropTotal = is_array($cropRow) ? (int)$cropRow['total'] : 0;
$harvestTotal = is_array($harvestRow) ? (int)$harvestRow['total'] : 0;
$scheduled = 0;
$completed = 0;
if (is_array($statusRows)) {
    foreach ($statusRows as $row) {
        if ($row['status'] === 'Scheduled') {
            $scheduled = (int)$row['total'];
        }
        if ($row['status'] === 'Completed') {
            $completed = (int)$row['total'];
        }
    }
}
$labels = array();
$counts = array();
if (is_array($chartRows)) {
    foreach ($chartRows as $row) {
        $labels[] = $row['crop_name'];
        $counts[] = (int)$row['total'];
    }
}
if (!is_array($activityRows)) {
    $activityRows = array();
}

$pageTitle = 'Dashboard';
$extraFoot = '';
if ($scheduled + $completed > 0) {
    $extraFoot = '<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>'
        . '<script src="' . e(url('assets/js/dashboard.js')) . '"></script>';
}
require __DIR__ . '/includes/header.php';
?>
<div class="page-top">
    <h1>Dashboard</h1>
</div>
<?php if ($loadError) { ?>
    <div class="alert alert-danger">Could not load dashboard.</div>
<?php } ?>
<div class="row g-3">
    <div class="col-md-6">
        <div class="card">
            <div class="card-body">
                <div class="text-muted">Total Crops</div>
                <div class="fs-2"><?php echo e($cropTotal); ?></div>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card">
            <div class="card-body">
                <div class="text-muted">Total Harvest</div>
                <div class="fs-2"><?php echo e($harvestTotal); ?></div>
            </div>
        </div>
    </div>
</div>

<div class="card mt-4">
    <div class="card-body">
        <h2 class="h5">Irrigation Summary</h2>
        <p class="mb-3">Scheduled: <?php echo e($scheduled); ?> &nbsp; Completed: <?php echo e($completed); ?></p>
        <?php if ($scheduled + $completed < 1) { ?>
            <p class="mb-0">No irrigation records yet</p>
        <?php } else { ?>
            <canvas id="irrigationChart" height="120"></canvas>
        <?php } ?>
    </div>
</div>

<div class="card mt-4">
    <div class="card-body">
        <h2 class="h5">Recent Activities</h2>
        <div class="table-responsive">
            <table class="table mb-0">
                <thead>
                    <tr>
                        <th>Time</th>
                        <th>User</th>
                        <th>Action</th>
                        <th>Details</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (!$activityRows) { ?>
                    <tr><td colspan="4">No activity yet.</td></tr>
                <?php } else {
                    foreach ($activityRows as $row) { ?>
                        <tr>
                            <td><?php echo e($row['created_at']); ?></td>
                            <td><?php echo e($row['username']); ?></td>
                            <td><?php echo e($row['action']); ?></td>
                            <td><?php echo e($row['details']); ?></td>
                        </tr>
                    <?php }
                } ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php if ($scheduled + $completed > 0) { ?>
<script id="chart-data" type="application/json"><?php echo json_encode(array('labels' => $labels, 'counts' => $counts), JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP); ?></script>
<?php } ?>
<?php require __DIR__ . '/includes/footer.php'; ?>
