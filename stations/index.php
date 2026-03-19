<?php
require_once '../config/db.php';
include '../includes/header.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') {
    header("Location: " . BASE_URL . "index.php");
    exit;
}

// Fetch all stations
$stmt = $pdo->query("SELECT * FROM Station ORDER BY Station_Name ASC");
$stations = $stmt->fetchAll();
?>

<div class="container">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <h2 style="color: var(--primary-color);">Manage Stations</h2>
        <a href="<?php echo BASE_URL; ?>stations/add.php" class="btn-action btn-primary"><i class="fa fa-plus"></i> Add New Station</a>
    </div>

    <?php if(isset($_GET['msg']) && $_GET['msg'] == 'deleted'): ?>
        <p style="color: green; background: #d4edda; padding: 10px; border-radius: 4px;">Station deleted successfully.</p>
    <?php endif; ?>

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Station Name</th>
                <th>Station Code</th>
                <th>Location</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach($stations as $s): ?>
            <tr>
                <td><?php echo htmlspecialchars($s['Station_ID']); ?></td>
                <td><?php echo htmlspecialchars($s['Station_Name']); ?></td>
                <td><?php echo htmlspecialchars($s['Station_Code']); ?></td>
                <td><?php echo htmlspecialchars($s['Location']); ?></td>
                <td>
                    <a href="<?php echo BASE_URL; ?>stations/add.php?id=<?php echo $s['Station_ID']; ?>" class="btn-action btn-edit">Edit</a>
                    <a href="<?php echo BASE_URL; ?>stations/delete.php?id=<?php echo $s['Station_ID']; ?>" class="btn-action btn-delete" onclick="return confirm('Are you sure you want to delete this station?');">Delete</a>
                </td>
            </tr>
            <?php endforeach; ?>
            <?php if(empty($stations)): ?>
            <tr>
                <td colspan="5" style="text-align: center;">No stations found in the system.</td>
            </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php include '../includes/footer.php'; ?>
