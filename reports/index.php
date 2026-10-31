<?php
require __DIR__ . '/../includes/init.php';
require_login();

$type = query_text('type');
if ($type != 'harvest') {
    $type = 'crop';
}
$from = query_text('from');
$to = query_text('to');
$error = '';
$rows = array();

if ($type == 'harvest') {
    if ($from != '' && !valid_date($from)) {
        $error = 'Please enter a valid start date';
    } elseif ($to != '' && !valid_date($to)) {
        $error = 'Please enter a valid end date';
    } else {
        $sql = 'SELECT h.harvest_date, h.quantity, h.unit, h.quality, h.notes, c.crop_name
                FROM harvests h
                JOIN crops c ON c.id = h.crop_id
                WHERE 1 = 1';
        $types = '';
        $params = array();
        if ($from != '') {
            $sql .= ' AND h.harvest_date >= ?';
            $types .= 's';
            $params[] = $from;
        }
        if ($to != '') {
            $sql .= ' AND h.harvest_date <= ?';
            $types .= 's';
            $params[] = $to;
        }
        $sql .= ' ORDER BY h.harvest_date DESC, h.id DESC';
        $rows = stmt_all($conn, $sql, $types, $params);
        if ($rows === null) {
            $error = 'Could not load the harvest report.';
            $rows = array();
        }
    }
} else {
    $rows = stmt_all(
        $conn,
        'SELECT c.crop_name, c.variety, c.area, c.area_unit, c.status,
                (SELECT COUNT(*) FROM irrigations i WHERE i.crop_id = c.id) AS irrigation_count,
                (SELECT COUNT(*) FROM fertilizers f WHERE f.crop_id = c.id) AS fertilizer_count,
                (SELECT COUNT(*) FROM harvests h WHERE h.crop_id = c.id) AS harvest_count
         FROM crops c
         ORDER BY c.crop_name, c.id',
        '',
        array()
    );
    if ($rows === null) {
        $error = 'Could not load the crop report.';
        $rows = array();
    }
}

$pageTitle = 'Reports';
require __DIR__ . '/../includes/header.php';
?>
<div class="page-top">
    <h1>Reports</h1>
    <div>
        <a class="btn btn-outline-success<?php echo $type == 'crop' ? ' active' : ''; ?>" href="index.php?type=crop">Crop Report</a>
        <a class="btn btn-outline-success<?php echo $type == 'harvest' ? ' active' : ''; ?>" href="index.php?type=harvest">Harvest Report</a>
    </div>
</div>
<?php if ($error != '') { ?>
    <div class="alert alert-danger"><?php echo e($error); ?></div>
<?php } ?>

<?php if ($type == 'harvest') { ?>
<form method="get" class="row g-2 align-items-end mb-3">
    <input type="hidden" name="type" value="harvest">
    <div class="col-auto">
        <label class="form-label" for="from">From</label>
        <input class="form-control" type="date" name="from" id="from" value="<?php echo e($from); ?>">
    </div>
    <div class="col-auto">
        <label class="form-label" for="to">To</label>
        <input class="form-control" type="date" name="to" id="to" value="<?php echo e($to); ?>">
    </div>
    <div class="col-auto">
        <button class="btn btn-primary" type="submit">Show</button>
    </div>
</form>
<?php } ?>

<?php if ($error == '') { ?>
<form method="post" action="pdf.php" class="mb-3">
    <?php echo csrf_field(); ?>
    <input type="hidden" name="type" value="<?php echo e($type); ?>">
    <?php if ($type == 'harvest') { ?>
        <input type="hidden" name="from" value="<?php echo e($from); ?>">
        <input type="hidden" name="to" value="<?php echo e($to); ?>">
    <?php } ?>
    <button class="btn btn-secondary" type="submit">Export PDF</button>
</form>
<?php } ?>

<div class="card">
    <div class="card-body">
        <h2 class="h5"><?php echo $type == 'harvest' ? 'Harvest Report' : 'Crop Report'; ?></h2>
        <div class="table-responsive">
            <table class="table mb-0">
                <?php if ($type == 'crop') { ?>
                    <thead>
                        <tr>
                            <th>Crop</th>
                            <th>Variety</th>
                            <th>Area</th>
                            <th>Status</th>
                            <th>Irrigations</th>
                            <th>Fertilizers</th>
                            <th>Harvests</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if (!$rows) { ?>
                        <tr><td colspan="7">No crops yet.</td></tr>
                    <?php } else {
                        foreach ($rows as $row) { ?>
                            <tr>
                                <td><?php echo e($row['crop_name']); ?></td>
                                <td><?php echo e($row['variety']); ?></td>
                                <td><?php echo e($row['area']); ?> <?php echo e($row['area_unit']); ?></td>
                                <td><?php echo e($row['status']); ?></td>
                                <td><?php echo e($row['irrigation_count']); ?></td>
                                <td><?php echo e($row['fertilizer_count']); ?></td>
                                <td><?php echo e($row['harvest_count']); ?></td>
                            </tr>
                        <?php }
                        } ?>
                    </tbody>
                <?php } else { ?>
                    <thead>
                        <tr>
                            <th>Crop</th>
                            <th>Date</th>
                            <th>Quantity</th>
                            <th>Unit</th>
                            <th>Quality</th>
                            <th>Notes</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if ($error != '') { ?>
                        <tr><td colspan="6">Fix the dates above to see the report.</td></tr>
                    <?php } elseif (!$rows) { ?>
                        <tr><td colspan="6">No harvest records yet.</td></tr>
                    <?php } else {  
                        foreach ($rows as $row) { ?>
                            <tr>
                                <td><?php echo e($row['crop_name']); ?></td>
                                <td><?php echo e($row['harvest_date']); ?></td>
                                <td><?php echo e($row['quantity']); ?></td>
                                <td><?php echo e($row['unit']); ?></td>
                                <td><?php echo e($row['quality']); ?></td>
                                <td><?php echo nl2br(e($row['notes'])); ?></td>
                            </tr>
                        <?php }
                        } ?>
                    </tbody>
                <?php } ?>
            </table>
        </div>
    </div>
</div>
<?php require __DIR__ . '/../includes/footer.php'; ?>
