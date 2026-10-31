<?php
if (!isset($pageTitle) || $pageTitle === '') {
    $pageTitle = 'Crop Irrigation';
}
$loggedIn = !empty($_SESSION['user_id']);
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo e($pageTitle); ?> - Crop Irrigation</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="<?php echo e(url('assets/css/style.css')); ?>" rel="stylesheet">
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark farm-nav">
    <div class="container">
        <a class="navbar-brand" href="<?php echo e(url($loggedIn ? 'dashboard.php' : 'login.php')); ?>"><i class="bi bi-droplet"></i> Crop Irrigation</a>
        <?php if ($loggedIn) { ?>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav" aria-controls="mainNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="mainNav">
            <ul class="navbar-nav me-auto">
                <li class="nav-item"><a class="nav-link<?php echo nav_on('dashboard.php'); ?>" href="<?php echo e(url('dashboard.php')); ?>">Dashboard</a></li>
                <li class="nav-item"><a class="nav-link<?php echo nav_on('crops'); ?>" href="<?php echo e(url('crops/index.php')); ?>">Crops</a></li>
                <li class="nav-item"><a class="nav-link<?php echo nav_on('irrigation'); ?>" href="<?php echo e(url('irrigation/index.php')); ?>">Irrigation</a></li>
                <li class="nav-item"><a class="nav-link<?php echo nav_on('fertilizer'); ?>" href="<?php echo e(url('fertilizer/index.php')); ?>">Fertilizer</a></li>
                <li class="nav-item"><a class="nav-link<?php echo nav_on('harvest'); ?>" href="<?php echo e(url('harvest/index.php')); ?>">Harvest</a></li>
                <li class="nav-item"><a class="nav-link<?php echo nav_on('reports'); ?>" href="<?php echo e(url('reports/index.php')); ?>">Reports</a></li>
                <li class="nav-item"><a class="nav-link<?php echo nav_on('profile'); ?>" href="<?php echo e(url('profile/index.php')); ?>">Profile</a></li>
            </ul>
            <a class="navbar-user" href="<?php echo e(url('profile/view.php?id=' . (int)$_SESSION['user_id'])); ?>"><?php echo e(isset($_SESSION['full_name']) ? $_SESSION['full_name'] : ''); ?></a>
            <form method="post" action="<?php echo e(url('logout.php')); ?>" class="d-flex">
                <?php echo csrf_field(); ?>
                <button class="btn btn-outline-light btn-sm" type="submit">Logout</button>
            </form>
        </div>
        <?php } ?>
    </div>
</nav>
<main class="container py-4">
<?php
$flash = take_flash();
if ($flash) {
    echo '<div class="alert alert-' . e($flash['type']) . '">' . e($flash['text']) . '</div>';
}
?>
