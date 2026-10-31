<?php
require __DIR__ . '/../includes/init.php';
require_login();

$error = '';
$crop_name = '';
$variety = '';
$season = '';
$area = '';
$area_unit = '';
$planting_date = '';
$expected_harvest_date = '';
$status = 'Growing';
$notes = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (!csrf_ok()) {
        $error = 'Invalid request. Please try again.';
    } else {
        $crop_name = post_text('crop_name');
        $variety = post_text('variety');
        $season = post_text('season');
        $area = post_text('area');
        $area_unit = post_text('area_unit');
        $planting_date = post_text('planting_date');
        $expected_harvest_date = post_text('expected_harvest_date');
        $status = post_text('status');
        $notes = post_text('notes');

        if ($crop_name == '') {
            $error = 'Please enter crop name';
        } elseif ($variety == '') {
            $error = 'Please enter variety';
        } elseif ($season != 'Rainy' && $season != 'Winter' && $season != 'Summer') {
            $error = 'Please select a season';
        } elseif (!is_numeric($area) || $area <= 0) {
            $error = 'Please enter a valid area';
        } elseif ($area_unit != 'hectare' && $area_unit != 'acre' && $area_unit != 'ropani') {
            $error = 'Please select an area unit';
        } elseif (!valid_date($planting_date)) {
            $error = 'Please enter a valid planting date';
        } elseif ($expected_harvest_date != '' && !valid_date($expected_harvest_date)) {
            $error = 'Please enter a valid expected harvest date';
        } elseif ($status != 'Growing' && $status != 'Harvested') {
            $error = 'Please select a status';
        } else {
            // save crop
            $ok = stmt_run(
                $conn,
                "INSERT INTO crops (crop_name, variety, season, area, area_unit, planting_date, expected_harvest_date, status, notes) VALUES (?, ?, ?, ?, ?, ?, NULLIF(?, ''), ?, ?)",
                'sssssssss',
                array($crop_name, $variety, $season, $area, $area_unit, $planting_date, $expected_harvest_date, $status, $notes)
            );
            if (!$ok) {
                $error = 'Something went wrong. Please try again.';
            } else {
                log_activity($conn, 'Added crop', $crop_name);
                flash('success', 'Crop added.');
                redirect('crops/index.php');
            }
        }
    }
}

$pageTitle = 'Add Crop';
require __DIR__ . '/../includes/header.php';
?>
<div class="page-top">
    <div>
        <h1>Add Crop</h1>
        <a class="back-link" href="index.php">Back to crops</a>
    </div>
</div>
<div class="card form-box">
    <div class="card-body">
        <?php if ($error != '') { ?>
            <div class="alert alert-danger"><?php echo e($error); ?></div>
        <?php } ?>
        <form method="post">
            <?php echo csrf_field(); ?>
            <div class="mb-3">
                <label class="form-label" for="crop_name">Crop name</label>
                <input class="form-control" type="text" name="crop_name" id="crop_name" value="<?php echo e($crop_name); ?>">
            </div>
            <div class="mb-3">
                <label class="form-label" for="variety">Variety</label>
                <input class="form-control" type="text" name="variety" id="variety" value="<?php echo e($variety); ?>">
            </div>
            <div class="mb-3">
                <label class="form-label" for="season">Season</label>
                <select class="form-select" name="season" id="season">
                    <option value="">Select</option>
                    <option value="Rainy" <?php if ($season == 'Rainy') { echo 'selected'; } ?>>Rainy</option>
                    <option value="Winter" <?php if ($season == 'Winter') { echo 'selected'; } ?>>Winter</option>
                    <option value="Summer" <?php if ($season == 'Summer') { echo 'selected'; } ?>>Summer</option>
                </select>
            </div>
            <div class="mb-3">
                <label class="form-label" for="area">Area</label>
                <input class="form-control" type="number" name="area" id="area" step="0.01" value="<?php echo e($area); ?>">
            </div>
            <div class="mb-3">
                <label class="form-label" for="area_unit">Area unit</label>
                <select class="form-select" name="area_unit" id="area_unit">
                    <option value="">Select</option>
                    <option value="hectare" <?php if ($area_unit == 'hectare') { echo 'selected'; } ?>>hectare</option>
                    <option value="acre" <?php if ($area_unit == 'acre') { echo 'selected'; } ?>>acre</option>
                    <option value="ropani" <?php if ($area_unit == 'ropani') { echo 'selected'; } ?>>ropani</option>
                </select>
            </div>
            <div class="mb-3">
                <label class="form-label" for="status">Status</label>
                <select class="form-select" name="status" id="status">
                    <option value="Growing" <?php if ($status == 'Growing') { echo 'selected'; } ?>>Growing</option>
                    <option value="Harvested" <?php if ($status == 'Harvested') { echo 'selected'; } ?>>Harvested</option>
                </select>
            </div>
            <div class="mb-3">
                <label class="form-label" for="planting_date">Planting date</label>
                <input class="form-control" type="date" name="planting_date" id="planting_date" value="<?php echo e($planting_date); ?>">
            </div>
            <div class="mb-3">
                <label class="form-label" for="expected_harvest_date">Expected harvest date</label>
                <input class="form-control" type="date" name="expected_harvest_date" id="expected_harvest_date" value="<?php echo e($expected_harvest_date); ?>">
            </div>
            <div class="mb-3">
                <label class="form-label" for="notes">Notes</label>
                <textarea class="form-control" name="notes" id="notes" rows="3"><?php echo e($notes); ?></textarea>
            </div>
            <button class="btn btn-primary" type="submit">Save</button>
        </form>
    </div>
</div>
<?php require __DIR__ . '/../includes/footer.php'; ?>
