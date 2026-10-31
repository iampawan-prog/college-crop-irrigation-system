<?php
require __DIR__ . '/../includes/init.php';
require_login();

$crops = stmt_all($conn, 'SELECT id, crop_name FROM crops ORDER BY crop_name, id', '', array());
$loadError = '';
if (!is_array($crops)) {
    $crops = array();
    $loadError = 'Could not load crops.';
}

$error = '';
$crop_id = '';
$fertilizer_name = '';
$quantity = '';
$unit = '';
$applied_date = '';
$notes = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (!csrf_ok()) {
        $error = 'Invalid request. Please try again.';
    } else {
        $crop_id = post_text('crop_id');
        $fertilizer_name = post_text('fertilizer_name');
        $quantity = post_text('quantity');
        $unit = post_text('unit');
        $applied_date = post_text('applied_date');
        $notes = post_text('notes');

        $cropName = '';
        foreach ($crops as $crop) {
            if ($crop['id'] == $crop_id) {
                $cropName = $crop['crop_name'];
            }
        }

        if ($cropName == '') {
            $error = 'Please select a crop';
        } elseif ($fertilizer_name == '') {
            $error = 'Please enter fertilizer name';
        } elseif (!is_numeric($quantity) || $quantity <= 0) {
            $error = 'Please enter a valid quantity';
        } elseif ($unit != 'kg' && $unit != 'g' && $unit != 'liter') {
            $error = 'Please select a unit';
        } elseif (!valid_date($applied_date)) {
            $error = 'Please enter a valid date';
        } else {
            // save fertilizer
            $ok = stmt_run(
                $conn,
                'INSERT INTO fertilizers (crop_id, fertilizer_name, quantity, unit, applied_date, notes) VALUES (?, ?, ?, ?, ?, ?)',
                'isssss',
                array((int)$crop_id, $fertilizer_name, $quantity, $unit, $applied_date, $notes)
            );
            if (!$ok) {
                $error = 'Something went wrong. Please try again.';
            } else {
                log_activity($conn, 'Added fertilizer', $fertilizer_name . ' for ' . $cropName);
                flash('success', 'Fertilizer record added.');
                redirect('fertilizer/index.php');
            }
        }
    }
}

$pageTitle = 'Add Fertilizer';
require __DIR__ . '/../includes/header.php';
?>
<div class="page-top">
    <div>
        <h1>Add Fertilizer</h1>
        <a class="back-link" href="index.php">Back to fertilizer</a>
    </div>
</div>
<div class="card form-box">
    <div class="card-body">
        <?php if ($loadError != '') { ?>
            <div class="alert alert-danger"><?php echo e($loadError); ?></div>
        <?php } elseif (!$crops) { ?>
            <p class="mb-0">Add a crop before you record fertilizer.</p>
        <?php } else {
            if ($error != '') { ?>
                <div class="alert alert-danger"><?php echo e($error); ?></div>
            <?php } ?>
            <form method="post">
                <?php echo csrf_field(); ?>
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
                    <label class="form-label" for="fertilizer_name">Fertilizer name</label>
                    <input class="form-control" type="text" name="fertilizer_name" id="fertilizer_name" value="<?php echo e($fertilizer_name); ?>">
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
                        <option value="g" <?php if ($unit == 'g') { echo 'selected'; } ?>>g</option>
                        <option value="liter" <?php if ($unit == 'liter') { echo 'selected'; } ?>>liter</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label" for="applied_date">Applied date</label>
                    <input class="form-control" type="date" name="applied_date" id="applied_date" value="<?php echo e($applied_date); ?>">
                </div>
                <div class="mb-3">
                    <label class="form-label" for="notes">Notes</label>
                    <textarea class="form-control" name="notes" id="notes" rows="3"><?php echo e($notes); ?></textarea>
                </div>
                <button class="btn btn-primary" type="submit">Save</button>
            </form>
        <?php } ?>
    </div>
</div>
<?php require __DIR__ . '/../includes/footer.php'; ?>
