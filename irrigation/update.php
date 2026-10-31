<?php
require __DIR__ . '/../includes/init.php';
require_login();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
}
$row = stmt_one($conn, 'SELECT i.*, c.crop_name FROM irrigations i JOIN crops c ON c.id = i.crop_id WHERE i.id = ?', 'i', array($id));
require_row($row, 'irrigation/index.php');

$crops = stmt_all($conn, 'SELECT id, crop_name FROM crops ORDER BY crop_name, id', '', array());
if (!is_array($crops)) {
    flash('danger', 'Something went wrong. Please try again.');
    redirect('irrigation/index.php');
}

$error = '';
$crop_id = $row['crop_id'];
$schedule_date = $row['schedule_date'];
$water_amount = $row['water_amount'];
$method = $row['method'];
$status = $row['status'];
$notes = $row['notes'] == null ? '' : $row['notes'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (!csrf_ok()) {
        $error = 'Invalid request. Please try again.';
    } else {
        $crop_id = post_text('crop_id');
        $schedule_date = post_text('schedule_date');
        $water_amount = post_text('water_amount');
        $method = post_text('method');
        $status = post_text('status');
        $notes = post_text('notes');

        $cropName = '';
        foreach ($crops as $crop) {
            if ($crop['id'] == $crop_id) {
                $cropName = $crop['crop_name'];
            }
        }

        if ($cropName == '') {
            $error = 'Please select a crop';
        } elseif (!valid_date($schedule_date)) {
            $error = 'Please enter a valid date';
        } elseif (!is_numeric($water_amount) || $water_amount <= 0) {
            $error = 'Please enter a valid water amount';
        } elseif ($method != 'Drip' && $method != 'Sprinkler' && $method != 'Flood' && $method != 'Manual') {
            $error = 'Please select a method';
        } elseif ($status != 'Scheduled' && $status != 'Completed') {
            $error = 'Please select a status';
        } else {
            // save irrigation
            $ok = stmt_run(
                $conn,
                'UPDATE irrigations SET crop_id = ?, schedule_date = ?, water_amount = ?, method = ?, status = ?, notes = ? WHERE id = ?',
                'isssssi',
                array((int)$crop_id, $schedule_date, $water_amount, $method, $status, $notes, $id)
            );
            if (!$ok) {
                $error = 'Something went wrong. Please try again.';
            } else {
                log_activity($conn, 'Updated irrigation', $cropName . ' on ' . $schedule_date);
                flash('success', 'Irrigation schedule updated.');
                redirect('irrigation/view.php?id=' . $id);
            }
        }
    }
}

$pageTitle = 'Update Irrigation';
require __DIR__ . '/../includes/header.php';
?>
<div class="page-top">
    <div>
        <h1>Update Irrigation</h1>
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
                <label class="form-label" for="crop_id">Crop</label>
                <select class="form-select" name="crop_id" id="crop_id">
                    <option value="">Select crop</option>
                    <?php foreach ($crops as $crop) { ?>
                        <option value="<?php echo (int)$crop['id']; ?>" <?php if ($crop_id == $crop['id']) { echo 'selected'; } ?>><?php echo e($crop['crop_name']); ?></option>
                    <?php } ?>
                </select>
            </div>
            <div class="mb-3">
                <label class="form-label" for="schedule_date">Date</label>
                <input class="form-control" type="date" name="schedule_date" id="schedule_date" value="<?php echo e($schedule_date); ?>">
            </div>
            <div class="mb-3">
                <label class="form-label" for="water_amount">Water amount (liters)</label>
                <input class="form-control" type="number" name="water_amount" id="water_amount" step="0.01" value="<?php echo e($water_amount); ?>">
            </div>
            <div class="mb-3">
                <label class="form-label" for="method">Method</label>
                <select class="form-select" name="method" id="method">
                    <option value="">Select</option>
                    <option value="Drip" <?php if ($method == 'Drip') { echo 'selected'; } ?>>Drip</option>
                    <option value="Sprinkler" <?php if ($method == 'Sprinkler') { echo 'selected'; } ?>>Sprinkler</option>
                    <option value="Flood" <?php if ($method == 'Flood') { echo 'selected'; } ?>>Flood</option>
                    <option value="Manual" <?php if ($method == 'Manual') { echo 'selected'; } ?>>Manual</option>
                </select>
            </div>
            <div class="mb-3">
                <label class="form-label" for="status">Status</label>
                <select class="form-select" name="status" id="status">
                    <option value="Scheduled" <?php if ($status == 'Scheduled') { echo 'selected'; } ?>>Scheduled</option>
                    <option value="Completed" <?php if ($status == 'Completed') { echo 'selected'; } ?>>Completed</option>
                </select>
            </div>
            <div class="mb-3">
                <label class="form-label" for="notes">Notes</label>
                <textarea class="form-control" name="notes" id="notes" rows="3"><?php echo e($notes); ?></textarea>
            </div>
            <button class="btn btn-primary" type="submit">Update</button>
        </form>
    </div>
</div>
<?php require __DIR__ . '/../includes/footer.php'; ?>
