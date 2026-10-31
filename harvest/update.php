<?php
require __DIR__ . '/../includes/init.php';
require_login();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
}
$row = stmt_one($conn, 'SELECT h.*, c.crop_name FROM harvests h JOIN crops c ON c.id = h.crop_id WHERE h.id = ?', 'i', array($id));
require_row($row, 'harvest/index.php');

$crops = stmt_all($conn, 'SELECT id, crop_name FROM crops ORDER BY crop_name, id', '', array());
if (!is_array($crops)) {
    flash('danger', 'Something went wrong. Please try again.');
    redirect('harvest/index.php');
}

$error = '';
$crop_id = $row['crop_id'];
$harvest_date = $row['harvest_date'];
$quantity = $row['quantity'];
$unit = $row['unit'];
$quality = $row['quality'];
$notes = $row['notes'] == null ? '' : $row['notes'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (!csrf_ok()) {
        $error = 'Invalid request. Please try again.';
    } else {
        $crop_id = post_text('crop_id');
        $harvest_date = post_text('harvest_date');
        $quantity = post_text('quantity');
        $unit = post_text('unit');
        $quality = post_text('quality');
        $notes = post_text('notes');

        $cropName = '';
        foreach ($crops as $crop) {
            if ($crop['id'] == $crop_id) {
                $cropName = $crop['crop_name'];
            }
        }

        if ($cropName == '') {
            $error = 'Please select a crop';
        } elseif (!valid_date($harvest_date)) {
            $error = 'Please enter a valid date';
        } elseif (!is_numeric($quantity) || $quantity <= 0) {
            $error = 'Please enter a valid quantity';
        } elseif ($unit != 'kg' && $unit != 'quintal' && $unit != 'ton') {
            $error = 'Please select a unit';
        } elseif ($quality != 'Good' && $quality != 'Average' && $quality != 'Poor') {
            $error = 'Please select a quality';
        } else {
            // save harvest
            $ok = stmt_run(
                $conn,
                'UPDATE harvests SET crop_id = ?, harvest_date = ?, quantity = ?, unit = ?, quality = ?, notes = ? WHERE id = ?',
                'isssssi',
                array((int)$crop_id, $harvest_date, $quantity, $unit, $quality, $notes, $id)
            );
            if (!$ok) {
                $error = 'Something went wrong. Please try again.';
            } else {
                log_activity($conn, 'Updated harvest', $cropName);
                flash('success', 'Harvest updated.');
                redirect('harvest/view.php?id=' . $id);
            }
        }
    }
}

$pageTitle = 'Update Harvest';
require __DIR__ . '/../includes/header.php';
?>
<div class="page-top">
    <div>
        <h1>Update Harvest</h1>
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
                <label class="form-label" for="harvest_date">Harvest date</label>
                <input class="form-control" type="date" name="harvest_date" id="harvest_date" value="<?php echo e($harvest_date); ?>">
            </div>
            <div class="mb-3">
                <label class="form-label" for="quantity">Quantity</label>
                <input class="form-control" type="number" name="quantity" id="quantity" step="0.01" value="<?php echo e($quantity); ?>">
            </div>
            <div class="mb-3">
                <label class="form-label" for="unit">Unit</label>
                <select class="form-select" name="unit" id="unit">
                    <option value="">Select</option>
                    <option value="kg" <?php if ($unit == 'kg') { echo 'selected'; } ?>>kg</option>
                    <option value="quintal" <?php if ($unit == 'quintal') { echo 'selected'; } ?>>quintal</option>
                    <option value="ton" <?php if ($unit == 'ton') { echo 'selected'; } ?>>ton</option>
                </select>
            </div>
            <div class="mb-3">
                <label class="form-label" for="quality">Quality</label>
                <select class="form-select" name="quality" id="quality">
                    <option value="">Select</option>
                    <option value="Good" <?php if ($quality == 'Good') { echo 'selected'; } ?>>Good</option>
                    <option value="Average" <?php if ($quality == 'Average') { echo 'selected'; } ?>>Average</option>
                    <option value="Poor" <?php if ($quality == 'Poor') { echo 'selected'; } ?>>Poor</option>
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
