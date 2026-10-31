<?php
require __DIR__ . '/../includes/init.php';
require_login();

$list = stmt_all($conn, 'SELECT id, username, full_name, created_at FROM users ORDER BY full_name, id', '', array());
$listError = '';
if ($list === null) {
    $listError = 'Could not load profiles.';
    $list = array();
}

$pageTitle = 'Profile';
require __DIR__ . '/../includes/header.php';
?>
<div class="page-top">
    <h1>Profiles</h1>
    <a class="btn btn-primary" href="create.php">Add Profile</a>
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
                        <th>Name</th>
                        <th>Username</th>
                        <th>Created</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                <?php if (!$list) { ?>
                    <tr><td colspan="4">No profiles yet.</td></tr>
                <?php } else {
                    foreach ($list as $row) { ?>
                        <tr>
                            <td><?php echo e($row['full_name']); ?></td>
                            <td><?php echo e($row['username']); ?></td>
                            <td><?php echo e($row['created_at']); ?></td>
                            <td class="actions">
                                <a class="icon-btn" href="view.php?id=<?php echo (int)$row['id']; ?>" title="View"><i class="bi bi-eye"></i></a>
                                <a class="icon-btn" href="update.php?id=<?php echo (int)$row['id']; ?>" title="Edit"><i class="bi bi-pencil"></i></a>
                                <form method="post" action="view.php" class="d-inline" onsubmit="return confirm('Delete this profile?');">
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
